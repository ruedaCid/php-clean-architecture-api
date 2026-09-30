<?php

declare(strict_types=1);

namespace App\Application\Customer\CreateCustomer;

final readonly class CreateCustomerCommand
{
    public function __construct(
        public string $id,
        public string $name,
        public string $email,
    ) {
    }
}
