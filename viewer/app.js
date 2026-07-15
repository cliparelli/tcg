(function () {
    'use strict';

    const fileSelect = document.getElementById('file-select');
    const searchInput = document.getElementById('search');
    const cardList = document.getElementById('card-list');
    const cardPreview = document.getElementById('card-preview');
    const cardRaw = document.getElementById('card-raw');

    let currentCards = [];
    let filteredCards = [];
    let activeCardId = null;
    let currentCollection = '';

    const TYPE_ASSET_MODEL = {
        personagem: 'card',
        item: 'card',
        energia: 'land',
    };

    init();

    async function init() {
        const data = await fetchJson('api.php?action=list-files');
        populateFileSelect(data.collections || []);
        fileSelect.addEventListener('change', () => loadFile(fileSelect.value));
        searchInput.addEventListener('input', applyFilter);

        if (fileSelect.options.length > 0) {
            loadFile(fileSelect.value);
        }
    }

    function populateFileSelect(collections) {
        fileSelect.innerHTML = '';
        for (const collection of collections) {
            const group = document.createElement('optgroup');
            group.label = collection.name;
            for (const file of collection.files) {
                const opt = document.createElement('option');
                opt.value = file.relPath;
                opt.dataset.type = file.type;
                opt.textContent = `${file.relPath} (${file.count})`;
                group.appendChild(opt);
            }
            fileSelect.appendChild(group);
        }
    }

    async function loadFile(relPath) {
        if (!relPath) {
            return;
        }
        currentCollection = relPath.split('/')[0] || '';
        cardList.innerHTML = '<li>Carregando...</li>';
        const data = await fetchJson('api.php?action=cards&file=' + encodeURIComponent(relPath));
        currentCards = data.cards || [];
        activeCardId = null;
        applyFilter();
    }

    function applyFilter() {
        const term = searchInput.value.trim().toLowerCase();
        filteredCards = term
            ? currentCards.filter((c) => (c.Nome || '').toLowerCase().includes(term))
            : currentCards;
        renderList();
    }

    function renderList() {
        cardList.innerHTML = '';
        if (filteredCards.length === 0) {
            cardList.innerHTML = '<li>Nenhuma carta encontrada.</li>';
            return;
        }

        for (const card of filteredCards) {
            const li = document.createElement('li');
            li.textContent = card.Nome || '(sem nome)';
            if (card._id === activeCardId) {
                li.classList.add('active');
            }

            const tag = document.createElement('span');
            tag.className = 'tag';
            tag.textContent = card.Tipo || card.Classificação || '';
            li.appendChild(tag);

            li.addEventListener('click', () => selectCard(card));
            cardList.appendChild(li);
        }
    }

    function selectCard(card) {
        activeCardId = card._id;
        renderList();
        renderPreview(card);
        renderRaw(card);
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
            img.alt = card.Nome || '';
            art.appendChild(img);
        } else {
            art.textContent = 'Sem arte';
        }
        cardPreview.appendChild(art);

        const title = document.createElement('div');
        title.className = 'card-preview__title';
        title.textContent = card.Nome || '';
        cardPreview.appendChild(title);

        if (card._type !== 'energia') {
            const status = document.createElement('div');
            status.className = 'card-preview__status';
            status.innerHTML = `
                <span>❤ ${escapeHtml(card.Vida || '-')}</span>
                <span>🛡 ${escapeHtml(card.Defesa || '-')}</span>
            `;
            cardPreview.appendChild(status);
        }

        const text = document.createElement('div');
        text.className = 'card-preview__text';
        text.innerHTML = buildTextBlocks(card);
        cardPreview.appendChild(text);

        const frame = document.createElement('img');
        frame.className = 'card-preview__frame';
        frame.src = frameSrc;
        frame.alt = 'moldura';
        cardPreview.appendChild(frame);
    }

    function resolveArtSrc(card) {
        const arte = (card.Arte || '').trim();
        if (arte) {
            return isUrl(arte)
                ? arte
                : 'asset.php?art=1&collection=' + encodeURIComponent(currentCollection) + '&file=' + encodeURIComponent(arte);
        }

        const imageUrl = card.IMAGEM || (card['Texto Final 1'] && isUrl(card['Texto Final 1']) ? card['Texto Final 1'] : '');
        return imageUrl && isUrl(imageUrl) ? imageUrl : '';
    }

    function buildTextBlocks(card) {
        const blocks = [];

        if (card._type === 'personagem') {
            const meta = [card.Classificação, card.Tipo, card.Universo].filter(Boolean).join(' · ');
            if (meta) blocks.push(`<h4>${escapeHtml(meta)}</h4>`);

            addBlock(blocks, 'Sidekick', card['Descrição Sidekick']);
            addBlock(blocks, 'Líder', card['Descrição Líder']);
            addBlock(blocks, 'Flanquear', card['Descrição Flanquear']);
            addBlock(blocks, 'Ataque', card['Descrição Ataque']);
            addBlock(blocks, null, card['Texto Final 2']);
        } else if (card._type === 'item') {
            const meta = [card.Tipo, card.Dinâmica, card.Universo].filter(Boolean).join(' · ');
            if (meta) blocks.push(`<h4>${escapeHtml(meta)}</h4>`);
            addBlock(blocks, null, card['Descrição']);
        } else if (card._type === 'energia') {
            const meta = [card.Tipo, card.Energia ? `Energia ${card.Energia}` : ''].filter(Boolean).join(' · ');
            if (meta) blocks.push(`<h4>${escapeHtml(meta)}</h4>`);
            addBlock(blocks, null, card['Descrição']);
            if (card['Texto Final'] && !isUrl(card['Texto Final'])) {
                blocks.push(`<p class="card-preview__flavor">${escapeHtml(card['Texto Final'])}</p>`);
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

    function renderRaw(card) {
        const rows = Object.entries(card)
            .filter(([key]) => !key.startsWith('_'))
            .map(([key, value]) => `<tr><td>${escapeHtml(key)}</td><td>${escapeHtml(value || '')}</td></tr>`)
            .join('');
        cardRaw.innerHTML = `<table>${rows}</table>`;
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
