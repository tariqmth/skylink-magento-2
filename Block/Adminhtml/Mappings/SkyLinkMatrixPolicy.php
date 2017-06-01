<?php

namespace RetailExpress\SkyLink\Block\Adminhtml\Mappings;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context as TemplateContext;
use RetailExpress\SkyLink\Api\Catalogue\Attributes\SkyLinkAttributeCodeRepositoryInterface;
use RetailExpress\SkyLink\Api\Catalogue\Attributes\SkyLinkProductTypeRepositoryInterface;
use RetailExpress\SkyLink\Api\Catalogue\Products\SkyLinkMatrixPolicyRepositoryInterface;
use RetailExpress\SkyLink\Api\Catalogue\Products\SkyLinkMatrixPolicyServiceInterface;
use RetailExpress\SkyLink\Sdk\Catalogue\Attributes\AttributeOption as SkyLinkAttributeOption;

class SkyLinkMatrixPolicy extends Template
{
    private $skyLinkProductTypeRepository;

    private $skyLinkAttributeCodeRepository;

    private $skyLinkMatrixPolicyRepository;

    private $skyLinkMatrixPolicyService;

    private $skyLinkAttributeCodes;

    public function __construct(
        TemplateContext $templateContext,
        SkyLinkProductTypeRepositoryInterface $skyLinkProductTypeRepository,
        SkyLinkAttributeCodeRepositoryInterface $skyLinkAttributeCodeRepository,
        SkyLinkMatrixPolicyRepositoryInterface $skyLinkMatrixPolicyRepository,
        SkyLinkMatrixPolicyServiceInterface $skyLinkMatrixPolicyService
    ) {
        parent::__construct($templateContext);

        $this->skyLinkProductTypeRepository = $skyLinkProductTypeRepository;
        $this->skyLinkAttributeCodeRepository = $skyLinkAttributeCodeRepository;
        $this->skyLinkMatrixPolicyRepository = $skyLinkMatrixPolicyRepository;
        $this->skyLinkMatrixPolicyService = $skyLinkMatrixPolicyService;
    }

    public function getSkyLinkProductTypes()
    {
        return $this->skyLinkProductTypeRepository->getList();
    }

    public function hasSkyLinkAttributeCodes()
    {
        return count($this->getSkyLinkAttributeCodes()) > 0;
    }

    public function getSkyLinkAttributeCodes()
    {
        if (null === $this->skyLinkAttributeCodes) {
            $this->skyLinkAttributeCodes = $this
                ->skyLinkMatrixPolicyService
                ->filterSkyLinkAttributeCodesForUseInSkyLinkMatrixPolicies($this->skyLinkAttributeCodeRepository->getList());
        }

        return $this->skyLinkAttributeCodes;
    }

    public function getMatrixPolicyForProductType(SkyLinkAttributeOption $productType)
    {
        return $this->skyLinkMatrixPolicyRepository->getMatrixPolicyForProductType($productType);
    }

    public function getSaveUrl()
    {
        return $this->getUrl('*/*/saveSkyLinkMatrixPolicy');
    }
}
