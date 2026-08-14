# Checklist de Produção — AgroLink MZ

Este documento cobre os itens de segurança/operação da secção 22 do [business-plan.md](business-plan.md), preparados na Fase 14 do [ROADMAP.md](ROADMAP.md) antes do piloto real. É um checklist técnico — o lançamento em si (cadastrar produtores/compradores/transportadores no corredor Dondo/Nhamatanda-Beira) é operacional e fica fora deste documento.

## 1. HTTPS e cookies seguros

- Definir `APP_ENV=production` e `APP_URL=https://...` no `.env` do servidor. Com `APP_ENV=production`, `AppServiceProvider::boot()` chama `URL::forceScheme('https')` automaticamente — todas as URLs geradas (incluindo as dos links de reset, etc.) saem em `https://`, mesmo que o pedido chegue à aplicação em HTTP vindo de um proxy.
- Definir `SESSION_SECURE_COOKIE=true` — sem isto, o cookie de sessão pode ser enviado em HTTP simples se o navegador alguma vez cair para essa rota.
- `bootstrap/app.php` já confia em qualquer proxy (`trustProxies(at: '*')`), para que o cabeçalho `X-Forwarded-Proto` do balanceador/proxy (nginx, Cloudflare, etc.) seja respeitado. Se a aplicação alguma vez ficar exposta directamente à Internet sem proxy à frente, isto deve ser restringido aos IPs do proxy conhecido — confiar em `*` só é seguro quando só o proxy consegue falar directamente com a aplicação.

## 2. Autenticação — rate limiting e 2FA

- **Login** (`App\Livewire\Auth\Login`): 5 tentativas falhadas por combinação telefone+IP bloqueiam novas tentativas por 60 segundos (`RateLimiter`, limpo após um login bem-sucedido).
- **OTP de verificação de telefone** (RN01): já limitado desde a Fase 4 — `OTP_MAX_ATTEMPTS` tentativas por código (por omissão 5) e `OTP_RESEND_COOLDOWN_SECONDS` entre reenvios (por omissão 60s).
- **2FA administrativo**: contas `admin`/`operator` passam por um segundo código OTP (mesmo mecanismo do RN01, propósito `admin_2fa`) antes de qualquer página de `/admin`, através do middleware `admin.2fa` (`App\Http\Middleware\EnsureTwoFactorConfirmed`) e do ecrã `/confirmar-acesso`. É pedido uma vez por sessão — fica válido até ao logout.

## 3. Backups automáticos e testados

- `php artisan app:backup-database` gera um `pg_dump` em formato SQL simples e guarda-o no disco configurado (`BACKUP_DISK`, por omissão `local`, pasta `BACKUP_DIRECTORY`, por omissão `backups`), apagando dumps com mais de `BACKUP_KEEP_DAYS` dias (por omissão 14).
- Está agendado diariamente às 02:00 em `routes/console.php`. **O agendamento só corre se o cron do servidor chamar o scheduler do Laravel** — adicionar ao crontab do servidor:
  ```
  * * * * * cd /caminho/para/o/projecto && php artisan schedule:run >> /dev/null 2>&1
  ```
- Se `pg_dump` não estiver no `PATH` do servidor, definir `BACKUP_PG_DUMP_PATH` com o caminho completo do executável.
- Em produção, `BACKUP_DISK` deve apontar para um disco fora da própria máquina da base de dados (ex.: S3) — um backup guardado só localmente não sobrevive à perda desse servidor.
- **Restauro (manual, deliberadamente não automatizado)** — testado de facto durante a Fase 14 contra um dump real de `agrolink_mz` (61 KB, contagens de linhas conferidas em `users`, `categories`, `products`, `locations` antes/depois — coincidiram exactamente):
  ```
  createdb -h <host> -U <superuser> -O agrolink <nome_da_bd_destino>
  psql -h <host> -U agrolink -d <nome_da_bd_destino> -v ON_ERROR_STOP=1 -f <ficheiro>.sql
  ```
  Restaurar sempre para uma base de dados **nova**, nunca por cima da de produção directamente — validar os dados e só depois trocar a ligação da aplicação, se for esse o cenário de recuperação.

## 4. Monitorização

- `GET /up` (rota de saúde nativa do Laravel) foi reforçada em `AppServiceProvider::boot()` para também verificar a ligação à base de dados (ouve o evento `DiagnosingHealth`) — responde `200` só se a aplicação **e** a base de dados estiverem operacionais, `500` caso contrário.
- Apontar um monitor externo (UptimeRobot, Healthchecks.io, ou equivalente) para `https://<domínio>/up`, com alerta (email/SMS) configurado nesse serviço — a aplicação não envia alertas por si própria.
- Os logs de auditoria (RN30, `audit_logs`, desde a Fase 10) cobrem acções administrativas; para erros de aplicação, `LOG_CHANNEL=stack` grava em `storage/logs/laravel.log` — considerar apontar para um serviço externo (ex.: um canal de log adicional) antes de escalar o piloto.

## 5. Antes de ligar o tráfego real

- [ ] `.env` de produção revisto (chaves acima + credenciais reais da base de dados e, quando disponíveis, dos agregadores de SMS/mobile money).
- [ ] `php artisan migrate --force` corrido contra a base de dados de produção.
- [ ] `php artisan db:seed --class=...` só com os seeders de referência (categorias/produtos/localizações-piloto) — nunca dados de teste.
- [ ] Cron do servidor com `schedule:run` a cada minuto (item 3).
- [ ] `/up` a responder 200 a partir de fora da rede interna.
- [ ] Contas iniciais de `admin`/`operator` criadas directamente na base de dados (não há registo público para estes papéis — secção 4 do ROADMAP).