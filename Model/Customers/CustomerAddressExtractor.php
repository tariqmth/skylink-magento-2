<?php

namespace RetailExpress\SkyLink\Model\Customers;

use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\AddressFactory;

class CustomerAddressExtractor
{
    private $addressFactory;

    public function __construct(AddressFactory $addressFactory)
    {
        $this->addressFactory = $addressFactory;
    }

    public function extract(CustomerInterface $customer)
    {
        $billingAddress = null;
        $shippingAddress = null;

        $addresses = $customer->getAddresses();

        // Loop through addresses and check if they are the default billing or shipping addresses
        array_walk($addresses, function (AddressInterface $address) use (&$billingAddress, &$shippingAddress) {
            if ($address->isDefaultBilling()) {
                $billingAddress = $address;

            // Prevent the same address being both billing and shipping
            } if ($address->isDefaultShipping()) {
                $shippingAddress = $address;
            }
        });

        if (null === $billingAddress) {
            $billingAddress = $this->createNewBillingAddress();
        }

        if (null === $shippingAddress) {
            $shippingAddress = $this->createNewShippingAddress();
        }

        return [$billingAddress, $shippingAddress];
    }

    /**
     * Creates a new Billing Address instance.
     *
     * @return AddressInterface
     */
    private function createNewBillingAddress()
    {
        $billingAddress = $this->addressFactory->create();

        return $billingAddress->setIsDefaultBilling(true);
    }

    /**
     * Creates a new Shipping Address instance.
     *
     * @return AddressInterface
     */
    private function createNewShippingAddress()
    {
        $shippingAddress = $this->addressFactory->create();

        return $shippingAddress->setIsDefaultShipping(true);
    }
}
