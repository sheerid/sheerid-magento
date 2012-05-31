<?php
class SheerID_Verify_Model_System_Config_Source_VerifyInCheckout
{

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
        	array('value' => "false", 'label'=>Mage::helper('adminhtml')->__('No')),
            array('value' => "true", 'label'=>Mage::helper('adminhtml')->__('Yes')),
            array('value' => "cookie", 'label'=>Mage::helper('adminhtml')->__('Cookie-Dependent')),
        );
    }

}