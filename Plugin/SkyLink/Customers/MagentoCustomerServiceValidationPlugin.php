<?php

namespace RetailExpress\SkyLink\Plugin\SkyLink\Customers;

use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\State\InputMismatchException;
use RetailExpress\SkyLink\Api\Customers\MagentoCustomerServiceInterface;
use RetailExpress\SkyLink\Api\Debugging\SkyLinkLoggerInterface;
use RetailExpress\SkyLink\Sdk\Customers\Customer as SkyLinkCustomer;

class MagentoCustomerServiceValidationPlugin
{
    const DUPLICATE_EMAIL_ERROR = 'A customer with the same email already exists in an associated website.';

    private $logger;

    public function __construct(SkyLinkLoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function aroundRegisterMagentoCustomer(
        MagentoCustomerServiceInterface $subject,
        callable $proceed,
        SkyLinkCustomer $skyLinkCustomer
    ) {
        try {

            /* @var \Magento\Customer\Api\Data\CustomerInterface $magentoCustomer */
            $magentoCustomer = $proceed($skyLinkCustomer);

            return $magentoCustomer;

        // Validation errors
        } catch (InputException $e) {
            $this->logger->error(__('Validation errors occured while saving a Magento Customer'), [
                'SkyLink Customer ID' => $skyLinkCustomer->getId(),
                'Error' => $e->getMessage(),
                'Validation Errors' => array_map(function (LocalizedException $e) {
                    return $e->getMessage();
                }, $e->getErrors()),
            ]);

            throw $e;

        // Typically caused becuase of a duplicated email
        } catch (InputMismatchException $e) {
            if (self::DUPLICATE_EMAIL_ERROR === $e->getRawMessage()) {
                $this->logger->error($e->getMessage(), [
                    'SkyLink Customer ID' => $skyLinkCustomer->getId(),
                    'SkyLink Email Address' => $skyLinkCustomer->getBillingContact()->getEmailAddress(),
                ]);
            }

            throw $e;
        }
    }
}
