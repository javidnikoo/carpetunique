<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260425090000 extends AbstractMigration
{
    /** @var list<string> */
    private const LOCALES = ['en_US', 'de_DE'];

    public function getDescription(): string
    {
        return 'Seed initial carpet taxons with translations';
    }

    public function up(Schema $schema): void
    {
        if ($this->taxonExists('CARPETS')) {
            return;
        }

        $now = (new \DateTimeImmutable())->format('Y-m-d H:i:s');
        $position = 1;
        $cursor = 1;

        $tree = [
            'code' => 'CARPETS',
            'translations' => [
                'en_US' => ['name' => 'Carpets', 'slug' => 'carpets'],
                'de_DE' => ['name' => 'Teppiche', 'slug' => 'teppiche'],
            ],
            'children' => [
                [
                    'code' => 'BY_STYLE',
                    'translations' => [
                        'en_US' => ['name' => 'By Style', 'slug' => 'by-style'],
                        'de_DE' => ['name' => 'Nach Stil', 'slug' => 'nach-stil'],
                    ],
                    'children' => [
                        $this->leaf('ORIENTAL_CARPETS', 'Oriental Carpets', 'oriental-carpets', 'Orientteppiche', 'orientteppiche'),
                        $this->leaf('PERSIAN_RUGS', 'Persian Rugs', 'persian-rugs', 'Persische Teppiche', 'persische-teppiche'),
                        $this->leaf('MODERN_CARPETS', 'Modern Carpets', 'modern-carpets', 'Moderne Teppiche', 'moderne-teppiche'),
                        $this->leaf('VINTAGE_CARPETS', 'Vintage Carpets', 'vintage-carpets', 'Vintage-Teppiche', 'vintage-teppiche'),
                        $this->leaf('CLASSIC_CARPETS', 'Classic Carpets', 'classic-carpets', 'Klassische Teppiche', 'klassische-teppiche'),
                        $this->leaf('MINIMALIST_CARPETS', 'Minimalist Carpets', 'minimalist-carpets', 'Minimalistische Teppiche', 'minimalistische-teppiche'),
                    ],
                ],
                [
                    'code' => 'BY_ROOM',
                    'translations' => [
                        'en_US' => ['name' => 'By Room', 'slug' => 'by-room'],
                        'de_DE' => ['name' => 'Nach Raum', 'slug' => 'nach-raum'],
                    ],
                    'children' => [
                        $this->leaf('LIVING_ROOM_CARPETS', 'Living Room Carpets', 'living-room-carpets', 'Wohnzimmerteppiche', 'wohnzimmerteppiche'),
                        $this->leaf('BEDROOM_CARPETS', 'Bedroom Carpets', 'bedroom-carpets', 'Schlafzimmerteppiche', 'schlafzimmerteppiche'),
                        $this->leaf('DINING_ROOM_CARPETS', 'Dining Room Carpets', 'dining-room-carpets', 'Esszimmerteppiche', 'esszimmerteppiche'),
                        $this->leaf('HALLWAY_RUNNERS', 'Hallway Runners', 'hallway-runners', 'Flurlaeufer', 'flurlaeufer'),
                        $this->leaf('OFFICE_CARPETS', 'Office Carpets', 'office-carpets', 'Bueroteppiche', 'bueroteppiche'),
                        $this->leaf('OUTDOOR_CARPETS', 'Outdoor Carpets', 'outdoor-carpets', 'Outdoor-Teppiche', 'outdoor-teppiche'),
                    ],
                ],
                [
                    'code' => 'BY_SIZE',
                    'translations' => [
                        'en_US' => ['name' => 'By Size', 'slug' => 'by-size'],
                        'de_DE' => ['name' => 'Nach Groesse', 'slug' => 'nach-groesse'],
                    ],
                    'children' => [
                        $this->leaf('SMALL_CARPETS', 'Small Carpets', 'small-carpets', 'Kleine Teppiche', 'kleine-teppiche'),
                        $this->leaf('MEDIUM_CARPETS', 'Medium Carpets', 'medium-carpets', 'Mittlere Teppiche', 'mittlere-teppiche'),
                        $this->leaf('LARGE_CARPETS', 'Large Carpets', 'large-carpets', 'Grosse Teppiche', 'grosse-teppiche'),
                        $this->leaf('OVERSIZED_CARPETS', 'Oversized Carpets', 'oversized-carpets', 'Uebergroesse Teppiche', 'uebergroesse-teppiche'),
                        $this->leaf('RUNNERS', 'Runners', 'runners', 'Laeufer', 'laeufer'),
                        $this->leaf('ROUND_CARPETS', 'Round Carpets', 'round-carpets', 'Runde Teppiche', 'runde-teppiche'),
                    ],
                ],
                [
                    'code' => 'BY_MATERIAL',
                    'translations' => [
                        'en_US' => ['name' => 'By Material', 'slug' => 'by-material'],
                        'de_DE' => ['name' => 'Nach Material', 'slug' => 'nach-material'],
                    ],
                    'children' => [
                        $this->leaf('WOOL_CARPETS', 'Wool Carpets', 'wool-carpets', 'Wollteppiche', 'wollteppiche'),
                        $this->leaf('SILK_CARPETS', 'Silk Carpets', 'silk-carpets', 'Seidenteppiche', 'seidenteppiche'),
                        $this->leaf('COTTON_CARPETS', 'Cotton Carpets', 'cotton-carpets', 'Baumwollteppiche', 'baumwollteppiche'),
                        $this->leaf('VISCOSE_CARPETS', 'Viscose Carpets', 'viscose-carpets', 'Viskoseteppiche', 'viskoseteppiche'),
                        $this->leaf('SYNTHETIC_CARPETS', 'Synthetic Carpets', 'synthetic-carpets', 'Synthetische Teppiche', 'synthetische-teppiche'),
                        $this->leaf('JUTE_CARPETS', 'Jute Carpets', 'jute-carpets', 'Juteteppiche', 'juteteppiche'),
                    ],
                ],
                [
                    'code' => 'BY_CRAFTSMANSHIP',
                    'translations' => [
                        'en_US' => ['name' => 'By Craftsmanship', 'slug' => 'by-craftsmanship'],
                        'de_DE' => ['name' => 'Nach Fertigung', 'slug' => 'nach-fertigung'],
                    ],
                    'children' => [
                        $this->leaf('HANDMADE_CARPETS', 'Handmade Carpets', 'handmade-carpets', 'Handgefertigte Teppiche', 'handgefertigte-teppiche'),
                        $this->leaf('HANDKNOTTED_CARPETS', 'Handknotted Carpets', 'handknotted-carpets', 'Handgeknuepfte Teppiche', 'handgeknuepfte-teppiche'),
                        $this->leaf('HANDWOVEN_CARPETS', 'Handwoven Carpets', 'handwoven-carpets', 'Handgewebte Teppiche', 'handgewebte-teppiche'),
                        $this->leaf('FLATWEAVE_CARPETS', 'Flatweave Carpets', 'flatweave-carpets', 'Flachgewebe-Teppiche', 'flachgewebe-teppiche'),
                        $this->leaf('KILIMS', 'Kilims', 'kilims', 'Kilims', 'kilims'),
                        $this->leaf('MACHINE_MADE_CARPETS', 'Machine-made Carpets', 'machine-made-carpets', 'Maschinell gefertigte Teppiche', 'maschinell-gefertigte-teppiche'),
                    ],
                ],
            ],
        ];

        $rootId = $this->insertNode(
            node: $tree,
            parentId: null,
            treeRoot: null,
            level: 0,
            position: $position,
            cursor: $cursor,
            createdAt: $now,
        );

        $this->connection->update('sylius_taxon', ['tree_root' => $rootId], ['id' => $rootId]);
    }

    public function down(Schema $schema): void
    {
        $codes = [
            'CARPETS',
            'BY_STYLE',
            'ORIENTAL_CARPETS',
            'PERSIAN_RUGS',
            'MODERN_CARPETS',
            'VINTAGE_CARPETS',
            'CLASSIC_CARPETS',
            'MINIMALIST_CARPETS',
            'BY_ROOM',
            'LIVING_ROOM_CARPETS',
            'BEDROOM_CARPETS',
            'DINING_ROOM_CARPETS',
            'HALLWAY_RUNNERS',
            'OFFICE_CARPETS',
            'OUTDOOR_CARPETS',
            'BY_SIZE',
            'SMALL_CARPETS',
            'MEDIUM_CARPETS',
            'LARGE_CARPETS',
            'OVERSIZED_CARPETS',
            'RUNNERS',
            'ROUND_CARPETS',
            'BY_MATERIAL',
            'WOOL_CARPETS',
            'SILK_CARPETS',
            'COTTON_CARPETS',
            'VISCOSE_CARPETS',
            'SYNTHETIC_CARPETS',
            'JUTE_CARPETS',
            'BY_CRAFTSMANSHIP',
            'HANDMADE_CARPETS',
            'HANDKNOTTED_CARPETS',
            'HANDWOVEN_CARPETS',
            'FLATWEAVE_CARPETS',
            'KILIMS',
            'MACHINE_MADE_CARPETS',
        ];

        $taxonIds = $this->connection->fetchFirstColumn(
            'SELECT id FROM sylius_taxon WHERE code IN (?)',
            [$codes],
            [ArrayParameterType::STRING],
        );

        if ($taxonIds === []) {
            return;
        }

        $this->connection->executeStatement(
            'DELETE FROM sylius_taxon_translation WHERE translatable_id IN (?)',
            [$taxonIds],
            [ArrayParameterType::INTEGER],
        );

        $this->connection->executeStatement(
            'DELETE FROM sylius_taxon WHERE id IN (?)',
            [$taxonIds],
            [ArrayParameterType::INTEGER],
        );
    }

    /**
     * @param array{
     *     code: string,
     *     translations: array<string, array{name: string, slug: string}>,
     *     children?: list<array{
     *         code: string,
     *         translations: array<string, array{name: string, slug: string}>,
     *         children?: list<mixed>
     *     }>
     * } $node
     */
    private function insertNode(
        array $node,
        ?int $parentId,
        ?int $treeRoot,
        int $level,
        int &$position,
        int &$cursor,
        string $createdAt,
    ): int {
        $left = $cursor++;

        $this->connection->insert('sylius_taxon', [
            'tree_root' => $treeRoot,
            'parent_id' => $parentId,
            'code' => $node['code'],
            'tree_left' => $left,
            'tree_right' => 0,
            'tree_level' => $level,
            'position' => $position++,
            'enabled' => 1,
            'created_at' => $createdAt,
            'updated_at' => null,
        ]);

        $taxonId = (int) $this->connection->lastInsertId();
        $effectiveTreeRoot = $treeRoot ?? $taxonId;

        if ($treeRoot === null) {
            $this->connection->update('sylius_taxon', ['tree_root' => $taxonId], ['id' => $taxonId]);
        }

        foreach (self::LOCALES as $locale) {
            $translation = $node['translations'][$locale];

            $this->connection->insert('sylius_taxon_translation', [
                'translatable_id' => $taxonId,
                'name' => $translation['name'],
                'slug' => $translation['slug'],
                'description' => null,
                'locale' => $locale,
            ]);
        }

        $childPosition = 0;
        foreach ($node['children'] ?? [] as $childNode) {
            $this->insertNode(
                node: $childNode,
                parentId: $taxonId,
                treeRoot: $effectiveTreeRoot,
                level: $level + 1,
                position: $childPosition,
                cursor: $cursor,
                createdAt: $createdAt,
            );
        }

        $right = $cursor++;

        $this->connection->update('sylius_taxon', ['tree_right' => $right], ['id' => $taxonId]);

        return $taxonId;
    }

    private function taxonExists(string $code): bool
    {
        return (bool) $this->connection->fetchOne(
            'SELECT 1 FROM sylius_taxon WHERE code = ?',
            [$code],
        );
    }

    /** @return array{code: string, translations: array<string, array{name: string, slug: string}>} */
    private function leaf(
        string $code,
        string $nameEn,
        string $slugEn,
        string $nameDe,
        string $slugDe,
    ): array {
        return [
            'code' => $code,
            'translations' => [
                'en_US' => ['name' => $nameEn, 'slug' => $slugEn],
                'de_DE' => ['name' => $nameDe, 'slug' => $slugDe],
            ],
        ];
    }
}
