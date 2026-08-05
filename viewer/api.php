<?php

declare(strict_types=1);

require_once __DIR__ . '/lib/CardLibrary.php';
require_once __DIR__ . '/lib/CardTypes.php';

header('Content-Type: application/json; charset=utf-8');

$expansionsDir = dirname(__DIR__) . '/EXPANSIONS';
$library = new CardLibrary($expansionsDir);

$action = $_GET['action'] ?? 'list-files';

try {
    switch ($action) {
        case 'list-files':
            echo json_encode($library->scan(), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            break;

        case 'cards':
            $relPath = (string) ($_GET['file'] ?? '');
            if ($relPath === '') {
                throw new InvalidArgumentException('Parâmetro "file" é obrigatório.');
            }

            $rows = $library->readByRelPath($relPath);
            echo json_encode(['file' => $relPath, 'cards' => $rows], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            break;

        case 'update-stats':
            if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
                throw new InvalidArgumentException('Use POST para esta ação.');
            }

            $body = json_decode(file_get_contents('php://input') ?: '', true);
            if (!is_array($body)) {
                throw new InvalidArgumentException('Corpo JSON inválido.');
            }

            $relPath = (string) ($body['file'] ?? '');
            $cardId = (string) ($body['id'] ?? '');
            $life = array_key_exists('life', $body) && $body['life'] !== null ? (int) $body['life'] : null;
            $defense = array_key_exists('defense', $body) && $body['defense'] !== null ? (int) $body['defense'] : null;

            if ($relPath === '' || $cardId === '') {
                throw new InvalidArgumentException('Parâmetros "file" e "id" são obrigatórios.');
            }

            $card = $library->updateCharacterStats($relPath, $cardId, $life, $defense);
            echo json_encode(['card' => $card], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            break;

        case 'update-action':
            if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
                throw new InvalidArgumentException('Use POST para esta ação.');
            }

            $body = json_decode(file_get_contents('php://input') ?: '', true);
            if (!is_array($body)) {
                throw new InvalidArgumentException('Corpo JSON inválido.');
            }

            $relPath = (string) ($body['file'] ?? '');
            $cardId = (string) ($body['id'] ?? '');
            $actionIndex = array_key_exists('actionIndex', $body) ? (int) $body['actionIndex'] : -1;
            $field = (string) ($body['field'] ?? '');
            $value = $body['value'] ?? null;

            if ($relPath === '' || $cardId === '' || $actionIndex < 0 || $field === '') {
                throw new InvalidArgumentException('Parâmetros "file", "id", "actionIndex" e "field" são obrigatórios.');
            }

            $card = $library->updateCharacterAction($relPath, $cardId, $actionIndex, $field, $value);
            echo json_encode(['card' => $card], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            break;

        default:
            http_response_code(400);
            echo json_encode(['error' => 'Ação desconhecida.'], JSON_UNESCAPED_UNICODE);
    }
} catch (InvalidArgumentException $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
