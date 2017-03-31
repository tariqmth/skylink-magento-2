<?php

namespace RetailExpress\SkyLink\Exceptions\Sales\Payments;

use Magento\Framework\Exception\LocalizedException;
use RetailExpress\SkyLink\Sdk\Sales\Payments\PaymentId as SkyLinkPayemntId;

class SkyLinkPaymentAlreadyCreatedForMagentoInvoiceException extends LocalizedException
{
    public static function withSkyLinkPaymentIdAndMagentoInvoiceIncrementId(
        SkyLinkPayemntId $skyLinkPaymentId,
        $magentoInvoiceIncrementId
    ) {
        return new self(__(
            'A SkyLink Payment (Payment #%1) has already been created for Magento Invoice #%2',
            $skyLinkPaymentId,
            $magentoInvoiceIncrementId
        ));
    }
}
