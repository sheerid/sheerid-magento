<?php
class SheerID_Verify_Model_Admin_Config_Observer {
	public function on_config_change() {
		$helper = Mage::helper('sheerid_verify');
		$reward = $helper->getReward();
		if (!$reward) {
			try {
				$reward = $helper->createReward();
			} catch (Exception $e) {
				$msg = 'Unable to create reward.';
				Mage::throwException(Mage::helper('adminhtml')->__($msg));
			}
		}
	}
}