Collaborative Workflow API
A Symfony-based RESTful API for managing users, boards, and roles in a collaborative task management system.

🛠️ Tech Stack
PHP 8.3

Symfony 6+

JWT Authentication (JSON Web Tokens)

PostgreSQL

Doctrine ORM

OpenApi SwaggerUI

PHPUnit + Mockery (unit testing)

PHP-CS-Fixer (code style)

PHP-Stan

Twig (optional, only if rendering HTML views)

📁 Project Structure
src/
├── Controller/ # API controllers
├── DTO/
├── Entity/ # Doctrine entities: User, Board, Role, UserRole, Task
├── Formatter/ # Formatters to transform entities into JSON-ready arrays
├── Repository/ # Custom repository logic
├── Services/ # Business logic layer
├── Utils

public/
├── openapi/ # Custom OpenAPI spec (api_doc.yaml)

config/
├── routes.yaml # Swagger UI & JSON spec exposure

tests/
├── Formatter/ # Unit tests for formatters
├── Services/ # Unit tests for services



🚀 Getting Started
1. Clone & Install Dependencies
git clone [https://github.com/your-repo/collaborative-workflow.git](https://github.com/your-repo/collaborative-workflow.git)
cd collaborative-workflow

composer install



2. Setup Environment
cp .env .env.local
# Edit database credentials as needed



3. Database
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate



4. Run the App (Local Development)
symfony serve
# or
php -S localhost:8000 -t public



📡 API Endpoints
🔐 User Endpoints
GET /users — Fetch all users
GET /users/{username} — Fetch a user by username
POST /users/save — Create a new user
PATCH /users/edit/{id} — Update an existing user (PATCH for partial update)
DELETE /users/{id} — Delete a user by ID

📋 Board Endpoints
GET /boards — Fetch all boards
GET /boards/{id} — Fetch a board by ID
POST /boards/save — Create a new board
PUT /boards/edit/{id} — Update an existing board (PUT kept for demonstration)
DELETE /boards/{id} — Delete a board by ID

All responses are formatted using Formatter classes (e.g., UserFormatter, BoardFormatter).

🧪 Running Tests
./vendor/bin/phpunit



Includes unit tests for services and formatters using Mockery.

💅 Code Style
./vendor/bin/php-cs-fixer fix --allow-risky=yes



CS Fixer is configured to enforce strict types, short array syntax, and Symfony rules.

📖 API Documentation
Swagger UI available at:

GET /api/doc         → Interactive Swagger UI
GET /api/doc.json    → Raw OpenAPI JSON spec



🔧 Define Your Own Docs
OpenAPI YAML:
Edit config/openapi/api_doc.yaml to define custom documentation manually.

Example:

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
Security (JWT): Most routes require a valid JWT token in the Authorization: Bearer <token> header.

Formatters abstract the serialization logic from controllers.

Services handle business logic and interact with Doctrine repositories.

DTOs and validation resolvers may be introduced later for request validation.

Manual OpenAPI definition avoids route annotations for cleaner code.

🎯 TODO
Add logs

Refactor

Containerization (Docker): Create an optimized (multi-stage) Dockerfile for the production environment (PHP-FPM).

Deployment on GCP Cloud Run: Set up CI/CD and gcloud commands to deploy the Docker image on Google Cloud Run.

I18n

🧑‍💻 Author
Yann
Open to contributions, improvements, and feedback!