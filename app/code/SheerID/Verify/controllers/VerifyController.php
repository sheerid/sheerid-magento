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
		if ($this->getRequest()->getParam('form_only') == 'true') {
			$block->setFormOnly(true);
		}
		if ($this->getRequest()->getParam('submit') == 'false') {
			$block->setSubmit(false);
		}
		if ($this->getRequest()->getParam('use_ajax') == 'false') {
			$block->setUseAjax(false);
		}
		if ($this->getRequest()->getParam('use_quote_information') == 'true') {
			$block->setUseQuoteInformation(true);
		}
		$this->getResponse()->setBody($block->toHtml());
	}
	
	public function verifyAction() {
		$helper = Mage::helper('sheerid_verify');
		$quote = $helper->getCurrentQuote();
		$verify_result = $helper->handleVerifyPost($this->getRequest(), $this->getResponse(), $quote);
		if (!$verify_result["result"]) {
			if ($verify_result["awaiting_upload"]) {
				$errors =  array($this->__("Please upload supporting documentation to continue the verification process."));
			} else {
				$errors =  array($this->__("Unable to verify. Please check that your information is correct."));
			}
			$resp = array("result" => false, "errors" => $errors, "allow_upload" => $verify_result['requestId'] && $helper->allowUploads());
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
			$this->redirectToHome();
			exit;
		}
	}

	public function couponAction() {
		$code = $this->getRequest()->getParam("coupon");
		$affiliations = array();
		$constraints = array();
		if ($code) {
			$coupon = Mage::getModel('salesrule/coupon')->load($code, 'code');
			$rule = Mage::getModel('salesrule/rule')->load($coupon->getRuleId());
			if ($rule->getId()) {
				$cart = Mage::getSingleton('checkout/cart');
				if (!$rule->validate($cart)) {
					$conds = $rule->getConditions();
					foreach ($conds->getConditions() as $c) {
						$attr = $c->getAttribute();
						if ('sheerid' == $attr) {
							if (!array_key_exists('affiliations', $constraints)) {
								$constraints['affiliations'] = array();
							}
							if (!in_array($constraints['affiliations'], $c->getValue())) {
								$constraints['affiliations'][] = $c->getValue();
							}
						} else if ('sheerid_campaign' == $attr) {
							$constraints['campaign'] = $c->getValue();
						}
					}
				}
			}
		}

		$this->getResponse()
			->clearHeaders()
			->setHeader('Content-Type', 'application/json')
			->setBody(json_encode($constraints));
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

	public function claimAction() {
		$requestId = $this->getRequest()->getParam("requestId");
		$product = $this->getRequest()->getParam("product");
		$coupon = $this->getRequest()->getParam("coupon");
		$helper = Mage::helper('sheerid_verify');
		$SheerID = Mage::helper('sheerid_verify/rest')->getService();
		if (!$SheerID || !$requestId) {
			return $this->redirectToHome();
		}
		$resp = $SheerID->inquire($requestId);
		if (!$resp) {
			return $this->redirectToHome();
		}
		if ($resp->request->metadata->orderId) {
			$this->redirectToCart($this->__('This offer has already been claimed.'), 'error');
		} else {
			$helper->saveResponseToQuote($helper->getCurrentQuote(), $resp);
			if ('PENDING' == $resp->status) {
				$this->redirectToCart($this->__('Verification is still pending. Please try again later.'), 'info');
			} else if ($resp->result) {
				$this->redirectToCart($this->__('You have been successfully verified for this offer.'), 'success', $product, $coupon);
			} else {
				$this->redirectToCart($this->__('We were unable to successfully verify you for this offer.'), 'error');
			}
		}
	}

	private function redirectToHome() {
		 Mage::app()->getResponse()->setRedirect(Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB))->sendResponse();
	}

	private function redirectToCart($message="", $severity="info", $product=null, $coupon=null) {
		if ($message) {
			if ("error" == $severity) {
				Mage::getSingleton('checkout/session')->addError($message);
			} else if ("success" == $severity) {
				Mage::getSingleton('checkout/session')->addSuccess($message);
			} else {
				Mage::getSingleton('checkout/session')->addNotice($message);
			}
			session_write_close();
		}
		if ($product) {
			$this->_redirect('checkout/cart/add', array('_query' => "product=$product"));
		} else if ($coupon) {
			$this->_redirect('checkout/cart/couponPost', array('_query' => "coupon_code=$coupon"));
		} else {
			$this->_redirect('checkout/cart');
		}
	}
}
