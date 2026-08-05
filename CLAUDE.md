# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## O que é este repositório

Repositório de **design de conteúdo** de um TCG (jogo de cartas colecionáveis) *fan-made* — regras, assets de cartas e, agora, a camada de dados estruturados que alimenta um **jogo eletrônico** do mesmo sistema (deck builder, motor de batalha, mapa PVE). Não há build/testes do "jogo" em si dentro deste repo — `viewer/` é a única aplicação executável (visualizador local em PHP). Todo o conteúdo é em **português do Brasil**; escreva e responda em pt-BR, mantendo os termos técnicos já cunhados (ex.: "DOT", "Debuff", "Ataque Perfurante", "Transpassar", "Sobrevida") em vez de traduzir ou inventar sinônimos.

## Sistema de regras ativo

- `rules.md` é o sistema de regras **atual e ativo**: "MULTIVERSITY CONQUEST". Qualquer edição de regras deve ir para este arquivo.
- `TCG.md` ("Heroes - The Contest") é um sistema **antigo/anterior**, não tocado pelos commits recentes. Não confundir terminologia entre os dois nem editar `TCG.md` a menos que o usuário peça explicitamente.
- `card.md` é um esboço abandonado/incompleto, não relacionado ao sistema de regras ativo.
- `analise-rules.md` é uma análise/auditoria do motor de regras (não um sistema de regras por si só).
- Nota de nomenclatura em transição: o que `rules.md` chama de **Aposte** é tratado nos `schemas/` e no motor futuro como **Desafiar** (`CHALLENGE`) — `rules.md` ainda não foi atualizado para refletir esse nome.

## Schemas de dados (`schemas/`)

Camada de formalização em JSON dos dados de `rules.md`, para consumo por deck builder, banco de cartas, motor de batalha e mapa PVE do jogo eletrônico em preparação. **Não redefinem regras** — apenas estruturam em dados o que `rules.md` já define. Todos travados como v1:

- `schema-cartas.md` — schema de Personagem, Item e Fonte de Energia (campos-base + miolo mecânico de cada tipo).
- `effectscript-taxonomy-v1.md` — taxonomia completa do campo `effectScript` (estruturado, não string livre), presente nos 3 tipos de carta.
- `schema-deck.md` — schema de deck (`entries[]`/`sideboard[]` referenciam cartas por `cardId`, nunca embutem dados de carta). Validação de regras de montagem é função separada (`ValidateDeck`), não parte do schema.
- `schema-pve-node.md` — schema de um nó do mapa PVE (estilo "mapa de nós"): config de partida, IA de oponente (catálogo fechado de perfis, todos determinísticos), recompensas.
- `schema-chapter-map.md` — schema de um capítulo do mapa PVE (agrupamento/metadados de exibição de nós; não redefine o grafo de conexão entre nós, que vive em `mapMeta.prerequisites`/`unlocks` de cada nó).

Convenção comum a todos: um documento **referencia** outro por `id`/`slug`/`cardId` em vez de embutir dados — a fonte de verdade de cada entidade vive em um único schema.

## Convenções de texto de carta (em `rules.md`)

- **Negrito** em efeitos de carta indica que o alvo é um personagem específico.
- *Itálico* indica que o alvo é uma categoria/tipo de personagem.

## Tipos de personagem e siglas de asset

Os tipos de personagem descritos em `rules.md` (Natureza, Vida, Morte, Divino, Elemental, TecSci, Magia, Mental, Físico, Poder Energético, Fera, Cósmico) mapeiam para siglas de 3 letras usadas nos nomes de arquivo de asset em `CARDS/ASSETS/` e `CARDS/ASSETS/STRUCTURES/V6/CHAR/`: `NTZ`, `LIF`, `DTH`, `DVN`, `ELM`, `TEC`, `MGC`, (Mental), `FSC`, `ENG`, `FRL`, `CSM`. Ao adicionar ou renomear um tipo, mantenha `rules.md`, as siglas de asset e `viewer/lib/CardTypes.php` consistentes entre si.

`STRUCTURES/` tem subpastas versionadas `V1` a `V6` — **V6 é a versão mais recente** do template/moldura de carta (`CARD-MODEL.png`, `LAND-MODEL.png`). Novos assets de estrutura devem ir em `V6`, não nas versões antigas.

## Coleções e biblioteca de cartas (`EXPANSIONS/`)

`EXPANSIONS/` (antigo `LIB/`) guarda tudo relativo a cada coleção/expansão, um subdiretório por expansão (`base-set/`, `Fratura do Multiverso/`), cada um com:

- `card-list/*.csv` — dados das cartas.
  - `base-set/card-list/*-CardGenerator.csv` — formato antigo, sem colunas `Prompt Arte`/`Arte`.
  - `Fratura do Multiverso/card-list/{PERSONAGENS,ITENS,ENERGIAS}.csv` — formato atual, separador `;`, inclui `Prompt Arte` e `Arte`/`IMAGEM` (nome do arquivo de asset gerado).
- `decks/` — decklists da coleção (ver seção "Decklists" abaixo). Substituiu o antigo `public/decks/` — `public/` hoje só guarda imagens soltas, sem mais decklists.
- `imgs/` — imagens de apoio da coleção (ex. arte de fundo, ícones).
- `schemas/` — pasta placeholder por coleção, ainda vazia; o schema de dados real e ativo continua em `schemas/` na raiz do repo (ver seção acima). Não confundir os dois.
- `Fratura do Multiverso/` também tem `card-art/` (arte final renderizada), `json-art/` (prompts de arte por mundo em JSON) e `fratura-do-multiverso.md` (documento de design/lore do set: escopo, tipos, mecânicas, cota de cartas — não é a lista final de cartas, isso é `card-list/`).

Novas coleções: crie uma subpasta em `EXPANSIONS/` com CSVs cujo nome contenha `PERSONAGENS`, `ITENS` ou `ENERGIAS` — `viewer/` detecta automaticamente.

## Viewer (`viewer/`)

Visualizador local em PHP que lê os CSVs de `EXPANSIONS/` e renderiza preview de carta usando as molduras de `CARDS/ASSETS/STRUCTURES/V6/`. Rodar com `php -S localhost:8791 -t viewer` a partir da raiz. Ver `viewer/README.md` para detalhes de arquitetura (`api.php`, `lib/CsvLibrary.php`, `lib/CardTypes.php`).

## Scripts utilitários (`scripts/`)

Automação de produção de assets, fora do fluxo de regras/conteúdo. Ver `scripts/README.md`:

- `gerar_imagens_chatgpt.py` — gera arte de carta via ChatGPT (browser automation/Playwright) a partir do `Prompt Arte` dos CSVs.
- `capturar_preview_cartas.py` — sobe o `viewer/` localmente e tira screenshot do preview renderizado de cada carta.
- `scripts/.chatgpt-profile/` (perfil de sessão do browser) e `scripts/__pycache__/` são ignorados pelo git — nunca versionar.

## Decklists

Arquivos de deck ficam em `EXPANSIONS/<coleção>/decks/` (antigo `public/decks/`) e seguem este formato: título `# [MONO|DUAL|TRIPLE|RAINBOW] <Tema> — <Mundo(s)/Tipo(s)> (<Afinidade(s)>)`, parágrafo(s) de estratégia, depois seções `## Personagens (N)`, `## Itens (N)`, `## Fontes de Energia (N)` com a contagem de cartas no título, subseções por categoria (`Líderes`, `Time`, `Permanente`, `Volátil`, `Básica`, `Prismática`, `Avançada`), e cada carta listada como `- NxNome (Versão) (Coleção) - Tag *`, onde `*` marca cartas-chave do combo e `(Coleção)` referencia a expansão de origem da carta (ex. `(Fratura do Multiverso)`). Nome de arquivo segue o prefixo do escopo, ex. `MONO-Divino-Sentinelas.md`, `DUAL-Magia+TecSci-Sinergia.md`, `TRIPLE-Magia+Fisico+Cosmico-Furia.md`, `RAINBOW-Equilibrio-Balanceado.md`. `new-decks.md` mantém um checklist manual de decks pendentes de criação, organizado por escopo (Mono Type, Dual Type, Triple Type, Rainbow).

## Licenciamento

O sistema de regras está sob GNU AGPL v3.0. A IP de terceiros usada (Marvel, DC, Saint Seiya, Tekken, etc.) é apenas para diversão entre amigos, não para uso comercial — uso comercial exige licenciamento à parte (ver seção "Licença" em `rules.md`).

## Changelog manual

`rules.md` tem uma seção `## Revisão` com um changelog manual referenciando hashes de commit (ex. `v0.1 [hash] - Adição de Licenciamento`). Essa convenção existe mas não está sendo mantida ativamente nos commits recentes — não é necessário atualizá-la a cada mudança, apenas esteja ciente do padrão caso o usuário peça para atualizá-la.

## Cuidado com assets grandes

`public/` contém imagens PNG grandes (até ~70 MB) versionadas diretamente no git. Não gere ou adicione novos assets de imagem sem necessidade — eles engordam o histórico do repositório permanentemente.
