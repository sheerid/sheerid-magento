<?php
class SheerID_Verify_Model_Rule_Condition_Verified extends Mage_SalesRule_Model_Rule_Condition_Address {
	public function loadAttributeOptions() {
		parent::loadAttributeOptions();
		$options = $this->getAttributeOption();
		
		$options['sheerid'] = Mage::helper('sheerid_verify')->__('SheerID Verified Affiliation Status');
		
		$this->setAttributeOption($options);
        return $this;
    }

    public function getInputType() {
		if ('sheerid' == $this->getAttribute()) {
			return 'select';
		}
      	return parent::getInputType();
    }

    public function getValueElementType() {
        if ('sheerid' == $this->getAttribute()) {
			return 'select';
		}
      	return parent::getInputType();
    }

	public function getValueSelectOptions()
    {
		if ('sheerid' == $this->getAttribute()) {
			$opts = array();
			
			$rest_helper = Mage::helper('sheerid_verify/rest');
			$types = $rest_helper->getService()->listAffiliationTypes();
			foreach ($types as $typeStr) {
				$opts[] = array('value' => $typeStr, 'label' => Mage::helper('sheerid_verify')->__($typeStr));
			}
			
			usort($opts, array($this, "compare"));
			
			$this->setData('value_select_options', $opts);
			return $this->getData('value_select_options');
		}
		return parent::getValueSelectOptions();
    }

	function compare($a, $b) {
	    if ($a['label'] > $b['label']) {
			return 1;
		} else if ($a['label'] < $b['label']) {
			return -1;
		} else {
			return 0;
		}
	}

    public function validate(Varien_Object $object)
    {
		if ('sheerid' == $this->getAttribute()) {
			$quote = $object->getQuote();
			
			if ($quote->getSheeridRequestId() && $quote->getSheeridResult()) {
				$affiliations = explode(",", $quote->getSheeridAffiliations());
				return false !== array_search($this->getValue(), $affiliations);
			} else {
				return false;
			}
		}
        return parent::validate($address);
    }
}