<?php

namespace RetailExpress\SkyLink\Model\Debugging;

use Psr\Log\LoggerInterface;
use RetailExpress\SkyLink\Api\Debugging\SkyLinkLoggerInterface;
use RetailExpress\SkyLink\Api\Debugging\SkyLinkMonologHandlerInterface;

class SkyLinkLogger implements SkyLinkLoggerInterface
{
    private $baseLogger;

    public function __construct(LoggerInterface $baseLogger)
    {
        $this->baseLogger = $baseLogger;
    }

    /**
     * {@inheritdoc}
     */
    public function emergency($message, array $context = array())
    {
        $this->baseLogger->emergency($message, $this->updateContext($context));
    }

    /**
     * {@inheritdoc}
     */
    public function alert($message, array $context = array())
    {
        $this->baseLogger->alert($message, $this->updateContext($context));
    }

    /**
     * {@inheritdoc}
     */
    public function critical($message, array $context = array())
    {
        $this->baseLogger->critical($message, $this->updateContext($context));
    }

    /**
     * {@inheritdoc}
     */
    public function error($message, array $context = array())
    {
        $this->baseLogger->error($message, $this->updateContext($context));
    }

    /**
     * {@inheritdoc}
     */
    public function warning($message, array $context = array())
    {
        $this->baseLogger->warning($message, $this->updateContext($context));
    }

    /**
     * {@inheritdoc}
     */
    public function notice($message, array $context = array())
    {
        $this->baseLogger->notice($message, $this->updateContext($context));
    }

    /**
     * {@inheritdoc}
     */
    public function info($message, array $context = array())
    {
        $this->baseLogger->info($message, $this->updateContext($context));
    }

    /**
     * {@inheritdoc}
     */
    public function debug($message, array $context = array())
    {
        $this->baseLogger->debug($message, $this->updateContext($context));
    }

    /**
     * {@inheritdoc}
     */
    public function log($level, $message, array $context = array())
    {
        $this->baseLogger->log($level, $message, $this->updateContext($context));
    }

    /**
     * Modifies the given context to add a reserved key so that we can determine
     * if we are to handle it or not in our own SkyLink implementations.
     *
     * @param array $context
     *
     * @return array
     */
    private function updateContext(array $context)
    {
        $context[SkyLinkMonologHandlerInterface::CONTEXT_KEY] = true;

        return $context;
    }
}
