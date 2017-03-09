<?php

namespace RetailExpress\SkyLink\Block\Adminhtml\Mappings;

use Magento\Catalog\Api\AttributeSetRepositoryInterface as BaseMagentoAttributeSetRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context as TemplateContext;
use RetailExpress\SkyLink\Sdk\Catalogue\Attributes\AttributeOption as SkyLinkAttributeOption;
use RetailExpress\SkyLink\Api\Catalogue\Attributes\MagentoAttributeSetRepositoryInterface;
use RetailExpress\SkyLink\Api\Catalogue\Attributes\SkyLinkProductTypeRepositoryInterface;

class MagentoAttributeSet extends Template
{
    private $skyLinkProductTypeRepository;

    private $baseMagentoAttributeSetRepository;

    private $magentoAtttributeSetRepository;

    private $searchCriteriaBuilder;

    public function __construct(
        TemplateContext $templateContext,
        SkyLinkProductTypeRepositoryInterface $skyLinkProductTypeRepository,
        BaseMagentoAttributeSetRepositoryInterface $baseMagentoAttributeSetRepository,
        MagentoAttributeSetRepositoryInterface $magentoAtttributeSetRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        parent::__construct($templateContext);

        $this->skyLinkProductTypeRepository = $skyLinkProductTypeRepository;
        $this->baseMagentoAttributeSetRepository = $baseMagentoAttributeSetRepository;
        $this->magentoAtttributeSetRepository = $magentoAtttributeSetRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    public function getSkyLinkProductTypes()
    {
        return $this->skyLinkProductTypeRepository->getList();
    }

    public function getMagentoAttributeSets()
    {
        $searchCriteria = $this->searchCriteriaBuilder->create();

        $searchResults = $this->baseMagentoAttributeSetRepository->getList($searchCriteria);

        return $searchResults->getItems();
    }

    public function getAttributeSetForProductType(SkyLinkAttributeOption $productType)
    {
        return $this->magentoAtttributeSetRepository->getAttributeSetForProductType($productType);
    }

    public function getSaveUrl()
    {
        return $this->getUrl('*/*/saveMagentoAttributeSet');
    }
}
