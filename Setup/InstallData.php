<?php

namespace RetailExpress\SkyLink\Setup;

use Magento\Customer\Model\Customer;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class InstallData implements InstallDataInterface
{
    private $customerSetupFactory;

    public function __construct(CustomerSetupFactory $customerSetupFactory)
    {
        $this->customerSetupFactory = $customerSetupFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        // https://github.com/magento/magento2/issues/1238#issuecomment-105034397

        /** @var \Magento\Customer\Setup\CustomerSetup $customerSetup **/
        $customerSetup = $this->customerSetupFactory->create(['setup' => $setup]);

        $customerSetup->addAttribute(
            Customer::ENTITY,
            'skylink_customer_id',
            [
                // @todo optimise arguments (such as "type", "backend")
                'label' => 'SkyLink Customer ID',
                'required' => false,
                'system' => false,
                'position' => 100,
            ]
        );

        $customerSetup
            ->getEavConfig()
            ->getAttribute(Customer::ENTITY, 'skylink_customer_id')
            ->setData('used_in_forms', ['adminhtml_customer'])
            ->save();
    }
}
