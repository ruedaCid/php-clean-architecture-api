<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Customer;

use App\Domain\Customer\Email;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class EmailTest extends TestCase
{
    public function test_it_accepts_a_valid_email(): void
    {
        $email = new Email('ignacio@example.com');

        self::assertSame(
            'ignacio@example.com',
            $email->value()
        );
    }

    public function test_it_rejects_an_invalid_email(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid email address.');

        new Email('invalid-email');
    }
}
