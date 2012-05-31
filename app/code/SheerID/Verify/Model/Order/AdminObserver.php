<?php

class SheerID_Verify_Model_Order_AdminObserver {
	
	public function on_adminhtml_block_html_before($observer) {
        $template = $observer->getBlock()->getTemplate();
        if($template == 'sales/order/view/info.phtml') {
            $event = $observer->getEvent();
            $eblock = $event->getBlock();
            $order = $eblock->getOrder();

			if ($order->getSheeridRequestId()) {
   	        	$layout = $eblock->getLayout();
	            $block = $layout->createBlock('sheerid/admin_order_custom');
				$block->setOrder($order);
	            Mage::getSingleton('core/layout')->getBlock('content')->append($block);
			}
        }
	}
}
