---
name: new-card-type
description: Adiciona um novo tipo de personagem ao TCG (ex. um novo elemento/afinidade). Use quando o usuário pedir para criar, adicionar ou registrar um novo tipo de personagem/carta no jogo.
---

Adicionar um novo tipo de personagem exige tocar múltiplos lugares de forma consistente. Siga esta checklist, na ordem:

1. **Definir nome e sigla.** O nome é em português por extenso (ex.: "Natureza", "TecSci") e a sigla é de 3 letras maiúsculas (ex.: `NTZ`, `TEC`), seguindo o padrão dos tipos existentes documentados em `CLAUDE.md`. Confirme com o usuário a sigla se não for óbvia — não invente uma sigla que colida com uma existente.

2. **Atualizar `rules.md`.** Adicione o novo tipo à lista de tipos de personagem (mesma seção onde os demais tipos são descritos) explicando sua identidade temática/mecânica.

3. **Criar os assets correspondentes**, usando os arquivos de um tipo existente como referência de nomenclatura e dimensões:
   - `CARDS/ASSETS/FRQ/f-<sigla-ou-nome>.png` — ícone de frequência/tipo.
   - `CARDS/ASSETS/RST/r-<sigla-ou-nome>.png` — ícone de resistência.
   - `CARDS/ASSETS/TIPOS/t-<sigla-ou-nome>.png` — ícone de tipo.
   - `CARDS/ASSETS/MARBLES/<NOME>.png` — ícone "marble" (nome em maiúsculas).
   - `CARDS/ASSETS/STRUCTURES/V5/CHAR/CHAR-<SIGLA3>.png` e `CHAR-<SIGLA3>-FULL.png` — estrutura/moldura de carta na versão mais recente (V5, nunca versões antigas).

   Se não for possível gerar as imagens diretamente, liste exatamente esses caminhos como pendências para o usuário produzir, ao invés de deixar o tipo parcialmente integrado sem avisar.

4. **Conferir consistência.** Releia `rules.md` e os nomes de arquivo criados para garantir que a sigla é usada de forma idêntica em todos os lugares — não deve haver "de-para" divergente entre regras e assets.

5. **Não** editar `TCG.md` (sistema de regras antigo) a menos que o usuário peça explicitamente.
