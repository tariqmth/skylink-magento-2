<?php

namespace RetailExpress\SkyLinkMagento2\Model\Verification;

use Magento\Framework\Event\ManagerInterface as EventManagerInterface;
use OutOfBoundsException;

class Verifier
{
    private $eventManager;

    private $groups = [];

    private $checks = [];

    public function __construct(EventManagerInterface $eventManager)
    {
        $this->eventManager = $eventManager;
    }

    /**
     * Gather groups and checks.
     *
     * @return void
     */
    public function gather()
    {
        $this->gatherGroups();
        $this->gatherChecks();
    }

    /**
     * Add a new group to the verifier.
     *
     * @param  Group $group
     * @return void
     */
    public function addGroup(Group $group)
    {
        $this->groups[] = $group;
    }

    /**
     * Get all registered groups.
     *
     * @return array
     */
    public function getGroups()
    {
        return array_map(function (Group $group) {
            return clone $group;
        }, $this->groups);
    }

    /**
     * Get all registered groups, sorted by their priority.
     *
     * @return array
     */
    public function getSortedGroups()
    {
        $groups = $this->getGroups();

        usort($groups, function (Group $groupA, Group $groupB) {
            $groupASortOrder = $groupA->getSortOrder();
            $groupBSortOrder = $groupA->getSortOrder();

            if ($groupASortOrder->sameValueAs($groupBSortOrder)) {
                return 0;
            }

            return $groupASortOrder > $groupBSortOrder ? 1 : -1;
        });

        return $groups;
    }

    /**
     * Determine if the verifier has a group with the given slug.
     *
     * @param  GroupSlug $groupSlug
     * @return bool
     */
    public function hasGroupWithSlug(GroupSlug $groupSlug)
    {
        foreach ($this->getGroups() as $group) {
            if ($group->getSlug()->sameValueAs($groupSlug)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Add a check to the verifier to run
     *
     * @param  Check $check
     * @return void
     */
    public function addCheck(Check $check)
    {
        $groupSlug = $check->getGroupSlug();

        if (!$this->hasGroupWithSlug($groupSlug)) {
            throw new OutOfBoundsException("Cannot add check \"{$check->getLocalisedName()}\" because its group \"{$groupSlug}\" has not been added to the verifier yet.");
        }

        $this->checks[] = $check;
    }

    /**
     * Get all registered checks.
     *
     * @return array
     */
    public function getChecks()
    {
        return $this->checks;
    }

    /**
     * Get All checks for the given group slug.
     *
     * @param  GroupSlug $groupSlug
     * @return Check[]
     */
    public function getChecksForGroupWithSlug(GroupSlug $groupSlug)
    {
        $checks = $this->getChecks();

        return array_filter($checks, function (Check $check) use ($groupSlug) {
            return $check->getGroupSlug()->sameValueAs($groupSlug);
        });
    }

    /**
     * Get all checks for the given group slug, in accordance with their sort order.
     *
     * @param  GroupSlug $groupSlug
     * @return Check[]
     */
    public function getSortedChecksForGroupWithSlug(GroupSlug $groupSlug)
    {
        $checks = $this->getChecksForGroupWithSlug($groupSlug);

        usort($checks, function (Check $checkA, Check $checkB) {
            $checkASortOrder = $checkA->getSortOrder();
            $checkBSortOrder = $checkB->getSortOrder();

            if ($checkASortOrder->sameValueAs($checkBSortOrder)) {
                return 0;
            }

            return $checkASortOrder > $checkBSortOrder ? 1 : -1;
        });

        return $checks;
    }

    /**
     * Gather all groups by dispatching an event whereby subscribers can add their own groups.
     *
     * @return void
     */
    private function gatherGroups()
    {
        $this->eventManager->dispatch('skylink_verification_groups', ['verifier' => $this]);
    }

    /**
     * Gather all checks by dispatching an event whereby subscribers can add their own checks.
     *
     * @return void
     */
    private function gatherChecks()
    {
        $this->eventManager->dispatch('skylink_verification_checks', ['verifier' => $this]);
    }
}
