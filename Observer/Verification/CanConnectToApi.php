<?php

namespace RetailExpress\SkyLink\Observer\Verification;

use Magento\Framework\Event\ObserverInterface;
use RetailExpress\SkyLink\Model\Verification\CanConnectToApiFactory as CheckFactory;

class CanConnectToApi implements ObserverInterface
{
    use VerificationCheckObserver;

    public function __construct(CheckFactory $checkFactory)
    {
        $this->checkFactory = $checkFactory;
    }
}
