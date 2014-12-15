<?php
class SheerID_Verify_VerifyController extends Mage_Core_Controller_Front_Action
{

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
							if (!in_array($affiliations, $c->getValue())) {
								$affiliations[] = $c->getValue();
							}
						} else if ('sheerid_campaign' == $attr) {
							$constraints['campaign'] = $c->getValue();
						}
					}
					$helper = Mage::helper('sheerid_verify');
					if (!array_key_exists('campaign', $constraints)) {
						$constraints['campaign'] = $helper->getDefaultCampaignId();
					}
					// Test that campaign can verify for the required affiliations, to prevent an unnecessary verification
					if (!$helper->campaignContainsAffiliations($constraints['campaign'], $affiliations)) {
						$constraints['campaign'] = null;
					}
				}
			}
		}

		$this->getResponse()
			->clearHeaders()
			->setHeader('Content-Type', 'application/json')
			->setBody(json_encode($constraints));
	}

	public function productAction() {
		$productId = (int) $this->getRequest()->getParam('product');
		$product = null;
		if ($productId) {
			$product = Mage::getModel('catalog/product')
				->setStoreId(Mage::app()->getStore()->getId())
				->load($productId);
		}
		$constraints = array();
		if ($product) {
			$helper = Mage::helper('sheerid_verify');
			$unmet_requirements = $helper->getUnmetPurchaseRequirements($product);
			if ($unmet_requirements && $unmet_requirements['campaign']) {
				$constraints['campaign'] = $unmet_requirements['campaign'];
			}
		}

		$this->getResponse()
			->clearHeaders()
			->setHeader('Content-Type', 'application/json')
			->setBody(json_encode($constraints));
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
			// Persist the response in the quote
			$helper->saveResponseToQuote($helper->getCurrentQuote(), $resp);

			// Construct the relevant messaging, add to session
			if ('PENDING' == $resp->status) {
				$this->addMessage($this->__('Verification is still pending. Please try again later.'), 'info');
			} else if ($resp->result) {
				$this->addMessage($this->__('You have been successfully verified for this offer.'), 'success');
			} else {
				$this->addMessage($this->__('We were unable to successfully verify you for this offer.'), 'error');
			}
			session_write_close();

			// Route the user to the appropriate location
			if ('dismiss' == $resp->request->metadata->action) {
				$opts = array();
				if ($product) {
					$opts["_query"] = "product=$product";
				}
				$this->_redirect('SheerID/verify/dismiss', $opts);
			} else if ($resp->result) {
				$this->redirectToCart($product, $coupon);
			} else {
				$this->redirectToCart();
			}
		}
	}

	public function testAction() {
		$helper = Mage::helper('sheerid_verify');
		$result = $helper->isAccessTokenValid();
		$this->getResponse()
			->clearHeaders()
			->setHeader('Content-Type', 'application/json')
			->setBody(json_encode(array("result" => $result)));
	}

	public function dismissAction() {
		$productId = (int) $this->getRequest()->getParam('product');
		if ($productId) {
			$cartUrl = Mage::getUrl('checkout/cart/add', array('_query' => "product=$productId"));
		} else {
			$cartUrl = Mage::getUrl('checkout/cart');
		}
		$this->getResponse()
			->clearHeaders()
			->setHeader('Content-Type', 'text/html')
			->setBody("<html><script>if (window.top == window) { window.location = '$cartUrl'; }</script></html>");
	}

	private function redirectToHome() {
		 Mage::app()->getResponse()->setRedirect(Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB))->sendResponse();
	}

	private function addMessage($message, $severity="info") {
		if ($message) {
			$session = Mage::getSingleton('checkout/session');
			if ("error" == $severity) {
				$session->addError($message);
			} else if ("success" == $severity) {
				$session->addSuccess($message);
			} else {
				$session->addNotice($message);
			}
		}
	}

	private function redirectToCart($product=null, $coupon=null) {
		if ($product) {
			$this->_redirect('checkout/cart/add', array('_query' => "product=$product"));
		} else if ($coupon) {
			$this->_redirect('checkout/cart/couponPost', array('_query' => "coupon_code=$coupon"));
		} else {
			$this->_redirect('checkout/cart');
		}
	}
}
