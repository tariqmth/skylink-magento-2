<?php

namespace RetailExpress\SkyLink\Eds;

use RetailExpress\SkyLink\Api\Debugging\ConfigInterface;
use RetailExpress\SkyLink\Api\Debugging\SkyLinkLoggerInterface;

class V2LoggingMiddleware implements V2Middleware
{
    private $config;

    private $logger;

    public function __construct(ConfigInterface $config, SkyLinkLoggerInterface $logger)
    {
        $this->config = $config;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function execute($payload, array &$changeSets, callable $next)
    {
        if (true === $this->config->shouldLogRawApiCalls()) {
            $this->logger->debug(__('Deserialising EDS Change Sets'), [
                'Payload' => $payload,
            ]);
        }

        return $next($payload, $changeSets);
    }
}
