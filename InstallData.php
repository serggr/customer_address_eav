<?php
namespace Sg\Shipper\Setup;

use Magento\Customer\Api\AddressMetadataInterface;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Quote\Setup\QuoteSetupFactory;
use Magento\Sales\Setup\SalesSetupFactory;

class InstallData implements InstallDataInterface
{
    /**
     * @var EavSetupFactory
     */
    protected $eavSetupFactory;

    /**
     * @var QuoteSetupFactory
     */
    protected $quoteSetupFactory;

    /**
     * @var SalesSetupFactory
     */
    protected $salesSetupFactory;

    /**
     * @var ModuleDataSetupInterface
     */
    protected $setup;

    /**
     * @param EavSetupFactory $eavSetupFactory
     * @param QuoteSetupFactory $quoteSetupFactory
     * @param SalesSetupFactory $salesSetupFactory
     */
    public function __construct(
        EavSetupFactory $eavSetupFactory,
        QuoteSetupFactory $quoteSetupFactory,
        SalesSetupFactory $salesSetupFactory
    ) {
        $this->eavSetupFactory = $eavSetupFactory;
        $this->quoteSetupFactory = $quoteSetupFactory;
        $this->salesSetupFactory = $salesSetupFactory;
    }

    protected function getAddressLocation()
    {
        return [
            'label' => 'Locationo',
            'type' => 'varchar',
            'input' => 'text',
            'required' => false,
            'sort_order' => 200,
            'position' => 200,
            'system' => true,
            'visible' => true,
        ];
    }

    protected function addAttributeToAllForm($attributeId)
    {
        foreach (['adminhtml_customer_address', 'customer_address_edit', 'customer_register_address'] as $formCode) {
            $this->setup->getConnection()
                ->insertMultiple(
                    $this->setup->getTable('customer_form_attribute'),
                    ['form_code' => $formCode, 'attribute_id' => $attributeId]
                );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $this->setup = $setup;
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

        $eavSetup->addAttribute(AddressMetadataInterface::ENTITY_TYPE_ADDRESS, 'locationo', $this->getAddressLocation());
        $this->quoteSetupFactory->create()->addAttribute('quote_address', 'locationo', ['type' => Table::TYPE_TEXT]);
        $this->salesSetupFactory->create()->addAttribute('order_address', 'locationo', ['type' => Table::TYPE_TEXT]);
        $this->addAttributeToAllForm(
            $eavSetup->getAttributeId(AddressMetadataInterface::ENTITY_TYPE_ADDRESS, 'locationo')
        );
    }
}