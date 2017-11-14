<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace RetailExpress\Skylink\Model\Sales\Payments;

/**
 * Class EbayPaymentMethod
 *
 * @method \Magento\Quote\Api\Data\PaymentMethodExtensionInterface getExtensionAttributes()
 */
class EbayPaymentMethod extends \Magento\Payment\Model\Method\AbstractMethod
{
    const PAYMENT_METHOD_EBAY_CODE = 'ebay';

    /**
     * Payment method code
     *
     * @var string
     */
    protected $_code = self::PAYMENT_METHOD_EBAY_CODE;

    /**
     * Availability option
     *
     * @var bool
     */
    protected $_isOffline = true;
}
