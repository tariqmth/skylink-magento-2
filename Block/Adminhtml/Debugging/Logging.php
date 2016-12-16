<?php

namespace RetailExpress\SkyLink\Block\Adminhtml\Debugging;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context as TemplateContext;
use Monolog\Logger;

class Logging extends Template
{
    public function __construct(TemplateContext $templateContext)
    {
        parent::__construct($templateContext);
    }

    public function getLogViewerUrl()
    {
        return $this->getUrl('*/*/logViewer');
    }

    public function getHumanLevels()
    {
        return Logger::getLevels();
    }

    public function getLevels()
    {
        return array_values($this->getHumanLevels());
    }
}
