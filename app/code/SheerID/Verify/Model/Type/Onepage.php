<?php

class SheerID_Verify_Model_Type_Onepage extends Mage_Checkout_Model_Type_Onepage
{
    public function initCheckout()
    {
        $checkout = $this->getCheckout();
        if (is_array($checkout->getStepData())) {
            foreach ($checkout->getStepData() as $step=>$data) {
                if (!($step==='login'
                    || Mage::getSingleton('customer/session')->isLoggedIn() && $step==='billing')) {
                    $checkout->setStepData($step, 'allow', false);
                }
            }
        }

		if (Mage::helper('sheerid_verify')->shouldShowInCheckout()) {
        	$checkout->setStepData('verify', 'allow', true);
		}

        /*
        * want to laod the correct customer information by assiging to address
        * instead of just loading from sales/quote_address
        */
        $customer = Mage::getSingleton('customer/session')->getCustomer();
        if ($customer) {
            $this->getQuote()->assignCustomer($customer);
        }
        if ($this->getQuote()->getIsMultiShipping()) {
            $this->getQuote()->setIsMultiShipping(false);
            $this->getQuote()->save();
        }
        return $this;
    }
}
