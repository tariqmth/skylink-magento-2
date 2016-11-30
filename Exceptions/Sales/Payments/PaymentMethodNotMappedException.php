<?php

namespace RetailExpress\SkyLink\Exceptions\Sales\Payments;

use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Model\MethodInterface;

class PaymentMethodNotMappedException extends LocalizedException
{
    public static function withMagentoPaymentMethod(MethodInterface $magentoPaymentMethod)
    {
        return new self(__(
            'Magento Payment Method "%s" has not been mapped to a Retail Express Payment Method. Please re-configure mappings.',
            $magentoPaymentMethod->getCode()
        ));
    }
}
