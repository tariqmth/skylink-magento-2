<?php

namespace RetailExpress\SkyLink\Api\Customers;

interface ConfigInterface
{
    /**
     * Get the tax class id used for new customer groups.
     *
     * @return int
     */
    public function getCustomerGroupTaxClassId();

    /**
     * Get the Price Group Type to choose for Customer Groups.
     *
     * @return \RetailExpress\SkyLink\Sdk\Customers\PriceGroups\PriceGroupType
     */
    public function getSkyLinkPriceGroupType();

    /**
     * Gets the default Customer Group ID. Basically a wrapper for functionality contained in:
     *
     * Magento\Customer\Model::getGroupId()
     *
     * @return int
     */
    public function getDefaultCustomerGroupId();

    /**
     * Flag for whether fake data should be used.
     *
     * @return bool
     */
    public function shouldUseFakeData();

    /**
     * Get the fake data for "first name".
     *
     * @return \ValueObjects\StringLiteral\StringLiteral
     */
    public function getFakeDataFirstName();

    /**
     * Get the fake data for "last name".
     *
     * @return \ValueObjects\StringLiteral\StringLiteral
     */
    public function getFakeDataLastName();

    /**
     * Get the fake data for "street".
     *
     * @return \ValueObjects\StringLiteral\StringLiteral
     */
    public function getFakeDataStreet();

    /**
     * Get the fake data for "city".
     *
     * @return \ValueObjects\StringLiteral\StringLiteral
     */
    public function getFakeDataCity();

    /**
     * Get the fake data for "postcode".
     *
     * @return \ValueObjects\StringLiteral\StringLiteral
     */
    public function getFakeDataPostcode();

    /**
     * Get the fake data for "country code".
     *
     * @return \ValueObjects\Geography\CountryCode
     */
    public function getFakeDataCountryCode();

    /**
     * Get the fake data for "telephone".
     *
     * @return \ValueObjects\StringLiteral\StringLiteral
     */
    public function getFakeDataTelephone();
}
