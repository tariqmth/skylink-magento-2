<?php

namespace RetailExpress\SkyLink\Plugin\SkyLink\Catalogue\Products;

use Exception;
use Magento\Framework\Registry;
use RetailExpress\SkyLink\Commands\Catalogue\Products\SyncSkyLinkProductToMagentoProductCommand;
use RetailExpress\SkyLink\Commands\Catalogue\Products\SyncSkyLinkProductToMagentoProductHandler;
use Throwable;

/**
 * This class is an attempted workaround for the issues experienced in
 * https://github.com/magento/magento2/issues/8520#issuecomment-285510588
 */
class SyncSkyLinkProductToMagentoProductHandlerWebsiteIdWorkaroundPlugin
{
    const REGISTRY_KEY = 'skylink_product_to_magento_product_website_id_workaround';

    private $registry;

    public function __construct(Registry $registry)
    {
        $this->registry = $registry;
    }

    public function aroundHandle(
        SyncSkyLinkProductToMagentoProductHandler $subject,
        callable $proceed,
        SyncSkyLinkProductToMagentoProductCommand $command
    ) {
        $this->registry->register(self::REGISTRY_KEY, true);

        try {
            $response = $proceed($command);
        } catch (Throwable $e) { // PHP 7+
            $this->registry->unregister(self::REGISTRY_KEY);
            throw $e;
        } catch (Exception $e) {
            $this->registry->unregister(self::REGISTRY_KEY);
            throw $e;
        }

        $this->registry->unregister(self::REGISTRY_KEY);
        return $response;
    }
}
