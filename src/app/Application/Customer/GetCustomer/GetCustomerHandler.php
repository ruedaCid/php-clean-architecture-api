<?php

declare(strict_types=1);

namespace App\Application\Customer\GetCustomer;

use App\Domain\Customer\Customer;
use App\Domain\Customer\CustomerId;
use App\Domain\Customer\CustomerRepository;

final readonly class GetCustomerHandler
{
    public function __construct(
        private CustomerRepository $customers
    ) {
    }

    public function handle(string $id): Customer
    {
        $customer = $this->customers->findById(
            new CustomerId($id)
        );

        if ($customer === null) {
            throw CustomerNotFound::withId($id);
        }

        return $customer;
    }
}
