<?php

declare(strict_types=1);

namespace App\Domain\Authorization;

enum Role: string
{
    case ADMIN = 'admin';
    case MANAGER = 'manager';
    case VIEWER = 'viewer';
}
