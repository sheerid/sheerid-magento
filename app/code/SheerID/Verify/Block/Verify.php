<?php
class SheerID_Verify_Block_Verify extends Mage_Core_Block_Template
{	
    protected function _construct()
    {
        parent::_construct();
		if (Mage::helper('sheerid_verify')->isSetUp()) {
	        $this->setTemplate('verify/verify.phtml');
		}
    }
}