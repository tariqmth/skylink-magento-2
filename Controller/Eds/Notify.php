<?php

namespace RetailExpress\SkyLink\Controller\Eds;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory as JsonResultFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use RetailExpress\SkyLink\Api\Eds\ChangeSetRepositoryInterface;
use RetailExpress\SkyLink\Eds\ChangeSet;
use RetailExpress\SkyLink\Eds\ChangeSetDeserialiser;

class Notify extends Action
{
    private $changeSetDeserialiser;

    private $changeSetRepository;

    private $jsonResultFactory;

    public function __construct(
        Context $context,
        ChangeSetDeserialiser $changeSetDeserialiser,
        ChangeSetRepositoryInterface $changeSetRepository,
        JsonResultFactory $jsonResultFactory
    ) {
        parent::__construct($context);

        $this->changeSetDeserialiser = $changeSetDeserialiser;
        $this->changeSetRepository = $changeSetRepository;
        $this->jsonResultFactory = $jsonResultFactory;
    }

    public function execute()
    {
        // Firstly, parse the body that's been sent through
        $payload = $this->getRequest()->getContent();
        $changeSets = $this->changeSetDeserialiser->deserialise($payload);

        $jsonResult = $this->jsonResultFactory->create();

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
