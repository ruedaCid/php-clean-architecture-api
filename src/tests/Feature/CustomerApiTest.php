<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Customer\Customer;
use App\Domain\Customer\CustomerId;
use App\Domain\Customer\CustomerRepository;
use App\Domain\Customer\Email;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class CustomerApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_a_customer(): void
    {
        $response = $this->postJson('/api/customers', [
            'name' => 'John Smith',
            'email' => 'john@example.com',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('data.name', 'John Smith')
            ->assertJsonPath('data.email', 'john@example.com')
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'email',
                ],
            ]);

        $this->assertDatabaseHas('customers', [
            'name' => 'John Smith',
            'email' => 'john@example.com',
        ]);
    }

    public function test_it_returns_conflict_for_a_duplicated_email(): void
    {
        $repository = app(CustomerRepository::class);

        $repository->save(
            new Customer(
                new CustomerId('existing-customer'),
                'Existing Customer',
                new Email('john@example.com')
            )
        );

        $response = $this->postJson('/api/customers', [
            'name' => 'Another Customer',
            'email' => 'john@example.com',
        ]);

        $response
            ->assertConflict()
            ->assertJson([
                'message' => 'Customer with email "john@example.com" already exists.',
            ]);
    }

    public function test_it_rejects_invalid_input(): void
    {
        $response = $this->postJson('/api/customers', [
            'name' => '',
            'email' => 'invalid-email',
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'name',
                'email',
            ]);
    }

     public function test_it_returns_a_customer_by_id(): void
{
    $repository = app(CustomerRepository::class);

    $repository->save(
        new Customer(
            new CustomerId('customer-123'),
            'John Smith',
            new Email('john@example.com')
        )
    );

    $response = $this->getJson('/api/customers/customer-123');

    $response
        ->assertOk()
        ->assertJson([
            'data' => [
                'id' => 'customer-123',
                'name' => 'John Smith',
                'email' => 'john@example.com',
            ],
        ]);
}

public function test_it_returns_not_found_for_an_unknown_customer(): void
{
    $response = $this->getJson(
        '/api/customers/customer-999'
    );

    $response
        ->assertNotFound()
        ->assertJson([
            'message' => 'Customer with ID "customer-999" was not found.',
        ]);
}
}
