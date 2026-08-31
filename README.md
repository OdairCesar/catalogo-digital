# Fit By Cae

Site da Fit By Cae (moda fitness) construído em Laravel 13 + Filament 4. Combina páginas públicas
(home, catálogo de produtos, blog, FAQ e contato) com um painel administrativo para gerenciar
catálogo, conteúdo e leads.

Este projeto nasceu de um boilerplate de agência de tecnologia (OD Tec) com módulos extras de
serviços/SEO local, portfólio e ferramentas de geração de leads. Esses módulos continuam no
código, mas ficam desativados via `.env` (`config/modules.php`) nesta implantação, por não fazerem
sentido para uma marca de moda fitness.

## Stack

- **Backend:** PHP 8.5, Laravel 13, Livewire 3
- **Admin:** Filament 4 (`/admin`)
- **Frontend:** Tailwind CSS 4, Vite
- **Testes:** Pest 4 / PHPUnit 12
- **Qualidade:** Larastan (PHPStan), Laravel Pint
- **IA:** abstração própria (`app/Services/Ai`) sobre `openai-php/laravel` e `google-gemini-php/laravel`,
  escolhida via config (`AI_TEXT_PROVIDER` / `AI_IMAGE_PROVIDER`) — usada para geração de posts de
  blog, conteúdo de clusters de serviço, imagens de capa e relatórios do consultor de IA
- **Mídia:** Cloudinary (via `codebar-ag/laravel-flysystem-cloudinary`) como disco de arquivos
- **Filas:** Laravel Queue (driver `database`)

## Funcionalidades principais

- **Páginas públicas** (`routes/web.php`): home, sobre, serviços, cidades, estados, portfólio, blog,
  FAQ, contato, consultor de IA, `sitemap.xml` e `robots.txt`.
- **SEO programático:** rotas curinga `{service}-em-{city}` que resolvem `LandingPage`s e
  `ServiceClusterLandingPage`s sincronizadas automaticamente a partir de `Service`/`ServiceCluster`/
  `City` (`app/Actions/Landing`, `app/Actions/ServiceCluster`).
- **Blog e clusters de serviço com geração via IA:** jobs `App\Jobs\GenerateAiBlogPost` e
  `App\Jobs\GenerateServiceClusterContent` (fila `database`) geram texto e imagem de capa via IA
  (`app/Services/Blog`).
- **Consultor de IA:** chat (`App\Livewire\ConsultationChat`) que coleta dados do lead e dispara
  `App\Jobs\GenerateConsultationReport`, gerando um relatório com IA e notificando por e-mail.
- **Captura de leads:** formulário de contato (`/contato`) com throttle, listado no painel admin.
- **Painel admin (Filament):** CRUD de Serviços, Clusters de Serviço, Cidades, Estados, Landing
  Pages, Portfólio, Posts, Categorias, Leads e Consultorias em `app/Filament/Resources`.

## Setup local

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed
npm install
npm run build
```

Ou use o script agregado do Composer, que faz tudo isso de uma vez:

```bash
composer run setup
```

Configure no `.env` as credenciais de banco (`DB_*`), do provedor de IA escolhido (`OPENAI_*` e/ou
`GEMINI_*`, mais `AI_TEXT_PROVIDER`/`AI_IMAGE_PROVIDER`) e Cloudinary (`CLOUDINARY_*`) antes de gerar
conteúdo via IA.

### Ambiente de desenvolvimento

```bash
composer run dev
```

Sobe em paralelo o servidor Laravel, o worker da fila (`queue:listen`) e o Vite em modo watch.

## Testes

```bash
php artisan test --compact
```

## Deploy no Railway

O serviço web padrão do Railway só atende HTTP — nada processa a fila `database` usada pelos jobs
de geração via IA (`GenerateAiBlogPost`, `GenerateServiceClusterContent`, `GenerateConsultationReport`)
e por `RegenerateLandingPages`. É preciso um **segundo serviço** ("worker"), apontando para o mesmo
repositório/branch:

1. Crie um novo serviço no mesmo projeto Railway, mesmo repo/branch do serviço web.
2. Em **Settings → Deploy → Custom Start Command**: `bash railway/run-worker.sh`.
3. Em **Settings → Networking**: não exponha domínio/porta — é um worker, não recebe requisições.
4. Em **Variables**: copie/vincule as mesmas variáveis do serviço web (`DB_*`, `OPENAI_*`/`GEMINI_*`,
   `CLOUDINARY_*`, `QUEUE_CONNECTION`, `DB_QUEUE_RETRY_AFTER`).

Veja [`railway/run-worker.sh`](railway/run-worker.sh) para o comando exato e por que os timeouts
(`--timeout`, `DB_QUEUE_RETRY_AFTER`) precisam ficar alinhados entre si.

### Proxy, IP do cliente e rate limiting

A aplicação roda atrás do proxy de borda do Railway (Envoy). Duas premissas de segurança
dependem disso e valem para qualquer plataforma onde o app for hospedado:

- **`X-Forwarded-Host` não é confiado** ([`bootstrap/app.php`](bootstrap/app.php)) para evitar
  _Host header poisoning_ de URLs assinadas e links de e-mail. O Railway já envia o domínio real
  no header `Host`, então nada quebra. Se migrar para um proxy que só repasse o domínio via
  `X-Forwarded-Host`, será preciso reavaliar isso.
- **Rate limiting usa o IP real** via [`App\Support\ClientIp`](app/Support/ClientIp.php), em vez do
  `X-Forwarded-For` (que o cliente controla e falsifica). A cadeia atual de DNS é
  **registro.br → Cloudflare → Railway**, então a ordem de preferência é:
  1. `CF-Connecting-IP` — IP real do visitante quando a Cloudflare está proxando (nuvem laranja).
     Necessário porque, atrás da Cloudflare, o header do Envoy só enxerga um IP de edge da Cloudflare.
  2. `X-Envoy-External-Address` — definido pelo Envoy do Railway; é o IP real quando a Cloudflare está
     só como DNS (nuvem cinza) ou ausente.
  3. `request()->ip()` — fallback local/testes.

  **Bypass (risco residual baixo):** o serviço não expõe o domínio `*.up.railway.app` — só
  `odtec.com.br`, atrás da Cloudflare (nuvem laranja). Assim o endpoint de origem do Railway fica
  escondido e não há caminho de bypass público, então `CF-Connecting-IP` é confiável na prática. O
  resíduo é apenas teórico: quem **descobrir a origem do Railway** (via Certificate Transparency, DNS
  antigo, vazamento) poderia bater direto com `Host: odtec.com.br` e forjar `CF-Connecting-IP` para
  furar o throttle. Para eliminar isso de vez, restrinja a origem à Cloudflare (Authenticated Origin
  Pull / header secreto) **ou** aplique o rate limiting nas regras de borda da Cloudflare. Ao trocar
  de provedor de borda, ajuste `ClientIp::TRUSTED_IP_HEADERS` para o header equivalente.

## Documentação e ferramentas de IA

O projeto usa [Laravel Boost](https://laravel.com/docs/ai) para dar contexto e ferramentas a
agentes de IA (Claude Code, Cursor, etc). Convenções e regras específicas do projeto estão em
[`CLAUDE.md`](CLAUDE.md).
