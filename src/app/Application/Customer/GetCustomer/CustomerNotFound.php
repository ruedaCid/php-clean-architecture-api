<?php

declare(strict_types=1);

namespace App\Application\Customer\GetCustomer;

use RuntimeException;

final class CustomerNotFound extends RuntimeException
{
    public static function withId(string $id): self
    {
        return new self(
            sprintf('Customer with ID "%s" was not found.', $id)
        );
    }
}
