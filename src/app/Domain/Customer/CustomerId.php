<?php

declare(strict_types=1);

namespace App\Domain\Customer;

use InvalidArgumentException;

final readonly class CustomerId
{
    public function __construct(
        private string $value
    ) {
        if (trim($value) === '') {
            throw new InvalidArgumentException('Customer ID cannot be empty.');
        }
    }

    public function value(): string
    {
        return $this->value;
    }
}
