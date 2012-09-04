<?php
class SheerID_Verify_Block_Widget extends Mage_Core_Block_Template
{	
    protected function _construct()
    {
        parent::_construct();
        $this->setIsConditional("true");
        $this->setTemplate('verify/verify-widget.phtml');
    }

    public function getTitle() {
       $title = parent::getTitle();
       if (!$title) {
          return $this->__("Verify");
       }
       return $title;
    }

	public function isOnCartPage() {
		$request = $this->getRequest();
		$module = $request->getModuleName();
		$controller = $request->getControllerName();
		$action = $request->getActionName();
		
		return ($module == 'checkout' && $controller == 'cart' && $action == 'index');
	}
	
	protected function widgetJavaScript($container='verify-form') {
		$config = array();
		if ($this->getAffiliationTypes()) {
			$config['affiliation_types'] = $this->getAffiliationTypes();
		}
		if ($this->getOrganizationId()) {
			$config['organization_id'] = $this->getOrganizationId();
		}
		$config['in_cart'] = $this->isOnCartPage();
?>
		<script type="text/javascript">
		function sheerIdVerify() {
			new Ajax.Updater('<?php echo $container; ?>', '/SheerID/verify', {
				method: 'get',
				parameters: <?php echo json_encode($config); ?>,
				onComplete: function(e) {
					addSheerIDEventListeners();
				}
			});
		}
		</script>
<?php
	}
	
	protected function _toHtml() {
		$helper = Mage::helper('sheerid_verify');
		$quote = $helper->getCurrentQuote();
		if ("true" != $this->getIsConditional() || $quote->getSheeridResult() != 1) {
			return parent::_toHtml();
		}
	}
}
