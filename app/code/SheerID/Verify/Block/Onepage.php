<?php
class SheerID_Verify_Block_Onepage extends Mage_Checkout_Block_Onepage
{
    public function getSteps()
    {
		if (Mage::helper('sheerid_verify')->shouldShowInCheckout()) {
			$after_step = 'shipping';
		
			$steps = parent::getSteps();
			$new_steps = array();
			foreach ($steps as $key => $step_data) {
				$new_steps[$key] = $step_data;
				if ($after_step == $key) {
					$new_steps['verify'] = $this->getCheckout()->getStepData('verify');
				}
			}
			return $new_steps;
		} else {
			return parent::getSteps();
		}
    }
}