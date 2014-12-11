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
		var openLight = function() {
			$$('body > .wrapper')[0].insert("<div id='overlay'></div>");
			$$('body > .wrapper')[0].insert("<div id='lightbox'></div>");
			$$('#lightbox')[0].insert('<a class="close">close</a>');
			$$('#lightbox > a')[0].observe('click',function(){ $('overlay').remove(); $('lightbox').remove();});
			return $$('#lightbox')[0];
		}

		sheerIdVerifyLightbox = function(templateId, productId, coupon) {
			var el = openLight();
			var verifyUrl = '<?php echo $SheerID->baseUrl; ?>/verify/' + templateId + '/';
			verifyUrl += '?metadata[returnUrl]=<?php echo $helper->getSuccessUrl(); ?>';
			if (productId) {
				verifyUrl += '&metadata[product]=' + productId;
			} else if (coupon) {
				verifyUrl += '&metadata[coupon]=' + coupon;
			}
			el.insert('<iframe id="sheerid-iframe" src="' + verifyUrl + '"></iframe>');
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

		}
		addSheerIDEventListeners();
		</script>

		<?php if ($helper->getBooleanSetting("coupon_code_entry")) { ?>
		<style type="text/css">
		#overlay{width:100%;height:100%;background:#000; opacity:0.8; position:fixed;top:0;z-index:1000;}
		#lightbox{width:50%;position:fixed;top:25%;left:25%;padding:2em;background-color:white;z-index:1001;text-align:left;}
		#lightbox a.close{cursor:pointer;color:black; position:relative; display:block;float:right;}
		</style>
		<?php }
	}
}
