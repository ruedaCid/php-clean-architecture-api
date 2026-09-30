<?php

declare(strict_types=1);

namespace App\Domain\Authorization;

enum Permission: string
{
    case CUSTOMER_CREATE = 'customer.create';
    case CUSTOMER_READ = 'customer.read';
}
