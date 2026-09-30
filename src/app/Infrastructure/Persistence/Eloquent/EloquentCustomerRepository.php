<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\Customer\Customer;
use App\Domain\Customer\CustomerId;
use App\Domain\Customer\CustomerRepository;
use App\Domain\Customer\Email;

final class EloquentCustomerRepository implements CustomerRepository
{
    public function save(Customer $customer): void
    {
        CustomerModel::query()->updateOrCreate(
            [
                'id' => $customer->id()->value(),
            ],
            [
                'name' => $customer->name(),
                'email' => $customer->email()->value(),
            ]
        );
    }

    public function findById(CustomerId $id): ?Customer
    {
        $model = CustomerModel::query()->find($id->value());

        return $model !== null
            ? $this->toDomain($model)
            : null;
    }

    public function findByEmail(Email $email): ?Customer
    {
        $model = CustomerModel::query()
            ->where('email', $email->value())
            ->first();

        return $model !== null
            ? $this->toDomain($model)
            : null;
    }

    private function toDomain(CustomerModel $model): Customer
    {
        return new Customer(
            new CustomerId((string) $model->id),
            (string) $model->name,
            new Email((string) $model->email)
        );
    }
}
