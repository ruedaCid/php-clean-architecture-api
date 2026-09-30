<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Customer\GetCustomer;

use App\Application\Customer\GetCustomer\CustomerNotFound;
use App\Application\Customer\GetCustomer\GetCustomerHandler;
use App\Domain\Customer\Customer;
use App\Domain\Customer\CustomerId;
use App\Domain\Customer\Email;
use PHPUnit\Framework\TestCase;
use Tests\Support\InMemoryCustomerRepository;

final class GetCustomerHandlerTest extends TestCase
{
    public function test_it_returns_an_existing_customer(): void
    {
        $repository = new InMemoryCustomerRepository();

        $repository->save(
            new Customer(
                new CustomerId('customer-123'),
                'John Smith',
                new Email('john@example.com')
            )
        );

        $handler = new GetCustomerHandler($repository);

        $customer = $handler->handle('customer-123');

        self::assertSame('customer-123', $customer->id()->value());
        self::assertSame('John Smith', $customer->name());
        self::assertSame('john@example.com', $customer->email()->value());
    }

    public function test_it_throws_when_customer_does_not_exist(): void
    {
        $handler = new GetCustomerHandler(
            new InMemoryCustomerRepository()
        );

        $this->expectException(CustomerNotFound::class);
        $this->expectExceptionMessage(
            'Customer with ID "customer-999" was not found.'
        );

        $handler->handle('customer-999');
    }
}
