<?php
use Braintree\PaymentInstrumentType;
use Braintree\Transaction\CreditCardDetails;
use Braintree\Transaction\PayPalDetails;
use Braintree\Transaction\ApplePayCardDetails;
use Braintree\Transaction\AndroidPayCardDetails;
use Braintree\Transaction\VenmoAccountDetails;
use Braintree\CreditCard;
use Braintree\PayPalAccount;
use Braintree\ApplePayCard;
use Braintree\AndroidPayCard;

/**
 * Return a payment method token contained within the Braintree_Transaction.
 *
 * @param \Braintree_Transaction $transaction        	
 */
function braintree_get_payment_token_from_transaction( $transaction )
{
	$token = '';
	switch( $transaction->paymentInstrumentType ) {
		case PaymentInstrumentType::CREDIT_CARD :
			$token = $transaction->creditCardDetails->token;
			break;
		case PaymentInstrumentType::PAYPAL_ACCOUNT :
			$token = $transaction->paypalDetails->token;
			break;
		case PaymentInstrumentType::APPLE_PAY_CARD :
			$token = $transaction->applePayCardDetails->token;
			break;
		case PaymentInstrumentType::ANDROID_PAY_CARD :
			$token = $transaction->androidPayCard->token;
			break;
		case PaymentInstrumentType::VENMO_ACCOUNT :
			$token = $transaction->venmoAccount->token;
			break;
	}
	return $token;
}

/**
 * Given a Braintree_Transaction, retrieve the formatted payment method
 * title.
 * <strong>Example</strong> Visa - 41111******1111
 *
 * @param \Braintree_Transaction $transaction        	
 * @return string
 */
function braintree_get_payment_method_title_from_transaction( $transaction )
{
	switch( $transaction->paymentInstrumentType ) {
		case PaymentInstrumentType::CREDIT_CARD :
			$title = braintree_get_payment_method_title_from_method_details( $transaction->creditCardDetails );
			break;
		case PaymentInstrumentType::PAYPAL_ACCOUNT :
			$title = braintree_get_payment_method_title_from_method_details( $transaction->paypalDetails );
			break;
		case PaymentInstrumentType::APPLE_PAY_CARD :
			$title = braintree_get_payment_method_title_from_method_details( $transaction->applePayCardDetails );
			break;
		case PaymentInstrumentType::ANDROID_PAY_CARD :
			$title = braintree_get_payment_method_title_from_method_details( $transaction->androidPayCard );
			break;
		case PaymentInstrumentType::VENMO_ACCOUNT :
			$title = braintree_get_payment_method_title_from_method_details( $transaction->venmoAccount );
			break;
	}
	return $title;
}

/**
 * Given a Braintree payment method, generate the payment method title.
 * * <strong>Example</strong> Visa - 41111******1111
 *
 * @param \Braintree\CreditCard|\Braintree\PayPalAccount|\Braintree\ApplePayCard $payment_method        	
 */
function braintree_get_payment_method_title_from_method( $payment_method )
{
	if ( $payment_method instanceof \Braintree\CreditCard ) {
		$formats = array (
				'{cardType}' => $payment_method->cardType, 
				'{maskedNumber}' => $payment_method->maskedNumber, 
				'{last4}' => $payment_method->last4 
		);
		$format_type = bt_manager()->get_option( 'creditcard_format' );
		$pattern = braintree_get_payment_method_formats() [ PaymentInstrumentType::CREDIT_CARD ] [ $format_type ] [ 'format' ];
	} elseif ( $payment_method instanceof \Braintree\PayPalAccount ) {
		$formats = array (
				'{email}' => $payment_method->payerEmail 
		);
		$format_type = bt_manager()->get_option( 'paypal_format' );
		$pattern = braintree_get_payment_method_formats() [ PaymentInstrumentType::PAYPAL_ACCOUNT ] [ $format_type ] [ 'format' ];
	} elseif ( $payment_method instanceof \Braintree\ApplePayCard ) {
		$formats = array (
				'{cardType}' => preg_match( '/[a-z]+/i', $payment_method->paymentInstrumentName, $matches ) ? $matches [ 0 ] : $payment_method->cardType, 
				'{appleCardType}' => $payment_method->cardType, 
				'{last4}' => preg_match( '/[\d]+/', $payment_method->paymentInstrumentName, $matches ) ? $matches [ 0 ] : $payment_method->paymentInstrumentName, 
				'{paymentInstrumentName}' => $payment_method->paymentInstrumentName 
		);
		$format_type = bt_manager()->get_option( 'applepay_format' );
		$pattern = braintree_get_payment_method_formats() [ PaymentInstrumentType::APPLE_PAY_CARD ] [ $format_type ] [ 'format' ];
	
	}
	$title = str_replace( array_keys( $formats ), $formats, $pattern );
	return apply_filters( 'braintree_get_payment_method_title_from_method', $title, $payment_method, $formats, $format_type );
}

/**
 * Given a Braintree payment method, fetch the formatted payment method
 * title.
 * <div><strong>Example:</strong></div>
 * <ul>
 * <li>CreditCard: Visa - 4111********1111</li>
 * <li>PayPalAccount: PayPal - user@example.com
 * </ul>
 *
 * @param CreditCardDetails|PayPalDetails|ApplePayCardDetails|AndroidPayCardDetails|VenmoAccountDetails $payment_method        	
 * @return string
 */
function braintree_get_payment_method_title_from_method_details( $payment_method )
{
	$title = '';
	if ( $payment_method instanceof CreditCardDetails ) {
		
		$formats = array (
				'{cardType}' => $payment_method->cardType, 
				'{maskedNumber}' => $payment_method->maskedNumber, 
				'{last4}' => $payment_method->last4 
		);
		$format_type = bt_manager()->get_option( 'creditcard_format' );
		$pattern = braintree_get_payment_method_formats() [ PaymentInstrumentType::CREDIT_CARD ] [ $format_type ] [ 'format' ];
	} elseif ( $payment_method instanceof PayPalDetails ) {
		
		$formats = array (
				'{email}' => $payment_method->payerEmail 
		);
		$format_type = bt_manager()->get_option( 'paypal_format' );
		$pattern = braintree_get_payment_method_formats() [ PaymentInstrumentType::PAYPAL_ACCOUNT ] [ $format_type ] [ 'format' ];
	} elseif ( $payment_method instanceof ApplePayCardDetails ) {
		
		$formats = array (
				'{cardType}' => preg_match( '/[a-z]+/i', $payment_method->paymentInstrumentName, $matches ) ? $matches [ 0 ] : $payment_method->cardType, 
				'{appleCardType}' => $payment_method->cardType, 
				'{last4}' => preg_match( '/[\d]+/', $payment_method->paymentInstrumentName, $matches ) ? $matches [ 0 ] : $payment_method->paymentInstrumentName, 
				'{paymentInstrumentName}' => $payment_method->paymentInstrumentName 
		);
		$format_type = bt_manager()->get_option( 'applepay_format' );
		$pattern = braintree_get_payment_method_formats() [ PaymentInstrumentType::APPLE_PAY_CARD ] [ $format_type ] [ 'format' ];
	} elseif ( $payment_method instanceof AndroidPayCardDetails ) {
		$format_type = bt_manager()->get_option( 'paypal_format' );
		$pattern = braintree_get_payment_method_formats() [ PaymentInstrumentType::ANDROID_PAY_CARD ] [ $format_type ] [ 'format' ];
	} elseif ( $payment_method instanceof VenmoAccountDetails ) {
		$format_type = bt_manager()->get_option( 'paypal_format' );
		$pattern = braintree_get_payment_method_formats() [ PaymentInstrumentType::VENMO_ACCOUNT ] [ $format_type ] [ 'format' ];
	}
	$title = str_replace( array_keys( $formats ), $formats, $pattern );
	
	return apply_filters( 'braintree_get_payment_method_title_from_method_details', $title, $formats, $pattern, $payment_method );
}

/**
 * Given a Braintree payment method token, fetch the formatted payment method
 * title.
 * <div><strong>Example:</strong></div>
 * <ul>
 * <li>CreditCard: Visa - 4111********1111</li>
 * <li>PayPalAccount: PayPal - user@example.com
 * </ul>
 *
 * @param int $user_id        	
 * @param string $token        	
 * @return string
 */
function braintree_get_payment_title_from_token( $user_id = 0, $token, $env = null )
{
	$method = braintree_get_payment_method_from_token( $user_id, $token, $env );
	return braintree_get_payment_method_title_from_array( $method );
}

/**
 * Given a payment method array, return the payment method title.
 * <div><strong>Example:</strong></div>
 * <div>Visa - 41111******11111</div>
 * <div>PayPal - user@example.com</div>
 *
 * @param array $method        	
 * @return string - returns a formatted title for the payment method array.
 */
function braintree_get_payment_method_title_from_array( $method )
{
	$title = '';
	switch( $method [ 'type' ] ) {
		case PaymentInstrumentType::CREDIT_CARD :
			$formats = array (
					'{cardType}' => $method [ 'card_type' ], 
					'{maskedNumber}' => $method [ 'masked_number' ], 
					'{last4}' => $method [ 'last4' ] 
			);
			$format_type = bt_manager()->get_option( 'creditcard_format' );
			$pattern = braintree_get_payment_method_formats() [ PaymentInstrumentType::CREDIT_CARD ] [ $format_type ] [ 'format' ];
			break;
		case PaymentInstrumentType::PAYPAL_ACCOUNT :
			$formats = array (
					'{email}' => $method [ 'email' ] 
			);
			$format_type = bt_manager()->get_option( 'paypal_format' );
			$pattern = braintree_get_payment_method_formats() [ PaymentInstrumentType::PAYPAL_ACCOUNT ] [ $format_type ] [ 'format' ];
			break;
		case PaymentInstrumentType::APPLE_PAY_CARD :
			$formats = array (
					'{cardType}' => preg_match( '/[a-z]+/i', $method [ 'payment_instrument_name' ], $matches ) ? $matches [ 0 ] : $method [ 'payment_instrument_name' ], 
					'{appleCardType}' => $method [ 'card_type' ], 
					'{last4}' => preg_match( '/[\d]+/', $method [ 'payment_instrument_name' ], $matches ) ? $matches [ 0 ] : $method [ 'payment_instrument_name' ], 
					'{paymentInstrumentName}' => $method [ 'payment_instrument_name' ] 
			);
			$format_type = bt_manager()->get_option( 'applepay_format' );
			$pattern = braintree_get_payment_method_formats() [ PaymentInstrumentType::APPLE_PAY_CARD ] [ $format_type ] [ 'format' ];
			break;
		case PaymentInstrumentType::ANDROID_PAY_CARD :
			$title = sprintf( '%s - %s', $method [ 'card_type' ], $method [ 'last4' ] );
			break;
		default :
			break;
	}
	$title = str_replace( array_keys( $formats ), $formats, $pattern );
	return apply_filters( 'braintree_get_payment_method_title_from_array', $title, $formats, $pattern, $method );
}

/**
 * Given a user Id and payment method token, return the payment method array
 * stored
 * in the usermeta table.
 *
 * @param int $user_id        	
 * @param string $token        	
 * @param string $env        	
 * @return array
 */
function braintree_get_payment_method_from_token( $user_id, $token, $env = null )
{
	$index = false;
	$methods = braintree_get_user_payment_methods( $user_id, $env );
	if ( array_key_exists( $token, $methods ) ) {
		return $methods [ $token ];
	} else {
		return '';
	}
}

/**
 * Return an array of Braintree payment methods for the given user Id.
 * If no payment methods exists,
 * this method returns an empty array.
 *
 * @param int $user_id        	
 * @param string $env        	
 * @return mixed|boolean|string|unknown
 */
function braintree_get_user_payment_methods( $user_id, $env = null )
{
	$meta_key = sprintf( 'braintree_%s_payment_methods', $env ? $env : bt_manager()->get_environment() );
	$methods = get_user_meta( $user_id, $meta_key, true );
	return empty( $methods ) ? array () : $methods;
}

/**
 * Delete any saved payment methods for the give user Id.
 *
 * @param int $user_id        	
 */
function braintree_delete_user_payment_methods( $user_id, $env = null )
{
	$meta_key = sprintf( 'braintree_%s_payment_methods', $env ? $env : bt_manager()->get_environment() );
	delete_user_meta( $user_id, $meta_key );
}

/**
 * Delete a single payment method for the user.
 * This function removes
 * the payment method from the user_meta table.
 *
 * @param int $user_id        	
 * @param string $token        	
 * @param string $env        	
 */
function braintree_delete_user_payment_method( $user_id, $token, $env = null )
{
	$methods = braintree_get_user_payment_methods( $user_id, $env );
	unset( $methods [ $token ] );
	braintree_save_user_payment_methods( $user_id, $methods, $env );
}

/**
 * Save a Braintree PaymentMethod to the given user's user_meta.
 *
 * @param int $user_id        	
 * @param
 *        	mixed CreditCard | PayPalAccount | ApplePayCard | AndroidPayCard
 *        	$payment_method
 */
function braintree_save_user_payment_method( $user_id, $payment_method, $env = null )
{
	$methods = braintree_get_user_payment_methods( $user_id, $env );
	
	if ( $payment_method instanceof CreditCard ) {
		$method = array (
				'type' => PaymentInstrumentType::CREDIT_CARD, 
				'default' => $payment_method->isDefault(), 
				'card_type' => $payment_method->cardType, 
				'method_type' => $payment_method->cardType, 
				'token' => $payment_method->token, 
				'holder_name' => $payment_method->cardholderName, 
				'created_at' => $payment_method->createdAt, 
				'exp_date' => $payment_method->expirationDate, 
				'exp_month' => $payment_method->expirationMonth, 
				'exp_year' => $payment_method->expirationYear, 
				'expired' => $payment_method->expired, 
				'image_url' => $payment_method->imageUrl, 
				'last4' => $payment_method->last4, 
				'masked_number' => $payment_method->maskedNumber, 
				'updated_at' => $payment_method->updatedAt 
		);
		$methods [ $payment_method->token ] = $method;
	} elseif ( $payment_method instanceof PayPalAccount ) {
		$method = array (
				'type' => PaymentInstrumentType::PAYPAL_ACCOUNT, 
				'method_type' => 'paypal', 
				'billing_agreement_id' => $payment_method->billingAgreementId, 
				'created_at' => $payment_method->createdAt, 
				'default' => $payment_method->isDefault(), 
				'email' => $payment_method->email, 
				'image_url' => $payment_method->imageUrl, 
				'token' => $payment_method->token, 
				'updated_at' => $payment_method->updatedAt 
		);
		$methods [ $payment_method->token ] = $method;
	} elseif ( $payment_method instanceof ApplePayCard ) {
		$method = array (
				'type' => PaymentInstrumentType::APPLE_PAY_CARD, 
				'default' => $payment_method->isDefault(), 
				'card_type' => $payment_method->cardType, 
				'method_type' => $payment_method->cardType, 
				'created_at' => $payment_method->createdAt, 
				'exp_month' => $payment_method->expirationMonth, 
				'exp_year' => $payment_method->expirationYear, 
				'image_url' => $payment_method->imageUrl, 
				'last4' => $payment_method->last4, 
				'payment_instrument_name' => $payment_method->paymentInstrumentName, 
				'source_description' => $payment_method->sourceDescription, 
				'token' => $payment_method->token, 
				'updated_at' => $payment_method->updatedAt 
		);
		$methods [ $payment_method->token ] = $method;
	} elseif ( $payment_method instanceof AndroidPayCard ) {
		$method = array (
				'type' => PaymentInstrumentType::ANDROID_PAY_CARD, 
				'default' => $payment_method->isDefault(), 
				'created_at' => $payment_method->createdAt, 
				'exp_month' => $payment_method->expirationMonth, 
				'exp_year' => $payment_method->expirationYear, 
				'google_transaction_id' => $payment_method->googleTransactionId, 
				'image_url' => $payment_method->imageUrl, 
				'last4' => $payment_method->sourceCardLast4, 
				'card_type' => $payment_method->sourceCardType, 
				'method_type' => $payment_method->sourceCardType, 
				'source_description' => $payment_method->sourceDescription, 
				'token' => $payment_method->token, 
				'updated_at' => $payment_method->updatedAt 
		);
	}
	
	if ( $method [ 'default' ] ) { // If this new method is the default, remove
	                               // the indicator for any other default
	                               // methods.
		array_walk( $methods, function ( &$data, $key )
		{
			if ( $data [ 'default' ] ) {
				$data [ 'default' ] = false;
			}
		} );
	}
	
	$methods [ $payment_method->token ] = $method;
	
	braintree_save_user_payment_methods( $user_id, $methods, $env );
}

/**
 *
 * @param int $user_id        	
 * @param \Braintree\Transaction $transaction        	
 */
function braintree_save_payment_method_from_transaction( $user_id, $transaction, $env = null )
{
	switch( $transaction->paymentInstrumentType ) {
		case PaymentInstrumentType::CREDIT_CARD :
			$payment_method = $transaction->creditCardDetails;
			$method = array (
					'type' => PaymentInstrumentType::CREDIT_CARD, 
					'default' => true, 
					'card_type' => $payment_method->cardType, 
					'method_type' => $payment_method->cardType, 
					'token' => $payment_method->token, 
					'holder_name' => $payment_method->cardholderName, 
					// 'created_at' => $payment_method->createdAt,
					'exp_date' => $payment_method->expirationDate, 
					'exp_month' => $payment_method->expirationMonth, 
					'exp_year' => $payment_method->expirationYear, 
					'expired' => false, 
					'image_url' => $payment_method->imageUrl, 
					'last4' => $payment_method->last4, 
					'masked_number' => $payment_method->maskedNumber 
			);
			break;
		case PaymentInstrumentType::PAYPAL_ACCOUNT :
			$payment_method = $transaction->paypalDetails;
			$method = array (
					'type' => PaymentInstrumentType::PAYPAL_ACCOUNT, 
					'method_type' => 'paypal', 
					'default' => true, 
					'email' => $payment_method->payerEmail, 
					'image_url' => $payment_method->imageUrl, 
					'token' => $payment_method->token 
			);
			break;
		case PaymentInstrumentType::APPLE_PAY_CARD :
			$payment_method = $transaction->applePayCardDetails;
			$method = array (
					'type' => PaymentInstrumentType::APPLE_PAY_CARD, 
					'default' => true, 
					'card_type' => $payment_method->cardType, 
					'method_type' => $payment_method->cardType, 
					'exp_month' => $payment_method->expirationMonth, 
					'exp_year' => $payment_method->expirationYear, 
					'image_url' => $payment_method->imageUrl, 
					'payment_instrument_name' => $payment_method->paymentInstrumentName, 
					'source_description' => $payment_method->sourceDescription, 
					'token' => $payment_method->token 
			);
			break;
		case PaymentInstrumentType::ANDROID_PAY_CARD :
			$payment_method = $transaction->androidPayCard;
			break;
		case PaymentInstrumentType::VENMO_ACCOUNT :
			$payment_method = $transaction->venmoAccount;
			break;
	}
	$methods = braintree_get_user_payment_methods( $user_id, $env );
	array_walk( $methods, function ( &$data, $key )
	{
		if ( $data [ 'default' ] ) {
			$data [ 'default' ] = false;
		}
	} );
	$methods [ $payment_method->token ] = $method;
	braintree_save_user_payment_methods( $user_id, $methods );
}

/**
 * Save the user's payment methods in the usermeta table of the database.
 * Payment methods are stored as arrays which contain the payment data such as
 * type, token, etc.
 *
 * @param int $user_id        	
 * @param array $methods        	
 * @param string $env        	
 */
function braintree_save_user_payment_methods( $user_id, $methods, $env = null )
{
	$meta_key = sprintf( 'braintree_%s_payment_methods', $env ? $env : bt_manager()->get_environment() );
	update_user_meta( $user_id, $meta_key, $methods );
}

function braintree_get_default_method( $methods )
{
	foreach ( $methods as $method ) {
		if ( $method [ 'default' ] === true ) {
			return $method;
		}
	}
	// No default was found so assign a default.
	$rand = array_rand( $methods );
	return isset( $methods [ $rand ] ) ? $methods [ $rand ] : array ();
}

/**
 * Wrapper for bt_manager()->get_customer_id($user_id).
 * Returns a Braintree customer Id
 * for the currently active environment.
 *
 * @param unknown $user_id        	
 * @return mixed|boolean|string|unknown
 */
function braintree_get_customer_id( $user_id, $env = null )
{
	return bt_manager()->get_customer_id( $user_id, $env );
}

function braintree_get_payment_method_formats()
{
	return apply_filters( 'bfwc_payment_method_formats', array (
			PaymentInstrumentType::CREDIT_CARD => array (
					'type_masked_number' => array (
							'label' => __( 'Type Masked Number', 'braintree-payments' ), 
							'example' => 'Visa 4111********1111', 
							'format' => '{cardType} {maskedNumber}' 
					), 
					'type_dash_masked_number' => array (
							'label' => __( 'Type Dash Masked Number', 'braintree-payments' ), 
							'example' => 'Visa - 4111********1111', 
							'format' => '{cardType} - {maskedNumber}' 
					), 
					'type_last4' => array (
							'label' => __( 'Type Last 4', 'braintree-payments' ), 
							'example' => 'Visa 1111', 
							'format' => '{cardType} {last4}' 
					), 
					'type_dash_last4' => array (
							'label' => __( 'Type Dash & Last 4', 'braintree-payments' ), 
							'example' => 'Visa - 1111', 
							'format' => '{cardType} - {last4}' 
					), 
					'masked_number' => array (
							'label' => __( 'Masked Number', 'braintree-payments' ), 
							'example' => '4111********1111', 
							'format' => '{maskedNumber}' 
					), 
					'last4' => array (
							'label' => __( 'Last Four', 'braintree-payments' ), 
							'example' => '1111', 
							'format' => '{last4}' 
					), 
					'card_type' => array (
							'label' => __( 'Card Type', 'braintree-payments' ), 
							'example' => 'Visa', 
							'format' => '{cardType}' 
					) 
			), 
			PaymentInstrumentType::PAYPAL_ACCOUNT => array (
					'paypal_and_email' => array (
							'label' => __( 'PayPal & Email' ), 
							'example' => 'PayPal - john@example.com', 
							'format' => 'PayPal - {email}' 
					), 
					'email' => array (
							'label' => __( 'Email' ), 
							'example' => 'john@example.com', 
							'format' => '{email}' 
					) 
			), 
			PaymentInstrumentType::APPLE_PAY_CARD => array (
					'apple_type_last4' => array (
							'label' => __( 'Type and Last Four', 'braintree-payments' ), 
							'example' => 'Apple Pay - Discover 2928', 
							'format' => 'Apple Pay - {paymentInstrumentName}' 
					), 
					'type_last4' => array (
							'label' => __( 'Type and Last Four', 'braintree-payments' ), 
							'example' => 'Discover 2928', 
							'format' => '{paymentInstrumentName}' 
					) 
			) 
	) );
}

/**
 *
 * @since 2.6.7
 * @return string[]
 */
function braintree_get_method_uris()
{
	$urls = array (
			'paypal' => 'payment-methods/paypal.png', 
			'visa' => 'payment-methods/visa.png', 
			'mastercard' => 'payment-methods/master_card.png', 
			'american_express' => 'payment-methods/amex.png', 
			'discover' => 'payment-methods/discover.png', 
			'maestro' => 'payment-methods/maestro.png', 
			'mastercard' => 'payment-methods/master_card.png', 
			'jcb' => 'payment-methods/jcb.png', 
			'diners_club' => 'payment-methods/diners_club_international.png', 
			'apple_pay_-_visa' => 'payment-methods/apple_pay_visa.png', 
			'apple_pay_-_mastercard' => 'payment-methods/apple_pay_mastercard.png', 
			'apple_pay_-_american_express' => 'payment-methods/apple_pay_american_express.png', 
			'apple_pay_-_discover' => 'payment-methods/apple_pay_discover.png' 
	);
	return $urls;
}

/**
 *
 * @since 2.6.7
 * @param string $type        	
 * @param string $path        	
 * @return string
 */
function braintree_get_method_url( $type, $path = '' )
{
	$path = $path ? $path : bt_manager()->plugin_assets_path() . 'img/';
	$uris = braintree_get_method_uris();
	return isset( $uris [ $type ] ) ? $path . $uris [ $type ] : '';
}