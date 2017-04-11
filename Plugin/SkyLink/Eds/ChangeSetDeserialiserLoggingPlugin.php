<?php

namespace RetailExpress\SkyLink\Plugin\SkyLink\Eds;

use Exception;
use RetailExpress\SkyLink\Api\Debugging\ConfigInterface;
use RetailExpress\SkyLink\Api\Debugging\SkyLinkLoggerInterface;
use RetailExpress\SkyLink\Eds\ChangeSetDeserialiser;
use Throwable;

class ChangeSetDeserialiserLoggingPlugin
{
    private $config;

    private $logger;

    public function __construct(ConfigInterface $config, SkyLinkLoggerInterface $logger)
    {
        $this->config = $config;
        $this->logger = $logger;
    }

    public function aroundDeserialise(ChangeSetDeserialiser $subject, callable $proceed, $payload)
    {
        if (false === $this->config->shouldLogRawApiCalls()) {
            return $proceed($payload);
        }

        try {
            $response = $proceed($payload);

            $this->logger->debug(__('Deserialising EDS Change Sets'), [
                'Payload' => $payload,
            ]);

            return $response;
        } catch (Throwable $e) { // PHP 7+
            goto fail;
        } catch (Exception $e) {
            goto fail;
        }

        fail:
        $this->logger->critical(__('Deserialising EDS Change Sets failed'), [
            'Payload' => $payload,
            'Exception' => [
                'Name' => class_basename($e),
                'Message' => $e->getMessage(),
                'Where' => sprintf('%s @ Line %d', $e->getFile(), $e->getLine()),
                'Trace' => $e->getTraceAsString(),
            ]
        ]);
        throw $e;
    }
}
