<?php

namespace RetailExpress\SkyLink\Block\Adminhtml\Mappings;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context as TemplateContext;
use Magento\Framework\Api\SearchCriteriaBuilder;
use RetailExpress\SkyLink\Api\Catalogue\Attributes\MagentoAttributeRepositoryInterface;
use RetailExpress\SkyLink\Api\Catalogue\Attributes\MagentoAttributeTypeManagerInterface;
use RetailExpress\SkyLink\Api\Catalogue\Attributes\SkyLinkAttributeCodeRepositoryInterface;
use RetailExpress\SkyLink\Sdk\Catalogue\Attributes\AttributeCode as SkyLinkAttributeCode;

class MagentoAttribute extends Template
{
    private $skyLinkAttributeCodeRepository;

    private $magentoAttributeRepository;

    public function __construct(
        TemplateContext $templateContext,
        SkyLinkAttributeCodeRepositoryInterface $skyLinkAttributeCodeRepository,
        MagentoAttributeRepositoryInterface $magentoAttributeRepository
    ) {
        parent::__construct($templateContext);

        $this->skyLinkAttributeCodeRepository = $skyLinkAttributeCodeRepository;
        $this->magentoAttributeRepository = $magentoAttributeRepository;
    }

    public function getSkyLinkAttributeCodes()
    {
        return $this->skyLinkAttributeCodeRepository->getList();
    }

    public function getMagentoAttributesByType()
    {
        return $this->magentoAttributeRepository->getMagentoAttributesByType();
    }

    public function getMagentoAttributeForSkyLinkAttributeCode(SkyLinkAttributeCode $skylinkAttributeCode)
    {
        $magentoAttribute = $this
            ->magentoAttributeRepository
            ->getMagentoAttributeForSkyLinkAttributeCode($skylinkAttributeCode);

        if (null !== $magentoAttribute) {
            return $magentoAttribute;
        }

        return $this
            ->magentoAttributeRepository
            ->getDefaultMagentoAttributeForSkyLinkAttributeCode($skylinkAttributeCode);
    }

    public function getSaveUrl()
    {
        return $this->getUrl('*/*/saveMagentoAttribute');
    }
}
