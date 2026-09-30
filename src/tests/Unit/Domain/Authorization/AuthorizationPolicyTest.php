<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Authorization;

use App\Domain\Authorization\AuthorizationPolicy;
use App\Domain\Authorization\Permission;
use App\Domain\Authorization\Role;
use PHPUnit\Framework\TestCase;

final class AuthorizationPolicyTest extends TestCase
{
    public function test_admin_has_all_permissions(): void
    {
        $policy = new AuthorizationPolicy();

        self::assertTrue(
            $policy->allows(
                Role::ADMIN,
                Permission::CUSTOMER_CREATE
            )
        );

        self::assertTrue(
            $policy->allows(
                Role::ADMIN,
                Permission::CUSTOMER_READ
            )
        );
    }

    public function test_manager_can_create_and_read_customers(): void
    {
        $policy = new AuthorizationPolicy();

        self::assertTrue(
            $policy->allows(
                Role::MANAGER,
                Permission::CUSTOMER_CREATE
            )
        );

        self::assertTrue(
            $policy->allows(
                Role::MANAGER,
                Permission::CUSTOMER_READ
            )
        );
    }

    public function test_viewer_can_read_customers(): void
    {
        $policy = new AuthorizationPolicy();

        self::assertTrue(
            $policy->allows(
                Role::VIEWER,
                Permission::CUSTOMER_READ
            )
        );
    }

    public function test_viewer_cannot_create_customers(): void
    {
        $policy = new AuthorizationPolicy();

        self::assertFalse(
            $policy->allows(
                Role::VIEWER,
                Permission::CUSTOMER_CREATE
            )
        );
    }
}
