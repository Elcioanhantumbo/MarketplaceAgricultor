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

## Fase 5 — Perfis, categorias e produtos

- Perfil mínimo do produtor (RN02).
- Categorias e produtos de referência.
- Gestão de propriedades (`farms`) com localização.

## Fase 6 — Ofertas, pesquisa e filtros

- CRUD de ofertas (`product_listings`) com estados (RN08, RN17).
- Pesquisa e filtros por localização (PostGIS), quantidade, preço, disponibilidade.

## Fase 7 — Pedidos, reserva e concorrência

- Criação de pedidos, validação de quantidade (RN05).
- Reserva atómica com bloqueio de linha (RN06, RN15).
- Estados do pedido e histórico de transições (RN09, RN10).
- Regras de cancelamento (RN11).

## Fase 8 — Entrega e transporte

- Registo de entregas (`deliveries`) e formas de entrega (RN19, RN20).
- Estados da entrega (RN21) e confirmação (RN22).
- Coordenação assistida (piloto) antes da atribuição automática (Fase 2 de negócio).

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
