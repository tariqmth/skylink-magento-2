<?php

namespace RetailExpress\SkyLink\Controller\Adminhtml\Debugging;

use DateTime;
use DateTimeZone;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory as JsonResultFactory;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Monolog\Logger;
use RetailExpress\SkyLink\Api\Debugging\LogManagerInterface;

class LogClearer extends Action
{
    private $logManager;

    private $jsonResultFactory;

    public function __construct(
        Context $context,
        LogManagerInterface $logManager,
        JsonResultFactory $jsonResultFactory
    ) {
        parent::__construct($context);

        $this->logManager = $logManager;
        $this->jsonResultFactory = $jsonResultFactory;
    }
    public function execute()
    {
        $this->logManager->clearAll();

        $jsonResult = $this->jsonResultFactory->create();

        return $jsonResult;
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('RetailExpress_SkyLink::skylink_logging');
    }
}
