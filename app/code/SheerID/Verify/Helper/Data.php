<?php
class SheerID_Verify_Helper_Data extends Mage_Core_Helper_Abstract
{
	public function handleVerifyPost($request, $response, $quote) {
		if ($request->isPost()) {
			$post_data = $request->getPost();
			$verify = $post_data['verify'];

			$organizationId = $verify['school'];
			$dob = $verify['birth_year']."-".$verify['birth_month']."-".$verify['birth_day'];

			$ba = $quote->getBillingAddress();
			if ($ba) {
				$firstName = $ba->getFirstname();
				$lastName = $ba->getLastname();
			}
			
			$ALLOW_NAME = true;
			if ($ALLOW_NAME && $verify['firstName']) {
				$firstName = $verify['firstName'];
			}
			if ($ALLOW_NAME && $verify['lastName']) {
				$lastName = $verify['lastName'];
			}
			
			$data = array();
			$data["FIRST_NAME"] = $firstName;
			$data["LAST_NAME"] = $lastName;
			$data["BIRTH_DATE"] = $dob;
			
			if ($verify['affiliation_types']) {
				//TODO: use config object
				$data["_affiliationTypes"] = $verify['affiliation_types'];
			}

			$rest_helper = Mage::helper('sheerid_verify/rest');
			$SheerID = $rest_helper->getService();

			$result = array();

			try {
				$resp = $SheerID->verify($data, $organizationId);
				$result["result"] = $resp->result;
			} catch (Exception $e) {
				$result["error"] = true;
				$result['message'] = $e->getMessage();
			}

			$this->saveResponseToQuote($quote, $resp);
			$this->saveResponseToCustomer($resp);

			return $result;
        }
	}

	public function saveResponseToQuote($quote, $resp) {
		if ($resp) {
			$affs = array();
			if ($resp->affiliations) {
				foreach ($resp->affiliations as $aff) {
					$affs[] = $aff->type;
				}
			}

			$quote->setSheeridRequestId($resp->requestId);
			$quote->setSheeridResult($resp->result);
			$quote->setSheeridAffiliations(implode(",", $affs));
			$quote->save();
		}
	}
	
	public function saveResponseToCustomer($resp) {
		echo "saving";
		if (Mage::getSingleton('customer/session')->isLoggedIn()) {
			$cust = Mage::getSingleton('customer/session')->getCustomer();
			if ($cust) {
				$affs = explode(",", $cust->getSheeridAffiliations());
				$reqs = explode(",", $cust->getSheeridRequestIds());
				if ($resp->affiliations) {
					foreach ($resp->affiliations as $aff) {
						$affs[] = $aff->type;
					}
				}
				$reqs[] = $resp->requestId;
				$cust->setSheeridAffiliations(implode(",", array_unique($affs)));
				$cust->setSheeridRequestIds(implode(",", array_unique($reqs)));
				$cust->save();
				
				echo $cust->getSheeridAffiliations();
			}
		}
	}
	
	public function shouldShowInCheckout() {
		$show_in_checkout = $this->getSetting("show_in_checkout");
		$cookie_name = $this->getSetting("show_in_checkout_cookie_name");
		$quote = Mage::getSingleton('checkout/cart')->getQuote();

		if ("false" == $show_in_checkout || $quote->getSheeridResult() == 1) {
			return false;
		} else if ("true" == $show_in_checkout) {
			return true;
		} else {
			$val = $_COOKIE[$cookie_name];
			return !!$val;
		}
	}
	
	public function getSetting($key) {
		return Mage::getStoreConfig("sheerid_options/settings/$key");
	}
}
