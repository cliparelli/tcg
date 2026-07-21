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
            echo json_encode(['decks' => listDecks($decksDir)], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
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
 * @return array<int, array{file: string, title: string}>
 */
function listDecks(string $decksDir): array
{
    if (!is_dir($decksDir)) {
        return [];
    }

    $files = glob($decksDir . '/*.md') ?: [];
    sort($files);

    $decks = [];
    foreach ($files as $path) {
        $contents = file_get_contents($path) ?: '';
        $title = basename($path, '.md');
        if (preg_match('/^#\s+(.+)$/m', $contents, $m) === 1) {
            $title = trim($m[1]);
        }
        $decks[] = ['file' => basename($path), 'title' => $title];
    }

    return $decks;
}
