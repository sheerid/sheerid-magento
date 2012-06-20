<?php
class SheerID_Verify_Model_Customer_Attribute_Source_Affiliationtype  extends Mage_Eav_Model_Entity_Attribute_Source_Table
{
    public function getAllOptions() {
		$opts = array();
		$rest_helper = Mage::helper('sheerid_verify/rest');
		$types = $rest_helper->getService()->listAffiliationTypes();
		foreach ($types as $typeStr) {
			$opts[] = array('value' => $typeStr, 'label' => Mage::helper('adminhtml')->__($typeStr));
		}
		return $opts;
	}

}