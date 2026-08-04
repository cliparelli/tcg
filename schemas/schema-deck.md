# Multiversity Conquest — Schema de Deck (v1)

> Fonte de verdade estrutural dos dados de deck. Consumido pelo deck builder,
> pelo motor de batalha (setup de partida) e pela config de fase PVE (via
> `deckId`). Não redefine regras de montagem — apenas formaliza em dados o
> que `rules.md` (seção "Montagem do Deck") já define. A validação das regras
> de montagem (60 cartas, mínimos, limites de cópia) é função separada
> (`ValidateDeck`), não parte do schema em si — um deck incompleto/rascunho
> pode existir na base sem violar este schema.
>
> Um deck **referencia** cartas por `cardId` — não duplica dados de carta.
> A carta em si é definida em `schema-cartas.md`.

---

## Campos-base

| Campo | Tipo | Descrição |
|---|---|---|
| `id` | string | Identificador único do deck |
| `slug` | string | Nome amigável para URL/arquivo, ex: `mono-destacamento-interregno-sentinelas` |
| `name` | string | Nome de exibição do deck |
| `description` | string \| null | Prosa livre — texto narrativo de estratégia/lore do deck (equivalente ao corpo do `.md` de referência). Informação contextual para humanos, não lida pelo motor |
| `format` | enum | Marca regulatória do deck. `standard` é o único formato v1 confirmado. Campo existe desde já para não exigir migração quando outros formatos ("Outras formas de jogar": Minions, Master of Dungeons, Monoverse) forem definidos. **Pendente:** revisão completa das marcas regulatórias em documento próprio |

```json
{
  "id": "",
  "slug": "",
  "name": "",
  "description": null,
  "format": "standard"
}
```

---

## `strategy` — sumário estratégico estruturado

Resumo do deck legível por máquina — não substitui `description`, complementa
com campos curtos e de vocabulário fechado que a IA de PVE e o deck builder
podem consultar sem NLP sobre a prosa livre.

| Campo | Tipo | Descrição |
|---|---|---|
| `archetype` | string \| null | Etiqueta livre de arquétipo geral (ex. `Control`, `Aggro`, `Stall`, `Combo`) |
| `winCondition` | enum \| null | `attrition \| burst \| tempo \| combo \| stall` |
| `keyMechanics` | array de string | Palavras-chave do glossário oficial (`rules.md`) que definem o deck, ex: `["Escudo", "Alvos Múltiplos (X)", "Devoção (Sentinelas)"]` |
| `coreCombo` | object \| null | O combo central do deck, se houver — ver estrutura abaixo |
| `tempo` | enum \| null | `fast \| medium \| slow` — heurística de agressividade/velocidade pretendida, útil para perfis de IA calibrarem postura |

**Estrutura de `coreCombo`:**

| Campo | Tipo | Descrição |
|---|---|---|
| `description` | string | Explicação curta em prosa de como o combo funciona |
| `cardIds` | array de string | `cardId` de cada carta envolvida no combo |

```json
{
  "strategy": {
    "archetype": null,
    "winCondition": null,
    "keyMechanics": [],
    "coreCombo": null,
    "tempo": null
  }
}
```

---

## `entries[]` — deck principal

Lista de cartas do deck principal. Cada item referencia uma carta por
`cardId` (não embute os dados da carta).

| Campo | Tipo | Descrição |
|---|---|---|
| `cardId` | string | Referência ao `id`/`slug` do schema de carta |
| `quantity` | integer | Número de cópias no deck |
| `suggestedRole` | enum \| null | `leader \| team \| null` — sugestão de composição para Personagens (equivalente à posição Principal/Time). **Não é regra**: a posição real é decidida dinamicamente durante a partida; serve de dica para deck builder/UI e para a IA de PVE saber a composição pretendida pelo deck |
| `suggestedPlay` | string \| null | Dica de uso pretendido para Itens (ex. `"attach-early"`, `"hold-until-critical"`, `"target-main-position"`) — string livre por ora, ajuda IAs simples a jogar melhor uma carta cujo melhor momento de uso não é óbvio só pelo `effectScript`. Candidato a virar enum fechado no futuro, como `winCondition`/`tempo` |
| `designNotes` | string \| null | Nota livre de design/arquétipo por carta (ex. `"Control"`, `"Sobrevida"`) — documentação, ignorada pelo motor |

```json
{
  "entries": [
    {
      "cardId": "",
      "quantity": 0,
      "suggestedRole": null,
      "suggestedPlay": null,
      "designNotes": null
    }
  ]
}
```

---

## `sideboard[]`

Até 15 cartas auxiliares trocáveis pelas cartas do deck principal (ver
`rules.md` > Sideboard). Estrutura simplificada — mesmos campos-base de
`entries[]`, sem os metadados de sugestão de jogo (não fazem sentido para
cartas fora do deck ativo). **Opcional**: lista vazia é válida e comum.

| Campo | Tipo | Descrição |
|---|---|---|
| `cardId` | string | Referência ao `id`/`slug` do schema de carta |
| `quantity` | integer | Número de cópias no sideboard |

```json
{
  "sideboard": [
    { "cardId": "", "quantity": 0 }
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
  "format": "standard",
  "strategy": {
    "archetype": null,
    "winCondition": null,
    "keyMechanics": [],
    "coreCombo": null,
    "tempo": null
  },
  "entries": [
    {
      "cardId": "",
      "quantity": 0,
      "suggestedRole": null,
      "suggestedPlay": null,
      "designNotes": null
    }
  ],
  "sideboard": []
}
```

---

## Notas de status

- Schema **travado como v1**.
- `entries[]`/`sideboard[]` referenciam cartas por `cardId` — nunca embutem
  dados de carta. Única fonte de verdade de carta é `schema-cartas.md`.
- `suggestedRole`/`suggestedPlay` são **sugestões de composição/uso**, não
  regras de jogo — a posição de campo (Principal/Time) e o momento de uso de
  um item continuam 100% dinâmicos durante a partida, conforme `rules.md`.
- `winCondition` e `tempo` são enums fechados (não string livre), diferente
  de `archetype` e `suggestedPlay`, que permanecem livres por ora.
- `rewardStones` (quantidade de Pedras de Recompensa) **não é campo do
  deck** — pertence à config de partida/fase PVE, decidido separadamente.
- `format` hoje só tem o valor `"standard"` confirmado. A revisão completa
  de marcas regulatórias (hoje na "Marca A") está pendente, a ser tratada
  em conversa/documento próprio.
- Regras de montagem (60 cartas, mín. 12 Personagens, mín. 12 Fontes de
  Energia, limites de cópia, Lendária/Épica = 1 cópia, sideboard ≤ 15) são
  verificadas por `ValidateDeck` (`validate_deck.py`), função separada do
  schema — um deck no banco pode estar incompleto sem violar este documento.
  Validado com sucesso (0 erros, 0 avisos) contra o deck de referência
  `deck-destacamento-sentinelas.json` (60/60 cartas, todos os limites
  respeitados).
