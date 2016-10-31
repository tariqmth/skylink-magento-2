<?php

namespace RetailExpress\SkyLink\Block\Adminhtml\Setup;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context as TemplateContext;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface as BaseMagentoProductAttributeRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrderBuilder;
use RetailExpress\SkyLink\Api\Catalogue\Attributes\MagentoAttributeRepositoryInterface;
use RetailExpress\SkyLink\Api\Catalogue\Attributes\SkyLinkAttributeCodeRepositoryInterface;
use RetailExpress\SkyLink\Sdk\Catalogue\Attributes\AttributeCode as SkyLinkAttributeCode;

class MagentoAttribute extends Template
{
    private $skyLinkAttributeCodeRepository;

    private $baseMagentoProductAttributeRepository;

    private $magentoAttributeRepository;

    private $searchCriteriaBuilder;

    private $sortOrderBuilder;

    public function __construct(
        TemplateContext $templateContext,
        SkyLinkAttributeCodeRepositoryInterface $skyLinkAttributeCodeRepository,
        BaseMagentoProductAttributeRepositoryInterface $baseMagentoProductAttributeRepository,
        MagentoAttributeRepositoryInterface $magentoAttributeRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        SortOrderBuilder $sortOrderBuilder
    ) {
        parent::__construct($templateContext);

        $this->skyLinkAttributeCodeRepository = $skyLinkAttributeCodeRepository;
        $this->baseMagentoProductAttributeRepository = $baseMagentoProductAttributeRepository;
        $this->magentoAttributeRepository = $magentoAttributeRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->sortOrderBuilder = $sortOrderBuilder;
    }

    public function getSkyLinkAttributeCodes()
    {
        return $this->skyLinkAttributeCodeRepository->getList();
    }

    /**
     * @todo move to specific repository?
     */
    public function getMagentoAttributes()
    {
        $this->searchCriteriaBuilder->addFilter('frontend_input', 'select');
        $this->searchCriteriaBuilder->addFilter('is_global', true);

        $nameSortOrder = $this->sortOrderBuilder->setField('frontend_label')->setAscendingDirection()->create();
        $this->searchCriteriaBuilder->addSortOrder($nameSortOrder);

        $searchCriteria = $this->searchCriteriaBuilder->create();

        $searchResults = $this->baseMagentoProductAttributeRepository->getList($searchCriteria);

        return $searchResults->getItems();
    }

    public function getMagentoAttributeForSkyLinkAttributeCode(SkyLinkAttributeCode $skylinkAttributeCode)
    {
        return $this->magentoAttributeRepository->getMagentoAttributeForSkyLinkAttributeCode($skylinkAttributeCode);
    }

    public function getSaveUrl()
    {
        return $this->getUrl('*/*/saveMagentoAttribute');
    }
}
