<?php

namespace RetailExpress\SkyLink\Model\Sales\Payments;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Payment\Model\MethodInterface;
use RetailExpress\SkyLink\Api\ConfigInterface;
use RetailExpress\SkyLink\Api\Sales\Payments\SkyLinkPaymentMethodRepositoryInterface;
use RetailExpress\SkyLink\Sdk\Sales\Payments\PaymentMethod as SkyLinkPaymentMethod;
use RetailExpress\SkyLink\Sdk\Sales\Payments\PaymentMethodId as SkyLinkPaymentMethodId;
use RetailExpress\SkyLink\Sdk\Sales\Payments\PaymentMethodRepositoryFactory as BasePaymentMethodRepositoryFactory;

class SkyLinkPaymentMethodRepository implements SkyLinkPaymentMethodRepositoryInterface
{
    use SkyLinkPaymentMethodHelpers;

    private $config;

    private $basePaymentMethodRepositoryFactory;

    public function __construct(
        ConfigInterface $config,
        BasePaymentMethodRepositoryFactory $basePaymentMethodRepositoryFactory,
        ResourceConnection $resourceConnection
    ) {
        $this->config = $config;
        $this->basePaymentMethodRepositoryFactory = $basePaymentMethodRepositoryFactory;
        $this->connection = $resourceConnection->getConnection(ResourceConnection::DEFAULT_CONNECTION);
    }

    /**
     * {@inheritdoc}
     */
    public function getList()
    {
        /* @var \RetailExpress\SkyLink\Sdk\Sales\Payments\PaymentMethodRepository $basePaymentMethodRepository */
        $basePaymentMethodRepository = $this->basePaymentMethodRepositoryFactory->create();

        return $basePaymentMethodRepository->all($this->config->getSalesChannelId());
    }

    public function getById(SkyLinkPaymentMethodId $paymentMethodId)
    {
        return array_first(
            $this->getList(),
            function ($key, SkyLinkPaymentMethod $paymentMethod) use ($paymentMethodId) {
                return true === $paymentMethod->getId()->sameValueAs($paymentMethodId);
            },
            function () use ($paymentMethodId) {
                throw NoSuchEntityException::singleField('id', $paymentMethodId);
            }
        );
    }

    public function getSkyLinkPaymentMethodForMagentoPaymentMethod(MethodInterface $magentoPaymentMethod)
    {
        $paymentMethodIdString = $this->connection->fetchOne(
            $this->connection
                ->select()
                ->from($this->getPaymentMethodsTable(), 'skylink_payment_method_id')
                ->where('magento_payment_method_code = ?', $magentoPaymentMethod->getCode())
        );

        if (false === $paymentMethodIdString) {
            return null;
        }

        return $this->getById(new SkyLinkPaymentMethodId($paymentMethodIdString));
    }
}
