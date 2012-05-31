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

$installer->endSetup();