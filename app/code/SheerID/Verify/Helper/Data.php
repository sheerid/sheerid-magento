<?php
class SheerID_Verify_Helper_Data extends Mage_Core_Helper_Abstract
{

	public function saveResponseToQuote($quote, $resp) {
		if ($quote && $resp) {
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
			
			if ($quote->getCustomer() && $quote->getCustomer()->getId()) {
				$this->saveResponseToCustomer($quote->getCustomer(), $resp);
			}
		}
	}
	
	public function saveResponseToCustomer($cust, $resp) {
		if ($cust && $resp) {
			$affs = array();
			foreach (explode(",", $cust->getSheeridAffiliations()) as $a) {
				if ($a) {
					$affs[] = $a;
				}
			}
			if ($resp->affiliations) {
				foreach ($resp->affiliations as $aff) {
					$affs[] = $aff->type;
				}
			}
			$cust->setSheeridAffiliations(implode(",", array_unique($affs)));
			$cust->save();
		}
	}

	public function getSheeridAffiliations($quote=null) {
		if (!$quote) {
			$quote = $this->getCurrentQuote(false);
		}
		$affiliations = array();
		if ($quote) {
			$affiliations = array_merge($affiliations, explode(',', $quote->getSheeridAffiliations()));
			if ($quote->getCustomer() && $quote->getCustomer()->getId()) {
				$affiliations = array_merge($affiliations, explode(',', $quote->getCustomer()->getSheeridAffiliations()));
			}
		}
		return array_filter(array_unique($affiliations));
	}

	public function isEligibleForCampaign($templateId, $quote=null) {
		$SheerID = Mage::helper('sheerid_verify/rest')->getService();
		try {
			$tmpl = $SheerID->getTemplate($templateId);
			foreach ($this->getSheeridAffiliations($quote) as $type) {
				if (in_array($type, $tmpl->config->affiliationTypes)) {
					return true;
				}
			}
		} catch (Exception $e) {}
		return false;
	}

	/**
	 * Check a product's required affiliations for purchase against verified affiliations.
	 * If SheerID requirements for purchase are not satisfied, return an object containing details on how to proceed.
	 * Returns false if no unmet requirements exist.
	 **/
	public function getUnmetPurchaseRequirements($product) {
		$required_affiliations_str = $product->getData('sheerid_require_verification');
		if ($required_affiliations_str) {
			$required_affiliations = explode(',', $required_affiliations_str);
			$requirements = array("affiliations" => $required_affiliations);
			$my_affiliations = $this->getSheeridAffiliations();
			foreach ($required_affiliations as $type) {
				if (in_array($type, $my_affiliations)) {
					return false;
				}
			}
			$campaign = $product->getData('sheerid_campaign');
			if (!$campaign) {
				$campaign = $this->getDefaultCampaignId();
			}
			if ($campaign && $this->campaignContainsAffiliations($campaign, $required_affiliations)) {
				$requirements["campaign"] = $campaign;
			}
			return $requirements;
		}
		return false;
	}

	public function campaignContainsAffiliations($templateId, $affiliations, $any=true) {
		$SheerID = Mage::helper('sheerid_verify/rest')->getService();
		try {
			$tmpl = $SheerID->getTemplate($templateId);
			$match = false;
			foreach ($affiliations as $type) {
				if (in_array($type, $tmpl->config->affiliationTypes)) {
					$match = true;
				} else if (!$any) {
					return false;
				}
			}
			return $match;
		} catch (Exception $e) {}
		return false;
	}

	public function getCurrentQuote($create=true) {
		$quote = Mage::getSingleton('checkout/cart')->getQuote();
		$session = Mage::getSingleton('checkout/session');
		if (!$session->getQuoteId() && $create) {
			$quote->save();
			$session->setQuoteId($quote->getId());
		}
		return $quote;
	}

	public function getVerifyUrl($templateId) {
		$verifyUrl = $this->getService()->getVerifyUrlFromTemplateId($templateId);
		$claimUrl = $this->getSuccessUrl();
		return "$verifyUrl?metadata[returnUrl]=$claimUrl";
	}

	public function getVerifyUrlByName($name) {
		$verifyUrl = $this->getService()->getVerifyUrlByName($name);
		$claimUrl = $this->getSuccessUrl();
		return "$verifyUrl?metadata[returnUrl]=$claimUrl";
	}
	
	public function getSetting($key) {
		return Mage::getStoreConfig("sheerid_options/settings/$key");
	}
	
	public function getBooleanSetting($key) {
		$val = $this->getSetting($key);
		return $val === 'true' || $val === 1 || $val === '1' || $val === true;
	}

	public function isSetUp() {
		return !!$this->getSetting("access_token");
	}

	public function isAccessTokenValid() {
		$SheerID = $this->getService();
		return $SheerID && $SheerID->isAccessible();
	}

	public function getDefaultCampaignId() {
		return $this->getSetting("default_campaign");
	}

	public function getSuccessUrl() {
		return Mage::getUrl('SheerID/Verify/claim', array('_secure' => true));
	}

	public function getService() {
		return Mage::helper('sheerid_verify/rest')->getService();
	}
}
