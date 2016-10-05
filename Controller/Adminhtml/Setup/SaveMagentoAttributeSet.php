<?php

namespace RetailExpress\SkyLink\Controller\Adminhtml\Setup;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Eav\Api\AttributeSetRepositoryInterface as MagentoAttributeSetRepositoryInterface;
use RetailExpress\SkyLink\Api\Products\MagentoAttributeSetServiceInterface;
use RetailExpress\SkyLink\Catalogue\Attributes\AttributeOption as SkyLinkAttributeOption;

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

        foreach ($data['magento_attribute_set_mappings'] as $skyLinkProductTypeId => $magentoAttributeSetId) {

            /** @var SkyLinkAttributeOption */
            $skyLinkProductType = SkyLinkAttributeOption::fromNative('product_type', (string) $skyLinkProductTypeId);

            /** @var \Magento\Eav\Api\Data\AttributeSetInterface */
            $magentoAttributeSet = $this->magentoAttributeSetRepository->get($magentoAttributeSetId);

            $this->magentoAttributeSetService->mapAttributeSetForProductType($magentoAttributeSet, $skyLinkProductType);
        }

        $this->messageManager->addSuccess(__('Successfully mapped Product Types to Attribute Sets.'));

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
