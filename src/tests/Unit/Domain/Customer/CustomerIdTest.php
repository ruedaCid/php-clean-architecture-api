<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Customer;

use App\Domain\Customer\CustomerId;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class CustomerIdTest extends TestCase
{
    public function test_it_accepts_a_valid_customer_id(): void
    {
        $id = new CustomerId('customer-123');

        self::assertSame('customer-123', $id->value());
    }

    public function test_it_rejects_an_empty_customer_id(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Customer ID cannot be empty.');

        new CustomerId('');
    }

    public function test_it_rejects_a_whitespace_only_customer_id(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new CustomerId('   ');
    }
}
