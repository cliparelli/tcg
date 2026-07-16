<?php

declare(strict_types=1);

require_once __DIR__ . '/lib/CsvLibrary.php';
require_once __DIR__ . '/lib/CardTypes.php';

header('Content-Type: application/json; charset=utf-8');

$libDir = dirname(__DIR__) . '/LIB';
$library = new CsvLibrary($libDir);

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

        case 'save-card':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new InvalidArgumentException('Ação "save-card" requer método POST.');
            }

            $body = json_decode(file_get_contents('php://input') ?: '', true);
            if (!is_array($body)) {
                throw new InvalidArgumentException('Corpo da requisição inválido.');
            }

            $relPath = (string) ($body['file'] ?? '');
            $id = (string) ($body['id'] ?? '');
            $fields = $body['fields'] ?? null;

            if ($relPath === '' || $id === '' || !is_array($fields)) {
                throw new InvalidArgumentException('Parâmetros "file", "id" e "fields" são obrigatórios.');
            }

            $stringFields = array_map(static fn ($v): string => (string) $v, $fields);
            $library->updateRow($relPath, $id, $stringFields);

            $rows = $library->readByRelPath($relPath);
            echo json_encode(['file' => $relPath, 'cards' => $rows], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            break;

        default:
            http_response_code(400);
            echo json_encode(['error' => 'Ação desconhecida.'], JSON_UNESCAPED_UNICODE);
    }
} catch (InvalidArgumentException $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
