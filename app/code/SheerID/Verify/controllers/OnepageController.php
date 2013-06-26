<?php

require_once 'Mage/Checkout/controllers/OnepageController.php';

class SheerID_Verify_OnepageController extends Mage_Checkout_OnepageController
{
	static $BEFORE_SECTIONS = array('shipping_method','payment');
	
	public function saveBillingAction() {
		parent::saveBillingAction();
		if (Mage::helper('sheerid_verify')->shouldShowInCheckout()) {
			$this->rewriteResponse();
		}
	}
	
	public function saveShippingAction() {
		parent::saveShippingAction();
		if (Mage::helper('sheerid_verify')->shouldShowInCheckout()) {
			$this->rewriteResponse();
		}
	}
	
	public function savePaymentAction() {
		$quote = $this->getOnePage()->getQuote();
		if ($quote->getSheeridRequestId()) {
			$SheerID = Mage::helper('sheerid_verify/rest')->getService();
			if ($SheerID) {
				$resp = $SheerID->inquire($quote->getSheeridRequestId());
				Mage::helper('sheerid_verify')->saveResponseToQuote($quote, $resp);
			}
		}
		parent::savePaymentAction();
	}
	
	public function verifyUploadSuccessAction(){
		// empty - 200 response necessary for upload form
	}
	
	public function verifyUploadFailureAction(){
		// empty - 200 response necessary for upload form
	}
	
	public function verifyUploadTokenAction() {
		$helper = Mage::helper('sheerid_verify');
		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			$quote = $this->getOnePage()->getQuote();
			$requestId = $quote->getSheeridRequestId();
			
			if ($requestId) {
				$SheerID = Mage::helper('sheerid_verify/rest')->getService();
				if ($SheerID) {
					$SheerID->updateMetadata($requestId, array('successUrl' => $helper->getSuccessUrl($requestId)));
					$token = $SheerID->getAssetToken($requestId);
				}
			}
			
			if ($token) {
				$result = array();
				$result['token'] = $token;
				$result['baseUrl'] = $SheerID->url();
				$this->getResponse()->setBody(Zend_Json::encode($result));
			} else {
				$this->getResponse()->setHttpResponseCode(404);
			}
		} else {
			header('Allow: POST');
			$this->getResponse()->setHttpResponseCode(405);
		}
	}
	
    public function saveVerifyAction() {
        $this->_expireAjax();
		$quote = $this->getOnePage()->getQuote();

		$helper = Mage::helper('sheerid_verify');
		$verify_result = $helper->handleVerifyPost($this->getRequest(), $this->getResponse(), $quote);

		$result = array();

		if ($verify_result && $quote->getSheeridRequestId() && $quote->getSheeridResult()) {
			$steps = $this->getOnePage()->getCheckout()->getStepData();
			foreach (SheerID_Verify_OnepageController::$BEFORE_SECTIONS as $stepName) {
				if (array_key_exists($stepName, $steps) && $this->getOnePage()->getCheckout()->getStepData($stepName, 'is_show')) {
					$result['goto_section'] = $stepName;
					break;
				}
			}
		} else {
			$result['goto_section'] = 'verify';
			$result['error'] = true;
			$result['message'] = $verify_result && $verify_result['message'] ? $verify_result['message'] : Mage::helper('sheerid_verify')->__("Unable to verify. Please check that your information is correct.");
		}

		$this->getResponse()->setBody(Zend_Json::encode($result));
    }

	private function rewriteResponse() {
		$body = $this->getResponse()->getBody();
		$result = Zend_Json::decode($body);
		if (false !== array_search($result['goto_section'], SheerID_Verify_OnepageController::$BEFORE_SECTIONS)) {
			$result['goto_section'] = 'verify';
	        	$this->getResponse()->setBody(Zend_Json::encode($result));
		}
	}
}
