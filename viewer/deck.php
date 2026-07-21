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
<div class="deck-app">
    <aside class="sidebar">
        <h1>Visualizador de Decks</h1>

        <div class="field field--deck">
            <label for="deck-select">Deck</label>
            <select id="deck-select"></select>
        </div>

        <p class="sidebar__hint">Arquivos lidos de <code>public/decks/</code>.</p>
        <a class="sidebar__link" href="index.php">&larr; Visualizador de cartas</a>
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

<script src="deck.js"></script>
</body>
</html>
