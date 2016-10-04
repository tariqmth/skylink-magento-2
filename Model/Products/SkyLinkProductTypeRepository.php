<?php

namespace RetailExpress\SkyLink\Model\Products;

use RetailExpress\SkyLink\Catalogue\Attributes\AttributeCode;
use RetailExpress\SkyLink\Catalogue\Attributes\AttributeRepository;
use RetailExpress\SkyLink\ValueObjects\SalesChannelId;
use RetailExpress\SkyLink\Model\Config;
use RetailExpress\SkyLink\Api\Products\SkyLinkProductTypeRepositoryInterface;
use RetailExpress\SkyLink\Api\RepositoryFactoryInterface;

class SkyLinkProductTypeRepository implements SkyLinkProductTypeRepositoryInterface
{
    private $attributeRepository;

    private $config;

    public function __construct(
        RepositoryFactoryInterface $repositoryFactory,
        Config $config
    ) {
        $this->attributeRepository = $repositoryFactory->createCatalogueAttributeRepository();
        $this->config = $config;
    }

    /**
     * List all Product Types available.
     *
     * @return \RetailExpress\SkyLink\Catalogue\Attributes\AttributeOption[]
     */
    public function getList()
    {
        /* @var \RetailExpress\SkyLink\Catalogue\Attributes\AttributeCode */
        $attributeCode = AttributeCode::get(AttributeCode::PRODUCT_TYPE);

        $attribute = $this->attributeRepository->find(
            $attributeCode,
            $this->config->getSalesChannelId()
        );

        return $attribute->getOptions();
    }
}
