<?php

namespace RetailExpress\SkyLink\Model\Verification;

use Magento\Framework\Phrase;
use ValueObjects\Number\Integer;

class Group
{
    private $slug;

    private $sortOrder;

    private $localisedName;

    public static function fromNative($slug, $sortOrder, $localisedName)
    {
        return new self(
            new GroupSlug($slug),
            new Integer($sortOrder),
            __($localisedName)
        );
    }

    public function __construct(GroupSlug $slug, Integer $sortOrder, Phrase $localisedName)
    {
        $this->slug = $slug;
        $this->sortOrder = $sortOrder;
        $this->localisedName = $localisedName;
    }

    /**
     * Get the group's slug.
     *
     * @return GroupSlug
     */
    public function getSlug()
    {
        return clone $this->slug;
    }

    /**
     * Get the sort order.
     *
     * @return \ValueObjects\Number\Integer
     */
    public function getSortOrder()
    {
        return clone $this->sortOrder;
    }

    /**
     * Get the localised name.
     *
     * @return GroupSlug
     */
    public function getLocalisedName()
    {
        return clone $this->localisedName;
    }
}
