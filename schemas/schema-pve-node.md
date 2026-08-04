# Multiversity Conquest — Schema de Config de Fase PVE (v1)

> Fonte de verdade estrutural de um **nó do mapa PVE** (estilo "mapa de nós",
> Marvel Future Fight). Consumido pela seleção de fase PVE (UI de mapa), pelo
> motor de batalha (setup de partida via `match`), pela IA de oponente (via
> `ai`) e pelo sistema de recompensas (via `rewards`). Não redefine regras —
> `match.matchOverrides` e `match.restrictions` são desvios explícitos e
> pontuais das regras padrão de `rules.md`/`ValidateDeck`, nunca um mecanismo
> de reescrita livre de regras.
>
> Um nó **referencia** um deck de oponente por `deckId` (ver `schema-deck.md`)
> — não embute a lista de cartas do oponente.
>
> `ai.profile` referencia um catálogo fechado de perfis de comportamento
> (seção 3 abaixo). O schema detalhado de cada `aiProfile` (parâmetros
> internos, heurísticas) é documento irmão futuro — aqui só o catálogo de
> nomes válidos e o que cada um significa em alto nível.

---

## 1. Campos-base e `mapMeta`

Metadados puramente estruturais — onde o nó vive no grafo do mapa. Não
interessam ao motor de batalha, só à UI de seleção de fase.

| Campo | Tipo | Descrição |
|---|---|---|
| `id` | string | Identificador único do nó |
| `slug` | string | Nome amigável para URL/arquivo |
| `name` | string | Nome de exibição do nó (título da fase) |
| `description` | string \| null | Prosa livre — texto de flavor/contexto do nó |
| `mapMeta.chapterId` | string | Agrupamento do nó em um capítulo/região do mapa |
| `mapMeta.position` | object | `{ "x": integer, "y": integer }` — posição visual no grafo do mapa |
| `mapMeta.prerequisites` | array de string | `id`s de nós que precisam estar concluídos antes deste ficar disponível. Lista vazia = disponível desde o início do capítulo |
| `mapMeta.unlocks` | array de string | `id`s de nós que este nó desbloqueia ao ser concluído. Redundante com `prerequisites` do nó alvo (mantido nos dois lados por conveniência de leitura do grafo; a fonte de verdade para desbloqueio é `prerequisites`) |
| `mapMeta.repeatable` | boolean | Se o nó pode ser jogado novamente após concluído (ex. farm de recompensas) |
| `mapMeta.nodeType` | enum | `battle \| training \| tutorial \| boss` — reservado para diferenciar exibição/comportamento no mapa; v1 usa só `battle` |

```json
{
  "id": "",
  "slug": "",
  "name": "",
  "description": null,
  "mapMeta": {
    "chapterId": "",
    "position": { "x": 0, "y": 0 },
    "prerequisites": [],
    "unlocks": [],
    "repeatable": false,
    "nodeType": "battle"
  }
}
```

---

## 2. `match` — configuração da partida

O que o motor de batalha precisa para montar o `GameState` inicial: qual
deck o oponente usa, quantas Pedras de Recompensa estão em jogo, e qualquer
desvio pontual das regras padrão.

| Campo | Tipo | Descrição |
|---|---|---|
| `opponentDeckId` | string | Referência ao `id`/`slug` do deck do oponente (ver `schema-deck.md`) |
| `difficultyTier` | integer | Nível de dificuldade do nó. Não tem efeito mecânico direto por si só — é lido por `ai.profileParams` e pode justificar os valores escolhidos em `matchOverrides`, mas **não recalcula nada automaticamente**; cada override é explícito no próprio nó |
| `rewardStones` | integer (4-8) | Quantidade de Pedras de Recompensa separadas para esta partida (ver `rules.md` > Áreas do Jogo). Prêmio conquistado **durante** a partida ao nocautear personagens — por isso vive em `match`, não em `rewards` (que é o prêmio de progressão pós-vitória) |

### 2.1 `matchOverrides` — desvios pontuais das regras padrão

Lista fechada de parâmetros que um nó pode ajustar. Todos `null`/`0`/`[]`
por padrão = partida idêntica ao PVP padrão de `rules.md`. Este bloco não é
um dicionário livre de regras — é um conjunto pequeno e explícito de
alavancas; um novo desvio necessário no futuro deve virar campo novo aqui,
não um campo genérico de "regra arbitrária".

| Campo | Tipo | Descrição |
|---|---|---|
| `opponentStartingLifeBonus` | integer | Bônus de Vida aplicado aos personagens do oponente ao entrarem em jogo. `0` = sem bônus |
| `opponentStartingHandSize` | integer \| null | Sobrescreve as 10 cartas padrão da mão inicial do oponente. `null` = usa o padrão de `rules.md` |
| `playerStartingHandSize` | integer \| null | Idem, para a mão inicial do jogador |
| `startingEnergyBonus` | integer | Energia extra pré-anexada a personagens do oponente no início da partida. `0` = sem bônus |
| `turnLimit` | integer \| null | Teto de turnos que força o fim da partida (para fases tipo "sobreviva N turnos"). Não é uma condição de vitória nova — é um limite que a partida respeita além das 3 condições de `rules.md` > Condições de Vitória. `null` = sem limite |
| `effects` | array de objeto | Efeitos de fase que afetam todos os personagens em campo (de ambos os jogadores, salvo indicação contrária no efeito). Reaproveita a mesma forma de nó de efeito de `effectscript-taxonomy-v1-1.md` — é a mesma "linguagem", só que aplicada no nível da partida em vez de uma carta. Lista vazia = nenhum efeito de fase |

```json
{
  "match": {
    "opponentDeckId": "",
    "difficultyTier": 1,
    "rewardStones": 6,
    "matchOverrides": {
      "opponentStartingLifeBonus": 0,
      "opponentStartingHandSize": null,
      "playerStartingHandSize": null,
      "startingEnergyBonus": 0,
      "turnLimit": null,
      "effects": []
    },
    "restrictions": {
      "allowedWorldTypes": null,
      "maxCopiesPerCard": null,
      "bannedCardIds": [],
      "requiredCardIds": []
    }
  }
}
```

### 2.2 `restrictions` — regras de construção de deck específicas do nó

Regras adicionais que o deck do **jogador** precisa respeitar para entrar
nesta fase — somam-se às regras normais de `rules.md`/`ValidateDeck`, nunca
as substituem. `null`/vazio em qualquer campo = sem restrição extra naquele
eixo.

| Campo | Tipo | Descrição |
|---|---|---|
| `allowedWorldTypes` | array de string \| null | Se definido, o deck do jogador só pode conter Personagens desses tipos (ex. `["Divino"]` força mono-tipo). `null` = sem restrição de tipo |
| `maxCopiesPerCard` | integer \| null | Sobrescreve o limite padrão de 4 cópias (R4/R6 de `ValidateDeck`) **apenas para esta fase**. `null` = usa o padrão |
| `bannedCardIds` | array de string | `cardId`s proibidos no deck do jogador para esta fase |
| `requiredCardIds` | array de string | `cardId`s que o deck do jogador precisa conter para entrar na fase (ex. fase de tutorial que exige uma carta específica) |

---

## 3. `ai` — comportamento do oponente

| Campo | Tipo | Descrição |
|---|---|---|
| `profile` | enum | Ver catálogo de perfis v1 abaixo |
| `profileParams` | object | Parâmetros específicos do perfil escolhido (ex. threshold numérico, sequência scripted). Estrutura interna definida pelo `profile`; schema detalhado de cada perfil é documento irmão futuro |

### Catálogo de perfis v1 (`ai.profile`)

| `profile` | Heurística |
|---|---|
| `passive-defensive` | Nunca ataca a menos que ameaçado; prioriza anexar energia e usar passivas de proteção/cura |
| `aggressive-simple` | Sempre ataca com o personagem de maior dano disponível; anexa energia antes de atacar |
| `defensive-simple` | Prioriza manter Defesa/Escudo; só ataca se puder nocautear no turno |
| `balanced` | Alterna entre atacar e proteger conforme a própria Vida restante (mistura das duas anteriores por threshold em `profileParams`) |
| `combo-follower` | Segue uma sequência de jogadas pré-definida em `profileParams` (scripted, não heurística livre) — usado em fases de tutorial/boss |

Nenhum perfil v1 usa busca em árvore (minimax/MCTS) — todos são
determinísticos e configuráveis só por dados, conforme decisão já registrada
em `multiversity-conquest-features-mvp.md` > IA de PVE.

```json
{
  "ai": {
    "profile": "aggressive-simple",
    "profileParams": {}
  }
}
```

---

## 4. `rewards` — recompensa de progressão pós-vitória

Distinto de `match.rewardStones` (prêmio *dentro* da partida): este bloco é
o que o jogador ganha ao **vencer** o nó, fora da partida em si.

| Campo | Tipo | Descrição |
|---|---|---|
| `guaranteedCardIds` | array de string | `cardId`s específicos garantidos como recompensa direta (fora de booster) |
| `boosterConfig` | object \| null | Ver estrutura abaixo. `null` = nenhum booster concedido |
| `energyGranted` | integer | Energia (moeda de custo de fase PVE, não Fonte de Energia de carta) concedida ao vencer |
| `coinsGranted` | integer | Moeda/crédito concedido ao vencer. **Nota**: valor "de mentira" enquanto a camada de live-service/pagamento real não existe (ver pergunta em aberto em `multiversity-conquest-features-mvp.md`) |

### 4.1 `boosterConfig` — booster como mecânica de gratificação

Boosters existem como mecânica de recompensa independente da loja (loja é
camada de monetização separada, catalogada como live-service).

| Campo | Tipo | Descrição |
|---|---|---|
| `count` | integer | Quantidade de boosters concedidos |
| `totalCards` | integer | Total de cartas por booster — deve ser igual à soma de `slots[].count` (checagem de consistência, análoga ao papel de `ValidateDeck` para o schema de Deck) |
| `slots` | array de objeto | Cada slot define quantas cartas de que tipo, em que faixa de raridade, vêm de qual fonte |

**Estrutura de cada item em `slots[]`:**

| Campo | Tipo | Descrição |
|---|---|---|
| `cardType` | enum | `Personagem \| Item \| FonteDeEnergia` |
| `count` | integer | Quantidade de cartas deste slot |
| `rarityRange` | array de enum | Faixa de raridades permitidas neste slot, ex: `["Comum", "Incomum"]`. **Campo obrigatório** — todo slot precisa declarar sua faixa de raridade, mesmo que seja a lista completa (`["Comum", "Incomum", "Rara", "Super-rara", "Ultra-rara"]`) |
| `source` | object | De onde as cartas deste slot são sorteadas — ver abaixo |

**Estrutura de `source`:**

```json
{ "kind": "collection", "collectionId": "fratura-do-multiverso" }
```
```json
{ "kind": "subset", "collectionId": "fratura-do-multiverso", "filter": { "worldType": "Divino" } }
```

| Campo | Tipo | Descrição |
|---|---|---|
| `kind` | enum | `collection \| subset` — v1 cobre só estes dois; pool ponderado por raridade e `cardIds[]` explícito ficam para quando a mecânica de boosters for aprofundada |
| `collectionId` | string | Set/coleção de origem (ex. `fratura-do-multiverso`) |
| `filter` | object \| null | Presente apenas quando `kind = "subset"`. v1 suporta só `{ "worldType": "..." }`; outros filtros (raridade, categoria) ficam para depois |

```json
{
  "rewards": {
    "guaranteedCardIds": [],
    "boosterConfig": {
      "count": 1,
      "totalCards": 6,
      "slots": [
        {
          "cardType": "Personagem",
          "count": 3,
          "rarityRange": ["Comum", "Incomum"],
          "source": { "kind": "collection", "collectionId": "fratura-do-multiverso" }
        },
        {
          "cardType": "Item",
          "count": 2,
          "rarityRange": ["Comum", "Incomum", "Rara"],
          "source": { "kind": "subset", "collectionId": "fratura-do-multiverso", "filter": { "worldType": "Divino" } }
        },
        {
          "cardType": "FonteDeEnergia",
          "count": 1,
          "rarityRange": ["Comum"],
          "source": { "kind": "collection", "collectionId": "fratura-do-multiverso" }
        }
      ]
    },
    "energyGranted": 1,
    "coinsGranted": 0
  }
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
  "mapMeta": {
    "chapterId": "",
    "position": { "x": 0, "y": 0 },
    "prerequisites": [],
    "unlocks": [],
    "repeatable": false,
    "nodeType": "battle"
  },
  "match": {
    "opponentDeckId": "",
    "difficultyTier": 1,
    "rewardStones": 6,
    "matchOverrides": {
      "opponentStartingLifeBonus": 0,
      "opponentStartingHandSize": null,
      "playerStartingHandSize": null,
      "startingEnergyBonus": 0,
      "turnLimit": null,
      "effects": []
    },
    "restrictions": {
      "allowedWorldTypes": null,
      "maxCopiesPerCard": null,
      "bannedCardIds": [],
      "requiredCardIds": []
    }
  },
  "ai": {
    "profile": "aggressive-simple",
    "profileParams": {}
  },
  "rewards": {
    "guaranteedCardIds": [],
    "boosterConfig": {
      "count": 1,
      "totalCards": 0,
      "slots": []
    },
    "energyGranted": 0,
    "coinsGranted": 0
  }
}
```

---

## Notas de status

- Schema **travado como v1**.
- Nó **referencia** o deck do oponente por `opponentDeckId` — nunca embute
  a lista de cartas. Única fonte de verdade de deck é `schema-deck.md`.
- `rewardStones` vive em `match`, não em `rewards` — é prêmio conquistado
  *durante* a partida (nocaute de personagens), distinto da recompensa de
  progressão pós-vitória.
- `matchOverrides` é uma lista fechada de alavancas explícitas, não um
  mecanismo de reescrita livre de regras — reforça a separação Core/config
  já estabelecida na Memória de Projeto de arquitetura técnica. Novo desvio
  necessário no futuro = campo novo aqui, não um dicionário genérico.
- `matchOverrides.effects[]` reaproveita a mesma forma de nó de efeito de
  `effectscript-taxonomy-v1-1.md`, aplicada no nível da partida em vez de
  uma carta — mesma "linguagem", escopo diferente.
- `restrictions` soma-se às regras de `ValidateDeck`, nunca as substitui.
- `ai.profile` usa um catálogo fechado de 5 perfis v1, todos determinísticos
  e sem busca em árvore (decisão já registrada em
  `multiversity-conquest-features-mvp.md`). O schema detalhado de cada
  `aiProfile` (estrutura de `profileParams` por perfil) é documento irmão
  futuro, ainda não escrito.
- `boosterConfig.totalCards` deve ser igual à soma de `slots[].count` —
  checagem de consistência a ser feita por uma função de validação análoga
  a `ValidateDeck`, ainda não escrita para este schema.
- `boosterConfig.slots[].rarityRange` é campo **obrigatório** em todo slot,
  mesmo quando cobre todas as raridades.
- `boosterConfig.slots[].source.kind` cobre só `collection` e `subset`
  (filtro único por `worldType`) em v1 — pool ponderado por raridade e
  `cardIds[]` explícito ficam para quando a mecânica de boosters for
  aprofundada, conforme decisão já registrada nesta conversa.
- `energyGranted`/`coinsGranted` em `rewards` são valores "de mentira"
  enquanto o backend de live-service/pagamento real não existe — pergunta
  em aberto herdada de `multiversity-conquest-features-mvp.md`.
