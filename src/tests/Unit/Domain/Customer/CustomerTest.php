<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Customer;

use App\Domain\Customer\Customer;
use App\Domain\Customer\CustomerId;
use App\Domain\Customer\Email;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class CustomerTest extends TestCase
{
    public function test_it_creates_a_customer(): void
    {
        $customer = new Customer(
            new CustomerId('customer-123'),
            'John Smith',
            new Email('john@example.com')
        );

        self::assertSame('customer-123', $customer->id()->value());
        self::assertSame('John Smith', $customer->name());
        self::assertSame('john@example.com', $customer->email()->value());
    }

    public function test_it_rejects_an_empty_name(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Customer name cannot be empty.');

        new Customer(
            new CustomerId('customer-123'),
            '',
            new Email('john@example.com')
        );
    }
}
