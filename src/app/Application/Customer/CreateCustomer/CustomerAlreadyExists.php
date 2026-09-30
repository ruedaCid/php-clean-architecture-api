<?php

declare(strict_types=1);

namespace App\Application\Customer\CreateCustomer;

use RuntimeException;

final class CustomerAlreadyExists extends RuntimeException
{
    public static function withEmail(string $email): self
    {
        return new self(
            sprintf('Customer with email "%s" already exists.', $email)
        );
    }
}
