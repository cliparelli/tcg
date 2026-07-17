# scripts/

Scripts utilitários para o repositório. Não fazem parte das regras nem do
conteúdo do jogo — apenas automatizam tarefas de produção de assets.

## gerar_imagens_chatgpt.py

Lê os CSVs de uma coleção dentro de `LIB/`, extrai o prompt de arte de cada
carta e usa o ChatGPT (via navegador, com login manual) para gerar a imagem
correspondente, salvando o resultado ao lado do CSV.

### Como funciona

1. Recebe o nome de uma pasta dentro de `LIB/` (ex.: `Fratura do Multiverso`).
2. Procura todos os `*.csv` dessa pasta e mantém só os que têm as colunas
   `Prompt Arte` e `Arte` — hoje isso cobre `PERSONAGENS.csv` e
   `ENERGIAS.csv` do padrão da "Fratura do Multiverso". CSVs no formato
   antigo (`LIB/base/*-CardGenerator.csv`) não têm essas colunas e são
   ignorados automaticamente.
3. Para cada linha com prompt preenchido, monta uma fila de geração:
   nome da carta, texto do prompt e nome do arquivo de destino (coluna
   `Arte`, ex. `fonte-poco-do-silencio-inicial-v1.png`).
4. Abre uma janela do Chromium (via Playwright) usando um **perfil
   persistente** salvo em `scripts/.chatgpt-profile/` — a sessão de login
   fica gravada ali entre execuções.
5. Se não detectar login ativo, pausa e pede para você logar manualmente
   na janela do navegador; depois pressione ENTER no terminal para
   continuar.
6. Envia os prompts **um de cada vez**, aguarda a imagem ser gerada,
   baixa e salva com o nome exato da coluna `Arte`, na mesma pasta do CSV
   (mesmo padrão dos `.png` já existentes na coleção).

### Pré-requisitos

```bash
pip3 install playwright
python3 -m playwright install chromium
```

### Uso

Rodar a partir da raiz do repositório:

```bash
# Gera todas as imagens faltantes/existentes da coleção
python3 scripts/gerar_imagens_chatgpt.py "Fratura do Multiverso"

# Pula cartas cujo arquivo de imagem já existe na pasta
python3 scripts/gerar_imagens_chatgpt.py "Fratura do Multiverso" --pular-existentes

# Gera só as imagens de um CSV específico (filtra pelo nome do arquivo)
python3 scripts/gerar_imagens_chatgpt.py "Fratura do Multiverso" --somente PERSONAGENS

# Roda sem abrir a interface gráfica (não recomendado no primeiro login,
# já que o login manual precisa da janela visível)
python3 scripts/gerar_imagens_chatgpt.py "Fratura do Multiverso" --headless
```

### Observações

- O script processa as cartas **sequencialmente**, uma imagem por vez —
  não há paralelismo, para não sobrecarregar a conta do ChatGPT nem
  disparar limites de uso.
- Se a geração de uma imagem estourar o tempo limite (3 minutos) ou
  falhar, o script avisa no terminal e segue para a próxima carta, sem
  interromper a fila inteira.
- Os seletores usados para encontrar o campo de prompt e a imagem gerada
  são baseados na interface atual do ChatGPT (chatgpt.com). Se a OpenAI
  mudar o HTML do site, pode ser necessário ajustar os seletores no topo
  do script.
- `scripts/.chatgpt-profile/` guarda dados de sessão do navegador
  (cookies de login) — não deve ser versionado no git.

## capturar_preview_cartas.py

Sobe o `viewer/` localmente (servidor PHP embutido), abre cada carta no
navegador (via Playwright) e tira um screenshot do preview renderizado
(`#card-preview` — moldura + arte + textos), salvando em
`EXPANSIONS/<coleção>/` para uso como referência visual.

### Como funciona

1. Recebe o nome de uma pasta dentro de `LIB/` (ex.: `Fratura do Multiverso`).
2. Lê os CSVs dessa coleção (`PERSONAGENS`, `ITENS`, `ENERGIAS`) e mantém só
   as cartas cujo arquivo de arte (coluna `Arte`, ou `IMAGEM` no caso de
   Itens) **existe de fato em disco** — cartas com prompt gerado mas ainda
   sem imagem são puladas.
3. Sobe `php -S localhost:PORTA -t viewer` automaticamente.
4. Abre o Chromium (Playwright, headless), seleciona o CSV e a carta na UI
   do viewer exatamente como um usuário faria, espera a arte carregar por
   completo e tira o screenshot do elemento `#card-preview`.
5. Salva cada imagem como `EXPANSIONS/<coleção>/<tipo>-<nome-da-carta>.png`.
6. Encerra o servidor PHP ao final (ou em caso de erro).

### Pré-requisitos

```bash
pip3 install playwright
python3 -m playwright install chromium
```

### Uso

Rodar a partir da raiz do repositório:

```bash
# Captura todas as cartas com arte existente da coleção
python3 scripts/capturar_preview_cartas.py "Fratura do Multiverso"

# Captura só as cartas de um CSV específico (filtra pelo nome do arquivo)
python3 scripts/capturar_preview_cartas.py "Fratura do Multiverso" --somente PERSONAGENS

# Usa outra porta local (caso 8000 já esteja ocupada)
python3 scripts/capturar_preview_cartas.py "Fratura do Multiverso" --porta 8087

# Captura as cartas SEM IMAGEM gerada
python3 scripts/capturar_preview_cartas.py "Fratura do Multiverso" --porta 8087 --sem-imagem
```

### Observações

- `EXPANSIONS/` não é versionada no git (ver `.gitignore`) — é apenas uma
  pasta de referência visual, regerável a qualquer momento a partir do
  `LIB/` e do `viewer/`.
- O script não gera nem edita nenhuma arte; ele só captura o preview de
  cartas cuja imagem já foi criada (por `gerar_imagens_chatgpt.py` ou
  manualmente).
