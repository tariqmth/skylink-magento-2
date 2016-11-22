<?php

namespace RetailExpress\SkyLink\Model\Sales\Orders;

use Magento\Framework\App\Config\ScopeConfigInterface;
use RetailExpress\SkyLink\Api\Sales\Orders\SkyLinkGuestCustomerServiceInterface;
use RetailExpress\SkyLink\Sdk\Customers\CustomerId as SkyLinkCustomerId;

class SkyLinkGuestCustomerService implements SkyLinkGuestCustomerServiceInterface
{
    private $scopeConfig;

    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function getGuestCustomerId()
    {
        return new SkyLinkCustomerId($this->scopeConfig->getValue('skylink/products/guest_customer_id'));
    }
}
