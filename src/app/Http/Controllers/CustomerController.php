<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Application\Customer\CreateCustomer\CreateCustomerCommand;
use App\Application\Customer\CreateCustomer\CreateCustomerHandler;
use App\Application\Customer\CreateCustomer\CustomerAlreadyExists;
use App\Http\Requests\CreateCustomerRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use App\Application\Customer\GetCustomer\GetCustomerHandler;

final class CustomerController extends Controller
{
    public function store(
        CreateCustomerRequest $request,
        CreateCustomerHandler $handler
    ): JsonResponse {
        try {
            $customer = $handler->handle(
                new CreateCustomerCommand(
                    (string) Str::uuid(),
                    $request->string('name')->toString(),
                    $request->string('email')->toString()
                )
            );
        } catch (CustomerAlreadyExists $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 409);
        }

        return response()->json([
            'data' => [
                'id' => $customer->id()->value(),
                'name' => $customer->name(),
                'email' => $customer->email()->value(),
            ],
        ], 201);
    }
public function show(
    string $id,
    GetCustomerHandler $handler
): JsonResponse {
    $customer = $handler->handle($id);

    return response()->json([
        'data' => [
            'id' => $customer->id()->value(),
            'name' => $customer->name(),
            'email' => $customer->email()->value(),
        ],
    ]);
}
}
