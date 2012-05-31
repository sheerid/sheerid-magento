<?php
class SheerID_Verify_Block_Verify extends Mage_Core_Block_Template
{	
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('verify/verify.phtml');
    }
}