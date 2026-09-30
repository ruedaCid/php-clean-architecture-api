<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Customer\CreateCustomer;

use App\Application\Customer\CreateCustomer\CreateCustomerCommand;
use App\Application\Customer\CreateCustomer\CreateCustomerHandler;
use App\Application\Customer\CreateCustomer\CustomerAlreadyExists;
use App\Domain\Customer\Customer;
use App\Domain\Customer\CustomerId;
use App\Domain\Customer\Email;
use PHPUnit\Framework\TestCase;
use Tests\Support\InMemoryCustomerRepository;

final class CreateCustomerHandlerTest extends TestCase
{
    public function test_it_creates_a_customer(): void
    {
        $repository = new InMemoryCustomerRepository();
        $handler = new CreateCustomerHandler($repository);

        $customer = $handler->handle(
            new CreateCustomerCommand(
                'customer-123',
                'John Smith',
                'john@example.com'
            )
        );

        self::assertSame('customer-123', $customer->id()->value());
        self::assertSame('John Smith', $customer->name());
        self::assertSame('john@example.com', $customer->email()->value());

        self::assertNotNull(
            $repository->findById(
                new CustomerId('customer-123')
            )
        );
    }

    public function test_it_rejects_a_duplicated_email(): void
    {
        $repository = new InMemoryCustomerRepository();

        $repository->save(
            new Customer(
                new CustomerId('existing-customer'),
                'Existing Customer',
                new Email('john@example.com')
            )
        );

        $handler = new CreateCustomerHandler($repository);

        $this->expectException(CustomerAlreadyExists::class);
        $this->expectExceptionMessage(
            'Customer with email "john@example.com" already exists.'
        );

        $handler->handle(
            new CreateCustomerCommand(
                'customer-456',
                'Another Customer',
                'john@example.com'
            )
        );
    }
}
