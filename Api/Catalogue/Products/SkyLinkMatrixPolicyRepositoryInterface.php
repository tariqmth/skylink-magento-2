<?php

namespace RetailExpress\SkyLink\Api\Catalogue\Products;

use RetailExpress\SkyLink\Sdk\Catalogue\Attributes\AttributeOption as SkyLinkAttributeOption;

interface SkyLinkMatrixPolicyRepositoryInterface
{
    /**
     * Get the Matrix Policy used for the given product type. If there is no
     * mapping defined, the default Matrix Policy is returned.
     *
     * @param SkyLinkAttributeOption $skyLinkProductType
     *
     * @return \RetailExpress\SkyLink\Sdk\Catalogue\Products\MatrixPolicy
     */
    public function getMatrixPolicyForProductType(SkyLinkAttributeOption $skyLinkProductType);
}
