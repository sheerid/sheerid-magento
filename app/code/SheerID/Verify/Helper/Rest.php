<?php

$ExternalLibPath = Mage::getModuleDir('', 'SheerID_Verify') . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR .'SheerID/SheerID.php';
require_once ($ExternalLibPath);

class SheerID_Verify_Helper_Rest extends Mage_Core_Helper_Abstract {
	function getService() {
		$sandbox = Mage::getStoreConfig('sheerid_options/settings/sandbox') == "1";
		$prefix = $sandbox ? 'sandbox_' : '';
		$token = Mage::getStoreConfig("sheerid_options/settings/${prefix}access_token");
		if (!$token) {
			return null;
		}
		return new SheerID($token, $sandbox ? SHEERID_ENDPOINT_SANDBOX : SHEERID_ENDPOINT_PRODUCTION);
	}
}