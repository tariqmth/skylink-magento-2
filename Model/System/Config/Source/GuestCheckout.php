<?php

namespace RetailExpress\SkyLink\Model\System\Config\Source;

use Magento\Framework\Option\ArrayInterface;
use Magento\Config\Model\Config\Source\Yesno;
use RetailExpress\SkyLink\Api\Sales\Orders\ConfigInterface;
use RetailExpress\SkyLink\Exceptions\Sales\Orders\NoGuestCustomerIdConfiguredException;

class GuestCheckout extends Yesno implements ArrayInterface
{
    private $orderConfig;

    public function __construct(ConfigInterface $orderConfig)
    {
        $this->orderConfig = $orderConfig;
    }

    public function toOptionArray()
    {
        // If there's no Guest Customer ID
        if (false === $this->orderConfig->hasGuestCustomerId()) {
            return array_values(array_filter(
                parent::toOptionArray(),
                function ($option) {
                    return 0 === $option['value'];
                }
            ));
        }

        return parent::toOptionArray();
    }
}
