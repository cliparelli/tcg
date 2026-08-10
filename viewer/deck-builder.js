(function () {
    'use strict';

    const poolCollection = document.getElementById('pool-collection');
    const poolSearch = document.getElementById('pool-search');
    const poolTypeButtons = document.getElementById('pool-type-buttons');
    const poolWorld = document.getElementById('pool-world');
    const poolGrid = document.getElementById('pool-grid');

    const deckSelect = document.getElementById('deck-select');
    const newDeckBtn = document.getElementById('new-deck-btn');
    const newDeckCollectionField = document.getElementById('new-deck-collection-field');
    const deckCollectionSelect = document.getElementById('deck-collection');
    const deckSlugField = document.getElementById('deck-slug-field');
    const deckNameInput = document.getElementById('deck-name');
    const deckSlugInput = document.getElementById('deck-slug');
    const deckDescriptionInput = document.getElementById('deck-description');
    const saveDeckBtn = document.getElementById('save-deck-btn');
    const saveStatus = document.getElementById('save-status');

    const deckEntriesEl = document.getElementById('deck-entries');
    const deckSideboardEl = document.getElementById('deck-sideboard');

    const legalityStatus = document.getElementById('legality-status');
    const legalityCounters = document.getElementById('legality-counters');
    const legalityMessages = document.getElementById('legality-messages');

    const TYPE_LABELS = {
        personagem: 'Personagem',
        item: 'Item',
        energia: 'Energia',
    };

    const SECTION_LABELS = {
        personagem: 'Personagens',
        item: 'Itens',
        energia: 'Fontes de Energia',
    };

    // Estado do builder em memória — persistido só ao clicar "Salvar deck".
    let state = {
        file: null,
        isNew: true,
        collection: '',
        meta: { id: '', slug: '', name: '', description: '', format: 'standard' },
        strategy: { archetype: null, winCondition: null, keyMechanics: [], coreCombo: null, tempo: null },
        entries: [],
        sideboard: [],
    };

    let collections = [];
    let poolCards = [];
    let currentPoolType = '';
    let cardIndex = new Map();

    init();

    async function init() {
        const collectionsData = await fetchJson('deck-builder-api.php?action=collections');
        collections = collectionsData.collections || [];
        populateSelect(poolCollection, collections);
        populateSelect(deckCollectionSelect, collections);

        await loadPool(poolCollection.value);
        await refreshDeckList();

        poolCollection.addEventListener('change', () => loadPool(poolCollection.value));
        poolSearch.addEventListener('input', renderPool);
        poolWorld.addEventListener('change', renderPool);
        poolTypeButtons.addEventListener('click', (ev) => {
            const btn = ev.target.closest('.type-buttons__btn');
            if (!btn) return;
            currentPoolType = btn.dataset.type;
            for (const b of poolTypeButtons.querySelectorAll('.type-buttons__btn')) {
                b.classList.toggle('type-buttons__btn--active', b === btn);
            }
            renderPool();
        });

        deckSelect.addEventListener('change', () => {
            if (deckSelect.value) {
                loadDeck(deckSelect.value);
            } else {
                startNewDeck();
            }
        });
        newDeckBtn.addEventListener('click', () => {
            deckSelect.value = '';
            startNewDeck();
        });

        deckNameInput.addEventListener('input', () => {
            state.meta.name = deckNameInput.value;
            if (state.isNew) {
                deckSlugInput.value = slugify(deckNameInput.value);
                state.meta.slug = deckSlugInput.value;
            }
        });
        deckSlugInput.addEventListener('input', () => {
            state.meta.slug = deckSlugInput.value;
        });
        deckDescriptionInput.addEventListener('input', () => {
            state.meta.description = deckDescriptionInput.value;
        });

        saveDeckBtn.addEventListener('click', saveDeck);

        startNewDeck();
    }

    function populateSelect(select, values) {
        select.innerHTML = '';
        for (const value of values) {
            const opt = document.createElement('option');
            opt.value = value;
            opt.textContent = value;
            select.appendChild(opt);
        }
    }

    async function loadPool(collection) {
        if (!collection) {
            poolCards = [];
            renderPool();
            return;
        }
        const data = await fetchJson('deck-builder-api.php?action=cards&collection=' + encodeURIComponent(collection));
        poolCards = data.cards || [];
        cardIndex = new Map(poolCards.map((c) => [c._id, c]));
        populateWorldFilter();
        renderPool();
    }

    function populateWorldFilter() {
        const worlds = new Set();
        for (const card of poolCards) {
            const w = card.worldType || card.worldAffinity;
            if (w) worlds.add(w);
        }
        const sorted = Array.from(worlds).sort((a, b) => a.localeCompare(b, 'pt-BR'));
        poolWorld.innerHTML = '<option value="">Todas</option>';
        for (const w of sorted) {
            const opt = document.createElement('option');
            opt.value = w;
            opt.textContent = w;
            poolWorld.appendChild(opt);
        }
    }

    function renderPool() {
        const search = poolSearch.value.trim().toLocaleLowerCase('pt-BR');
        const world = poolWorld.value;

        const filtered = poolCards.filter((card) => {
            if (currentPoolType && card._type !== currentPoolType) return false;
            if (world && (card.worldType || card.worldAffinity) !== world) return false;
            if (search && !(card.name || '').toLocaleLowerCase('pt-BR').includes(search)) return false;
            return true;
        });

        filtered.sort((a, b) => (a.name || '').localeCompare(b.name || '', 'pt-BR'));

        poolGrid.innerHTML = '';
        for (const card of filtered) {
            poolGrid.appendChild(renderPoolCard(card));
        }
    }

    function renderPoolCard(card) {
        const row = document.createElement('div');
        row.className = 'pool-card';

        const info = document.createElement('div');
        info.className = 'pool-card__info';

        const name = document.createElement('div');
        name.className = 'pool-card__name';
        name.textContent = card.name || card._id;
        info.appendChild(name);

        const tags = document.createElement('div');
        tags.className = 'pool-card__tags';
        const tagParts = [TYPE_LABELS[card._type] || card._type];
        const world = card.worldType || card.worldAffinity;
        if (world) tagParts.push(world);
        if (card._type === 'energia' && card.type) tagParts.push(card.type);
        if (card.isLegendary) tagParts.push('Lendária');
        if (card.isEpic) tagParts.push('Épica');
        tags.textContent = tagParts.join(' · ');
        info.appendChild(tags);

        row.appendChild(info);

        const actions = document.createElement('div');
        actions.className = 'pool-card__actions';

        const addBtn = document.createElement('button');
        addBtn.type = 'button';
        addBtn.className = 'pool-card__btn';
        addBtn.textContent = '+';
        addBtn.title = 'Adicionar ao deck';
        addBtn.addEventListener('click', () => addCard(card, 'entries'));
        actions.appendChild(addBtn);

        const sideBtn = document.createElement('button');
        sideBtn.type = 'button';
        sideBtn.className = 'pool-card__btn pool-card__btn--sideboard';
        sideBtn.textContent = 'SB';
        sideBtn.title = 'Adicionar ao sideboard';
        sideBtn.addEventListener('click', () => addCard(card, 'sideboard'));
        actions.appendChild(sideBtn);

        row.appendChild(actions);

        return row;
    }

    function addCard(card, listName) {
        const list = state[listName];
        const existing = list.find((e) => e.cardId === card._id);
        if (existing) {
            existing.quantity += 1;
        } else {
            const entry = { cardId: card._id, quantity: 1 };
            if (listName === 'entries') {
                entry.suggestedRole = null;
                entry.suggestedPlay = null;
                entry.designNotes = null;
            }
            list.push(entry);
        }
        renderDeck();
    }

    function changeQty(listName, cardId, delta) {
        const list = state[listName];
        const entry = list.find((e) => e.cardId === cardId);
        if (!entry) return;
        entry.quantity += delta;
        if (entry.quantity <= 0) {
            state[listName] = list.filter((e) => e.cardId !== cardId);
        }
        renderDeck();
    }

    function removeCard(listName, cardId) {
        state[listName] = state[listName].filter((e) => e.cardId !== cardId);
        renderDeck();
    }

    function renderDeck() {
        deckEntriesEl.innerHTML = '';
        deckSideboardEl.innerHTML = '';

        const grouped = groupByType(state.entries);
        for (const [type, entries] of grouped) {
            const groupTitle = document.createElement('div');
            groupTitle.className = 'decklist__group-title';
            groupTitle.textContent = SECTION_LABELS[type] || type;
            deckEntriesEl.appendChild(groupTitle);

            for (const entry of entries) {
                deckEntriesEl.appendChild(renderDeckRow(entry, 'entries'));
            }
        }

        for (const entry of state.sideboard) {
            deckSideboardEl.appendChild(renderDeckRow(entry, 'sideboard'));
        }

        renderLegality();
    }

    function groupByType(entries) {
        const map = new Map();
        for (const entry of entries) {
            const card = cardIndex.get(entry.cardId);
            const type = card ? card._type : 'outro';
            if (!map.has(type)) map.set(type, []);
            map.get(type).push(entry);
        }
        return map;
    }

    function renderDeckRow(entry, listName) {
        const card = cardIndex.get(entry.cardId);
        const row = document.createElement('div');
        row.className = 'deck-row';

        const name = document.createElement('div');
        name.className = 'deck-row__name';
        name.textContent = card ? card.name : entry.cardId;
        name.title = name.textContent;
        row.appendChild(name);

        if (listName === 'entries' && card && card._type === 'personagem') {
            const roleSelect = document.createElement('select');
            roleSelect.className = 'deck-row__role';
            for (const [value, label] of [['', '—'], ['leader', 'Líder'], ['team', 'Time']]) {
                const opt = document.createElement('option');
                opt.value = value;
                opt.textContent = label;
                if ((entry.suggestedRole || '') === value) opt.selected = true;
                roleSelect.appendChild(opt);
            }
            roleSelect.addEventListener('change', () => {
                entry.suggestedRole = roleSelect.value || null;
            });
            row.appendChild(roleSelect);
        }

        const qty = document.createElement('div');
        qty.className = 'deck-row__qty';

        const minusBtn = document.createElement('button');
        minusBtn.type = 'button';
        minusBtn.className = 'deck-row__qty-btn';
        minusBtn.textContent = '-';
        minusBtn.addEventListener('click', () => changeQty(listName, entry.cardId, -1));
        qty.appendChild(minusBtn);

        const qtyValue = document.createElement('span');
        qtyValue.className = 'deck-row__qty-value';
        qtyValue.textContent = String(entry.quantity);
        qty.appendChild(qtyValue);

        const plusBtn = document.createElement('button');
        plusBtn.type = 'button';
        plusBtn.className = 'deck-row__qty-btn';
        plusBtn.textContent = '+';
        plusBtn.addEventListener('click', () => changeQty(listName, entry.cardId, 1));
        qty.appendChild(plusBtn);

        row.appendChild(qty);

        const removeBtn = document.createElement('button');
        removeBtn.type = 'button';
        removeBtn.className = 'deck-row__remove';
        removeBtn.textContent = '×';
        removeBtn.title = 'Remover';
        removeBtn.addEventListener('click', () => removeCard(listName, entry.cardId));
        row.appendChild(removeBtn);

        return row;
    }

    // Regras de montagem — rules.md > "Montagem do Deck".
    function validateDeck() {
        const errors = [];
        const warnings = [];

        let total = 0;
        let personagens = 0;
        let itens = 0;
        let energiasBasicas = 0;
        let energiasAvancadas = 0;
        const copyIssues = [];

        for (const entry of state.entries) {
            const card = cardIndex.get(entry.cardId);
            total += entry.quantity;

            if (!card) continue;

            if (card._type === 'personagem') {
                personagens += entry.quantity;
                if (entry.quantity > 4) copyIssues.push(`${card.name}: ${entry.quantity} cópias (máx. 4 para Personagem)`);
            } else if (card._type === 'item') {
                itens += entry.quantity;
                if (entry.quantity > 4) copyIssues.push(`${card.name}: ${entry.quantity} cópias (máx. 4 para Item)`);
            } else if (card._type === 'energia') {
                if (card.type === 'Básica') {
                    energiasBasicas += entry.quantity;
                } else {
                    energiasAvancadas += entry.quantity;
                    if (entry.quantity > 4) copyIssues.push(`${card.name}: ${entry.quantity} cópias (máx. 4 para Energia avançada)`);
                }
            }

            if ((card.isLegendary || card.isEpic) && entry.quantity > 1) {
                copyIssues.push(`${card.name}: ${entry.quantity} cópias (Lendária/Épica permite só 1)`);
            }
        }

        const energiasTotal = energiasBasicas + energiasAvancadas;

        if (total > 60) errors.push(`Deck principal tem ${total} cartas — máximo é 60.`);
        if (personagens < 12) errors.push(`Apenas ${personagens} Personagens no deck — mínimo é 12.`);
        if (energiasTotal < 12) errors.push(`Apenas ${energiasTotal} Fontes de Energia no deck — mínimo é 12.`);
        for (const issue of copyIssues) errors.push(issue);

        const sideboardTotal = state.sideboard.reduce((sum, e) => sum + e.quantity, 0);
        if (sideboardTotal > 15) errors.push(`Sideboard tem ${sideboardTotal} cartas — máximo é 15.`);

        return {
            errors,
            warnings,
            counters: {
                'Total (deck principal)': { value: total, bad: total > 60 },
                'Personagens': { value: personagens, bad: personagens < 12 },
                'Itens': { value: itens, bad: false },
                'Energias básicas': { value: energiasBasicas, bad: false },
                'Energias avançadas': { value: energiasAvancadas, bad: false },
                'Energias (total)': { value: energiasTotal, bad: energiasTotal < 12 },
                'Sideboard': { value: sideboardTotal, bad: sideboardTotal > 15 },
            },
        };
    }

    function renderLegality() {
        const result = validateDeck();

        legalityCounters.innerHTML = '';
        for (const [label, info] of Object.entries(result.counters)) {
            const dt = document.createElement('dt');
            dt.textContent = label;
            legalityCounters.appendChild(dt);

            const dd = document.createElement('dd');
            dd.textContent = String(info.value);
            if (info.bad) dd.classList.add('legality-counters__value--bad');
            legalityCounters.appendChild(dd);
        }

        legalityMessages.innerHTML = '';
        for (const msg of result.errors) {
            const el = document.createElement('div');
            el.className = 'legality-message';
            el.textContent = msg;
            legalityMessages.appendChild(el);
        }
        for (const msg of result.warnings) {
            const el = document.createElement('div');
            el.className = 'legality-message legality-message--warning';
            el.textContent = msg;
            legalityMessages.appendChild(el);
        }

        if (result.errors.length === 0) {
            legalityStatus.textContent = '✓ Deck legal';
            legalityStatus.className = 'legality-status legality-status--ok';
        } else {
            legalityStatus.textContent = `${result.errors.length} problema(s) de legalidade`;
            legalityStatus.className = 'legality-status legality-status--error';
        }
    }

    async function refreshDeckList() {
        const data = await fetchJson('deck-builder-api.php?action=list');
        const decks = data.decks || [];
        const previous = deckSelect.value;
        deckSelect.innerHTML = '<option value="">— Novo deck —</option>';
        for (const deck of decks) {
            const opt = document.createElement('option');
            opt.value = deck.file;
            opt.textContent = deck.title;
            deckSelect.appendChild(opt);
        }
        if (previous && Array.from(deckSelect.options).some((o) => o.value === previous)) {
            deckSelect.value = previous;
        }
    }

    function startNewDeck() {
        state = {
            file: null,
            isNew: true,
            collection: deckCollectionSelect.value || collections[0] || '',
            meta: { id: '', slug: '', name: '', description: '', format: 'standard' },
            strategy: { archetype: null, winCondition: null, keyMechanics: [], coreCombo: null, tempo: null },
            entries: [],
            sideboard: [],
        };

        newDeckCollectionField.hidden = false;
        deckSlugField.hidden = false;
        deckNameInput.value = '';
        deckSlugInput.value = '';
        deckDescriptionInput.value = '';
        saveStatus.textContent = '';
        saveStatus.className = 'save-status';

        renderDeck();
    }

    async function loadDeck(file) {
        saveStatus.textContent = 'Carregando...';
        saveStatus.className = 'save-status';

        const data = await fetchJson('deck-builder-api.php?action=deck&file=' + encodeURIComponent(file));
        const deck = data.deck;
        const collection = file.split('/')[0];

        if (poolCollection.value !== collection && collections.includes(collection)) {
            poolCollection.value = collection;
            await loadPool(collection);
        }

        const slug = file.split('/').pop().replace(/\.json$/, '');
        state = {
            file,
            isNew: false,
            collection,
            meta: {
                id: slug,
                slug,
                name: deck.title || '',
                description: deck.description || '',
                format: 'standard',
            },
            strategy: deck.strategy || { archetype: null, winCondition: null, keyMechanics: [], coreCombo: null, tempo: null },
            entries: (deck.entries || []).map((e) => ({
                cardId: e.cardId,
                quantity: e.quantity,
                suggestedRole: e.suggestedRole || null,
                suggestedPlay: e.suggestedPlay || null,
                designNotes: e.designNotes || null,
            })),
            sideboard: (deck.sideboard || []).map((e) => ({ cardId: e.cardId, quantity: e.quantity })),
        };

        newDeckCollectionField.hidden = true;
        deckSlugField.hidden = true;
        deckNameInput.value = state.meta.name;
        deckDescriptionInput.value = state.meta.description;
        saveStatus.textContent = '';
        saveStatus.className = 'save-status';

        renderDeck();
    }

    async function saveDeck() {
        if (!state.meta.name.trim()) {
            saveStatus.textContent = 'Informe um nome para o deck.';
            saveStatus.className = 'save-status save-status--error';
            return;
        }

        saveStatus.textContent = 'Salvando...';
        saveStatus.className = 'save-status';

        try {
            if (state.isNew) {
                if (!state.meta.slug.trim()) {
                    throw new Error('Informe um slug para o deck.');
                }
                if (!deckCollectionSelect.value) {
                    throw new Error('Selecione uma coleção para o deck.');
                }

                const createPayload = {
                    collection: deckCollectionSelect.value,
                    meta: {
                        id: state.meta.slug,
                        slug: state.meta.slug,
                        name: state.meta.name,
                        description: state.meta.description || null,
                        format: 'standard',
                    },
                };
                console.debug('[deck-builder] action=create payload', createPayload);

                const created = await fetchJson('deck-builder-api.php?action=create', {
                    method: 'POST',
                    body: JSON.stringify(createPayload),
                });
                state.file = created.file;
                state.isNew = false;
            }

            await fetchJson('deck-builder-api.php?action=save', {
                method: 'POST',
                body: JSON.stringify({
                    file: state.file,
                    deck: {
                        id: state.meta.slug,
                        slug: state.meta.slug,
                        name: state.meta.name,
                        description: state.meta.description || null,
                        format: 'standard',
                        strategy: state.strategy,
                        entries: state.entries,
                        sideboard: state.sideboard,
                    },
                }),
            });

            saveStatus.textContent = '✓ Salvo';
            saveStatus.className = 'save-status save-status--ok';
            deckSlugField.hidden = true;
            newDeckCollectionField.hidden = true;

            await refreshDeckList();
            deckSelect.value = state.file;
        } catch (err) {
            console.error('[deck-builder] falha ao salvar', err);
            saveStatus.textContent = 'Erro: ' + err.message;
            saveStatus.className = 'save-status save-status--error';
        }
    }

    function slugify(text) {
        const transliterated = text
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '');
        return transliterated
            .toLowerCase()
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/^-+|-+$/g, '');
    }

    async function fetchJson(url, options) {
        const res = await fetch(url, options);
        const data = await res.json().catch(() => ({}));
        if (!res.ok) {
            console.error('[deck-builder] ' + res.status + ' em ' + url, data);
            throw new Error(data.error || (res.status + ' ao buscar ' + url));
        }
        return data;
    }
})();
