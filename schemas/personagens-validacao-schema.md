# Validação de Schema — 6 Personagens

> Objetivo: testar `schema-cartas.md` (estrutura da carta) e
> `effectscript-taxonomy-v1.md` (estrutura do efeito) contra texto real de
> carta, incluindo casos ainda não cobertos por exemplo (recoil, Ataque
> Expansível com custo variável ligado a X, imunidade a Ações Passivas,
> condição de evento de fase).

---

## 1. Dorhen, Guarda da Última Miragem

Texto fonte: Passiva — "No final de sua fase de limpeza, adicione um token de
Escudo, neste personagem." Ativa (custo 3) — "Cause 4 pontos de dano ao alvo.
Se algum token foi removido deste personagem durante a fase de limpeza deste
turno, coloque um token de Invisibilidade no fim da fase de combate."

```json
{
  "id": "p006",
  "slug": "personagem-dorhen-guarda-da-ultima-miragem-v1",
  "set": "fratura-do-multiverso",
  "cardType": "Personagem",
  "name": "Dorhen, Guarda da Última Miragem",
  "version": "v1",
  "rarity": "Rara",

  "category": "Punhos Vazios",
  "worldType": "Magia",
  "weakness": "TecSci",
  "resistance": "Físico",

  "stats": { "life": 6, "defense": 4 },

  "actions": [
    {
      "name": "Guarda Invisível",
      "kind": "Passive",
      "energyCost": 0,
      "additionalCosts": [],
      "optionalCosts": [],
      "effectScript": {
        "conditionToAct": [],
        "trigger": "OnEvent",
        "onceKind": "none",
        "additionalCosts": [],
        "optionalCosts": [],
        "effects": [
          {
            "id": "e1",
            "action": "TOKEN",
            "params": { "tokenType": "Escudo", "amount": 1 },
            "target": { "scope": "self", "filter": {"filterType":"none"}, "selection": "fixed", "count": 1 },
            "condition": "phase.current == 'Cleanup' && phase.owner == controller"
          }
        ],
        "combo": null
      },
      "designNotes": "Efeito dispara no fim da própria fase de limpeza do controlador — 'OnEvent' com condição de fase, não 'Passive' contínuo puro, já que o gatilho é pontual (uma vez por fase de limpeza), não um estado permanente."
    },
    {
      "name": "Golpe da Fronteira",
      "kind": "Active",
      "energyCost": 3,
      "additionalCosts": [],
      "optionalCosts": [],
      "effectScript": {
        "conditionToAct": [],
        "trigger": "Active",
        "onceKind": "none",
        "additionalCosts": [],
        "optionalCosts": [],
        "effects": [
          {
            "id": "e1",
            "action": "DAMAGE",
            "params": { "amount": 4, "combatFlags": [] },
            "target": { "scope": "singleCharacter", "filter": {"filterType":"none"}, "selection": "choice", "count": 1 }
          },
          {
            "id": "e2",
            "action": "TOKEN",
            "params": { "tokenType": "Invisibilidade", "amount": 1 },
            "target": { "scope": "self", "filter": {"filterType":"none"}, "selection": "fixed", "count": 1 },
            "condition": "self.tokensRemovedThisCleanup.count > 0",
            "resolvesAt": "endOfCombatPhase"
          }
        ],
        "combo": null
      },
      "designNotes": "'resolvesAt' é um campo novo de timing de resolução (o token só é colocado no fim da fase de combate, não imediatamente ao resolver a ação) — ver pergunta em aberto #1 ao final do documento."
    }
  ],

  "assetRef": "personagem-dorhen-guarda-da-ultima-miragem-v1.png",
  "flavorText": null,
  "designNotes": "Patamar Esportista no topo da faixa (Vida 6, Defesa 4 — a mais alta entre os Esportistas de Silenteia). Passiva reforça perfil defensivo com Escudo recorrente. Ativa é dano Médio-Alto direto (4, custo 3), sem condicionais, já que a identidade de Dorhen é resiliência, não debuff. Efeito determinístico, sem Desafiar."
}
```

---

## 2. Bragan, o Recorde Imbatível

Texto fonte: Passiva — "Este personagem não pode ser alvo de ações que
reduzam seu ataque ou defesa." Ativa (custo 4) — "Flanquear - Ignorar Defesa -
esta ação causa 6 pontos de dano ao alvo."

```json
{
  "id": "p040",
  "slug": "personagem-bragan-o-recorde-imbativel-v1",
  "set": "fratura-do-multiverso",
  "cardType": "Personagem",
  "name": "Bragan, o Recorde Imbatível",
  "version": "v1",
  "rarity": "Super-rara",

  "category": "Titãs Comunais",
  "worldType": "Físico",
  "weakness": "Magia",
  "resistance": "TecSci",

  "stats": { "life": 9, "defense": 6 },

  "actions": [
    {
      "name": "Patrimônio Vivo",
      "kind": "Passive",
      "energyCost": 0,
      "additionalCosts": [],
      "optionalCosts": [],
      "effectScript": {
        "conditionToAct": [],
        "trigger": "Passive",
        "onceKind": "none",
        "additionalCosts": [],
        "optionalCosts": [],
        "effects": [
          {
            "id": "e1",
            "action": "GRANT_KEYWORD",
            "params": { "keyword": "ImunidadeATokens", "tokenTypes": ["AtkDown", "DefDown"], "duration": "permanent" },
            "target": { "scope": "self", "filter": {"filterType":"none"}, "selection": "fixed", "count": 1 }
          }
        ],
        "combo": null
      },
      "designNotes": "Imunidade específica a dois tipos de token (AtkDown/DefDown), não imunidade geral a Debuffs — usa 'GRANT_KEYWORD' com lista de tokens-alvo em vez de um token de imunidade genérico, para não confundir com 'Imune a Efeitos Negativos' (que bloqueia todo o espectro negativo)."
    },
    {
      "name": "Recorde Inquebrável",
      "kind": "Active",
      "energyCost": 4,
      "additionalCosts": [],
      "optionalCosts": [],
      "effectScript": {
        "conditionToAct": [],
        "trigger": "Active",
        "onceKind": "combat",
        "additionalCosts": [],
        "optionalCosts": [],
        "effects": [
          {
            "id": "e1",
            "action": "DAMAGE",
            "params": { "amount": 6, "combatFlags": [ { "flag": "IgnorarDefesa" } ] },
            "target": { "scope": "singleCharacter", "filter": {"filterType":"none"}, "selection": "choice", "count": 1 }
          }
        ],
        "combo": null
      },
      "keywords": ["Flanquear"],
      "designNotes": "'Flanquear' tratado como restrição posicional/de timing da própria ação (metadado em 'keywords', paralelo a Liderança/Sidekick), não como combatFlag de DAMAGE — Flanquear não modifica o cálculo de dano em si, apenas quando/quem pode usar a ação. Ver pergunta em aberto #2."
    }
  ],

  "assetRef": "personagem-bragan-o-recorde-imbativel-v1.png",
  "flavorText": null,
  "designNotes": "Patamar Sobre-humano no topo da faixa (Vida 9 de 8-10), o mais forte de Solar Comum. Passiva cobre baixa dependência de suporte (resiliência própria contra debuff de Atk). Ativa cobre Transpassar em sua forma mais extrema (Ignorar Defesa) com dano Alto (6, custo 4)."
}
```

---

## 3. Skarn da Fúria Acordada

Texto fonte: Passiva — "Sempre que este personagem causar dano a outro
personagem, ele também sofrerá esta mesma quantidade de dano." Ativa (custo
2) — "Cause 4 pontos de dano ao personagem alvo." mais uma cláusula de
Devoção: "Devoção (Nyx-Vahl, o Titã em Ruínas) - Este personagem recebe
Imunidade à Ações Passivas (incluindo a sua própria)."

```json
{
  "id": "p103",
  "slug": "personagem-skarn-da-furia-acordada-v1",
  "set": "fratura-do-multiverso",
  "cardType": "Personagem",
  "name": "Skarn da Fúria Acordada",
  "version": "v1",
  "rarity": "Rara",

  "category": "Tempestades Primordiais",
  "worldType": "Cósmico",
  "weakness": null,
  "resistance": null,

  "stats": { "life": 6, "defense": 3 },

  "actions": [
    {
      "name": "Rachaduras Abertas",
      "kind": "Passive",
      "energyCost": 0,
      "additionalCosts": [],
      "optionalCosts": [],
      "effectScript": {
        "conditionToAct": [],
        "trigger": "OnEvent",
        "onceKind": "none",
        "additionalCosts": [],
        "optionalCosts": [],
        "effects": [
          {
            "id": "e1",
            "action": "DAMAGE",
            "params": { "amount": "event.damageDealt", "combatFlags": [] },
            "target": { "scope": "self", "filter": {"filterType":"none"}, "selection": "fixed", "count": 1 },
            "condition": "event.type == 'characterDealtDamage' && event.source == self"
          }
        ],
        "combo": null
      },
      "designNotes": "Recoil de valor espelhado (não fixo): 'params.amount' referencia o valor do evento gatilho em vez de um número fixo — ver pergunta em aberto #3 sobre expressão de valor dinâmico dentro de params."
    },
    {
      "name": "Fúria do Despertar",
      "kind": "Active",
      "energyCost": 2,
      "additionalCosts": [],
      "optionalCosts": [],
      "effectScript": {
        "conditionToAct": [
          { "kind": "Devotion", "condition": { "filterType": "characterName", "value": "Nyx-Vahl, o Titã em Ruínas" } }
        ],
        "trigger": "Active",
        "onceKind": "none",
        "additionalCosts": [],
        "optionalCosts": [],
        "effects": [
          {
            "id": "e1",
            "action": "DAMAGE",
            "params": { "amount": 4, "combatFlags": [] },
            "target": { "scope": "singleCharacter", "filter": {"filterType":"none"}, "selection": "choice", "count": 1 }
          },
          {
            "id": "e2",
            "action": "GRANT_KEYWORD",
            "params": { "keyword": "ImunidadeAAçõesPassivas", "duration": "permanent" },
            "target": { "scope": "self", "filter": {"filterType":"none"}, "selection": "fixed", "count": 1 },
            "condition": "conditionToAct.Devotion.satisfied == true"
          }
        ],
        "combo": null
      },
      "designNotes": "Modelagem alternativa considerada: tratar a segunda parte (imunidade condicionada a Devoção) como um segundo 'conditionToAct' inteiro separado, em vez de um efeito com 'condition' apontando para a satisfação do gate. Optei por manter como efeito condicional dentro da mesma ação, já que o dano (e1) NÃO depende de Devoção — só o efeito extra (e2) depende. Ver pergunta em aberto #4."
    }
  ],

  "assetRef": "personagem-skarn-da-furia-acordada-v1.png",
  "flavorText": null,
  "designNotes": "Patamar Esportista (Vida 6, base 5-7). Passiva cobre recoil em toda ação de dano, reforçando a raiva autodestrutiva do personagem. Ativa é dano Alto direto e simples (4, custo 2), coerente com pouca sutileza."
}
```

---

## 4. Sarien Mão-Que-Julga

Texto fonte: Ativa (custo 2, sem passiva) — "Cause 3 pontos de dano ao alvo."
mais cláusula de Devoção: "Devoção (Ishmael, o Último Serafim) - Esta ação
causa 5 pontos de dano ao invés de 3."

```json
{
  "id": "p072",
  "slug": "personagem-sarien-mao-que-julga-v1",
  "set": "fratura-do-multiverso",
  "cardType": "Personagem",
  "name": "Sarien Mão-Que-Julga",
  "version": "v1",
  "rarity": "Rara",

  "category": "Mãos",
  "worldType": "Divino",
  "weakness": null,
  "resistance": null,

  "stats": { "life": 6, "defense": 4 },

  "actions": [
    {
      "name": "Julgamento da Mão",
      "kind": "Active",
      "energyCost": 2,
      "additionalCosts": [],
      "optionalCosts": [],
      "effectScript": {
        "conditionToAct": [],
        "trigger": "Active",
        "onceKind": "none",
        "additionalCosts": [],
        "optionalCosts": [],
        "effects": [
          {
            "id": "e1",
            "action": "DAMAGE",
            "params": { "amount": 3, "combatFlags": [] },
            "target": { "scope": "singleCharacter", "filter": {"filterType":"none"}, "selection": "choice", "count": 1 },
            "condition": "!conditionToAct.Devotion('Ishmael, o Último Serafim').satisfied"
          },
          {
            "id": "e2",
            "action": "DAMAGE",
            "params": { "amount": 5, "combatFlags": [] },
            "target": { "scope": "singleCharacter", "filter": {"filterType":"none"}, "selection": "choice", "count": 1 },
            "condition": "conditionToAct.Devotion('Ishmael, o Último Serafim').satisfied"
          }
        ],
        "combo": null
      },
      "designNotes": "Modelado como dois efeitos mutuamente exclusivos por condição, em vez de 'amount' variável com fórmula — mais legível e evita expressão aritmética dentro de params. Ver pergunta em aberto #5 sobre se este é o padrão correto para 'valor alterado por Devoção', dado que já vimos em Sarien um padrão diferente do de Skarn (efeito extra) — aqui é substituição de valor, não adição de efeito."
    }
  ],

  "assetRef": "personagem-sarien-mao-que-julga-v1.png",
  "flavorText": null,
  "designNotes": "Patamar Esportista (Vida 6, base 5-7). Sem passiva, ativa de dano Médio direto (3, custo 2), contrastando deliberadamente com Rafiel dentro da mesma categoria Mãos."
}
```

---

## 5. Mireia Passo-Rápido

Texto fonte: Ativa (custo 2, sem passiva) — "Cause 3 pontos de dano ao alvo.
Se esta foi a única ação utilizada este turno, causa 4 pontos de dano ao
invés de 3."

```json
{
  "id": "p043",
  "slug": "personagem-mireia-passo-rapido-v1",
  "set": "fratura-do-multiverso",
  "cardType": "Personagem",
  "name": "Mireia Passo-Rápido",
  "version": "v1",
  "rarity": "Rara",

  "category": "Recordes Vivos",
  "worldType": "Físico",
  "weakness": "Magia",
  "resistance": "TecSci",

  "stats": { "life": 5, "defense": 2 },

  "actions": [
    {
      "name": "Corrida Contra o Recorde",
      "kind": "Active",
      "energyCost": 2,
      "additionalCosts": [],
      "optionalCosts": [],
      "effectScript": {
        "conditionToAct": [],
        "trigger": "Active",
        "onceKind": "none",
        "additionalCosts": [],
        "optionalCosts": [],
        "effects": [
          {
            "id": "e1",
            "action": "DAMAGE",
            "params": { "amount": 3, "combatFlags": [] },
            "target": { "scope": "singleCharacter", "filter": {"filterType":"none"}, "selection": "choice", "count": 1 },
            "condition": "controller.actionsUsedThisTurn.count > 1"
          },
          {
            "id": "e2",
            "action": "DAMAGE",
            "params": { "amount": 4, "combatFlags": [] },
            "target": { "scope": "singleCharacter", "filter": {"filterType":"none"}, "selection": "choice", "count": 1 },
            "condition": "controller.actionsUsedThisTurn.count == 1"
          }
        ],
        "combo": null
      },
      "designNotes": "'controller.actionsUsedThisTurn.count == 1' assume que esta própria ação já conta no total (isto é, checagem ocorre incluindo-a). Mesmo padrão de dois efeitos mutuamente exclusivos usado em Sarien."
    }
  ],

  "assetRef": "personagem-mireia-passo-rapido-v1.png",
  "flavorText": null,
  "designNotes": "Patamar Esportista (Vida 5, base 5-7). Sem passiva, ativa de dano Médio condicionado a ser a única ação do turno (reforça velocidade/primazia)."
}
```

---

## 6. Nyx-Vahl, o Titã em Ruínas

Texto fonte: Passiva — "Sempre que este personagem causar dano a outro
personagem, ele também sofrerá esta mesma quantidade de dano." Ativa (custo
1) — "Ação Única - Ataque Expansível (2) - cause 4 pontos de dano ao alvo.
Extinguir Fonte (X) - Alvos Múltiplos (X)."

```json
{
  "id": "p098",
  "slug": "personagem-nyx-vahl-o-titan-em-ruinas-v1",
  "set": "fratura-do-multiverso",
  "cardType": "Personagem",
  "name": "Nyx-Vahl, o Titã em Ruínas",
  "version": "v1",
  "rarity": "Super-rara",

  "category": "Vastidão Titânica",
  "worldType": "Cósmico",
  "weakness": null,
  "resistance": null,

  "stats": { "life": 9, "defense": 7 },

  "actions": [
    {
      "name": "Rachaduras Abertas",
      "kind": "Passive",
      "energyCost": 0,
      "additionalCosts": [],
      "optionalCosts": [],
      "effectScript": {
        "conditionToAct": [],
        "trigger": "OnEvent",
        "onceKind": "none",
        "additionalCosts": [],
        "optionalCosts": [],
        "effects": [
          {
            "id": "e1",
            "action": "DAMAGE",
            "params": { "amount": "event.damageDealt", "combatFlags": [] },
            "target": { "scope": "self", "filter": {"filterType":"none"}, "selection": "fixed", "count": 1 },
            "condition": "event.type == 'characterDealtDamage' && event.source == self"
          }
        ],
        "combo": null
      },
      "designNotes": "Idêntico em estrutura à passiva de Skarn (mesmo nome, mesma redação) — reaproveita o mesmo padrão de recoil espelhado."
    },
    {
      "name": "Colapso Parcial",
      "kind": "Active",
      "energyCost": 1,
      "additionalCosts": [
        {
          "id": "c1",
          "action": "DESTROY",
          "params": { "scope": "energySource", "amount": "X", "variable": true },
          "target": { "scope": "self", "filter": {"filterType":"none"}, "selection": "fixed" }
        }
      ],
      "optionalCosts": [],
      "effectScript": {
        "conditionToAct": [],
        "trigger": "Active",
        "onceKind": "turn",
        "additionalCosts": [
          {
            "id": "c1",
            "action": "DESTROY",
            "params": { "scope": "energySource", "amount": "X", "variable": true },
            "target": { "scope": "self", "filter": {"filterType":"none"}, "selection": "fixed" }
          }
        ],
        "optionalCosts": [],
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
        ],
        "combo": null
      },
      "designNotes": "Caso mais complexo do lote: 'X' é uma variável compartilhada entre o custo (Extinguir Fonte(X), quantidade de Fontes descartadas) e o efeito (Alvos Múltiplos(X), mesma quantidade). 'linkedToCost' amarra o valor de 'X' na combatFlag ao valor efetivamente pago em 'c1' — ver pergunta em aberto #6, já que a taxonomia atual não tinha uma forma explícita para 'variável X compartilhada entre custo e efeito'. 'AtaqueExpansível(2)' é limite fixo (não X) e é independente do custo de Extinguir Fonte — motor real de energia anexada, não relacionado ao X desta ação."
    }
  ],

  "assetRef": "personagem-nyx-vahl-o-titan-em-ruinas-v1.png",
  "flavorText": null,
  "designNotes": "Patamar Sobre-humano no topo da faixa (Vida 9 de 8-10). Passiva cobre recoil (assinatura de Cósmico) em toda ação de dano. Ativa cobre ataque expansível clássico + custo/efeito variável ligado a X."
}
```

---

## Perguntas em aberto — casos não cobertos pela taxonomia v1

1. **Timing de resolução tardia** (Dorhen): o token de Invisibilidade só é
   colocado "no fim da fase de combate", não imediatamente ao resolver a
   ação. Proponho um campo opcional `resolvesAt` no efeito (`immediate` |
   `endOfCombatPhase` | `endOfTurn`), default `immediate`. Confirma a adição
   deste campo à taxonomia?

2. **`Flanquear` como restrição da ação, não combatFlag**: modelei como
   metadado `keywords: ["Flanquear"]` no nível da `action` (paralelo a
   Liderança/Sidekick, que também restringem *quem/quando* pode usar, não
   *como o dano é calculado*). Isso sugere que a `action` de Personagem
   (schema de carta, não o effectScript) precisa de um campo
   `positionRestriction` ou `keywords[]` que hoje não existe no
   `schema-cartas.md`. Confirma essa adição?

3. **Valor dinâmico em `params.amount`** (recoil de Skarn/Nyx-Vahl): usei a
   string `"event.damageDealt"` reaproveitando a mesma linguagem de
   `condition`, mas dentro de um campo que normalmente é um inteiro fixo.
   Isso funciona conceitualmente, mas quebra a expectativa de tipo
   (`amount: integer` vira `amount: integer | expression-string`). Prefere
   isso, ou um campo irmão explícito tipo `amountFormula` separado de
   `amount`, para não misturar os dois tipos no mesmo campo?

4. **Efeito condicionado à satisfação de um `conditionToAct` da mesma ação**
   (Skarn): usei `condition: "conditionToAct.Devotion.satisfied == true"`.
   Isso é um padrão novo (referenciar o próprio gate da ação dentro de um
   efeito individual). Faz sentido, ou prefere modelar diferente — por
   exemplo, duas ações Ativas separadas na carta (uma só disponível com
   Devoção satisfeita), o que não bate com "1 nome de ação só" que vimos no
   texto real?

5. **Valor substituído por Devoção** (Sarien: 3 dano vira 5 com Devoção) —
   modelei como dois efeitos mutuamente exclusivos com condições opostas.
   Alternativa seria um único efeito com `amount` "base + bônus condicional"
   (`baseAmount: 3, conditionalBonus: {condition: ..., amount: 2}`). A
   primeira forma (dois efeitos) é mais verbosa mas reaproveita only nós já
   existentes; a segunda é mais compacta mas introduz um novo formato de
   `params`. Qual prefere como padrão geral para "valor X normalmente, valor Y
   se condição"?

6. **Variável `X` compartilhada entre custo e efeito** (Nyx-Vahl, Colapso
   Parcial): este é o caso mais estruturalmente nada trivial do lote —
   `Extinguir Fonte(X)` no custo e `Alvos Múltiplos(X)` no efeito
   referenciam o **mesmo** valor escolhido pelo jogador ao pagar o custo.
   Propus `linkedToCost: "c1"` na combatFlag para amarrar isso. Confirma essa
   abordagem, ou prefere uma variável nomeada declarada explicitamente na
   raiz do `effectScript` (ex. `"variables": [{"name": "X", "boundBy": "c1"}]`)
   e referenciada por nome (`"limit": "$X"`) em qualquer lugar do script, o
   que escalaria melhor se X for usada em 3+ lugares numa carta futura?
