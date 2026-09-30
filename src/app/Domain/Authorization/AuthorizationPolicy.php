<?php

declare(strict_types=1);

namespace App\Domain\Authorization;

final class AuthorizationPolicy
{
    public function allows(Role $role, Permission $permission): bool
    {
        return match ($role) {
            Role::ADMIN => true,

            Role::MANAGER => in_array(
                $permission,
                [
                    Permission::CUSTOMER_CREATE,
                    Permission::CUSTOMER_READ,
                ],
                true
            ),

            Role::VIEWER => $permission === Permission::CUSTOMER_READ,
        };
    }
}
