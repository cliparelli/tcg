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

    const SECTION_LABELS = {
        personagem: 'Personagens',
        item: 'Itens',
        energia: 'Fontes de Energia',
    };

    const ROLE_LABELS = {
        leader: 'Líderes',
        team: 'Time',
    };

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
        const data = await fetchJson('deck-api.php?action=list');
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
            const fieldName = field === 'title' ? 'title' : field;
            const valueA = (a[fieldName] || '').toLocaleLowerCase('pt-BR');
            const valueB = (b[fieldName] || '').toLocaleLowerCase('pt-BR');
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

        if (deck.description) {
            const description = document.createElement('div');
            description.className = 'deck-header__strategy';
            description.textContent = deck.description;
            header.appendChild(description);
        }

        const coreCombo = deck.strategy && deck.strategy.coreCombo;
        if (coreCombo && coreCombo.description) {
            const combo = document.createElement('div');
            combo.className = 'deck-header__combo';
            combo.innerHTML = '<strong>Combo central:</strong> ' + escapeHtml(coreCombo.description);
            header.appendChild(combo);
        }

        deckPane.appendChild(header);

        const body = document.createElement('div');
        body.className = 'deck-body';

        const grid = document.createElement('div');
        grid.className = 'deck-grid';

        const comboCardIds = new Set((coreCombo && coreCombo.cardIds) || []);
        const entries = deck.entries || [];
        for (const entry of entries) {
            grid.appendChild(renderCardEntry(entry, comboCardIds.has(entry.cardId)));
        }

        if (entries.length > 0) {
            body.appendChild(grid);
            body.appendChild(renderDecklist(deck, comboCardIds));
            deckPane.appendChild(body);
        } else {
            deckPane.appendChild(placeholderMessage('Este deck não tem cartas reconhecíveis.'));
        }
    }

    function groupEntries(entries) {
        const bySection = new Map();

        for (const entry of entries) {
            const sectionKey = entry.cardType || 'outro';
            if (!bySection.has(sectionKey)) {
                bySection.set(sectionKey, new Map());
            }
            const byRole = bySection.get(sectionKey);
            const roleKey = sectionKey === 'personagem' ? (entry.suggestedRole || '') : '';
            if (!byRole.has(roleKey)) {
                byRole.set(roleKey, []);
            }
            byRole.get(roleKey).push(entry);
        }

        return bySection;
    }

    function renderDecklist(deck, comboCardIds) {
        const list = document.createElement('div');
        list.className = 'decklist';

        const grouped = groupEntries(deck.entries || []);

        for (const [sectionKey, byRole] of grouped) {
            const sectionEl = document.createElement('div');
            sectionEl.className = 'decklist__section';

            const title = document.createElement('div');
            title.className = 'decklist__section-title';
            title.textContent = SECTION_LABELS[sectionKey] || sectionKey;
            sectionEl.appendChild(title);

            for (const [roleKey, roleEntries] of byRole) {
                if (roleKey && ROLE_LABELS[roleKey]) {
                    const groupTitle = document.createElement('div');
                    groupTitle.className = 'decklist__group-title';
                    groupTitle.textContent = ROLE_LABELS[roleKey];
                    sectionEl.appendChild(groupTitle);
                }

                for (const entry of roleEntries) {
                    sectionEl.appendChild(renderDecklistRow(entry, comboCardIds.has(entry.cardId)));
                }
            }

            list.appendChild(sectionEl);
        }

        if (deck.sideboard && deck.sideboard.length > 0) {
            const sideboardEl = document.createElement('div');
            sideboardEl.className = 'decklist__section';

            const title = document.createElement('div');
            title.className = 'decklist__section-title';
            title.textContent = 'Sideboard';
            sideboardEl.appendChild(title);

            for (const entry of deck.sideboard) {
                sideboardEl.appendChild(renderDecklistRow(entry, comboCardIds.has(entry.cardId)));
            }

            list.appendChild(sideboardEl);
        }

        return list;
    }

    function renderDecklistRow(entry, isCombo) {
        const row = document.createElement('div');
        row.className = 'decklist__row' + (isCombo ? ' decklist__row--combo' : '');

        const qty = document.createElement('span');
        qty.className = 'decklist__qty';
        qty.textContent = entry.quantity + 'x';
        row.appendChild(qty);

        const name = document.createElement('span');
        name.className = 'decklist__name';
        name.textContent = entry.card ? entry.card.name : entry.cardId;
        row.appendChild(name);

        return row;
    }

    function renderCardEntry(entry, isCombo) {
        const wrapper = document.createElement('div');
        wrapper.className = 'deck-card' + (isCombo ? ' deck-card--combo' : '');

        const cardName = entry.card ? entry.card.name : entry.cardId;

        const thumb = document.createElement('div');
        thumb.className = 'deck-card__thumb';

        if (entry.expansionImage) {
            const src = 'asset.php?expansion=1&file=' + encodeURIComponent(entry.expansionImage);

            const img = document.createElement('img');
            img.className = 'deck-card__image';
            img.src = src;
            img.alt = cardName;
            img.addEventListener('error', () => {
                img.remove();
                thumb.classList.remove('deck-card__thumb--clickable');
                thumb.classList.add('deck-card--missing');
                wrapper.classList.add('deck-card--missing');
                thumb.textContent = 'Sem imagem: ' + cardName;
            });
            thumb.appendChild(img);

            thumb.classList.add('deck-card__thumb--clickable');
            thumb.addEventListener('click', () => openCardModal(src, cardName));
        } else {
            thumb.classList.add('deck-card--missing');
            wrapper.classList.add('deck-card--missing');
            thumb.textContent = entry.card ? 'Sem imagem: ' + cardName : 'Carta não encontrada: ' + cardName;
        }

        const qty = document.createElement('div');
        qty.className = 'deck-card__qty';
        qty.textContent = 'x' + entry.quantity;
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

    function escapeHtml(str) {
        return String(str ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;');
    }

    async function fetchJson(url) {
        const res = await fetch(url);
        if (!res.ok) {
            throw new Error('Falha ao buscar ' + url);
        }
        return res.json();
    }
})();
