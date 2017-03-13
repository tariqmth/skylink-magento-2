<?php

namespace RetailExpress\SkyLink\Exceptions\Sales\Orders;

use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\Data\OrderInterface;

class NoGuestCustomerIdConfiguredException extends LocalizedException
{
    public static function newInstance()
    {
        return new self(__('No SkyLink Guest Customer ID has been configured.'));
    }
}
