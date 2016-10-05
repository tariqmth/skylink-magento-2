<?php

namespace RetailExpress\SkyLink\Block\Adminhtml\Setup;

use Magento\Eav\Api\AttributeSetRepositoryInterface as MagentoAttributeSetRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context as TemplateContext;
use RetailExpress\SkyLink\Api\Products\SkyLinkProductTypeRepositoryInterface;

class MagentoAttributeSet extends Template
{
    private $skyLinkProductTypeRepository;

    private $magentoAttributeSetRepository;

    private $searchCriteriaBuilder;

    public function __construct(
        TemplateContext $templateContext,
        SkyLinkProductTypeRepositoryInterface $skyLinkProductTypeRepository,
        MagentoAttributeSetRepositoryInterface $magentoAttributeSetRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        parent::__construct($templateContext);

        $this->skyLinkProductTypeRepository = $skyLinkProductTypeRepository;
        $this->magentoAttributeSetRepository = $magentoAttributeSetRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    public function getSkyLinkProductTypes()
    {
        return $this->skyLinkProductTypeRepository->getList();
    }

    public function getMagentoAttributeSets()
    {
        $searchCriteria = $this->searchCriteriaBuilder->create();

        $searchResults = $this->magentoAttributeSetRepository->getList($searchCriteria);

        return $searchResults->getItems();
    }

    public function getSaveUrl()
    {
        return $this->getUrl('*/*/saveMagentoAttributeSet');
    }
}
