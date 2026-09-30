# PHP Clean Architecture API# PHP Clean Architecture API

[![Tests](https://github.com/ruedaCid/php-clean-architecture-api/actions/workflows/tests.yml/badge.svg)](https://github.com/ruedaCid/php-clean-architecture-api/actions/workflows/tests.yml)

A production-inspired REST API built with **PHP 8.4 and Laravel**, designed to demonstrate how Clean Architecture principles can be applied while keeping core business logic independent from the framework, persistence layer and HTTP transport.

This repository is intentionally small. Its purpose is not to showcase a large CRUD application, but to demonstrate architectural boundaries, dependency inversion, testability, authorization design and pragmatic backend engineering.

---

## Architecture

The application is divided into explicit layers:

```text
┌─────────────────────────────────────────────┐
│                    HTTP                     │
│ Controllers · Requests · Middleware · JSON  │
└──────────────────────┬──────────────────────┘
                       │
                       ▼
┌─────────────────────────────────────────────┐
│                Application                  │
│       Use Cases · Commands · Handlers       │
└──────────────────────┬──────────────────────┘
                       │
                       ▼
┌─────────────────────────────────────────────┐
│                   Domain                    │
│ Entities · Value Objects · Repository Ports │
│        Roles · Permissions · Policies       │
└──────────────────────▲──────────────────────┘
                       │
                       │ implements
┌──────────────────────┴──────────────────────┐
│               Infrastructure                │
│         Eloquent · MySQL · Adapters         │
└─────────────────────────────────────────────┘
```

The key rule is simple:

> **The domain does not depend on Laravel, Eloquent, HTTP or the database.**

Dependencies point towards the business core. Infrastructure implements contracts defined by the inner layers.

---

## Why this project?

Laravel makes it easy to build applications quickly, but business logic can easily become coupled to controllers, Eloquent models, facades and framework services.

This project explores a different approach:

- Keep domain objects framework-independent.
- Express application behaviour through explicit use cases.
- Define persistence as a contract rather than an implementation detail.
- Keep controllers focused on translating HTTP input and output.
- Translate application exceptions into HTTP responses at the application boundary.
- Keep authorization rules outside controllers and independent from Laravel.
- Test business behaviour without requiring the framework or a database.
- Test infrastructure and HTTP concerns separately.
- Run the complete test suite automatically through CI.

The goal is not architectural purity for its own sake.

The goal is to make important business logic easier to **understand, test, maintain and change**.

---

## Example request flow

Creating a customer follows this path:

```text
POST /api/customers
        │
        ▼
Authorization Middleware
        │
        ▼
CreateCustomerRequest
        │
        ▼
CustomerController
        │
        ▼
CreateCustomerCommand
        │
        ▼
CreateCustomerHandler
        │
        ▼
CustomerRepository
        │
        ▼
EloquentCustomerRepository
        │
        ▼
MySQL
```

The `CreateCustomerHandler` does not know that the request came through HTTP or that the customer will ultimately be stored using Eloquent.

Retrieving a customer follows the same principle:

```text
GET /api/customers/{id}
        │
        ▼
Authorization Middleware
        │
        ▼
CustomerController
        │
        ▼
GetCustomerHandler
        │
        ▼
CustomerRepository
```

---

## Project structure

```text
src/app/
├── Application/
│   └── Customer/
│       ├── CreateCustomer/
│       └── GetCustomer/
│
├── Domain/
│   ├── Authorization/
│   │   ├── AuthorizationPolicy.php
│   │   ├── Permission.php
│   │   └── Role.php
│   │
│   └── Customer/
│       ├── Customer.php
│       ├── CustomerId.php
│       ├── Email.php
│       └── CustomerRepository.php
│
├── Infrastructure/
│   └── Persistence/
│       └── Eloquent/
│
└── Http/
    ├── Controllers/
    ├── Middleware/
    └── Requests/
```

### Domain

Contains business concepts and rules.

It has no dependency on Laravel or Eloquent.

Examples:

- `Customer`
- `CustomerId`
- `Email`
- `CustomerRepository`
- `Role`
- `Permission`
- `AuthorizationPolicy`

### Application

Coordinates use cases.

Examples:

- `CreateCustomerHandler`
- `GetCustomerHandler`
- `CustomerAlreadyExists`
- `CustomerNotFound`

Application code depends on domain abstractions rather than infrastructure implementations.

### Infrastructure

Contains technical adapters.

`EloquentCustomerRepository` implements the repository contract defined by the domain and maps domain objects to the persistence model.

This allows the persistence mechanism to change without modifying the core business rules.

### HTTP

Contains Laravel-specific transport concerns:

- routing
- controllers
- middleware
- request validation
- JSON responses
- HTTP error representation

Controllers deliberately contain very little business logic.

---

# API

## Create customer

```http
POST /api/customers
Content-Type: application/json
Accept: application/json
X-Role: manager
```

Request:

```json
{
  "name": "John Smith",
  "email": "john@example.com"
}
```

Successful response:

```http
201 Created
```

```json
{
  "data": {
    "id": "generated-uuid",
    "name": "John Smith",
    "email": "john@example.com"
  }
}
```

A duplicated email produces:

```http
409 Conflict
```

Invalid HTTP input produces:

```http
422 Unprocessable Content
```

A role without the required permission produces:

```http
403 Forbidden
```

---

## Get customer

```http
GET /api/customers/{id}
Accept: application/json
X-Role: viewer
```

Successful requests return:

```http
200 OK
```

Unknown customers return:

```http
404 Not Found
```

---

# Error handling

Application exceptions remain independent from HTTP.

For example:

```text
CustomerAlreadyExists
        │
        ▼
HTTP exception mapping
        │
        ▼
409 Conflict
```

and:

```text
CustomerNotFound
        │
        ▼
HTTP exception mapping
        │
        ▼
404 Not Found
```

The application layer therefore does not need to know anything about HTTP status codes.

This keeps transport concerns at the application boundary instead of leaking them into use cases or domain objects.

---

# Authorization / RBAC

The project includes a small **Role-Based Access Control (RBAC)** example designed to demonstrate how authorization rules can remain independent from Laravel and the HTTP layer.

The authorization model defines three roles:

- `ADMIN`
- `MANAGER`
- `VIEWER`

and two permissions:

- `customer.create`
- `customer.read`

The current policy is:

| Role | `customer.create` | `customer.read` |
|---|---:|---:|
| Admin | ✓ | ✓ |
| Manager | ✓ | ✓ |
| Viewer | ✗ | ✓ |

## Authorization flow

```text
HTTP Request
     │
     │ X-Role
     ▼
RequirePermission Middleware
     │
     │ required permission
     ▼
AuthorizationPolicy
     │
     ├── Role
     └── Permission
     │
     ├── allowed ─────► Controller ─────► Application
     │
     └── denied ──────► 403 Forbidden
```

`Role`, `Permission` and `AuthorizationPolicy` are plain PHP domain concepts and have no dependency on Laravel.

The HTTP middleware is responsible for translating transport-specific information into those domain concepts.

This keeps authorization rules out of controllers:

```text
Controller
    │
    └── no role checks
        no permission matrices
        no framework-specific authorization rules
```

---

## Authentication vs authorization

For simplicity, this architectural example uses the `X-Role` HTTP header to simulate the role of an already authenticated actor.

For example:

```http
X-Role: manager
```

This is intentionally **not a production authentication mechanism**.

A real application should obtain the actor and its roles from a trusted authentication mechanism such as:

- OAuth2 / OpenID Connect
- JWT
- SSO
- server-side sessions
- an external Identity Provider

Client-provided role headers **must never be trusted in production**.

The purpose of `X-Role` in this repository is to keep authentication infrastructure outside the scope of the example while still demonstrating the authorization boundary.

---

## Authorization behaviour

A request without a valid role returns:

```http
401 Unauthorized
```

An actor without the required permission returns:

```http
403 Forbidden
```

Examples:

```text
VIEWER
   │
   ├── GET /api/customers/{id}  ──► 200
   │
   └── POST /api/customers      ──► 403

MANAGER
   │
   ├── GET /api/customers/{id}  ──► 200
   │
   └── POST /api/customers      ──► 201

ADMIN
   │
   ├── GET /api/customers/{id}  ──► 200
   │
   └── POST /api/customers      ──► 201
```

The authorization policy is covered both by isolated domain tests and HTTP feature tests.

---

# Testing strategy

The project uses several levels of automated tests.

The current automated test suite contains **30 tests** covering domain, application, infrastructure, HTTP and authorization behaviour.

The complete suite is executed automatically by GitHub Actions on every push and pull request to `main`.

## Domain unit tests

Domain tests exercise entities and value objects without Laravel or a database.

Examples include:

- valid and invalid email addresses
- customer identifiers
- customer invariants

These tests execute against plain PHP objects.

---

## Authorization tests

RBAC rules are tested independently from Laravel.

The tests verify the permission matrix for administrators, managers and viewers without booting the framework.

For example:

```text
ADMIN
  customer.create ✓
  customer.read   ✓

MANAGER
  customer.create ✓
  customer.read   ✓

VIEWER
  customer.create ✗
  customer.read   ✓
```

HTTP feature tests additionally verify that authorization is correctly enforced at the application boundary.

---

## Application unit tests

Application tests use an in-memory repository implementation.

This allows use cases to be tested independently from Eloquent and MySQL.

```text
Application
     │
     ▼
CustomerRepository
     │
     ▼
InMemoryCustomerRepository
```

The same application code later receives the real Eloquent implementation through dependency injection.

---

## Infrastructure tests

Infrastructure tests verify the Eloquent repository implementation and persistence mapping.

These tests ensure that the infrastructure adapter correctly fulfils the repository contract expected by the application.

---

## HTTP feature tests

Feature tests exercise the API boundary.

Scenarios include:

- customer creation
- duplicated customers
- invalid requests
- customer retrieval
- missing customers
- authorized customer creation
- forbidden customer creation
- read-only access
- unauthenticated requests
- RBAC enforcement

They also verify that forbidden operations do not produce persistence side effects.

Run the complete suite with:

```bash
docker compose exec app php artisan test
```

---

# Continuous Integration

The repository uses **GitHub Actions** for continuous integration.

Every push and pull request to `main` automatically runs the test workflow:

```text
Push / Pull Request
        │
        ▼
GitHub Actions
        │
        ├── PHP 8.4
        ├── Composer validation
        ├── Dependency installation
        ├── Laravel environment preparation
        │
        └── Test suite
                │
                ▼
           30 tests
```

The current CI status is displayed at the top of this README.

---

# Technology

- PHP 8.4
- Laravel
- MySQL 8.4
- SQLite for automated tests
- Eloquent ORM
- PHPUnit
- Docker
- Docker Compose
- Nginx
- GitHub Actions

---

# Running locally

## Requirements

You only need:

- Docker
- Docker Compose
- Git

Clone the repository:

```bash
git clone git@github.com:ruedaCid/php-clean-architecture-api.git
cd php-clean-architecture-api
```

Create the Laravel environment configuration:

```bash
cp src/.env.example src/.env
```

Build and start the containers:

```bash
docker compose up -d --build
```

Install dependencies:

```bash
docker compose exec app composer install
```

Generate the Laravel application key:

```bash
docker compose exec app php artisan key:generate
```

Run the migrations:

```bash
docker compose exec app php artisan migrate
```

Run the complete test suite:

```bash
docker compose exec app php artisan test
```

The API is available locally at:

```text
http://localhost:8090
```

---

# Example requests

## Create a customer as manager

```bash
curl -X POST http://localhost:8090/api/customers \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "X-Role: manager" \
  -d '{
    "name": "John Smith",
    "email": "john@example.com"
  }'
```

Expected response:

```http
201 Created
```

---

## Attempt creation as viewer

```bash
curl -X POST http://localhost:8090/api/customers \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "X-Role: viewer" \
  -d '{
    "name": "John Smith",
    "email": "john@example.com"
  }'
```

Expected response:

```http
403 Forbidden
```

---

## Retrieve a customer as viewer

```bash
curl http://localhost:8090/api/customers/customer-id \
  -H "Accept: application/json" \
  -H "X-Role: viewer"
```

A viewer can read customers because `customer.read` is included in the viewer permission set.

---

# Design decisions

## Why not use Eloquent models directly in the domain?

Doing so would couple business rules to a specific ORM and framework.

Instead:

```text
Domain
   │
   └── CustomerRepository interface

Infrastructure
   │
   └── EloquentCustomerRepository
```

The dependency points inward.

The application depends on the abstraction rather than the persistence implementation.

---

## Why validate email twice?

HTTP validation and domain validation solve different problems.

The HTTP layer rejects malformed requests early.

The domain guarantees that an invalid `Email` value object cannot exist regardless of whether it was created through:

- HTTP
- CLI
- a queue
- a scheduled process
- a test
- another integration

The domain therefore protects its own invariants.

---

## Why use an in-memory repository in unit tests?

Application behaviour can be tested without booting Laravel or connecting to a database.

```text
CreateCustomerHandler
        │
        ▼
CustomerRepository
        │
        ├── tests ─────► InMemoryCustomerRepository
        │
        └── runtime ───► EloquentCustomerRepository
```

The use case does not change.

Only the adapter does.

---

## Why not put authorization directly in controllers?

Code such as:

```text
if user.role == admin
```

inside controllers mixes authorization rules with HTTP transport concerns.

Instead, the project separates:

```text
HTTP Middleware
      │
      ▼
AuthorizationPolicy
      │
      ├── Role
      └── Permission
```

Controllers remain focused on translating requests into application calls.

---

## Why distinguish 401 and 403?

They represent different situations.

```text
No valid identity / role
        │
        ▼
401 Unauthorized
```

versus:

```text
Known role
but insufficient permission
        │
        ▼
403 Forbidden
```

Keeping that distinction makes the API contract clearer.

---

## Why is the project deliberately small?

The repository is an architectural example rather than a complete customer-management product.

Adding additional CRUD endpoints, entities or infrastructure would increase the amount of code without necessarily improving the architectural demonstration.

The focus is instead on showing a complete vertical slice:

```text
HTTP
 │
 ▼
Authorization
 │
 ▼
Application
 │
 ▼
Domain
 │
 ▼
Repository abstraction
 │
 ▼
Infrastructure
 │
 ▼
Database
```

with automated tests around each important boundary.

---

# Principles demonstrated

This project demonstrates practical use of:

- Clean Architecture
- Dependency Inversion Principle
- Separation of Concerns
- Domain modelling
- Value Objects
- Repository Pattern
- Use Case / Application Service Pattern
- Dependency Injection
- Framework-independent business logic
- Infrastructure adapters
- Centralized HTTP error handling
- Role-Based Access Control (RBAC)
- Framework-independent authorization rules
- Authentication / authorization separation
- Unit testing
- Feature testing
- Persistence testing
- Dockerized development
- Continuous Integration

---

# What this project intentionally does not include

To keep the repository focused on architecture, some production concerns are intentionally outside its scope.

These include:

- full user authentication
- OAuth2 / OIDC implementation
- JWT management
- password management
- refresh tokens
- user registration
- production secrets management
- advanced observability
- distributed caching
- queues
- Kubernetes deployment

These could be added around the existing architecture without requiring the domain model to depend on them.

---

# Author

**Ignacio Rodriguez**

Senior software engineering and development leader with a background in backend development, software architecture, APIs, systems integration and cloud-based platforms.

My work focuses on connecting technical architecture with business requirements, designing maintainable systems and integrations, and leading development across complex software ecosystems.

This repository is part of my technical portfolio and demonstrates an approach to building PHP applications where business rules remain independent from frameworks and infrastructure.
