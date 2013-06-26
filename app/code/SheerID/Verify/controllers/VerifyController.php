<?php
class SheerID_Verify_VerifyController extends Mage_Core_Controller_Front_Action
{
	public function indexAction() {
		$helper = Mage::helper('sheerid_verify');
		$config = array("collect_name" => true);
		
		$block = $this->getLayout()->createBlock('sheerid/verify', 'SheerID_Verify');
		
		if ($this->getRequest()->getParam('affiliation_types')) {
			$block->setAffiliationTypes($this->getRequest()->getParam('affiliation_types'));
		}
		if ($this->getRequest()->getParam('organization_id')) {
			$block->setOrganizationId($this->getRequest()->getParam('organization_id'));
		}
		
		if ($this->getRequest()->getParam('in_cart') == 'true') {
			$block->setOnCartPage(true);
		}
		if ($this->getRequest()->getParam('promo_code')) {
			$block->setPromoCode(true);
		}
		
		echo $block->toHtml();
	}
	
	public function verifyAction() {
		$helper = Mage::helper('sheerid_verify');
		$quote = $helper->getCurrentQuote();
		$verify_result = $helper->handleVerifyPost($this->getRequest(), $this->getResponse(), $quote);
		if (!$verify_result["result"]) {
			$errors =  array($this->__("Unable to verify. Please check that your information is correct."));
			$resp = array("result" => false, "errors" => $errors, "allow_upload" => $helper->getBooleanSetting('allow_uploads'));
		} else {
			$resp = array("result" => true);
			
			if ($this->getRequest()->getParam("on_cart_page") == 1){
				$resp['refresh'] = true;
			} else if ($this->getRequest()->getParam("promo_code") == 1){
				$resp['discountSubmit'] = true;
				$resp['message'] = "The coupon code will now be applied to your cart.";
			} else {
				$msg = '<p><strong>';
				$msg .= $this->__('Success!');
				$msg .= '</strong> ';
				$msg .= $this->__('Click <a href="%s">here</a> to continue shopping.', Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB));
				$msg .= '</p>';
				$resp['message'] = $msg;
			}
		}
		if ($this->getRequest()->getParam("ajax")) {
			$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($resp))->sendResponse();
			exit;
		} else {
			Mage::app()->getResponse()->setRedirect(Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB));
			Mage::app()->getResponse()->sendResponse();
			exit;
		}
	}

	public function couponAction() {
		$code = $this->getRequest()->getParam("coupon");
		$affiliations = array();
		if ($code) {
			$coupon = Mage::getModel('salesrule/coupon')->load($code, 'code');
			$rule = Mage::getModel('salesrule/rule')->load($coupon->getRuleId());
			if ($rule->getId()) {
				$cart = Mage::getSingleton('checkout/cart');
				if (!$rule->validate($cart)) {
					$conds = $rule->getConditions();
					foreach ($conds->getConditions() as $c) {
						$clazz = get_class($c);
						if ("SheerID_Verify_Model_Rule_Condition_Verified" == $clazz) {
							$affiliations[] = $c->getValue();
						}
					}
				}
			}
		}

		$this->getResponse()
			->clearHeaders()
			->setHeader('Content-Type', 'application/json')
			->setBody(json_encode(array_unique($affiliations)));
	}
	
	public function uploadTokenAction() {
		$helper = Mage::helper('sheerid_verify');
		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			$quote = $helper->getCurrentQuote();
			$requestId = $quote->getSheeridRequestId();
			
			if ($requestId) {
				$SheerID = Mage::helper('sheerid_verify/rest')->getService();
				if ($SheerID) {
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
	
	public function verifyUploadSuccessAction(){
		// empty - 200 response necessary for upload form
	}
	
	public function verifyUploadFailureAction(){
		// empty - 200 response necessary for upload form
	}
}
