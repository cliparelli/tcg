(function () {
    'use strict';

    const collectionSelect = document.getElementById('collection-select');
    const typeButtons = document.getElementById('type-buttons');
    const cardSelect = document.getElementById('card-select');
    const searchInput = document.getElementById('search');
    const sortSelect = document.getElementById('sort-select');
    const cardList = document.getElementById('card-list');
    const cardPreview = document.getElementById('card-preview');
    const cardRaw = document.getElementById('card-raw');
    const shareBtn = document.getElementById('share-btn');

    let collectionsData = [];
    let currentCards = [];
    let filteredCards = [];
    let activeCardId = null;
    let currentCollection = '';
    let currentType = '';

    const TYPE_ASSET_MODEL = {
        personagem: 'card',
        item: 'land',
        energia: 'land',
    };

    const RARITY_INITIAL = {
        comum: 'C',
        incomum: 'I',
        rara: 'R',
        'super-rara': 'S',
        'ultra-rara': 'U',
    };

    const TYPE_COLOR_SLUG = {
        magia: 'magia',
        tecsci: 'tec',
        'físico': 'fisico',
        fisico: 'fisico',
        divino: 'divino',
        'cósmico': 'cosmico',
        cosmico: 'cosmico',
        natureza: 'natureza',
        vida: 'vida',
        morte: 'morte',
        elemental: 'elemental',
        mental: 'mental',
        'poder energético': 'energetico',
        'poder energetico': 'energetico',
        energético: 'energetico',
        energetico: 'energetico',
        fera: 'fera',
    };

    init();

    async function init() {
        const data = await fetchJson('api.php?action=list-files');
        collectionsData = data.collections || [];
        populateCollectionSelect(collectionsData);
        collectionSelect.addEventListener('change', () => selectCollection(collectionSelect.value));
        typeButtons.addEventListener('click', (ev) => {
            const btn = ev.target.closest('.type-buttons__btn');
            if (btn && !btn.disabled) {
                selectType(btn.dataset.type);
            }
        });
        searchInput.addEventListener('input', applyFilter);
        sortSelect.addEventListener('change', applyFilter);
        cardSelect.addEventListener('change', () => {
            const card = filteredCards.find((c) => c._id === cardSelect.value);
            if (card) {
                selectCard(card);
            }
        });
        shareBtn.addEventListener('click', shareCard);

        if (collectionSelect.options.length > 0) {
            const queryParams = window.appQueryParams || {};
            if (queryParams.collection) {
                collectionSelect.value = queryParams.collection;
                await selectCollection(queryParams.collection, queryParams.type);
                if (queryParams.card) {
                    await new Promise(r => setTimeout(r, 100));
                    const card = currentCards.find((c) => c._id === queryParams.card);
                    if (card) {
                        selectCard(card);
                    }
                }
            } else {
                await selectCollection(collectionSelect.value);
            }
        }
    }

    function populateCollectionSelect(collections) {
        collectionSelect.innerHTML = '';
        for (const collection of collections) {
            const opt = document.createElement('option');
            opt.value = collection.name;
            opt.textContent = collection.name;
            collectionSelect.appendChild(opt);
        }
    }

    function findCollection(name) {
        return collectionsData.find((c) => c.name === name) || null;
    }

    function updateTypeButtons(collection) {
        const buttons = typeButtons.querySelectorAll('.type-buttons__btn');
        for (const btn of buttons) {
            const file = collection && collection.files.find((f) => f.type === btn.dataset.type);
            btn.disabled = !file;
            btn.classList.toggle('type-buttons__btn--active', btn.dataset.type === currentType);
            btn.title = file ? `${file.count} carta(s)` : 'Nenhuma carta neste tipo';
        }
    }

    async function selectCollection(name, preferredType) {
        currentCollection = name || '';
        const collection = findCollection(currentCollection);
        currentType = '';
        updateTypeButtons(collection);

        if (!collection) {
            currentCards = [];
            activeCardId = null;
            applyFilter();
            return;
        }

        const wantedType = preferredType && collection.files.some((f) => f.type === preferredType)
            ? preferredType
            : (collection.files[0] && collection.files[0].type);

        if (wantedType) {
            await selectType(wantedType);
        }
    }

    async function selectType(type) {
        const collection = findCollection(currentCollection);
        const file = collection && collection.files.find((f) => f.type === type);
        if (!file) {
            return;
        }

        currentType = type;
        updateTypeButtons(collection);
        await loadFile(file.relPath);
    }

    async function loadFile(relPath) {
        if (!relPath) {
            return;
        }
        cardList.innerHTML = '<li>Carregando...</li>';
        const data = await fetchJson('api.php?action=cards&file=' + encodeURIComponent(relPath));
        currentCards = data.cards || [];
        activeCardId = null;
        applyFilter();
    }

    function applyFilter() {
        const term = searchInput.value.trim().toLowerCase();
        filteredCards = term
            ? currentCards.filter((c) => (c.name || '').toLowerCase().includes(term))
            : currentCards.slice();
        sortCards(filteredCards);
        renderList();
    }

    function sortCards(cards) {
        const mode = sortSelect.value;
        const collator = new Intl.Collator('pt-BR', { sensitivity: 'base' });

        cards.sort((a, b) => {
            if (mode === 'tipo') {
                const diff = collator.compare(cardAffinity(a), cardAffinity(b));
                if (diff !== 0) return diff;
            } else if (mode === 'faccao') {
                const diff = collator.compare(extractFaction(a), extractFaction(b));
                if (diff !== 0) return diff;
            }
            return collator.compare(a.name || '', b.name || '');
        });
    }

    /** Afinidade elemental: worldType em personagens, worldAffinity em itens/energias. */
    function cardAffinity(card) {
        const value = card._type === 'personagem' ? card.worldType : card.worldAffinity;
        return (value || '').trim();
    }

    /** Facção/tribo: category em personagens e itens, type em energias. */
    function extractFaction(card) {
        const value = card._type === 'energia' ? card.type : card.category;
        return (value || '').trim();
    }

    function renderList() {
        cardList.innerHTML = '';
        cardSelect.innerHTML = '';

        if (filteredCards.length === 0) {
            cardList.innerHTML = '<li>Nenhuma carta encontrada.</li>';
            const opt = document.createElement('option');
            opt.textContent = 'Nenhuma carta encontrada';
            cardSelect.appendChild(opt);
            return;
        }

        for (const card of filteredCards) {
            const li = document.createElement('li');
            li.textContent = card.name || '(sem nome)';
            if (card._id === activeCardId) {
                li.classList.add('active');
            }

            const tag = document.createElement('span');
            tag.className = 'tag';
            tag.textContent = sortSelect.value === 'faccao'
                ? (extractFaction(card) || cardAffinity(card))
                : (cardAffinity(card) || extractFaction(card));
            li.appendChild(tag);

            li.addEventListener('click', () => selectCard(card));
            cardList.appendChild(li);

            const opt = document.createElement('option');
            opt.value = card._id;
            opt.textContent = card.name || '(sem nome)';
            if (card._id === activeCardId) {
                opt.selected = true;
            }
            cardSelect.appendChild(opt);
        }
    }

    function selectCard(card) {
        activeCardId = card._id;
        renderList();
        renderPreview(card);
        renderRaw(card);
        shareBtn.hidden = false;
    }

    function shareCard() {
        const collection = currentCollection;
        const cardId = activeCardId;

        if (!collection || !cardId) {
            return;
        }

        const url = new URL(window.location);
        url.searchParams.set('collection', collection);
        url.searchParams.set('type', currentType);
        url.searchParams.set('card', cardId);

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

    function renderPreview(card) {
        const model = TYPE_ASSET_MODEL[card._type] || 'card';
        const frameSrc = 'asset.php?model=' + model + '&t=' + encodeURIComponent(card._type);

        cardPreview.innerHTML = '';

        const art = document.createElement('div');
        art.className = 'card-preview__art';
        const artSrc = resolveArtSrc(card);
        if (artSrc) {
            const img = document.createElement('img');
            img.src = artSrc;
            img.alt = '';
            img.addEventListener('error', () => {
                img.remove();
                art.textContent = 'Sem arte';
            });
            art.appendChild(img);
        } else {
            art.textContent = 'Sem arte';
        }
        cardPreview.appendChild(art);

        if (card._type === 'personagem') {
            const stats = card.stats || {};

            const heart = document.createElement('div');
            heart.className = 'card-preview__heart';
            heart.textContent = statValue(stats.life);
            cardPreview.appendChild(heart);

            const shield = document.createElement('div');
            shield.className = 'card-preview__shield';
            shield.textContent = statValue(stats.defense);
            cardPreview.appendChild(shield);
        }

        const title = document.createElement('div');
        title.className = 'card-preview__title';
        title.textContent = card.name || '';
        cardPreview.appendChild(title);

        const elementalType = card._type !== 'item' ? cardAffinity(card) : '';
        if (elementalType) {
            const typeBadge = document.createElement('div');
            typeBadge.className = 'card-preview__type-badge';
            const typeDot = document.createElement('span');
            typeDot.className = `card-preview__dot ${typeColorClass(elementalType)}`;
            typeDot.title = elementalType;
            typeBadge.appendChild(typeDot);
            cardPreview.appendChild(typeBadge);
        }

        const classification = document.createElement('div');
        classification.className = 'card-preview__classification';
        const classificationText = card._type === 'energia'
            ? (card.type || '').trim()
            : (card.category || '').trim();
        const rarityInitial = rarityInitialFor(card.rarity);
        classification.textContent = rarityInitial
            ? `${classificationText} ${rarityInitial}`
            : classificationText;
        cardPreview.appendChild(classification);

        const text = document.createElement('div');
        text.className = 'card-preview__text';
        text.innerHTML = buildTextBlocks(card);
        cardPreview.appendChild(text);

        if (card._type === 'personagem' && (card.resistance || card.weakness)) {
            const footer = document.createElement('div');
            footer.className = 'card-preview__footer';
            footer.innerHTML = `
                <span class="card-preview__dot ${typeColorClass(card.resistance)}" title="Resistência: ${escapeHtml(card.resistance || '')}"></span>
                <span class="card-preview__dot ${typeColorClass(card.weakness)}" title="Fraqueza: ${escapeHtml(card.weakness || '')}"></span>
            `;
            cardPreview.appendChild(footer);
        }

        const frame = document.createElement('img');
        frame.className = 'card-preview__frame';
        frame.src = frameSrc;
        frame.alt = 'moldura';
        cardPreview.appendChild(frame);
    }

    function extractTypeName(tipo) {
        const value = (tipo || '').trim();
        if (!value) {
            return '';
        }
        const parts = value.split('-');
        return parts.length > 1 ? parts.slice(1).join('-').trim() : value;
    }

    function typeColorClass(tipo) {
        const key = (tipo || '').trim().toLowerCase();
        const slug = TYPE_COLOR_SLUG[key];
        return slug ? `card-preview__dot--${slug}` : 'card-preview__dot--neutro';
    }

    function rarityInitialFor(raridade) {
        const key = (raridade || '').trim().toLowerCase();
        return RARITY_INITIAL[key] || '';
    }

    function statValue(value) {
        return value === null || value === undefined || value === '' ? '-' : String(value);
    }

    function costToSymbols(cost, tipo) {
        const n = parseInt(cost, 10);
        if (!n || n <= 0) {
            return '';
        }
        const dot = `<span class="card-preview__dot card-preview__dot--cost ${typeColorClass(tipo)}" title="${escapeHtml(extractTypeName(tipo))}"></span>`;
        return dot.repeat(n);
    }

    function splitTitledDescription(value) {
        const text = (value || '').trim();
        const match = text.match(/^\*\*(.+?)\*\*\s*—\s*([\s\S]*)$/);
        if (match) {
            return { title: match[1].trim(), body: match[2].trim() };
        }
        return { title: '', body: text };
    }

    function resolveArtSrc(card) {
        const assetRef = (card.assetRef || '').trim();
        if (!assetRef) {
            return '';
        }

        const file = currentCollection + '/card-art/' + assetRef;
        return 'asset.php?expansion=1&file=' + encodeURIComponent(file);
    }

    function buildTextBlocks(card) {
        const blocks = [];

        if (card._type === 'personagem') {
            addActionBlock(blocks, card['Custo P'], card.Tipo, card['Descrição P']);
            addActionBlock(blocks, card['Custo A'], card.Tipo, card['Descrição A']);

            addBlock(blocks, 'Sidekick', card['Descrição Sidekick']);
            addBlock(blocks, 'Líder', card['Descrição Líder']);
            addBlock(blocks, 'Flanquear', card['Descrição Flanquear']);
            addBlock(blocks, 'Ataque', card['Descrição Ataque']);
            addBlock(blocks, null, card['Texto Final 2']);
        } else if (card._type === 'item') {
            addBlock(blocks, null, card.cardText);
            if (card.flavorText) {
                blocks.push(`<p class="card-preview__flavor">${escapeHtml(card.flavorText)}</p>`);
            }
        } else if (card._type === 'energia') {
            const meta = card.energyGenerated ? `Energia ${card.energyGenerated}` : '';
            if (meta) blocks.push(`<h4>${escapeHtml(meta)}</h4>`);
            addBlock(blocks, null, card.cardText);
            if (card.flavorText) {
                blocks.push(`<p class="card-preview__flavor">${escapeHtml(card.flavorText)}</p>`);
            }
        }

        return blocks.join('') || '<p>Sem texto de efeito.</p>';
    }

    function addBlock(blocks, label, value) {
        if (!value || isUrl(value)) {
            return;
        }
        if (label) {
            blocks.push(`<h4>${escapeHtml(label)}</h4>`);
        }
        blocks.push(`<p>${escapeHtml(value)}</p>`);
    }

    function addActionBlock(blocks, cost, tipo, description) {
        if (!description || isUrl(description)) {
            return;
        }
        const { title, body } = splitTitledDescription(description);
        const isPassive = !parseInt(cost, 10);
        const symbols = costToSymbols(cost, tipo);

        const headingParts = [];
        if (isPassive) {
            headingParts.push('<em>Passiva</em>');
        }
        if (symbols) {
            headingParts.push(`<span class="card-preview__cost">${symbols}</span>`);
        }
        if (title) {
            headingParts.push(`<strong>${escapeHtml(title)}</strong>`);
        }
        const heading = headingParts.join(' ');

        blocks.push('<div class="card-preview__action">');
        if (heading) {
            blocks.push(`<h4>${heading}</h4>`);
        }
        blocks.push(`<p>${escapeHtml(body)}</p>`);
        blocks.push('</div>');
    }

    function renderRaw(card) {
        cardRaw.innerHTML = '';

        const table = document.createElement('table');
        table.className = 'card-raw__table';
        const tbody = document.createElement('tbody');

        for (const [key, value] of Object.entries(card)) {
            if (key.startsWith('_')) {
                continue;
            }
            const tr = document.createElement('tr');

            const th = document.createElement('td');
            th.textContent = key;
            tr.appendChild(th);

            const td = document.createElement('td');
            if (value !== null && typeof value === 'object') {
                const pre = document.createElement('pre');
                pre.className = 'card-raw__json';
                pre.textContent = JSON.stringify(value, null, 2);
                td.appendChild(pre);
            } else {
                td.textContent = value ?? '';
            }
            tr.appendChild(td);

            tbody.appendChild(tr);
        }

        table.appendChild(tbody);
        cardRaw.appendChild(table);
    }

    function isUrl(value) {
        return typeof value === 'string' && /^https?:\/\//i.test(value.trim());
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
