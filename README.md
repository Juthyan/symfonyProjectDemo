# Collaborative Workflow API

A Symfony-based RESTful API for managing users, boards, and roles in a collaborative task management system.

---

## 🛠️ Tech Stack

- **PHP 8.3**
- **Symfony 6+**
- **PostgreSQL**
- **Doctrine ORM**
- **NelmioApiDocBundle** (OpenAPI/Swagger UI)
- **PHPUnit + Mockery** (unit testing)
- **PHP-CS-Fixer** (code style)
- **Twig** (optional, only if rendering HTML views)

---

## 📁 Project Structure

src/
├── Controller/ # API controllers
├── Entity/ # Doctrine entities: User, Board, Role, UserRole, Task
├── Formatter/ # Formatters to transform entities into JSON-ready arrays
├── Repository/ # Custom repository logic
├── Services/ # Business logic layer
config/
├── openapi/ # Custom OpenAPI spec (api_doc.yaml)
├── routes.yaml # Swagger UI & JSON spec exposure
tests/
├── Formatter/ # Unit tests for formatters
├── Services/ # Unit tests for services

yaml
Copier le code

---

## 🚀 Getting Started

### 1. Clone & Install Dependencies

```bash
git clone https://github.com/your-repo/collaborative-workflow.git
cd collaborative-workflow

composer install
2. Setup Environment
bash
Copier le code
cp .env .env.local
# Edit database credentials as needed
3. Database
bash
Copier le code
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
4. Run the App
bash
Copier le code
symfony serve
# or
php -S localhost:8000 -t public
📡 API Endpoints
🔐 User Endpoints
GET /user — Fetch all users

GET /user/{username} — Fetch a user by username

📋 Board Endpoints
GET /board — Fetch all boards

GET /board/{id} — Fetch a board by ID

All responses are formatted using Formatter classes (e.g., UserFormatter, BoardFormatter).

🧪 Running Tests
bash
Copier le code
./vendor/bin/phpunit
Includes unit tests for services and formatters using Mockery.

💅 Code Style
bash
Copier le code
./vendor/bin/php-cs-fixer fix --allow-risky=yes
CS Fixer is configured to enforce strict types, short array syntax, and Symfony rules.

📖 API Documentation
Swagger UI available at:

pgsql
Copier le code
GET /api/doc         → Interactive Swagger UI
GET /api/doc.json    → Raw OpenAPI JSON spec
🔧 Define Your Own Docs
OpenAPI YAML:
Edit config/openapi/api_doc.yaml to define custom documentation manually.

Example:

yaml
Copier le code
openapi: 3.0.0
info:
  title: My App API
  description: API documentation for My App
  version: 1.0.0
paths:
  /user/{username}:
    get:
      summary: Get a user by username
      parameters:
        - name: username
          in: path
          required: true
          schema:
            type: string
      responses:
        '200':
          description: OK
Make sure the YAML paths match your actual route prefixes (e.g., /user, not /api/user unless configured that way).

📌 Design Notes
Formatters abstract the serialization logic from controllers.

Services handle business logic and interact with Doctrine repositories.

DTOs and validation resolvers may be introduced later for request validation.

Manual OpenAPI definition avoids route annotations for cleaner code.

🧼 TODO
 Add DTO & request validation with Symfony Validator

 Add authentication (JWT or session-based)

 Extend Swagger spec to cover all endpoints

 CI pipeline for tests and code style checks

🧑‍💻 Author
Yann
Open to contributions, improvements, and feedback!

yaml
Copier le code

---