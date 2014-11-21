<?php
class SheerID_Verify_Block_Widget extends Mage_Core_Block_Template
{	
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('verify/verify-widget.phtml');
    }

    public function getTitle() {
       $title = parent::getTitle();
       if (!$title) {
          return $this->__("Verify");
       }
       return $title;
    }

	public function isOnCartPage() {
		$request = $this->getRequest();
		$module = $request->getModuleName();
		$controller = $request->getControllerName();
		$action = $request->getActionName();
		
		return ($module == 'checkout' && $controller == 'cart' && $action == 'index');
	}

}
