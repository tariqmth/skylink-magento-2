<?php

namespace RetailExpress\SkyLink\Controller\Adminhtml\Setup;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use RetailExpress\SkyLink\Api\Catalogue\Attributes\MagentoAttributeServiceInterface;
use RetailExpress\SkyLink\Sdk\Catalogue\Attributes\AttributeCode as SkyLinkAttributeCode;

class SaveMagentoAttribute extends Action
{
    private $magentoProductAttributeRepository;

    private $magentoAttributeService;

    public function __construct(
        Context $context,
        ProductAttributeRepositoryInterface $magentoProductAttributeRepository,
        MagentoAttributeServiceInterface $magentoAttributeService
    ){
        parent::__construct($context);

        $this->magentoProductAttributeRepository = $magentoProductAttributeRepository;
        $this->magentoAttributeService = $magentoAttributeService;
    }

    public function execute()
    {
        $data = $this->getRequest()->getPostValue();

        array_walk($data['magento_attribute_mappings'], function ($magentoAttributeCode, $skyLinkAttributeCodeString) {

            /** @var \Magento\Catalog\Api\Data\ProductAttributeInterface $magentoAttribute */
            $magentoAttribute = $this->magentoProductAttributeRepository->get($magentoAttributeCode);

            /** @var SkyLinkAttributeCode */
            $skyLinkAttributeCode = SkyLinkAttributeCode::get($skyLinkAttributeCodeString);

            $this->magentoAttributeService->mapMagentoAttributeForSkyLinkAttributeCode($magentoAttribute, $skyLinkAttributeCode);
        });

        $this->messageManager->addSuccess(__('Successfully mapped SkyLink Attributes to Magento Attributes.'));

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
