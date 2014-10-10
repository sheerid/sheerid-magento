<?php
class SheerID_Verify_Model_Rule_Condition_Verified extends Mage_SalesRule_Model_Rule_Condition_Address {

	public function loadAttributeOptions() {
		parent::loadAttributeOptions();
		$options = $this->getAttributeOption();

		$options['sheerid'] = Mage::helper('sheerid_verify')->__('SheerID Verified Affiliation Status');
		$options['sheerid_campaign'] = Mage::helper('sheerid_verify')->__('SheerID Campaign Eligibility');

		$this->setAttributeOption($options);
        return $this;
    }

    public function getInputType() {
		if ('sheerid' == $this->getAttribute()) {
			return 'select';
		} else if ('sheerid_campaign' == $this->getAttribute()) {
			return 'select';
		}
      	return parent::getInputType();
    }

    public function getOperatorSelectOptions() {
        if ('sheerid' == $this->getAttribute()) {
            return array(array('label' => 'is', 'value' => '=='));
        } else if ('sheerid_campaign' == $this->getAttribute()) {
            return array(array('label' => 'is', 'value' => '=='));
        }
        return parent::getOperatorSelectOptions();
    }

    public function getValueElementType() {
        if ('sheerid' == $this->getAttribute()) {
			return 'select';
		} else if ('sheerid_campaign' == $this->getAttribute()) {
			return 'select';
		}
      	return parent::getValueElementType();
    }

	public function getValueSelectOptions()
    {
		if (!$this->hasData('value_select_options')) {
			if ('sheerid' == $this->getAttribute()) {
				$source = new SheerID_Verify_Model_Customer_Attribute_Source_Affiliationtype();
				$this->setData('value_select_options', $source->getAllOptions());
			} else if ('sheerid_campaign' == $this->getAttribute()) {
				$source = new SheerID_Verify_Model_Customer_Attribute_Source_SheeridCampaign();
				$this->setData('value_select_options', $source->getAllOptions());
			} else {
				$this->setData('value_select_options', parent::getValueSelectOptions());
			}
		}
		return $this->getData('value_select_options');
    }

    public function validate(Varien_Object $object) {
		if ('sheerid' == $this->getAttribute()) {
			$helper = Mage::helper('sheerid_verify');
			$affiliations = $helper->getSheeridAffiliations($object->getQuote());
			return false !== array_search($this->getValue(), $affiliations);
		} else if ('sheerid_campaign' == $this->getAttribute()) {
			$templateId = $this->getValue();
			$helper = Mage::helper('sheerid_verify');
			return $helper->isEligibleForCampaign($templateId, $object->getQuote());
		}
        return parent::validate($object);
    }
}
