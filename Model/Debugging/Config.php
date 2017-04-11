<?php

namespace RetailExpress\SkyLink\Model\Debugging;

use Magento\Framework\App\Config\ScopeConfigInterface;
use RetailExpress\SkyLink\Api\Debugging\ConfigInterface;
use ValueObjects\Number\Integer;
use ValueObjects\Number\Real;

class Config implements ConfigInterface
{
    private $scopeConfig;

    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function shouldCaptureLogs()
    {
        return $this->scopeConfig->getValue('skylink/debugging/should_capture_logs');
    }

    /**
     * {@inheritdoc}
     */
    public function getUncapturedLogsToKeep()
    {
        return new Integer($this->scopeConfig->getValue('skylink/debugging/uncaptured_logs_to_keep'));
    }

    /**
     * {@inheritdoc}
     */
    public function getCapturedLogsToKeep()
    {
        return new Integer($this->scopeConfig->getValue('skylink/debugging/captured_logs_to_keep'));
    }

    /**
     * {@inheritdoc}
     */
    public function shouldLogRawApiCalls()
    {
        return (bool) $this->scopeConfig->getValue('skylink/debugging/should_log_raw_api_calls');
    }

    public function getPurgingChance()
    {
        return new Real($this->scopeConfig->getValue('skylink/debugging/purging_chance'));
    }
}
