<?php

namespace RetailExpress\SkyLink\Controller\Adminhtml\Debugging;

use Magento\Backend\App\Action;

class Logging extends Action
{
    public function execute()
    {
        $this->_view->loadLayout();
        $this->_setActiveMenu('RetailExpress_SkyLink::skylink_logging');
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Logging'));
        $this->_addBreadcrumb(__('Logging'), __('Logging'));
        $this->_view->renderLayout();
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('RetailExpress_SkyLink::skylink_logging');
    }
}
