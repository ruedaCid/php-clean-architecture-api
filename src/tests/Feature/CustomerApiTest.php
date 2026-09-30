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

    private function asRole(string $role): self
    {
        $this->withHeader('X-Role', $role);

        return $this;
    }

    public function test_it_creates_a_customer(): void
    {
        $response = $this->asRole('manager')
            ->postJson('/api/customers', [
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
            new CustomerId('customer-existing'),
            'Existing Customer',
            new Email('john@example.com')
        )
    );

    $response = $this->asRole('manager')
        ->postJson('/api/customers', [
            'name' => 'John Smith',
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
        $response = $this->asRole('manager')
            ->postJson('/api/customers', [
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

        $response = $this->asRole('viewer')
            ->getJson('/api/customers/customer-123');

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
        $response = $this->asRole('viewer')
            ->getJson('/api/customers/customer-999');

        $response
            ->assertNotFound()
            ->assertJson([
                'message' => 'Customer with ID "customer-999" was not found.',
            ]);
    }

    public function test_manager_can_create_customers(): void
    {
        $response = $this->asRole('manager')
            ->postJson('/api/customers', [
                'name' => 'Manager Customer',
                'email' => 'manager@example.com',
            ]);

        $response->assertCreated();

        $this->assertDatabaseHas('customers', [
            'name' => 'Manager Customer',
            'email' => 'manager@example.com',
        ]);
    }

    public function test_viewer_cannot_create_customers(): void
    {
        $response = $this->asRole('viewer')
            ->postJson('/api/customers', [
                'name' => 'Viewer Customer',
                'email' => 'viewer@example.com',
            ]);

        $response
            ->assertForbidden()
            ->assertJson([
                'message' => 'You are not authorized to perform this action.',
            ]);

        $this->assertDatabaseMissing('customers', [
            'email' => 'viewer@example.com',
        ]);
    }

    public function test_viewer_can_read_customers(): void
    {
        $repository = app(CustomerRepository::class);

        $repository->save(
            new Customer(
                new CustomerId('viewer-readable-customer'),
                'Readable Customer',
                new Email('readable@example.com')
            )
        );

        $response = $this->asRole('viewer')
            ->getJson('/api/customers/viewer-readable-customer');

        $response
            ->assertOk()
            ->assertJsonPath(
                'data.email',
                'readable@example.com'
            );
    }

    public function test_request_without_role_is_unauthorized(): void
    {
        $response = $this->postJson('/api/customers', [
            'name' => 'Anonymous Customer',
            'email' => 'anonymous@example.com',
        ]);

        $response
            ->assertUnauthorized()
            ->assertJson([
                'message' => 'A valid role is required.',
            ]);

        $this->assertDatabaseMissing('customers', [
            'email' => 'anonymous@example.com',
        ]);
    }

    public function test_admin_can_create_customers(): void
    {
        $response = $this->asRole('admin')
            ->postJson('/api/customers', [
                'name' => 'Admin Customer',
                'email' => 'admin@example.com',
            ]);

        $response->assertCreated();

        $this->assertDatabaseHas('customers', [
            'name' => 'Admin Customer',
            'email' => 'admin@example.com',
        ]);
    }
}
