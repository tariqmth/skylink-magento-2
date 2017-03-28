<?php

namespace RetailExpress\SkyLink\Model\System\Config\Source;

use Magento\Customer\Model\Config\Share;

class CustomerScope extends Share
{
    public function toOptionArray()
    {
        // Remove website scope
        return array_diff_key(parent::toOptionArray(), array_flip([Share::SHARE_WEBSITE]));
    }
}
