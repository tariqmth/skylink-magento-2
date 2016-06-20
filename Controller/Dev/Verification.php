<?php

namespace RetailExpress\SkyLink\Controller\Dev;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Verification extends Action
{
    private $pageFactory;

    public function __construct(Context $context, PageFactory $pageFactory)
    {
        $this->pageFactory = $pageFactory;

        parent::__construct($context);
    }

    public function execute()
    {
        return $this->pageFactory->create();
    }
}
