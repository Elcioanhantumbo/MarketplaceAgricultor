# AGROLINK MZ — Documento de Estruturação do Projecto

Modelo de Negócio e Regras de Negócio — Versão 2.0 (Final)
Sistema Web responsivo adaptado ao contexto moçambicano
Plataforma B2B de comercialização agrícola — corredor Dondo/Nhamatanda — Beira

## Nota de escopo e versão

Esta é a versão consolidada e final do documento de estruturação do AgroLink MZ. Integra num único documento a estrutura do projecto, as melhorias recomendadas na revisão crítica e regras de negócio mais consistentes, com a stack tecnológica seleccionada para o contexto moçambicano e secções dedicadas à logística de transporte e aos pagamentos.

Nesta fase, o AgroLink MZ será desenvolvido como um **sistema web responsivo**, com experiência mobile obtida através de uma interface leve (e PWA opcional), sem aplicação Android ou iOS nativa. O backend, a API e a base de dados serão únicos para desktop e mobile, e a arquitectura ficará preparada para uma futura aplicação nativa sem reescrita do backend.

## 1. Resumo Executivo

O AgroLink MZ é uma plataforma web de comercialização agrícola B2B que liga produtores agrícolas a compradores profissionais, começando pelo corredor Dondo/Nhamatanda — Beira. A plataforma reduz dificuldades de escoamento, descoberta de compradores, organização de pedidos, transporte e acesso a informação comercial.

O sistema não é um simples site de anúncios. O objectivo é facilitar transacções agrícolas reais, acompanhando o ciclo desde a publicação da oferta até à entrega, ao pagamento e à conclusão do pedido. O valor da plataforma — e a base para a cobrança de comissão — está em concentrar dentro do sistema aquilo que não existe fora dele: confiança verificável, coordenação de transporte, pagamento seguro e resolução de disputas.

## 2. Visão, Missão e Objectivos

### 2.1 Visão
Ser a infra-estrutura digital de referência para a comercialização agrícola em Moçambique.

### 2.2 Missão
Conectar produtores e compradores de forma simples, confiável e adaptada às condições económicas, digitais e de conectividade do mercado moçambicano.

### 2.3 Objectivos
- Facilitar a descoberta de produtos agrícolas disponíveis.
- Ajudar produtores a encontrar compradores reais e compradores a encontrar fornecedores confiáveis.
- Facilitar pedidos, negociação, transporte e pagamento de forma integrada.
- Criar mecanismos de confiança (verificação, avaliações, histórico, escrow).
- Organizar informação de produtos, quantidades, preços e localização.
- Coordenar a ligação com o transporte dos produtos.
- Construir uma base de dados que permita gerar inteligência de mercado.

## 3. Problema de Negócio

- Produtores têm produção disponível mas nem sempre encontram compradores em quantidade.
- Compradores profissionais têm dificuldade em encontrar fornecedores confiáveis.
- Existe pouca visibilidade sobre oferta, localização, quantidade e preço.
- A negociação é informal e dispersa por contactos pessoais e WhatsApp.
- O transporte é frequentemente o obstáculo que impede a conclusão da venda.
- Os pagamentos são informais e sem garantia para nenhuma das partes.
- A conectividade é limitada, sobretudo fora dos centros urbanos, e a literacia digital é variável.

## 4. Proposta de Valor

| Actor | Problema | Valor oferecido |
|---|---|---|
| Produtor | Dificuldade de encontrar compradores e de receber com segurança | Publicação da oferta, acesso a compradores, transporte coordenado e pagamento garantido |
| Comprador | Dificuldade de encontrar fornecedores confiáveis | Pesquisa de produtos e fornecedores verificados, pedido organizado e entrega acompanhada |
| Transportador | Dificuldade de encontrar cargas | Acesso a pedidos de transporte na sua rota |
| Administrador | Falta de controlo e de dados | Painel de gestão, verificações, disputas e relatórios |

## 5. Público-Alvo

### 5.1 Produtores
- Pequenos e médios produtores.
- Associações e cooperativas.
- Produtores com produção comercializável em volume.

### 5.2 Compradores
- Restaurantes, hotéis e supermercados.
- Grossistas e comerciantes.
- Agro-processadores.
- Instituições e empresas com compras recorrentes.

## 6. Modelo de Negócio e Receita

O modelo é B2B híbrido, com foco inicial em compradores profissionais. A regra estratégica mantém-se: não cobrar ao pequeno produtor pela publicação, para garantir baixa barreira de entrada na aquisição de oferta.

| Fonte de receita | Fase | Regra proposta |
|---|---|---|
| Assinatura de compradores | Inicial / MVP | Planos de 1.000 a 15.000+ MT/mês; é a receita âncora enquanto não há pagamento integrado |
| Comissão por transacção | Após integração de pagamento | 1%–3% (referência 2%), retida automaticamente no momento da libertação do pagamento |
| Comissão logística | Fase 2 | Percentagem sobre o serviço de transporte intermediado |
| Relatórios / inteligência de mercado | Fase 3 | Venda de informação agregada a clientes B2B |
| Serviços financeiros | Fase 3 | Apenas via parceiros; sem crédito próprio inicialmente |

Resolução da contradição da versão anterior: enquanto o pagamento não estiver integrado, a comissão por transacção não é cobrável de forma fiável. Por isso, a receita inicial assenta na assinatura de compradores (facturável e verificável), e a comissão por transacção passa a ser cobrada automaticamente só quando existir mobile money integrado com retenção (escrow) — ver secção 17.

## 7. Stack Tecnológica (seleccionada para o contexto moçambicano)

A escolha tecnológica prioriza páginas leves para redes 2G/3G, custo de operação baixo, robustez e disponibilidade de talento local. Opta-se por um monólito renderizado no servidor com uma fronteira de API limpa, em vez de uma aplicação de página única pesada — mantendo a porta aberta a uma futura app nativa e a componentes ricos apenas onde compensarem.

| Componente | Tecnologia seleccionada | Justificação no contexto MZ |
|---|---|---|
| Aplicação / Frontend | Laravel + Blade + Livewire (renderização no servidor); PWA leve | Páginas minúsculas para baixa largura de banda; um único deploy; interactividade só onde precisa |
| Backend / API | Laravel (PHP) | Talento PHP abundante em Moçambique; robusto e económico; API pronta para futura app nativa |
| Base de dados | PostgreSQL + PostGIS | Dados transacionais fiáveis + pesquisa geográfica por distância |
| Autenticação | Sessões Laravel + OTP por SMS; Sanctum (token) reservado para a futura API pública/app | Verificação real do número de telefone; segurança adequada |
| Notificações | SMS (agregador local) + WhatsApp; e-mail secundário | São os canais realmente usados pelo público-alvo |
| Pagamentos | Mobile money: M-Pesa, e-Mola, mKesh (via agregador) | Meios dominantes no país; ligados ao número de telefone |
| Imagens | Compressão/redimensionamento no servidor; object storage ao escalar | Reduz consumo de dados e custo |
| Infra-estrutura | 1 VPS (fase inicial), Docker opcional, backups automáticos testados, monitorização | Custo baixo com continuidade assegurada |

Evolução: quando o negócio o justificar, pode acrescentar-se uma camada rica (ex.: Next.js/React) ou uma app nativa reutilizando a mesma API, sem reescrever o backend. A base de dados pode migrar de um único VPS para um serviço gerido conforme a escala crescer.

## 8. Arquitectura do Sistema

Uma única aplicação web com API/backend central. Desktop e smartphone consomem os mesmos serviços e a mesma base de dados. Serviços externos (mobile money, SMS/WhatsApp) são integrados por trás do backend, nunca directamente pelo cliente.

Fluxo: Navegador (Desktop/Mobile) → Aplicação Web (Laravel/Livewire) → API/Backend → PostgreSQL/PostGIS → Serviços auxiliares (SMS, WhatsApp, Mobile Money, Armazenamento).

## 9. Perfis e Permissões

| Perfil | Responsabilidades principais |
|---|---|
| Produtor | Gerir perfil, publicar produtos, receber e aceitar/rejeitar pedidos, coordenar entrega e acompanhar vendas e pagamentos |
| Comprador | Pesquisar produtos, criar pedidos, escolher forma de entrega, pagar, acompanhar compras e avaliar fornecedores |
| Transportador (Fase 2) | Ver cargas na sua rota, aceitar entregas, actualizar estados de transporte |
| Administrador / Operador | Gerir utilizadores, verificações, categorias, pedidos, disputas, coordenação assistida de transporte e relatórios |

## 10. Módulos Funcionais do MVP

1. Autenticação, gestão de contas e verificação por OTP.
2. Perfis de produtor e comprador.
3. Categorias e produtos de referência.
4. Publicação e gestão de ofertas.
5. Pesquisa e filtros (incluindo por localização).
6. Pedidos de compra e gestão de estados.
7. Coordenação de entrega (registo e acompanhamento).
8. Registo de pagamento e cálculo de comissão.
9. Histórico e avaliações.
10. Painel administrativo e de operador.
11. Notificações por SMS/WhatsApp.
12. Mapa e localização básica.

## 11. Funcionalidades por Actor

### 11.1 Produtor
- Criar conta, verificar telefone por OTP e iniciar sessão.
- Completar perfil e informar localização da(s) propriedade(s).
- Criar, editar, pausar e encerrar ofertas com quantidade, unidade, preço e período de disponibilidade.
- Receber pedidos e aceitar ou rejeitar.
- Indicar forma de entrega e coordenar transporte.
- Confirmar pagamento e acompanhar libertação de fundos.
- Consultar vendas e histórico e avaliar compradores após a conclusão.

### 11.2 Comprador
- Criar conta, verificar telefone e iniciar sessão.
- Pesquisar por produto e filtrar por localização, quantidade, preço e disponibilidade.
- Ver o perfil e a reputação do produtor.
- Criar pedido, escolher forma de entrega e pagar.
- Acompanhar o estado do pedido e da entrega.
- Cancelar segundo as regras e consultar histórico.
- Avaliar produtores após a conclusão.

### 11.3 Administrador / Operador
- Painel com KPIs.
- Gerir utilizadores, categorias, ofertas e pedidos.
- Validar perfis (verificação) e gerir denúncias e disputas.
- Coordenar transporte assistido no piloto.
- Bloquear/desbloquear contas e consultar relatórios.

## 12. Regras de Negócio (consolidadas)

| ID | Regra | Definição |
|---|---|---|
| RN01 | Telefone único e verificado | Cada conta tem telefone único, confirmado por código OTP antes de operar |
| RN02 | Perfil mínimo | O produtor completa dados mínimos antes de publicar |
| RN03 | Oferta obrigatória | Produto tem nome, categoria, quantidade, unidade, preço, localização e disponibilidade |
| RN04 | Quantidade válida | A quantidade disponível nunca pode ser negativa |
| RN05 | Pedido válido | O comprador não pode pedir quantidade superior à disponível |
| RN06 | Reserva atómica | Ao aceitar o pedido, a quantidade é reservada numa transacção com bloqueio da linha da oferta |
| RN07 | Venda parcial | Uma oferta pode ser vendida a vários compradores até esgotar |
| RN08 | Estados da oferta | Disponível, Reservado, Vendido ou Expirado |
| RN09 | Estados do pedido | Pendente, Aceite, Em preparação, Pronto, Em transporte, Entregue, Concluído; Rejeitado/Cancelado como alternativos |
| RN10 | Histórico de estados | Toda a transição de estado do pedido é registada (quem, de, para, quando) |
| RN11 | Cancelamento | Livre antes da aceitação; após aceitação é registado e sujeito à política definida |
| RN12 | Disputa | Após a entrega, problemas são tratados como disputa, com estados e responsável administrativo |
| RN13 | Avaliação | Só participantes de uma transacção concluída podem avaliar |
| RN14 | Autorização | Um utilizador não altera dados de outro sem permissão administrativa |
| RN15 | Integridade em concorrência | Transacções concorrentes não vendem a mesma quantidade duas vezes (bloqueio pessimista ou versão optimista) |
| RN16 | Verificação | O selo de verificado só é atribuído após processo administrativo definido |
| RN17 | Expiração | Ofertas fora da data de disponibilidade deixam de aceitar novos pedidos |
| RN18 | Valores monetários | Preços e montantes em DECIMAL, moeda MZN; nunca vírgula flutuante |
| RN19 | Forma de entrega | Cada pedido define a forma de entrega: produtor entrega, comprador levanta, ou transporte intermediado |
| RN20 | Custo de transporte | Quando há transporte intermediado, o custo é acordado/estimado e integra o valor total do pedido |
| RN21 | Estados da entrega | Solicitada, Atribuída, Em recolha, Em trânsito, Entregue, Confirmada |
| RN22 | Confirmação de entrega | A entrega é confirmada pelo comprador (ou por prova de entrega) antes da conclusão |
| RN23 | Escrow | Com pagamento integrado, o valor pago fica retido pela plataforma até à confirmação de entrega |
| RN24 | Libertação e comissão | Após confirmação, a plataforma liberta o valor ao produtor, retendo automaticamente a comissão |
| RN25 | Estados do pagamento | Pendente, Pago, Retido, Libertado, Reembolsado, Falhado |
| RN26 | Idempotência de pagamento | Callbacks/retentativas do agregador não podem duplicar pagamentos nem comissões |
| RN27 | Reembolso em disputa | Em disputa procedente, o valor retido é reembolsado segundo a decisão administrativa |
| RN28 | Comissão configurável | Referência 2%, configurável pelo administrador |
| RN29 | Protecção de dados | Recolha de localização e dados pessoais com consentimento, minimização e retenção definida |
| RN30 | Auditoria | Acções administrativas sensíveis são registadas em trilho de auditoria |

## 13. Estados da Oferta

Fluxo principal: **DISPONÍVEL → RESERVADO → VENDIDO**. Uma oferta passa a **EXPIRADO** quando termina a validade.

## 14. Estados do Pedido

Fluxo principal: **PENDENTE → ACEITE → EM PREPARAÇÃO → PRONTO → EM TRANSPORTE → ENTREGUE → CONCLUÍDO**.
Fluxos alternativos: PENDENTE → REJEITADO; ACEITE → CANCELADO.
Cada transição é registada no histórico de estados (RN10).

## 15. Processo de Compra (ponta a ponta)

1. Produtor cria a oferta.
2. Comprador pesquisa e abre os detalhes.
3. Comprador informa a quantidade; o sistema valida a disponibilidade.
4. Comprador escolhe a forma de entrega e, se aplicável, solicita transporte.
5. Sistema cria o pedido com itens, entrega e valor total (produtos + transporte).
6. Produtor aceita ou rejeita; ao aceitar, a quantidade é reservada de forma atómica.
7. Comprador paga (mobile money); o valor fica retido em escrow (fase de pagamento integrado).
8. Transporte é coordenado e o produto entregue.
9. Comprador confirma o recebimento (ou é registada prova de entrega).
10. Sistema liberta o valor ao produtor, retém a comissão e conclui o pedido.
11. Ambas as partes podem avaliar-se.

## 16. Logística e Transporte dos Produtos

O transporte é frequentemente o factor que decide se a venda se concretiza. Por isso, a ligação ao transporte é tratada explicitamente desde o MVP — ainda que, na fase inicial, seja coordenada de forma assistida por um operador da plataforma.

### 16.1 Três formas de entrega
- **Comprador levanta (self-pickup)**: o comprador recolhe na propriedade/ponto do produtor. Sem custo de transporte na plataforma; o pedido regista apenas o ponto e a janela de levantamento.
- **Produtor entrega**: o produtor faz a entrega com meios próprios. O custo, se existir, é acordado e adicionado ao valor do pedido.
- **Transporte intermediado pela plataforma**: a plataforma liga o pedido a um transportador. É esta a ligação central e a base da comissão logística (Fase 2).

### 16.2 Como se faz a ligação pedido → transporte
Quando o comprador escolhe transporte intermediado, o sistema gera um registo de entrega (delivery) associado ao pedido, contendo: origem (localização da propriedade), destino (endereço do comprador), volume/peso estimado, tipo de produto, janela temporal e distância calculada (PostGIS).

Essa entrega passa então por um de dois modelos, consoante a fase:
- **Fase inicial (piloto) — coordenação assistida**: o operador da plataforma contacta um conjunto de transportadores parceiros locais da rota, acorda preço e prazo, e regista a atribuição e o custo no sistema. Toda a informação fica registada, mesmo sendo o contacto manual.
- **Fase 2 — atribuição na plataforma**: transportadores registados recebem, por SMS/WhatsApp/painel, as cargas disponíveis na sua rota. Aceitam, confirmam preço e prazo; o comprador confirma; a entrega é acompanhada por estados até à confirmação de recebimento.

### 16.3 Cálculo e integração do custo
- Distância estimada por PostGIS entre origem e destino.
- Custo por tarifa (por km e/ou por kg/volume) ou negociado, conforme o transportador.
- O custo de transporte integra o valor total do pedido (RN20), sendo visível ao comprador antes de confirmar.

### 16.4 Estados da entrega (RN21)
SOLICITADA → ATRIBUÍDA → EM RECOLHA → EM TRÂNSITO → ENTREGUE → CONFIRMADA. A confirmação da entrega (RN22) é o gatilho para a libertação do pagamento (secção 17).

### 16.5 O que fica de fora nesta fase
- Frota própria e armazéns próprios.
- Optimização automática de rotas e rastreamento GPS em tempo real (evolução futura).

## 17. Pagamentos

Os pagamentos usam os meios dominantes em Moçambique, ligados ao número de telefone — coerente com a identidade da conta. A abordagem é faseada para não bloquear o arranque na complexidade da integração, mas com um caminho claro para o pagamento seguro.

### 17.1 Meios de pagamento
- Mobile money: M-Pesa (Vodacom), e-Mola (Movitel) e mKesh (Tmcel).
- Transferência bancária e dinheiro na entrega, como opções registadas manualmente.

### 17.2 Fase inicial (piloto) — pagamento registado
No piloto, o pagamento é combinado entre as partes (mobile money directo ou na entrega) e registado na plataforma com o respectivo estado e referência. A receita da plataforma nesta fase vem da assinatura de compradores, que é facturável de forma fiável. Isto resolve a cobrança sem depender de integração imediata.

### 17.3 Fase de integração — mobile money com retenção (escrow)
Na fase seguinte integra-se o mobile money via agregador, introduzindo retenção de fundos (escrow), que resolve simultaneamente o risco de desintermediação e a cobrança automática da comissão:
1. O comprador paga o valor total (produtos + transporte) à plataforma.
2. A plataforma retém o valor (estado RETIDO) e notifica o produtor para preparar o pedido.
3. O transporte é feito e o comprador confirma a entrega (ou é registada prova de entrega).
4. A plataforma liberta o valor ao produtor e ao transportador, retendo automaticamente a comissão (RN24).
5. Em disputa procedente, o valor retido é reembolsado segundo a decisão administrativa (RN27).

### 17.4 Estados e integridade do pagamento
- Estados (RN25): PENDENTE → PAGO → RETIDO → LIBERTADO; alternativos: REEMBOLSADO, FALHADO.
- Idempotência (RN26): callbacks e retentativas do agregador não duplicam pagamentos nem comissões.
- Segurança: a plataforma nunca guarda credenciais de mobile money; usa webhooks do agregador; toda a operação financeira corre em transacção de base de dados.

### 17.5 Porque é que isto sustenta o negócio
Ao fazer o dinheiro circular pela plataforma com escrow, a comissão passa a ser cobrada automaticamente e as partes ganham segurança que não têm ao transaccionar por fora. É este o principal antídoto contra a desintermediação (produtores e compradores contornarem a plataforma para evitar a comissão).

## 18. Modelo de Dados

| Tabela | Finalidade |
|---|---|
| users | Conta, autenticação, papel e verificação de telefone |
| profiles | Dados gerais do utilizador |
| producers / buyers / transporters | Dados específicos de cada tipo de utilizador |
| farms | Propriedades/localizações de produção |
| categories / products | Categorias agrícolas e produtos de referência |
| product_listings | Ofertas publicadas pelo produtor |
| orders / order_items | Pedidos de compra e respectivos itens/quantidades |
| order_status_history | Histórico de transições de estado do pedido (RN10) |
| deliveries | Entregas: origem, destino, transportador, custo e estado (RN19–RN22) |
| vehicles | Veículos dos transportadores (Fase 2) |
| transactions | Registo financeiro/comercial da transacção |
| payments | Pagamentos, estados e referências do agregador (RN23–RN26) |
| reviews | Avaliações entre partes de transacções concluídas |
| complaints | Reclamações/disputas com estados (RN12, RN27) |
| notifications | Notificações enviadas (SMS/WhatsApp/e-mail) |
| audit_logs | Trilho de auditoria de acções administrativas (RN30) |
| locations | Dados geográficos de apoio quando necessário |

### 18.1 Estrutura de campos (principais tabelas)

```
users:                 id, name, phone, phone_verified_at, email, password, role, status, created_at, updated_at
product_listings:      id, producer_id, product_id, quantity, unit, price (DECIMAL, MZN),
                        available_from, available_until, latitude, longitude, status, created_at, updated_at
orders:                id, buyer_id, producer_id, total_amount (DECIMAL), delivery_method,
                        delivery_fee (DECIMAL), status, created_at, updated_at
order_items:            id, order_id, product_listing_id, quantity, unit_price (DECIMAL), subtotal (DECIMAL)
deliveries:             id, order_id, transporter_id, origin_lat, origin_lng, dest_lat, dest_lng,
                        weight_estimate, cost (DECIMAL), status, pickup_at, delivered_at
payments:               id, order_id, method, amount (DECIMAL, MZN), provider_reference, status,
                        held_at, released_at, refunded_at, created_at
order_status_history:   id, order_id, from_status, to_status, changed_by, changed_at
```

Nota de consistência: um pedido pertence a um único produtor; um "carrinho" com vários produtores gera vários pedidos. O `total_amount` é sempre recalculado no servidor a partir dos itens e da taxa de entrega.

## 19. Adaptação Mobile e Baixa Largura de Banda

- Layout responsivo pensado primeiro para operações simples em smartphones.
- Menu compacto, botões grandes e formulários curtos.
- Upload de fotografia pelo navegador, com compressão.
- Uso de GPS do dispositivo mediante permissão.
- Listas e cartões em vez de tabelas largas.
- Páginas leves e renderizadas no servidor para reduzir consumo de dados.
- PWA opcional para instalação no ecrã inicial e cache básico.

## 20. Conectividade e Offline

O sistema é desenhado para conectividade instável desde o MVP: páginas leves e tolerância a falhas. Numa fase seguinte poderão ser implementados cache e armazenamento local de dados não críticos. Operações críticas — criação final de pedidos, pagamento e confirmação de entrega — são sempre sincronizadas com o servidor antes de serem consideradas definitivas.

## 21. Notificações

Os canais primários são SMS e WhatsApp, por serem os efectivamente usados pelo público-alvo; o e-mail é secundário. Eventos notificados incluem: novo pedido, aceitação/rejeição, pagamento recebido/libertado, entrega atribuída e a caminho, e conclusão.

## 22. Segurança e Protecção de Dados

- Password armazenada com hash seguro; verificação de telefone por OTP.
- Autorização por papel e protecção contra acesso a recursos de outros utilizadores.
- Validação de todos os dados recebidos; nunca confiar apenas no frontend.
- 2FA para contas administrativas.
- Rate limiting em endpoints sensíveis e logs de acções administrativas (auditoria).
- HTTPS em produção e validação de tipos de ficheiro nos uploads.
- Transacções de base de dados em operações críticas de reserva e pagamento.
- Conformidade com a Lei de Protecção de Dados Pessoais: consentimento, minimização e retenção definida, sobretudo para geolocalização.
- Backups automáticos e testados (restauro real) e monitorização com alertas.

## 23. KPIs do Negócio

| KPI | O que mede |
|---|---|
| Ofertas publicadas | Volume de oferta na plataforma |
| Taxa de conclusão | Pedidos concluídos / pedidos criados |
| Taxa de entrega com sucesso | Entregas confirmadas / entregas solicitadas |
| Valor transaccionado (GMV) | Montante total das transacções concluídas |
| Retenção | Utilizadores que continuam a usar a plataforma |
| Tempo até compra | Tempo entre publicação e primeiro pedido |
| Recompra | Compradores que voltam a comprar |

## 24. Estratégia de Lançamento

O piloto concentra-se numa cadeia geográfica curta, com onboarding manual e transacções assistidas.

1. Seleccionar Dondo/Nhamatanda como zonas de oferta e Beira como zona de procura.
2. Cadastrar manualmente os primeiros produtores e compradores profissionais.
3. Começar com 2–3 categorias/produtos.
4. Coordenar transporte de forma assistida e registar tudo.
5. Realizar transacções assistidas e registar problemas.
6. Ajustar regras e interface e só depois ampliar a área geográfica.

Meta inicial: 20 produtores, 10 compradores e 3 transportadores para validação operacional. O transportador pode participar manualmente no piloto mesmo estando fora do MVP técnico.

## 25. Roadmap

| Fase | Entrega |
|---|---|
| Fase 0 | Pesquisa, entrevistas e validação manual |
| Fase 1 (MVP) | Contas + OTP, produtos, pesquisa, pedidos, entrega registada, pagamento registado, painel |
| Fase 2 | Transporte intermediado na plataforma, avaliações, notificações e melhorias mobile |
| Fase 3 | Integração de mobile money com escrow e comissão automática |
| Fase 4 | Relatórios, preços e inteligência de mercado |
| Fase 5 | Expansão para outras regiões de Moçambique |

## 26. Ordem Recomendada de Desenvolvimento

1. Criar repositório e documentação.
2. Configurar Laravel + PostgreSQL/PostGIS.
3. Migrations, modelos e relacionamentos.
4. Autenticação, papéis e OTP por SMS.
5. Perfis, categorias e produtos.
6. Ofertas, pesquisa e filtros.
7. Pedidos, regras de reserva e concorrência (RN06/RN15).
8. Coordenação de entrega e estados da entrega.
9. Registo de pagamento e cálculo de comissão.
10. Painel administrativo e de operador.
11. Design responsivo leve e PWA opcional.
12. Mapa, avaliações e notificações.
13. Testar o fluxo produtor → comprador → entrega → pagamento → conclusão.
14. Executar o piloto real e corrigir antes de adicionar módulos.

## 27. Critérios de Aceitação do MVP

- Um produtor cria conta, verifica o telefone e publica uma oferta.
- Um comprador encontra a oferta pela pesquisa e solicita uma quantidade válida.
- O produtor aceita/rejeita e a quantidade fica correctamente reservada.
- Não é possível vender a mesma quantidade duas vezes em pedidos concorrentes.
- O comprador escolhe a forma de entrega e o transporte é registado e acompanhado.
- O pagamento é registado e a comissão calculada na conclusão.
- O pedido chega ao estado CONCLUÍDO e as partes podem avaliar-se.
- O administrador acompanha todo o processo, incluindo entregas e disputas.
- Todas as telas principais funcionam em desktop e smartphone com baixo consumo de dados.

## 28. O que NÃO entra na primeira versão

- Aplicações Android/iOS nativas.
- Sistema próprio de crédito e carteira digital própria.
- Frota e armazéns próprios.
- IA para previsão agrícola.
- Cobertura nacional imediata.
- Leilões complexos e optimização automática de rotas.
- Escrow antes de validar o fluxo comercial (entra na Fase 3).

## 29. Princípios de Desenvolvimento

- Mobile-first na experiência; web renderizada no servidor e leve na implementação.
- Construir primeiro o fluxo comercial principal (oferta → pedido → entrega → pagamento → conclusão).
- Manter as regras de negócio no backend e não confiar apenas no frontend.
- Projectar para baixa largura de banda e manter a API bem definida.
- Testar regras críticas (reserva, concorrência, pagamento) antes da interface.
- Evitar funcionalidades que não contribuam para transacções e crescer sem complexidade desnecessária.

## 30. Fluxo Central do Sistema

PRODUTOR publica oferta → COMPRADOR pesquisa e cria pedido → escolhe entrega → PRODUTOR aceita → quantidade reservada → COMPRADOR paga (retido) → transporte coordenado → entrega confirmada → valor libertado e comissão retida → pedido concluído → avaliação.

## 31. Próximos Passos

A etapa seguinte é a especificação técnica detalhada assumindo esta stack: diagrama ER completo, estratégia de bloqueio de stock, contratos de API, integração do agregador de pagamentos e do transporte, wireframes leves das telas e plano de testes das regras críticas.
