<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260425091000 extends AbstractMigration
{
    /** @var list<string> */
    private const LOCALES = ['en_US', 'de_DE'];

    public function getDescription(): string
    {
        return 'Seed carpet product attributes with translations';
    }

    public function up(Schema $schema): void
    {
        $now = (new \DateTimeImmutable())->format('Y-m-d H:i:s');

        $attributes = [
            [
                'code' => 'CARPET_MATERIAL',
                'type' => 'select',
                'storage_type' => 'json',
                'position' => 0,
                'translatable' => 0,
                'translations' => [
                    'en_US' => 'Material',
                    'de_DE' => 'Material',
                ],
                'configuration' => [
                    'multiple' => false,
                    'choices' => [
                        'wool' => ['en_US' => 'Wool', 'de_DE' => 'Wolle'],
                        'silk' => ['en_US' => 'Silk', 'de_DE' => 'Seide'],
                        'cotton' => ['en_US' => 'Cotton', 'de_DE' => 'Baumwolle'],
                        'viscose' => ['en_US' => 'Viscose', 'de_DE' => 'Viskose'],
                        'jute' => ['en_US' => 'Jute', 'de_DE' => 'Jute'],
                        'synthetic' => ['en_US' => 'Synthetic', 'de_DE' => 'Synthetik'],
                        'mixed' => ['en_US' => 'Mixed', 'de_DE' => 'Mischgewebe'],
                    ],
                ],
            ],
            [
                'code' => 'CARPET_SIZE',
                'type' => 'text',
                'storage_type' => 'text',
                'position' => 1,
                'translatable' => 0,
                'translations' => [
                    'en_US' => 'Size',
                    'de_DE' => 'Groesse',
                ],
                'configuration' => [],
            ],
            [
                'code' => 'CARPET_COLOR_FAMILY',
                'type' => 'select',
                'storage_type' => 'json',
                'position' => 2,
                'translatable' => 0,
                'translations' => [
                    'en_US' => 'Color Family',
                    'de_DE' => 'Farbfamilie',
                ],
                'configuration' => [
                    'multiple' => true,
                    'choices' => [
                        'beige' => ['en_US' => 'Beige', 'de_DE' => 'Beige'],
                        'cream' => ['en_US' => 'Cream', 'de_DE' => 'Creme'],
                        'blue' => ['en_US' => 'Blue', 'de_DE' => 'Blau'],
                        'red' => ['en_US' => 'Red', 'de_DE' => 'Rot'],
                        'green' => ['en_US' => 'Green', 'de_DE' => 'Gruen'],
                        'gray' => ['en_US' => 'Gray', 'de_DE' => 'Grau'],
                        'black' => ['en_US' => 'Black', 'de_DE' => 'Schwarz'],
                        'brown' => ['en_US' => 'Brown', 'de_DE' => 'Braun'],
                        'multicolor' => ['en_US' => 'Multicolor', 'de_DE' => 'Mehrfarbig'],
                    ],
                ],
            ],
            [
                'code' => 'CARPET_KNOT_DENSITY',
                'type' => 'integer',
                'storage_type' => 'integer',
                'position' => 3,
                'translatable' => 0,
                'translations' => [
                    'en_US' => 'Knot Density',
                    'de_DE' => 'Knotendichte',
                ],
                'configuration' => [],
            ],
            [
                'code' => 'CARPET_PRODUCTION_METHOD',
                'type' => 'select',
                'storage_type' => 'json',
                'position' => 4,
                'translatable' => 0,
                'translations' => [
                    'en_US' => 'Production Method',
                    'de_DE' => 'Herstellungsart',
                ],
                'configuration' => [
                    'multiple' => false,
                    'choices' => [
                        'handmade' => ['en_US' => 'Handmade', 'de_DE' => 'Handgefertigt'],
                        'handknotted' => ['en_US' => 'Handknotted', 'de_DE' => 'Handgeknuepft'],
                        'handwoven' => ['en_US' => 'Handwoven', 'de_DE' => 'Handgewebt'],
                        'machine_made' => ['en_US' => 'Machine-made', 'de_DE' => 'Maschinell gefertigt'],
                    ],
                ],
            ],
            [
                'code' => 'CARPET_ORIGIN_COUNTRY',
                'type' => 'select',
                'storage_type' => 'json',
                'position' => 5,
                'translatable' => 0,
                'translations' => [
                    'en_US' => 'Origin Country',
                    'de_DE' => 'Herkunftsland',
                ],
                'configuration' => [
                    'multiple' => false,
                    'choices' => [
                        'iran' => ['en_US' => 'Iran', 'de_DE' => 'Iran'],
                        'turkey' => ['en_US' => 'Turkey', 'de_DE' => 'Tuerkei'],
                        'india' => ['en_US' => 'India', 'de_DE' => 'Indien'],
                        'pakistan' => ['en_US' => 'Pakistan', 'de_DE' => 'Pakistan'],
                        'afghanistan' => ['en_US' => 'Afghanistan', 'de_DE' => 'Afghanistan'],
                        'morocco' => ['en_US' => 'Morocco', 'de_DE' => 'Marokko'],
                        'nepal' => ['en_US' => 'Nepal', 'de_DE' => 'Nepal'],
                        'china' => ['en_US' => 'China', 'de_DE' => 'China'],
                    ],
                ],
            ],
        ];

        foreach ($attributes as $attribute) {
            if ($this->attributeExists($attribute['code'])) {
                continue;
            }

            $this->connection->insert('sylius_product_attribute', [
                'code' => $attribute['code'],
                'type' => $attribute['type'],
                'storage_type' => $attribute['storage_type'],
                'configuration' => json_encode($attribute['configuration'], JSON_THROW_ON_ERROR),
                'created_at' => $now,
                'updated_at' => null,
                'position' => $attribute['position'],
                'translatable' => $attribute['translatable'],
            ]);

            $attributeId = (int) $this->connection->lastInsertId();

            foreach (self::LOCALES as $locale) {
                $this->connection->insert('sylius_product_attribute_translation', [
                    'translatable_id' => $attributeId,
                    'name' => $attribute['translations'][$locale],
                    'locale' => $locale,
                ]);
            }
        }
    }

    public function down(Schema $schema): void
    {
        $codes = [
            'CARPET_MATERIAL',
            'CARPET_SIZE',
            'CARPET_COLOR_FAMILY',
            'CARPET_KNOT_DENSITY',
            'CARPET_PRODUCTION_METHOD',
            'CARPET_ORIGIN_COUNTRY',
        ];

        $attributeIds = $this->connection->fetchFirstColumn(
            'SELECT id FROM sylius_product_attribute WHERE code IN (?)',
            [$codes],
            [ArrayParameterType::STRING],
        );

        if ($attributeIds === []) {
            return;
        }

        $this->connection->executeStatement(
            'DELETE FROM sylius_product_attribute_translation WHERE translatable_id IN (?)',
            [$attributeIds],
            [ArrayParameterType::INTEGER],
        );

        $this->connection->executeStatement(
            'DELETE FROM sylius_product_attribute WHERE id IN (?)',
            [$attributeIds],
            [ArrayParameterType::INTEGER],
        );
    }

    private function attributeExists(string $code): bool
    {
        return (bool) $this->connection->fetchOne(
            'SELECT 1 FROM sylius_product_attribute WHERE code = ?',
            [$code],
        );
    }
}
