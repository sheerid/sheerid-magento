<?php
class SheerID_Verify_Block_Admin_Order_Custom extends Mage_Core_Block_Template
{
	var $order;
	
	public function setOrder($order) {
		$this->order = $order;
	}
	
    protected function _toHtml() {
        $str = '<script type="text/javascript">';
        $str .= 'var mystr = "';
        $str .= '<tr><td class=\"label\"><label>';
        $str .= Mage::helper('sheerid_verify')->__("SheerID Verification");
        $str .= ':</label></td><td class=\"value\"><strong>';
		
		$affiliations = array();
		foreach (explode(",", $this->order->getSheeridAffiliations()) as $a) {
			$affiliations[] = Mage::helper('sheerid_verify')->__($a);
		}
		
		$affs = implode(", ", $affiliations);
		$info = $this->order->getSheeridResult() ? Mage::helper('sheerid_verify')->__("Verified")." ($affs)" : Mage::helper('sheerid_verify')->__("Not Verified");
	 	$str .= $info . ":<br/>" . $this->order->getSheeridRequestId(); //TODO: link somewhere!

        $str .= '</strong></td></tr>";';
        $str .= "$$('table.form-list')[0].insert({bottom: mystr});</script>";
        return $str;
	}
}