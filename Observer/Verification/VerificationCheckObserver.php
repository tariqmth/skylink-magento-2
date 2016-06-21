<?php

namespace RetailExpress\SkyLink\Observer\Verification;

use Magento\Framework\Event\Observer;

trait VerificationCheckObserver
{
    private $checkFactory;

    /**
     * {@inheritdoc}
     */
    public function execute(Observer $observer)
    {
        $check = $this->checkFactory->create();
        $verifier = $observer->getVerifier();

        $verifier->addCheck($check);
    }
}
