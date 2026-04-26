<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260425100000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Seed Carpet Unique channel from environment configuration';
    }

    public function up(Schema $schema): void
    {
        $channelCode = $this->env('SYLIUS_CHANNEL_CODE', 'CARPET_UNIQUE');

        if ($this->exists('sylius_channel', 'code', $channelCode)) {
            return;
        }

        $now = (new \DateTimeImmutable())->format('Y-m-d H:i:s');
        $defaultLocaleCode = $this->env('SYLIUS_CHANNEL_DEFAULT_LOCALE', 'en_US');
        $localeCodes = $this->csvEnv('SYLIUS_CHANNEL_LOCALES', [$defaultLocaleCode]);
        $baseCurrencyCode = $this->env('SYLIUS_CHANNEL_BASE_CURRENCY', 'EUR');
        $currencyCodes = $this->csvEnv('SYLIUS_CHANNEL_CURRENCIES', [$baseCurrencyCode]);
        $hostname = $this->env('SYLIUS_CHANNEL_HOSTNAME', $this->env('SERVER_NAME', 'localhost'));

        if (!in_array($defaultLocaleCode, $localeCodes, true)) {
            array_unshift($localeCodes, $defaultLocaleCode);
        }

        if (!in_array($baseCurrencyCode, $currencyCodes, true)) {
            array_unshift($currencyCodes, $baseCurrencyCode);
        }

        $localeIds = [];
        foreach ($localeCodes as $localeCode) {
            $localeIds[$localeCode] = $this->findOrCreateLocale($localeCode, $now);
        }

        $currencyIds = [];
        foreach ($currencyCodes as $currencyCode) {
            $currencyIds[$currencyCode] = $this->findOrCreateCurrency($currencyCode, $now);
        }

        $menuTaxonId = $this->connection->fetchOne('SELECT id FROM sylius_taxon WHERE code = ?', ['CARPETS']) ?: null;

        $this->connection->insert('sylius_channel_price_history_config', [
            'lowest_price_for_discounted_products_checking_period' => 30,
            'lowest_price_for_discounted_products_visible' => 1,
        ]);

        $priceHistoryConfigId = (int) $this->connection->lastInsertId();

        $this->connection->insert('sylius_channel', [
            'shop_billing_data_id' => null,
            'channel_price_history_config_id' => $priceHistoryConfigId,
            'default_locale_id' => $localeIds[$defaultLocaleCode],
            'base_currency_id' => $currencyIds[$baseCurrencyCode],
            'default_tax_zone_id' => null,
            'menu_taxon_id' => $menuTaxonId !== false ? $menuTaxonId : null,
            'code' => $channelCode,
            'name' => $this->env('SYLIUS_CHANNEL_NAME', 'Carpet Unique'),
            'color' => '#1f6f5b',
            'description' => null,
            'enabled' => 1,
            'hostname' => $hostname,
            'created_at' => $now,
            'updated_at' => null,
            'theme_name' => null,
            'tax_calculation_strategy' => 'order_items_based',
            'contact_email' => $this->env('SYLIUS_CHANNEL_CONTACT_EMAIL', 'kontakt@landteppiche.de'),
            'contact_phone_number' => $this->env('SYLIUS_CHANNEL_CONTACT_PHONE', null),
            'skipping_shipping_step_allowed' => 0,
            'skipping_payment_step_allowed' => 0,
            'account_verification_required' => 0,
            'shipping_address_in_checkout_required' => 1,
        ]);

        $channelId = (int) $this->connection->lastInsertId();

        foreach ($localeIds as $localeId) {
            $this->connection->insert('sylius_channel_locales', [
                'channel_id' => $channelId,
                'locale_id' => $localeId,
            ]);
        }

        foreach ($currencyIds as $currencyId) {
            $this->connection->insert('sylius_channel_currencies', [
                'channel_id' => $channelId,
                'currency_id' => $currencyId,
            ]);
        }
    }

    public function down(Schema $schema): void
    {
        $channelCode = $this->env('SYLIUS_CHANNEL_CODE', 'CARPET_UNIQUE');
        $channel = $this->connection->fetchAssociative(
            'SELECT id, channel_price_history_config_id FROM sylius_channel WHERE code = ?',
            [$channelCode],
        );

        if ($channel === false) {
            return;
        }

        $channelId = (int) $channel['id'];
        $priceHistoryConfigId = $channel['channel_price_history_config_id'] !== null ? (int) $channel['channel_price_history_config_id'] : null;

        $this->connection->delete('sylius_channel_locales', ['channel_id' => $channelId]);
        $this->connection->delete('sylius_channel_currencies', ['channel_id' => $channelId]);
        $this->connection->delete('sylius_channel_countries', ['channel_id' => $channelId]);
        $this->connection->delete('sylius_channel', ['id' => $channelId]);

        if ($priceHistoryConfigId !== null) {
            $this->connection->delete('sylius_channel_price_history_config', ['id' => $priceHistoryConfigId]);
        }
    }

    private function findOrCreateLocale(string $code, string $createdAt): int
    {
        $id = $this->connection->fetchOne('SELECT id FROM sylius_locale WHERE code = ?', [$code]);

        if ($id !== false) {
            return (int) $id;
        }

        $this->connection->insert('sylius_locale', [
            'code' => $code,
            'created_at' => $createdAt,
            'updated_at' => null,
        ]);

        return (int) $this->connection->lastInsertId();
    }

    private function findOrCreateCurrency(string $code, string $createdAt): int
    {
        $id = $this->connection->fetchOne('SELECT id FROM sylius_currency WHERE code = ?', [$code]);

        if ($id !== false) {
            return (int) $id;
        }

        $this->connection->insert('sylius_currency', [
            'code' => $code,
            'created_at' => $createdAt,
            'updated_at' => null,
        ]);

        return (int) $this->connection->lastInsertId();
    }

    private function exists(string $table, string $field, string $value): bool
    {
        return (bool) $this->connection->fetchOne(sprintf('SELECT 1 FROM %s WHERE %s = ?', $table, $field), [$value]);
    }

    /**
     * @return list<string>
     */
    private function csvEnv(string $name, array $default): array
    {
        $value = $this->env($name, null);

        if ($value === null || trim($value) === '') {
            return $default;
        }

        $items = array_filter(array_map(static fn (string $item): string => trim($item), explode(',', $value)));

        return array_values(array_unique($items));
    }

    private function env(string $name, ?string $default): ?string
    {
        $value = $_ENV[$name] ?? $_SERVER[$name] ?? getenv($name);

        if ($value === false || $value === '') {
            return $default;
        }

        return (string) $value;
    }
}
