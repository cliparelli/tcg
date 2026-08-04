# Multiversity Conquest — Schema de Capítulo/Mapa PVE (v1)

> Fonte de verdade estrutural de um **capítulo** do mapa PVE — o agrupamento
> que dá nome, contexto visual e ordem a um conjunto de nós (ver
> `schema-pve-node.md`). Consumido pela UI de seleção de mapa/capítulo
> (tela "escolha o capítulo" antes de entrar no mapa de nós em si) e pelo
> sistema de progressão (ordem de liberação de capítulos).
>
> Este documento **não redefine conexão entre nós**. O grafo em si (quem
> desbloqueia quem, pré-requisitos) já existe em `mapMeta.prerequisites` /
> `mapMeta.unlocks` de cada nó individual — o Capítulo apenas **agrupa** nós
> pelo mesmo `chapterId` e adiciona metadados que pertencem ao grupo como um
> todo (nome de exibição, arte de fundo, ordem entre capítulos), sem duplicar
> a lógica de ligação nó-a-nó.
>
> Um Capítulo **referencia** nós por `id` — não embute a config completa de
> cada nó. A config do nó em si é definida em `schema-pve-node.md`.

---

## 1. Por que um documento separado (e não só `chapterId: string`)

`mapMeta.chapterId` em cada nó já permite reconstruir "todos os nós do
Capítulo 1" via filtro. Isso é suficiente para o motor, mas insuficiente
para a UI de seleção de mapa, que precisa saber coisas que **não pertencem
a nenhum nó individual**: o nome do capítulo, sua arte de fundo, se ele
está desbloqueado, e a ordem em que os capítulos aparecem na tela de
seleção. Sem esta entidade, essas informações teriam que ser
hard-coded na UI ou duplicadas em cada nó — o Capítulo existe para ser a
única fonte de verdade desses metadados de grupo.

---

## 2. Campos-base

| Campo | Tipo | Descrição |
|---|---|---|
| `id` | string | Identificador único do capítulo — mesmo valor referenciado por `mapMeta.chapterId` nos nós |
| `slug` | string | Nome amigável para URL/arquivo |
| `name` | string | Nome de exibição do capítulo (ex. "Capítulo 1 — A Fratura") |
| `description` | string \| null | Prosa livre — texto de introdução/lore do capítulo, exibido na tela de seleção antes de entrar no mapa |
| `collectionId` | string | Coleção/set de onde vêm as cartas predominantes deste capítulo (ex. `fratura-do-multiverso`) — informação de contexto para UI e para o `boosterConfig` dos nós do capítulo, não uma trava mecânica |
| `order` | integer | Posição deste capítulo na sequência de capítulos (1, 2, 3...). Determina a ordem de exibição, não a disponibilidade — isso é `unlockCondition` |
| `backgroundArtRef` | string \| null | Referência de asset da arte de fundo/capa do capítulo na tela de seleção |

```json
{
  "id": "",
  "slug": "",
  "name": "",
  "description": null,
  "collectionId": "",
  "order": 1,
  "backgroundArtRef": null
}
```

---

## 3. `unlockCondition` — disponibilidade do capítulo

Distinto de `mapMeta.prerequisites` (que trava um **nó** dentro de um
capítulo já acessível). Este campo trava o **capítulo inteiro** antes que
qualquer um dos seus nós fique visível/jogável.

| Campo | Tipo | Descrição |
|---|---|---|
| `unlockCondition.kind` | enum | `always \| previousChapterComplete \| specificNodesComplete` |
| `unlockCondition.requiredChapterId` | string \| null | Presente apenas quando `kind = "previousChapterComplete"` — `id` do capítulo que precisa estar 100% concluído |
| `unlockCondition.requiredNodeIds` | array de string | Presente apenas quando `kind = "specificNodesComplete"` — lista de `id`s de nós (de qualquer capítulo) que precisam estar concluídos |

`kind = "always"` é o valor para o primeiro capítulo do jogo (sem
pré-requisito). Mantido como enum fechado, no mesmo espírito de
`matchOverrides` em `schema-pve-node.md` — um conjunto pequeno e explícito
de condições, não uma expressão livre.

```json
{
  "unlockCondition": {
    "kind": "always",
    "requiredChapterId": null,
    "requiredNodeIds": []
  }
}
```

---

## 4. `nodes[]` — referência aos nós do capítulo

Lista de nós que pertencem a este capítulo, na ordem lógica de
apresentação (não necessariamente a ordem de desbloqueio, que já vem do
grafo de `prerequisites`/`unlocks` de cada nó).

| Campo | Tipo | Descrição |
|---|---|---|
| `nodeId` | string | Referência ao `id` do nó (`schema-pve-node.md`). O capítulo do nó (`mapMeta.chapterId`) deve bater com o `id` deste documento — checagem de consistência, não duplicação de dado |
| `isChapterBoss` | boolean | Marca o nó como o "nó final" do capítulo (tipicamente o mais difícil, ou o único que desbloqueia o próximo capítulo via `previousChapterComplete`). Não tem efeito mecânico por si só — é metadado de exibição/narrativa; a config de dificuldade real continua em `match`/`ai` do próprio nó |
| `isSubBoss` | boolean | Marca o nó como um "pré-chefe" ou chefe menor do capítulo — mais notável que uma fase comum, mas abaixo do chefe final. Tipicamente guarda uma ramificação do mapa ou antecede o `isChapterBoss`. Mutuamente exclusivo com `isChapterBoss` (um nó não deve ser os dois); assim como `isChapterBoss`, é puramente narrativo/de exibição, sem efeito mecânico próprio — dificuldade e recompensa continuam 100% no `match`/`ai`/`rewards` do próprio nó |

```json
{
  "nodes": [
    { "nodeId": "", "isChapterBoss": false, "isSubBoss": false }
  ]
}
```

---

## Template completo (documento vazio)

```json
{
  "id": "",
  "slug": "",
  "name": "",
  "description": null,
  "collectionId": "",
  "order": 1,
  "backgroundArtRef": null,
  "unlockCondition": {
    "kind": "always",
    "requiredChapterId": null,
    "requiredNodeIds": []
  },
  "nodes": [
    { "nodeId": "", "isChapterBoss": false, "isSubBoss": false }
  ]
}
```

---

## Notas de status

- Schema **travado como v1**.
- Este documento **não modela o grafo de conexão entre nós** — isso
  permanece inteiramente em `mapMeta.prerequisites`/`mapMeta.unlocks` de
  cada nó (`schema-pve-node.md`). O Capítulo é uma entidade de agrupamento
  e exibição, não uma segunda fonte de verdade para desbloqueio de nó
  individual.
- `nodes[].nodeId` deve ter `mapMeta.chapterId == this.id` no nó
  referenciado — checagem de consistência a ser feita por uma função de
  validação futura, mesmo espírito de `ValidateDeck` e da checagem de
  `boosterConfig.totalCards` pendente em `schema-pve-node.md`.
- `unlockCondition` é enum fechado (`always | previousChapterComplete |
  specificNodesComplete`), não uma expressão livre — mesma decisão de
  design já aplicada a `matchOverrides` em `schema-pve-node.md`: um
  conjunto pequeno e explícito de condições, expansível por campo novo
  quando necessário, não por dicionário genérico.
- `isChapterBoss` e `isSubBoss` são puramente narrativos/de exibição — não
  alteram `match`, `ai` ou `rewards` do nó. Se um chefe (final ou menor)
  precisa de dificuldade ou recompensa diferenciada, isso já é 100%
  configurável no próprio nó via `schema-pve-node.md`, sem precisar de
  campo novo aqui.
- `isChapterBoss` e `isSubBoss` são mutuamente exclusivos por convenção (um
  nó é um ou outro, nunca os dois) — não reforçado por enum único
  (`bossTier: none | sub | final`) para manter os dois campos como booleans
  independentes e simples de consultar; a exclusividade mútua é regra de
  validação futura (`ValidateChapter`), não uma restrição de schema.
- Ainda não construído (pendência nova, herdada da mesma lógica de
  `schema-pve-node.md`): função de validação do capítulo (`ValidateChapter`
  ou equivalente) cobrindo a checagem de consistência `chapterId` acima,
  detecção de nós órfãos (nó que referencia um `chapterId` para o qual não
  existe documento de Capítulo), e a exclusividade mútua entre
  `isChapterBoss` e `isSubBoss` no mesmo nó.
