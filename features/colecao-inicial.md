# Coleção Inicial — "ARENA PRIMORDIAL" (nome de trabalho)

Documento de design para o primeiro set de uma **propriedade intelectual própria**, derivada do sistema de regras de MULTIVERSITY CONQUEST (`rules.md`), mas sem nenhuma IP de terceiros (Marvel, DC, Saint Seiya, Tekken, Minecraft etc.). Este documento define escopo, lore, tipos, mecânicas e proporção de cartas do set inicial — ainda não é a lista final de cartas.

## Por que este documento existe

`rules.md` é um sistema de regras genérico que pode hospedar qualquer IP (inclusive fan-made). Este documento descreve a **primeira coleção própria** publicada sob esse sistema: um universo autoral, pequeno o bastante para ser um "starter set" real, mas desenhado para crescer. As decisões aqui foram tomadas em conversa com o autor em 2026-07-14 e substituem, para esta coleção, qualquer suposição herdada dos decks de referência em `LIB/` (que eram todos fan-made).

## Lore — premissa do universo

Existe um pequeno multiverso de **três mundos rivais**, cada um dominado por uma escola de poder: **Magia**, **TecSci** e **Físico**. Periodicamente — e há gerações — esses três povos disputam um **Torneio sazonal**, um ritual de combate natural entre eles que decide prestígio, recursos e território até a próxima rodada. Não é uma guerra total: é uma rivalidade competitiva, quase esportiva, com códigos e tradições próprias em cada mundo.

O equilíbrio se rompe com a **Fratura do Multiverso**: um evento que rasga a fronteira entre esse pequeno multiverso e o resto da existência, introduzindo dois novos mundos que não faziam parte do ciclo do Torneio — **Divino** e **Cósmico**. Eles não são rivais naturais dos três primeiros; são forças externas, mais antigas ou mais vastas, que agora intervêm no conflito com motivações próprias. Isso muda o Torneio para sempre e é o gancho narrativo natural para expansões futuras (mais mundos podem ter vazado pela Fratura).

Este pano de fundo é deliberadamente enxuto: poucos nomes próprios de "mundos", uma tradição central (o Torneio), um evento de virada (a Fratura) e espaço aberto para personagens, categorias e novos mundos em sets seguintes.

## Os 5 tipos (afinidades) do set inicial

Reduzido de 12 tipos (sistema atual) para **5**, organizados como um triângulo de rivalidade + uma dupla de poder externo.

### Triângulo — os três mundos do Torneio

Cada tipo vence tematicamente um e perde tematicamente para o outro (rock-paper-scissors), reaproveitando a mecânica de vantagem de tipo que já existe em `rules.md` (Resistência/Fraqueza dobra defesa/dano):

- **Magia** vence **Físico**, perde para **TecSci**
- **TecSci** vence **Magia**, perde para **Físico**
- **Físico** vence **TecSci**, perde para **Magia**

Leitura temática: o místico domina o corpo desprotegido; a técnica decifra e neutraliza a magia; a força bruta esmaga a máquina. Nenhum dos três é estritamente superior — todo deck mono-tipo tem um algoz natural, o que empurra para decks combinados ou para bom uso de itens/energias fora da afinidade.

Arquétipo de jogo sugerido por tipo (adaptando os arquétipos já descritos em `rules.md` para os tipos correspondentes):
- **Magia**: suporte/controle — cura, buffs, manipulação de recursos.
- **TecSci**: controle de deck/stall — Prever, Clarividência, Triturar, Extinguir Fonte/Recurso.
- **Físico**: agressão direta — dano consistente, alteração simples de Atk/Def/Vida.

### Dupla de topo — as forças da Fratura

- **Divino** e **Cósmico** não são antagônicos entre si; são as duas forças externas que entraram no multiverso pela Fratura. Interagem por sinergia (não por vantagem/fraqueza) e ocupam o topo de curva do set: custos mais altos, efeitos mais decisivos, presença mais rara na coleção.
- **Divino**: proteção/negação de morte, imunidades, efeitos de grande escala mas situacionais (herdando o "não há arquétipo único" do `rules.md` — aqui restrito a paz/proteção vs. julgamento/punição, sem se comprometer com um único registro).
- **Cósmico**: alto poder de fogo, ataques expansíveis, alvos múltiplos — o "finalizador" do set.

Nenhum dos dois recebe Resistência/Fraqueza formal contra o triângulo — narrativamente, eles estão fora do ciclo do Torneio, não dentro dele.

## Mecânica de assinatura: `Aposte (X)` — presente, mas rara

`Aposte (X)` continua sendo a mecânica de assinatura do sistema (moeda/dado), mas na coleção inicial ela é **a exceção, não a regra** — o oposto do bloco de referência em `LIB/` (que usava aposta quase em toda carta). O restante do set é determinístico por design: custo pago → efeito garantido, no estilo Magic/Pokémon/Hearthstone, para reduzir a carga cognitiva de quem está aprendendo.

Distribuição de `Aposte (X)` no set inicial:
- **Magia / TecSci / Físico**: ~2 cartas de personagem por tipo usam Aposte (as "cartas de risco" do tipo, tipicamente incomuns/raras de efeito forte condicionado à sorte).
- **Divino / Cósmico**: ~3 a 5 cartas de personagem por tipo usam Aposte — reforça que essas duas afinidades são mais voláteis e de maior impacto, coerente com serem o topo de curva do set.
- Itens e Fontes de Energia: `Aposte (X)` pode aparecer ocasionalmente em cartas raras de qualquer tipo, mas não é obrigatória em nenhuma categoria.

Todo o resto — custos de ativação, dano, buffs/debuffs — permanece 100% determinístico.

## Tamanho do set

- **16+ personagens por tipo × 5 tipos = 80+ cartas de personagem** no set completo (robusto o bastante para variedade de curva e algum arquétipo dentro do próprio tipo, ex. Magia-controle vs. Magia-cura).
- Segue as regras de deck já existentes em `rules.md` (mínimo 12 personagens, até 4 cópias por carta, 60 cartas por deck) — 80+ cartas de personagem é o **pool do set**, não o tamanho de um deck individual.
- Um jogador iniciante deve conseguir montar um deck competitivo mono-tipo (dentro do triângulo) só com cartas do set inicial, ou combinar dois tipos vizinhos do triângulo, ou puxar 1 tipo do triângulo + 1 tipo da dupla de topo como "splash" de poder.

## Itens e Fontes de Energia — estrutura simplificada

Reduzido de 3×2 categorias (Fontes: Básica/Avançada/Prismática; Itens: Permanente/Volátil/Anexável) para **2 categorias cada**, mais próximo de "Terreno/Feitiço" do Magic:

- **Fontes de Energia**: só **Básica** (gera 1 energia, sem efeito) e **Avançada** (gera energia + efeito). A categoria Prismática (afinidade cruzada) não existe no set inicial — pode voltar em expansão futura se fizer sentido para novos mundos.
- **Itens**: só **Permanente** (efeito contínuo enquanto em jogo) e **Volátil** (efeito único, depois vai para descarte). O subtipo Anexável não existe como categoria própria no set inicial — um item permanente pode narrativamente "pertencer" a um personagem via seu próprio texto de efeito (ex. "Enquanto anexado a...") sem precisar de uma categoria de carta separada.

Isso reduz o número de decisões de categorização para quem está montando o primeiro deck, mantendo compatibilidade total com o motor de regras de `rules.md` (nenhuma mudança de regra é necessária, só uma redução do espaço de opções usado nesta coleção).

## Convenções herdadas de `rules.md` (sem mudança)

- **Negrito** = alvo é um personagem específico; *Itálico* = alvo é uma categoria/tipo.
- Cálculo de dano, blocos de mitigação, fila de combate, palavras-chave — usa o motor de `rules.md` sem alteração.
- Siglas de asset: **MGC** (Magia), **TEC** (TecSci), **FSC** (Físico), **DVN** (Divino), **CSM** (Cósmico) já existem em `CARDS/ASSETS/STRUCTURES/V5/` — o set inicial reaproveita os templates existentes, sem necessidade de novas siglas ou novas molduras.

## Em aberto / próximos passos

- Nome definitivo do set e dos 3 mundos do Torneio (hoje só descritos por tipo — Magia/TecSci/Físico podem ganhar nomes próprios de "reino"/"cidade-estado").
- Categorias de personagem (o equivalente a "Cavaleiro de Bronze" ou "Kriptoniano" no sistema antigo) para cada um dos 5 tipos — dão gancho para sinergias tipo *Bando (X)* e *Devoção (X)* sem depender de IP externa.
- Lista nomeada de personagens, itens e fontes de energia (uso do skill `new-card-type`/`new-deck` quando estiver pronto para popular CSVs e decklists).
- Decidir se a Fratura do Multiverso é um evento fechado (fan-service de expansão futura) ou algo que pode ser referenciado mecanicamente em cartas do próprio set inicial (ex. um Item lendário "Fratura" com efeito único).
