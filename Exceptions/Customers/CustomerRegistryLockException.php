<?php

namespace RetailExpress\SkyLink\Exceptions\Customers;

use Magento\Framework\Exception\LocalizedException;

class CustomerRegistryLockException extends LocalizedException
{
    public static function withMagentoCustomerId($customerId)
    {
        return new self(__(
            'Customer sync registry was locked when syncing Magento Customer with ID %1',
            $customerId
        ));
    }
}
