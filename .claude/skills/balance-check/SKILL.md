---
name: balance-check
description: Analisa o balanceamento numérico e mecânico das cartas de uma coleção (ou sub-set/tipo dentro dela) já geradas em LIB/{Expansão}/*.csv. Use quando o usuário pedir para revisar, auditar ou opinar sobre o balanceamento de um set, tipo, patamar ou lote de cartas já criadas — diferente de new-card (que gera cartas novas) e da análise de rules.md (que audita o motor de regras, não cartas específicas).
---

Esta skill audita cartas **já existentes** nos CSVs de `LIB/{Expansão}/` — não gera cartas novas (isso é `new-card`) nem revisa o motor de regras em si (isso seria uma leitura direta de `rules.md`/`analise-rules.md`). O objetivo é responder "essas cartas estão balanceadas entre si e frente ao Power System/diretrizes de design?", apontando outliers, buracos de cobertura e inconsistências — não reescrever cartas sem que o usuário peça.

## 1. Delimite o escopo antes de analisar

Pergunte ou confirme, se não estiver óbvio pelo pedido do usuário:

- **Coleção**: qual expansão em `LIB/` (ex. `LIB/Fratura do Multiverso/`)? Pode haver mais de uma.
- **Sub-set**: o pedido é sobre a coleção inteira ou um recorte — um tipo de personagem (ex. só `Magia`), um patamar de Power System (ex. só Sobre-humanos), uma categoria de carta (só Personagens, só Itens, só Fontes), ou um arquétipo/nação nomeado (ex. "Guardiões do Contrato")?
- Se o usuário disser algo genérico como "analise o balanceamento da coleção", trate como escopo = todos os CSVs daquela expansão, os três tipos de carta juntos.

Não presuma a expansão se houver mais de uma em `LIB/` e o pedido não especificar — pergunte.

## 2. Reúna as fontes de verdade antes de julgar qualquer número

- **`LIB/{Expansão}/PERSONAGENS.csv`, `ITENS.csv`, `ENERGIAS.csv`** — os dados brutos a analisar. Leia o CSV inteiro do escopo definido (não amostre só as primeiras linhas); para CSVs grandes, pagine a leitura mas cubra 100% das linhas do escopo antes de concluir.
- **`rules.md`, seção `Personagens > Power System`** — a régua oficial de Vida → patamar e as faixas esperadas de Defesa/Dano por patamar (tabela reproduzida abaixo). Releia a seção **completa** de `Power System`/`Arquétipos dos Tipos de Personagem` em `rules.md`, não confie só na tabela resumida aqui, pois `rules.md` pode ter mudado desde a última revisão desta skill.
- **`features/*.md`** relevante à expansão (ex. `features/colecao-inicial.md`), se existir — cotas de raridade (Épico/Lendário/Temático/Genérico), distribuição-alvo por Power System, palavras-chave de assinatura por tipo, tamanho-alvo do set. Esse documento é a fonte de verdade sobre *intenção* de design; os CSVs são a fonte de verdade sobre o que *foi de fato* produzido. Divergência entre os dois é, em si, um achado a relatar — não corrija silenciosamente um pelo outro.
- **`CLAUDE.md`** — convenções de negrito/itálico e siglas de tipo, caso precise checar consistência textual junto do balanceamento numérico.

Referência rápida do Power System (confirme contra `rules.md` antes de aplicar, pois pode ter sido revisado):

| Vida | Patamar | Defesa esperada | Dano de ação esperado |
|---|---|---|---|
| 1-2 | Sedentário / sub-humano | baixa (0-2) | Baixo 1-2 |
| 3-4 | Adulto | baixa-média (0-3) | Baixo-Médio 1-3 |
| 5-7 | Esportista | média (2-5) | Médio 3-4 |
| 8-10 | Sobre-humano | alta (4-8) | Alto 5-6 |
| 11-12 | Divino | alta+ (6-8+) | Divino 7+ |

Regra geral de custo: ~1-2 pontos de dano por energia de custo.

## 3. Dimensões de análise

Para o escopo definido, produza uma leitura organizada nestas dimensões (pule as que não se aplicam ao escopo, ex. Fontes de Energia não têm Power System):

### 3.1 Coerência Vida/Defesa/Dano/Custo por patamar (Personagens)
- Para cada carta, confira se Vida bate com o patamar declarado na `Classificação`/Observação, e se Defesa e dano de ação estão dentro (ou perto) da faixa esperada daquele patamar.
- Sinalize outliers: Vida alta com Defesa muito abaixo da faixa (ou vice-versa), dano de ação muito acima do custo pago (regra ~1-2 dano/energia), ou dano muito abaixo do custo pago (carta "fraca demais" para o slot).
- Cartas sem Ação Ativa ou sem Ação Passiva não são um problema per se (o motor permite), mas registre se um patamar/tipo tem proporção incomum de cartas "incompletas" comparado ao resto do set.

### 3.2 Eficiência custo/efeito entre cartas comparáveis
- Compare cartas do mesmo patamar (ou custo de energia) entre si: duas cartas de custo 2 e patamar Esportista deveriam ter poder de fogo/efeito similar, salvo diferenciação deliberada e documentada (na coluna `Observação`, se preenchida).
- Aponte cartas que parecem estritamente melhores ou estritamente piores que outra do mesmo custo/patamar sem justificativa aparente (isso é o tipo de achado mais acionável desta análise).

### 3.3 Distribuição por tipo e por patamar
- Conte quantas cartas existem por Tipo de personagem e por patamar dentro do escopo. Compare contra a distribuição-alvo em `features/*.md` (seção "Distribuição por Power System" ou equivalente), se existir.
- Para Itens/Fontes: conte por categoria de raridade (Épico/Lendário/Temático/Genérico) e confira contra a cota declarada no documento de design (ex. "1 Épico, até 3 Lendários" por tipo).
- Sinalize tipos/patamares sobre-representados ou sub-representados frente à intenção declarada — ou, na ausência de um documento de design, frente à distribuição observada nos demais tipos da mesma coleção (times devem ser simétricos entre si, salvo assimetria deliberada, ex. "dupla de topo" vs. "triângulo" em `colecao-inicial.md`).

### 3.4 Cobertura de palavras-chave de assinatura por tipo
- Se `features/*.md` define palavras-chave de assinatura por tipo (ex. Magia = Ponto Fraco/Clarividência/Cura-Sobrevida/Debuff), confira se as cartas do tipo no escopo de fato cobrem essas palavras-chave, e se a cobertura está concentrada em poucas cartas ou bem distribuída.
- Sinalize palavras-chave de assinatura sem nenhuma carta que as cubra, ou uma única carta carregando sozinha toda a identidade mecânica do tipo (fragilidade de design: se aquela carta sair da rotação/for banida, o tipo perde a identidade).

### 3.5 Mecânicas de raridade/assinatura fora do padrão
- `Aposte (X)` e efeitos de cópia única (Épico/Lendário) devem ser raros — confira se a proporção de cartas com essas mecânicas no escopo é coerente com "presente, mas rara" (ver `features/*.md`, seção "Mecânica de assinatura").
- Sinalize concentração incomum de mecânicas fortes/raras em um único personagem, arquétipo ou patamar.

### 3.6 Consistência textual ligada a balanceamento
- Confira se `Observação` (quando preenchida) de fato justifica escolhas numéricas fora da régua padrão — uma carta fora da faixa sem observação explicando o porquê é, em si, um achado a relatar (falta de rastro de decisão de design), mesmo que o número em si pareça razoável.
- Não reabra debate sobre convenções de negrito/itálico ou nomenclatura a menos que afete diretamente a leitura de um efeito relevante ao balanceamento.

## 4. Formato do relatório

Entregue como uma resposta estruturada (texto ou tabela conforme a quantidade de achados), não como edição direta dos CSVs:

- **Resumo do escopo analisado** (expansão, sub-set, quantas cartas cobertas).
- **Achados por dimensão** (seções 3.1 a 3.6 acima que se aplicarem), cada achado citando a(s) carta(s) pelo nome e o número/coluna em questão — não generalize sem citar exemplo concreto.
- **Outliers/prioridades**: destaque no topo os 3-5 achados mais acionáveis (ex. cartas estritamente dominantes/dominadas, buracos de cobertura de palavra-chave, patamar sub-representado), não uma lista plana de tudo que notou.
- Não corrija as cartas automaticamente. Se o usuário pedir para corrigir um achado específico depois do relatório, trate como uma edição pontual do CSV (mesmas convenções de coluna descritas em `new-card`), confirmando o número/efeito novo com o usuário antes de escrever.

## 5. Limites

- Não é a mesma coisa que auditar `rules.md` (mecânicas gerais do jogo) — se um achado for na verdade uma ambiguidade do motor de regras, não da carta, mencione mas não tente resolvê-lo aqui; sugira que seja tratado como uma revisão de regras à parte.
- Não invente cotas/distribuições-alvo quando não existir `features/*.md` para aquela coleção — nesse caso, analise só a consistência interna do set (cartas entre si) e deixe explícito que não há documento de design para comparar contra intenção declarada.
