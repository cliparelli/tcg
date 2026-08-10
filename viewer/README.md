# Visualizador

Visualizador local (PHP) com duas páginas: preview de carta individual
(`index.php`) e preview de deck (`deck.php`). Ambos leem os schemas JSON de
`EXPANSIONS/{Coleção}/schemas/` (schema v1, ver `schemas/schema-cartas.md` e
`schemas/schema-deck.md` na raiz do repo) e renderizam a carta usando as
molduras de `CARDS/ASSETS/STRUCTURES/V6/`.

- Personagens e Itens usam `CARD-MODEL.png`.
- Fontes de Energia (Avançadas) usam `LAND-MODEL.png`. Se esse arquivo ainda
  não existir na pasta V6, o visualizador cai automaticamente para
  `CARD-MODEL.png` como placeholder.

## Como rodar

```
php -S localhost:8791 -t viewer
```

Depois acesse http://localhost:8791 no navegador.

## Card Viewer (`index.php`)

- `api.php` — endpoints JSON:
  - `action=list-files` — varre `EXPANSIONS/{Coleção}/schemas/` e lista
    `personagens.json`, `itens.json`, `energias.json` encontrados.
  - `action=cards&file=<coleção>/schemas/<arquivo>.json` — devolve os
    registros daquele JSON, com `_id`/`_type`/`_collection` anexados.
  - `action=update-stats` (POST) — edita `stats.life`/`stats.defense` de um
    personagem (`{file, id, life?, defense?}`), regravando o JSON de origem.
  - `action=update-action` (POST) — edita um campo simples (`name`,
    `description`, `kind`, `energyCost`, `keywords`) de `actions[n]` de um
    personagem (`{file, id, actionIndex, field, value}`).
- `lib/CardLibrary.php` — scanner de `EXPANSIONS/*/schemas/*.json` e as
  rotinas de edição/regravação (preserva indentação de 2 espaços e a
  distinção `{}`/`[]` do JSON original).
- `lib/CardTypes.php` — mapa Tipo de personagem → sigla de 3 letras (ver
  `CLAUDE.md`).
- `app.js` — lista, filtro, busca e preview da carta; monta o texto de
  personagem a partir de `actions[].energyCost` + `actions[].description`.

## Deck Viewer (`deck.php`)

- `deck-api.php` — endpoints JSON:
  - `action=list` — varre `EXPANSIONS/{Coleção}/schemas/decks/*.json` e
    lista título, estilo (`MONO`/`DUAL`/`TRIPLE`/`RAINBOW`, extraído do
    prefixo do nome do arquivo) e tipos dominantes (para os filtros da UI).
  - `action=deck&file=<coleção>/schemas/decks/<arquivo>.json` — devolve o
    deck com `entries[]`/`sideboard[]` já resolvidos: cada `cardId` é casado
    com o registro real da carta (personagem/item/energia) e com a imagem
    correspondente em `imgs/`, quando existir.
- `lib/DeckLibrary.php` — scanner de `schemas/decks/*.json` (schema v1 de
  `schemas/schema-deck.md`) e resolução de `cardId` contra o índice de
  cartas montado via `CardLibrary`. A arte é localizada em `imgs/` a partir
  do nome da carta (`{tipo}-{Nome-Sem-Acentos-Com-Hifens}.png`); quando só
  existe a versão placeholder (`-sem-imagem.png`), ela é usada como
  fallback.
- `deck.js` — filtro por tipo/estilo, ordenação, grid de cartas + decklist
  lateral agrupada por seção (Personagens/Itens/Energias) e papel sugerido
  (`suggestedRole: leader|team`), com destaque do combo central
  (`strategy.coreCombo`).

Coleções sem `schemas/decks/` (hoje, `base-set/`) não aparecem no Deck
Viewer — só decklists já migradas para o schema JSON são lidas.

## Deck Builder (`deck-builder.php`)

Editor de decks (criar e editar), inspirado no Builder do Limitless TCG:
paleta de cartas pesquisável à esquerda, deck em construção ao centro, painel
de legalidade ao vivo à direita.

- `deck-builder-api.php` — endpoints JSON:
  - `action=collections` — lista as coleções disponíveis (subpastas de
    `EXPANSIONS/` com `schemas/`).
  - `action=cards&collection=<coleção>` — todas as cartas
    (personagem/item/energia) da coleção, para popular a paleta.
  - `action=list` / `action=deck&file=...` — mesmos dados de
    `deck-api.php` (via `DeckLibrary`), reaproveitados para a tela de
    "editar deck existente".
  - `action=create` (POST `{collection, meta}`) — cria um deck vazio
    (template de `schema-deck.md`) em
    `EXPANSIONS/{coleção}/schemas/decks/{slug}.json`.
  - `action=save` (POST `{file, deck}`) — regrava o deck completo no
    arquivo indicado.
- `lib/DeckLibrary.php` — além de `listDecks`/`readDeck` (somente-leitura,
  usados pelo Deck Viewer), tem `createDeck`/`saveDeck` para o Builder,
  regravando o JSON com o mesmo guard de path traversal e a mesma
  indentação de 2 espaços usada por `CardLibrary`.
- `deck-builder.js` — estado do deck em memória (`entries[]`/`sideboard[]`),
  adição/remoção de cartas, e `validateDeck()`: valida ao vivo, no client,
  as regras de "Montagem do Deck" de `rules.md` (máx. 60 cartas no deck
  principal, mín. 12 Personagens, mín. 12 Fontes de Energia, máx. 4 cópias
  de Personagem/Item/Energia avançada, Lendária/Épica só 1 cópia, sideboard
  máx. 15). O backend não valida legalidade no save — um deck
  incompleto/rascunho é um estado válido, conforme `schema-deck.md`.

## Adicionando novas coleções

Basta criar uma subpasta em `EXPANSIONS/` com `schemas/{personagens,itens,
energias}.json` — o Card Viewer detecta automaticamente na próxima
varredura. Para aparecer no Deck Viewer, a coleção também precisa de
`schemas/decks/*.json` (schema v1 de `schemas/schema-deck.md`).
