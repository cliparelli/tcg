# Visualizador de Cartas

Visualizador local que lê os CSVs de `LIB/` e monta uma pré-visualização das
cartas usando as molduras de `CARDS/ASSETS/STRUCTURES/V6/`.

- Personagens e Itens usam `CARD-MODEL.png`.
- Fontes de Energia (Avançadas) usam `LAND-MODEL.png`. Se esse arquivo ainda
  não existir na pasta V6, o visualizador cai automaticamente para
  `CARD-MODEL.png` como placeholder.

Toda a leitura/parse dos CSVs acontece em PHP (`lib/CsvLibrary.php`); o
resultado é exposto como JSON (`api.php`) e consumido pelo JS (`app.js`)
apenas para renderizar a lista e o preview.

## Como rodar

```
cd viewer
php -S localhost:8791
```

Depois acesse http://localhost:8791 no navegador.

## Estrutura

- `index.php` — página principal (lista + preview).
- `api.php` — endpoints JSON:
  - `api.php?action=list-files` — varre `LIB/` recursivamente (uma pasta por
    coleção, ex. `base/`, `Fratura do Multiverso/`) e lista os CSVs
    reconhecidos (nome contendo `PERSONAGENS`, `ITENS` ou `ENERGIAS`).
  - `api.php?action=cards&file=<coleção>/<arquivo>.csv` — devolve as cartas
    daquele CSV já parseadas.
- `asset.php?model=card|land` — serve a moldura correspondente de V6, com
  fallback para `CARD-MODEL.png`.
- `lib/CsvLibrary.php` — scanner de `LIB/` e parser de CSV (separador `;`,
  cabeçalhos com quebra de linha).
- `lib/CardTypes.php` — mapa Tipo de personagem → sigla de 3 letras (ver
  `CLAUDE.md`).

## Adicionando novas coleções

Basta criar uma nova subpasta em `LIB/` com CSVs cujo nome contenha
`PERSONAGENS`, `ITENS` ou `ENERGIAS` — o visualizador detecta automaticamente
na próxima varredura (recarregue a página).
