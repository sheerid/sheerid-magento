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
		$sandbox = Mage::getStoreConfig('sheerid_options/settings/sandbox') == "1";
		$hostname = $sandbox ? 'verify-demo.sheerid.com' : 'verify.sheerid.com';
		$claimUrl = $this->getSuccessUrl();
		return "https://$hostname/verify/$templateId/?metadata[returnUrl]=$claimUrl";
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

	public function getDefaultCampaignId() {
		return $this->getSetting("default_campaign");
	}

	public function getSuccessUrl() {
		return Mage::getUrl('SheerID/Verify/claim');
	}

	public function getService() {
		return Mage::helper('sheerid_verify/rest')->getService();
	}
}
