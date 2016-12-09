<?php

namespace RetailExpress\SkyLink\Controller\Adminhtml\Setup;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use RetailExpress\CommandBus\Api\CommandBusInterface;
use RetailExpress\SkyLink\Commands\Catalogue\Attributes\SyncSkyLinkAttributeToMagentoAttributeCommand;

class SaveMagentoAttribute extends Action
{
    private $commandBus;

    public function __construct(
        Context $context,
        CommandBusInterface $commandBus
    ) {
        parent::__construct($context);

        $this->commandBus = $commandBus;
    }

    public function execute()
    {
        $data = $this->getRequest()->getPostValue();

        array_walk($data['magento_attribute_mappings'], function ($magentoAttributeCode, $skyLinkAttributeCode) {
            $command = new SyncSkyLinkAttributeToMagentoAttributeCommand();
            $command->magentoAttributeCode = $magentoAttributeCode;
            $command->skyLinkAttributeCode = $skyLinkAttributeCode;

            $this->commandBus->handle($command);
        });

        $this->messageManager->addSuccess(__('Queued the mapping SkyLink Attributes to Magento Attributes. Your mapped attributes selection will update once the background task has completed (@todo make selections update immediately).'));

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
