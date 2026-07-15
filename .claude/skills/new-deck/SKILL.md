---
name: new-deck
description: Cria uma nova decklist para o TCG em public/decks/. Use quando o usuário pedir para montar, criar ou registrar um novo deck/build de personagens.
---

Crie um novo arquivo em `public/decks/<nome-do-deck>.md` seguindo exatamente o formato observado em `public/decks/dot.md`:

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
- `N` no título de cada seção (`## Personagens (N)`) é a contagem total de cartas naquela seção — recalcule sempre que adicionar/remover cartas.
- `*` ao final de uma linha marca uma carta-chave do combo (peça central da estratégia). Use com moderação, só nas cartas realmente essenciais.
- Coleção entre parênteses indica a origem da carta: `(SS)` = Saint Seiya, `(Basic)` = carta genérica/neutra, ou o nome da franquia (ex. `(Minecraft)`, `(Tekken)`). Confirme a sigla/nome correto com o usuário se a franquia for nova.
- Itens levam uma tag de categoria após o nome (ex. `Buff`, `Control`, `Search`, `Dmg`) indicando o papel mecânico da carta.
- "Lendária" antes da coleção nos Itens indica raridade especial — mantenha esse marcador quando aplicável.
- Fontes de Energia se dividem em `Básica`, `Prismática` e `Avançada`, do mesmo jeito que em `dot.md`.

Antes de escrever o arquivo, pergunte ao usuário (se não for óbvio pelo contexto): tema/arquétipo do deck, quais personagens/líderes são o núcleo, e a estratégia geral — não invente cartas ou números que o usuário não mencionou.
