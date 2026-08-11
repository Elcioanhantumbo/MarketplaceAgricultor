# Estrutura do Projecto

Esqueleto de aplicação Laravel (convenção Laravel 11 — `app/Livewire`, sem `app/Http/Kernel.php` explícito). Ainda sem dependências instaladas (ver [ROADMAP.md](ROADMAP.md), Fase 2).

```
MarketplaceAgricultor/
├── app/
│   ├── Events/                 # Eventos de domínio (pedido aceite, pagamento libertado, etc.)
│   ├── Http/
│   │   ├── Controllers/        # Controladores HTTP (fina camada sobre Services)
│   │   ├── Middleware/         # Middlewares (papéis, verificação de telefone, etc.)
│   │   └── Requests/           # Form Requests (validação de entrada)
│   ├── Jobs/                   # Jobs em fila (envio de SMS/WhatsApp, processamento assíncrono)
│   ├── Listeners/              # Listeners dos eventos de domínio
│   ├── Livewire/               # Componentes Livewire, organizados por actor
│   │   ├── Auth/                   # Registo, login, verificação OTP
│   │   ├── Producer/                # Ofertas, pedidos recebidos, entregas, vendas
│   │   ├── Buyer/                   # Pesquisa, pedidos, pagamento, avaliações
│   │   └── Admin/                   # Painel administrativo e de operador
│   ├── Models/                 # Eloquent models (users, product_listings, orders, ...)
│   ├── Notifications/          # Notificações (SMS, WhatsApp, e-mail)
│   ├── Policies/                # Autorização por recurso (RN14)
│   ├── Providers/               # Service Providers
│   └── Services/                 # Regras de negócio (reserva atómica, cálculo de comissão,
│                                 #  cálculo de distância/custo de transporte, escrow, OTP)
├── bootstrap/
│   └── cache/                  # Cache de bootstrap do framework
├── config/                     # Configuração (app, database, services de terceiros)
├── database/
│   ├── factories/              # Model factories para testes
│   ├── migrations/             # Migrations (ordem de dependência — ver ROADMAP Fase 3)
│   └── seeders/                # Seeders (categorias base, utilizador admin, etc.)
├── docs/
│   ├── business-plan.md        # Documento de negócio e regras de negócio (fonte de verdade)
│   ├── ROADMAP.md              # Fases de desenvolvimento deste repositório
│   └── ESTRUTURA-PROJECTO.md   # Este ficheiro
├── public/                     # Document root (index.php, assets compilados)
├── resources/
│   ├── css/                    # Tailwind/CSS
│   ├── js/                     # JS/Alpine
│   └── views/
│       ├── layouts/            # Layouts base (mobile-first, leve)
│       ├── components/         # Blade components partilhados
│       ├── livewire/           # Views dos componentes Livewire
│       └── emails/             # Templates de e-mail (canal secundário)
├── routes/                     # web.php, api.php, console.php
├── storage/
│   ├── app/public/             # Uploads (fotos de produtos, comprimidas)
│   ├── framework/              # Cache, sessões, views compiladas
│   └── logs/                   # Logs da aplicação
├── tests/
│   ├── Feature/                 # Testes de fluxo (reserva, concorrência, pagamento)
│   └── Unit/                    # Testes unitários de Services
├── .env.example                 # Variáveis de ambiente (DB, mobile money, SMS, WhatsApp)
├── .gitignore
├── composer.json                 # Dependências PHP planeadas (Laravel, Livewire, Sanctum, PostGIS helper)
├── package.json                  # Dependências front-end (Vite, Tailwind, Alpine)
└── README.md
```

## Convenções

- **Regras de negócio no backend**: toda a lógica das secções 12 (RN01–RN30) do business plan vive em `app/Services`, nunca só na view/Livewire.
- **Um pedido = um produtor**: reflectido no modelo de dados (`orders.producer_id` único por pedido).
- **Moeda**: todos os campos monetários são `DECIMAL`, nunca float (RN18).
- **Estados**: geridos por enums/constantes nos Models, com histórico próprio para pedidos (`order_status_history`, RN10).
- **Mobile-first**: views organizadas por Livewire component, pensadas primeiro para ecrã pequeno.
