<?php

namespace RetailExpress\SkyLinkMagento2\Model\Verification;

interface Check
{
    /**
     * Get the group slug for the check.
     *
     * @return GroupSlug
     */
    public function getGroupSlug();

    /**
     * Get the sort order for the check within it's group.
     *
     * @return \ValueObjects\Number\Integer
     */
    public function getSortOrder();

    /**
     * Get a localised name of the given check.
     *
     * @return \Magento\Framework\Phrase
     */
    public function getLocalisedName();

    /**
     * Tell if the check passses or not.
     *
     * @return bool
     */
    public function passes();

    /**
     * Get an array of localised errors, populated if the check does not pass.
     *
     * @return Magento\Framework\Phrase[]
     */
    public function getLocalisedErrors();
}
