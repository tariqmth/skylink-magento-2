<?php

namespace RetailExpress\SkyLink\Plugin\SkyLink\Sdk\Apis;

use DOMDocument;
use Exception;
use RetailExpress\SkyLink\Sdk\Apis\V2 as V2Api;
use RetailExpress\SkyLink\Api\Debugging\ConfigInterface;
use RetailExpress\SkyLink\Api\Debugging\SkyLinkLoggerInterface;
use Throwable;

class V2ApiLoggingPlugin
{
    private $config;

    private $logger;

    public function __construct(ConfigInterface $config, SkyLinkLoggerInterface $logger)
    {
        $this->config = $config;
        $this->logger = $logger;
    }

    public function aroundCall(V2Api $subject, callable $proceed, $method, array $arguments = [], callable $postProcesing = null)
    {
        // We'll exit early if we don't need to log
        if (false === $this->config->shouldLogRawApiCalls()) {
            return $proceed($method, $arguments, $postProcesing);
        }

        try {
            $response = $proceed($method, $arguments, $postProcesing);
            $this->logRequestAndResponse($method, $arguments, $response);
            return $response;
        } catch (Throwable $e) { // PHP 7+
            goto fail;
        } catch (Exception $e) {
            goto fail;
        }

        fail:
        $this->logRequestAndException($method, $arguments, $e);
        throw $e;
    }

    private function logRequestAndResponse($method, array $arguments = [], $response)
    {
        $this->logger->debug(__('V2 API call'), [
            'Method' => $method,
            'Arguments' => $arguments,
            'Response' => $this->formatXml($response),
        ]);
    }

    private function logRequestAndException($method, array $arguments = [], Exception $exception)
    {
        $this->logger->critical('V2 API call failed', [
            'Method' => $method,
            'Arguments' => $arguments,
            'Exception' => [
                'Name' => class_basename($exception),
                'Message' => $exception->getMessage(),
                'Where' => sprintf('%s @ Line %d', $exception->getFile(), $exception->getLine()),
                'Trace' => $exception->getTraceAsString(),
            ],
        ]);
    }

    private function formatXml($response)
    {
        // We'll just check we have valid XML
        libxml_use_internal_errors(true);
        if (false === simplexml_load_string($response)) {
            return $response;
        }

        $domxml = new DOMDocument('1.0');
        $domxml->preserveWhiteSpace = false;
        $domxml->formatOutput = true;
        /* @var $xml SimpleXMLElement */
        $domxml->loadXML($response);
        return $domxml->saveXML();
    }
}
