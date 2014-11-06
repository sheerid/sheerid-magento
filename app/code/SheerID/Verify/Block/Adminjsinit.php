<?php
class SheerID_Verify_Block_Adminjsinit extends Mage_Adminhtml_Block_Template
{

    /**
     * Print admin JS script into body
     * @return string
     */
    protected function _toHtml()
    {
        $section = $this->getAction()->getRequest()->getParam('section', false);

        if ($section == 'sheerid_options') {
            return parent::_toHtml();
        } else {
            return '';
        }
    }
}
