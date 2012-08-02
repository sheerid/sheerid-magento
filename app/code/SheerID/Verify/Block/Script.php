<?php
class SheerID_Verify_Block_Script extends Mage_Core_Block_Template
{
    protected function _construct()
    {
        parent::_construct();
    }

	protected function _toHtml() {
		$helper = Mage::helper('sheerid_verify');
		$rest_helper = Mage::helper('sheerid_verify/rest');
		$SheerID = $rest_helper->getService();
		?>

		<script type="text/javascript" src="https://www.sheerid.com/jsapi/SheerID.js"></script>
		<script type="text/javascript">
		addSheerIDEventListeners = function() {
			$$('form.verify-form-ajax').each(function(el) {
				Event.observe(el, 'submit', function(event) {
					var validator = typeof Validation == 'function' ? new Validation(el) : (function(){return{validate:function(){return true;}}}());
					if (validator.validate()) {
						$$('.verify-messages')[0].update('');
						var loader = $('verify-please-wait');
						loader.show();
						this.request({
							evalJSON : 'force',
							parameters: {
								'ajax' : true
							},
							onFailure: function() {
								loader.hide();
							},
							onSuccess: function(t) {
								loader.hide();
								var resp = t.responseJSON;
								if (resp.result) {
									$$('.verify-messages')[0].removeClassName('error').update(resp.message || '<?php echo Mage::helper('sheerid_verify')->__("Success!"); ?>');
									el.hide();
									$$('.verify-prompt').invoke('hide');
								} else {
									$$('.verify-messages')[0].addClassName('error').update(resp.errors.join('<br/>'))
								}

								if (resp.refresh) {
									window.location.reload();
								}
								if (resp.discountSubmit) {
									typeof discountForm != 'undefined' && discountForm.submit(false, true);
								}
					        }
						});
					}
				    Event.stop(event);
				});
			});

			SheerIDOrganizationFields = $$('.sheerid-orgs');
			if (SheerIDOrganizationFields.length) {
				if (SheerIDOrganizationFields.length == 1) {
					var type = null;
					var m = SheerIDOrganizationFields[0].className.match(/sheerid-orgs-(\w+)/);
					if (m) {
						type = m[1];
					}
					var field = SheerIDOrganizationFields[0];
					var isFixed = false;
					field.ancestors().each(function(el){ var pos = el.getStyle('position'); if (pos == 'fixed'){ isFixed = true; return false; } });
					SheerID.load('combobox', '1.0', {
						config: {
							baseUrl: '<?php echo $SheerID->baseUrl; ?>',
							allowName: true,
							input: field,
							fixedPosition: isFixed,
							params: {
								type: type ? type : 'university'
							}
						}
					});
				}
			}

			<?php if ($helper->getBooleanSetting("coupon_code_entry")) { ?>

			// promo code form
			if (typeof discountForm !== 'undefined') {
				if (typeof discountForm.loader == 'undefined') {
					var buttons = discountForm.form.select('.button');
					if (buttons.length) {
						buttons[buttons.length-1].insert({'after':'<span id="discount-form-loading" style="display: none;"><img src="http://demo.sheerid.com/skin/frontend/default/default/images/opc-ajax-loader.gif" alt="Loading..." title="Loading..." class="v-middle"/> Loading...</span>'});
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
						new Ajax.Request('/SheerID/verify/coupon?coupon=' + val, {
							asynchronous: false,
							onSuccess: function(r){
								var affs = r.responseJSON;
								if (affs && affs.length) {
									new Ajax.Request('/SheerID/verify', {
										parameters: {
											affiliation_types: affs.join(','),
											promo_code: true
										},
										insertion: 'after',
										onSuccess: function(r){
											var el = openLight();
											el.insert('<h2>Verify</h2>' + r.responseText);
											addSheerIDEventListeners();
											discountForm.loader.hide();
										}
									});
								} else {
									_discountSubmit(false);
								}
							}
						});
					}
					return false;
				};
				discountForm.form.onsubmit = function(){ return discountForm.submit(false); }
			}

			function openLight() {
				$$('body > .wrapper')[0].insert("<div id='overlay'></div>");
				$$('body > .wrapper')[0].insert("<div id='lightbox'></div>");
				$$('#lightbox')[0].insert('<a class="close">close</a>');
				$$('#lightbox > a')[0].observe('click',function(){ $('overlay').remove(); $('lightbox').remove();});
				return $$('#lightbox')[0];
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