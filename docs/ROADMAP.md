# Roteiro de Desenvolvimento — AgroLink MZ

Este roteiro segue a "Ordem Recomendada de Desenvolvimento" (secção 26) e o "Roadmap" (secção 25) do [business-plan.md](business-plan.md), reorganizados em fases de trabalho concretas para este repositório.

Cada fase só arranca depois da anterior estar fechada e validada. O estado é actualizado à medida que avançamos.

## Fase 1 — Repositório e estrutura do projecto ✅

- [x] Documentação do negócio (`docs/business-plan.md`).
- [x] Roteiro de desenvolvimento (este ficheiro).
- [x] Estrutura de pastas Laravel (app, database, resources, routes, tests…).
- [x] Ficheiros de configuração base (`composer.json`, `package.json`, `.env.example`, `.gitignore`).
- [x] Ambiente local funcional (PHP, Composer, PostgreSQL instalados e `composer install` corrido).

## Fase 2 — Base técnica: Laravel + PostgreSQL ✅

- [x] Laravel 13 instalado sobre o esqueleto (via `composer create-project` num directório limpo, fundido no repositório).
- [x] Ligação PostgreSQL configurada (`agrolink_mz`, utilizador dedicado `agrolink`).
- [x] Ambiente `.env` configurado; filas, cache e sessões a usar o driver `database`.
- [x] Livewire e Tailwind 4/Vite instalados; `npm run build` e `artisan serve` verificados a funcionar.
- [ ] **PostGIS** — adiado: sem build Windows disponível para PostgreSQL 18 (versão muito recente) no momento da instalação. O modelo de dados da secção 18.1 já usa `latitude`/`longitude` simples nas migrations, pelo que a Fase 3 arranca sem PostGIS; a extensão pode ser activada mais tarde (Fase 6 — pesquisa geográfica) quando houver build disponível, sem alterar o esquema base.

## Fase 3 — Migrations, modelos e relacionamentos ✅

Tabelas da secção 18 do business plan, por ordem de dependência:
`users` → `profiles` → `producers/buyers/transporters` → `farms` → `categories/products` → `product_listings` → `orders/order_items` → `order_status_history` → `deliveries` → `vehicles` → `transactions` → `payments` → `reviews` → `complaints` → `notifications` → `audit_logs` → `locations`.

- [x] 21 migrations criadas e a correr (`php artisan migrate:fresh --seed`).
- [x] `users` com `phone` único + `phone_verified_at` (RN01), `role` e `status`.
- [x] Restrições de integridade ao nível da base de dados: `product_listings.quantity >= 0` (RN04), `reviews.rating` entre 1–5 (RN13), `reviews` único por `(order_id, reviewer_id)`, `payments.provider_reference` único (RN26).
- [x] Models Eloquent com relacionamentos (User, Profile, Producer, Buyer, Transporter, Farm, Category, Product, ProductListing, Order, OrderItem, OrderStatusHistory, Delivery, Vehicle, Transaction, Payment, Review, Complaint, NotificationLog, AuditLog, Location).
- [x] Seeders piloto: 3 categorias/7 produtos de referência e as 3 localizações do corredor (Dondo, Nhamatanda, Beira) — secção 24.
- [ ] Reserva atómica com bloqueio de linha (RN06/RN15) — fica para a Fase 7, junto com a lógica de pedidos.

## Fase 4 — Autenticação, papéis e OTP por SMS (RN01) ✅

- [x] Registo (produtor/comprador) com telefone único moçambicano normalizado (`App\Support\MozambiquePhone`).
- [x] Verificação por OTP (`OtpService` + tabela `otp_codes`; código hasheado, expiração, limite de tentativas, cooldown de reenvio).
- [x] Driver de SMS por interface (`App\Services\Sms\SmsGateway`), com `LogSmsGateway` por omissão enquanto o agregador local não está contratado (secção 21).
- [x] Login por telefone/password (sessões Laravel); contas `blocked` são recusadas.
- [x] Middleware `verified.phone` bloqueia o acesso à aplicação até o telefone estar confirmado.
- [x] Painel (`/painel`) mínimo pós-login, a preencher nas próximas fases.
- [x] Testes de funcionalidade (`tests/Feature/Auth/RegistrationOtpLoginTest.php`) cobrindo registo, OTP errado/correcto, conta bloqueada e login.
- Transportador e administrador/operador não têm registo público (transportador participa de forma assistida no piloto; admin/operador são criados directamente na base de dados/painel administrativo — Fase 10).

## Fase 5 — Perfis, categorias e produtos ✅

- [x] Edição de perfil (`/perfil`): nome, endereço/distrito/província, dados específicos de produtor (nome do negócio) ou comprador (nome do negócio + tipo).
- [x] RN02 — `User::hasMinimumProfile()` / `Producer::isReadyToPublish()`; aviso no painel enquanto o produtor não tiver perfil + pelo menos uma propriedade.
- [x] Gestão de propriedades (`/minhas-propriedades`): CRUD de `farms` (nome, endereço, distrito, província, latitude/longitude opcionais), restrito a produtores e ao dono de cada propriedade (`FarmPolicy` — RN14).
- [x] Catálogo de categorias/produtos (`/categorias`), navegável publicamente.
- [x] Testes de funcionalidade cobrindo perfil, RN02, CRUD de propriedades e autorização entre produtores.

## Fase 6 — Ofertas, pesquisa e filtros ✅

- [x] CRUD de ofertas (`/minhas-ofertas`): criar/editar (enquanto `disponivel`) e encerrar; produto do catálogo, propriedade opcional (define localização), quantidade, unidade, preço e período de disponibilidade. Restrito a produtores prontos a publicar (RN02) e ao dono de cada oferta (`ProductListingPolicy` — RN14).
- [x] RN17 — comando `app:expire-product-listings` marca como `expirado` as ofertas fora do período, agendado diariamente (`routes/console.php`); a pesquisa também exclui expiradas em tempo real (`scopeAvailable`).
- [x] Pesquisa pública (`/ofertas`) com filtros por categoria, preço mín./máx., quantidade mín. e proximidade a uma das localizações-piloto (raio configurável).
- [x] Proximidade calculada por fórmula de Haversine sobre `latitude`/`longitude` simples — substituto do PostGIS enquanto não há build para a versão do PostgreSQL instalada (ver Fase 2).
- [x] Detalhe público da oferta (`/ofertas/{listing}`) com dados do produtor (RN — "ver perfil do produtor", secção 11.2); pedido de compra fica para a Fase 7.
- [x] Testes de funcionalidade: CRUD, autorização entre produtores, validação de quantidade (RN04), expiração automática e todos os filtros de pesquisa.

## Fase 7 — Pedidos, reserva e concorrência ✅

- [x] Criação de pedidos a partir da página da oferta (`/ofertas/{id}`), com validação da quantidade pedida contra a disponível (RN05) e escolha da forma de entrega (RN19).
- [x] `OrderWorkflowService::accept()` — reserva atómica: bloqueia a linha da oferta (`lockForUpdate`), confirma stock suficiente e só então decrementa (RN06/RN15). A oferta mantém-se `disponivel` enquanto sobrar quantidade e só passa a `vendido` quando esgotar — permite vender a vários compradores (RN07). O estado `reservado` do enum (RN08) fica por usar nesta fase: nada no fluxo automático o justifica sem violar RN07; pode vir a ser usado manualmente numa fase futura.
- [x] Estados do pedido (RN09) com máquina de estados explícita e histórico de transições — quem, de, para, quando (RN10): `pendente → aceite → em_preparação → pronto → em_transporte → entregue → concluído`, alternativas `pendente → rejeitado` e `pendente|aceite → cancelado`.
- [x] Cancelamento (RN11): livre em `pendente`; em `aceite` também é permitido e repõe a quantidade reservada; a partir de `em_preparação` deixa de ser possível (fica para gestão de disputas — Fase 12/painel administrativo).
- [x] `OrderPolicy` garante que só o comprador e o produtor de cada pedido o veem/gerem (RN14); confirmação de entrega (RN22) é exclusiva do comprador.
- [x] `delivery_fee` fica a 0 nesta fase — a integração do custo de transporte (RN20) faz sentido quando a entrega for de facto coordenada, na Fase 8.
- [x] Testes cobrindo RN05, reserva/concorrência (RN06/RN15), venda parcial (RN07), ciclo de estados completo (RN09/RN10), cancelamento em cada estado (RN11) e autorização (RN14).

## Fase 8 — Entrega e transporte ✅

- [x] Quando o pedido com `transporte_intermediado` é aceite, gera-se automaticamente o registo de entrega associado (origem a partir da propriedade do produtor — secção 16.2). Para `comprador_levanta`/`produtor_entrega` não há registo de entrega, tal como descrito na secção 16.1.
- [x] `DeliveryWorkflowService::assign()` — o operador regista o transportador (conta formal opcional ou apenas contacto, para a coordenação assistida do piloto) e o custo acordado, que passa a integrar o total do pedido (RN20).
- [x] Estados da entrega (RN21) com máquina de estados própria: `solicitada → atribuída → em_recolha → em_transito → entregue`, geridos pelo operador em `/entregas`.
- [x] Confirmação (RN22): o botão "Confirmar recepção" do comprador (já existente na Fase 7) passa a confirmar também a entrega quando esta existe — exige que a entrega e o pedido estejam ambos `entregue`, marca a entrega como `confirmada` e conclui o pedido.
- [x] `DeliveryPolicy`: gestão restrita a operador/administrador; consulta também permitida ao comprador e produtor do pedido.
- [x] Testes cobrindo criação automática (ou não) da entrega, RN20, RN21, RN22 e autorização.

> Nota de âmbito: sem coordenadas do comprador (`Profile` só tem endereço em texto), o destino da entrega fica sem `dest_lat/dest_lng` — o cálculo de distância fica para quando essa informação existir (não bloqueia o MVP).

## Fase 9 — Pagamentos e comissão ✅

- [x] `PaymentService::register()` — fase piloto (secção 17.2): comprador e produtor combinam o pagamento directamente (mobile money ou dinheiro) e qualquer um dos dois o regista na plataforma (método + referência opcional), só depois do pedido ser aceite.
- [x] RN18 — o valor do pagamento é sempre recalculado no servidor a partir de `order.total_amount`, nunca aceite do cliente.
- [x] RN26 — idempotência: registar de novo para o mesmo pedido actualiza o registo existente (`updateOrCreate` por `order_id`); reutilizar uma `provider_reference` de outro pedido é rejeitado (constrain único já existente desde a Fase 3).
- [x] RN24/RN28 — ao concluir o pedido (`OrderWorkflowService`, independentemente de vir de `advance()` directo ou da confirmação de entrega da Fase 8), regista-se automaticamente a `transaction` com a comissão da plataforma, percentagem configurável via `PLATFORM_COMMISSION_PERCENT` (`config/commission.php`, referência 2%). Ainda sem escrow real, é só o registo contabilístico.
- [x] Produtor vê a comissão e o valor líquido a receber na página do pedido.
- [x] Testes cobrindo RN18, RN26 (nas duas formas), RN24/RN28 e autorização.

> Nota de âmbito: RN23 (retenção/escrow) e RN25 completo (`retido`/`libertado`) ficam por automatizar — dependem da integração real com o agregador de mobile money (secção 17.3, Fase 3 de negócio, fora do MVP técnico). O esquema (`payments.held_at/released_at/refunded_at`) já está pronto para essa fase.

## Fase 10 — Painel administrativo e de operador ✅

- [x] `/admin` (KPIs, secção 23): ofertas publicadas, taxa de conclusão, taxa de entrega confirmada, valor transaccionado (GMV) e taxa de recompra. "Tempo até compra" e "retenção" ficam de fora — precisam de análise de coortes mais elaborada, não essencial ao MVP.
- [x] `/admin/utilizadores`: listar/filtrar por papel e pesquisar; bloquear/desbloquear conta (não permite bloquear a própria).
- [x] `/admin/verificacoes` (RN16): lista perfis com dados mínimos preenchidos ainda por verificar; acção de verificação atribui o selo.
- [x] `/admin/categorias`: adicionar categorias e produtos de referência.
- [x] `/admin/ofertas` e `/admin/pedidos`: supervisão só de leitura, com filtro por estado; `OrderPolicy::view` passou a permitir também administrador/operador ver o detalhe de qualquer pedido.
- [x] Disputas (RN12/RN27): comprador ou produtor podem "Reportar problema" na página do pedido depois de `entregue`/`concluido`; `/admin/disputas` permite decidir (procedente/improcedente/resolvida) com justificação.
- [x] Auditoria (RN30): `AuditLogger` regista bloqueio/desbloqueio de conta, verificação de perfil, criação de categoria/produto e resolução de disputa em `audit_logs`.
- [x] Testes cobrindo autorização (incluindo defesa em profundidade ao nível do componente, não só da rota), bloqueio/desbloqueio, verificação, categorias e o ciclo completo de uma disputa.

> Nota de âmbito: RN27 (reembolso em disputa procedente) fica registado como decisão administrativa (texto de resolução), sem automatizar a devolução de dinheiro — não há escrow real a reverter nesta fase (ver nota da Fase 9).

Com esta fase fecha-se o **MVP técnico** (módulos 1–10 da secção 10 do business plan). As fases seguintes (11–14) são UI responsiva/PWA, mapa/avaliações/notificações, testes end-to-end e o piloto real.

## Fase 11 — UI responsiva leve e PWA opcional ✅

- [x] Página inicial (`/`) substituída — ainda usava o scaffold genérico do Laravel (referenciava até uma rota `/dashboard` inexistente); agora tem a marca AgroLink MZ, proposta de valor e chamadas para acção.
- [x] Upload de foto de perfil com **compressão no navegador** (canvas, redimensiona para 800px e reexporta em JPEG antes do envio) — secção 19.
- [x] Botão "Usar a minha localização" nas propriedades, via GPS do dispositivo (`navigator.geolocation`), mediante permissão — secção 19.
- [x] Tabelas do painel administrativo substituídas por listas/cartões — mais legíveis em ecrãs pequenos do que tabelas largas.
- [x] Menu compacto (hamburger) no cabeçalho em ecrãs pequenos, com Alpine.js.
- [x] PWA opcional: `manifest.json`, ícones, `theme-color`, e um service worker mínimo que só põe em cache os ficheiros estáticos compilados (nomes com hash) — páginas, formulários e chamadas Livewire vão sempre à rede, para não servir versões antigas de operações críticas (secção 20).
- [x] Testes cobrindo o upload de foto (incluindo substituição do ficheiro antigo e validação de tipo).

> Nota de âmbito: o service worker cobre só o "app shell" estático. Cache mais profunda de dados haveria de respeitar a secção 20 ("operações críticas... sempre sincronizadas com o servidor antes de serem consideradas definitivas") — decidiu-se não fazê-lo agora para não arriscar mostrar dados desactualizados (stock, estado de pedidos) offline.

## Fase 12 — Mapa, avaliações e notificações ✅

- [x] RN13 — avaliações pós-transacção: comprador e produtor avaliam-se um ao outro depois de um pedido `concluido` (1–5 estrelas + comentário opcional), uma vez por pedido (`OrderPolicy::review`, reforçado pelo constraint único desde a Fase 3).
- [x] Perfil público do produtor (`/produtores/{producer}`) com reputação (média + número de avaliações), biografia, localização e ofertas actualmente disponíveis — a "reputação do produtor" da secção 11.2. Ligado a partir do detalhe da oferta.
- [x] Notificações (secção 21): `NotificationService` regista e "envia" (via `SmsGateway`, ainda o driver `log` da Fase 4) os eventos novo pedido, aceitação/rejeição, pagamento recebido, entrega atribuída/a caminho e conclusão. Cada utilizador consulta o seu histórico em `/notificacoes`.
- [x] Mapa e localização básica — sem PostGIS disponível (nota da Fase 2/6), usa-se Leaflet + OpenStreetMap sobre `latitude`/`longitude` simples: pontos das ofertas em `/ofertas` e localização da propriedade no detalhe da oferta.
- [x] O Leaflet (~43 KB gzipped) é carregado por *import* dinâmico só nas páginas com mapa — o `app.js` principal ficou em ~2 KB gzipped, mantendo as restantes páginas leves (secção 19).
- [x] Testes cobrindo RN13 (incluindo dupla-avaliação e avaliação antes da conclusão), reputação pública do produtor, e notificação de cada evento da secção 21.

> Nota de âmbito: WhatsApp fica por integrar (sem agregador escolhido, tal como o SMS — Fase 4); os eventos ficam prontos a enviar por qualquer canal assim que um agregador for contratado.

## Fase 13 — Testes end-to-end ✅

- [x] `tests/Feature/EndToEnd/FullOrderFlowTest.php` — dois testes que percorrem o ciclo de vida inteiro sempre pelos componentes Livewire (não pelos serviços directamente), tal como um utilizador real: perfil → propriedade → oferta → pedido → aceitação → avanço de estados → pagamento → confirmação/conclusão → comissão → notificações → avaliação mútua → reputação pública. Um cenário cobre levantamento próprio, o outro transporte intermediado (inclui o painel do operador em `/entregas`). O valor destes testes é apanhar problemas de "ligação" entre módulos que os testes unitários de cada fase (7–12), ao isolarem-se uns dos outros, não conseguem detectar.
- [x] RN06/RN15 — `tests/Concurrency/ConcurrentAcceptTest.php`: até agora a "concorrência" só estava provada por simulação sequencial (duas chamadas a `accept()` seguidas, no mesmo processo/transacção do PHPUnit). Este teste novo lança dois **processos do SO reais e independentes** (via `proc_open`, cada um com a sua própria ligação PostgreSQL), sincronizados por uma barreira de ficheiro para maximizarem a sobreposição exactamente no `lockForUpdate`, contra uma base de dados sem embrulho de transacção (`DatabaseMigrations`, não `RefreshDatabase`, para que os processos vejam dados realmente committed). Confirma que, com dois pedidos cuja soma excede o stock, exactamente um `accept()` tem sucesso e o stock nunca fica negativo.
- [x] RN26 (idempotência de pagamento) já estava coberta desde a Fase 9 (`PaymentWorkflowTest`): registar duas vezes o mesmo pedido actualiza em vez de duplicar, e reutilizar uma `provider_reference` de outro pedido é rejeitado. Revalidado neste conjunto, sem alterações.
- [x] Suite completa: **86 testes, 261 assertions**, todos a passar (era 83 no fecho da Fase 12).

## Fase 14 — Piloto real

- Lançamento no corredor Dondo/Nhamatanda — Beira (secção 24).
- Meta: 20 produtores, 10 compradores, 3 transportadores.

---

### Fora de âmbito nas primeiras fases (secção 28)

Apps nativas Android/iOS, carteira digital/crédito próprio, frota e armazéns próprios, IA para previsão agrícola, cobertura nacional imediata, leilões complexos, escrow antes de validar o fluxo comercial.
