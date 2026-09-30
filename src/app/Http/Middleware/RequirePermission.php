<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Domain\Authorization\AuthorizationPolicy;
use App\Domain\Authorization\Permission;
use App\Domain\Authorization\Role;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class RequirePermission
{
    public function __construct(
        private readonly AuthorizationPolicy $policy
    ) {
    }

    public function handle(
        Request $request,
        Closure $next,
        string $permission
    ): Response {
        $role = Role::tryFrom(
            (string) $request->header('X-Role')
        );

        if ($role === null) {
            return new JsonResponse([
                'message' => 'A valid role is required.',
            ], 401);
        }

        $requiredPermission = Permission::tryFrom($permission);

        if (
            $requiredPermission === null ||
            !$this->policy->allows($role, $requiredPermission)
        ) {
            return new JsonResponse([
                'message' => 'You are not authorized to perform this action.',
            ], 403);
        }

        return $next($request);
    }
}
