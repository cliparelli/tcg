<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Visualizador de Cartas — MULTIVERSITY CONQUEST</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<div class="app">
    <aside class="sidebar">
        <h1>Visualizador de Cartas</h1>

        <div class="field">
            <label for="file-select">Arquivo CSV</label>
            <select id="file-select"></select>
        </div>

        <div class="field">
            <label for="search">Buscar</label>
            <input type="search" id="search" placeholder="Nome da carta...">
        </div>

        <ul id="card-list" class="card-list"></ul>
    </aside>

    <main class="preview-pane">
        <div id="card-preview" class="card-preview">
            <div class="card-preview__placeholder">Selecione uma carta na lista ao lado.</div>
        </div>
        <div id="card-raw" class="card-raw"></div>
    </main>
</div>

<script src="app.js"></script>
</body>
</html>
