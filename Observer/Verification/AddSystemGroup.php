<?php

namespace RetailExpress\SkyLinkMagento2\Observer\Verification;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use RetailExpress\SkyLinkMagento2\Model\Verification\Group;

class AddSystemGroup implements ObserverInterface
{
    /**
     * {@inheritdoc}
     */
    public function execute(Observer $observer)
    {
        $verifier = $observer->getVerifier();

        $group = Group::fromNative('system', 100, 'System');
        $verifier->addGroup($group);
    }
}
