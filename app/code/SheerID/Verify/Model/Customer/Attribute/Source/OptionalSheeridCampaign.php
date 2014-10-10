<?php
class SheerID_Verify_Model_Customer_Attribute_Source_OptionalSheeridCampaign extends SheerID_Verify_Model_Customer_Attribute_Source_SheeridCampaign
{
    public function getAllOptions() {
		$opts = parent::getAllOptions();
		array_unshift($opts, array('value' => '', 'label' => ' -- None -- '));
		return $opts;
	}

}