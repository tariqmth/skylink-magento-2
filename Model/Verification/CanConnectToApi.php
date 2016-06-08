<?php

namespace RetailExpress\SkyLinkMagento2\Model\Verification;

use RetailExpress\SkyLink\Apis\ApiException;
use RetailExpress\SkyLinkMagento2\Model\Config as SkyLinkConfig;
use RetailExpress\SkyLinkMagento2\Model\RepositoryFactory as SkyLinkRepositoryFactory;
use ValueObjects\Number\Integer;

class CanConnectToApi implements Check
{
    private $skyLinkConfig;

    private $skyLinkRepositoryFactory;

    private $passes;

    private $localisedErrors = [];

    public function __construct(SkyLinkConfig $skyLinkConfig, SkyLinkRepositoryFactory $skyLinkRepositoryFactory)
    {
        $this->skyLinkConfig = $skyLinkConfig;
        $this->skyLinkRepositoryFactory = $skyLinkRepositoryFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function getGroupSlug()
    {
        return new GroupSlug('system');
    }

    /**
     * {@inheritdoc}
     */
    public function getSortOrder()
    {
        return new Integer(100);
    }

    /**
     * {@inheritdoc}
     */
    public function getLocalisedName()
    {
        return __('Can Connect to API');
    }

    /**
     * {@inheritdoc}
     */
    public function passes()
    {
        if (null === $this->passes) {
            try {

                // To test connectivity, we'll just fetch outlets
                $this
                    ->skyLinkRepositoryFactory
                    ->createOutletRepository()
                    ->all($this->skyLinkConfig->getSalesChannelId());

                $this->passes = true;
            } catch (ApiException $e) {
                $this->localisedErrors[] = __($e->getMessage());
                $this->passes = false;
            }
        }

        return $this->passes;
    }

    /**
     * {@inheritdoc}
     */
    public function getLocalisedErrors()
    {
        return $this->localisedErrors;
    }
}
