<?php

namespace RetailExpress\SkyLink\Api\Catalogue\Products;

use RetailExpress\SkyLink\Sdk\Catalogue\Attributes\AttributeOption as SkyLinkAttributeOption;
use RetailExpress\SkyLink\Sdk\Catalogue\Products\MatrixPolicy as SkyLinkMatrixPolicy;

interface SkyLinkMatrixPolicyServiceInterface
{
    /**
     * Defines the Matrix Policy used when SkyLink creates a new product in
     * Magento for the given SkyLink "product type".
     *
     * @param SkyLinkMatrixPolicy    $skyLinkMatrixPolicy
     * @param SkyLinkAttributeOption $skyLinkProductType
     */
    public function mapSkyLinkMatrixPolicyForSkyLinkProductType(
        SkyLinkMatrixPolicy $skyLinkMatrixPolicy,
        SkyLinkAttributeOption $skyLinkProductType
    );

    /**
     * Takes the given SkyLink Attribute Codes and filters the ones we allow to be used in a Matrix Policy.
     *
     * @param \RetailExpress\SkyLink\Sdk\Catalogue\Attributes\AttributeCode[] $skyLinkAttributeCodes
     *
     * @return \RetailExpress\SkyLink\Sdk\Catalogue\Attributes\AttributeCode[]
     */
    public function filterSkyLinkAttributeCodesForUseInSkyLinkMatrixPolicies(array $skyLinkAttributeCodes);
}
