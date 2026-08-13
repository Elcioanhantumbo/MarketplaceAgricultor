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

## Fase 9 — Pagamentos e comissão

- Registo de pagamento (piloto, sem integração) — secção 17.2.
- Estrutura pronta para escrow com mobile money (Fase 3 de negócio) — secção 17.3.
- Estados do pagamento (RN25), idempotência (RN26), comissão configurável (RN28).

## Fase 10 — Painel administrativo e de operador

- KPIs, gestão de utilizadores/categorias/ofertas/pedidos.
- Verificação de perfis (RN16), disputas (RN12, RN27), auditoria (RN30).

## Fase 11 — UI responsiva leve e PWA opcional

- Layout mobile-first, formulários curtos, upload de fotos com compressão.
- PWA opcional para instalação no ecrã inicial.

## Fase 12 — Mapa, avaliações e notificações

- Mapa e localização básica (PostGIS).
- Avaliações pós-transacção (RN13).
- Notificações SMS/WhatsApp (secção 21).

## Fase 13 — Testes end-to-end

- Testar fluxo completo: produtor → comprador → entrega → pagamento → conclusão.
- Testes de concorrência (RN06/RN15) e idempotência de pagamento (RN26).

## Fase 14 — Piloto real

- Lançamento no corredor Dondo/Nhamatanda — Beira (secção 24).
- Meta: 20 produtores, 10 compradores, 3 transportadores.

---

### Fora de âmbito nas primeiras fases (secção 28)

Apps nativas Android/iOS, carteira digital/crédito próprio, frota e armazéns próprios, IA para previsão agrícola, cobertura nacional imediata, leilões complexos, escrow antes de validar o fluxo comercial.
