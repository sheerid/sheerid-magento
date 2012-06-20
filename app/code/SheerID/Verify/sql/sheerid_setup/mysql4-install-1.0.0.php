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

$eavConfig = Mage::getSingleton('eav/config');
$attribute = $eavConfig->getAttribute('customer', 'sheerid_affiliations');
$attribute->setData('used_in_forms', array('adminhtml_customer'));
$attribute->setData('sort_order', '900');
$attribute->setData('is_used_for_customer_segment', true);
$attribute->save();

$installer->endSetup();