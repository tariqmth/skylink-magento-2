<?php

namespace RetailExpress\SkyLink\Model\Products;

use RetailExpress\SkyLink\Sdk\Catalogue\Attributes\AttributeCode;
use RetailExpress\SkyLink\Sdk\Catalogue\Attributes\AttributeRepository;
use RetailExpress\SkyLink\ValueObjects\SalesChannelId;
use RetailExpress\SkyLink\Api\ConfigInterface;
use RetailExpress\SkyLink\Api\Catalogue\Attributes\SkyLinkProductTypeRepositoryInterface;
use RetailExpress\SkyLink\Sdk\Catalogue\Attributes\AttributeRepositoryFactory;

class SkyLinkProductTypeRepository implements SkyLinkProductTypeRepositoryInterface
{
    private $attributeRepositoryFactory;

    private $config;

    public function __construct(
        AttributeRepositoryFactory $attributeRepositoryFactory,
        ConfigInterface $config
    ) {
        $this->attributeRepositoryFactory = $attributeRepositoryFactory;
        $this->config = $config;
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

        $attribute = $attributeRepository->find(
            $attributeCode,
            $this->config->getSalesChannelId()
        );

        return $attribute->getOptions();
    }
}
