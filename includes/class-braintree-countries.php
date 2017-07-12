<?php

/**
 * 
 * @author Payment Plugins
 * @copyright Payment Plugins 2016
 *
 */
class Braintree_Gateway_Countries
{

	/**
	 * Format the given address into the country specific format.
	 *
	 * @param array $args 
	 */
	public static function get_formatted_address( $args = array() )
	{
		/* 1. extract the args, parse defaults. */
		$args = wp_parse_args( $args, array ( 
				'first_name' => '', 
				'last_name' => '', 
				'company' => '', 
				'address_1' => '', 
				'address_2' => '', 
				'city' => '', 
				'country' => 'US', 
				'state' => '', 
				'postalcode' => '' 
		) );
		extract( $args );
		
		/* 2. Create format array using the billing_country. */
		$formats = self::get_address_formats();
		
		// format string.
		$format = isset( $formats[ $country ] ) ? $formats[ $country ] : $formats[ 'default' ];
		
		// Create an array that has all of the key value pairs.
		$fields_to_format = array_map( 'esc_html', array ( 
				'{first_name}' => $first_name, 
				'{last_name}' => $last_name, 
				'{company}' => $company, 
				'{name}' => $first_name . ' ' . $last_name, 
				'{country}' => $country, 
				'{address_1}' => $address_1, 
				'{address_2}' => $address_2, 
				'{state}' => $state, 
				'{city}' => $city, 
				'{postalcode}' => $postalcode 
		) );
		/* 3. */
		
		$formatted_address = str_replace( array_keys( $fields_to_format ), $fields_to_format, $format );
		
		// Replace all unfilled formatted strings such as {company_name} etc.
		$formatted_address = preg_replace( '/{.+}/', '', $formatted_address );
		
		$formatted_address = preg_replace( '/\n\n+/', "\n", $formatted_address );
		
		$formatted_address = preg_replace( '/\n/', '</br>', $formatted_address );
		
		return $formatted_address;
	}

	/**
	 * Return an array of address fields that are associated with a given
	 * country.
	 *
	 * @param unknown $country 
	 */
	public static function get_address_fields( $country = 'US' )
	{
		$formats = self::get_address_formats();
		$format = isset( $formats[ $country ] ) ? $formats[ $country ] : $formats[ 'default' ];
		
		// $fields_string = preg_split('/{\w+}/', $format);
		
		// convert string format to array.
		preg_match_all( '/\w+/', $format, $matches );
		
		$fields = array ();
		$default_fields = self::get_default_fields();
		foreach ( $matches[ 0 ] as $match ) {
			if ( $match === 'name' ) {
				
				$fields[ 'first_name' ] = $default_fields[ 'first_name' ];
				$fields[ 'last_name' ] = $default_fields[ 'last_name' ];
			}
			if ( ! isset( $default_fields[ $match ] ) ) {
				continue;
			} else {
				
				$fields[ $match ] = self::get_default_fields()[ $match ];
			}
		}
		
		return $fields;
	}

	public static function get_default_fields()
	{
		$fields = array ( 
				'first_name' => array ( 
						'type' => 'text', 
						'label' => __( 'First Name', 'braintree-payments' ), 
						'required' => true, 
						'class' => '', 
						'autocomplete' => 'given-name' 
				), 
				'last_name' => array ( 
						'type' => 'text', 
						'label' => __( 'Last Name', 'braintree-payments' ), 
						'required' => true, 
						'class' => '', 
						'clear' => true, 
						'autocomplete' => 'family-name' 
				), 
				'company' => array ( 
						'type' => 'text', 
						'label' => __( 'Company Name', 'braintree-payments' ), 
						'class' => '', 
						'autocomplete' => 'organization' 
				), 
				'country' => array ( 
						'type' => 'select', 
						'label' => __( 'Country', 'braintree-payments' ), 
						'required' => true, 
						'class' => '', 
						'autocomplete' => 'country' 
				), 
				'address_1' => array ( 
						'type' => 'text', 
						'label' => __( 'Address', 'braintree-payments' ), 
						'placeholder' => _x( 'Street address', 'placeholder', 'braintree-payments' ), 
						'required' => true, 
						'class' => '', 
						'autocomplete' => 'address-line1' 
				), 
				'address_2' => array ( 
						'type' => 'text', 
						'placeholder' => _x( 'Apartment, suite, unit etc. (optional)', 'placeholder', 'braintree-payments' ), 
						'class' => '', 
						'label' => __( 'Address 2', 'braintree-payments' ), 
						'required' => false, 
						'autocomplete' => 'address-line2' 
				), 
				'city' => array ( 
						'type' => 'text', 
						'label' => __( 'Town / City', 'braintree-payments' ), 
						'required' => true, 
						'class' => '', 
						'autocomplete' => 'address-level2' 
				), 
				'state' => array ( 
						'type' => 'text', 
						'label' => __( 'State / County', 'braintree-payments' ), 
						'required' => true, 
						'class' => '', 
						'validate' => array ( 
								'state' 
						), 
						'autocomplete' => 'address-level1' 
				), 
				'postalcode' => array ( 
						'type' => 'text', 
						'label' => __( 'Postcode / ZIP', 'braintree-payments' ), 
						'required' => true, 
						'class' => '', 
						'clear' => true, 
						'validate' => array ( 
								'postcode' 
						), 
						'autocomplete' => 'postal-code' 
				) 
		);
		
		return $fields;
	}

	public static function get_address_formats()
	{
		// This is a common format.
		$postcode_before_city = "{company}\n{name}\n{address_1}\n{address_2}\n {city}\n{country}";
		
		return apply_filters( 'braintree_get_address_formats', array ( 
				'default' => "{name}\n{company}\n{address_1}\n{address_2}\n{city}\n{state}\n\n{country}", 
				'AU' => "{name}\n{company}\n{address_1}\n{address_2}\n{city} {state} \n{country}", 
				'AT' => $postcode_before_city, 
				'BE' => $postcode_before_city, 
				'CA' => "{company}\n{name}\n{address_1}\n{address_2}\n{city} {state} \n{country}", 
				'CH' => $postcode_before_city, 
				'CL' => "{company}\n{name}\n{address_1}\n{address_2}\n{state}\n {city}\n{country}", 
				'CN' => "{country} \n{state}, {city}, {address_2}, {address_1}\n{company}\n{name}", 
				'CZ' => $postcode_before_city, 
				'DE' => $postcode_before_city, 
				'EE' => $postcode_before_city, 
				'FI' => $postcode_before_city, 
				'DK' => $postcode_before_city, 
				'FR' => "{company}\n{name}\n{address_1}\n{address_2}\n {city_upper}\n{country}", 
				'HK' => "{company}\n{first_name} {last_name_upper}\n{address_1}\n{address_2}\n{city_upper}\n{state_upper}\n{country}", 
				'HU' => "{name}\n{company}\n{city}\n{address_1}\n{address_2}\n\n{country}", 
				'IN' => "{company}\n{name}\n{address_1}\n{address_2}\n{city} - \n{state}, {country}", 
				'IS' => $postcode_before_city, 
				'IT' => "{company}\n{name}\n{address_1}\n{address_2}\n\n{city}\n{state_upper}\n{country}", 
				'JP' => "\n{state}{city}{address_1}\n{address_2}\n{company}\n{last_name} {first_name}\n{country}", 
				'TW' => "{company}\n{last_name} {first_name}\n{address_1}\n{address_2}\n{state}, {city} \n{country}", 
				'LI' => $postcode_before_city, 
				'NL' => $postcode_before_city, 
				'NZ' => "{name}\n{company}\n{address_1}\n{address_2}\n{city} \n{country}", 
				'NO' => $postcode_before_city, 
				'PL' => $postcode_before_city, 
				'SK' => $postcode_before_city, 
				'SI' => $postcode_before_city, 
				'ES' => "{name}\n{company}\n{address_1}\n{address_2}\n {city}\n{state}\n{country}", 
				'SE' => $postcode_before_city, 
				'TR' => "{name}\n{company}\n{address_1}\n{address_2}\n {city} {state}\n{country}", 
				'US' => "{name}\n{company}\n{address_1}\n{address_2}\n{city}, {state} {postalcode} \n{country}", 
				'VN' => "{name}\n{company}\n{address_1}\n{city}\n{country}" 
		) );
	}
}