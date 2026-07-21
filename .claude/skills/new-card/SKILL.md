---
name: new-card
description: Gera uma ou mais cartas (Personagem, Item ou Fonte de Energia) para o TCG, seguindo o motor de regras de rules.md e diretrizes de design fornecidas pelo usuário (ex. um documento de coleção como features/colecao-inicial.md). Use quando o usuário pedir para criar, escrever, redigir ou popular cartas específicas — diferente de new-card-type (cria um tipo/afinidade novo) e new-deck (monta uma decklist a partir de cartas já existentes).
---

Gerar uma carta não é só inventar um efeito — é encaixar um personagem/item/fonte dentro dos limites que `rules.md` já estabelece e das diretrizes de design que o usuário forneceu (lore, palavras-chave de assinatura, cotas de raridade, distribuição de Power System etc., tipicamente documentadas em `features/*.md`). Siga este processo:

## 1. Reúna as diretrizes antes de escrever qualquer carta

Antes de gerar conteúdo, identifique:

- **Diretrizes de design do usuário.** Se o pedido referenciar (ou estiver no contexto de) um documento como `features/colecao-inicial.md`, releia-o: lore do tipo/mundo, palavras-chave de assinatura por tipo, distribuição por Power System, cota de itens (Épico/Lendário/Temático/genérico), nomes de personagens/Fontes já definidos. Não invente lore ou nomes que contradigam esse documento — use-o como fonte de verdade.
- **O motor de `rules.md`.** Releia as seções relevantes ao tipo de carta:
  - Personagens: seção `Personagens` + `Power System` (faixas de Vida → patamar de poder, ver tabela abaixo) + `Arquétipos dos Tipos de Personagem`.
  - Itens: seção `Itens` (Permanente/Volátil, e Anexável se o sistema em uso incluir esse subtipo).
  - Fontes de Energia: seção `Fontes de Energia` (Básica/Avançada/Prismática, se aplicável).
  - Todos os tipos: seção `Palavras-Chave` (Combate, Controle de Cartas, Dinâmicas de Jogo, Efeitos) — não invente uma palavra-chave nova sem necessidade; prefira reaproveitar as já existentes no glossário.
- **Convenções de texto**: **negrito** = alvo é um personagem específico; *itálico* = alvo é uma categoria/tipo (ver `CLAUDE.md`).

Se qualquer um desses pontos estiver ambíguo ou faltando (ex. usuário pede "crie o personagem X" sem dizer Vida/patamar/tipo), pergunte antes de inventar — não adivinhe atributos numéricos que vão desbalancear o set.

## 2. Referência do Power System (para Personagens)

Ao definir Vida (e por consequência o patamar/categoria do personagem, se o documento de design usar categorias por patamar):

| Vida | Patamar |
|---|---|
| 1-2 | Sedentário / sub-humano |
| 3-4 | Adulto |
| 5-7 | Esportista |
| 8-10 | Sobre-humano |
| 11-12 | Divino |

- Defesa: 0-5 para patamares comuns; 6-8+ para sobre-humano/divino.
- Dano de ação: Baixo 1-2, Médio 3-4, Alto 5-6, Divino 7+. Regra geral: 1-2 pontos de dano por energia de custo.
- Se o documento de design já atribuiu uma categoria nomeada ao patamar (ex. "Punhos Vazios" = Esportista de Silenteia), use esse nome como a `Classificação` da carta.

## 3. Preencha os campos exigidos pelo CSV do gerador de cartas

Cada expansão gera seus próprios CSVs, um por tipo de carta, em **`LIB/{Nome da Expansão}/TIPO_CARTA.csv`** (ex. `LIB/Fratura do Multiverso/PERSONAGENS.csv`, `LIB/Fratura do Multiverso/ITENS.csv`, `LIB/Fratura do Multiverso/ENERGIAS.csv`). Confirme o nome exato da expansão com o usuário antes de criar a pasta — use o mesmo nome do set definido no documento de design (ex. "Fratura do Multiverso" em `features/colecao-inicial.md`). Ao gerar cartas, apresente os dados já organizados nessas colunas (mesmo que a entrega final seja uma tabela em texto, não o CSV em si — só escreva/edite o CSV se o usuário pedir explicitamente para popular o arquivo).

**Personagens** (`LIB/{Expansão}/PERSONAGENS.csv`) — colunas, em ordem:
`Nome; Versão; Classificação; Tipo; Fraqueza; Resistência; Vida; Defesa; Nome Ação Passiva; Custo P; Descrição P; Nome Ação Ativa; Custo A; Descrição A; Observação; Arte; Prompt Arte`

- `Classificação` é o equivalente à categoria/patamar (ex. "Punhos Vazios") + nome próprio do personagem, no estilo `Nome Próprio - Categoria`.
- Toda carta tem no máximo **1 Ação Passiva** (efeito automático/contínuo, tipicamente sem custo de energia) e **1 Ação Ativa** (o golpe/efeito principal, pago com energia) — não existem mais as 4 ações por posição (Sidekick/Líder/Flanquear/Ataque) do formato antigo. Uma carta pode ter só a Ativa, só a Passiva, ou as duas — não preencha uma ação só para "completar a tabela"; siga o que o design pede.
- `Custo P`/`Custo A` ficam vazios quando a ação correspondente não existe na carta.
- `Descrição P`/`Descrição A` seguem o padrão: **Nome do golpe/efeito** — palavra(s)-chave relevantes — efeito em texto corrido, terminando com `.` Use `/` para separar efeito de Aposte bem-sucedida vs. malsucedida quando a ação usa `Aposte (X)`.

**Itens** (`LIB/{Expansão}/ITENS.csv`) — colunas, em ordem:
`Nome; Versão; Tipo; Dinâmica; Descrição; IMAGEM; Observação; Arte; Prompt Arte`

- `Tipo` aqui é a subcategoria da carta (`perm` = Permanente, `volt`/equivalente = Volátil — confira os valores já usados no CSV antes de introduzir um novo).
- `Dinâmica` é uma tag curta de papel mecânico (ex. "Deck Control", "Crowd Control / Dmg", "FX", "Buff") — reaproveite os valores já usados no CSV quando o papel for equivalente.
- `Descrição` normalmente abre com a afinidade do item quando aplicável (ex. "Anx Tec —", "Anexável (TecSci):") antes do efeito em si.

**Fontes de Energia** (`LIB/{Expansão}/ENERGIAS.csv`) — colunas, em ordem:
`Nome; Versão; Tipo; Energia; Descrição; Texto Final; Observação; Arte; Prompt Arte`

- `Tipo` aqui é Básica/Avançada/Prismática (a estrutura de categorias efetivamente em uso pode estar reduzida — ver o documento de design do set, ex. `colecao-inicial.md` remove Prismática). Além da sua classificação, apresenta o tipo (Magia, TecSci, Vida, Morte, etc)
- `Energia` é o número de energias que a fonte gera (Básica = 1, Avançada pode gerar mais).
- Fontes Básicas não têm efeito — deixe `Descrição` vazia ou com o texto padrão de reciclagem, se houver.

`Observação` existe em todos os três CSVs (Personagens, Itens, Fontes de Energia) e registra detalhes da criação da carta que não fazem parte do texto/mecânica final — ex. justificativa de design, referência à diretriz do documento que motivou o efeito, decisões de balanceamento, ou avisos de que uma escolha (palavra-chave nova, raridade, mecânica de assinatura fora do padrão) foi deliberada. Preencha sempre que houver algo não óbvio a registrar; deixe vazia se a carta é direta e não exige contexto extra.

`Arte` e `Prompt Arte` também existem nos três CSVs e não fazem parte do texto/mecânica da carta:

- `Arte` guarda o nome do arquivo de imagem associado à carta (ex. `personagem-nome-versao.png`) — preencha mesmo que o arquivo ainda não exista, como referência de qual nome usar quando a imagem for gerada/importada.
- `Prompt Arte` guarda o prompt em texto para gerar essa imagem via IA generativa (composição, estilo visual, pose, elementos de cena) — coerente com o tipo/afinidade e a lore do personagem/item/fonte, mas sem precisar reproduzir a descrição mecânica da carta. Este prompt deev ser robusto para manter a coerência entre o sub-set. Sempre finalize o prompt com "Proporção 1:1".

Preencher essas colunas é geração de **texto/metadado**, não de imagem — não gere o arquivo de imagem em si (ver seção 4 abaixo e `CLAUDE.md`, "Cuidado com assets grandes").

## 4. Raridade da carta

Toda carta (Personagem, Item ou Fonte de Energia) precisa de uma raridade, conforme a seção `Raridade` de `rules.md`:

- **Comum**, **Incomum**, **Rara**, **Super-rara**, **Ultra-rara** — em ordem crescente de impacto/poder e decrescente de disponibilidade no pool.

`Épico` e `Lendário` **não são raridades** — são classificações especiais de carta, cada uma atrelada a uma raridade fixa:

- Carta **Épica** → sempre **Ultra-rara**.
- Carta **Lendária** → **Super-rara** ou **Ultra-rara**, a depender do impacto do efeito.

Respeite a cota de Épico/Lendário já fechada pelo documento de design da coleção (ex. 1 Épico e até 3 Lendários por tipo, conforme `colecao-inicial.md`) — não promova uma carta a Épica/Lendária só porque o efeito "parece forte"; siga a cota. Registre a raridade final da carta na coluna `Observação` do CSV quando não houver coluna dedicada a isso.

## 5. Nomenclatura de arquivo de asset (se for gerar imagem/estrutura, não só o texto da carta)

Cartas usam as siglas de 3 letras já mapeadas em `CLAUDE.md` (`NTZ`, `LIF`, `DTH`, `DVN`, `ELM`, `TEC`, `MGC`, Mental, `FSC`, `ENG`, `FRL`, `CSM`, mais quaisquer siglas de tipos novos criados via `new-card-type`). Não gere novos assets de imagem sem necessidade (ver `CLAUDE.md`, seção "Cuidado com assets grandes") — o foco desta skill é o **conteúdo textual/mecânico** da carta, não a arte; as colunas `Arte`/`Prompt Arte` só registram o nome de arquivo e o prompt, não o asset em si.

## 6. Validação antes de entregar

Antes de apresentar a(s) carta(s) geradas, confira:

- O efeito é determinístico por padrão (custo pago → efeito garantido); só use `Aposte (X)` se o documento de design da coleção reservar essa mecânica para aquele tipo/raridade (ver seção "Mecânica de assinatura: Aposte (X)" em `colecao-inicial.md`, quando aplicável).
- O texto reforça pelo menos uma das palavras-chave de assinatura já definidas para o tipo do personagem/item (se o documento de design tiver essa lista) — não crie um efeito genérico desconectado da identidade do tipo sem avisar que é uma escolha deliberada.
- Raridade atribuída conforme a seção 4 acima; classificação Lendária/Épica só em cartas de efeito forte, respeitando a cota já fechada por tipo (1 Épico, até 3 Lendários, ver documento de design).
- Números de Atk/Def/Vida/custo são coerentes com o patamar de Power System escolhido (não dar Vida 10 a um personagem "Adulto", por exemplo).
- Nenhuma IP de terceiros é usada a menos que o próprio universo do set already a utilize (ver `CLAUDE.md` sobre licenciamento).
- Caso o documento de design, tenha uma listagem de cartas, utilize as colunas "Carta Gerada" e "Arte Gerada" para marcar a evolução da criação do set.

## 7. Pergunte antes de escrever em massa

Se o pedido for “gere as 16 cartas do tipo X”, prefira gerar em lotes menores (ex. por patamar de Power System, do mais poderoso ao menos, replicando a ordem já usada nas conversas de design) e confirmar com o usuário antes de seguir para o próximo lote — o mesmo padrão iterativo usado para itens/personagens em `features/colecao-inicial.md`.
