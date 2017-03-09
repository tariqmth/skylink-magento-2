<?php

namespace RetailExpress\SkyLink\Controller\Adminhtml\Mappings;

use Magento\Backend\App\Action;

class Index extends Action
{
    public function execute()
    {
        $this->_view->loadLayout();
        $this->_setActiveMenu('RetailExpress_SkyLink::skylink_setup');
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Mappings'));
        $this->_addBreadcrumb(__('Mappings'), __('Mappings'));
        $this->_view->renderLayout();
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('RetailExpress_SkyLink::skylink_setup');
    }
}
