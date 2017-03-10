<?php

namespace RetailExpress\SkyLink\Controller\Eds;

use InvalidArgumentException;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory as JsonResultFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use RetailExpress\SkyLink\Api\Eds\ChangeSetRepositoryInterface;
use RetailExpress\SkyLink\Eds\ChangeSet;
use RetailExpress\SkyLink\Eds\ChangeSetDeserialiserFactory;

class Notify extends Action
{
    private $changeSetDeserialiserFactory;

    private $changeSetRepository;

    private $jsonResultFactory;

    public function __construct(
        Context $context,
        ChangeSetDeserialiserFactory $changeSetDeserialiserFactory,
        ChangeSetRepositoryInterface $changeSetRepository,
        JsonResultFactory $jsonResultFactory
    ) {
        parent::__construct($context);

        $this->changeSetDeserialiserFactory = $changeSetDeserialiserFactory;
        $this->changeSetRepository = $changeSetRepository;
        $this->jsonResultFactory = $jsonResultFactory;
    }

    public function execute()
    {
        /* @var \Magento\Framework\Controller\Result\Json $jsonResult */
        $jsonResult = $this->jsonResultFactory->create();

        if (!$this->getRequest()->isPut()) {
            return $jsonResult
                ->setHttpResponseCode(405)
                ->setData(['message' => 'Only PUT requests are allowed.']);
        }

        // Firstly, parse the body that's been sent through
        $payload = $this->getRequest()->getContent();

        /* @var \RetailExpress\SkyLink\Eds\ChangeSetDeserialiserFactory $changeSetDeserializer */
        $changeSetDeserialiser = $this->changeSetDeserialiserFactory->create();

        try {
            $changeSets = $changeSetDeserialiser->deserialise($payload);
        } catch (InvalidArgumentException $e) {
            return $jsonResult
                ->setHttpResponseCode(422)
                ->setData([
                    'message' => $e->getMessage(),
                ]);
        }

        $responses = [];
        array_walk($changeSets, function (ChangeSet $changeSet) use (&$responses) {
            try {
                $this->changeSetRepository->find($changeSet->getId());

                // Being idempotent, we'll accept the same Change Set twice without bitching
                $responses[] = [
                    'change_set' => (string) $changeSet->getId(),
                    'status' => 202, // 202 Accepted
                    'message' => 'The Change Set has already been registered.',
                ];
            } catch (NoSuchEntityException $e) {
                $this->changeSetRepository->save($changeSet);
                $responses[] = [
                    'change_set' => (string) $changeSet->getId(),
                    'status' => 201, // 202 Created
                    'message' => 'The Change Set has been registered.',
                ];
            }
        });

        return $jsonResult
            ->setHttpResponseCode($this->determineHttpStatusFromResponses($responses))
            ->setData($responses);

        return $jsonResult;
    }

    private function determineHttpStatusFromResponses(array $responses)
    {
        // Rather than set a priority, we will just return the lower status because
        // we either use 201 or 202, and 201 is the preferred if required.
        return min(array_map(function (array $response) {
            return $response['status'];
        }, $responses));
    }
}
