<?php

namespace RetailExpress\SkyLinkMagento2\Observer\Verification;

use Magento\Framework\Event\ObserverInterface;
use RetailExpress\SkyLinkMagento2\Model\Verification\CanConnectToApiFactory as CheckFactory;

class CanConnectToApi implements ObserverInterface
{
    use VerificationCheckObserver;

    public function __construct(CheckFactory $checkFactory)
    {
        $this->checkFactory = $checkFactory;
    }
}
