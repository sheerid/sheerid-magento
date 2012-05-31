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
			$errors =  array($this->__("Unable to verify.  Please check that your information is correct."));
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
				$resp['message'] = $this->__('<p><strong>Success!</strong> Click <a href="%s">here</a> to continue shopping.</p>', Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB));
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
	
	public function organizationFieldAction() {
		$type = $this->getRequest()->getParam("type");
		$orgs = Mage::helper('sheerid_verify/rest')->getService()->listOrganizations($type);
		?>
		var data = <?php echo json_encode($orgs); ?>;
		if (SheerIDOrganizationFields) {
			for (var i=0; i<SheerIDOrganizationFields.length; i++) {
				var el = SheerIDOrganizationFields[i];
				var m = el.className.match(/sheerid-orgs-(\w+)/);
				var type = null;
				if (m) {
					type = m[1];
				}
				  el.type = 'hidden';
				  var html = '<select>';
				  html += '<option value="">-- Select one --</option>';
				  for (var i=0; i<data.length; i++) {
				    var o = data[i];
					if (!type || o.type == type.toUpperCase()) {
				    	html += '<option value="' + o.id + '">'+o.name+'</option>';
					}
				  }
				  html += '</select>';
				  var sp = document.createElement('span');
				  sp.innerHTML = html;
				  var sel = sp.children[0];

				  var copy_attrs = ['id','name','class'];
				  for (var i=0; i<copy_attrs.length; i++) {
				    var v = el.getAttribute(copy_attrs[i]);
				    if (v && v != 'null') {
				      sel.setAttribute(copy_attrs[i], v);
				    }
				  }
				  el.parentNode.replaceChild(sel, el);
			}
		}
		<?php
	}
}
