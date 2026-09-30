<?php

declare(strict_types=1);

namespace Tests\Support;

use App\Domain\Customer\Customer;
use App\Domain\Customer\CustomerId;
use App\Domain\Customer\CustomerRepository;
use App\Domain\Customer\Email;

final class InMemoryCustomerRepository implements CustomerRepository
{
    /** @var array<string, Customer> */
    private array $customers = [];

    public function save(Customer $customer): void
    {
        $this->customers[$customer->id()->value()] = $customer;
    }

    public function findById(CustomerId $id): ?Customer
    {
        return $this->customers[$id->value()] ?? null;
    }

    public function findByEmail(Email $email): ?Customer
    {
        foreach ($this->customers as $customer) {
            if ($customer->email()->value() === $email->value()) {
                return $customer;
            }
        }

        return null;
    }
}
