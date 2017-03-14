<?php

namespace RetailExpress\SkyLink\Model\Segregation;

use Exception;
use InvalidArgumentException;
use RetailExpress\SkyLink\Api\Segregation\MagentoStoreEmulatorInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Api\Data\WebsiteInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\Website;
use Throwable;

class MagentoStoreEmulator implements MagentoStoreEmulatorInterface
{
    /**
     * The Magneto Store Manager instance.
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $magentoStoreManager;

    public function __construct(StoreManagerInterface $magentoStoreManager)
    {
        $this->magentoStoreManager = $magentoStoreManager;
    }

    public function onWebsite(WebsiteInterface $magentoWebsite, callable $callback)
    {
        $this->assertImplementationOfWebsiteInterface($magentoWebsite);

        return $this->onStore($magentoWebsite->getDefaultStore(), $callback);
    }

    public function onStore(StoreInterface $magentoStore, callable $callback)
    {
        $currentStore = $this->magentoStoreManager->getStore();

        try {
            $this->magentoStoreManager->setCurrentStore($magentoStore->getCode());
            $response = $callback();
            $this->magentoStoreManager->setCurrentStore($currentStore);
            return $response;
        } catch (Throwable $e) { // PHP 7+
            $this->magentoStoreManager->setCurrentStore($currentStore);
            throw $e;
        } catch (Exception $e) {
            $this->magentoStoreManager->setCurrentStore($currentStore);
            throw $e;
        }
    }

    private function assertImplementationOfWebsiteInterface(WebsiteInterface $magentoWebsite)
    {
        if (!$magentoWebsite instanceof Website) {
            throw new InvalidArgumentException(sprintf(
                'Executing commands against a given Magento Website requires the Website be an instance of %s, %s given.',
                Website::class,
                get_class($magentoWebsite)
            ));
        }
    }
}
