<?php

namespace RetailExpress\SkyLink\Api\Customers;

use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Validator\Exception as ValidatorException;

interface MagentoCustomerDataFakerInterface
{
    public function fakeCustomerFromValidationErrors(
        CustomerInterface $magentoCustomer,
        ValidatorException $validatorException
    );

    public function fakeAddressFromValidationErrors(AddressInterface $magentoAddress, InputException $inputException);
}
