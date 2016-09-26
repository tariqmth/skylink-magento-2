<?php

namespace RetailExpress\SkyLink\Magento2\Model\Products;

use RetailExpress\SkyLink\Catalogue\Attributes\AttributeCode;
use RetailExpress\SkyLink\Catalogue\Attributes\AttributeRepository;
use RetailExpress\SkyLink\Magento2\Api\Products\SkyLinkProductTypeRepositoryInterface;
use RetailExpress\SkyLink\ValueObjects\SalesChannelId;

class SkyLinkProductTypeRepository implements SkyLinkProductTypeRepositoryInterface
{
    private $attributeRepository;

    private $salesChannelId;

    public function __construct(
        AttributeRepository $attributeRepository,
        SalesChannelId $salesChannelId
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->salesChannelId = $salesChannelId;
    }

    /**
     * List all Product Types available.
     *
     * @return \RetailExpress\SkyLink\Catalogue\Attributes\AttributeOption[]
     */
    public function getList()
    {
        /** @var \RetailExpress\SkyLink\Catalogue\Attributes\AttributeCode */
        $attributeCode = AttributeCode::get(AttributeCode::PRODUCT_TYPE);

        $attribute = $this->attributeRepository->find(
            $attributeCode,
            $this->salesChannelId
        );

        return $attribute->getOptions();
    }
}
