# Análise de `rules.md` — MULTIVERSITY CONQUEST

Análise do documento de regras vigente (`rules.md`), separando o que é simples/sólido do que é complexo, identificando ambiguidades e lacunas, e propondo caminhos de melhoria. Baseado na leitura integral do documento em 2026-07-14, com atualizações em 2026-07-14 após a rodada 1 (tokens/efeitos, simplificação de ações e Flanquear), a rodada 2 (ordem de mitigação de dano, contradição Escudo x Dano Perfurante, combate sem personagens elegíveis), a rodada 3 (tabela de apoio para o Cálculo de Dano Complexo), a rodada 4 (Power System movido para dentro de Personagens, tipos de personagem desduplicados), a rodada 5 (DOT somando por ação, sorteio padrão com D4, descrição de Aposte (X), declarações no Cálculo de Dano Complexo), a rodada 6 (exemplo de declaração de combate com Flanquear dos dois lados), a rodada 7 (precedência de alvo Provocação/Invisibilidade, reversão de Ignorar Defesa para afetar Escudo, distinção Ataque/Ação Expansível, glossário de palavras-chave, revisão ortográfica geral, changelog) e a rodada 8 (mudança de licenciamento para CC BY-NC-SA 4.0).

---

## 0. Mudanças já aplicadas (rodada 1)

Esta rodada resolveu, por decisão explícita do autor, um subconjunto dos achados abaixo. Mantidos aqui como registro:

- **Temporalidade de tokens unificada**: por convenção, todo token é temporário e some na próxima fase de limpeza do controlador do personagem, independente de quem o aplicou. Isso resolve a base do achado 2.4 (três categorias mal definidas) — a categoria "tokens definitivos" foi removida do documento por não ter nenhum membro atribuído.
  - Exceções nomeadas, por dependerem de persistir/acumular entre turnos: **DOT, Perdição, Sonolência, Infecção**.
  - **Reviver, Imunidade a Dano, Imunidade a Morte** passaram a seguir a regra geral (antes eram "permanentes até consumidos por gatilho").
  - **Desviar, Reflexão, Assistência** deixaram de ser tokens e viraram palavras-chave de combate (podem ser fixas em uma carta ou concedidas por efeito pontual/token comum).
- **Ações de personagem simplificadas**: só restam duas categorias — Passiva (qualquer posição) e Ativa/ataque (qualquer posição). As antigas subcategorias posicionais Efeito Líder e Ação Sidekick foram removidas da seção `Personagens`.
- **Flanquear virou palavra-chave de combate** (não mais um tipo de ação à parte): só pode ser usada por personagem na posição de time, nunca da principal; limite de uma ação com Flanquear por jogador por turno; não consome nem substitui o ataque principal do atacante.
- **Fila de Combate reescrita**: novo modelo de declaração (atacante anuncia intenção → defensor declara se usará Flanquear → atacante declara Flanquear + ataque principal) e resolução em FIFO (Flanquear do defensor → Flanquear do atacante → ataque principal → cálculo de dano → efeitos temporários). Um combate agora pode gerar até três ações de dano em sequência.
- Os três exemplos de `Cálculo de Dano` foram reescritos para refletir o novo modelo de combate (Flanquear vindo de um personagem do time diferente do atacante/defensor principal).
- Removida uma duplicata de definição de Flanquear (e das antigas Efeito Líder/Ação Sidekick sob os nomes Liderança/Sidekick) que existia em `Palavras-Chave → Efeitos`; Liderança e Sidekick foram mantidas ali como palavras-chave leves e opcionais que uma ação pode ter, sem relação com a antiga estrutura de passivas por posição, e sua redação foi corrigida.

Isso resolve por completo o achado 2.4 e parcialmente o achado 2.1 (a lista de mitigação já não cita Desviar/Reflexão como tokens). Os achados 2.1 (ordem de mitigação), 2.2 e 2.3 (antiga fila de combate) foram amplamente reescritos pela mudança de modelo de combate — ver seção 2 atualizada abaixo.

---

## 0-bis. Mudanças já aplicadas (rodada 2)

- **Ordem de mitigação de dano simplificada de 8 passos para 4 blocos + 1 passo inicial**, resolvendo o achado 2.1 e o caminho de melhoria 2:
  ```
  0. DOT
  1. Efeitos gerais e Imunidades: DefUp/DefDown, AtkUp/AtkDown, Imunidade a Dano
  2. Efeitos de Absorção de Dano: Escudo, depois Sobrevida
  3. Desviar/Reflexão/Reviver: Desviar ou Reflexão, depois Imunidade a Morte, depois Reviver
  4. Defesa do personagem
  ```
  Cada bloco agora tem uma frase de propósito ("o que ele faz") e a mecânica de cada efeito foi movida para dentro do bloco a que pertence, em vez de ficar solta em prosa antes da lista — atende ao caminho de melhoria 2 (facilitar o entendimento). Imunidade a Morte migrou do antigo passo isolado para dentro do Bloco 3, junto de Reviver, por compartilharem o mesmo gatilho (dano fatal).
- **Contradição Escudo x Dano Perfurante resolvida (achado 3.1, caminho de melhoria 1):** decisão de design foi que o efeito que ignora defesa afeta **apenas a defesa**, nunca o Escudo. A antiga palavra-chave/token "Ataque Perfurante" (que dizia ignorar defesa E escudo, a fonte da contradição) foi renomeada para **Ignorar Defesa** e seu texto corrigido para ignorar somente a defesa. "Dano Perfurante" (a categoria de tipo de dano em `Tipos de Dano`, que já dizia a coisa certa) foi mantida como está, sem mudar de nome — os dois conceitos continuam formalmente distintos (um é tipo de dano fixo da ação, outro é palavra-chave/token aplicável), mas agora dizem a mesma coisa em vez de se contradizerem, e uma nota cruzada foi adicionada em `Tipos de Dano` para deixar isso explícito.
  - Evitada uma colisão de nomes: já existia uma palavra-chave `Triturar (X)` com significado totalmente diferente (descarte do topo do deck do alvo) em `Controle de Cartas` — por isso o novo nome escolhido foi "Ignorar Defesa", não "Triturar".
  - A duplicata da definição (que existia tanto em `Efeitos Negativos e Positivos` quanto em `Palavras-Chave → Combate`) foi corrigida nos dois lugares.
- **Fila de Combate: adicionada regra para "sem personagens elegíveis"**, resolvendo a lacuna registrada na rodada 1 (achado 3.9/lacuna correspondente): se nenhum personagem em campo é elegível para nenhuma ação de combate (Flanquear ou ataque principal), a fase de combate se conclui sem nenhuma dessas ações, mas os efeitos/gatilhos que disparam pela fase de combate (antes, durante ou depois) continuam sendo ativados e resolvidos normalmente.

---

## 0-ter. Mudanças já aplicadas (rodada 3)

- **Tabela de apoio adicionada ao `Cálculo de Dano Complexo`**, resolvendo o achado 2.2 e o caminho de melhoria 13: logo após o parágrafo narrativo do exemplo com os personagens C e D, uma tabela resume passo a passo as seis ações de dano do combate (DOT, passiva contra C, passiva contra D, Flanquear do atacante contra C, Flanquear do atacante contra D, ataque principal), mostrando alvo, dano bruto, qual bloco de `Como Mitigar Dano` resolveu cada uma e o resultado final. A tabela não substitui o texto narrativo original — foi adicionada como complemento visual, referenciando os blocos pelo nome definido na rodada 2 (Bloco 0/2/3/4).

---

## 0-quater. Mudanças já aplicadas (rodada 4)

- **`Power System` movido para dentro de `Personagens`**, resolvendo parte do caminho de melhoria 3: a antiga seção de topo `## Power System` (que ficava entre `Palavras-Chave` e `Outras formas de jogar`) virou uma subseção `#### Power System` dentro de `### Personagens`, logo após a definição de Ações — reflete que o Power System sempre foi, na prática, só o guia de criação de novas cartas de personagem, não um sistema paralelo. `Arquétipos dos Tipos de Personagem` foi junto, um nível abaixo, como `##### Arquétipos dos Tipos de Personagem`.
- **Desduplicação da lista de 12 tipos de personagem**, resolvendo por completo o achado 3.6 e o restante do caminho de melhoria 3: a lista completa (nome, tema, exemplos) continua existindo só na definição de `Tipo` dentro de `Personagens`. Os 12 blocos de `Arquétipos`, que antes reabriam cada tipo com um parágrafo de reapresentação (nome/tema/exemplos) redundante com essa lista, foram reescritos como uma entrada compacta em negrito por tipo (`**Natureza**: ...`), mantendo apenas o conteúdo que não existia em nenhum outro lugar: possibilidades mecânicas e a linha de estratégia (Aggro, Suporte, Stall, etc.). O parágrafo de abertura de `Arquétipos` também foi ajustado para não reintroduzir a redundância.
- **Índice (`## INDEX`) reorganizado** para refletir a nova hierarquia: `Power System` e `Arquétipos` viraram itens aninhados sob `Personagens`, e a entrada de topo duplicada de `Power System` foi removida.

---

## 0-quinquies. Mudanças já aplicadas (rodada 5)

- **DOT soma em cada ação de dano direto, não uma vez por combate** — resolvendo por completo o achado 3.5/3.9 e o caminho de melhoria 12, que era o item de maior prioridade pendente. Decisão de design: se um personagem tem N tokens de DOT e sofre múltiplas ações de dano direto no mesmo combate (ex.: Flanquear do defensor, Flanquear do atacante, ataque principal), o valor de DOT é somado ao dano bruto de **cada uma** dessas ações separadamente, não apenas uma vez. A definição em `Tipos de Dano` (Dano Contínuo), a entrada do token DOT (em `Efeitos Negativos e Positivos`) e o Passo 0 de `Como Mitigar Dano` foram todos ajustados para deixar isso explícito e cruzado entre si.
- **`Cálculo de Dano Complexo` reescrito com declarações explícitas**, resolvendo o pedido de facilitar o entendimento do exemplo e recalculado para refletir a nova regra de DOT por ação: o texto agora abre com uma lista de "Personagens envolvidos" (atributos de C e D) e uma lista numerada de "Ações resolvidas" antes de entrar no cálculo, além de uma nota "Importante" chamando atenção para o novo comportamento do DOT. A narrativa do cálculo e a tabela (da rodada 3) foram recalculadas: como o DOT de C agora soma em cada uma das três ações que o têm como alvo (passiva, Flanquear do atacante, ataque principal), o caminho de mitigação mudou — o Escudo de C se esgota já na primeira ação, a Sobrevida e a defesa entram nas ações seguintes — ainda que o resultado final (C com 6 de vida, D usando Reviver) tenha permanecido o mesmo por coincidência dos números escolhidos.
- **`Aposte (X)` ganhou descrição parentética na primeira aparição real da palavra-chave** (na definição do token Infecção, em `Efeitos Negativos e Positivos`), resolvendo o achado 3.2 e o caminho de melhoria 6: `Aposte (1) (jogue 1 moeda ou dado (...) - se ganhar)`, com link cruzado para a definição completa em `Palavras-Chave → Dinâmicas de Jogo`.
- **Componente físico do sorteio padrão formalizado como D4**, resolvendo o achado 3.3 e o caminho de melhoria 7: o parágrafo de sorteio em `Executando Ações` foi reescrito com a regra completa —
  - Padrão: 1 D4, resultado indica a posição do personagem-alvo (1 = principal, 2-4 = time).
  - Sem D4: usa-se outra moeda/dado, testando um personagem-alvo possível de cada vez (cara/par = escolhido; senão, passa ao próximo); se nenhum "acertar" depois de testados todos, quem executa a ação escolhe livremente entre eles.
  - Se a ação abrange personagens de ambos os jogadores: primeiro um lançamento único decide o jogador-alvo (cara/par = quem executa a ação, coroa/ímpar = oponente - o D4 não se aplica a esta etapa), depois o sorteio de personagem segue o procedimento acima dentro dos personagens do jogador sorteado.

---

## 0-sexies. Mudanças já aplicadas (rodada 6)

- **Exemplo de declaração de combate adicionado**, resolvendo por completo o remanescente do caminho de melhoria 13 (o último item pendente de maior prioridade): o `Exemplo de combate` em `Fila de Combate` foi dividido em duas partes explícitas, `Declaração` e `Resolução`. A parte de `Declaração` é nova e ilustra, passo a passo, um caso concreto de Flanquear dos dois jogadores no mesmo combate: o defensor precisa declarar sua ação com Flanquear (passo 2) sem ainda saber se o atacante também usará Flanquear ou qual será o alvo do ataque principal; só depois o atacante declara (passo 3), já com essa informação. A parte de `Resolução` é o exemplo que já existia desde a rodada 1, mantido sem alterações de conteúdo, apenas com o cabeçalho `**Resolução:**` adicionado para deixar a divisão clara. Uma nota final reforça a assimetria de informação entre defensor e atacante na fase de declaração.

---

## 0-septies. Mudanças já aplicadas (rodada 7)

- **Regras de precedência de alvo entre Provocação, Invisibilidade, Visão Verdadeira e Amedrontar**, resolvendo por completo o achado 3.4 e o caminho de melhoria 8 (item de maior prioridade pendente):
  - Provocação e Invisibilidade são mutuamente exclusivos por personagem: um personagem não pode receber um dos dois tokens se já tiver o outro.
  - Se algum personagem do time-alvo tem Provocação, a ação deve mirar um personagem com Provocação (todos são elegíveis se houver mais de um); em ações com múltiplos alvos, pelo menos um dos alvos deve ter Provocação.
  - Personagens com Invisibilidade não são elegíveis como alvo, exceto: (a) se todo o time-alvo tiver Invisibilidade, todos passam a ser elegíveis; ou (b) em ações com múltiplos alvos, depois de esgotados todos os alvos possíveis sem Invisibilidade, os alvos com Invisibilidade tornam-se elegíveis, na quantidade necessária para completar o total exigido pela ação (a ordem de preenchimento é: primeiro todos os sem Invisibilidade, só depois os com Invisibilidade).
  - Visão Verdadeira ignora as regras acima por completo.
  - Amedrontar não interage com nenhuma delas, pois seu gatilho ("ser alvo de ações de combate") não envolve escolha de alvo.
  - As entradas de Invisibilidade, Provocação e Visão Verdadeira em `Efeitos Negativos e Positivos`, e de Amedrontar em `Palavras-Chave → Efeitos`, foram reescritas para refletir essas regras.
- **Decisão de design revertida: `Ignorar Defesa` volta a ignorar defesa E escudo**, reabrindo intencionalmente a decisão da rodada 2. `Dano Perfurante` (categoria de tipo de dano) continua ignorando só a defesa, sem afetar o escudo - agora os dois conceitos têm comportamentos deliberadamente diferentes (Ignorar Defesa é mais forte), cada um com texto próprio e claro, sem reintroduzir a contradição original (que era sobre os dois dizerem coisas incompatíveis sobre o mesmo comportamento). As duas ocorrências de `Ignorar Defesa` (`Efeitos Negativos e Positivos` e `Palavras-Chave → Combate`) e a nota cruzada em `Dano Perfurante` foram atualizadas. Isso substitui a resolução do achado 3.1/caminho de melhoria 1 da rodada 2 - ver achado 3.10 atualizado.
- **`Ataque Expansível` e `Ação Expansível` mantidas como palavras-chave distintas, com escopo esclarecido**: `Ataque Expansível` é exclusiva de ações que causam dano; `Ação Expansível` é para ações que geram outros tipos de efeito. Cada entrada agora linka para a outra, evitando a confusão de nomes quase idênticos sem precisar consolidar as duas em uma só. Resolve o achado 3.7 e o caminho de melhoria 9.
- **Glossário de palavras-chave adicionado**, resolvendo o caminho de melhoria 4: uma nova seção `## Glossário de Palavras-Chave`, inserida logo após `## Outras formas de jogar` (antes de `## Licença`), lista alfabeticamente as ~40 palavras-chave do jogo (incluindo as adicionadas/renomeadas nas rodadas anteriores), cada uma com uma mini-descrição de uma linha e um link de volta para a subseção de `Palavras-Chave` onde está definida em detalhe. O índice (`## INDEX`) foi atualizado com as novas entradas `Outras formas de jogar` e `Glossário de Palavras-Chave`.
- **Revisão ortográfica geral do restante do documento**, resolvendo por completo o caminho de melhoria 10: corrigidas as recorrências de "persoangem"/"persongem", "Elementoal", "parenteses", "Silencio"/"Furia" (sem acento), "consequencia(s)", "resoluçaõ", "inciar", "sepadors", "previnido"/"previnir", "energia energia" duplicado, "calculo", "definção", "qualuqer", entre outras, em todas as seções do documento (Intro, Personagens, Itens, Fontes de Energia, Montagem do Deck, Iniciando o Jogo, Fases do Turno, Palavras-Chave, Power System, Licença).
- **Seção `## Revisão` (changelog) atualizada**, resolvendo o caminho de melhoria 11: adicionadas as entradas v0.2 a v0.8 descrevendo cada uma das rodadas 1 a 7 desta sessão, com o campo de hash marcado como `[pendente]` até que as mudanças sejam commitadas (nenhuma das mudanças desta sessão havia sido commitada no momento da edição).

---

## 0-octies. Mudanças já aplicadas (rodada 8)

- **Licenciamento trocado de GNU AGPL v3.0 para Creative Commons Atribuição-NãoComercial-CompartilhaIgual 4.0 (CC BY-NC-SA 4.0)**, a pedido explícito do autor para reforçar a proteção contra uso comercial não autorizado. A AGPL é uma licença de copyleft pensada para software (código-fonte, execução em rede) e não é a ferramenta usual para proteger conteúdo textual como um sistema de regras de jogo - a CC BY-NC-SA 4.0 é o padrão de mercado para esse tipo de material, com a mesma lógica de "copyleft" (obriga derivados a usar a mesma licença) mas com a cláusula de não-comercialização redigida em termos diretos e amplamente reconhecidos. A seção `## Licença` foi reescrita para refletir a nova licença, seus termos (atribuição, não-comercial, compartilha-igual) e o link para o texto oficial em português. Uma entrada v0.9 foi adicionada ao changelog em `## Revisão`.
- Vale registrar: nenhuma licença aberta (incluindo esta) impede cópia/reprodução por completo - todas permitem algum uso por terceiros, sob condições. Se a intenção fosse impedir qualquer uso de terceiros, a resposta correta seria remover a licença aberta e reservar todos os direitos ("Todos os direitos reservados"), não trocar de uma licença aberta para outra. O autor confirmou que o objetivo específico era bloquear uso comercial sem autorização, não bloquear todo e qualquer uso - por isso a CC BY-NC-SA 4.0 foi a escolha adequada.

---

## 1. O que é simples e está bem resolvido

Estas seções são curtas, autocontidas e não geram dúvida de arbitragem:

- **Tipos de Carta** (Personagens / Itens / Fontes de Energia) — divisão clara em três categorias, cada uma com um papel óbvio.
- **Áreas do Jogo** — cinco regiões bem definidas (Posição Principal, Time, Pedras de Recompensa, Deck, Descarte) mais a mão. Sem sobreposição de conceitos.
- **Montagem do Deck** — limites numéricos objetivos (60 cartas, mínimo 12 personagens/energias, cópias por raridade). Fácil de validar mecanicamente.
- **Sideboard** — regra pequena e objetiva (até 15 cartas, troca 1-por-1).
- **Iniciando o Jogo / Primeiro Turno** — sequência linear de setup, sem ramificações complexas.
- **Fases do Turno** (Limpeza → Compra → Principal → Combate) — estrutura de alto nível clara, cada fase tem um propósito único.
- **Condições de Vitória** — três condições, mutuamente claras.
- **Palavras-chave de Controle de Cartas** (Clarividência, Triturar, Reciclar Fonte/Recursos, etc.) — cada uma é uma regra isolada, sem interação entre si.

Essas partes têm baixo risco: mudanças aqui tendem a ser aditivas (novas palavras-chave) e não exigem reescrever lógica existente.

---

## 2. O que é complexo

### 2.1 ~~Ordem de mitigação de dano~~ — Resolvido na rodada 2

A antiga lista de 8 passos foi substituída por 4 blocos temáticos + 1 passo inicial de DOT (ver seção 0-bis). Isso resolve as duas críticas estruturais que este achado registrava: a mistura de efeitos que alteram o alvo com efeitos que absorvem dano agora está separada em blocos distintos (Bloco 2 = absorção, Bloco 3 = redirecionamento/último recurso), e cada bloco tem uma frase de propósito explicando o que ele faz antes de listar os efeitos, o que ataca diretamente a dificuldade de leitura original.

A defesa do personagem continua sendo o último passo (Bloco 4) — isso foi mantido deliberadamente, não é mais uma inconsistência: os 3 exemplos de `Cálculo de Dano` já aplicavam a defesa como a mitigação "principal" de cada ataque individual (não como último recurso do combate inteiro), então a ordem narrada nos exemplos e a ordem declarada nos blocos são coerentes entre si.

### 2.2 ~~Cálculo de Dano Complexo~~ — Resolvido na rodada 3

O terceiro exemplo (Personagem C e D) foi reescrito na rodada 1 para refletir o novo modelo de combate (Flanquear do defensor, Flanquear do atacante, ataque principal), mantendo a mesma riqueza mecânica original (resistência de tipo, Escudo, DOT, Sobrevida, Reviver). Era o trecho mais denso de acompanhar do documento — a rodada 3 adicionou uma tabela logo após o texto narrativo, detalhando cada uma das ações de dano (alvo, dano bruto, bloco de mitigação usado, resultado), o que reduz bastante o esforço de releitura necessário para seguir o exemplo. Ver seção 0-ter.

Na rodada 5, o exemplo ganhou listas explícitas de "Personagens envolvidos" e "Ações resolvidas" antes do cálculo, e foi recalculado por completo para refletir a nova regra de DOT somando em cada ação de dano (não uma vez por combate) — ver seção 0-quinquies.

### 2.3 ~~Fila de Combate (modelo novo, pós-rodada 1)~~ — Resolvido na rodada 2

A antiga ambiguidade sobre pilha de respostas (LIFO/FIFO não declarado) foi parcialmente resolvida: a nova Fila de Combate declara explicitamente resolução **FIFO** para a sequência estrutural (Flanquear do defensor → Flanquear do atacante → ataque principal). Isso é uma melhoria real de clareza.

Complexidade nova introduzida pelo modelo:
- Um único combate agora pode gerar **até três ações de dano independentes** (Flanquear defensor, Flanquear atacante, ataque principal), cada uma mitigada separadamente — mais estados para o jogador acompanhar do que o modelo anterior (ataque + 1 resposta).
- A declaração em duas etapas (defensor informa Flanquear antes do atacante declarar o seu) exige que os jogadores segurem informação e decidam em uma ordem específica — isso é uma mecânica de informação bem mais fina do que o resto do documento usa em qualquer outro lugar, o que a torna a parte mais "avançada" das regras de combate.
- **Resolvido na rodada 6**: o exemplo de combate em `Fila de Combate` agora tem uma parte de `Declaração` explícita, ilustrando passo a passo um caso com Flanquear dos dois jogadores, além da parte de `Resolução` que já existia. Ver caminho de melhoria 13 e seção 0-sexies.
- **Resolvido na rodada 2**: o que acontece se não houver nenhum personagem elegível em campo para nenhuma ação de combate agora está definido explicitamente — a fase de combate se conclui sem ação, mas os efeitos/gatilhos que disparam pela fase de combate continuam sendo resolvidos normalmente. Ver seção 0-bis.

### 2.4 ~~Tokens: permanente vs. temporário vs. definitivo~~ — Resolvido na rodada 1

Este achado foi meta da rodada 1: a regra de temporalidade agora é uma única convenção geral ("todo token some na próxima limpeza do controlador") com quatro exceções nomeadas (DOT, Perdição, Sonolência, Infecção). A categoria "definitivo" foi removida por não ter nenhum token atribuído a ela. Ver seção 0.

### 2.5 ~~Power System~~ — Resolvido na rodada 4

Não é "regra de jogo" no sentido de arbitragem, mas é a subseção mais longa e a mais **subjetiva** do documento — orienta como equilibrar um personagem novo usando faixas de vida/dano/defesa e "arquétipos" descritivos por tipo. Complexidade aqui não é de lógica, é de **julgamento de design**: não há fórmula fechada ligando "vida + defesa + dano do efeito" a um custo de energia recomendado, o que torna o balanceamento dependente inteiramente da experiência de quem cria a carta.

Na rodada 4, Power System deixou de ser uma seção de topo nível (`##`) e passou a ser `#### Power System`, subseção de `### Personagens` (ver seção 0-quater) — reflete melhor sua real função (guia de criação de cartas de personagem) e elimina a estranheza de ter "Power System" listado como se fosse uma área de regras paralela às demais (Combate, Efeitos, etc.). A subseção `Arquétipos`, dentro dela, também foi reescrita para não repetir mais a lista de tipos já definida em `Personagens` — ver achado 3.6 (resolvido).

---

## 3. Ambiguidades e contradições identificadas

1. **~~Escudo x Dano Perfurante — contradição direta~~ — Resolvido na rodada 2.**
   - `Tipos de Dano` (Dano Perfurante) sempre disse: escudo mitiga mesmo contra esse tipo de dano.
   - A antiga palavra-chave/token `Ataque Perfurante` dizia o oposto: ignorava defesa e escudo.
   
   Decisão de design: **escudo sempre mitiga; apenas a defesa pode ser ignorada.** A palavra-chave foi renomeada para `Ignorar Defesa` e seu texto corrigido para não afetar mais o Escudo. Ver seção 0-bis. Um novo achado nasceu dessa correção — ver item 10 abaixo (distinção entre "Dano Perfurante" e "Ignorar Defesa").

2. **~~`Aposte (X)` é usada antes de ser definida~~ — Resolvido na rodada 5.** A primeira aparição real da palavra-chave (na definição do token Infecção) ganhou uma breve descrição entre parênteses e um link cruzado para a definição completa em `Palavras-Chave → Dinâmicas de Jogo`. Ver seção 0-quinquies.

3. **~~`Confusão` depende de "moeda" sem definir a moeda~~ — Resolvido na rodada 5.** O parágrafo de sorteio em `Executando Ações` agora formaliza o D4 como componente padrão (resultado = posição do alvo), define o procedimento de fallback com moeda/outro dado (testar um a um até cara/par, ou escolha livre de quem executa a ação se nenhum acertar), e cobre o caso de a ação abranger ambos os jogadores (sorteio de jogador primeiro, depois de personagem). Ver seção 0-quinquies. Isso também esclarece por extensão o componente usado em `Confusão` e nos demais efeitos que citam "cara ou coroa"/"número par".

4. **~~Ordem de resolução entre efeitos concorrentes de alvo (Provocação, Invisibilidade, Visão Verdadeira, Amedrontar)~~ — Resolvido na rodada 7.** Provocação e Invisibilidade agora são mutuamente exclusivos por personagem; Provocação sempre obriga a escolha de alvo (com regras para múltiplos alvos com Provocação); Invisibilidade protege o alvo exceto quando todo o time-alvo é invisível ou quando a ação precisa de mais alvos do que personagens elegíveis sem Invisibilidade existem; Visão Verdadeira ignora tudo; Amedrontar não interage por não envolver escolha de alvo. Ver seção 0-septies.

5. **~~Dano Contínuo (DOT) descrito duas vezes, com nuance diferente~~ — Resolvido na rodada 5.** Decisão de design: DOT soma ao dano bruto de **cada** ação de dano direto que tiver o personagem como alvo, não uma vez por combate. `Tipos de Dano`, a entrada do token DOT e o Passo 0 de `Como Mitigar Dano` foram sincronizados para dizer isso de forma explícita e cruzada. Ver seção 0-quinquies e achado 3.9 (mesmo problema de fundo).

6. **~~Repetição integral da lista de 12 tipos de personagem~~ — Resolvido na rodada 4.** A lista completa (nome, tema, exemplos) continua existindo apenas na definição de `Tipo` em `Personagens`. Os blocos de `Arquétipos dos Tipos de Personagem` foram reescritos para não reabrir mais cada tipo com um parágrafo de reapresentação — hoje contêm só o conteúdo que não estava duplicado (possibilidades mecânicas e linha de estratégia por tipo), em formato compacto. Ver seção 0-quater.

7. **~~`Ação Expansível` definida duas vezes com nomes ligeiramente diferentes~~ — Resolvido na rodada 7.** São duas palavras-chave distintas por design, não uma duplicata: `Ataque Expansível` é para ações que causam dano, `Ação Expansível` é para ações que geram outros efeitos. Cada entrada agora referencia a outra para evitar a confusão de nomes parecidos. Ver seção 0-septies.

8. **~~Duplicata de Flanquear/Efeito Líder/Ação Sidekick~~ — Resolvido na rodada 1.** A reescrita da seção `Personagens` (rodada 1) removeu Efeito Líder e Ação Sidekick de lá, mas uma segunda definição dessas mesmas ideias — sob os nomes `Liderança`, `Sidekick` e uma terceira cópia de `Flaquear` com erros de digitação próprios — sobrevivia, não detectada, em `Palavras-Chave → Efeitos`. Corrigido durante a atualização desta análise: a duplicata de Flanquear foi removida (mantendo apenas a definição em `Palavras-Chave → Combate`), e Liderança/Sidekick foram mantidas como palavras-chave leves e opcionais (sem relação com a antiga estrutura de passivas por posição), com a redação corrigida.

9. **~~DOT dentro do novo modelo de combate~~ — Resolvido na rodada 5.** Decisão de design confirmada: soma em cada uma das até três ações de dano possíveis por combate (Flanquear defensor, Flanquear atacante, ataque principal), não uma vez só. Mesmo achado que o item 5 acima. Ver seção 0-quinquies.

10. **~~"Dano Perfurante" e "Ignorar Defesa" descreviam o mesmo comportamento~~ — Decisão revertida na rodada 7.** A rodada 2 havia unificado o comportamento dos dois (ambos ignoram só a defesa) para resolver a contradição original. A rodada 7 reabriu essa decisão intencionalmente: `Ignorar Defesa` agora ignora defesa E escudo (mais forte), enquanto `Dano Perfurante` continua ignorando só a defesa - os dois voltam a ter comportamentos diferentes, mas agora cada um com texto próprio e sem contradição entre si (a contradição original era dois textos dizendo coisas incompatíveis sobre a mesma mecânica; agora são duas mecânicas diferentes, cada uma consistente internamente). Ver seção 0-septies.

---

## 4. Lacunas (o que falta)

- **Desempate de simultaneidade**: quando dois jogadores têm efeitos para resolver "ao mesmo tempo" (ex: ambos ativam passiva no mesmo passo da fila de combate), não há regra de quem decide a ordem.
- **Limite de tokens empilháveis**: não é dito se hosts de token (Escudo, DOT, Sobrevida etc.) têm limite máximo por personagem.
- **Zonas de "fora do jogo"** (mencionadas para cartas Épicas) não têm seção própria — não fica claro se interagem com efeitos que dizem respeito à pilha de descarte.
- **Como funciona "revelar" a mão** não tem mecânica própria — quando um efeito pede revelação, não há regra de quanto tempo a informação fica pública nem se o oponente pode agir sobre ela imediatamente.
- ~~**Glossário/índice de palavras-chave**~~ — **Resolvido na rodada 7**: nova seção `## Glossário de Palavras-Chave`, inserida após `Outras formas de jogar`, lista todas as palavras-chave em ordem alfabética com mini-descrição e link de volta para a definição completa. Ver seção 0-septies.
- **Mulligan**: definido, mas não linkado no índice do documento (`## INDEX` não lista Mulligan nem Sideboard como entradas próprias, embora sideboard tenha `###`).
- **(Nova, pós-rodada 1) Comportamento de DOT com múltiplas ações de dano no mesmo combate**: ver achado 3.9. Com o novo modelo de Flanquear, um combate pode ter até três ações de dano — não está definido se DOT soma uma vez por combate ou uma vez por ação. Ainda pendente.
- **(Nova, pós-rodada 1) Sem exemplo de combate completo com Flanquear de ambos os lados**: o exemplo em `Fila de Combate` ilustra passivas + resposta de item, mas nenhum dos exemplos do documento mostra passo a passo a declaração e resolução de um combate com Flanquear do defensor e do atacante ao mesmo tempo, que é hoje o caso mais complexo de se arbitrar corretamente. Ainda pendente.
- ~~O que acontece se o defensor não tem personagem elegível para Flanquear~~ — **Resolvido na rodada 2**, e generalizado: agora cobre qualquer ausência de personagem elegível para qualquer ação de combate (não só Flanquear), não apenas o caso do defensor. Ver seção 0-bis.

---

## 5. Problemas de forma (não afetam a lógica, mas afetam legibilidade)

- ~~Erros de digitação recorrentes~~ — **Resolvido na rodada 7**: corrigidas as recorrências de "persoangem"/"persongem", "Elementoal", "cargas" por "cartas", "sepadors", "oela", "váriadas", "infrormações", "constriuir", "energia energia" duplicado, "parenteses", "Silencio"/"Furia" sem acento, "consequencia(s)", "resoluçaõ", "inciar", "previnido"/"previnir", "calculo", "definção", "qualuqer", entre outras, em todo o documento. Ver seção 0-septies.
- Inconsistência de maiúsculas em nomes de token/palavra-chave (ex.: "Vida" usado tanto como atributo quanto como nome de Tipo, o que pode confundir buscas em texto). Não tocado - baixo risco, deixado como está.
- ~~A seção `## Revisão` (changelog manual) parou em v0.1~~ — **Resolvido na rodada 7**: adicionadas as entradas v0.2 a v0.8 cobrindo as rodadas 1 a 7, com hash marcado como `[pendente]` até o commit. Ver seção 0-septies.

---

## 6. Caminhos de melhoria propostos

Ordenados por impacto na jogabilidade (não por esforço). Itens concluídos ao longo das 7 rodadas estão marcados e mantidos para histórico.

1. **~~Resolver a contradição Escudo vs. Dano Perfurante~~ — Concluído na rodada 2, decisão parcialmente revertida na rodada 7.** A rodada 2 decidiu que só a defesa pode ser ignorada. A rodada 7 reabriu essa decisão: `Ignorar Defesa` agora ignora defesa e escudo, enquanto `Dano Perfurante` continua ignorando só a defesa - sem reintroduzir a contradição original, já que agora são duas mecânicas com textos próprios e consistentes. Ver seção 0-septies e achado 3.10.

2. **~~Reescrever `Como Mitigar Dano` de forma mais didática~~ — Concluído na rodada 2.** A ordem de 8 passos virou 4 blocos temáticos + 1 passo inicial de DOT, cada um com uma frase de propósito e a mecânica de cada efeito explicada dentro do bloco a que pertence. Ver seção 0-bis e achado 2.1. Complementado na rodada 3 pela tabela do `Cálculo de Dano Complexo` (item 13 abaixo), que aplica esses mesmos blocos a um exemplo numérico completo.

3. **~~Unificar a lista de tipos de personagem em um único lugar~~ — Concluído na rodada 4.** A definição completa (nome, tema, exemplos) ficou só em `Personagens`; `Power System → Arquétipos` (agora subseção de `Personagens`) mantém apenas as sugestões de mecânica/estratégia por tipo, em formato compacto. Ver seção 0-quater e achado 3.6.

4. **~~Adicionar um glossário alfabético único de palavras-chave~~ — Concluído na rodada 7.** Nova seção `## Glossário de Palavras-Chave` após `Outras formas de jogar`. Ver seção 0-septies.

5. **~~Definir formalmente as categorias de duração de token~~ — Concluído na rodada 1.** A regra geral de temporalidade (tudo temporário, com exceções nomeadas) substituiu as três categorias antigas e a repetição de "token permanente" nas 11 entradas foi removida. Ver seção 0.

6. **~~Definir "Aposte (X)" antes de seu primeiro uso~~ — Concluído na rodada 5.** Ver seção 0-quinquies e achado 3.2.

7. **~~Especificar o componente físico do sorteio~~ — Concluído na rodada 5.** D4 formalizado como padrão, com procedimento de fallback (moeda/outro dado) e regra para ações que abrangem ambos os jogadores. Ver seção 0-quinquies e achado 3.3.

8. **~~Formalizar a regra de prioridade de alvo~~ — Concluído na rodada 7.** Regras de exclusividade mútua (Provocação/Invisibilidade), obrigatoriedade de Provocação, proteção condicional de Invisibilidade, precedência de Visão Verdadeira e não-interação de Amedrontar. Ver seção 0-septies e achado 3.4.

9. **~~Consolidar `Ataque Expansível` e `Ação Expansível`~~ — Concluído na rodada 7.** Mantidas como palavras-chave distintas (dano vs. efeito), com referência cruzada entre elas. Ver seção 0-septies e achado 3.7.

10. **~~Revisão ortográfica geral~~ — Concluído na rodada 7.** Todo o documento revisado. Ver seção 0-septies.

11. **~~Atualizar ou formalizar a seção `## Revisão`~~ — Concluído na rodada 7.** Entradas v0.2 a v0.8 adicionadas, hash como `[pendente]` até o commit. Ver seção 0-septies.

12. **~~Definir se DOT soma uma vez por combate ou uma vez por cada ação de dano resolvida~~ — Concluído na rodada 5.** Decisão: soma em cada ação. Ver achados 3.5 e 3.9 e seção 0-quinquies.

13. **~~Adicionar uma tabela/passo-a-passo visual ao `Cálculo de Dano Complexo`~~ — Concluído na rodada 3** (ver seção 0-ter). **Remanescente (exemplo de declaração de combate) concluído na rodada 6.** O `Exemplo de combate` em `Fila de Combate` foi dividido em duas partes explícitas: `Declaração` (os 3 passos já descritos em prosa logo acima, agora ilustrados com um caso concreto de Flanquear dos dois jogadores - o defensor declara sua ação com Flanquear sem ainda saber o ataque principal do atacante, e o atacante declara por último, já sabendo do Flanquear do defensor) e `Resolução` (o exemplo que já existia, mantido como estava). Uma nota final reforça a ordem de informação assimétrica entre defensor e atacante na declaração. Ver seção 0-sexies.

14. **~~Avaliar se "Dano Perfurante" e "Ignorar Defesa" deveriam se unificar~~ — Decisão tomada na rodada 7, na direção oposta.** Em vez de unificar, os dois foram deliberadamente diferenciados (Ignorar Defesa mais forte, afeta escudo; Dano Perfurante mais fraco, não afeta). Ver achado 3.10 e seção 0-septies.

---

## 7. Sugestão de sequenciamento

Todos os 14 itens do caminho de melhoria (seção 6) e todas as lacunas pontuais (seção 4) foram resolvidos ao longo das 7 rodadas desta sessão. Os únicos itens remanescentes, de baixo risco e sem urgência de jogo, são:

- **Lacunas estruturais mais amplas, fora do escopo das rodadas até aqui** (seção 4): desempate de simultaneidade entre efeitos "ao mesmo tempo", limite de tokens empilháveis por personagem, zonas de "fora do jogo" para cartas Épicas, e a mecânica de "revelar" a mão. Nenhuma delas foi apontada pelo autor como prioridade nas rodadas 1-7; valem uma rodada futura dedicada caso surjam dúvidas de mesa concretas sobre elas.
- **Inconsistência de maiúsculas em "Vida"** (atributo vs. nome de Tipo) — registrada na seção 5, considerada baixo risco e deixada como está.
- **Preencher os hashes de commit `[pendente]`** na seção `## Revisão` do `rules.md` assim que as mudanças desta sessão forem commitadas.

Não há mais um item de prioridade máxima pendente no momento - o documento está, pela primeira vez desde o início desta análise, sem nenhuma contradição, ambiguidade de combate ou lacuna de alta prioridade em aberto.
