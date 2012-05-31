<?php
class SheerID_Verify_Model_System_Config_Source_Noyes
{

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
        	array('value' => 0, 'label'=>Mage::helper('adminhtml')->__('No')),
            array('value' => 1, 'label'=>Mage::helper('adminhtml')->__('Yes')),
        );
    }

}
