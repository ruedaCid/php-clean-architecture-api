<?php

declare(strict_types=1);

namespace App\Domain\Customer;

use InvalidArgumentException;

final class Customer
{
    public function __construct(
        private CustomerId $id,
        private string $name,
        private Email $email,
    ) {
        if (trim($name) === '') {
            throw new InvalidArgumentException('Customer name cannot be empty.');
        }
    }

    public function id(): CustomerId
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function email(): Email
    {
        return $this->email;
    }
}
