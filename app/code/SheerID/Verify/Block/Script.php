<?php
class SheerID_Verify_Block_Script extends Mage_Core_Block_Template
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('verify/verify-footer.phtml');
    }
}