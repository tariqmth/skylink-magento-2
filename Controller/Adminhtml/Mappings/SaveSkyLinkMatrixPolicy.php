<?php

namespace RetailExpress\SkyLink\Controller\Adminhtml\Mappings;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use RetailExpress\SkyLink\Api\Catalogue\Products\SkyLinkMatrixPolicyRepositoryInterface;
use RetailExpress\SkyLink\Api\Catalogue\Products\SkyLinkMatrixPolicyServiceInterface;
use RetailExpress\SkyLink\Sdk\Catalogue\Attributes\AttributeOption as SkyLinkAttributeOption;
use RetailExpress\SkyLink\Sdk\Catalogue\Products\MatrixPolicy as SkyLinkMatrixPolicy;

class SaveSkyLinkMatrixPolicy extends Action
{
    private $skyLinkMatrixPolicyRepository;

    private $skyLinkMatrixPolicyService;

    public function __construct(
        Context $context,
        SkyLinkMatrixPolicyRepositoryInterface $skyLinkMatrixPolicyRepository,
        SkyLinkMatrixPolicyServiceInterface $skyLinkMatrixPolicyService
    ) {
        parent::__construct($context);

        $this->skyLinkMatrixPolicyRepository = $skyLinkMatrixPolicyRepository;
        $this->skyLinkMatrixPolicyService = $skyLinkMatrixPolicyService;
    }

    public function execute()
    {
        $data = $this->getRequest()->getPostValue();

        array_walk(
            $data['skylink_matrix_policy_mappings'],
            function (array $skyLinkAttributeCodes, $skyLinkProductTypeId) {

                /* @var SkyLinkMatrixPolicy $skyLinkMatrixPolicy */
                $skyLinkMatrixPolicy = SkyLinkMatrixPolicy::fromNative($skyLinkAttributeCodes);

                /* @var SkyLinkAttributeOption $skyLinkProductType */
                $skyLinkProductType = SkyLinkAttributeOption::fromNative('product_type', (string) $skyLinkProductTypeId);

                $this->skyLinkMatrixPolicyService->mapSkyLinkMatrixPolicyForSkyLinkProductType(
                    $skyLinkMatrixPolicy,
                    $skyLinkProductType
                );
            }
        );

        $this->messageManager->addSuccess(__('Successfully mapped SkyLink Product Types to SkyLink Matrix Policies.'));

        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('*/*/index');

        return $resultRedirect;
    }
}
