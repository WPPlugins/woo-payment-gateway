jQuery(document).ready(function($){
	var admin = {
			init: function(){
				$('.button-collapse').sideNav();
				
				$('select.bfwc-select2').filter(':not(select2-hidden-accessible)').select2();
				
				$('.modal-trigger').leanModal();
				
				$('.bfwc-close-modal').on('click', this.close_modal);
				
				$('.add-chip-setting').on('click', this.add_chip_setting);
				
				$('.remove-settings-chip').on('click', this.close_meta_chip);
				
				$('#braintree_checkout_settings_custom_form_design').on('change', this.custom_form_style_change);
				
				$('select, input[type="checkbox"]').on('change', this.show_child_settings);
				
				$('.add-merchant-account').on('click', this.add_merchant_account);
				
				$('.trash-merchant-account').on('click', this.remove_merchant_account);
				
				$('input[type="checkbox"]').on('change', this.uncheck_opposite);
				
				$('button.btn').on('click', this.show_loader);
				
				$('[name^="braintree_api_settings"]').on('keyup paste change', this.settings_updated);
				
				this.show_child_settings();
				
				this.disable_if_inactive();
				
				this.prepare_multiselect();
				
				this.prepare_fee_templates();
				
				this.prepare_merchant_templates();
			},
			close_admin_notice: function(e){
				e.preventDefault();
				$(this).parent('div').slideUp().remove();
			},
			add_chip_setting: function(e){
				e.preventDefault();
				var option, html, title, fieldKey;
				fieldKey = $(this).attr('field-key');
				option = $($(this).attr('select-id')).val();
				title = braintree_settings_vars.keys[fieldKey].options[option];
				html = braintree_settings_vars.keys[fieldKey].html.replace('%title%', title);
				html = html.replace('%name%', option);
				if($('input[name="'+fieldKey + '['+option+']"]').length > 0){
					Materialize.toast(braintree_settings_vars.keys[fieldKey].toast.replace('%s', title), 2000, 'red lighten-2');
					return;
				}
				var parent = $(this).parent().parent('.row');
				var next = parent.find('div.chip-settings-container');
				next.append(html);
				$('.remove-settings-chip').on('click', this.close_meta_chip);
			},
			custom_form_style_change: function(){
				var form = $(this).val();
				$('#braintree_checkout_settings_custom_form_styles').val(braintree_settings_vars.custom_forms[form].styles);
			},
			show_child_settings: function(e){
				$('select, input[type="checkbox"]').each(function(){
					var val = $(this).is('select') ? $(this).val() : ($(this).is('input[type="checkbox"]') && $(this).is(':checked') ? 'checked' : '');
					var element = $(this);
					$('[data-parent-setting]').each(function(){
						if($(this).attr('data-parent-setting') === element.attr('id')){
							if($(this).attr('data-show-if') === val){
								$(this).closest('tr').show();
							}else{
								$(this).closest('tr').hide();
							}
						}
					})
				})
			},
			disable_if_inactive: function(){
				if($('#braintree_gateway_license_status').val() !== 'active'){
					$('.production-option').prop('disabled', true);
				}
			},
			add_merchant_account: function(e){
				e.preventDefault();
				var currency, environment, html, name, row;
				currency = $($(this).attr('select-id')).val();
				if(currency == null){
					Materialize.toast(braintree_settings_vars.merchant_accounts.messages.no_currency, 2500, 'red lighten-2');
					return;
				}
				environment = $(this).attr('environment');
				name = $(this).attr('data-setting').replace('%currency%', currency);
				if($('input[name="'+name+'"]').length > 0){
					Materialize.toast(braintree_settings_vars.merchant_accounts.messages.currency_exists.replace('%currency%', currency), 2500, 'red lighten-2');
					return;
				}
				html = braintree_settings_vars.merchant_accounts[environment].html.replace('%currency%', currency).replace('%name%', name);
				row = $(this).closest('.row');
				row.append(html);
				$('.trash-merchant-account').on('click', admin.remove_merchant_account);
			},
			remove_merchant_account: function(){
				var element = $(this).closest('.col');
				$(this).closest('.col').fadeOut(400, function(){
					element.remove();
				})
			},
			uncheck_opposite: function(){
				var id;
				if($(this).is(':checked')){
					$($(this).attr('uncheck')).prop('checked', false);
				}
			},
			show_loader: function(){
				$(this).children('div.preloader-wrapper').show();
			},
			close_modal: function(){
				$(this).closest('.modal').closeModal();
			},
			prepare_multiselect: function(){
				$('.bfwc-multiselect').select2();
			},
			settings_updated: function(){
				$('[name="bfwc_settings_input_changed"]').val('true');
			},
			prepare_fee_templates: function(){
				var Fee = Backbone.Model.extend({
					defaults: {
						name: '',
						calculation: '',
						tax_status: 'none',
						gateways: [],
					}
				});
				var FeeView = Backbone.View.extend({
					tagName: 'tr',
					template: _.template($(braintree_settings_vars.templates.fees.fee).html()),
					events: {
						'click .bfwc-delete-row': 'removeFee'
					},
					initialize: function(params){
						this.parent = params.parent
						this.model.set('index', this.parent.index);
						this.render();
					},
					render: function(){
						this.$el.html(this.template(this.model.toJSON()));
						var pre_select = [], 
						that = this;
						$.each(this.model.get('gateways'), function(index, id){
							that.$el.find('select option[value="' + id + '"]').attr('selected', true);
						})
						this.$el.find('.bfwc-backbone-selec2').select2();
						return this;
					},
					removeFee: function(){
						this.remove();
					}
				});
				var FeesCollection = Backbone.Collection.extend({
					model: Fee
				});
				
				var FeesView = Backbone.View.extend({
					el: '#bfwc-fee-tbody',
					tagName: 'tbody',
					index: 0,
					initialize: function(params){
						this.feesCollection = params.feesCollection;
						this.render();
					},
					render: function(){
						this.feesCollection.each(function(fee){
							var feeView = new FeeView({
								model: fee,
								parent: this
							});
							this.$el.append(feeView.$el);
							this.index++;
						}, this);
						return this;
					},
					addFee: function(){
						var feeView = new FeeView({
							model: new Fee(),
							parent: this
						});
						this.$el.append(feeView.$el);
						this.index++;
					}
				});
				var FeeContainer = Backbone.View.extend({
					el: '#bfwc-fee-container',
					table: '#bfwc-fee-table',
					events: {
						'click .bfwc-add-fee': 'addFee'
					},
					initialize: function(){
						this.render();
						var feesCollection = new FeesCollection();
						//populate the feesCollection with existing fees.
						$.each(braintree_settings_vars.fees, function(index, fee){
							feesCollection.add(new Fee({
								name: fee.name,
								calculation: fee.calculation,
								tax_status: fee.tax_status,
								gateways: fee.gateways
							}));
						});
						this.feesView = new FeesView({
							feesCollection: feesCollection,
							index: feesCollection.size() - 1
						});
						$(this.table).append(this.feesView.$el);
					},
					render: function(){
						this.$el.append($(braintree_settings_vars.templates.fees.container));
						return this;
					},
					addFee: function(e){
						e.preventDefault()
						this.feesView.addFee();
					}
				});
				
				var feeContainer = new FeeContainer();
			},
			prepare_merchant_templates: function(){
				
				var MerchantAccount = Backbone.Model.extend({
					defaults : {
						merchant_account : '',
						currency : ''
					}
				});
				
				var MerchantView = Backbone.View.extend({
					tagName: 'tr',
					events: {
						'click .bfwc-delete-row': 'removeMerchantAccount'
					},
					initialize: function(params){
						this.parent = params.parent;
						this.model.set('index', this.parent.index);
						this.environment = params.environment;
						this.template = _.template($(braintree_settings_vars.templates[this.environment].merchant_accounts.merchant_account).html());
						this.render();
					},
					render: function(){
						this.$el.html(this.template(this.model.toJSON()));
						var that = this;
						this.$el.find('select option').each(function(){
							if($(this).val() === that.model.get('currency')){
								$(this).attr('selected', true);
							}
						})
						this.$el.find('.bfwc-backbone-selec2').select2();
						return this;
					},
					removeMerchantAccount: function(){
						this.remove();
					}
				});
				
				var MerchantCollection = Backbone.Collection.extend({
					model: MerchantAccount
				});
				
				var MerchantsView = Backbone.View.extend({
					tagName: 'tbody',
					index: 0,
					initialize: function(params){
						this.collection = new MerchantCollection();
						this.environment = params.environment;
						this.setElement($('#bfwc-' + this.environment + '-merchant-tbody'));
						this.render();
					},
					render: function(){
						if(typeof braintree_settings_vars.merchant_accounts !== "undefined"){
							var merchants = merchants = braintree_settings_vars.merchant_accounts[this.environment],
							that = this;
							$.each(merchants, function(index, account){
								that.collection.add(new MerchantAccount({
									currency: account.currency,
									merchant_account: account.id
								}));
							});
							this.collection.each(function(merchantAccount){
								var merchantView = new MerchantView({
									model: merchantAccount,
									parent: this,
									environment: this.environment
								});
								this.$el.append(merchantView.$el);
								this.index++;
							}, this);
						}
					},
					addMerchantAccount: function(){
						var merchantView = new MerchantView({
							model: new MerchantAccount(),
							parent: this,
							environment: this.environment
						});
						this.$el.append(merchantView.$el);
						this.index++;
					}
				});
				
				var environments = ["production", "sandbox"];
				
				var MerchantContainer = Backbone.View.extend({
					events: {
						'click .bfwc-add-merchant': 'addMerchantAccount'
					},
					initialize: function(params){
						this.environment = params.environment;
						this.setElement($(braintree_settings_vars.templates[this.environment].merchant_accounts.container));
						this.render();
					},
					render: function(){
						$('#bfwc-' + this.environment + '-merchant-container').append(this.$el);
						this.merchantsView = new MerchantsView({
							environment: this.environment
						});
						return this;
					},
					addMerchantAccount: function(e){
						e.preventDefault();
						this.merchantsView.addMerchantAccount();
					}
				});
				
				$.each(environments, function(index, environment){
					var merchantContainer = new MerchantContainer({
						environment: environment
					});
				});
			}
	}
	admin.init();
})