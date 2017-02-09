<?php

namespace RetailExpress\SkyLink\Model\Outlets;

use ValueObjects\Enum\Enum;

class PickupGroup extends Enum
{
    const NONE = 'none';
    const ONE = 'one';
    const TWO = 'two';
    const BOTH = 'both';

    /**
     * Get the default Pickup Group.
     *
     * @return PickupGroup
     */
    public static function getDefault()
    {
        return self::get(self::NONE);
    }

    /**
     * Get the user selectable values - user cannot select Pickup Group 2.
     */
    public static function getUserSelectableValues()
    {
        return array_diff(self::getConstants(), [self::TWO]);
    }

    /**
     * Get the lable for the Pickup Group.
     *
     * @return \Magento\Framework\Phrase
     */
    public function getUserSelectableLabel()
    {
        $labels = [
            self::NONE => __('No Pickup Allowed'),
            self::ONE => __('Pickup from Group 1 Only'),
            self::BOTH => __('Pickup from Group 1 or Group 2'),
        ];

        return $labels[$this->getValue()];
    }
}
