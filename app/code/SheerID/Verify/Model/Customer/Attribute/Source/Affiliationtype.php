<?php
class SheerID_Verify_Model_Customer_Attribute_Source_Affiliationtype  extends Mage_Eav_Model_Entity_Attribute_Source_Table
{
    public function getAllOptions() {
		$opts = array();
		$SheerID = Mage::helper('sheerid_verify/rest')->getService();
		
		if ($SheerID) {
			$types = $SheerID->listAffiliationTypes();
			foreach ($types as $typeStr) {
				$opts[] = array('value' => $typeStr, 'label' => Mage::helper('sheerid_verify')->__($typeStr));
			}
		
			usort($opts, array($this, "compare"));
		}
		
		return $opts;
	}
	
	function compare($a, $b) {
	    if ($a['label'] > $b['label']) {
			return 1;
		} else if ($a['label'] < $b['label']) {
			return -1;
		} else {
			return 0;
		}
	}

}