<?php

class SheerID_Verify_Model_Order_Observer
{
	public function on_sales_order_invoice_pay($observer) {
		$invoice = $observer->getEvent()->getInvoice();
		$this->orderUpdated();
	    return $this;
	}
	
	public function on_sales_order_save_commit_after($observer) {
	    $order = $observer->getOrder();
	    if ($order->getState() == Mage_Sales_Model_Order::STATE_COMPLETE) {
	    	$this->orderUpdated();
	    }
		return $this;
	}
	
	private function orderUpdated($order) {
		if (!$order || !$order->getSheeridRequestId()) {
			return;
		}
		if ($order->getState() == Mage_Sales_Model_Order::STATE_COMPLETE) {
			$this->trackConversion($order);
		}
	}
	
    private function linkOrder($order) {
		// TODO: update SheerID request with conversion info: total spend, discount, etc.
	}
	
	private function trackConversion($order) {
		// TODO: update SheerID request with conversion info: total spend, discount, etc.
	}
}