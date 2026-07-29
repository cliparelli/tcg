<?php

declare(strict_types=1);

require_once __DIR__ . '/lib/CsvLibrary.php';
require_once __DIR__ . '/lib/DeckParser.php';
require_once __DIR__ . '/lib/CardResolver.php';

header('Content-Type: application/json; charset=utf-8');

$decksDir = dirname(__DIR__) . '/public/decks';
$libDir = dirname(__DIR__) . '/LIB';
$expansionsDir = dirname(__DIR__) . '/EXPANSIONS';

$action = $_GET['action'] ?? 'list';

try {
    switch ($action) {
        case 'list':
            $library = new CsvLibrary($libDir);
            $resolver = new CardResolver($library, $expansionsDir);
            echo json_encode(['decks' => listDecks($decksDir, $resolver)], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            break;

        case 'deck':
            $file = (string) ($_GET['file'] ?? '');
            if ($file === '') {
                throw new InvalidArgumentException('Parâmetro "file" é obrigatório.');
            }

            $path = realpath($decksDir . '/' . $file);
            $decksReal = realpath($decksDir);
            if ($path === false || $decksReal === false || !str_starts_with($path, $decksReal) || !is_file($path)) {
                throw new InvalidArgumentException('Deck não encontrado.');
            }

            $markdown = file_get_contents($path);
            if ($markdown === false) {
                throw new InvalidArgumentException('Não foi possível ler o arquivo.');
            }

            $deck = DeckParser::parse($markdown);
            $library = new CsvLibrary($libDir);
            $resolver = new CardResolver($library, $expansionsDir);

            foreach ($deck['sections'] as &$section) {
                foreach ($section['groups'] as &$group) {
                    foreach ($group['entries'] as &$entry) {
                        $match = $resolver->resolve($entry['name'], $entry['tags']);
                        if ($match !== null) {
                            $entry['card'] = $match['record'];
                            $entry['cardType'] = $match['type'];
                            $entry['collection'] = $match['collection'];
                            $entry['expansionImage'] = $resolver->resolveExpansionImage(
                                $match['collection'],
                                $match['type'],
                                $match['record']['Nome'] ?? $entry['name']
                            );
                        } else {
                            $entry['card'] = null;
                            $entry['cardType'] = null;
                            $entry['collection'] = null;
                            $entry['expansionImage'] = null;
                        }
                    }
                    unset($entry);
                }
                unset($group);
            }
            unset($section);

            echo json_encode(['file' => $file, 'deck' => $deck], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            break;

        default:
            http_response_code(400);
            echo json_encode(['error' => 'Ação desconhecida.'], JSON_UNESCAPED_UNICODE);
    }
} catch (InvalidArgumentException $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}

/**
 * @return array<int, array{file: string, title: string, style: string, type: string}>
 */
function listDecks(string $decksDir, CardResolver $resolver): array
{
    if (!is_dir($decksDir)) {
        return [];
    }

    $files = glob($decksDir . '/*.md') ?: [];
    sort($files);

    $decks = [];
    foreach ($files as $path) {
        $contents = file_get_contents($path) ?: '';
        $file = basename($path);
        $title = basename($path, '.md');
        if (preg_match('/^#\s+(.+)$/m', $contents, $m) === 1) {
            $title = trim($m[1]);
        }

        $deck = DeckParser::parse($contents);
        $decks[] = [
            'file' => $file,
            'title' => $title,
            'style' => deckStyle($title),
            'type' => deckType($deck, $resolver),
        ];
    }

    return $decks;
}

/**
 * Extrai o "estilo" do deck a partir da primeira palavra do título
 * (padrão observado nas decklists: "Mono", "Bi", "Tri", "Splash", "Aggro"...).
 */
function deckStyle(string $title): string
{
    if (preg_match('/^([A-Za-zÀ-ÿ]+)/u', $title, $m) === 1) {
        return $m[1];
    }

    return '';
}

/**
 * @param array{sections: array<int, array{groups: array<int, array{entries: array<int, array{name: string, tags: array<int, string>}>}>}>} $deck
 */
function deckType(array $deck, CardResolver $resolver): string
{
    $counts = [];

    foreach ($deck['sections'] as $section) {
        foreach ($section['groups'] as $group) {
            foreach ($group['entries'] as $entry) {
                $match = $resolver->resolve($entry['name'], $entry['tags']);
                if ($match === null || $match['type'] !== 'personagem') {
                    continue;
                }

                $tipo = trim($match['record']['Tipo'] ?? '');
                if ($tipo === '') {
                    continue;
                }

                $counts[$tipo] = ($counts[$tipo] ?? 0) + 1;
            }
        }
    }

    if ($counts === []) {
        return '';
    }

    arsort($counts);

    return array_key_first($counts);
}
