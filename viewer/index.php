<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Visualizador de Cartas — MULTIVERSITY CONQUEST</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<nav class="top-nav">
    <span class="top-nav__brand">MULTIVERSITY CONQUEST</span>
    <a class="top-nav__link active" href="index.php">Card Viewer</a>
    <a class="top-nav__link" href="deck.php">Deck Viewer</a>
</nav>
<div class="app">
    <aside class="sidebar">
        <h1>Visualizador de Cartas</h1>

        <button type="button" id="share-btn" class="share-btn" title="Copiar link desta carta" hidden>
            <span class="share-btn__icon">🔗</span>
            <span class="share-btn__text">Compartilhar</span>
        </button>

        <div class="field field--collection">
            <label for="collection-select">Coleção</label>
            <select id="collection-select"></select>
        </div>

        <div class="field field--type-buttons">
            <label>Tipo</label>
            <div class="type-buttons" id="type-buttons">
                <button type="button" class="type-buttons__btn" data-type="personagem">Personagens</button>
                <button type="button" class="type-buttons__btn" data-type="item">Itens</button>
                <button type="button" class="type-buttons__btn" data-type="energia">Energias</button>
            </div>
        </div>

        <div class="field field--card-select">
            <label for="card-select">Carta</label>
            <select id="card-select"></select>
        </div>

        <div class="field field--search">
            <label for="search">Buscar</label>
            <input type="search" id="search" placeholder="Nome da carta...">
        </div>

        <div class="field field--sort">
            <label for="sort-select">Ordenar por</label>
            <select id="sort-select">
                <option value="nome">Nome (A-Z)</option>
                <option value="tipo">Tipo</option>
                <option value="faccao">Facção / Tribo</option>
            </select>
        </div>

        <ul id="card-list" class="card-list"></ul>
    </aside>

    <main class="preview-pane">
        <div id="card-preview" class="card-preview">
            <div class="card-preview__placeholder">Selecione uma carta na lista ou no combo acima.</div>
        </div>
        <div id="card-raw" class="card-raw"></div>
    </main>
</div>

<script>
(function() {
    const urlParams = new URLSearchParams(window.location.search);
    window.appQueryParams = {
        collection: urlParams.get('collection'),
        type: urlParams.get('type'),
        card: urlParams.get('card')
    };
})();
</script>
<script src="app.js"></script>
</body>
</html>
