<?php

namespace RetailExpress\SkyLink\Model\Sales\Payments;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Payment\Helper\Data as PaymentHelper;
use Magento\Payment\Model\MethodInterface;
use RetailExpress\SkyLink\Api\Sales\Payments\MagentoPaymentMethodRepositoryInterface;

class MagentoPaymentMethodRepository implements MagentoPaymentMethodRepositoryInterface
{
    private $paymentHelper;

    public function __construct(PaymentHelper $paymentHelper)
    {
        $this->paymentHelper = $paymentHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function getList()
    {
        return $this->paymentHelper->getStoreMethods();
    }

    /**
     * {@inheritdoc}
     */
    public function get($magentoPaymentMethodCode)
    {
        return array_first(
            $this->getList(), function (MethodInterface $method) use ($magentoPaymentMethodCode) {
                return $method->getCode() === $magentoPaymentMethodCode;
            },
            function () use ($magentoPaymentMethodCode) {
                throw NoSuchEntityException::singleField('code', $magentoPaymentMethodCode);
            }
        );
    }
}
