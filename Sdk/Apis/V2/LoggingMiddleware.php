<?php

namespace RetailExpress\SkyLink\Sdk\Apis\V2;

use DOMDocument;
use Exception;
use RetailExpress\SkyLink\Api\Debugging\ConfigInterface;
use RetailExpress\SkyLink\Api\Debugging\SkyLinkLoggerInterface;
use SoapFault;
use Throwable;

class LoggingMiddleware implements Middleware
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
    public function execute($request, &$response, SoapFault $soapFault = null, callable $next)
    {
        if (false === $this->config->shouldLogRawApiCalls()) {
            return $next($request, $response, $soapFault);
        }

        try {
            $response = $next($request, $response, $soapFault);
            $this->logRequestAndResponse($request, $response);
            return $response;
        } catch (Throwable $e) { // PHP 7+
            goto fail;
        } catch (Exception $e) {
            goto fail;
        }

        fail:
        $this->logRequestResponseAndException($request, $response, $e);
        throw $e;
    }

    private function logRequestAndResponse($request, $response)
    {
        $this->logger->debug(__('V2 API call'), [
            'Request' => $this->formatXml($request),
            'Response' => $this->formatXml($response),
        ]);
    }

    private function logRequestResponseAndException($request, $response, Exception $exception)
    {
        $this->logger->critical('V2 API call failed', [
            'Request' => $this->formatXml($request),
            'Response' => $this->formatXml($response),
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
