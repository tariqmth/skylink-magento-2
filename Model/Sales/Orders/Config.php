<?php

namespace RetailExpress\SkyLink\Model\Sales\Orders;

use Magento\Framework\App\Config\ScopeConfigInterface;
use RetailExpress\SkyLink\Api\Sales\Orders\ConfigInterface;
use RetailExpress\SkyLink\Exceptions\Sales\Orders\NoGuestCustomerIdConfiguredException;
use RetailExpress\SkyLink\Sdk\Customers\CustomerId as SkyLinkCustomerId;
use RetailExpress\SkyLink\Sdk\Sales\Orders\ItemFulfillmentMethod as SkyLinkItemFulfillmentMethod;

class Config implements ConfigInterface
{
    private $scopeConfig;

    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function hasGuestCustomerId()
    {
        try {
            $this->getGuestCustomerId();
        } catch (NoGuestCustomerIdConfiguredException $e) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getGuestCustomerId()
    {
        $value = $this->scopeConfig->getValue('checkout/options/skylink_guest_customer_id');

        if (!$value) {
            throw NoGuestCustomerIdConfiguredException::newInstance();
        }

        return new SkyLinkCustomerId($value);
    }

    /**
     * {@inheritdoc}
     */
    public function getItemFulfillmentMethod()
    {
        return SkyLinkItemFulfillmentMethod::get($this->scopeConfig->getValue('skylink/orders/item_delivery_method'));
    }
}
