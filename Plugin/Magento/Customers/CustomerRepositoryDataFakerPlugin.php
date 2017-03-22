<?php

namespace RetailExpress\SkyLink\Plugin\Magento\Customers;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\Api\ExtensibleDataObjectConverter;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Validator\Exception as ValidatorException;
use Magento\Framework\Validator\Factory as ValidatorFactory;
use RetailExpress\SkyLink\Api\Customers\ConfigInterface;
use RetailExpress\SkyLink\Api\Customers\MagentoCustomerDataFakerInterface;

class CustomerRepositoryDataFakerPlugin
{
    private $customerConfig;

    private $magentoCustomerDataFaker;

    private $magentoCustomerFactory;

    /**
     * @var \Magento\Framework\Api\ExtensibleDataObjectConverter
     */
    protected $extensibleDataObjectConverter;

    /**
     * @var \Magento\Framework\Validator\Factory
     */
    protected $_validatorFactory;

    public function __construct(
        ConfigInterface $customerConfig,
        MagentoCustomerDataFakerInterface $magentoCustomerDataFaker,
        CustomerFactory $magentoCustomerFactory,
        ExtensibleDataObjectConverter $extensibleDataObjectConverter,
        ValidatorFactory $validatorFactory
    ) {
        $this->customerConfig = $customerConfig;
        $this->magentoCustomerDataFaker = $magentoCustomerDataFaker;
        $this->magentoCustomerFactory = $magentoCustomerFactory;
        $this->extensibleDataObjectConverter = $extensibleDataObjectConverter;
        $this->validatorFactory = $validatorFactory;
    }

    public function aroundSave(
        CustomerRepositoryInterface $subject,
        callable $proceed,
        CustomerInterface $magentoCustomer
    ) {
        // If we don't need to fake any data, let's just leave it to the gods
        if (false === $this->customerConfig->shouldUseFakeData()) {
            return $proceed($magentoCustomer);
        }

        // We'll run the same validation against the customer that Magento does and fake data for any missing validation
        try {
            $this->validate($magentoCustomer);
        } catch (ValidatorException $e) {
            $magentoCustomer = $this->magentoCustomerDataFaker->fakeCustomerFromValidationErrors($magentoCustomer, $e);
        }

        return $proceed($magentoCustomer);
    }

    /**
     * @see \Magento\Customer\Model\CustomerRepository::save()
     * @see \Magento\Customer\Model\Customer::_validate()
     */
    private function validate(CustomerInterface $magentoCustomer)
    {
        $magentoCustomerData = $this->extensibleDataObjectConverter->toNestedArray(
            $magentoCustomer,
            [],
            CustomerInterface::class
        );

        $magentoCustomerModel = $this->magentoCustomerFactory->create(['data' => $magentoCustomerData]);

        $magentoCustomerValidator = $this->validatorFactory->createValidator('customer', 'save');

        if (!$magentoCustomerValidator->isValid($magentoCustomerModel)) {
            throw new ValidatorException(null, null, $magentoCustomerValidator->getMessages());
        }
    }
}
