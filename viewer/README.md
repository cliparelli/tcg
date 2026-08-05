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

## Adicionando novas coleções

Basta criar uma subpasta em `EXPANSIONS/` com `schemas/{personagens,itens,
energias}.json` — o Card Viewer detecta automaticamente na próxima
varredura. Para aparecer no Deck Viewer, a coleção também precisa de
`schemas/decks/*.json` (schema v1 de `schemas/schema-deck.md`).
