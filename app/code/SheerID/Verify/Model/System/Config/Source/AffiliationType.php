<?php
class SheerID_Verify_Model_System_Config_Source_AffiliationType
{
    public function toOptionArray() {
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
