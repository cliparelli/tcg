# Multiversity Conquest — effectScript: Sintaxe e Taxonomia (v1)

> Fonte de verdade estrutural do campo `effectScript`, presente em Fontes de
> Energia, Itens e em cada `action` de Personagem (ver `schema-cartas.md`).
> Consumidor: motor de batalha (executa), banco de cartas (armazena), deck
> builder (ignora, só lê metadados), IA (heurística de valor).
> Formato: **JSON estruturado** — sem parser de texto livre.
>
> Nota de nomenclatura: o que `rules.md` chama de **Aposte** é tratado neste
> schema e no motor como **Desafiar** (`CHALLENGE`). `rules.md` será
> atualizado separadamente para refletir esse nome.

---

## 1. Visão geral da árvore

Todo `effectScript` tem a mesma forma raiz:

```json
{
  "conditionToAct": [ /* Devotion | Affinity | Concentration | Dependency */ ],
  "trigger": "OnEnter | Passive | OnAttach | OnDetach | OnEvent | null",
  "onceKind": "turn | combat | game | none",
  "additionalCosts": [ /* nós de ação completos, ver seção 9 */ ],
  "optionalCosts": [ /* nós de ação completos, ver seção 9 */ ],
  "effects": [ /* lista de nós de efeito, resolvidos em sequência */ ],
  "combo": null
}
```

| Campo | Descrição |
|---|---|
| `conditionToAct` | Gate: a ação/carta só pode ser usada/jogada se as condições forem satisfeitas |
| `trigger` | Quando o efeito dispara (ETB, passiva contínua, anexar/desanexar, evento nomeado) |
| `onceKind` | Granularidade de "uma vez por X" — seção 6 |
| `additionalCosts` / `optionalCosts` | Custos pagos para executar a ação — seção 9 |
| `effects` | O que acontece, em ordem — seção 4 |
| `combo` | Reservado para Starter/Combo/Finisher/Tag/Stance — seção 11. `null` até termos exemplos reais |

---

## 2. `conditionToAct` — gates de execução

```json
"conditionToAct": [
  { "kind": "Devotion",      "condition": { "filterType": "worldType", "value": "Magia" } },
  { "kind": "Affinity",      "condition": { "filterType": "characterName", "value": "Kaelen" } },
  { "kind": "Concentration", "condition": "target.life <= 2" },
  { "kind": "Dependency",    "condition": "controller.discard.count >= 5" }
]
```

- `kind` ∈ `Devotion | Affinity | Concentration | Dependency` (vocabulário do glossário, unificado em um único nó).
- `Devotion`/`Affinity` usam `condition` estruturado: `filterType: worldType | category | characterName`. Ambos checam presença em campo (não são filtros de destinatário de efeito — mecanicamente distintos de `target.filter`, seção 3, mesmo usando vocabulário parecido).
- `Concentration` (trava a ação) e `Dependency` (trava o efeito) usam `condition` como expressão livre — mesma linguagem de condição da seção 7.
- Lista vazia `[]` = sem gate.

**Exemplo real (Fossa dos Sábios Caídos):**
```json
"conditionToAct": [
  { "kind": "Devotion", "condition": { "filterType": "worldType", "value": "TecSci" } }
]
```

---

## 3. `target` — objeto estruturado, incorporado em cada efeito

```json
{
  "scope": "self | singleCharacter | allAllies | allEnemies | eachController | opponentController | allCharacters | attachedCharacter",
  "filter": { "filterType": "worldType | category | characterName | none", "value": "X" },
  "selection": "fixed | choice | random",
  "count": 1
}
```

- `scope` define o universo básico de quem é elegível.
- `attachedCharacter` — usado por Item/Fonte de Energia cujo texto se refere ao "personagem que este card for anexado" (ex. "coloque um token de AtkUp no personagem que este card for anexado"). Distinto de `self`: `self` é a própria carta de Fonte/Item/Personagem (relevante quando ela própria pode ser alvo de um efeito, ex. recoil em Personagem); `attachedCharacter` é sempre o personagem hospedeiro do anexo, nunca a carta de Item/Fonte em si. Não se aplica a Personagem (que não é anexado a nada).
- `filter` restringe dentro do `scope` (ex. "categoria Sentinelas" dentro de `allAllies`). `filterType: "none"` quando não há restrição.
- `selection`: `fixed` (alvo implícito/automático, ex. "self"), `choice` (controlador escolhe), `random` (sorteio D4/moeda — o fallback "escolha livre se não houver resultado" é regra fixa de motor e não aparece aqui).
- `count`: quantos alvos, default 1. Trabalha junto com a flag `AlvosMúltiplos` (seção 5) quando for efeito de dano.

**Exemplo (Aridan, Leitura do Último Fôlego — dano + efeito colateral em time inteiro):**
```json
"target": { "scope": "singleCharacter", "filter": { "filterType": "none" }, "selection": "choice", "count": 1 }
```
efeito colateral secundário (token em cada personagem do defensor, condicionado a nocaute):
```json
"target": { "scope": "eachController", "filter": { "filterType": "none" }, "selection": "fixed", "count": null }
```

**Campos adicionais opcionais em `target`** — usados quando `scope`/`filter`
sozinhos não capturam uma restrição pontual do texto da carta, sem precisar
de um novo valor de `filterType` para um caso isolado:

| Campo | Tipo | Descrição |
|---|---|---|
| `note` | string | Anotação legível por humano/IA para uma restrição de alvo que ainda não tem vocabulário fechado próprio (ex. `"personagem na posição principal"`, `"mesmo alvo de e1"`). Não é lido por parser de regra — é documentação inline até a restrição aparecer em cartas suficientes para virar `filterType` novo |
| `excludeController` | boolean | Quando `true`, restringe implicitamente a `singleCharacter`/`allCharacters` para excluir personagens do próprio controlador do efeito (ex. "Extinguir Fonte de um personagem que você não controla"). Default `false` |

**Exemplo real (Trono Enferrujado — Fonte de Energia, "Extinguir Fonte (1)
de um personagem que você não controla"):**
```json
"target": { "scope": "singleCharacter", "filter": {"filterType":"none"}, "selection": "choice", "count": 1, "excludeController": true }
```

---

## 4. `effects[]` — nós de ação (vocabulário fechado, extensível)

Cada efeito:
```json
{
  "id": "e1",
  "action": "DAMAGE | TOKEN | REMOVE_TOKEN | HEAL | DRAW | DISCARD | LOOK_TOP | SEARCH | RETURN_TO_HAND | SHUFFLE_INTO_DECK | DESTROY | KNOCKOUT | CHANGE_TYPE | MODIFY_STAT | GENERATE_ENERGY | STATIC_MODIFIER | GRANT_KEYWORD | SET_STATE | CHALLENGE | CHOICE",
  "params": { /* específico de cada action; campos numéricos aceitam inteiro fixo OU expressão dinâmica — ver 4.4 */ },
  "target": { /* seção 3; ausente em CHALLENGE/CHOICE, que têm sub-efeitos com seu próprio target */ },
  "condition": "expressão opcional, seção 7 — dispara só se verdadeira",
  "resolvesAt": "immediate | endOfCombatPhase | endOfTurn | endOfCleanupPhase (opcional, default immediate — ver 4.5)"
}
```

### 4.1 Ações primitivas e seus `params`

| `action` | `params` típico | Cobre (glossário) |
|---|---|---|
| `DAMAGE` | `{ "amount": 4, "combatFlags": [] }` | Dano direto |
| `TOKEN` | `{ "tokenType": "Escudo\|Sobrevida\|AtkUp\|DefDown\|...", "amount": 2, "removable": true }` | Todos os tokens de efeito positivo/negativo |
| `REMOVE_TOKEN` | `{ "tokenType": "DefDown", "amount": "all\|1" }` | Remoção de token |
| `HEAL` | `{ "amount": 3 }` | Cura direta de Vida (distinto de token Sobrevida) |
| `DRAW` | `{ "amount": 1, "deck": "own\|opponent" }` | Compra de carta |
| `DISCARD` | `{ "amount": 1, "chosenBy": "self\|opponent", "wholeHand": false }` | `Extinguir Recurso` como efeito |
| `LOOK_TOP` | `{ "amount": 2, "deck": "own\|targetPlayer", "reorder": true, "reveal": false, "keepOrDiscard": false }` | `Prever`/`Sondar` — ver 4.2 |
| `SEARCH` | `{ "matchType": "cardType\|worldType\|exactName", "value": "X", "reveal": true, "destination": "hand" }` | `Clarividência` |
| `RETURN_TO_HAND` | `{ "scope": "character\|item\|energySource", "amount": 1, "from": "discard\|battlefield" }` | `Reaproveitar Recurso` / Scoopup |
| `SHUFFLE_INTO_DECK` | `{ "scope": "character\|item\|energySource", "amount": 1, "from": "discard" }` | `Renovar Recursos` |
| `DESTROY` | `{ "scope": "energySource\|item" }` | `Extinguir Fonte` como efeito |
| `KNOCKOUT` | `{}` | Nocaute direto (ex. custo "Nocauteie um personagem que você controla") |
| `CHANGE_TYPE` | `{ "newWorldType": "Cósmico\|any-choice" }` | Alteração de tipo |
| `MODIFY_STAT` | `{ "stat": "attack\|defense\|life", "amount": 1, "duration": "untilCleanup\|permanent\|untilEndOfTurn" }` | Buff/debuff de atributo fora do sistema de tokens |
| `GENERATE_ENERGY` | `{ "amount": 1, "worldType": "any-choice\|Magia\|..." }` | Geração pontual de energia |
| `STATIC_MODIFIER` | ver 4.3 | Efeitos contínuos (Permanentes/Avançadas) |
| `GRANT_KEYWORD` | `{ "keyword": "Desviar\|Reflexão\|IgnorarDefesa\|Flanquear\|...", "duration": "untilCleanup\|permanent\|untilEndOfTurn\|nextCombatAction", "freeCost": false }` | Concessão temporária de palavra-chave |
| `SET_STATE` | `{ "state": "Stance", "value": "NomeDaStance\|null" }` | Ver seção 10 |
| `CHALLENGE` | ver seção 8 | `Desafiar` (ex-Aposte) |
| `CHOICE` | ver seção 8 | Escolha entre efeitos (N de M) |

Todos os `additionalCosts[]`/`optionalCosts[]` usam esta mesma tabela de ações
— não existe um vocabulário de custo separado (seção 9).

**Campos opcionais adicionais em `params`, com default que preserva o
comportamento anterior (nenhuma carta existente precisa de migração):**

| Campo | Em | Tipo | Default | Descrição |
|---|---|---|---|---|
| `wholeHand` | `DISCARD` | boolean | `false` | Quando `true`, `amount` é ignorado e a ação descarta a mão inteira do alvo, sem escolha — distinto de um `amount` fixo ou expressão dinâmica (seção 4.4) sobre uma quantidade parcial escolhida. Caso real: Reciclagem Total ("Recicle todas as cartas disponíveis na sua mão") |
| `removable` | `TOKEN` | boolean | `true` | Quando `false`, o token não pode ser removido por efeitos nem pela fase de limpeza — reforço textual raro, não um novo tipo de token. Caso real: Fratura do Sol ("Estes tokens não podem ser removidos por efeitos ou durante a fase de limpeza") |
| `freeCost` | `GRANT_KEYWORD` | boolean | `false` | Quando `true`, a próxima vez que a palavra-chave concedida seria usada, seu custo de energia normal é ignorado — usado quando o texto concede a keyword explicitamente "sem custo" além de concedê-la. Caso real: Investida Dupla ("as ações do personagem alvo ganham Duplicar sem custo") |

`duration` em `GRANT_KEYWORD`/`MODIFY_STAT` inclui o valor `nextCombatAction`
— concede o efeito até (e incluindo) a próxima ação de combate do
personagem, consumido no uso dela, distinto de `untilEndOfTurn` (que expira
no fim do turno mesmo sem uso). Caso real: Vale da Têmpera (Fonte de
Energia) — "a próxima ação de combate do personagem que este card for
anexado ganha Ignorar Defesa e Transpassar":
```json
{
  "action": "GRANT_KEYWORD",
  "params": { "keyword": "IgnorarDefesa", "duration": "nextCombatAction" },
  "target": { "scope": "attachedCharacter", "filter": {"filterType":"none"}, "selection": "fixed", "count": 1 }
}
```

### 4.2 Nota: `Prever` vs `Sondar` vs `Clarividência`

Três primitivas de "olhar deck" com semânticas distintas:
- **Prever(X)** → `LOOK_TOP{ reorder: true, reveal: false, keepOrDiscard: false }` no deck alvo — reorganiza livremente.
- **Sondar(X)** → `LOOK_TOP{ reorder: true, reveal: false, keepOrDiscard: true }` — decide manter ou mandar cada carta pro fundo.
- **Clarividência(X)** → `SEARCH` — busca dirigida por tipo ou nome exato, revela, vai para a mão.

### 4.3 `STATIC_MODIFIER` — forma estruturada

```json
{
  "affects": "actionCost | stat | rule | combatFlag",
  "filter": { "filterType": "hasKeyword | hasAdditionalCost | worldType | category | actionCategory | none", "value": "X" },
  "amount": -3,
  "scope": "controller | opponent | both",
  "appliesTo": "allMatching | nextAction",
  "ruleId": "energyAttachLimit | itemsPerTurnLimit | ...",
  "newValue": 2,
  "grantsCombatFlag": "DanoPerfurante"
}
```

- `affects: "actionCost"` — reduz/aumenta custo de ações que casam com `filter` (ex. Cajado do Vento Parado: `filter.filterType = "hasKeyword"`, `value: "Clarividencia"`, `amount: -3`, `scope: "controller"`).
- `affects: "stat"` — modifica atributo continuamente enquanto a carta estiver em jogo.
- `affects: "rule"` — modificação de um limite nomeado do jogo, não numérico-simples sobre um filtro. Usa `ruleId` (enum fechado e extensível, ex. `energyAttachLimit`) + `newValue` no lugar de `filter`/`amount` (ex. Sobrecarga de Núcleo: `ruleId: "energyAttachLimit"`, `newValue: 2`, `scope: "controller"`).
- `affects: "combatFlag"` — concede uma `combatFlag` (seção 5) a todas as ações de combate que casam com `filter`, continuamente enquanto a carta estiver em jogo. Usa `grantsCombatFlag` (nome da flag) no lugar de `amount`/`newValue`. Distinto de `GRANT_KEYWORD` (que concede uma keyword pontual, com `duration` finita, a um alvo específico) — aqui o efeito é permanente e vale para toda ação de combate futura do personagem, não uma concessão única. Caso real (Or'Kaath, a Última Estrela — "todas as suas ações de combate causam Dano Penetrante"):
```json
{ "action": "STATIC_MODIFIER", "params": { "affects": "combatFlag", "filter": { "filterType": "actionCategory", "value": "Combat" }, "scope": "controller", "grantsCombatFlag": "DanoPerfurante" } }
```
- `filter.filterType: "hasAdditionalCost"` — casa ações cujo `additionalCosts[]` contém uma ação de um tipo dado (`value` é o nome da `action`, ex. `"DISCARD"` para "ações que possuem Extinguir Recursos como custo adicional"), distinto de `hasKeyword` (que casa pela keyword da própria ação, não pelo seu custo). Caso real: Bolsa sem Fundo.
- `appliesTo` (opcional, default `allMatching`) — quando `nextAction`, o modificador se consome na próxima ação que casar com `filter`, em vez de valer continuamente enquanto a carta estiver em jogo. Usado por efeitos pontuais (não `Passive`) que reduzem o custo de uma única ação futura específica, distinto do uso contínuo padrão de `STATIC_MODIFIER`. Caso real: Chave de Fenda Torta ("o custo da próxima ação do personagem alvo é reduzida em 1").

**Exemplo real (Arena Neutra do Torneio — custo de ações de Combate reduzido para ambos os jogadores):**
```json
{
  "action": "STATIC_MODIFIER",
  "params": {
    "affects": "actionCost",
    "filter": { "filterType": "actionCategory", "value": "Combat" },
    "amount": -1,
    "min": 1,
    "scope": "both"
  }
}
```

**Exemplo real (Sobrecarga de Núcleo — regra nomeada, não numérica sobre filtro):**
```json
{
  "action": "STATIC_MODIFIER",
  "params": {
    "affects": "rule",
    "ruleId": "energyAttachLimit",
    "newValue": 2,
    "scope": "controller"
  }
}
```

### 4.4 Valores dinâmicos em `params`

Campos numéricos que normalmente recebem um inteiro fixo (ex. `DAMAGE.amount`)
podem alternativamente receber uma **string de expressão**, reaproveitando o
mesmo vocabulário de condições da seção 7, quando o valor não é conhecido até
o momento da resolução:

```json
{ "action": "DAMAGE", "params": { "amount": "event.damageDealt", "combatFlags": [] } }
```

Caso de uso observado — **recoil espelhado** (Nyx-Vahl, Skarn — "Rachaduras
Abertas"): o dano sofrido é igual ao dano que o próprio personagem acabou de
causar. `amount: "event.damageDealt"`, com `condition: "event.type ==
'characterDealtDamage' && event.source == self"` no mesmo efeito.

Não existe um campo irmão (`amountFormula`) — o mesmo campo (`amount`) aceita
`integer | expression-string`, mantendo um único ponto de leitura para quem
consome a carta.

### 4.5 `resolvesAt` — timing de resolução tardia

Tokens e efeitos podem ser aplicados em um momento diferente daquele em que a
ação é resolvida — inclusive fora do turno do controlador (ex. no fim da fase
de combate, independente de quem a iniciou). Campo opcional em qualquer
efeito (ver definição na seção 4):

```json
"resolvesAt": "immediate | endOfCombatPhase | endOfTurn | endOfCleanupPhase"
```

Default `immediate`. Quando presente com outro valor, o efeito é resolvido no
momento indicado, não no momento em que a ação foi usada — a `condition` (se
houver) é avaliada normalmente antes de aplicar o efeito, no momento do
`resolvesAt`.

**Exemplo real (Dorhen, Golpe da Fronteira — token só ao fim da fase de
combate, condicionado a remoção de token durante a limpeza deste turno):**
```json
{
  "id": "e2",
  "action": "TOKEN",
  "params": { "tokenType": "Invisibilidade", "amount": 1 },
  "target": { "scope": "self", "filter": {"filterType":"none"}, "selection": "fixed", "count": 1 },
  "condition": "self.tokensRemovedThisCleanup.count > 0",
  "resolvesAt": "endOfCombatPhase"
}
```

---

## 5. `combatFlags[]` — modificadores dentro de `DAMAGE`

Array heterogêneo dentro de `params` de um efeito `DAMAGE`:

```json
"combatFlags": [
  { "flag": "IgnorarDefesa" },
  { "flag": "DanoPerfurante" },
  { "flag": "Transpassar" },
  { "flag": "AtaqueExpansível", "limit": null },
  { "flag": "AlvosMúltiplos", "limit": 3 },
  { "flag": "PontoFraco" },
  { "flag": "Vampirismo" },
  { "flag": "Espinhos", "ratio": 3 },
  { "flag": "ToqueMortal" },
  { "flag": "Assistência" },
  { "flag": "ConexãoVital", "recipient": "self\|character" },
  { "flag": "TipoMutável", "worldType": "X" },
  { "flag": "NegarMorte" }
]
```

- `AlvosMúltiplos` aqui é informativa e redundante com `target.count` — este último é a fonte de verdade; a flag serve para leitura rápida por humanos/IA sem reprocessar `target`.
- `DanoPerfurante`, embora seja formalmente um **tipo de dano** em `rules.md` (categoria própria, distinta das keywords de combate), é modelado aqui como mais uma `combatFlag` — não existe campo `damageType` separado. Decisão de simplicidade.

**Custo opcional que ativa uma combat flag**: cada flag pode individualmente
carregar seu próprio `requiresOptionalCost`, permitindo que um único efeito
de dano nasça "normal" e ganhe propriedades condicionalmente ao pagamento de
um custo opcional (ver seção 9 para o exemplo completo):
```json
"combatFlags": [
  { "flag": "DanoPerfurante", "requiresOptionalCost": "c2" },
  { "flag": "Transpassar", "requiresOptionalCost": "c2" }
]
```

**Valor de flag amarrado a uma quantidade variável de custo (`linkedToCost`)**:
quando o texto da carta usa a mesma letra em duas palavras-chave — uma no
custo, outra no efeito (ex. "Extinguir Fonte (X) - Alvos Múltiplos (X)") — as
duas sempre se referem ao **mesmo valor**, escolhido no momento em que o
custo é pago. A flag correspondente usa `linkedToCost` (o `id` do custo em
`additionalCosts[]`/`optionalCosts[]`) no lugar de um `limit` numérico fixo:
```json
{ "flag": "AlvosMúltiplos", "limit": "X", "linkedToCost": "c1" }
```
Quando a carta usa **letras diferentes** para custo e efeito (ex. "Extinguir
Fonte (X) - Alvos Múltiplos (Y)", onde Y tem sua própria definição no texto),
os dois valores são independentes — não usar `linkedToCost` nesse caso; `Y`
deve ter sua própria fonte de valor (fixo, ou fórmula própria via seção 4.4).
Não existe um sistema de variáveis nomeadas na raiz do `effectScript` — a
amarração é sempre local, via `linkedToCost` apontando o `id` do custo.

**Exemplo real (Nyx-Vahl, Colapso Parcial — X compartilhado entre custo e
efeito):**
```json
{
  "additionalCosts": [
    {
      "id": "c1",
      "action": "DESTROY",
      "params": { "scope": "energySource", "amount": "X", "variable": true },
      "target": { "scope": "self", "filter": {"filterType":"none"}, "selection": "fixed" }
    }
  ],
  "effects": [
    {
      "id": "e1",
      "action": "DAMAGE",
      "params": {
        "amount": 4,
        "combatFlags": [
          { "flag": "AtaqueExpansível", "limit": 2 },
          { "flag": "AlvosMúltiplos", "limit": "X", "linkedToCost": "c1" }
        ]
      },
      "target": { "scope": "singleCharacter", "filter": {"filterType":"none"}, "selection": "choice", "count": "X" }
    }
  ]
}
```
(`AtaqueExpansível(2)` aqui é um limite fixo e independente — refere-se ao
mecanismo padrão de energia extra anexada, não ao `X` variável do custo.)

---

## 6. `onceKind` — granularidade de repetição

Enum: `"turn" | "combat" | "game" | "none"`.

| Valor | Cobre |
|---|---|
| `turn` | Ação Única |
| `combat` | Flanquear; Combo com Desafiar por combate |
| `game` | Ação Etérea |
| `none` | Sem restrição (default) |

---

## 7. Condições livres (`Concentration`/`Dependency`, e `condition` em efeitos)

Expressão simples em string, vocabulário fechado de operandos conhecidos do
motor (não é uma linguagem de programação genérica):

```
"<subject>.<property> <op> <value>"
```

Exemplos observados nas cartas:
- `"target.life <= 2"` (Cinzas do Último Templo)
- `"controller.discard.count >= 5"` (Serra Circular Remendada)
- `"self.attachedEnergy.count >= 3"` (Cadeia de Supernovas)
- `"event.characterKnockedOut == true"` (Testemunho da Fratura)

Um efeito individual carrega sua própria condição de disparo no campo
`condition` (ver seção 4), podendo referenciar o resultado de um efeito
anterior por `id`:
```json
{
  "id": "e2",
  "action": "TOKEN",
  "params": { "tokenType": "Perdicao", "amount": 1 },
  "target": { "scope": "eachController", "filter": {"filterType":"none"}, "selection": "fixed" },
  "condition": "e1.result.targetKnockedOut == true"
}
```
(cobre "se o alvo foi nocauteado por este ataque, ...")

Uma `condition` também pode referenciar a satisfação de um `conditionToAct`
da própria ação (útil quando só uma parte dos efeitos depende do gate, e não
a ação inteira):
```json
"condition": "conditionToAct.Devotion.satisfied == true"
```
**Exemplo real (Skarn, Fúria do Despertar):** o dano principal é
incondicional; só o efeito extra (imunidade a Ações Passivas) depende da
Devoção a Nyx-Vahl estar em campo:
```json
{
  "conditionToAct": [
    { "kind": "Devotion", "condition": { "filterType": "characterName", "value": "Nyx-Vahl, o Titã em Ruínas" } }
  ],
  "effects": [
    { "id": "e1", "action": "DAMAGE", "params": { "amount": 4, "combatFlags": [] }, "target": {...} },
    {
      "id": "e2",
      "action": "GRANT_KEYWORD",
      "params": { "keyword": "ImunidadeAAçõesPassivas", "duration": "permanent" },
      "target": { "scope": "self", "filter": {"filterType":"none"}, "selection": "fixed" },
      "condition": "conditionToAct.Devotion.satisfied == true"
    }
  ]
}
```

**Padrão geral para "valor X normalmente, valor Y se condição"** (ex. Sarien,
Julgamento da Mão: 3 de dano normalmente, 5 se Devoção a Ishmael satisfeita):
dois efeitos com `action` e `target` iguais, `amount` diferente, e condições
mutuamente exclusivas — sem introduzir um formato de "valor base + bônus
condicional" à parte:
```json
{
  "conditionToAct": [],
  "effects": [
    {
      "id": "e1", "action": "DAMAGE",
      "params": { "amount": 3, "combatFlags": [] },
      "target": {...},
      "condition": "!Devotion('Ishmael, o Último Serafim').satisfied"
    },
    {
      "id": "e2", "action": "DAMAGE",
      "params": { "amount": 5, "combatFlags": [] },
      "target": {...},
      "condition": "Devotion('Ishmael, o Último Serafim').satisfied"
    }
  ]
}
```
Nota: aqui a Devoção não é um gate de execução da ação (a ação funciona com ou
sem ela) — ela só altera o valor. Por isso `conditionToAct` na raiz fica
vazio, e a checagem de Devoção é referenciada ad-hoc dentro de `condition`
via `Devotion('<characterName>').satisfied`, distinto do caso de Skarn acima
(onde a Devoção *está* declarada em `conditionToAct`, ainda que só module um
efeito, porque ali ela é a mesma condição citada na regra Devoção(X) do
próprio glossário associada ao personagem como um todo).

---

## 8. `CHALLENGE` (Desafiar, ex-Aposte) e `CHOICE` (escolha entre efeitos)

**`CHALLENGE`** — nó especial no lugar de um efeito em `effects[]`:
```json
{
  "id": "e1",
  "action": "CHALLENGE",
  "params": { "rolls": 2, "requireMoreWinsThanLosses": true, "coinFlipStyle": false },
  "onWin":  [ /* lista de efeitos, 1..n */ ],
  "onLose": [ /* lista de efeitos, 1..n */ ]
}
```

`params.coinFlipStyle` (opcional, default `false`) — quando `true`, o
resultado de cada rolagem individual (cara/coroa) fica disponível para os
efeitos de `onWin`/`onLose` via `challenge.heads`/`challenge.tails` (contagem
de cada face entre as `rolls` rolagens), em vez de só o resultado agregado
vitória/derrota. Distinto do caso padrão, em que só `onWin`/`onLose` importam
e o placar interno da rolagem é irrelevante ao efeito. Caso real: Draug-Nol,
Golpe Instável ("se ganhar, +1 de dano para cada cara; se perder, -1 de dano
para cada coroa"):
```json
{
  "id": "e1", "action": "CHALLENGE",
  "params": { "rolls": 3, "requireMoreWinsThanLosses": false, "coinFlipStyle": true },
  "onWin":  [{ "action": "DAMAGE", "params": { "amount": "3 + challenge.heads", "combatFlags": [] }, "target": {...} }],
  "onLose": [{ "action": "DAMAGE", "params": { "amount": "3 - challenge.tails", "combatFlags": [] }, "target": {...} }]
}
```

**`CHOICE`** — escolha de N entre M efeitos:
```json
{
  "id": "e1",
  "action": "CHOICE",
  "chooseCount": 2,
  "options": [
    { "action": "DAMAGE", "params": {...}, "target": {...} },
    { "action": "DRAW", "params": {...}, "target": {...} },
    { "action": "TOKEN", "params": {...}, "target": {...} },
    { "action": "TOKEN", "params": {...}, "target": {...} }
  ]
}
```

**Exemplo real completo — Zaphiel, Juízo Silencioso:**
```json
{
  "conditionToAct": [],
  "trigger": "Active",
  "onceKind": "none",
  "effects": [
    {
      "id": "e1",
      "action": "CHALLENGE",
      "params": { "rolls": 2, "requireMoreWinsThanLosses": true },
      "onWin": [
        {
          "action": "DAMAGE",
          "params": { "amount": 8, "combatFlags": [] },
          "target": { "scope": "singleCharacter", "filter": {"filterType":"none"}, "selection": "choice", "count": 1 }
        },
        {
          "action": "TOKEN",
          "params": { "tokenType": "Silencio", "amount": 1 },
          "target": { "scope": "self", "filter": {"filterType":"none"}, "selection": "fixed", "count": 1 }
        }
      ],
      "onLose": [
        {
          "action": "TOKEN",
          "params": { "tokenType": "DefDown", "amount": 5 },
          "target": { "scope": "self", "filter": {"filterType":"none"}, "selection": "fixed", "count": 1 }
        }
      ]
    }
  ]
}
```

**Exemplo real — Manualidade e Esmero, Golpe do Ofício (escolha 2 de 4):**
```json
{
  "conditionToAct": [],
  "trigger": "Active",
  "onceKind": "none",
  "effects": [
    {
      "id": "e1",
      "action": "CHOICE",
      "chooseCount": 2,
      "options": [
        { "action": "DAMAGE", "params": { "amount": 1, "combatFlags": [] },
          "target": { "scope": "singleCharacter", "filter": {"filterType":"none"}, "selection": "choice", "count": 1 } },
        { "action": "DRAW", "params": { "amount": 1, "deck": "own" },
          "target": { "scope": "self", "filter": {"filterType":"none"}, "selection": "fixed" } },
        { "action": "TOKEN", "params": { "tokenType": "AtkUp", "amount": 1 },
          "target": { "scope": "singleCharacter", "filter": {"filterType":"none"}, "selection": "choice", "count": 1 } },
        { "action": "TOKEN", "params": { "tokenType": "AtkDown", "amount": 1 },
          "target": { "scope": "singleCharacter", "filter": {"filterType":"none"}, "selection": "choice", "count": 1 } }
      ]
    }
  ]
}
```

---

## 9. Custos: `additionalCosts[]` / `optionalCosts[]`

Mesma forma de nó que `effects[]` (ação completa, tabela da seção 4.1),
posicionados em arrays próprios na raiz do `effectScript`. Efeitos em
`effects[]` referenciam um custo opcional pelo seu `id`:

```json
{
  "additionalCosts": [
    { "id": "c1", "action": "DISCARD", "params": { "amount": 1, "chosenBy": "self" }, "target": { "scope": "self", "filter": {"filterType":"none"}, "selection": "fixed" } }
  ],
  "optionalCosts": [
    { "id": "c2", "action": "DISCARD", "params": { "amount": 1, "chosenBy": "self" }, "target": { "scope": "self", "filter": {"filterType":"none"}, "selection": "fixed" } }
  ],
  "effects": [
    {
      "id": "e1",
      "action": "DAMAGE",
      "params": {
        "amount": 2,
        "combatFlags": [
          { "flag": "DanoPerfurante", "requiresOptionalCost": "c2" },
          { "flag": "Transpassar", "requiresOptionalCost": "c2" }
        ]
      },
      "target": { "scope": "singleCharacter", "filter": {"filterType":"none"}, "selection": "choice", "count": 1 }
    }
  ]
}
```

Padrão para "custo opcional habilita propriedade extra num efeito já
existente" (ex.: ação de custo 2 causa 2 de dano; pagando Extinguir Recurso(1)
como opcional, o dano ganha Dano Perfurante + Transpassar): as `combatFlags`
do efeito de dano carregam individualmente seu `requiresOptionalCost` — o
efeito nasce único (`e1`), e as flags condicionais só se aplicam na resolução
se o custo referenciado (`c2`) tiver sido pago. Não existe efeito "fantasma"
nem referência cruzada entre dois efeitos de dano.

`Extinguir Fonte` e `Extinguir Recurso` (ações `DESTROY`/`DISCARD`, seção 4.1)
podem aparecer em `effects[]`, `additionalCosts[]`, ou `optionalCosts[]`
indistintamente — a mesma ação primitiva; a posição na árvore é que define se
ela é custo obrigatório, custo opcional, ou efeito autônomo da carta.

**Padrão alternativo — custo opcional habilita um efeito inteiro (não só uma
combatFlag)**: quando o texto liga o pagamento de um custo opcional a um
*efeito adicional completo* em vez de só uma propriedade de dano, o segundo
efeito usa `condition: "<id-do-custo>.paid == true"`, referenciando
diretamente se aquele `optionalCosts[]` foi pago — paralelo a
`requiresOptionalCost` (seção 5), mas no nível de `effects[].condition` em
vez de dentro de `combatFlags`. Caso real (Treino de Fim de Tarde): dano
base incondicional + 2 de dano adicional apenas se Extinguir Fonte(2) foi
pago como custo opcional:
```json
{
  "optionalCosts": [
    { "id": "c1", "action": "DESTROY", "params": { "scope": "energySource", "amount": 2 }, "target": {...} }
  ],
  "effects": [
    { "id": "e1", "action": "DAMAGE", "params": { "amount": 2, "combatFlags": [] }, "target": {...} },
    { "id": "e2", "action": "DAMAGE", "params": { "amount": 2, "combatFlags": [] }, "target": {...}, "condition": "c1.paid == true" }
  ]
}
```

**Campo `variable` em custos com quantidade `"X"`**: quando `additionalCosts[]`/
`optionalCosts[]` usa uma quantidade `"X"` escolhida pelo controlador no
momento do pagamento (em vez de um valor fixo), o nó de custo carrega
`"variable": true` junto ao `amount: "X"`, deixando explícito que `X` não é
uma expressão calculada (seção 4.4) mas um valor livre escolhido ali, e cujo
significado exato é resolvido por `linkedToCost` em qualquer `combatFlag` ou
`condition` que o referencie (seção 5). Caso real: Coroa Reforjada (Ishara
da Coroa Fundida) — `Reaproveitar Recursos (X)`:
```json
{ "id": "c1", "action": "RETURN_TO_HAND", "params": { "scope": "item", "amount": "X", "from": "discard", "variable": true }, "target": {...} }
```

---

## 10. `SET_STATE` — Stance como estado, não efeito de carta

`Stance` é modelado como um campo de estado do personagem em jogo (fora do
`effectScript`, no modelo de `CharacterInstance` do motor), não como um efeito
recorrente:
```json
{ "currentStance": "NomeDaStance | null" }
```
O efeito `SET_STATE{ state: "Stance", value: "X" }` é o único elo entre
`effectScript` e essa mudança de estado. Enquanto `currentStance != null`, o
motor restringe as `actions[]` disponíveis do personagem àquelas marcadas com
a mesma tag de Stance (ver seção 11 — ainda reservado, sem exemplo real).

---

## 11. `combo` — reservado (Starter/Combo/Finisher/Tag/Stance)

Sem exemplos reais no set atual. Placeholder na raiz do `effectScript`:
```json
"combo": null
```
Quando surgir a primeira carta real que use uma dessas dinâmicas, a estrutura
deve ser construída reaproveitando os nós já definidos nesta taxonomia
(`SEARCH` para a busca no deck, `onceKind` para a limitação de uso,
`CHALLENGE` para o custo Desafiar(1) embutido em Starter/Finisher) — não
modelar em detalhe antes de haver um caso concreto para validar contra.

---

## Referência rápida — enums fechados

| Enum | Valores |
|---|---|
| `conditionToAct[].kind` | `Devotion \| Affinity \| Concentration \| Dependency` |
| `trigger` | `OnEnter \| Passive \| OnAttach \| OnDetach \| OnEvent \| null` |
| `onceKind` | `turn \| combat \| game \| none` |
| `target.scope` | `self \| singleCharacter \| allAllies \| allEnemies \| eachController \| opponentController \| allCharacters \| attachedCharacter` |
| `target.filter.filterType` | `worldType \| category \| characterName \| none` |
| `target.selection` | `fixed \| choice \| random` |
| `resolvesAt` | `immediate \| endOfCombatPhase \| endOfTurn \| endOfCleanupPhase` |
| `effects[].action` | ver tabela 4.1 |
| `STATIC_MODIFIER.affects` | `actionCost \| stat \| rule \| combatFlag` |
| `STATIC_MODIFIER.filter.filterType` | `hasKeyword \| hasAdditionalCost \| worldType \| category \| actionCategory \| none` (namespace próprio, distinto de `target.filter.filterType`) |
| `CHALLENGE.params.coinFlipStyle` | boolean, default `false` |
| custo `variable` (`additionalCosts[]`/`optionalCosts[]`) | boolean, default `false` — marca quantidade `"X"` escolhida no pagamento |
| `STATIC_MODIFIER.appliesTo` | `allMatching \| nextAction` |
| `DISCARD.wholeHand` | boolean, default `false` |
| `TOKEN.removable` | boolean, default `true` |
| `GRANT_KEYWORD.freeCost` | boolean, default `false` |
| `target.excludeController` | boolean, default `false` |
