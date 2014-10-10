<?php
$installer = $this;

$installer->startSetup();

$setup = new Mage_Sales_Model_Mysql4_Setup('sales_setup');
#$setup = new Mage_Eav_Model_Entity_Setup('core_setup');

$entities = array("quote", "order");

foreach ($entities as $entity) {
	/**
	 * sheerid_request_id Field
	 **/

	$installer->getConnection()->addColumn(
	    $installer->getTable("sales_flat_${entity}"),
	    'sheerid_request_id',
	    'varchar(36) NULL DEFAULT NULL'
	);
	$setup->addAttribute($entity, 'sheerid_request_id', array('type' => 'static', 'visible' => false));

	/**
	 * sheerid_result Field
	 **/

	$installer->getConnection()->addColumn(
	    $installer->getTable("sales_flat_${entity}"),
	    'sheerid_result',
	    'smallint(5) unsigned NULL DEFAULT NULL'
	);
	$setup->addAttribute($entity, 'sheerid_result', array(
		'type' => 'static',
		'input' => 'boolean',
		'visible' => false
	));

	/**
	 * sheerid_affiliations Field
	 **/

	$installer->getConnection()->addColumn(
	    $installer->getTable("sales_flat_${entity}"),
	    'sheerid_affiliations',
	    'varchar(255) NULL DEFAULT NULL'
	);
	$setup->addAttribute($entity, 'sheerid_affiliations', array('type' => 'static', 'visible' => false));
}

$store = Mage::app()->getStore(Mage_Core_Model_App::ADMIN_STORE_ID);

$core_setup = new Mage_Eav_Model_Entity_Setup('core_setup');
$core_setup->addAttribute('customer', 'sheerid_affiliations', array(
    'type'              => 'varchar',
    'backend'           => '',
    'frontend'          => '',
	'label'				=> 'SheerID Verified Affiliations',
    'class'             => '',
    'input'             => 'multiselect',
    'source'            => 'SheerID_Verify_Model_Customer_Attribute_Source_Affiliationtype',
    'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
    'visible'           => true,
    'required'          => false,
    'user_defined'      => false,
    'default'           => '',
    'searchable'        => false,
    'filterable'        => false,
    'comparable'        => false,
    'visible_on_front'  => false,
    'unique'            => false,
	'website'			=> $store->getWebsite()
));

$core_setup->addAttribute('catalog_product', 'sheerid_require_verification', array(
	'type'                    => 'varchar',
	'group'                   => 'SheerID Settings',
	'backend'                 => 'eav/entity_attribute_backend_array',
	'frontend'                => '',
	'input'                   => 'multiselect',
	'label'                   => 'Required Affiliation Type(s)',
	'note'                    => 'If at least one affiliation type is selected, verification will be required to add this product to the cart. If more than one selection is made, verification of ANY of the selected types will be sufficient to purchase.',
	'class'                   => '',
	'source'                  => 'SheerID_Verify_Model_Customer_Attribute_Source_Affiliationtype',
	'global'                  => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
	'visible'                 => true,
	'required'                => false,
	'user_defined'            => false,
	'default'                 => '',
	'used_in_product_listing' => 1,
	'is_used_for_promo_rules' => 1
));

$core_setup->addAttribute('catalog_product', 'sheerid_campaign', array(
	'type'                    => 'varchar',
	'group'                   => 'SheerID Settings',
	'backend'                 => '',
	'frontend'                => '',
	'input'                   => 'select',
	'label'                   => 'Verification Campaign',
	'note'                    => 'If a user attempts to add this product to the cart before being verified for one of the selected types above, they will be directed to this campaign to be verified. Note that this campaign should be configured to verify the affiliation(s) selected above. If no selection is made, un-verified users will be presented with an error message when attempting to purchase, but no call to action.',
	'class'                   => '',
	'source'                  => 'SheerID_Verify_Model_Customer_Attribute_Source_OptionalSheeridCampaign',
	'global'                  => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
	'visible'                 => true,
	'required'                => false,
	'user_defined'            => false,
	'default'                 => '',
	'used_in_product_listing' => 1,
	'is_used_for_promo_rules' => 1
));

$eavConfig = Mage::getSingleton('eav/config');
$attribute = $eavConfig->getAttribute('customer', 'sheerid_affiliations');
$attribute->setData('used_in_forms', array('adminhtml_customer'));
$attribute->setData('sort_order', '900');
$attribute->setData('is_used_for_customer_segment', true);
$attribute->save();

$installer->endSetup();