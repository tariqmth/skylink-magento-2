<?php

namespace RetailExpress\SkyLink\Model\Eav\Entity\Attribute\Source;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;
use RetailExpress\SkyLink\Model\Outlets\PickupGroup as BasePickupGroup;

class PickupGroup extends AbstractSource
{
    /**
     * {@inheritdoc}
     */
    public function getAllOptions()
    {
        if (null === $this->_options) {
            $this->_options = array_map(function ($value) {
                return [
                    'value' => $value,
                    'label' => BasePickupGroup::get($value)->getUserSelectableLabel(),
                ];
            }, BasePickupGroup::getUserSelectableValues());
        }

        return $this->_options;
    }

    /**
     * {@inheritdoc}
     */
    public function getOptionArray()
    {
        $options = [];

        array_map(function (array $option) use (&$options) {
            $options[$option['value']] = $option['label'];
        }, $this->getAllOptions());

        return $options;
    }
}
