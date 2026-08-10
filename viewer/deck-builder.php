<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Editor de Decks — MULTIVERSITY CONQUEST</title>
<link rel="stylesheet" href="style.css">
<link rel="stylesheet" href="deck.css">
<link rel="stylesheet" href="deck-builder.css">
</head>
<body>
<nav class="top-nav">
    <span class="top-nav__brand">MULTIVERSITY CONQUEST</span>
    <a class="top-nav__link" href="index.php">Card Viewer</a>
    <a class="top-nav__link" href="deck.php">Deck Viewer</a>
    <a class="top-nav__link active" href="deck-builder.php">Deck Builder</a>
</nav>

<div class="builder-app">
    <section class="builder-pool">
        <div class="builder-pool__controls">
            <div class="field field--collection">
                <label for="pool-collection">Coleção</label>
                <select id="pool-collection"></select>
            </div>

            <div class="field field--search">
                <label for="pool-search">Buscar</label>
                <input type="search" id="pool-search" placeholder="Nome da carta...">
            </div>

            <div class="field field--type-buttons">
                <label>Tipo</label>
                <div class="type-buttons" id="pool-type-buttons">
                    <button type="button" class="type-buttons__btn type-buttons__btn--active" data-type="">Todos</button>
                    <button type="button" class="type-buttons__btn" data-type="personagem">Personagens</button>
                    <button type="button" class="type-buttons__btn" data-type="item">Itens</button>
                    <button type="button" class="type-buttons__btn" data-type="energia">Energias</button>
                </div>
            </div>

            <div class="field field--world-filter">
                <label for="pool-world">Afinidade/Tipo</label>
                <select id="pool-world">
                    <option value="">Todas</option>
                </select>
            </div>
        </div>

        <div class="builder-pool__grid" id="pool-grid"></div>
    </section>

    <section class="builder-deck">
        <div class="builder-deck__header">
            <div class="builder-deck__picker">
                <div class="field field--deck-select">
                    <label for="deck-select">Deck</label>
                    <select id="deck-select">
                        <option value="">— Novo deck —</option>
                    </select>
                </div>
                <button type="button" id="new-deck-btn" class="btn btn--secondary">+ Novo deck</button>
            </div>

            <div class="builder-deck__meta" id="deck-meta-form">
                <div class="field field--new-collection" id="new-deck-collection-field" hidden>
                    <label for="deck-collection">Coleção</label>
                    <select id="deck-collection"></select>
                </div>
                <div class="field field--deck-name">
                    <label for="deck-name">Nome</label>
                    <input type="text" id="deck-name" placeholder="[MONO] Tema — Mundo (Afinidade)">
                </div>
                <div class="field field--deck-slug" id="deck-slug-field">
                    <label for="deck-slug">Slug (nome de arquivo)</label>
                    <input type="text" id="deck-slug" placeholder="mono-tema-mundo">
                </div>
                <div class="field field--deck-description">
                    <label for="deck-description">Descrição / estratégia</label>
                    <textarea id="deck-description" rows="3" placeholder="Prosa livre de estratégia..."></textarea>
                </div>
            </div>

            <div class="builder-deck__actions">
                <button type="button" id="save-deck-btn" class="btn btn--primary">Salvar deck</button>
                <span id="save-status" class="save-status"></span>
            </div>
        </div>

        <div class="builder-deck__lists">
            <div class="builder-deck__section" id="deck-entries-section">
                <h3>Deck Principal</h3>
                <div id="deck-entries" class="builder-deck__rows"></div>
            </div>

            <div class="builder-deck__section" id="deck-sideboard-section">
                <h3>Sideboard</h3>
                <div id="deck-sideboard" class="builder-deck__rows"></div>
            </div>
        </div>
    </section>

    <aside class="builder-legality" id="builder-legality">
        <h2>Legalidade</h2>
        <div class="legality-status" id="legality-status">Selecione ou crie um deck.</div>

        <dl class="legality-counters" id="legality-counters"></dl>

        <div class="legality-messages" id="legality-messages"></div>
    </aside>
</div>

<script src="deck-builder.js"></script>
</body>
</html>
