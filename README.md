# AgroLink MZ

Plataforma web B2B de comercialização agrícola, ligando produtores a compradores profissionais — piloto no corredor Dondo/Nhamatanda — Beira, Moçambique.

Stack: **Laravel + Blade + Livewire**, **PostgreSQL + PostGIS**, notificações por **SMS/WhatsApp**, pagamentos via **mobile money** (M-Pesa, e-Mola, mKesh).

## Documentação

- [docs/business-plan.md](docs/business-plan.md) — modelo de negócio, regras de negócio (RN01–RN30) e modelo de dados. Fonte de verdade do projecto.
- [docs/ROADMAP.md](docs/ROADMAP.md) — fases de desenvolvimento deste repositório.
- [docs/ESTRUTURA-PROJECTO.md](docs/ESTRUTURA-PROJECTO.md) — layout de pastas e convenções de código.
- [docs/DEPLOY.md](docs/DEPLOY.md) — checklist de segurança/produção antes do piloto real.

## Estado actual

✅ **MVP técnico completo (Fases 1–13)** e **prontidão de produção fechada (Fase 14)**. Pesquisa por proximidade já usa PostGIS real (não a fórmula de Haversine das fases iniciais). Falta apenas o **piloto real** (cadastro dos primeiros produtores/compradores/transportadores no terreno) — ver [ROADMAP.md](docs/ROADMAP.md).

## Requisitos

- PHP >= 8.3
- Composer
- PostgreSQL >= 14 com extensão **PostGIS**
- Node.js + NPM (build de assets)

## Arranque local

```bash
composer install
cp .env.example .env
php artisan key:generate
```

### Configurar DB_\* no .env (PostgreSQL + PostGIS)

1. **Instalar o PostGIS**, se ainda não estiver instalado. No Windows, descarregue o instalador correspondente à versão do PostgreSQL instalada em <https://download.osgeo.org/postgis/windows/> (ex.: `pgXX/postgis-bundle-pgXXx64-setup-<versão>.exe`) e execute-o — basta seguir o assistente (ou `postgis-bundle-...-setup.exe /S` para instalação silenciosa). Em Linux, use o pacote da distribuição (ex.: `apt install postgresql-18-postgis-3`).

2. **Criar a base de dados e o utilizador da aplicação** (como superutilizador `postgres`):

   ```sql
   CREATE ROLE agrolink WITH LOGIN PASSWORD 'escolha-uma-password';
   CREATE DATABASE agrolink_mz OWNER agrolink;
   \c agrolink_mz
   CREATE EXTENSION postgis;
   ```

   > `CREATE EXTENSION postgis` exige privilégios de superutilizador — corre-se uma única vez, como `postgres`, não como o utilizador `agrolink` da aplicação.

3. **Preencher o `.env`** com as credenciais criadas:

   ```env
   DB_CONNECTION=pgsql
   DB_HOST=127.0.0.1
   DB_PORT=5432
   DB_DATABASE=agrolink_mz
   DB_USERNAME=agrolink
   DB_PASSWORD=escolha-uma-password
   ```

4. Repita os passos 2–3 para a base de dados de testes (`agrolink_mz_test`, usada por `phpunit.xml`) se for correr a suite de testes.

### Continuar o arranque

```bash
php artisan migrate --seed
npm install
npm run build
php artisan serve
```

### Testes

```bash
php artisan test
```

Os testes correm contra PostgreSQL real (`agrolink_mz_test`), não SQLite — as migrations usam `CHECK` constraints e o tipo `geography` do PostGIS, que o SQLite não suporta.