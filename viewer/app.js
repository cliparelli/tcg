(function () {
    'use strict';

    const fileSelect = document.getElementById('file-select');
    const cardSelect = document.getElementById('card-select');
    const searchInput = document.getElementById('search');
    const sortSelect = document.getElementById('sort-select');
    const cardList = document.getElementById('card-list');
    const cardPreview = document.getElementById('card-preview');
    const cardRaw = document.getElementById('card-raw');

    let currentCards = [];
    let filteredCards = [];
    let activeCardId = null;
    let currentCollection = '';

    const TYPE_ASSET_MODEL = {
        personagem: 'card',
        item: 'land',
        energia: 'land',
    };

    const RARITY_OPTIONS = ['Comum', 'Incomum', 'Rara', 'Super-rara', 'Ultra-rara'];

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
        populateFileSelect(data.collections || []);
        fileSelect.addEventListener('change', () => loadFile(fileSelect.value));
        searchInput.addEventListener('input', applyFilter);
        sortSelect.addEventListener('change', applyFilter);
        cardSelect.addEventListener('change', () => {
            const card = filteredCards.find((c) => c._id === cardSelect.value);
            if (card) {
                selectCard(card);
            }
        });

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
            : currentCards.slice();
        sortCards(filteredCards);
        renderList();
    }

    function sortCards(cards) {
        const mode = sortSelect.value;
        const collator = new Intl.Collator('pt-BR', { sensitivity: 'base' });

        cards.sort((a, b) => {
            if (mode === 'tipo') {
                const diff = collator.compare(extractTypeName(a.Tipo) || '', extractTypeName(b.Tipo) || '');
                if (diff !== 0) return diff;
            } else if (mode === 'faccao') {
                const diff = collator.compare(extractFaction(a), extractFaction(b));
                if (diff !== 0) return diff;
            }
            return collator.compare(a.Nome || '', b.Nome || '');
        });
    }

    function extractFaction(card) {
        if (card._type === 'personagem') {
            const raw = (card.Classificação || '').trim();
            if (!raw) return '';
            const parts = raw.split(' - ')[0].trim().split(/\s+/);
            return parts.length > 1 ? parts.slice(1).join(' ') : parts[0] || '';
        }
        if (card._type === 'item') {
            return (card.Dinâmica || '').trim() || (card.Tipo || '').trim();
        }
        return (card.Tipo || '').trim();
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
            li.textContent = card.Nome || '(sem nome)';
            if (card._id === activeCardId) {
                li.classList.add('active');
            }

            const tag = document.createElement('span');
            tag.className = 'tag';
            tag.textContent = sortSelect.value === 'faccao'
                ? (extractFaction(card) || card.Tipo || card.Classificação || '')
                : (card.Tipo || card.Classificação || '');
            li.appendChild(tag);

            li.addEventListener('click', () => selectCard(card));
            cardList.appendChild(li);

            const opt = document.createElement('option');
            opt.value = card._id;
            opt.textContent = card.Nome || '(sem nome)';
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

        if (card._type !== 'energia') {
            const heart = document.createElement('div');
            heart.className = 'card-preview__heart';
            heart.textContent = card.Vida || '-';
            cardPreview.appendChild(heart);

            const shield = document.createElement('div');
            shield.className = 'card-preview__shield';
            shield.textContent = card.Defesa || '-';
            cardPreview.appendChild(shield);
        }

        const title = document.createElement('div');
        title.className = 'card-preview__title';
        title.textContent = card.Nome || '';
        cardPreview.appendChild(title);

        const elementalType = card._type !== 'item' ? extractTypeName(card.Tipo) : '';
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
        const classificationText = card._type === 'personagem'
            ? extractClassification(card)
            : (card._type === 'item' ? (card.Tipo || '').trim() : elementalType);
        const rarityInitial = rarityInitialFor(card.Raridade);
        classification.textContent = rarityInitial
            ? `${classificationText} ${rarityInitial}`
            : classificationText;
        cardPreview.appendChild(classification);

        const text = document.createElement('div');
        text.className = 'card-preview__text';
        text.innerHTML = buildTextBlocks(card);
        cardPreview.appendChild(text);

        if (card._type !== 'energia' && (card.Resistência || card.Fraqueza)) {
            const footer = document.createElement('div');
            footer.className = 'card-preview__footer';
            footer.innerHTML = `
                <span class="card-preview__dot ${typeColorClass(card.Resistência)}" title="Resistência: ${escapeHtml(card.Resistência || '')}"></span>
                <span class="card-preview__dot ${typeColorClass(card.Fraqueza)}" title="Fraqueza: ${escapeHtml(card.Fraqueza || '')}"></span>
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

    function extractClassification(card) {
        const raw = (card.Classificação || '').trim();
        if (!raw) {
            return '';
        }
        const parts = raw.split(' - ');
        return parts.length > 1 ? parts.slice(1).join(' - ').trim() : raw;
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
            addActionBlock(blocks, card['Custo P'], card.Tipo, card['Descrição P']);
            addActionBlock(blocks, card['Custo A'], card.Tipo, card['Descrição A']);

            addBlock(blocks, 'Sidekick', card['Descrição Sidekick']);
            addBlock(blocks, 'Líder', card['Descrição Líder']);
            addBlock(blocks, 'Flanquear', card['Descrição Flanquear']);
            addBlock(blocks, 'Ataque', card['Descrição Ataque']);
            addBlock(blocks, null, card['Texto Final 2']);
        } else if (card._type === 'item') {
            const meta = [card.Dinâmica, card.Universo].filter(Boolean).join(' · ');
            if (meta) blocks.push(`<h4>${escapeHtml(meta)}</h4>`);
            addBlock(blocks, null, card['Descrição']);
        } else if (card._type === 'energia') {
            const meta = card.Energia ? `Energia ${card.Energia}` : '';
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

        const form = document.createElement('form');
        form.className = 'card-raw__form';
        form.addEventListener('submit', (ev) => {
            ev.preventDefault();
            saveCard(card, form);
        });

        const table = document.createElement('table');
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
            let field;
            if (key === 'Raridade') {
                field = document.createElement('select');
                const emptyOpt = document.createElement('option');
                emptyOpt.value = '';
                emptyOpt.textContent = '—';
                field.appendChild(emptyOpt);
                for (const rarity of RARITY_OPTIONS) {
                    const opt = document.createElement('option');
                    opt.value = rarity;
                    opt.textContent = rarity;
                    field.appendChild(opt);
                }
                field.value = value || '';
            } else {
                const isLong = String(value || '').length > 60 || /\n/.test(String(value || ''));
                field = document.createElement(isLong ? 'textarea' : 'input');
                if (!isLong) {
                    field.type = 'text';
                } else {
                    field.rows = 3;
                }
                field.value = value || '';
            }
            field.name = key;
            field.className = 'card-raw__field';
            td.appendChild(field);
            tr.appendChild(td);

            tbody.appendChild(tr);
        }

        table.appendChild(tbody);
        form.appendChild(table);

        const actions = document.createElement('div');
        actions.className = 'card-raw__actions';

        const status = document.createElement('span');
        status.className = 'card-raw__status';
        actions.appendChild(status);

        const saveBtn = document.createElement('button');
        saveBtn.type = 'submit';
        saveBtn.textContent = 'Salvar';
        actions.appendChild(saveBtn);

        form.appendChild(actions);
        cardRaw.appendChild(form);
    }

    async function saveCard(card, form) {
        const status = form.querySelector('.card-raw__status');
        const saveBtn = form.querySelector('button[type="submit"]');
        const fields = {};
        for (const el of form.elements) {
            if (el.name) {
                fields[el.name] = el.value;
            }
        }

        saveBtn.disabled = true;
        status.textContent = 'Salvando...';
        status.className = 'card-raw__status';

        try {
            const res = await fetch('api.php?action=save-card', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    file: fileSelect.value,
                    id: card._id,
                    fields,
                }),
            });
            const data = await res.json();
            if (!res.ok || data.error) {
                throw new Error(data.error || 'Falha ao salvar.');
            }

            currentCards = data.cards || [];
            const updated = currentCards.find((c) => c._id === card._id);
            applyFilter();
            if (updated) {
                activeCardId = updated._id;
                renderList();
                renderPreview(updated);
                renderRaw(updated);
                const refreshedStatus = cardRaw.querySelector('.card-raw__status');
                refreshedStatus.textContent = 'Salvo.';
                refreshedStatus.className = 'card-raw__status card-raw__status--ok';
            }
        } catch (err) {
            status.textContent = err.message || 'Erro ao salvar.';
            status.className = 'card-raw__status card-raw__status--error';
            saveBtn.disabled = false;
        }
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
