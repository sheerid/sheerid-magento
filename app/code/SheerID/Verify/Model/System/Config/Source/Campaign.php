<?php
class SheerID_Verify_Model_System_Config_Source_Campaign
{
    public function toOptionArray() {
		$opts = array();
		$opts[] = array('value' => '', 'label' => ' [None]');
		$SheerID = Mage::helper('sheerid_verify/rest')->getService();

		if ($SheerID) {
			$templates = $SheerID->getJson('/template');
			foreach ($templates as $tmpl) {
				$opts[] = array('value' => $tmpl->id, 'label' => $tmpl->name);
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
