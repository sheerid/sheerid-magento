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

	public function testAction() {
		$helper = Mage::helper('sheerid_verify');
		$result = $helper->isAccessTokenValid();
		$this->getResponse()
			->clearHeaders()
			->setHeader('Content-Type', 'application/json')
			->setBody(json_encode(array("result" => $result)));
	}

	public function dismissAction() {
		$this->getResponse()
			->clearHeaders()
			->setHeader('Content-Type', 'text/html')
			->setBody('<html></html>');
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
