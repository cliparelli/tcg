<?php

declare(strict_types=1);

require_once __DIR__ . '/CardLibrary.php';

/**
 * Casa entradas de decklist (nome + tags entre parênteses, ex. "Milo de Escorpião
 * Divino (SS)" ou "Yemira das Mil Dunas (v1) (Fratura do Multiverso)") com os
 * registros reais lidos via CardLibrary, tentando por nome + coleção/universo/versão.
 */
final class CardResolver
{
    private const UNIVERSE_ALIASES = [
        'ss' => 'saint seiya',
        'basic' => 'basic',
        'minecraft' => 'minecraft',
        'dc' => 'dc',
        'death note' => 'death note',
    ];

    /** @var array<int, array{collection: string, type: string, records: array<int, array<string, mixed>>}>|null */
    private ?array $index = null;

    public function __construct(
        private readonly CardLibrary $library,
        private readonly ?string $expansionsDir = null,
    ) {
    }

    /**
     * @param array<int, string> $tags
     * @return array{record: array<string, mixed>, type: string, collection: string}|null
     */
    public function resolve(string $name, array $tags): ?array
    {
        $this->buildIndex();

        $needle = self::normalize($name);
        $candidates = [];

        foreach ($this->index ?? [] as $bucket) {
            foreach ($bucket['records'] as $record) {
                $recordName = self::normalize((string) ($record['name'] ?? ''));
                if ($recordName === '') {
                    continue;
                }
                if ($recordName === $needle) {
                    $candidates[] = ['record' => $record, 'type' => $bucket['type'], 'collection' => $bucket['collection']];
                }
            }
        }

        if ($candidates === []) {
            return null;
        }

        if (count($candidates) === 1 || $tags === []) {
            return $candidates[0];
        }

        $normalizedTags = array_map(self::normalize(...), $tags);

        foreach ($candidates as $candidate) {
            if ($this->matchesTags($candidate, $normalizedTags)) {
                return $candidate;
            }
        }

        return $candidates[0];
    }

    /**
     * @param array{record: array<string, mixed>, type: string, collection: string} $candidate
     * @param array<int, string> $normalizedTags
     */
    private function matchesTags(array $candidate, array $normalizedTags): bool
    {
        $record = $candidate['record'];
        $collection = self::normalize($candidate['collection']);
        $versao = self::normalize((string) ($record['version'] ?? ''));

        foreach ($normalizedTags as $tag) {
            $aliased = self::UNIVERSE_ALIASES[$tag] ?? $tag;

            if ($collection === $tag || $collection === $aliased) {
                return true;
            }
            if ($versao !== '' && ($versao === $tag || $versao === ltrim($tag, 'v'))) {
                return true;
            }
        }

        return false;
    }

    private function buildIndex(): void
    {
        if ($this->index !== null) {
            return;
        }

        $this->index = [];
        $scan = $this->library->scan();

        foreach ($scan['collections'] as $collection) {
            foreach ($collection['files'] as $file) {
                $records = $this->library->readByRelPath($file['relPath']);
                $this->index[] = [
                    'collection' => $collection['name'],
                    'type' => $file['type'],
                    'records' => $records,
                ];
            }
        }
    }

    /**
     * Localiza, em EXPANSIONS/{coleção}/card-art/, o PNG já renderizado da carta,
     * a partir do campo assetRef do registro do schema.
     */
    public function resolveExpansionImage(string $collection, string $type, string $assetRef): ?string
    {
        if ($this->expansionsDir === null || $collection === '' || $assetRef === '') {
            return null;
        }

        $expansionsReal = realpath($this->expansionsDir);
        $path = realpath($this->expansionsDir . '/' . $collection . '/card-art/' . $assetRef);

        if ($path === false || $expansionsReal === false || !str_starts_with($path, $expansionsReal)) {
            return null;
        }

        return $collection . '/card-art/' . basename($path);
    }

    private static function normalize(string $value): string
    {
        $value = mb_strtolower(trim($value));
        $value = str_replace(['á', 'à', 'ã', 'â', 'ä'], 'a', $value);
        $value = str_replace(['é', 'è', 'ê', 'ë'], 'e', $value);
        $value = str_replace(['í', 'ì', 'î', 'ï'], 'i', $value);
        $value = str_replace(['ó', 'ò', 'õ', 'ô', 'ö'], 'o', $value);
        $value = str_replace(['ú', 'ù', 'û', 'ü'], 'u', $value);
        $value = str_replace('ç', 'c', $value);
        $value = preg_replace('/\s+/', ' ', $value) ?? $value;

        return trim($value);
    }
}
