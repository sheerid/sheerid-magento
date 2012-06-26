<?php
class SheerID_Verify_VerifyController extends Mage_Core_Controller_Front_Action
{
	public function indexAction() {
		$helper = Mage::helper('sheerid_verify');
		$config = array("collect_name" => true);
		
		$block = $this->getLayout()->createBlock('sheerid/verify', 'SheerID_Verify');
		
		if ($this->getRequest()->getParam('affiliation_types')) {
			$config['affiliation_types'] = $this->getRequest()->getParam('affiliation_types');
		}
		
		if ($this->getRequest()->getParam('in_cart') == 1) {
			$block->setOnCartPage(true);
		}
		
		echo $block->toHtml();
	}
	
	public function verifyAction() {
		$helper = Mage::helper('sheerid_verify');
		$quote = Mage::getSingleton('checkout/session')->getQuote();
		$verify_result = $helper->handleVerifyPost($this->getRequest(), $this->getResponse(), $quote);
		if (!$verify_result["result"]) {
			$errors =  array($this->__("Unable to verify. Please check that your information is correct."));
			$resp = array("result" => false, "errors" => $errors);
		} else {
			$session = Mage::getSingleton('checkout/session');
			if (!$session->getQuoteId()) {
				$session->setQuoteId($quote->getId());
			}
			
			$resp = array("result" => true);
			
			if ($this->getRequest()->getParam("on_cart_page") == 1){
				$resp['refresh'] = true;
			} else {
				$msg = '<p><strong>';
				$msg .= $this->__('Success!');
				$msg .= '</strong> ';
				$msg .= $this->__('Click <a href="%s">here</a> to continue shopping.', Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB));
				$msg .= '</p>'
				$resp['message'] = $msg;
			}
		}
		if ($this->getRequest()->getParam("ajax")) {
			echo json_encode($resp);
		} else {
			Mage::app()->getResponse()->setRedirect(Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB));
			Mage::app()->getResponse()->sendResponse();
			exit;
		}
	}
	
	public function organizationsAction() {
		$name = $this->getRequest()->getParam("q");
		$type = $this->getRequest()->getParam("type");
		echo json_encode(Mage::helper('sheerid_verify/rest')->getService()->listOrganizations($type, $name));
	}
	
}
