<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260425101000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Seed Germany VAT configuration for Carpet Unique channel';
    }

    public function up(Schema $schema): void
    {
        $now = (new \DateTimeImmutable())->format('Y-m-d H:i:s');

        $countryCode = $this->env('SYLIUS_TAX_COUNTRY_CODE', 'DE');
        $zoneCode = $this->env('SYLIUS_TAX_ZONE_CODE', 'DE');
        $zoneName = $this->env('SYLIUS_TAX_ZONE_NAME', 'Germany');
        $categoryCode = $this->env('SYLIUS_TAX_CATEGORY_CODE', 'STANDARD_VAT');
        $categoryName = $this->env('SYLIUS_TAX_CATEGORY_NAME', 'Standard VAT');
        $rateCode = $this->env('SYLIUS_TAX_RATE_CODE', 'DE_STANDARD_VAT_19');
        $rateName = $this->env('SYLIUS_TAX_RATE_NAME', 'Germany VAT 19%');
        $rateAmount = $this->normalizeRate($this->env('SYLIUS_TAX_RATE_AMOUNT', '0.19'));
        $includedInPrice = $this->boolEnv('SYLIUS_TAX_INCLUDED_IN_PRICE', true);
        $channelCode = $this->env('SYLIUS_CHANNEL_CODE', 'CARPET_UNIQUE');

        $countryId = $this->findOrCreateCountry($countryCode);
        $zoneId = $this->findOrCreateTaxZone($zoneCode, $zoneName);
        $categoryId = $this->findOrCreateTaxCategory($categoryCode, $categoryName, $now);

        $this->findOrCreateZoneMember($zoneId, $countryCode);
        $this->findOrCreateTaxRate($rateCode, $rateName, $rateAmount, $includedInPrice, $categoryId, $zoneId, $now);

        $channelId = $this->connection->fetchOne('SELECT id FROM sylius_channel WHERE code = ?', [$channelCode]);

        if ($channelId === false) {
            return;
        }

        $this->connection->update('sylius_channel', ['default_tax_zone_id' => $zoneId], ['id' => (int) $channelId]);
        $this->findOrCreateChannelCountry((int) $channelId, $countryId);
    }

    public function down(Schema $schema): void
    {
        $channelCode = $this->env('SYLIUS_CHANNEL_CODE', 'CARPET_UNIQUE');
        $countryCode = $this->env('SYLIUS_TAX_COUNTRY_CODE', 'DE');
        $zoneCode = $this->env('SYLIUS_TAX_ZONE_CODE', 'DE');
        $categoryCode = $this->env('SYLIUS_TAX_CATEGORY_CODE', 'STANDARD_VAT');
        $rateCode = $this->env('SYLIUS_TAX_RATE_CODE', 'DE_STANDARD_VAT_19');

        $channelId = $this->connection->fetchOne('SELECT id FROM sylius_channel WHERE code = ?', [$channelCode]);
        $countryId = $this->connection->fetchOne('SELECT id FROM sylius_country WHERE code = ?', [$countryCode]);
        $zoneId = $this->connection->fetchOne('SELECT id FROM sylius_zone WHERE code = ?', [$zoneCode]);

        if ($channelId !== false && $zoneId !== false) {
            $this->connection->executeStatement(
                'UPDATE sylius_channel SET default_tax_zone_id = NULL WHERE id = ? AND default_tax_zone_id = ?',
                [(int) $channelId, (int) $zoneId],
            );
        }

        if ($channelId !== false && $countryId !== false) {
            $this->connection->delete('sylius_channel_countries', [
                'channel_id' => (int) $channelId,
                'country_id' => (int) $countryId,
            ]);
        }

        $this->connection->delete('sylius_tax_rate', ['code' => $rateCode]);

        if ($zoneId !== false) {
            $this->connection->delete('sylius_zone_member', ['belongs_to' => (int) $zoneId, 'code' => $countryCode]);
            $this->connection->delete('sylius_zone', ['id' => (int) $zoneId]);
        }

        $categoryInUse = (bool) $this->connection->fetchOne(
            'SELECT 1 FROM sylius_product_variant WHERE tax_category_id = (SELECT id FROM sylius_tax_category WHERE code = ?)',
            [$categoryCode],
        );

        if (!$categoryInUse) {
            $this->connection->delete('sylius_tax_category', ['code' => $categoryCode]);
        }
    }

    private function findOrCreateCountry(string $code): int
    {
        $id = $this->connection->fetchOne('SELECT id FROM sylius_country WHERE code = ?', [$code]);

        if ($id !== false) {
            $this->connection->update('sylius_country', ['enabled' => 1], ['id' => (int) $id]);

            return (int) $id;
        }

        $this->connection->insert('sylius_country', [
            'code' => $code,
            'enabled' => 1,
        ]);

        return (int) $this->connection->lastInsertId();
    }

    private function findOrCreateTaxZone(string $code, string $name): int
    {
        $id = $this->connection->fetchOne('SELECT id FROM sylius_zone WHERE code = ?', [$code]);

        if ($id !== false) {
            $this->connection->update('sylius_zone', ['name' => $name, 'type' => 'country', 'scope' => 'tax'], ['id' => (int) $id]);

            return (int) $id;
        }

        $this->connection->insert('sylius_zone', [
            'code' => $code,
            'name' => $name,
            'type' => 'country',
            'scope' => 'tax',
            'priority' => 0,
        ]);

        return (int) $this->connection->lastInsertId();
    }

    private function findOrCreateTaxCategory(string $code, string $name, string $createdAt): int
    {
        $id = $this->connection->fetchOne('SELECT id FROM sylius_tax_category WHERE code = ?', [$code]);

        if ($id !== false) {
            $this->connection->update('sylius_tax_category', ['name' => $name, 'updated_at' => $createdAt], ['id' => (int) $id]);

            return (int) $id;
        }

        $this->connection->insert('sylius_tax_category', [
            'code' => $code,
            'name' => $name,
            'description' => null,
            'created_at' => $createdAt,
            'updated_at' => null,
        ]);

        return (int) $this->connection->lastInsertId();
    }

    private function findOrCreateZoneMember(int $zoneId, string $countryCode): void
    {
        if ($this->connection->fetchOne('SELECT 1 FROM sylius_zone_member WHERE belongs_to = ? AND code = ?', [$zoneId, $countryCode]) !== false) {
            return;
        }

        $this->connection->insert('sylius_zone_member', [
            'belongs_to' => $zoneId,
            'code' => $countryCode,
        ]);
    }

    private function findOrCreateTaxRate(string $code, string $name, string $amount, bool $includedInPrice, int $categoryId, int $zoneId, string $createdAt): void
    {
        $data = [
            'category_id' => $categoryId,
            'zone_id' => $zoneId,
            'name' => $name,
            'amount' => $amount,
            'included_in_price' => $includedInPrice ? 1 : 0,
            'calculator' => 'default',
        ];

        $id = $this->connection->fetchOne('SELECT id FROM sylius_tax_rate WHERE code = ?', [$code]);

        if ($id !== false) {
            $this->connection->update('sylius_tax_rate', $data + ['updated_at' => $createdAt], ['id' => (int) $id]);

            return;
        }

        $this->connection->insert('sylius_tax_rate', $data + [
            'code' => $code,
            'start_date' => null,
            'end_date' => null,
            'created_at' => $createdAt,
            'updated_at' => null,
        ]);
    }

    private function findOrCreateChannelCountry(int $channelId, int $countryId): void
    {
        if ($this->connection->fetchOne('SELECT 1 FROM sylius_channel_countries WHERE channel_id = ? AND country_id = ?', [$channelId, $countryId]) !== false) {
            return;
        }

        $this->connection->insert('sylius_channel_countries', [
            'channel_id' => $channelId,
            'country_id' => $countryId,
        ]);
    }

    private function normalizeRate(string $rate): string
    {
        return number_format((float) $rate, 5, '.', '');
    }

    private function boolEnv(string $name, bool $default): bool
    {
        $value = $this->env($name, $default ? '1' : '0');

        return in_array(strtolower($value), ['1', 'true', 'yes', 'on'], true);
    }

    private function env(string $name, string $default): string
    {
        $value = $_ENV[$name] ?? $_SERVER[$name] ?? getenv($name);

        if ($value === false || $value === '') {
            return $default;
        }

        return (string) $value;
    }
}
