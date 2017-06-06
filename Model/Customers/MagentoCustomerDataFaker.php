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

                // The default group is assigned by an observer in the customer model, so we don't need to fake
                // data here. We can't just change the priority of this plugin either, because it's an
                // actual observer that does it, so we can't really help where we intercept. Meh!
                case 'Group':
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

                case 'postcode':
                    $magentoAddress->setPostcode((string) $this->customerConfig->getFakeDataPostcode());
                    break;

                case 'countryId':
                    $magentoAddress->setCountryId((string) $this->customerConfig->getFakeDataCountryCode());
                    break;

                case 'telephone':
                    $magentoAddress->setTelephone((string) $this->customerConfig->getFakeDataTelephone());
                    break;

                case 'regionId':
                    throw new InvalidArgumentException('Due to the way Magento uses a either dropdown or a freeform text for states and that the state is required for this Address (this is configurable in Magento), we cannot provide a fake state as it would be change depending on the country this Address uses. Please add a state for the Customer in Retail Express and try syncing again.');

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
