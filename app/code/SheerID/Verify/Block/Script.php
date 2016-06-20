<?php
class SheerID_Verify_Block_Script extends Mage_Core_Block_Template
{
    protected function _construct()
    {
        parent::_construct();
    }

	protected function _toHtml() {
		$helper = Mage::helper('sheerid_verify');
		$SheerID = Mage::helper('sheerid_verify/rest')->getService();
		if (!$SheerID) {
			return;
		}
		?>

		<script type="text/javascript" src="<?php echo $SheerID->baseUrl; ?>/jsapi/SheerID.js"></script>
		<script type="text/javascript">

		var closeLight = function() {
			$('overlay').remove();
			$('lightbox').remove();
		}

		var openLight = function() {
			$$('body')[0].insert("<div id='overlay'></div>");
			$$('body')[0].insert("<div id='lightbox'></div>");
			$$('#lightbox')[0].insert('<a class="close"><span>close</span></a>');
			$$('#lightbox > a')[0].observe('click', closeLight);
			return $$('#lightbox')[0];
		}

		sheerIdVerifyLightbox = function(templateId, productFormData, coupon, complete) {
			var el = openLight();
			var verifyUrl = '<?php echo $SheerID->baseUrl; ?>/verify/' + templateId + '/';
			verifyUrl += '?metadata[returnUrl]=<?php echo $helper->getSuccessUrl(); ?>';
			if (typeof complete == 'function') {
				verifyUrl += '&metadata[action]=dismiss';
			}
			if (productFormData) {
				verifyUrl += '&metadata[state]=' + escape(productFormData);
				verifyUrl += '&metadata[state_type]=product';
			} else if (coupon) {
				verifyUrl += '&metadata[state]=' + coupon;
				verifyUrl += '&metadata[state_type]=coupon';
			}
			el.insert('<iframe id="sheerid-iframe" src="' + verifyUrl + '"></iframe>');
			$('sheerid-iframe').on('load', function(){
				var isComplete = false;
				try {
					isComplete = this.contentDocument 
				    	&& this.contentDocument.location.href.indexOf("<?php echo Mage::getUrl('SheerID/verify/dismiss'); ?>") == 0;
		    	}
		    	catch (err) {
		    		// do nothing
		    	}
				if (isComplete === true) {
					closeLight();
					if (typeof complete == 'function') {
						complete();
					}
				}
			});
		}

		addSheerIDEventListeners = function() {
			var defaultCampaignId = '<?php echo $helper->getDefaultCampaignId(); ?>';
			$$('a[data-sheerid="lightbox"], button[data-sheerid="lightbox"]').each(function(el){
				$(el).observe('click', function(event) {
					var campaignId = el.getAttribute('data-sheerid-campaign-id') || el.getAttribute('data-sheerid-template-id') || defaultCampaignId;
					sheerIdVerifyLightbox(campaignId);
					return false;
				});
			});

			<?php if ($helper->getBooleanSetting("coupon_code_entry")) { ?>

			// promo code form
			if (typeof discountForm !== 'undefined') {
				if (typeof discountForm.loader == 'undefined') {
					var buttons = discountForm.form.select('.button');
					if (buttons.length) {
						buttons[buttons.length-1].insert({'after':'<span id="discount-form-loading" style="display: none;">&nbsp;<img src="<?php echo Mage::getBaseUrl("skin"); ?>frontend/default/default/images/opc-ajax-loader.gif" alt="Loading..." title="Loading..." class="v-middle"/> Loading...</span>'});
					}
				}
				discountForm.loader = $('discount-form-loading');
				var _discountSubmit = function(isRemove) {
					if (isRemove) {
						$('coupon_code').removeClassName('required-entry');
						$('remove-coupone').value = "1";
					} else {
						$('coupon_code').addClassName('required-entry');
						$('remove-coupone').value = "0";
					}
			    		return VarienForm.prototype.submit.bind(discountForm)();
				};
				discountForm.submit = function(isRemove, bypassAffiliationCheck) {
					if (isRemove) {
						_discountSubmit(isRemove);
					} else if (bypassAffiliationCheck) {
						_discountSubmit(isRemove);
					} else {
						var val = discountForm.form.elements['coupon_code'].value;
						if (!val) { return false; }
						discountForm.loader.show();
						new Ajax.Request("<?php echo Mage::getUrl('SheerID/verify/coupon'); ?>?coupon=" + val, {
							asynchronous: false,
							onSuccess: function(r){
								var constraints = r.responseJSON;
								if (constraints.campaign) {
									sheerIdVerifyLightbox(constraints.campaign, null, val);
									discountForm.form.elements['coupon_code'].value = '';
								} else {
									_discountSubmit(false);
								}
								discountForm.loader.hide();
							}
						});
					}
					return false;
				};
				discountForm.form.onsubmit = function(){ return discountForm.submit(false); }
			}
			<?php } ?>

			<?php if ($helper->getBooleanSetting("add_to_cart_button")) { ?>

			// add to cart form
			if (typeof productAddToCartForm != 'undefined') {
				var _submit = (function(){
					return productAddToCartForm.submit;
				})();
				var product_eligible = false;
				Validation.add('validate-sheerid-verify-product', 'Product requires verification', function (val) {
					return product_eligible;
				});
				productAddToCartForm.form.elements['product'].addClassName('validate-sheerid-verify-product');
				productAddToCartForm.submit = function(a) {
					if (productAddToCartForm.validator.validate()) {
						_submit(a);
					} else {
						var anyOtherValidationError = false;
						productAddToCartForm.form.select('.validation-failed').each(function(el) {
							anyOtherValidationError = anyOtherValidationError || !el.hasClassName('validate-sheerid-verify-product');
						});
						if (!anyOtherValidationError) {
							var productId = productAddToCartForm.form.elements['product'].value;
							var formData = productAddToCartForm.form.serialize();
							new Ajax.Request("<?php echo Mage::getUrl('SheerID/verify/product'); ?>?product=" + productId, {
								asynchronous: true,
								onSuccess: function(r) {
									var constraints = r.responseJSON;
									if (constraints.campaign) {
										sheerIdVerifyLightbox(constraints.campaign, formData, null, function() {
											product_eligible = true;
											productAddToCartForm.submit();
										});
									} else {
										product_eligible = true;
										productAddToCartForm.submit();
									}
								}
							});
						}
					}
				};
			}
			<?php } ?>
		}
		addSheerIDEventListeners();
		</script>
<?php
	}
}
