<?php

namespace RetailExpress\SkyLink\Model\Catalogue\Attributes;

use RetailExpress\SkyLink\Sdk\Catalogue\Attributes\AttributeCode;
use RetailExpress\SkyLink\Api\Catalogue\Attributes\SkyLinkProductTypeRepositoryInterface;
use RetailExpress\SkyLink\Sdk\Catalogue\Attributes\AttributeRepositoryFactory;

class SkyLinkProductTypeRepository implements SkyLinkProductTypeRepositoryInterface
{
    private $attributeRepositoryFactory;

    public function __construct(AttributeRepositoryFactory $attributeRepositoryFactory)
    {
        $this->attributeRepositoryFactory = $attributeRepositoryFactory;
    }

    /**
     * List all Product Types available.
     *
     * @return \RetailExpress\SkyLink\Catalogue\Attributes\AttributeOption[]
     */
    public function getList()
    {
        /* @var \RetailExpress\SkyLink\Sdk\Catalogue\Attributes\AttributeCode */
        $attributeCode = AttributeCode::get(AttributeCode::PRODUCT_TYPE);

        /* @var \RetailExpress\SkyLink\Sdk\Catalogue\Attributes\AttributeRepository */
        $attributeRepository = $this->attributeRepositoryFactory->create();

        $attribute = $attributeRepository->find($attributeCode);

        return $attribute->getOptions();
    }
}
