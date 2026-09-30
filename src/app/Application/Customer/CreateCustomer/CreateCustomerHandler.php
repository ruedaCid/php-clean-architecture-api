<?php

declare(strict_types=1);

namespace App\Application\Customer\CreateCustomer;

use App\Domain\Customer\Customer;
use App\Domain\Customer\CustomerId;
use App\Domain\Customer\CustomerRepository;
use App\Domain\Customer\Email;

final readonly class CreateCustomerHandler
{
    public function __construct(
        private CustomerRepository $customers
    ) {
    }

    public function handle(CreateCustomerCommand $command): Customer
    {
        $email = new Email($command->email);

        if ($this->customers->findByEmail($email) !== null) {
            throw CustomerAlreadyExists::withEmail($email->value());
        }

        $customer = new Customer(
            new CustomerId($command->id),
            $command->name,
            $email
        );

        $this->customers->save($customer);

        return $customer;
    }
}
