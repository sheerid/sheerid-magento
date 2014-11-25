<?php
class SheerID_Verify_Model_System_Config_Source_OptionalSheeridCampaign extends SheerID_Verify_Model_System_Config_Source_SheeridCampaign
{

	// Allow as backend config source
	public function toOptionArray() {
		return $this->getAllOptions();
	}

    public function getAllOptions() {
		$opts = parent::getAllOptions();
		array_unshift($opts, array('value' => '', 'label' => ' -- None -- '));
		return $opts;
	}

}