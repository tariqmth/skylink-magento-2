<?php

namespace RetailExpress\SkyLink\Console\Command;

use Exception;
use Magento\Backend\App\Area\FrontNameResolver as BackendFrontNameResolver;
use Magento\Framework\App\State as AppState;
use Magento\Framework\Registry;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Throwable;

class AdminEmulator
{
    /**
     * Application state instance.
     *
     * @var AppState
     */
    private $appState;

    /**
     * Registry instance.
     *
     * @var Registry
     */
    private $registry;

    private $storeManager;

    private $originalStore;

    public function __construct(
        AppState $appState,
        Registry $registry,
        StoreManagerInterface $storeManager
    ) {
        $this->appState = $appState;
        $this->registry = $registry;
        $this->storeManager = $storeManager;
    }

    public function onAdmin(callable $callback)
    {
        $this->setupAdmin();

        try {
            $response = $callback();
            $this->revertAdmin();
        } catch (Throwable $e) { // PHP 7+
            $this->revertAdmin();
            throw $e;
        } catch (Exception $e) {
            $this->revertAdmin();
            throw $e;
        }

        return $response;
    }

    /**
     * @see RetailExpress\CommandBus\Console\Command\ConsumeCommand::execute
     */
    private function setupAdmin()
    {
        // Save our current store to revert
        $this->originalStore = $this->storeManager->getStore();

        // Set into admin mode
        $this->registry->register('isSecureArea', true);
        $this->appState->setAreaCode(BackendFrontNameResolver::AREA_CODE);
        $this->storeManager->setCurrentStore(Store::ADMIN_CODE); // http://magento.stackexchange.com/a/151162
    }

    private function revertAdmin()
    {
        // We can't reset the app state, but we'll revert everything else
        $this->registry->unregister('isSecureArea');
        $this->storeManager->setCurrentStore($this->originalStore->getCode());
    }
}
