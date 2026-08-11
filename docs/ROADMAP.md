# Roteiro de Desenvolvimento — AgroLink MZ

Este roteiro segue a "Ordem Recomendada de Desenvolvimento" (secção 26) e o "Roadmap" (secção 25) do [business-plan.md](business-plan.md), reorganizados em fases de trabalho concretas para este repositório.

Cada fase só arranca depois da anterior estar fechada e validada. O estado é actualizado à medida que avançamos.

## Fase 1 — Repositório e estrutura do projecto ✅ (em curso)

- [x] Documentação do negócio (`docs/business-plan.md`).
- [x] Roteiro de desenvolvimento (este ficheiro).
- [x] Estrutura de pastas Laravel (app, database, resources, routes, tests…).
- [x] Ficheiros de configuração base (`composer.json`, `package.json`, `.env.example`, `.gitignore`).
- [ ] Ambiente local funcional (PHP, Composer, PostgreSQL + PostGIS instalados e `composer install` corrido).

> Nesta fase não existe ainda código funcional — apenas esqueleto, configuração e documentação, para depois instalar as dependências reais com Composer/NPM.

## Fase 2 — Base técnica: Laravel + PostgreSQL/PostGIS

- Instalar Laravel real via Composer sobre este esqueleto (ou `composer create-project` num directório limpo e fundir).
- Configurar ligação PostgreSQL + extensão PostGIS.
- Configurar ambiente `.env`, filas (queue), cache e sessões.
- Configurar Livewire e Tailwind/Vite.

## Fase 3 — Migrations, modelos e relacionamentos

Tabelas da secção 18 do business plan, por ordem de dependência:
`users` → `profiles` → `producers/buyers/transporters` → `farms` → `categories/products` → `product_listings` → `orders/order_items` → `order_status_history` → `deliveries` → `vehicles` → `transactions` → `payments` → `reviews` → `complaints` → `notifications` → `audit_logs` → `locations`.

## Fase 4 — Autenticação, papéis e OTP por SMS (RN01)

- Registo/login com telefone único.
- Verificação por OTP via agregador SMS local.
- Papéis: produtor, comprador, transportador (Fase 2 de negócio), administrador/operador.

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
