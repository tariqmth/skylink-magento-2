<?php

namespace RetailExpress\SkyLink\Observer\CommandBus;

use Bernard\Consumer;
use Bernard\Envelope;
use Bernard\Queue\PersistentQueue;
use Bernard\Queue;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use ReflectionClass;
use RetailExpress\SkyLink\Api\Debugging\SkyLinkLoggerInterface;

class WhenQueuedCommandFailed implements ObserverInterface
{
    private $consumer;

    private $logger;

    public function __construct(
        Consumer $consumer,
        SkyLinkLoggerInterface $logger
    ) {
        $this->consumer = $consumer;
        $this->logger = $logger;
    }

    public function execute(Observer $observer)
    {
        /* @var \Bernard\Event\RejectEnvelopeEvent $event */
        $event = $observer->getEvent();

        $envelope = $event->getEnvelope();
        $command = $envelope->getMessage();
        $queuedCommandId = $this->guessQueuedCommandId($event->getQueue(), $envelope);
        $exception = $event->getException();

        if ($this->isStoppingOnError()) {
            $message = 'A queued command has failed and will cause the queue worker to crash. If you have a process manager (e.g. Supervisor) it may restart this worker.';
        } else {
            $message = 'A queued command has failed and the worker will skip to the next one.';
        }

        $message .= ' Please look through previous logs to see what may have caused the issue.';

        $this->logger->critical($message, [
            'Command ID' => $queuedCommandId !== null ? $queuedCommandId : 'Unkown',
            'Command Name' => class_basename(get_class($command)),
            'Arguments' => get_object_vars($command),
            'Exception' => [
                'Name' => class_basename($exception),
                'Message' => $exception->getMessage(),
                'Where' => sprintf('%s @ Line %d', $exception->getFile(), $exception->getLine()),
                'Trace' => $exception->getTraceAsString(),
            ],
        ]);
    }

    /**
     * Inspects the singleton instance of the Bernard Consumer to determine if it will
     * fail after our observer is finished, which is useful for debugging.
     *
     * @return bool
     */
    private function isStoppingOnError()
    {
        $reflectedConsumer = new ReflectionClass($this->consumer);
        $reflectedOptions = $reflectedConsumer->getProperty('options');
        $reflectedOptions->setAccessible(true);

        $options = $reflectedOptions->getValue($this->consumer);

        return isset($options['stop-on-error']) && true === $options['stop-on-error'];
    }

    private function guessQueuedCommandId(Queue $queue, Envelope $envelope)
    {
        if (!$queue instanceof PersistentQueue) {
            return null;
        }

        $reflectedQueue = new ReflectionClass($queue);
        $reflectedReceipts = $reflectedQueue->getProperty('receipts');
        $reflectedReceipts->setAccessible(true);

        $receipts = $reflectedReceipts->getValue($queue);

        if (!$receipts->contains($envelope)) {
            return null;
        }

        return $receipts[$envelope];
    }
}
