<?php

namespace RetailExpress\SkyLink\Block\Adminhtml\Debugging;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context as TemplateContext;
use Monolog\Logger;
use RetailExpress\SkyLink\Api\Debugging\ConfigInterface;

class Logging extends Template
{
    private $config;

    public function __construct(TemplateContext $templateContext, ConfigInterface $config)
    {
        parent::__construct($templateContext);
        $this->config = $config;
    }

    public function getLogViewerUrl()
    {
        return $this->getUrl('*/*/logViewer');
    }

    public function getlogClearerUrl()
    {
        return $this->getUrl('*/*/logClearer');
    }

    public function getHumanLevels()
    {
        return array_reverse(Logger::getLevels());
    }

    public function getDefaultLevel()
    {
        return min($this->getLevels());
    }

    public function getLevels()
    {
        return array_values($this->getHumanLevels());
    }

    public function getLogsToKeep()
    {
        if ($this->config->shouldCaptureLogs()) {
            return $this->config->getCapturedLogsToKeep();
        }

        return $this->config->getUncapturedLogsTokeep();
    }
}
