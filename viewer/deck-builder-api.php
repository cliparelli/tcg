<?php

declare(strict_types=1);

require_once __DIR__ . '/lib/CardLibrary.php';
require_once __DIR__ . '/lib/DeckLibrary.php';

header('Content-Type: application/json; charset=utf-8');

$expansionsDir = dirname(__DIR__) . '/EXPANSIONS';
$cardLibrary = new CardLibrary($expansionsDir);
$deckLibrary = new DeckLibrary($expansionsDir, $cardLibrary);

$action = $_GET['action'] ?? 'list';

try {
    switch ($action) {
        case 'list':
            echo json_encode(['decks' => $deckLibrary->listDecks()], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            break;

        case 'collections':
            $scan = $cardLibrary->scan();
            $names = array_map(static fn (array $c): string => $c['name'], $scan['collections']);
            echo json_encode(['collections' => $names], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            break;

        case 'cards':
            $collection = (string) ($_GET['collection'] ?? '');
            if ($collection === '') {
                throw new InvalidArgumentException('Parâmetro "collection" é obrigatório.');
            }

            $scan = $cardLibrary->scan();
            $cards = [];
            foreach ($scan['collections'] as $c) {
                if ($c['name'] !== $collection) {
                    continue;
                }
                foreach ($c['files'] as $file) {
                    foreach ($cardLibrary->readByRelPath($file['relPath']) as $record) {
                        $cards[] = $record;
                    }
                }
            }

            echo json_encode(['collection' => $collection, 'cards' => $cards], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            break;

        case 'deck':
            $relPath = (string) ($_GET['file'] ?? '');
            if ($relPath === '') {
                throw new InvalidArgumentException('Parâmetro "file" é obrigatório.');
            }

            $deck = $deckLibrary->readDeck($relPath);
            echo json_encode(['file' => $relPath, 'deck' => $deck], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            break;

        case 'create':
            if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
                throw new InvalidArgumentException('Use POST para esta ação.');
            }

            $body = json_decode(file_get_contents('php://input') ?: '', true);
            if (!is_array($body)) {
                throw new InvalidArgumentException('Corpo JSON inválido.');
            }

            $collection = (string) ($body['collection'] ?? '');
            $meta = is_array($body['meta'] ?? null) ? $body['meta'] : [];

            $relPath = $deckLibrary->createDeck($collection, $meta);
            $deck = $deckLibrary->readDeck($relPath);
            echo json_encode(['file' => $relPath, 'deck' => $deck], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            break;

        case 'save':
            if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
                throw new InvalidArgumentException('Use POST para esta ação.');
            }

            $body = json_decode(file_get_contents('php://input') ?: '', true);
            if (!is_array($body)) {
                throw new InvalidArgumentException('Corpo JSON inválido.');
            }

            $relPath = (string) ($body['file'] ?? '');
            $deck = is_array($body['deck'] ?? null) ? $body['deck'] : null;
            if ($relPath === '' || $deck === null) {
                throw new InvalidArgumentException('Parâmetros "file" e "deck" são obrigatórios.');
            }

            $deckLibrary->saveDeck($relPath, $deck);
            $saved = $deckLibrary->readDeck($relPath);
            echo json_encode(['file' => $relPath, 'deck' => $saved], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            break;

        default:
            http_response_code(400);
            echo json_encode(['error' => 'Ação desconhecida.'], JSON_UNESCAPED_UNICODE);
    }
} catch (InvalidArgumentException $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
