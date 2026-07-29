---
name: new-deck
description: Cria uma nova decklist para o TCG em public/decks/. Use quando o usuário pedir para montar, criar ou registrar um novo deck/build de personagens.
---

Esta skill é **autossuficiente** para construção de decks: todas as regras de montagem estão neste documento. Consulte `rules.md` apenas para revisar alguma mecânica de jogo específica (palavras-chave, tokens, tipos de dano) ou algum detalhe extra — nunca para as regras de construção em si.

## Regras de construção de deck (MULTIVERSITY CONQUEST)

Regras obrigatórias — todo deck gerado DEVE respeitar:
- Máximo de **60 cartas** no deck principal;
- Mínimo de **12 personagens**;
- Mínimo de **12 Fontes de Energia**;
- Personagens e Itens: até **4 cópias** por carta;
- Fontes de Energia **básicas**: sem limite de cópias;
- Fontes de Energia **avançadas** (incluindo Prismáticas): até **4 cópias** por carta;
- Cartas **Lendárias e/ou Épicas**: apenas **1 cópia** de cada;
- Não há limitação de tipos de personagem, mas mantenha sinergia entre as cartas selecionadas.

Proporção recomendada (boa divisão padrão):
- **18 personagens** (6 personagens x 3 cópias, ou 4 personagens x 4 cópias + 1 personagem x 2 cópias);
- **12 a 16 itens**;
- **18 Fontes de Energia básicas**;
- Se possível, **4 a 8 Fontes de Energia avançadas** variadas.

### Sideboard
- Até **15 cartas** auxiliares, trocáveis por cartas do deck principal entre jogos;
- Toda troca é **uma carta por outra**, mantendo os mínimos de personagens e energias, os limites de cópias e o total de 60 cartas;
- Uso típico: cartas situacionais/tech (ex. uma carta que só funciona contra personagens de Natureza).

### Tipos de carta (referência rápida)
- **Personagens**: um fica na Posição Principal (única que ataca/usa Liderança), até três nas Posições do Time (reservas, podem usar Flanquear);
- **Itens Permanentes**: efeito perene (equivalente a encantamento/cenário);
- **Itens Voláteis**: efeito único ou de duração determinada (equivalente a mágica/feitiço);
- **Itens Anexáveis**: permanentes que precisam estar anexados a um personagem de tipo/categoria/nome válido;
- **Fontes de Energia básicas**: geram 1 energia;
- **Fontes de Energia avançadas**: geram 2+ energias ou efeitos extras;
- **Fontes Prismáticas**: subtipo de avançada — geram 2 energias para um personagem do mesmo tipo da fonte.

### Condições de vitória (a estratégia do deck deve mirar pelo menos uma)
1. Adquirir todas as **Pedras de Recompensa**, nocauteando os personagens do oponente;
2. Deixar o oponente **sem personagens em ação** no jogo;
3. Fazer o **deck do oponente terminar** (deck out).

## Arquétipos e estratégias

Todo deck deve declarar um arquétipo no título e a estratégia deve ser coerente com ele. Use estes arquétipos como vocabulário-base:

### Aggro (Agressão)
Vencer o mais rápido possível com ameaças de custo baixo e pressão constante de nocautes para tomar as Pedras de Recompensa antes que o oponente estabilize.
- **Burn**: dano direto via ações e itens voláteis, reduzindo a vida dos personagens do oponente sem depender só de combate;
- **Swarm (Go-Wide)**: preenche todas as Posições do Time rapidamente e vence pela superioridade numérica e uso agressivo de Flanquear/Assistência;
- **Direct Aggression**: foca em nocautes individuais constantes para vencer a "troca de prêmios" (Pedras de Recompensa);
- **Spread**: distribui dano por todo o time do oponente (Alvos Múltiplos, DOT) para preparar múltiplos nocautes simultâneos.

### Control (Controle)
Retardar o ritmo do jogo, negar recursos e vencer no late-game com ameaças de maior valor.
- **Mill**: vitória pela condição 3 (deck out), com efeitos como **Triturar** e descarte forçado;
- **Stall**: impede nocautes e dano com personagens de muita vida, cura, Escudo e Sobrevida — estilo típico do tipo **TecSci**, que controla deck e recursos (Triturar, Prever, Clarividência, Extinguir/rotacionar fontes);
- **Hand Disruption**: força descartes ou anula jogadas diretamente da mão do oponente;
- **Board Control**: mantém o campo do oponente limpo com remoções e efeitos de limpeza de campo.

### Midrange
Equilíbrio entre Aggro e Control: joga como agressor contra decks de controle e como controlador contra decks agressivos ("going bigger"). Usa curva de custo de energia eficiente — controle no início da partida e ameaças poderosas no meio do jogo.

### Combo
Baseado na interação específica de duas ou mais cartas que gera efeito decisivo ou vitória imediata. Prioriza consistência e velocidade para reunir as peças (Clarividência, Sondar, Reciclar Recursos) enquanto sobrevive à pressão inicial. As palavras-chave **Starter (X)**, **Finisher (X)** e **Tag (X)** são a dinâmica nativa de combos do jogo — decks Combo devem considerá-las. Marque as peças do combo com `*` na decklist.

### Híbridos e outras estratégias
- **Tempo (Aggro-Control)**: ameaças rápidas protegidas por pequenas interrupções/anulações, mantendo o oponente fora de equilíbrio;
- **Prison (Control-Combo)**: negação total de recursos (travar/extinguir Fontes de Energia), criando um "bloqueio" que impede o oponente de jogar;
- **Voltron (Go-Tall)**: fortalecer um único personagem com vários buffs e itens anexáveis até torná-lo imbatível na Posição Principal;
- **Ramp**: acelerar a geração de energia para jogar ações e cartas caras antes do esperado;
- **Scam**: colocar personagens/efeitos poderosos em jogo de graça ou por manipulação do deck;
- **Gimmick**: deck construído em torno de uma interação muito específica/divertida, menos competitivo, focado em diversão.

## Formato do arquivo

Crie um novo arquivo em `public/decks/<nome-do-deck>.md` com o formato abaixo:

```markdown
# <Arquétipo> <Tema> <Mecânica principal, se houver>
<Parágrafo de estratégia: como o deck vence, que combo/sinergia central usa.>

<Um ou mais parágrafos explicando as peças-chave do combo e como as cartas de suporte encaixam.>

## Personagens (N)
 - Líderes
	- NxNome (Versão) (Coleção) *
 - Time
	- NxNome (Versão) (Coleção) *

## Itens (N)
- Permanente
	- NxNome - Tag (Coleção) - Categoria *
- Volátil
	- NxNome (Coleção) - Categoria

## Fontes de Energia (N)
- Básica
	- NxNome (Coleção)
- Prismática
	- NxNome (Coleção)
- Avançada
	- NxNome (Coleção)
```

Regras de formatação:
- `N` no título de cada seção (`## Personagens (N)`) é a contagem total de cartas naquela seção — recalcule sempre que adicionar/remover cartas. A soma das três seções não pode passar de 60.
- `*` ao final de uma linha marca uma carta-chave do combo (peça central da estratégia). Use com moderação, só nas cartas realmente essenciais.
- Coleção entre parênteses indica a origem da carta: `(SS)` = Saint Seiya, `(Basic)` = carta genérica/neutra, ou o nome da franquia (ex. `(Minecraft)`, `(Tekken)`). Confirme a sigla/nome correto com o usuário se a franquia for nova.
- Itens levam uma tag de categoria após o nome (ex. `Buff`, `Control`, `Search`, `Dmg`) indicando o papel mecânico da carta.
- "Lendária" antes da coleção nos Itens indica raridade especial — mantenha esse marcador e lembre-se do limite de 1 cópia para Lendárias/Épicas.
- Fontes de Energia se dividem em `Básica`, `Prismática` e `Avançada`, do mesmo jeito que em `dot.md`.

## Checklist antes de escrever o arquivo

1. Pergunte ao usuário (se não for óbvio pelo contexto): tema/arquétipo do deck, quais personagens/líderes são o núcleo, e a estratégia geral — não invente cartas ou números que o usuário não mencionou.
2. Valide a decklist contra as regras de construção: total ≤ 60, ≥ 12 personagens, ≥ 12 energias, limites de cópias (4x / 1x Lendária-Épica), e as contagens `(N)` de cada seção corretas.
3. Confira que a estratégia declarada no parágrafo inicial aponta para pelo menos uma das três condições de vitória e é coerente com o arquétipo do título.
