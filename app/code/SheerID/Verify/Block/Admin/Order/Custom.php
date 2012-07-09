<?php
class SheerID_Verify_Block_Admin_Order_Custom extends Mage_Core_Block_Template
{
    protected function _toHtml() {
	$SheerID = Mage::helper('sheerid_verify/rest')->getService();

	try {
		$response = $SheerID->inquire($this->order->getSheeridRequestId());
	} catch (Exception $e) {
		$response = null;
	}

	if ($response) {
		$str = '<script type="text/javascript">';
		$str .= 'var mystr = "';
		$str .= '<tr><td class=\"label\"><label>';
		$str .= Mage::helper('sheerid_verify')->__("SheerID Verification");
		$str .= ':</label></td><td class=\"value\"><strong>';
		
		$affiliations = array();
		foreach ($response->affiliations as $a) {
			$aff = Mage::helper('sheerid_verify')->__($a->type);
			if ($a->organizationName) {
				$aff .= " (" . $a->organizationName . ")";
			}
			$affiliations[] = $aff;
		}
		
		$info = $response->result ? Mage::helper('sheerid_verify')->__("Verified") : Mage::helper('sheerid_verify')->__("Not Verified");
		$color = $response->result ? "green" : "red";

		$str .= "<span style='color: $color'>$info</span><br/>";
		$str .= implode("<br/>", $affiliations)."<br/>";
		$str .= "Request ID: ".$this->order->getSheeridRequestId()."<br/>";

		$str .= '</strong></td></tr>";';
		$str .= "$$('table.form-list')[0].insert({bottom: mystr});</script>";
		return $str;
	} else {
		return "";
	}
    }
}
