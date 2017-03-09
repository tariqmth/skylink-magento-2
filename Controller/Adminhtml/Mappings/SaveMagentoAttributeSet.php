<?php

namespace RetailExpress\SkyLink\Controller\Adminhtml\Mappings;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Eav\Api\AttributeSetRepositoryInterface as MagentoAttributeSetRepositoryInterface;
use RetailExpress\SkyLink\Api\Catalogue\Attributes\MagentoAttributeSetServiceInterface;
use RetailExpress\SkyLink\Sdk\Catalogue\Attributes\AttributeOption as SkyLinkAttributeOption;

class SaveMagentoAttributeSet extends Action
{
    private $magentoAttributeSetRepository;

    private $magentoAttributeSetService;

    public function __construct(
        Context $context,
        MagentoAttributeSetRepositoryInterface $magentoAttributeSetRepository,
        MagentoAttributeSetServiceInterface $magentoAttributeSetService
    ) {
        parent::__construct($context);

        $this->magentoAttributeSetRepository = $magentoAttributeSetRepository;
        $this->magentoAttributeSetService = $magentoAttributeSetService;
    }

    public function execute()
    {
        $data = $this->getRequest()->getPostValue();

        array_walk($data['magento_attribute_set_mappings'], function ($magentoAttributeSetId, $skyLinkProductTypeId) {

            /* @var SkyLinkAttributeOption */
            $skyLinkProductType = SkyLinkAttributeOption::fromNative('product_type', (string) $skyLinkProductTypeId);

            /* @var \Magento\Eav\Api\Data\AttributeSetInterface */
            $magentoAttributeSet = $this->magentoAttributeSetRepository->get($magentoAttributeSetId);

            $this->magentoAttributeSetService->mapAttributeSetForProductType($magentoAttributeSet, $skyLinkProductType);
        });

        $this->messageManager->addSuccess(__('Successfully mapped SkyLink Product Types to Magento Attribute Sets.'));

        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('*/*/index');

        return $resultRedirect;
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('RetailExpress_SkyLink::skylink_setup_save');
    }
}
