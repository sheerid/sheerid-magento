<?php

require_once Mage::getModuleDir('controllers', 'Mage_Checkout') . DS . 'CartController.php';

class SheerID_Verify_CartController extends Mage_Checkout_CartController {

    protected function _initProduct() {
		$product = parent::_initProduct();
		if ($product) {
			$required_affiliations_str = $product->getData('sheerid_require_verification');
			if ($required_affiliations_str) {
				$required_affiliations = explode(',', $required_affiliations_str);
				$helper = Mage::helper('sheerid_verify');
				$my_affiliations = $helper->getSheeridAffiliations();
				$friendly_names = array();
				foreach ($required_affiliations as $type) {
					if (in_array($type, $my_affiliations)) {
						return $product;
					}
					$friendly_names[] = $this->__($type);
				}
				$campaign = $product->getData('sheerid_campaign');
				if (!$campaign) {
					$campaign = $helper->getDefaultCampaignId();
				}
				$message = $this->__('This product can only be purchased by customers verified as one of: %s.', implode(', ', $friendly_names));
				if ($campaign && $helper->campaignContainsAffiliations($campaign, $required_affiliations)) {
					$message .= '<br/><a href="javascript:sheerIdVerifyLightbox(\''. $campaign .'\', ' . $product->getId() . ')">' . $this->__('Get Verified') . '</a>';
				}
				Mage::getSingleton('core/session')->addError($message);
				return false;
			}
		}
		return $product;
    }

}