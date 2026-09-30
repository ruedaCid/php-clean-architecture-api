# PHP Clean Architecture API

A production-inspired REST API built with **PHP 8.4 and Laravel**, designed to demonstrate how Clean Architecture principles can be applied while keeping the core business logic independent from the framework, persistence layer and HTTP transport.

This repository is intentionally small. Its purpose is not to showcase a large CRUD application, but to demonstrate architectural boundaries, dependency inversion, testability and pragmatic backend design.

## Architecture

The application is divided into explicit layers:

```text
┌─────────────────────────────────────────────┐
│                  HTTP                       │
│   Controllers · Requests · JSON responses   │
└──────────────────────┬──────────────────────┘
                       │
                       ▼
┌─────────────────────────────────────────────┐
│               Application                   │
│      Use Cases · Commands · Handlers        │
└──────────────────────┬──────────────────────┘
                       │
                       ▼
┌─────────────────────────────────────────────┐
│                  Domain                     │
│ Entities · Value Objects · Repository Ports │
└──────────────────────▲──────────────────────┘
                       │
                       │ implements
┌──────────────────────┴──────────────────────┐
│              Infrastructure                 │
│        Eloquent · MySQL · Adapters          │
└─────────────────────────────────────────────┘
```

The key rule is simple:

> The domain does not depend on Laravel, Eloquent, HTTP or the database.

Dependencies point towards the business core. Infrastructure implements contracts defined by the inner layers.

## Why this project?

Laravel makes it easy to build applications quickly, but business logic can easily become coupled to controllers, Eloquent models, facades and framework services.

This project explores a different approach:

- Keep domain objects framework-independent.
- Express application behaviour through explicit use cases.
- Define persistence as a contract rather than an implementation detail.
- Keep controllers focused on translating HTTP input and output.
- Translate application exceptions into HTTP responses at the application boundary.
- Test business behaviour without requiring Laravel or a database.

The goal is not architectural purity for its own sake. The goal is to make important business logic easier to understand, test and change.

## Example request flow

Creating a customer follows this path:

```text
POST /api/customers
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
CustomerController
        │
        ▼
GetCustomerHandler
        │
        ▼
CustomerRepository
```

## Project structure

```text
src/app/
├── Application/
│   └── Customer/
│       ├── CreateCustomer/
│       └── GetCustomer/
│
├── Domain/
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
- request validation
- JSON responses
- HTTP error representation

Controllers deliberately contain very little business logic.

## API

### Create customer

```http
POST /api/customers
Content-Type: application/json
Accept: application/json
```

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

### Get customer

```http
GET /api/customers/{id}
Accept: application/json
```

Successful requests return:

```http
200 OK
```

Unknown customers return:

```http
404 Not Found
```

## Error handling

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

## Testing strategy

The project uses several levels of automated tests.

### Domain unit tests

Test entities and value objects without Laravel or a database.

Examples include:

- valid and invalid email addresses
- customer identifiers
- customer invariants

### Application unit tests

Use an in-memory repository implementation.

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

### Infrastructure tests

Verify the Eloquent repository implementation and persistence mapping.

### HTTP feature tests

Exercise the API boundary and verify scenarios such as:

- customer creation
- duplicate customers
- invalid requests
- customer retrieval
- missing customers

Run the complete suite with:

```bash
docker compose exec app php artisan test
```

## Technology

- PHP 8.4
- Laravel
- MySQL 8.4
- Eloquent ORM
- PHPUnit
- Docker
- Docker Compose
- Nginx

## Running locally

### Requirements

- Docker
- Docker Compose

Clone the repository:

```bash
git clone git@github.com:ruedaCid/php-clean-architecture-api.git
cd php-clean-architecture-api
```

Create the environment configuration:

```bash
cp src/.env.example src/.env
```

Start the containers:

```bash
docker compose up -d --build
```

Install dependencies if required:

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

Run the tests:

```bash
docker compose exec app php artisan test
```

The API is available locally at:

```text
http://localhost:8090
```

## Design decisions

### Why not use Eloquent models directly in the domain?

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

### Why validate email twice?

HTTP validation and domain validation solve different problems.

The HTTP layer rejects malformed requests early.

The domain guarantees that an invalid `Email` value object cannot exist regardless of whether it was created through HTTP, CLI, a queue, a test or another integration.

### Why use an in-memory repository in unit tests?

Application behaviour can be tested without booting Laravel or connecting to a database.

The same application code later receives an Eloquent implementation through dependency injection.

### Why is the project deliberately small?

The repository is an architectural example rather than a complete customer-management product.

Adding more CRUD endpoints would increase the amount of code without significantly improving the architectural demonstration.

## Principles demonstrated

- Clean Architecture
- Dependency Inversion
- Separation of Concerns
- Domain modelling
- Value Objects
- Repository Pattern
- Use Case / Application Service Pattern
- Dependency Injection
- Framework-independent business logic
- Automated testing at multiple architectural boundaries

## Author

**Ignacio Rodriguez**

Senior software engineering and development leader with a background in backend development, software architecture, APIs, systems integration and cloud-based platforms.

This repository is part of my technical portfolio and demonstrates an approach I use to separate business rules from frameworks and infrastructure.
