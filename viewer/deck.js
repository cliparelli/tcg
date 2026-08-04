(function () {
    'use strict';

    const deckSelect = document.getElementById('deck-select');
    const sortField = document.getElementById('sort-field');
    const sortDirection = document.getElementById('sort-direction');
    const typeFilter = document.getElementById('type-filter');
    const typeFilterInputs = Array.from(typeFilter.querySelectorAll('input[type="checkbox"]'));
    const styleFilter = document.getElementById('style-filter');
    const styleFilterInputs = styleFilter ? Array.from(styleFilter.querySelectorAll('input[type="checkbox"]')) : [];
    const deckPane = document.getElementById('deck-pane');
    const shareBtn = document.getElementById('share-btn');

    let decks = [];

    const cardModal = document.getElementById('card-modal');
    const cardModalImage = document.getElementById('card-modal-image');
    const cardModalBackdrop = document.getElementById('card-modal-backdrop');
    const cardModalClose = document.getElementById('card-modal-close');

    cardModalBackdrop.addEventListener('click', closeCardModal);
    cardModalClose.addEventListener('click', closeCardModal);
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            closeCardModal();
        }
    });
    shareBtn.addEventListener('click', shareDeck);

    init();

    async function init() {
        // Carrega os dados dos decks do arquivo JSON
        const data = await fetchJson('../decks/decks.json');
        decks = data.decks || [];

        sortField.addEventListener('change', renderDeckSelect);
        sortDirection.addEventListener('change', renderDeckSelect);
        for (const input of typeFilterInputs) {
            input.addEventListener('change', renderDeckSelect);
        }
        for (const input of styleFilterInputs) {
            input.addEventListener('change', renderDeckSelect);
        }
        deckSelect.addEventListener('change', () => loadDeck(deckSelect.value));

        renderDeckSelect();

        if (deckSelect.options.length > 0) {
            const queryParams = window.deckQueryParams || {};
            if (queryParams.deck) {
                deckSelect.value = queryParams.deck;
                if (Array.from(deckSelect.options).some((opt) => opt.value === queryParams.deck)) {
                    loadDeck(queryParams.deck);
                }
            } else {
                loadDeck(deckSelect.value);
            }
        }
    }

    function renderDeckSelect() {
        const previous = deckSelect.value;
        let filtered = filterDecksByType(decks, getSelectedTypes());
        filtered = filterDecksByStyle(filtered, getSelectedStyles());
        populateDeckSelect(sortDecks(filtered, sortField.value, sortDirection.value));

        if (previous && Array.from(deckSelect.options).some((opt) => opt.value === previous)) {
            deckSelect.value = previous;
        } else if (deckSelect.options.length > 0) {
            loadDeck(deckSelect.value);
        } else {
            deckPane.innerHTML = '';
            deckPane.appendChild(placeholderMessage('Nenhum deck corresponde aos filtros selecionados.'));
        }
    }

    function getSelectedTypes() {
        return typeFilterInputs.filter((input) => input.checked).map((input) => input.value);
    }

    function getSelectedStyles() {
        return styleFilterInputs.filter((input) => input.checked).map((input) => input.value);
    }

    function filterDecksByType(list, selectedTypes) {
        if (selectedTypes.length === typeFilterInputs.length) {
            return list;
        }
        return list.filter((deck) => {
            const deckTypes = deck.types || [];
            return deckTypes.some((type) => selectedTypes.includes(type));
        });
    }

    function filterDecksByStyle(list, selectedStyles) {
        if (selectedStyles.length === styleFilterInputs.length || selectedStyles.length === 0) {
            return list;
        }
        return list.filter((deck) => selectedStyles.includes(deck.style));
    }

    function sortDecks(list, field, direction) {
        const sorted = [...list].sort((a, b) => {
            const valueA = (a[field] || '').toLocaleLowerCase('pt-BR');
            const valueB = (b[field] || '').toLocaleLowerCase('pt-BR');
            return valueA.localeCompare(valueB, 'pt-BR');
        });

        if (direction === 'desc') {
            sorted.reverse();
        }

        return sorted;
    }

    function populateDeckSelect(decks) {
        deckSelect.innerHTML = '';
        for (const deck of decks) {
            const opt = document.createElement('option');
            opt.value = deck.file;
            opt.textContent = deck.title;
            deckSelect.appendChild(opt);
        }
    }

    async function loadDeck(file) {
        if (!file) {
            return;
        }
        deckPane.innerHTML = '<div class="deck-pane__placeholder">Carregando...</div>';
        const data = await fetchJson('deck-api.php?action=deck&file=' + encodeURIComponent(file));
        renderDeck(data.deck);
    }

    function renderDeck(deck) {
        shareBtn.hidden = false;
        deckPane.innerHTML = '';

        const header = document.createElement('div');
        header.className = 'deck-header';

        const h2 = document.createElement('h2');
        h2.textContent = deck.title || '(sem título)';
        header.appendChild(h2);

        if (deck.strategy) {
            const strategy = document.createElement('div');
            strategy.className = 'deck-header__strategy';
            strategy.textContent = deck.strategy;
            header.appendChild(strategy);
        }

        deckPane.appendChild(header);

        const body = document.createElement('div');
        body.className = 'deck-body';

        const grid = document.createElement('div');
        grid.className = 'deck-grid';

        let hasEntries = false;
        for (const section of deck.sections || []) {
            for (const group of section.groups || []) {
                for (const entry of group.entries || []) {
                    hasEntries = true;
                    grid.appendChild(renderCardEntry(entry));
                }
            }
        }

        if (hasEntries) {
            body.appendChild(grid);
            body.appendChild(renderDecklist(deck));
            deckPane.appendChild(body);
        } else {
            deckPane.appendChild(placeholderMessage('Este deck não tem cartas reconhecíveis.'));
        }
    }

    function renderDecklist(deck) {
        const list = document.createElement('div');
        list.className = 'decklist';

        for (const section of deck.sections || []) {
            const sectionEl = document.createElement('div');
            sectionEl.className = 'decklist__section';

            const title = document.createElement('div');
            title.className = 'decklist__section-title';
            title.textContent = section.name;
            sectionEl.appendChild(title);

            for (const group of section.groups || []) {
                if (group.name) {
                    const groupTitle = document.createElement('div');
                    groupTitle.className = 'decklist__group-title';
                    groupTitle.textContent = group.name;
                    sectionEl.appendChild(groupTitle);
                }

                for (const entry of group.entries || []) {
                    sectionEl.appendChild(renderDecklistRow(entry));
                }
            }

            list.appendChild(sectionEl);
        }

        return list;
    }

    function renderDecklistRow(entry) {
        const row = document.createElement('div');
        row.className = 'decklist__row' + (entry.combo ? ' decklist__row--combo' : '');

        const qty = document.createElement('span');
        qty.className = 'decklist__qty';
        qty.textContent = entry.qty + 'x';
        row.appendChild(qty);

        const name = document.createElement('span');
        name.className = 'decklist__name';
        name.textContent = entry.name;
        row.appendChild(name);

        return row;
    }

    function renderCardEntry(entry) {
        const wrapper = document.createElement('div');
        wrapper.className = 'deck-card' + (entry.combo ? ' deck-card--combo' : '');

        const thumb = document.createElement('div');
        thumb.className = 'deck-card__thumb';

        if (entry.expansionImage) {
            const src = 'asset.php?expansion=1&file=' + encodeURIComponent(entry.expansionImage);

            const img = document.createElement('img');
            img.className = 'deck-card__image';
            img.src = src;
            img.alt = entry.name;
            img.addEventListener('error', () => {
                img.remove();
                thumb.classList.remove('deck-card__thumb--clickable');
                thumb.classList.add('deck-card--missing');
                wrapper.classList.add('deck-card--missing');
                thumb.textContent = 'Sem imagem: ' + entry.name;
            });
            thumb.appendChild(img);

            thumb.classList.add('deck-card__thumb--clickable');
            thumb.addEventListener('click', () => openCardModal(src, entry.name));
        } else {
            thumb.classList.add('deck-card--missing');
            wrapper.classList.add('deck-card--missing');
            thumb.textContent = entry.card ? 'Sem imagem: ' + entry.name : 'Carta não encontrada: ' + entry.name;
        }

        const qty = document.createElement('div');
        qty.className = 'deck-card__qty';
        qty.textContent = 'x' + entry.qty;
        thumb.appendChild(qty);

        wrapper.appendChild(thumb);

        return wrapper;
    }

    function openCardModal(src, name) {
        cardModalImage.src = src;
        cardModalImage.alt = name;
        cardModal.hidden = false;
    }

    function closeCardModal() {
        cardModal.hidden = true;
        cardModalImage.src = '';
    }

    function placeholderMessage(message) {
        const el = document.createElement('div');
        el.className = 'deck-pane__placeholder';
        el.textContent = message;
        return el;
    }

    function shareDeck() {
        const deckFile = deckSelect.value;

        if (!deckFile) {
            return;
        }

        const url = new URL(window.location);
        url.searchParams.set('deck', deckFile);

        const shareUrl = url.toString();

        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(shareUrl).then(() => {
                showCopySuccess();
            }).catch(() => {
                fallbackCopy(shareUrl);
            });
        } else {
            fallbackCopy(shareUrl);
        }
    }

    function showCopySuccess() {
        const textSpan = shareBtn.querySelector('.share-btn__text');
        const originalText = textSpan.textContent;
        textSpan.textContent = '✓ Copiado!';
        shareBtn.classList.add('share-btn--copied');
        setTimeout(() => {
            textSpan.textContent = originalText;
            shareBtn.classList.remove('share-btn--copied');
        }, 2000);
    }

    function fallbackCopy(text) {
        const textarea = document.createElement('textarea');
        textarea.value = text;
        document.body.appendChild(textarea);
        textarea.select();
        try {
            document.execCommand('copy');
            showCopySuccess();
        } catch (err) {
            alert('Erro ao copiar link: ' + text);
        }
        document.body.removeChild(textarea);
    }

    async function fetchJson(url) {
        const res = await fetch(url);
        if (!res.ok) {
            throw new Error('Falha ao buscar ' + url);
        }
        return res.json();
    }
})();
