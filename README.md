# Admin Service

Administration dashboard for the portfolio platform. Built with FilamentPHP, provides user management and content moderation. Authenticates exclusively through SSO (OAuth 2.0 Authorization Code flow) and communicates with the Users service via internal API.

## Architecture

```
Browser ──▶ Traefik ──▶ Nginx ──▶ PHP-FPM (Laravel + Filament)
                                       │
                              ┌────────┴────────┐
                              ▼                  ▼
                         Users API           SSO OAuth
                      (internal API key)   (authentication)
```

**Domain:** `admin.microservices.local`

## Tech Stack

- **Backend:** PHP 8.5 / Laravel 12
- **Admin panel:** FilamentPHP 3.3
- **Database:** MySQL 8
- **Authentication:** SSO OAuth 2.0 (Authorization Code flow)

## Features

- User management (list, edit, delete, role assignment)
- SSO-based admin authentication (only users with `admin` role)
- Service-to-service communication with Users API (API key)
- Kubernetes-ready health endpoints

## Routes

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/admin` | Filament dashboard |
| GET | `/auth/sso/callback` | SSO OAuth callback |
| GET | `/health` | Liveness probe |
| GET | `/ready` | Readiness probe (DB) |

## Services Communication

| Target | Protocol | Purpose |
|--------|----------|---------|
| Users API | HTTP (X-Internal-Api-Key) | User CRUD, role management |
| SSO | OAuth 2.0 | Admin authentication |

## Getting Started

### Prerequisites

- Docker & Docker Compose
- Running infrastructure services (Traefik)
- Running Users and SSO services

### Development

```bash
cp src/.env.example src/.env
# Edit .env with your configuration

docker compose up -d
```

Containers:

| Container | Role | Port |
|-----------|------|------|
| `admin-app` | PHP-FPM | 9000 (internal) |
| `admin-nginx` | Web server | via Traefik |
| `admin-db` | MySQL 8 | 127.0.0.1:3309 |

### Running Tests

```bash
docker compose run --rm --no-deps \
  -e APP_ENV=testing -e DB_CONNECTION=sqlite -e DB_DATABASE=:memory: \
  admin-app ./vendor/bin/phpunit
```

## Test Coverage

| Metric | Value |
|--------|-------|
| Line coverage | 73.0% |
| Tests | 28 |

## Roadmap

- [x] Filament admin panel
- [x] User management (CRUD, role assignment)
- [x] SSO OAuth2 authentication (replacing Filament login form)
- [x] Kubernetes manifests and health endpoints
- [ ] Analytics dashboard (post view statistics via Filament)

## License

All rights reserved.
