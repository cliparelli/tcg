<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Visualizador de Decks — MULTIVERSITY CONQUEST</title>
<link rel="stylesheet" href="style.css">
<link rel="stylesheet" href="deck.css">
</head>
<body>
<nav class="top-nav">
    <span class="top-nav__brand">MULTIVERSITY CONQUEST</span>
    <a class="top-nav__link" href="index.php">Card Viewer</a>
    <a class="top-nav__link active" href="deck.php">Deck Viewer</a>
</nav>
<div class="deck-app">
    <aside class="sidebar">
        <h1>Visualizador de Decks</h1>

        <button type="button" id="share-btn" class="share-btn" title="Copiar link deste deck" hidden>
            <span class="share-btn__icon">🔗</span>
            <span class="share-btn__text">Compartilhar</span>
        </button>

        <div class="field field--type-filter">
            <label>Filtrar por tipo</label>
            <div class="type-filter" id="type-filter">
                <label class="type-filter__option"><input type="checkbox" value="Magia" checked> Magia</label>
                <label class="type-filter__option"><input type="checkbox" value="TecSci" checked> TecSci</label>
                <label class="type-filter__option"><input type="checkbox" value="Físico" checked> Físico</label>
                <label class="type-filter__option"><input type="checkbox" value="Divino" checked> Divino</label>
                <label class="type-filter__option"><input type="checkbox" value="Cósmico" checked> Cósmico</label>
            </div>
        </div>

        <div class="field field--style-filter">
            <label>Filtrar por estilo</label>
            <div class="style-filter" id="style-filter">
                <label class="style-filter__option"><input type="checkbox" value="MONO" checked> MONO</label>
                <label class="style-filter__option"><input type="checkbox" value="DUAL" checked> DUAL</label>
                <label class="style-filter__option"><input type="checkbox" value="TRIPLE" checked> TRIPLE</label>
                <label class="style-filter__option"><input type="checkbox" value="RAINBOW" checked> RAINBOW</label>
            </div>
        </div>

        <div class="field field--sort">
            <label for="sort-field">Ordenar por</label>
            <select id="sort-field">
                <option value="title">Ordem alfabética (título)</option>
                <option value="file">Nome do arquivo</option>
                <option value="style">Estilo (Mono/Bi/Tri/Splash...)</option>
                <option value="type">Tipo (Físico, Cósmico...)</option>
            </select>
        </div>

        <div class="field field--sort-dir">
            <label for="sort-direction">Direção</label>
            <select id="sort-direction">
                <option value="asc">Crescente</option>
                <option value="desc">Decrescente</option>
            </select>
        </div>

        <div class="field field--deck">
            <label for="deck-select">Deck</label>
            <select id="deck-select"></select>
        </div>

        <p class="sidebar__hint">Arquivos lidos de <code>EXPANSIONS/*/schemas/decks/</code>.</p>
    </aside>

    <main class="deck-pane" id="deck-pane">
        <div class="deck-pane__placeholder">Selecione um deck na lista ao lado.</div>
    </main>
</div>

<div class="card-modal" id="card-modal" hidden>
    <div class="card-modal__backdrop" id="card-modal-backdrop"></div>
    <div class="card-modal__content">
        <button type="button" class="card-modal__close" id="card-modal-close" aria-label="Fechar">&times;</button>
        <img class="card-modal__image" id="card-modal-image" src="" alt="">
    </div>
</div>

<script>
(function() {
    const urlParams = new URLSearchParams(window.location.search);
    window.deckQueryParams = {
        deck: urlParams.get('deck')
    };
})();
</script>
<script src="deck.js"></script>
</body>
</html>
