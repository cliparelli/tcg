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

        case 'deck':
            $relPath = (string) ($_GET['file'] ?? '');
            if ($relPath === '') {
                throw new InvalidArgumentException('Parâmetro "file" é obrigatório.');
            }

            $deck = $deckLibrary->readDeck($relPath);
            echo json_encode(['file' => $relPath, 'deck' => $deck], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            break;

        default:
            http_response_code(400);
            echo json_encode(['error' => 'Ação desconhecida.'], JSON_UNESCAPED_UNICODE);
    }
} catch (InvalidArgumentException $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
