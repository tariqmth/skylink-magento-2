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

        array_walk($changeSets, function (ChangeSet $changeSet) use ($jsonResult) {
            try {
                $this->changeSetRepository->find($changeSet->getId());
                $jsonResult
                    ->setHttpResponseCode(202)
                    ->setData(['message' => 'The Change Set has already been registered.']);
            } catch (NoSuchEntityException $e) {
                $this->changeSetRepository->save($changeSet);
                $jsonResult
                    ->setHttpResponseCode(201)
                    ->setData(['message' => 'The Change Set has been registered.']);
            }
        });

        return $jsonResult;
    }
}
