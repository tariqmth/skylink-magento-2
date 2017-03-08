<?php

namespace RetailExpress\SkyLink\Plugin\SkyLink\Customers;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\State\InputMismatchException;
use Magento\Framework\Validator\Exception as ValidatorException;
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
        return $this->handelValidationErrors(
            function () use ($proceed, $skyLinkCustomer) {
                return $proceed($skyLinkCustomer);
            },
            $skyLinkCustomer
        );
    }

    public function aroundUpdateMagentoCustomer(
        MagentoCustomerServiceInterface $subject,
        callable $proceed,
        CustomerInterface $magentoCustomer,
        SkyLinkCustomer $skyLinkCustomer
    ) {
        return $this->handelValidationErrors(
            function () use ($proceed, $magentoCustomer, $skyLinkCustomer) {
                return $proceed($magentoCustomer, $skyLinkCustomer);
            },
            $skyLinkCustomer
        );
    }

    private function handelValidationErrors(
        callable $callback,
        SkyLinkCustomer $skyLinkCustomer,
        CustomerInterface $magentoCustomer = null
    ) {
        $payload = ['SkyLink Customer ID' => $skyLinkCustomer->getId()];
        if (null !== $magentoCustomer) {
            $payload['Magento Customer ID'] = $magentoCustomer->getId();
        }

        try {
            return $callback();

        // Specific customer errors
        } catch (InputException $e) {
            $payload['Error'] = $e->getMessage();
            $payload['Validation Errors'] = array_map(function (LocalizedException $e) {
                return $e->getMessage();
            }, $e->getErrors());

            $this->addValidationError($payload);

            throw $e;

        // Generic validation errors
        } catch (ValidatorException $e) {
            $payload['Error'] = $e->getMessage();

            $this->addValidationError($payload);

            throw $e;

        // Typically caused becuase of a duplicated email
        } catch (InputMismatchException $e) {
            if (self::DUPLICATE_EMAIL_ERROR === $e->getRawMessage()) {
                $payload['SkyLink Email Address'] = $skyLinkCustomer->getBillingContact()->getEmailAddress();

                $this->logger->error($e->getMessage(), $payload);
            }

            throw $e;
        }
    }

    private function addValidationError(array $payload)
    {
        $this->logger->error(__('Validation errors occured while saving a Magento Customer'), $payload);
    }
}
