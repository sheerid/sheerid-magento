<?php

require_once Mage::getModuleDir('controllers', 'Mage_Checkout') . DS . 'CartController.php';

class SheerID_Verify_CartController extends Mage_Checkout_CartController {

    protected function _initProduct() {
		$product = parent::_initProduct();
		if ($product) {
			$required_affiliations = $product->getData('sheerid_require_verification');
			if ($required_affiliations) {
				$helper = Mage::helper('sheerid_verify');
				$my_affiliations = $helper->getSheeridAffiliations();
				$friendly_names = array();
				foreach (explode(',', $required_affiliations) as $type) {
					if (in_array($type, $my_affiliations)) {
						return $product;
					}
					$friendly_names[] = $this->__($type);
				}
				$message = $this->__('This product can only be purchased by customers verified as one of: %s.', implode(', ', $friendly_names));
				Mage::getSingleton('core/session')->addError($message);
				return false;
			}
		}
		return $product;
    }

}