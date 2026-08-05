# Multiversity Conquest — Schema de Cartas (v1)

> Fonte de verdade estrutural dos dados de carta. Consumido pelo banco de cartas,
> deck builder, catálogo e motor de batalha. Não redefine regras — apenas
> formaliza em dados o que `rules.md` e `colecao-inicial.md` já definem.
>
> `effectScript` é um **objeto estruturado** (JSON), presente nos 3 tipos de
> carta. A taxonomia completa vive em documento irmão:
> `effectscript-taxonomy-v1.md`. `null` apenas quando a carta não tem efeito
> algum (ex. Fonte Básica pura, item sem texto de regra).

---

## Campos-base (presentes em toda carta, dos 3 tipos)

| Campo | Tipo | Descrição |
|---|---|---|
| `id` | string | Identificador alfanumérico único da carta |
| `slug` | string | Mesmo padrão do nome de arquivo em `Arte`, ex: `fonte-poco-do-silencio-inicial-v1` |
| `set` | string | Slug da coleção/expansão, ex: `fratura-do-multiverso` |
| `version` | string | Ex: `v1` |
| `rarity` | enum | `Comum \| Incomum \| Rara \| Super-rara \| Ultra-rara` |
| `assetRef` | string | Nome do arquivo de imagem, ex: `fonte-poco-do-silencio-inicial-v1.png` |
| `flavorText` | string \| null | Texto de flavor/lore, quando existir separado do efeito mecânico |
| `cardText` | string\| null | Texto base que será apresentado no corpo da carta. Em Personagem, consolida o texto em prosa de todas as `actions[]` no formato `**Nome** — corpo`, separado por linha em branco; o corpo de cada ação também vive individualmente em `actions[].description` — texto renderizável, não substitui `effectScript` estruturado |

```json
{
  "id": "",
  "slug": "",
  "set": "",
  "version": "",
  "rarity": "",
  "assetRef": "",
  "flavorText": null,
  "cardText": ""
}
```

---

## 1. Fonte de Energia

| Campo | Tipo | Descrição |
|---|---|---|
| `cardType` | const | `"FonteDeEnergia"` |
| `name` | string | Nome da carta |
| `type` | enum | `Básica \| Avançada \| Prismática` |
| `worldAffinity` | string \| null | Tipagem: `Magia\|TecSci\|Físico\|Divino\|Cósmico`. Preenchido mesmo em Básicas (usado por Prismáticas para energia extra por tipagem coincidente). `null` só quando genérica. |
| `worldName` | string \| null | Nome de lore do mundo (ex. `Silenteia`). `null` quando genérica — não existe "mundo genérico", é o multiverso. |
| `isLegendary` | boolean | Cópia única no deck |
| `isEpic` | boolean | Cópia única + removida do jogo se for ao descarte |
| `energyGenerated` | integer (0-99) | Quantidade de energia gerada. Base de cálculo para efeitos (ex. bônus de Prismática) |
| `effectScript` | object \| null | Efeito mecânico estruturado — ver `effectscript-taxonomy-v1.md`. `null` em Fontes Básicas sem efeito |
| `designNotes` | string \| null | Observação de design (equivalente à coluna `Observação` do CSV) |

```json
{
  "cardType": "FonteDeEnergia",
  "name": "",
  "type": "",
  "worldAffinity": null,
  "worldName": null,
  "isLegendary": false,
  "isEpic": false,
  "energyGenerated": 0,
  "effectScript": null,
  "designNotes": null
}
```

---

## 2. Item

| Campo | Tipo | Descrição |
|---|---|---|
| `cardType` | const | `"Item"` |
| `name` | string | Nome da carta |
| `category` | enum | `Volátil \| Permanente \| Anexável` |
| `worldAffinity` | string \| null | Tipagem, `null` se genérico |
| `worldName` | string \| null | Nome de lore do mundo, `null` se genérico |
| `isLegendary` | boolean | Cópia única no deck |
| `isEpic` | boolean | Cópia única + removida do jogo se for ao descarte |
| `energyCost` | const | Sempre `0` — itens não custam energia (limitação é por uso/turno, via regras) |
| `attachRequirement` | object \| null | Preenchido **apenas** quando `category = "Anexável"`. Ver 3 formatos abaixo |
| `effectScript` | object \| null | Efeito mecânico estruturado — ver `effectscript-taxonomy-v1.md` |
| `designNotes` | string \| null | Observação de design |

**Formatos de `attachRequirement`** (`target` define o que `value` significa):
```json
{ "target": "worldType",     "value": "Magia" }
{ "target": "category",      "value": "Silenteia Vívida" }
{ "target": "any-character", "value": null }
```

```json
{
  "cardType": "Item",
  "name": "",
  "category": "",
  "worldAffinity": null,
  "worldName": null,
  "isLegendary": false,
  "isEpic": false,
  "energyCost": 0,
  "attachRequirement": null,
  "effectScript": null,
  "designNotes": null
}
```

---

## 3. Personagem

| Campo | Tipo | Descrição |
|---|---|---|
| `cardType` | const | `"Personagem"` |
| `name` | string | Nome da carta |
| `category` | string \| null | Classificação/agrupamento (clã, tribo) — **não** é raridade nem Lendário/Épico |
| `worldType` | string | Tipagem: `Magia\|TecSci\|Físico\|Divino\|Cósmico` |
| `weakness` | string \| null | Tipagem à qual é fraco. `null` para Divino/Cósmico |
| `resistance` | string \| null | Tipagem à qual é resistente. `null` para Divino/Cósmico |
| `stats.life` | integer (1-12) | Vida — define patamar de Power System |
| `stats.defense` | integer | Defesa |
| `actions` | array | Lista de ações (0 a 2). Ver estrutura abaixo |

**Estrutura de cada item em `actions[]`:**

| Campo | Tipo | Descrição |
|---|---|---|
| `name` | string | Nome da ação |
| `description` | string | Texto em prosa do efeito desta ação (equivalente ao corpo de `Descrição P`/`Descrição A` do CSV, já sem o prefixo `**Nome** —`) — texto renderizável por ação, não substitui o `effectScript` estruturado |
| `kind` | enum | `Passive \| Active` |
| `energyCost` | integer (≥0) | Custo em energia (pode ser 0, inclusive em passivas) |
| `keywords` | array de string | Restrições posicionais/de uso que não afetam o cálculo do efeito em si — ex. `Flanquear`, `Liderança`, `Sidekick`, `Posição Estratégia`, `Posição Vantajosa`. Controlam **quando/quem** pode usar a ação, não o que ela faz — por isso ficam fora do `effectScript` (ver `effectscript-taxonomy-v1.md`, nota na seção 8) |
| `effectScript` | object | Efeito mecânico estruturado — ver `effectscript-taxonomy-v1.md` para a árvore completa (`conditionToAct`, `trigger`, `onceKind`, `additionalCosts`, `optionalCosts`, `effects`, `combo`) |

`additionalCosts`/`optionalCosts` vivem **dentro** de `effectScript` (raiz do
objeto), não como irmãos de `energyCost` — mesma forma de nó usada em
`effects[]` (ver taxonomia, seções 4 e 9). Isso mantém uma única fonte de
verdade para "o que uma ação faz e custa", reutilizável por Fonte, Item e
Personagem sem duplicar estrutura.

```json
{
  "cardType": "Personagem",
  "name": "",
  "category": null,
  "worldType": "",
  "weakness": null,
  "resistance": null,
  "stats": { "life": 0, "defense": 0 },
  "actions": [
    {
      "name": "",
      "description": "",
      "kind": "",
      "energyCost": 0,
      "keywords": [],
      "effectScript": {
        "conditionToAct": [],
        "trigger": null,
        "onceKind": "none",
        "additionalCosts": [],
        "optionalCosts": [],
        "effects": [],
        "combo": null
      }
    }
  ]
}
```

---

## Notas de status

- Os 3 schemas acima (campos-base + miolo mecânico de cada tipo) estão
  **travados como v1**.
- `effectScript` é **estruturado** (JSON), não string livre — a taxonomia
  completa está em `effectscript-taxonomy-v1.md`, documento irmão deste.
  Mesma estrutura de `effectScript` se aplica a Fonte de Energia e Item (nos
  respectivos schemas acima), não só a Personagem.
- `additionalCosts[]`/`optionalCosts[]` ficam dentro de `effectScript`, não
  como campo próprio da `action` — validado contra exemplos reais (Nyx-Vahl,
  Colapso Parcial) em `personagens-validacao-schema.md`.
- `description` na `action` de Personagem é campo novo: permite renderizar cada
  ação isoladamente (nome + custo + texto) sem precisar reparsear o `cardText`
  consolidado, que continua existindo como texto integral da carta.
- `keywords[]` na `action` de Personagem é campo novo, adicionado após
  validação contra cartas reais (Bragan, Recorde Inquebrável — palavra-chave
  `Flanquear`) — cobre restrições de posição/uso que não são parte do cálculo
  do efeito.
