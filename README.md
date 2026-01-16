# Job Management API

A Symfony REST API for managing members, job offers, and applications.
Now fully containerized with Docker for easy development and deployment.

## Features

- **User Management**: Admins can create and manage users with role-based access (ADMIN/MEMBER)
- **Job Offers**: Admins create jobs, members can view and apply
- **Applications**: Members apply once per job, admins monitor all applications
- **Security**: JWT authentication with role-based access control

## Technical Stack

- **Framework**: Symfony 6.4 (LTS) + API Platform 3.4
- **Language**: PHP 8.2
- **Database**: MySQL 8.0
- **Web Server**: Nginx (Alpine)
- **Containerization**: Docker & Docker Compose
- **Authentication**: JWT (LexikJWT)
- **Testing**: PHPUnit

---

## 🚀 Quick Start (Docker)

The recommended way to run this project is using Docker.

### Prerequisites
- Docker & Docker Compose
- Make (optional, for shortcut commands)

### Installation

1. **Setup Environment**
   ```bash
   cp .env.example .env
   cp .env.test.example .env.test
   ```

2. **Build and Start**
   Using Make:
   ```bash
   make first-install
   ```
   *Note: This builds images, installs dependencies, sets up the database, generates JWT keys, and seeds data.*
   
    OR Manually:
    ```bash
    # 1. Build and start containers
    docker compose build --no-cache
    docker compose up -d

    # 2. Install PHP dependencies
    docker compose exec php composer install

    # 3. Setup database and migrations
    docker compose exec php php bin/console doctrine:database:create --if-not-exists
    docker compose exec php php bin/console doctrine:migrations:migrate --no-interaction

    # 4. Generate JWT keys
    docker compose exec php php bin/console lexik:jwt:generate-keypair --overwrite --no-interaction

    # 5. Seed initial data
    docker compose exec php php bin/console app:setup
    ```

3. **Access the Application**
   - **API Documentation**: [http://localhost:8080/api](http://localhost:8080/api)
   - **Application**: [http://localhost:8080](http://localhost:8080)

### Useful Commands

| Feature | Make Command | Manual Docker Command |
|---------|--------------|-----------------------|
| **Setup** | `make first-install` | *(See steps above)* |
| **Start/Stop** | `make up` / `make down` | `docker compose up -d` / `down` |
| **Logs** | `make logs` | `docker compose logs -f` |
| **PHP Shell** | `make shell` | `docker compose exec php bash` |
| **Test Suite** | `make test` | *(See Testing section)* |
| **DB Reset** | `make db-reset` | `docker compose exec php php bin/console doctrine:database:drop --force...` |
| **JWT Keys** | `make jwt-keys` | `docker compose exec php php bin/console lexik:jwt:generate-keypair...` |

---

## 💻 Local Development (No Docker)

If you prefer to run PHP locally without Docker:

### Prerequisites
- PHP 8.2+ with extensions: `intl`, `pdo_mysql`, `sodium`, `zip`
- Composer
- MySQL Server

### Installation

```bash
# 1. Install dependencies
composer install

# 2. Setup Environment
cp .env.example .env
# Edit .env to configure your local DATABASE_URL

# 3. Generate JWT Keys
php bin/console lexik:jwt:generate-keypair

# 4. Setup Database
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate --no-interaction

# 5. Project Setup (Populates seed data)
php bin/console app:setup
```

### Running

```bash
php -S 127.0.0.1:8000 -t public
```
API Docs: `http://127.0.0.1:8000/api`

---

## 🧪 Testing

Run all 49+ unit and functional tests:

**With Docker:**
Using Make:
```bash
make test
```

Manually:
```bash
# 1. Create test database
docker compose exec -e DATABASE_URL="mysql://root:root@mysql:3306/symfony_db?serverVersion=8.0&charset=utf8mb4" php php bin/console --env=test doctrine:database:create --if-not-exists

# 2. Run migrations for test environment
docker compose exec -e DATABASE_URL="mysql://root:root@mysql:3306/symfony_db?serverVersion=8.0&charset=utf8mb4" php php bin/console --env=test doctrine:migrations:migrate --no-interaction

# 3. Execute PHPUnit
docker compose exec -e DATABASE_URL="mysql://root:root@mysql:3306/symfony_db?serverVersion=8.0&charset=utf8mb4" php php bin/phpunit
```

**Locally (No Docker):**
```bash
php bin/phpunit
```

---

## 🔑 Default Credentials

After running migrations/setup, the following accounts are available (if seeded):

- **Admin**: `admin@example.com` / `password`
- **User**: `user@example.com` / `password`

## 📚 API Endpoints (V1)

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| POST | `/api/v1/login` | - | Get JWT token |
| GET | `/api/admin/v1/users` | ADMIN | List users |
| POST | `/api/admin/v1/users` | ADMIN | Create user |
| GET | `/api/v1/job-offers` | AUTH | List job offers |
| POST | `/api/admin/v1/job-offers` | ADMIN | Create job offer |
| POST | `/api/v1/job-applications` | AUTH | Apply for a job |
| GET | `/api/admin/v1/job-applications` | ADMIN | List all applications |

> [!NOTE]
> We use the `/api/admin/v1` prefix for Administrative operations.

---

## 🏗 Architecture Highlights

The project follows clean architecture principles:
- **Domain logic** separated from infrastructure
- **CQRS**: Command/Query separation for write and read operations
- **Event-driven**: Side effects (e.g., notifications) handled via events
- **Comprehensive test coverage**

## Technical Decisions & Scope

### What Was Intentionally NOT Implemented

The following features were deliberately omitted to maintain focus on core backend architecture:

**Infrastructure & DevOps:**
- **CI/CD Pipeline**: Automated testing and deployment (GitHub Actions, GitLab CI).
- **Error Monitoring**: Real-time error tracking and performance monitoring (Sentry).
- **Observability**: Structured logging (Monolog), metrics (Prometheus), distributed tracing (OpenTelemetry).

**Performance & Scalability:**
- **Caching Layer**: Redis for query results, session storage, and rate limiting.
- **Database Read Replicas**: Horizontal scaling with master-slave replication.
- **Background Job Queue**: Async processing with Symfony Messenger + RabbitMQ for heavy operations.

**Security & Compliance:**
- **reCAPTCHA v3**: Bot protection for login endpoint.
- **OAuth2 / Social Login**: Third-party authentication (Google, GitHub).
- **Audit Logging**: Track all state changes for compliance.
- **Data Encryption**: At-rest encryption for sensitive fields.

**Features:**
- **Email/SMS Notifications**: Event listener infrastructure exists (`JobAppliedListener`) but is not wired to an actual email service.
- **File Uploads**: CV/Resume attachments for job applications.
- **Advanced Search**: Full-text search with Elasticsearch.
