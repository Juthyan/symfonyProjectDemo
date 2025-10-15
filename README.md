# 🧠 Collaborative Workflow API

A **Symfony-based RESTful API** for managing users, boards, and roles in a collaborative task management system.

---

## 🛠️ Tech Stack

- **PHP 8.3**
- **Symfony 6+**
- **JWT Authentication (JSON Web Tokens)**
- **PostgreSQL (Cloud SQL)**
- **Doctrine ORM**
- **OpenAPI / Swagger UI**
- **PHPUnit + Mockery** (unit testing)
- **PHP-CS-Fixer** (code style)
- **PHPStan** (static analysis)
- **Twig** *(optional, for HTML rendering)*

---

## 📁 Project Structure

```
src/
├── Controller/   # API controllers
├── DTO/
├── Entity/       # Doctrine entities: User, Board, Role, UserRole, Task
├── Formatter/    # Formatters for clean JSON responses
├── Repository/   # Custom repository logic
├── Services/     # Business logic layer
├── Utils/

public/
├── openapi/      # Custom OpenAPI spec (api_doc.yaml)

config/
├── routes.yaml   # Swagger UI & JSON spec exposure

tests/
├── Formatter/    # Unit tests for formatters
├── Services/     # Unit tests for services
```

---

## 🚀 Getting Started

### 1️⃣ Clone & Install Dependencies
```bash
git clone https://github.com/your-repo/collaborative-workflow.git
cd collaborative-workflow
composer install
```

### 2️⃣ Setup Environment
```bash
cp .env .env.local
# Edit database credentials as needed
```

### 3️⃣ Database
```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

### 4️⃣ Run the App (Local Development)
```bash
symfony serve
# or
php -S localhost:8000 -t public
```

---

## 📡 API Endpoints

### 🔐 User Endpoints
- `GET /users` — Fetch all users  
- `GET /users/{username}` — Fetch a user by username  
- `POST /users/save` — Create a new user  
- `PATCH /users/edit/{id}` — Update an existing user  
- `DELETE /users/{id}` — Delete a user  

### 📋 Board Endpoints
- `GET /boards` — Fetch all boards  
- `GET /boards/{id}` — Fetch a board by ID  
- `POST /boards/save` — Create a new board  
- `PUT /boards/edit/{id}` — Update a board  
- `DELETE /boards/{id}` — Delete a board  

All responses are formatted using dedicated `Formatter` classes.

---

## 🧪 Running Tests
```bash
./vendor/bin/phpunit
```
Includes unit tests for `Services` and `Formatters` using Mockery.

---

## 💅 Code Style
```bash
./vendor/bin/php-cs-fixer fix --allow-risky=yes
```
Ensures PSR-12 and Symfony rules with strict types and short array syntax.

---

## 📖 API Documentation

Swagger UI available at:
- `GET /api/doc` → Interactive Swagger UI  
- `GET /api/doc.json` → Raw OpenAPI JSON spec  

Custom OpenAPI spec: `public/openapi/api_doc.yaml`

---

## ☁️ Deployment (Google Cloud Run)

### CI/CD Pipeline Overview

This project is configured for **automatic deployment to Google Cloud Run** using **Cloud Build** and **Artifact Registry**.

Every push to the `main` branch will:
1. Build a Docker image via Cloud Build  
2. Push it to Artifact Registry  
3. Deploy it to Cloud Run  

---

### Required GCP Resources

| Resource | Purpose |
|-----------|----------|
| **Secret Manager** | Stores sensitive data like `DATABASE_URL`, `JWT_PASSPHRASE`, etc. |
| **Artifact Registry** | Hosts Docker images |
| **Cloud Run** | Runs Symfony API |
| **Cloud SQL (PostgreSQL)** | Shared database |
| **VPC Connector** | `symfony-vpc-connector` for secure SQL access |
| **Service Account** | `cloud-run-db-job@PROJECT_ID.iam.gserviceaccount.com` with least privilege roles |

---

### Build Configuration (`cloudbuild.yaml`)
```yaml
steps:
  - name: 'gcr.io/cloud-builders/docker'
    args: [
      'build',
      '-t', 'REGION-docker.pkg.dev/PROJECT_ID/symfony-repo/symfony-image:$COMMIT_SHA',
      '.'
    ]

  - name: 'gcr.io/cloud-builders/docker'
    args: [
      'push',
      'REGION-docker.pkg.dev/PROJECT_ID/symfony-repo/symfony-image:$COMMIT_SHA'
    ]

  - name: 'gcr.io/google.com/cloudsdktool/cloud-sdk'
    entrypoint: gcloud
    args: [
      'run', 'deploy', 'symfony-web-service',
      '--image', 'REGION-docker.pkg.dev/PROJECT_ID/symfony-repo/symfony-image:$COMMIT_SHA',
      '--region', 'REGION',
      '--platform', 'managed',
      '--service-account', 'cloud-run-db-job@PROJECT_ID.iam.gserviceaccount.com',
      '--allow-unauthenticated',
      '--vpc-connector', 'symfony-vpc-connector',
      '--set-env-vars', 'APP_ENV=prod,DATABASE_URL=${DATABASE_URL}',
      '--timeout', '300',
      '--concurrency', '80',
      '--min-instances', '1'
    ]

images:
  - 'REGION-docker.pkg.dev/PROJECT_ID/symfony-repo/symfony-image:$COMMIT_SHA'
```

Then simply push:
```bash
git push origin main
```
The pipeline runs automatically 🎯

---

## 🔭 Phase 2 (Upcoming Features)

| Feature | Description |
|----------|--------------|
| **Go Microservice** | Background job handler for async processing, events, or notifications |
| **Refactor** | Code structure and performance improvements |
| **User Permissions** | Role-based access control (RBAC) for fine-grained authorization |
| **I18n** | Internationalization and localization support |

---

## 🧑‍💻 Author

**Yann**  
Open to contributions, improvements, and feedback!

---