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
   ```

2. **Build and Start**
   Using Make:
   ```bash
   make first-install
   ```
   *Note: This builds images, installs dependencies, sets up the database, generates JWT keys, and seeds data.*
   
   OR Manually:
   ```bash
   docker compose build
   docker compose up -d
   docker compose exec php composer install
   docker compose exec php php bin/console doctrine:database:create
   docker compose exec php php bin/console doctrine:migrations:migrate --no-interaction
   docker compose exec php php bin/console lexik:jwt:generate-keypair --skip-if-exists
   ```

3. **Access the Application**
   - **API Documentation**: [http://localhost:8080/api](http://localhost:8080/api)
   - **Application**: [http://localhost:8080](http://localhost:8080)

### Useful Commands

| Command | Description |
|---------|-------------|
| `make up` / `make down` | Start/Stop containers |
| `make logs` | View application logs |
| `make shell` | Access the PHP container shell |
| `make test` | Run the test suite |
| `make db-reset` | Reset database (drop, create, migrate) |

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
```bash
make test
```

**Locally:**
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
