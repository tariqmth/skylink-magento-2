<?php

namespace RetailExpress\SkyLink\Plugin\Magento\Customers;

use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Framework\Exception\InputException;
use RetailExpress\SkyLink\Api\Customers\ConfigInterface;
use RetailExpress\SkyLink\Api\Customers\MagentoCustomerDataFakerInterface;

class AddressRepositoryDataFakerPlugin
{
    private $customerConfig;

    private $magentoCustomerDataFaker;

    public function __construct(
        ConfigInterface $customerConfig,
        MagentoCustomerDataFakerInterface $magentoCustomerDataFaker
    ) {
        $this->customerConfig = $customerConfig;
        $this->magentoCustomerDataFaker = $magentoCustomerDataFaker;
    }

    public function aroundSave(
        AddressRepositoryInterface $subject,
        callable $proceed,
        AddressInterface $magentoAddress
    ) {
        // If we don't need to fake any data, let's just leave it to the gods
        if (false === $this->customerConfig->shouldUseFakeData()) {
            return $proceed($magentoAddress);
        }

        try {
            return $proceed($magentoAddress);
        } catch (InputException $e) {
            $magentoAddress = $this->magentoCustomerDataFaker->fakeAddressFromValidationErrors($magentoAddress, $e);
        }

        return $proceed($magentoAddress);
    }
}
