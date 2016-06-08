<?php

namespace RetailExpress\SkyLinkMagento2\Observer\Verification;

use Magento\Framework\Event\Observer;

trait VerificationCheckObserver
{
    private $checkFactory;

    /**
     * {@inheritDoc}
     */
    public function execute(Observer $observer)
    {
        $check = $this->checkFactory->create();
        $verifier = $observer->getVerifier();

        $verifier->addCheck($check);
    }
}
