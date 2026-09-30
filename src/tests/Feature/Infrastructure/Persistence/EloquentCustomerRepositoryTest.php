<?php

declare(strict_types=1);

namespace Tests\Feature\Infrastructure\Persistence;

use App\Domain\Customer\Customer;
use App\Domain\Customer\CustomerId;
use App\Domain\Customer\CustomerRepository;
use App\Domain\Customer\Email;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class EloquentCustomerRepositoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_saves_and_finds_a_customer_by_id(): void
    {
        $repository = app(CustomerRepository::class);

        $customer = new Customer(
            new CustomerId('customer-123'),
            'John Smith',
            new Email('john@example.com')
        );

        $repository->save($customer);

        $storedCustomer = $repository->findById(
            new CustomerId('customer-123')
        );

        self::assertNotNull($storedCustomer);
        self::assertSame('customer-123', $storedCustomer->id()->value());
        self::assertSame('John Smith', $storedCustomer->name());
        self::assertSame('john@example.com', $storedCustomer->email()->value());
    }

    public function test_it_finds_a_customer_by_email(): void
    {
        $repository = app(CustomerRepository::class);

        $repository->save(
            new Customer(
                new CustomerId('customer-456'),
                'Jane Smith',
                new Email('jane@example.com')
            )
        );

        $storedCustomer = $repository->findByEmail(
            new Email('jane@example.com')
        );

        self::assertNotNull($storedCustomer);
        self::assertSame('customer-456', $storedCustomer->id()->value());
    }

    public function test_it_returns_null_when_customer_does_not_exist(): void
    {
        $repository = app(CustomerRepository::class);

        self::assertNull(
            $repository->findById(
                new CustomerId('does-not-exist')
            )
        );
    }
}
