<?php

namespace RetailExpress\SkyLink\Model\Sales\Payments;

use Magento\Framework\App\ResourceConnection;
use Magento\Payment\Model\MethodInterface;
use RetailExpress\SkyLink\Api\Sales\Payments\SkyLinkPaymentMethodServiceInterface;
use RetailExpress\SkyLink\Sdk\Sales\Payments\PaymentMethod as SkyLinkPaymentMethod;

class SkyLinkPaymentMethodService implements SkyLinkPaymentMethodServiceInterface
{
    use SkyLinkPaymentMethodHelpers;

    /**
     * Create a new SkyLink Payment Method Service.
     *
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(ResourceConnection $resourceConnection)
    {
        $this->connection = $resourceConnection->getConnection(ResourceConnection::DEFAULT_CONNECTION);
    }

    /**
     * {@inheritdoc}
     */
    public function mapSkyLinkPaymentMethodForMagentoPaymentMethod(
        SkyLinkPaymentMethod $skyLinkPaymentMethod,
        MethodInterface $magentoPaymentMethod
    ) {
        if ($this->mappingExists($magentoPaymentMethod)) {
            $this->connection->update(
                $this->getPaymentMethodsTable(),
                [
                    'skylink_payment_method_id' => $skyLinkPaymentMethod->getId(),
                ],
                [
                    'magento_payment_method_code = ?' => $magentoPaymentMethod->getCode(),
                ]
            );
        } else {
            $this->connection->insert(
                $this->getPaymentMethodsTable(),
                [
                    'magento_payment_method_code' => $magentoPaymentMethod->getCode(),
                    'skylink_payment_method_id' => $skyLinkPaymentMethod->getId(),
                ]
            );
        }
    }

    private function mappingExists(MethodInterface $magentoPaymentMethod)
    {
        return (bool) $this->connection->fetchOne(
            $this->connection
                ->select()
                ->from($this->getPaymentMethodsTable(), 'count(magento_payment_method_code)')
                ->where('magento_payment_method_code = ?', $magentoPaymentMethod->getCode())
        );
    }
}
