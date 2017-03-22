<?php

namespace RetailExpress\SkyLink\Model\Customers;

use InvalidArgumentException;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\AbstractMessage;
use Magento\Framework\Validator\Exception as ValidatorException;
use ReflectionClass;
use RetailExpress\SkyLink\Api\Customers\ConfigInterface;
use RetailExpress\SkyLink\Api\Customers\MagentoCustomerDataFakerInterface;

class MagentoCustomerDataFaker implements MagentoCustomerDataFakerInterface
{
    private $customerConfig;

    public function __construct(ConfigInterface $customerConfig)
    {
        $this->customerConfig = $customerConfig;
    }

    public function fakeCustomerFromValidationErrors(
        CustomerInterface $magentoCustomer,
        ValidatorException $validatorException
    ) {
        $failedFields = $this->extractFailedFieldsFromValidatorException($validatorException);

        array_walk($failedFields, function ($failedField) use ($magentoCustomer) {
            switch ($failedField) {
                case 'Last Name':
                    $magentoCustomer->setLastname((string) $this->customerConfig->getFakeDataLastName());
                    break;

                default:
                    throw new InvalidArgumentException("No fake data exists for Magento Customer field \"{$failedField}\".");
            }
        });

        return $magentoCustomer;
    }

    public function fakeAddressFromValidationErrors(AddressInterface $magentoAddress, InputException $inputException)
    {
        $failedFields = $this->extractFailedFieldsFromInputException($inputException);

        array_walk($failedFields, function ($failedField) use ($magentoAddress) {
            switch ($failedField) {
                case 'firstname':
                    $magentoAddress->setFirstname((string) $this->customerConfig->getFakeDataFirstName());
                    break;

                case 'lastname':
                    $magentoAddress->setLastname((string) $this->customerConfig->getFakeDataLastName());
                    break;

                case 'street':
                    $magentoAddress->setStreet([(string) $this->customerConfig->getFakeDataStreet()]);
                    break;

                case 'city':
                    $magentoAddress->setCity((string) $this->customerConfig->getFakeDataCity());
                    break;

                case 'telephone':
                    $magentoAddress->setTelephone((string) $this->customerConfig->getFakeDataTelephone());
                    break;

                case 'countryId':
                    $magentoAddress->setCountryId((string) $this->customerConfig->getFakeDataCountryCode());
                    break;

                default:
                    throw new InvalidArgumentException("No fake data exists for Magento Address field \"{$failedField}\".");
            }
        });

        return $magentoAddress;
    }

    private function extractFailedFieldsFromValidatorException(ValidatorException $validatorException)
    {
        $fields = array_map(function (AbstractMessage $message) {

            $reflectedMessage = new ReflectionClass($message);
            $reflectedText = $reflectedMessage->getProperty('text');
            $reflectedText->setAccessible(true);

            $text = $reflectedText->getValue($message);

            return (string) current($text->getArguments());
        }, $validatorException->getMessages());

        // There can be multiple validation errors on the same field, so we'll just take the unique ones
        return array_values(array_unique($fields));
    }

    private function extractFailedFieldsFromInputException(InputException $inputException)
    {
        // Additional errors
        $fields = array_map(function (LocalizedException $error) {
            return current($error->getParameters());
        }, $inputException->getErrors());

        // First error
        if (count($inputException->getParameters())) {
            array_unshift($fields, current($inputException->getParameters()));
        }

        return $fields;
    }
}
