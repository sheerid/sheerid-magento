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
		addSheerIDEventListeners = function() {
			$$('form.verify-form-ajax').each(function(form) {
				Event.observe(form, 'submit', function(event) {
					var validator = typeof Validation == 'function' ? new Validation(form) : (function(){return{validate:function(){return true;}}}());
					if (validator.validate()) {
						var wrap = form.up('#sheerid_verify')
						wrap.select('.verify-messages')[0].update('');
						var loader = form.select('.verify-please-wait')[0];
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
									wrap.select('.verify-messages')[0].removeClassName('error').update(resp.message || '<?php echo Mage::helper('sheerid_verify')->__("Success!"); ?>');
									form.hide();
									wrap.select('.verify-prompt').invoke('hide');
									wrap.select('.verify-status').invoke('hide');
								} else {
									if (resp.allow_upload) {
										resp.errors.push('<a href="javascript:;" class="link-upload"><?php echo Mage::helper("sheerid_verify")->__("Upload proof of affiliation"); ?></a>');
									}
									wrap.select('.verify-messages')[0].addClassName('error').update(resp.errors.join('<br/>'));
									
									var uploadLinks = wrap.select('.verify-messages .link-upload');
									if (uploadLinks.length) {
										var link = uploadLinks[0];
										link.onclick = function(){
											var ctUpload = 'verify-upload-' + new Date()/1;
											form.replace('<div id="' + ctUpload + '">Loading...</div>');
											wrap.select('.verify-messages').each(function(msgs){ msgs.update(); });
											wrap.select('.verify-status').invoke('hide');

											var failureMsg = '<?php echo Mage::helper("sheerid_verify")->__("Unable to prepare upload form."); ?>';

											var request = new Ajax.Request("<?php echo Mage::getUrl('SheerID/verify/uploadToken'); ?>", {
									                method: 'post',
									                onSuccess: function(transport) {
														if (transport && transport.responseText){
															var response;
															try{
																response = eval('(' + transport.responseText + ')');
															} catch (e) {
																response = {};
															}
															if (response.token) {
																SheerID.load('asset', '1.0', {
																	config: {
																		container : ctUpload,
																		maxFiles: 3,
																		baseUrl : response.baseUrl,
																		success : '<?php echo Mage::app()->getStore()->getBaseUrl();?>SheerID/verify/verifyUploadSuccess',
																		failure : '<?php echo Mage::app()->getStore()->getBaseUrl();?>SheerID/verify/verifyUploadFailure',
																		onSuccess : function() {
																			var success_msg = '<?php echo $this->__("Your documentation has been uploaded successfully."); ?>';
																			var keep_shopping = '<?php echo $this->__('Click <a href="%s">here</a> to continue shopping.', Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB)); ?>';
																			wrap.select('.verify-prompt').each(function(msgs){ msgs.update(); });
																			$(ctUpload).update('<p>' + success_msg + '</p><p>' + keep_shopping + '</p>');
																		},
																		ajax: true,
																		token: response.token
																	}
																});
															}
												        } else {
															$(ctUpload).update(failureMsg);
														}
													},
													onFailure: function(transport) {
														$(ctUpload).update(failureMsg);
													},
									                parameters: {}
									  			});
										};
									}
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
			$$('form.verify-form').each(function(form) {
				var wrap = form.up('#sheerid_verify')
				var buffered;
				wrap.select('.affiliation-type-choice').each(function(el){
					if (!$(el).hasClassName('observing')) {
						$(el).observe('change', function(event) {
							if (buffered) {
								window.clearTimeout(buffered);
							}
							buffered = window.setTimeout(function(){
								if (el.value) {
									var params = {affiliation_types:el.value, form_only: true};
									if (wrap.up('.opc')) {
										params.use_ajax = false;
										params.submit = false;
										params.use_quote_information = true;
									}
									new Ajax.Request("<?php echo Mage::getUrl('SheerID/verify'); ?>", {
										method: 'get',
										parameters: params,
										onComplete: function(response) {
											form.update(response.responseText);
											addSheerIDEventListeners();
										}
									});
								} else {
									form.update('');
								}
								delete buffered;
							}, 50);
						});
						$(el).addClassName('observing');
					}
				});
			});

			SheerIDOrganizationFields = $$('.sheerid-orgs');
			for (var i=0; i<SheerIDOrganizationFields.length; i++) {
				var field = SheerIDOrganizationFields[i];
				var m = field.className.match(/sheerid-orgs-(\w+)/);
				if (m) {
					var isFixed = false;
					field.ancestors().each(function(el){ var pos = el.getStyle('position'); if (pos == 'fixed'){ isFixed = true; return false; } });
					SheerID.load('combobox', '1.0', {
						baseUrl : '<?php echo $SheerID->baseUrl; ?>/jsapi',
						config: {
							baseUrl: '<?php echo $SheerID->baseUrl; ?>',
							allowName: true,
							input: field,
							fixedPosition: isFixed,
							params: {
								type: m[1].toUpperCase()
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
						new Ajax.Request("<?php echo Mage::getUrl('SheerID/verify/coupon'); ?>?coupon=" + val, {
							asynchronous: false,
							onSuccess: function(r){
								var affs = r.responseJSON;
								if (affs && affs.length) {
									new Ajax.Request("<?php echo Mage::getUrl('SheerID/verify'); ?>", {
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
