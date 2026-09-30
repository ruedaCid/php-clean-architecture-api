<?php

declare(strict_types=1);

namespace App\Domain\Customer;

interface CustomerRepository
{
    public function save(Customer $customer): void;

    public function findById(CustomerId $id): ?Customer;

    public function findByEmail(Email $email): ?Customer;
}
