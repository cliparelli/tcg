# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## O que é este repositório

Este não é um projeto de software — é o repositório de **design de conteúdo** de um TCG (jogo de cartas colecionáveis) *fan-made*, com regras e assets de cartas. Não há código-fonte, build, testes ou lint. Todo o conteúdo é em **português do Brasil**; escreva e responda em pt-BR, mantendo os termos técnicos já cunhados (ex.: "DOT", "Debuff", "Ataque Perfurante", "Transpassar", "Sobrevida") em vez de traduzir ou inventar sinônimos.

## Sistema de regras ativo

- `rules.md` é o sistema de regras **atual e ativo**: "MULTIVERSITY CONQUEST". Qualquer edição de regras deve ir para este arquivo.
- `TCG.md` ("Heroes - The Contest") é um sistema **antigo/anterior**, não tocado pelos commits recentes. Não confundir terminologia entre os dois nem editar `TCG.md` a menos que o usuário peça explicitamente.
- `card.md` é um esboço abandonado/incompleto, não relacionado ao sistema de regras ativo.

## Convenções de texto de carta (em `rules.md`)

- **Negrito** em efeitos de carta indica que o alvo é um personagem específico.
- *Itálico* indica que o alvo é uma categoria/tipo de personagem.

## Tipos de personagem e siglas de asset

Os tipos de personagem descritos em `rules.md` (Natureza, Vida, Morte, Divino, Elemental, TecSci, Magia, Mental, Físico, Poder Energético, Fera, Cósmico) mapeiam para siglas de 3 letras usadas nos nomes de arquivo de asset em `CARDS/ASSETS/` e `CARDS/ASSETS/STRUCTURES/V5/CHAR/`: `NTZ`, `LIF`, `DTH`, `DVN`, `ELM`, `TEC`, `MGC`, (Mental), `FSC`, `ENG`, `FRL`, `CSM`. Ao adicionar ou renomear um tipo, mantenha `rules.md` e as siglas de asset consistentes entre si.

`STRUCTURES/` tem subpastas versionadas `V1` a `V5` — **V5 é a versão mais recente** do template/moldura de carta. Novos assets de estrutura devem ir em `V5`, não nas versões antigas.

## Decklists

Arquivos de deck ficam em `public/decks/` (ex.: `dot.md`) e seguem este formato: título `# Mono <Tema> DOT`, parágrafo de estratégia, depois seções `## Personagens (N)`, `## Itens (N)`, `## Fontes de Energia (N)` com a contagem de cartas no título, subseções por categoria (`Líderes`, `Time`, `Permanente`, `Volátil`, `Básica`, `Prismática`, `Avançada`), e cada carta listada como `- NxNome (Versão) - Tag *`, onde `*` marca cartas-chave do combo.

## Licenciamento

O sistema de regras está sob GNU AGPL v3.0. A IP de terceiros usada (Marvel, DC, Saint Seiya, Tekken, etc.) é apenas para diversão entre amigos, não para uso comercial — uso comercial exige licenciamento à parte (ver seção "Licença" em `rules.md`).

## Changelog manual

`rules.md` tem uma seção `## Revisão` com um changelog manual referenciando hashes de commit (ex. `v0.1 [hash] - Adição de Licenciamento`). Essa convenção existe mas não está sendo mantida ativamente nos commits recentes — não é necessário atualizá-la a cada mudança, apenas esteja ciente do padrão caso o usuário peça para atualizá-la.

## Cuidado com assets grandes

`public/` contém imagens PNG grandes (até ~70 MB) versionadas diretamente no git. Não gere ou adicione novos assets de imagem sem necessidade — eles engordam o histórico do repositório permanentemente.
