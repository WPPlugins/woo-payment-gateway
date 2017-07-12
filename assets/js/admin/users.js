jQuery(function($){
	
	var user = {
			init: function(){
				$('.bfwc-select2').select2({
					width: 'resolve'
				});
				
				this.init_backbone();
			},
			init_backbone: function(){
				var CreditCardForm = Backbone.Model.extend({
					initialize: function(){
						window.alert('Hello Guy');
					}
				});
				var CreditCardView = Backbone.View.extend({
					tagName: 'div',
					className: 'bfwc-modal-overlay',
					events: {
						'click .bfwc-close': 'close',
						'click .bfwc-tokenize': 'tokenize'
					},
					initialize: function(params){
						this.environment = params.environment;
						this.render();
						return this;
						
					},
					render: function(){
						this.$el.html(bfwc_user_params[this.environment].modal_template);
						$('body').append(this.$el);
						this.dropin = new Dropin(this.environment);
						this.dropin.init_fields();
						return this;
					},
					show: function(){
						$('body').addClass('modal-open');
						this.$el.fadeIn();
					},
					close: function(){
						var view = this;
						this.$el.fadeOut('400', function(view){
							$('body').removeClass('modal-open');
						});
					},
					tokenize: function(e){
						e.preventDefault();
						this.dropin.tokenize();
					}
				})
				var Dropin = Backbone.Model.extend({
					initialize: function(environment){
						this.environment = environment;
						this.container = '#modal-content-' + this.environment;
						this.payment_nonce = '#bfwc_' + this.environment + '_payment_nonce';
					},
					init_fields: function(){
						var that = this;
						braintree.dropin.create({
							authorization: bfwc_user_params[this.environment].client_token,
							selector: this.container,
							locale: bfwc_user_params.locale
						}, function(err, dropinInstance){
							if(err){
								that.submit_error(err);
								return;
							}
							that.dropinInstance = dropinInstance;
						});
					},
					tokenize: function(){
						var that = this;
						if(this.dropinInstance){
							this.dropinInstance.requestPaymentMethod(function(err, payload){
								if(err){
									that.submit_error(err);
									return;
								}
								that.on_payment_method_received(payload);
							})
						}
					},
					on_payment_method_received: function(payload){
						$(this.payment_nonce).val(payload.nonce);
						$('#submit').trigger('click');
					},
					submit_error: function(err){
						$(this.container).closest('.modal-body').find('.bfwc-error').remove();
						$(this.container).closest('.modal-body').prepend('<div class="bfwc-error">' + err.message + '</div>');
					}
				});
				$(document.body).on('click', '.bfwc-open-form', function(e){
					e.preventDefault();
					var environment = $(this).attr('data-environment');
					var modal = modals[environment];
					modal.show();
				});
				
				var modals = {
					'sandbox': new CreditCardView({environment: 'sandbox'}),
					'production': new CreditCardView({environment: 'production'})
				};
				
				$(document.body).on('click', '.bfwc-open-form', function(e){
					e.preventDefault();
					var modal = modals[$(this).attr('data-environment')];
					modal.show();
				})
			}
	}
	user.init();
})