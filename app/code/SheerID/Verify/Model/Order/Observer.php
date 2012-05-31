<?php

class SheerID_Verify_Model_Order_Observer
{
	public function on_sales_order_invoice_pay($observer) {
		$invoice = $observer->getEvent()->getInvoice();
	    if ($invoice->getState() == Mage_Sales_Model_Order_Invoice::STATE_PAID) {
			//$this->track_conversion($invoice);
	    }
	    return $this;
	}
	
	public function on_sales_order_save_commit_after($observer) {
	    $order = $observer->getOrder();
	    if ($order->getState() == Mage_Sales_Model_Order::STATE_COMPLETE) {
	    	$this->track_conversion($order);
	    }
	}
	
    private function track_conversion($orderOrInvoice) {
		// TODO: update SheerID request with conversion info: total spend, discount, etc.
	}
}