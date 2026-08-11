# AgroLink MZ

Plataforma web B2B de comercialização agrícola, ligando produtores a compradores profissionais — piloto no corredor Dondo/Nhamatanda — Beira, Moçambique.

Stack: **Laravel + Blade + Livewire**, **PostgreSQL + PostGIS**, notificações por **SMS/WhatsApp**, pagamentos via **mobile money** (M-Pesa, e-Mola, mKesh).

## Documentação

- [docs/business-plan.md](docs/business-plan.md) — modelo de negócio, regras de negócio (RN01–RN30) e modelo de dados. Fonte de verdade do projecto.
- [docs/ROADMAP.md](docs/ROADMAP.md) — fases de desenvolvimento deste repositório.
- [docs/ESTRUTURA-PROJECTO.md](docs/ESTRUTURA-PROJECTO.md) — layout de pastas e convenções de código.

## Estado actual

🚧 **Fase 1 — estrutura do projecto.** Ainda sem dependências instaladas. Ver [ROADMAP.md](docs/ROADMAP.md) para o que falta.

## Requisitos (para as próximas fases)

- PHP >= 8.2
- Composer
- PostgreSQL >= 14 com extensão PostGIS
- Node.js + NPM (build de assets)

## Arranque local (quando o ambiente estiver pronto)

```bash
composer install
cp .env.example .env
php artisan key:generate
# configurar DB_* no .env (PostgreSQL + PostGIS)
php artisan migrate --seed
npm install
npm run dev
php artisan serve
```
