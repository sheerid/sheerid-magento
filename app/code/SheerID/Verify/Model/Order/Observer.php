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
		$this->orderUpdated($order);
		return $this;
	}
	
	private function orderUpdated($order) {
		if (!$order || !$order->getSheeridRequestId()) {
			return;
		}
		$this->linkOrder($order);
		if ($order->getState() == Mage_Sales_Model_Order::STATE_COMPLETE) {
			$this->trackConversion($order);
		}
	}
	
	private function linkOrder($order) {
		$SheerID = Mage::helper('sheerid_verify/rest')->getService();
		try {
			$SheerID->updateOrderId($order->getSheeridRequestId(), $order->getRealOrderId());
		} catch (Exception $e) {}
	}
	
	private function trackConversion($order) {
		// TODO: update SheerID request with conversion info: total spend, discount, etc.
	}
}
