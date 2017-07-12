<?php

use Braintree\PaymentInstrumentType;

/**
 * Function that generates an attributes array and implodes it for use in an
 * html field.
 *
 * @param array $attributes
 * @param string $echo
 */
function braintree_get_html_field_attributes( $attributes, $echo = false )
{
	$attrs = array ();
	foreach ( $attributes as $k => $v ) {
		$attrs [] = $k . '="' . $v . '"';
	}
	if ( $echo ) {
		echo implode( ' ', $attrs );
	} else {
		return implode( ' ', $attrs );
	}
}

function braintree_get_currencies()
{
	return apply_filters( 'braintree_get_currencies', array (
			'AED' => __( 'United Arab Emirates dirham', 'braintree-payments' ), 
			'AFN' => __( 'Afghan afghani', 'braintree-payments' ), 
			'ALL' => __( 'Albanian lek', 'braintree-payments' ), 
			'AMD' => __( 'Armenian dram', 'braintree-payments' ), 
			'ANG' => __( 'Netherlands Antillean guilder', 'braintree-payments' ), 
			'AOA' => __( 'Angolan kwanza', 'braintree-payments' ), 
			'ARS' => __( 'Argentine peso', 'braintree-payments' ), 
			'AUD' => __( 'Australian dollar', 'braintree-payments' ), 
			'AWG' => __( 'Aruban florin', 'braintree-payments' ), 
			'AZN' => __( 'Azerbaijani manat', 'braintree-payments' ), 
			'BAM' => __( 'Bosnia and Herzegovina convertible mark', 'braintree-payments' ), 
			'BBD' => __( 'Barbadian dollar', 'braintree-payments' ), 
			'BDT' => __( 'Bangladeshi taka', 'braintree-payments' ), 
			'BGN' => __( 'Bulgarian lev', 'braintree-payments' ), 
			'BHD' => __( 'Bahraini dinar', 'braintree-payments' ), 
			'BIF' => __( 'Burundian franc', 'braintree-payments' ), 
			'BMD' => __( 'Bermudian dollar', 'braintree-payments' ), 
			'BND' => __( 'Brunei dollar', 'braintree-payments' ), 
			'BOB' => __( 'Bolivian boliviano', 'braintree-payments' ), 
			'BRL' => __( 'Brazilian real', 'braintree-payments' ), 
			'BSD' => __( 'Bahamian dollar', 'braintree-payments' ), 
			'BTC' => __( 'Bitcoin', 'braintree-payments' ), 
			'BTN' => __( 'Bhutanese ngultrum', 'braintree-payments' ), 
			'BWP' => __( 'Botswana pula', 'braintree-payments' ), 
			'BYR' => __( 'Belarusian ruble', 'braintree-payments' ), 
			'BZD' => __( 'Belize dollar', 'braintree-payments' ), 
			'CAD' => __( 'Canadian dollar', 'braintree-payments' ), 
			'CDF' => __( 'Congolese franc', 'braintree-payments' ), 
			'CHF' => __( 'Swiss franc', 'braintree-payments' ), 
			'CLP' => __( 'Chilean peso', 'braintree-payments' ), 
			'CNY' => __( 'Chinese yuan', 'braintree-payments' ), 
			'COP' => __( 'Colombian peso', 'braintree-payments' ), 
			'CRC' => __( 'Costa Rican col&oacute;n', 'braintree-payments' ), 
			'CUC' => __( 'Cuban convertible peso', 'braintree-payments' ), 
			'CUP' => __( 'Cuban peso', 'braintree-payments' ), 
			'CVE' => __( 'Cape Verdean escudo', 'braintree-payments' ), 
			'CZK' => __( 'Czech koruna', 'braintree-payments' ), 
			'DJF' => __( 'Djiboutian franc', 'braintree-payments' ), 
			'DKK' => __( 'Danish krone', 'braintree-payments' ), 
			'DOP' => __( 'Dominican peso', 'braintree-payments' ), 
			'DZD' => __( 'Algerian dinar', 'braintree-payments' ), 
			'EGP' => __( 'Egyptian pound', 'braintree-payments' ), 
			'ERN' => __( 'Eritrean nakfa', 'braintree-payments' ), 
			'ETB' => __( 'Ethiopian birr', 'braintree-payments' ), 
			'EUR' => __( 'Euro', 'braintree-payments' ), 
			'FJD' => __( 'Fijian dollar', 'braintree-payments' ), 
			'FKP' => __( 'Falkland Islands pound', 'braintree-payments' ), 
			'GBP' => __( 'Pound sterling', 'braintree-payments' ), 
			'GEL' => __( 'Georgian lari', 'braintree-payments' ), 
			'GGP' => __( 'Guernsey pound', 'braintree-payments' ), 
			'GHS' => __( 'Ghana cedi', 'braintree-payments' ), 
			'GIP' => __( 'Gibraltar pound', 'braintree-payments' ), 
			'GMD' => __( 'Gambian dalasi', 'braintree-payments' ), 
			'GNF' => __( 'Guinean franc', 'braintree-payments' ), 
			'GTQ' => __( 'Guatemalan quetzal', 'braintree-payments' ), 
			'GYD' => __( 'Guyanese dollar', 'braintree-payments' ), 
			'HKD' => __( 'Hong Kong dollar', 'braintree-payments' ), 
			'HNL' => __( 'Honduran lempira', 'braintree-payments' ), 
			'HRK' => __( 'Croatian kuna', 'braintree-payments' ), 
			'HTG' => __( 'Haitian gourde', 'braintree-payments' ), 
			'HUF' => __( 'Hungarian forint', 'braintree-payments' ), 
			'IDR' => __( 'Indonesian rupiah', 'braintree-payments' ), 
			'ILS' => __( 'Israeli new shekel', 'braintree-payments' ), 
			'IMP' => __( 'Manx pound', 'braintree-payments' ), 
			'INR' => __( 'Indian rupee', 'braintree-payments' ), 
			'IQD' => __( 'Iraqi dinar', 'braintree-payments' ), 
			'IRR' => __( 'Iranian rial', 'braintree-payments' ), 
			'ISK' => __( 'Icelandic kr&oacute;na', 'braintree-payments' ), 
			'JEP' => __( 'Jersey pound', 'braintree-payments' ), 
			'JMD' => __( 'Jamaican dollar', 'braintree-payments' ), 
			'JOD' => __( 'Jordanian dinar', 'braintree-payments' ), 
			'JPY' => __( 'Japanese yen', 'braintree-payments' ), 
			'KES' => __( 'Kenyan shilling', 'braintree-payments' ), 
			'KGS' => __( 'Kyrgyzstani som', 'braintree-payments' ), 
			'KHR' => __( 'Cambodian riel', 'braintree-payments' ), 
			'KMF' => __( 'Comorian franc', 'braintree-payments' ), 
			'KPW' => __( 'North Korean won', 'braintree-payments' ), 
			'KRW' => __( 'South Korean won', 'braintree-payments' ), 
			'KWD' => __( 'Kuwaiti dinar', 'braintree-payments' ), 
			'KYD' => __( 'Cayman Islands dollar', 'braintree-payments' ), 
			'KZT' => __( 'Kazakhstani tenge', 'braintree-payments' ), 
			'LAK' => __( 'Lao kip', 'braintree-payments' ), 
			'LBP' => __( 'Lebanese pound', 'braintree-payments' ), 
			'LKR' => __( 'Sri Lankan rupee', 'braintree-payments' ), 
			'LRD' => __( 'Liberian dollar', 'braintree-payments' ), 
			'LSL' => __( 'Lesotho loti', 'braintree-payments' ), 
			'LYD' => __( 'Libyan dinar', 'braintree-payments' ), 
			'MAD' => __( 'Moroccan dirham', 'braintree-payments' ), 
			'MDL' => __( 'Moldovan leu', 'braintree-payments' ), 
			'MGA' => __( 'Malagasy ariary', 'braintree-payments' ), 
			'MKD' => __( 'Macedonian denar', 'braintree-payments' ), 
			'MMK' => __( 'Burmese kyat', 'braintree-payments' ), 
			'MNT' => __( 'Mongolian t&ouml;gr&ouml;g', 'braintree-payments' ), 
			'MOP' => __( 'Macanese pataca', 'braintree-payments' ), 
			'MRO' => __( 'Mauritanian ouguiya', 'braintree-payments' ), 
			'MUR' => __( 'Mauritian rupee', 'braintree-payments' ), 
			'MVR' => __( 'Maldivian rufiyaa', 'braintree-payments' ), 
			'MWK' => __( 'Malawian kwacha', 'braintree-payments' ), 
			'MXN' => __( 'Mexican peso', 'braintree-payments' ), 
			'MYR' => __( 'Malaysian ringgit', 'braintree-payments' ), 
			'MZN' => __( 'Mozambican metical', 'braintree-payments' ), 
			'NAD' => __( 'Namibian dollar', 'braintree-payments' ), 
			'NGN' => __( 'Nigerian naira', 'braintree-payments' ), 
			'NIO' => __( 'Nicaraguan c&oacute;rdoba', 'braintree-payments' ), 
			'NOK' => __( 'Norwegian krone', 'braintree-payments' ), 
			'NPR' => __( 'Nepalese rupee', 'braintree-payments' ), 
			'NZD' => __( 'New Zealand dollar', 'braintree-payments' ), 
			'OMR' => __( 'Omani rial', 'braintree-payments' ), 
			'PAB' => __( 'Panamanian balboa', 'braintree-payments' ), 
			'PEN' => __( 'Peruvian nuevo sol', 'braintree-payments' ), 
			'PGK' => __( 'Papua New Guinean kina', 'braintree-payments' ), 
			'PHP' => __( 'Philippine peso', 'braintree-payments' ), 
			'PKR' => __( 'Pakistani rupee', 'braintree-payments' ), 
			'PLN' => __( 'Polish z&#x142;oty', 'braintree-payments' ), 
			'PRB' => __( 'Transnistrian ruble', 'braintree-payments' ), 
			'PYG' => __( 'Paraguayan guaran&iacute;', 'braintree-payments' ), 
			'QAR' => __( 'Qatari riyal', 'braintree-payments' ), 
			'RON' => __( 'Romanian leu', 'braintree-payments' ), 
			'RSD' => __( 'Serbian dinar', 'braintree-payments' ), 
			'RUB' => __( 'Russian ruble', 'braintree-payments' ), 
			'RWF' => __( 'Rwandan franc', 'braintree-payments' ), 
			'SAR' => __( 'Saudi riyal', 'braintree-payments' ), 
			'SBD' => __( 'Solomon Islands dollar', 'braintree-payments' ), 
			'SCR' => __( 'Seychellois rupee', 'braintree-payments' ), 
			'SDG' => __( 'Sudanese pound', 'braintree-payments' ), 
			'SEK' => __( 'Swedish krona', 'braintree-payments' ), 
			'SGD' => __( 'Singapore dollar', 'braintree-payments' ), 
			'SHP' => __( 'Saint Helena pound', 'braintree-payments' ), 
			'SLL' => __( 'Sierra Leonean leone', 'braintree-payments' ), 
			'SOS' => __( 'Somali shilling', 'braintree-payments' ), 
			'SRD' => __( 'Surinamese dollar', 'braintree-payments' ), 
			'SSP' => __( 'South Sudanese pound', 'braintree-payments' ), 
			'STD' => __( 'S&atilde;o Tom&eacute; and Pr&iacute;ncipe dobra', 'braintree-payments' ), 
			'SYP' => __( 'Syrian pound', 'braintree-payments' ), 
			'SZL' => __( 'Swazi lilangeni', 'braintree-payments' ), 
			'THB' => __( 'Thai baht', 'braintree-payments' ), 
			'TJS' => __( 'Tajikistani somoni', 'braintree-payments' ), 
			'TMT' => __( 'Turkmenistan manat', 'braintree-payments' ), 
			'TND' => __( 'Tunisian dinar', 'braintree-payments' ), 
			'TOP' => __( 'Tongan pa&#x2bb;anga', 'braintree-payments' ), 
			'TRY' => __( 'Turkish lira', 'braintree-payments' ), 
			'TTD' => __( 'Trinidad and Tobago dollar', 'braintree-payments' ), 
			'TWD' => __( 'New Taiwan dollar', 'braintree-payments' ), 
			'TZS' => __( 'Tanzanian shilling', 'braintree-payments' ), 
			'UAH' => __( 'Ukrainian hryvnia', 'braintree-payments' ), 
			'UGX' => __( 'Ugandan shilling', 'braintree-payments' ), 
			'USD' => __( 'United States dollar', 'braintree-payments' ), 
			'UYU' => __( 'Uruguayan peso', 'braintree-payments' ), 
			'UZS' => __( 'Uzbekistani som', 'braintree-payments' ), 
			'VEF' => __( 'Venezuelan bol&iacute;var', 'braintree-payments' ), 
			'VND' => __( 'Vietnamese &#x111;&#x1ed3;ng', 'braintree-payments' ), 
			'VUV' => __( 'Vanuatu vatu', 'braintree-payments' ), 
			'WST' => __( 'Samoan t&#x101;l&#x101;', 'braintree-payments' ), 
			'XAF' => __( 'Central African CFA franc', 'braintree-payments' ), 
			'XCD' => __( 'East Caribbean dollar', 'braintree-payments' ), 
			'XOF' => __( 'West African CFA franc', 'braintree-payments' ), 
			'XPF' => __( 'CFP franc', 'braintree-payments' ), 
			'YER' => __( 'Yemeni rial', 'braintree-payments' ), 
			'ZAR' => __( 'South African rand', 'braintree-payments' ), 
			'ZMW' => __( 'Zambian kwacha', 'braintree-payments' ) 
	) );
}

function braintree_get_countries()
{
	return array (
			'AF' => 'Afghanistan', 
			'AX' => 'Aland Islands', 
			'AL' => 'Albania', 
			'DZ' => 'Algeria', 
			'AS' => 'American Samoa', 
			'AD' => 'Andorra', 
			'AO' => 'Angola', 
			'AI' => 'Anguilla', 
			'AQ' => 'Antarctica', 
			'AG' => 'Antigua And Barbuda', 
			'AR' => 'Argentina', 
			'AM' => 'Armenia', 
			'AW' => 'Aruba', 
			'AU' => 'Australia', 
			'AT' => 'Austria', 
			'AZ' => 'Azerbaijan', 
			'BS' => 'Bahamas', 
			'BH' => 'Bahrain', 
			'BD' => 'Bangladesh', 
			'BB' => 'Barbados', 
			'BY' => 'Belarus', 
			'BE' => 'Belgium', 
			'BZ' => 'Belize', 
			'BJ' => 'Benin', 
			'BM' => 'Bermuda', 
			'BT' => 'Bhutan', 
			'BO' => 'Bolivia', 
			'BA' => 'Bosnia And Herzegovina', 
			'BW' => 'Botswana', 
			'BV' => 'Bouvet Island', 
			'BR' => 'Brazil', 
			'IO' => 'British Indian Ocean Territory', 
			'BN' => 'Brunei Darussalam', 
			'BG' => 'Bulgaria', 
			'BF' => 'Burkina Faso', 
			'BI' => 'Burundi', 
			'KH' => 'Cambodia', 
			'CM' => 'Cameroon', 
			'CA' => 'Canada', 
			'CV' => 'Cape Verde', 
			'KY' => 'Cayman Islands', 
			'CF' => 'Central African Republic', 
			'TD' => 'Chad', 
			'CL' => 'Chile', 
			'CN' => 'China', 
			'CX' => 'Christmas Island', 
			'CC' => 'Cocos (Keeling) Islands', 
			'CO' => 'Colombia', 
			'KM' => 'Comoros', 
			'CG' => 'Congo', 
			'CD' => 'Congo, Democratic Republic', 
			'CK' => 'Cook Islands', 
			'CR' => 'Costa Rica', 
			'CI' => 'Cote D\'Ivoire', 
			'HR' => 'Croatia', 
			'CU' => 'Cuba', 
			'CY' => 'Cyprus', 
			'CZ' => 'Czech Republic', 
			'DK' => 'Denmark', 
			'DJ' => 'Djibouti', 
			'DM' => 'Dominica', 
			'DO' => 'Dominican Republic', 
			'EC' => 'Ecuador', 
			'EG' => 'Egypt', 
			'SV' => 'El Salvador', 
			'GQ' => 'Equatorial Guinea', 
			'ER' => 'Eritrea', 
			'EE' => 'Estonia', 
			'ET' => 'Ethiopia', 
			'FK' => 'Falkland Islands (Malvinas)', 
			'FO' => 'Faroe Islands', 
			'FJ' => 'Fiji', 
			'FI' => 'Finland', 
			'FR' => 'France', 
			'GF' => 'French Guiana', 
			'PF' => 'French Polynesia', 
			'TF' => 'French Southern Territories', 
			'GA' => 'Gabon', 
			'GM' => 'Gambia', 
			'GE' => 'Georgia', 
			'DE' => 'Germany', 
			'GH' => 'Ghana', 
			'GI' => 'Gibraltar', 
			'GR' => 'Greece', 
			'GL' => 'Greenland', 
			'GD' => 'Grenada', 
			'GP' => 'Guadeloupe', 
			'GU' => 'Guam', 
			'GT' => 'Guatemala', 
			'GG' => 'Guernsey', 
			'GN' => 'Guinea', 
			'GW' => 'Guinea-Bissau', 
			'GY' => 'Guyana', 
			'HT' => 'Haiti', 
			'HM' => 'Heard Island & Mcdonald Islands', 
			'VA' => 'Holy See (Vatican City State)', 
			'HN' => 'Honduras', 
			'HK' => 'Hong Kong', 
			'HU' => 'Hungary', 
			'IS' => 'Iceland', 
			'IN' => 'India', 
			'ID' => 'Indonesia', 
			'IR' => 'Iran, Islamic Republic Of', 
			'IQ' => 'Iraq', 
			'IE' => 'Ireland', 
			'IM' => 'Isle Of Man', 
			'IL' => 'Israel', 
			'IT' => 'Italy', 
			'JM' => 'Jamaica', 
			'JP' => 'Japan', 
			'JE' => 'Jersey', 
			'JO' => 'Jordan', 
			'KZ' => 'Kazakhstan', 
			'KE' => 'Kenya', 
			'KI' => 'Kiribati', 
			'KR' => 'Korea', 
			'KW' => 'Kuwait', 
			'KG' => 'Kyrgyzstan', 
			'LA' => 'Lao People\'s Democratic Republic', 
			'LV' => 'Latvia', 
			'LB' => 'Lebanon', 
			'LS' => 'Lesotho', 
			'LR' => 'Liberia', 
			'LY' => 'Libyan Arab Jamahiriya', 
			'LI' => 'Liechtenstein', 
			'LT' => 'Lithuania', 
			'LU' => 'Luxembourg', 
			'MO' => 'Macao', 
			'MK' => 'Macedonia', 
			'MG' => 'Madagascar', 
			'MW' => 'Malawi', 
			'MY' => 'Malaysia', 
			'MV' => 'Maldives', 
			'ML' => 'Mali', 
			'MT' => 'Malta', 
			'MH' => 'Marshall Islands', 
			'MQ' => 'Martinique', 
			'MR' => 'Mauritania', 
			'MU' => 'Mauritius', 
			'YT' => 'Mayotte', 
			'MX' => 'Mexico', 
			'FM' => 'Micronesia, Federated States Of', 
			'MD' => 'Moldova', 
			'MC' => 'Monaco', 
			'MN' => 'Mongolia', 
			'ME' => 'Montenegro', 
			'MS' => 'Montserrat', 
			'MA' => 'Morocco', 
			'MZ' => 'Mozambique', 
			'MM' => 'Myanmar', 
			'NA' => 'Namibia', 
			'NR' => 'Nauru', 
			'NP' => 'Nepal', 
			'NL' => 'Netherlands', 
			'AN' => 'Netherlands Antilles', 
			'NC' => 'New Caledonia', 
			'NZ' => 'New Zealand', 
			'NI' => 'Nicaragua', 
			'NE' => 'Niger', 
			'NG' => 'Nigeria', 
			'NU' => 'Niue', 
			'NF' => 'Norfolk Island', 
			'MP' => 'Northern Mariana Islands', 
			'NO' => 'Norway', 
			'OM' => 'Oman', 
			'PK' => 'Pakistan', 
			'PW' => 'Palau', 
			'PS' => 'Palestinian Territory, Occupied', 
			'PA' => 'Panama', 
			'PG' => 'Papua New Guinea', 
			'PY' => 'Paraguay', 
			'PE' => 'Peru', 
			'PH' => 'Philippines', 
			'PN' => 'Pitcairn', 
			'PL' => 'Poland', 
			'PT' => 'Portugal', 
			'PR' => 'Puerto Rico', 
			'QA' => 'Qatar', 
			'RE' => 'Reunion', 
			'RO' => 'Romania', 
			'RU' => 'Russian Federation', 
			'RW' => 'Rwanda', 
			'BL' => 'Saint Barthelemy', 
			'SH' => 'Saint Helena', 
			'KN' => 'Saint Kitts And Nevis', 
			'LC' => 'Saint Lucia', 
			'MF' => 'Saint Martin', 
			'PM' => 'Saint Pierre And Miquelon', 
			'VC' => 'Saint Vincent And Grenadines', 
			'WS' => 'Samoa', 
			'SM' => 'San Marino', 
			'ST' => 'Sao Tome And Principe', 
			'SA' => 'Saudi Arabia', 
			'SN' => 'Senegal', 
			'RS' => 'Serbia', 
			'SC' => 'Seychelles', 
			'SL' => 'Sierra Leone', 
			'SG' => 'Singapore', 
			'SK' => 'Slovakia', 
			'SI' => 'Slovenia', 
			'SB' => 'Solomon Islands', 
			'SO' => 'Somalia', 
			'ZA' => 'South Africa', 
			'GS' => 'South Georgia And Sandwich Isl.', 
			'ES' => 'Spain', 
			'LK' => 'Sri Lanka', 
			'SD' => 'Sudan', 
			'SR' => 'Suriname', 
			'SJ' => 'Svalbard And Jan Mayen', 
			'SZ' => 'Swaziland', 
			'SE' => 'Sweden', 
			'CH' => 'Switzerland', 
			'SY' => 'Syrian Arab Republic', 
			'TW' => 'Taiwan', 
			'TJ' => 'Tajikistan', 
			'TZ' => 'Tanzania', 
			'TH' => 'Thailand', 
			'TL' => 'Timor-Leste', 
			'TG' => 'Togo', 
			'TK' => 'Tokelau', 
			'TO' => 'Tonga', 
			'TT' => 'Trinidad And Tobago', 
			'TN' => 'Tunisia', 
			'TR' => 'Turkey', 
			'TM' => 'Turkmenistan', 
			'TC' => 'Turks And Caicos Islands', 
			'TV' => 'Tuvalu', 
			'UG' => 'Uganda', 
			'UA' => 'Ukraine', 
			'AE' => 'United Arab Emirates', 
			'GB' => 'United Kingdom', 
			'US' => 'United States', 
			'UM' => 'United States Outlying Islands', 
			'UY' => 'Uruguay', 
			'UZ' => 'Uzbekistan', 
			'VU' => 'Vanuatu', 
			'VE' => 'Venezuela', 
			'VN' => 'Viet Nam', 
			'VG' => 'Virgin Islands, British', 
			'VI' => 'Virgin Islands, U.S.', 
			'WF' => 'Wallis And Futuna', 
			'EH' => 'Western Sahara', 
			'YE' => 'Yemen', 
			'ZM' => 'Zambia', 
			'ZW' => 'Zimbabwe' 
	);
}

function braintree_get_states()
{
	return apply_filters( 'braintree_get_states', array (
			'AL' => __( 'Alabama', 'braintree-payments' ), 
			'AK' => __( 'Alaska', 'braintree-payments' ), 
			'AZ' => __( 'Arizona', 'braintree-payments' ), 
			'AR' => __( 'Arkansas', 'braintree-payments' ), 
			'CA' => __( 'California', 'braintree-payments' ), 
			'CO' => __( 'Colorado', 'braintree-payments' ), 
			'CT' => __( 'Connecticut', 'braintree-payments' ), 
			'DE' => __( 'Delaware', 'braintree-payments' ), 
			'DC' => __( 'District Of Columbia', 'braintree-payments' ), 
			'FL' => __( 'Florida', 'braintree-payments' ), 
			'GA' => _x( 'Georgia', 'US state of Georgia', 'braintree-payments' ), 
			'HI' => __( 'Hawaii', 'braintree-payments' ), 
			'ID' => __( 'Idaho', 'braintree-payments' ), 
			'IL' => __( 'Illinois', 'braintree-payments' ), 
			'IN' => __( 'Indiana', 'braintree-payments' ), 
			'IA' => __( 'Iowa', 'braintree-payments' ), 
			'KS' => __( 'Kansas', 'braintree-payments' ), 
			'KY' => __( 'Kentucky', 'braintree-payments' ), 
			'LA' => __( 'Louisiana', 'braintree-payments' ), 
			'ME' => __( 'Maine', 'braintree-payments' ), 
			'MD' => __( 'Maryland', 'braintree-payments' ), 
			'MA' => __( 'Massachusetts', 'braintree-payments' ), 
			'MI' => __( 'Michigan', 'braintree-payments' ), 
			'MN' => __( 'Minnesota', 'braintree-payments' ), 
			'MS' => __( 'Mississippi', 'braintree-payments' ), 
			'MO' => __( 'Missouri', 'braintree-payments' ), 
			'MT' => __( 'Montana', 'braintree-payments' ), 
			'NE' => __( 'Nebraska', 'braintree-payments' ), 
			'NV' => __( 'Nevada', 'braintree-payments' ), 
			'NH' => __( 'New Hampshire', 'braintree-payments' ), 
			'NJ' => __( 'New Jersey', 'braintree-payments' ), 
			'NM' => __( 'New Mexico', 'braintree-payments' ), 
			'NY' => __( 'New York', 'braintree-payments' ), 
			'NC' => __( 'North Carolina', 'braintree-payments' ), 
			'ND' => __( 'North Dakota', 'braintree-payments' ), 
			'OH' => __( 'Ohio', 'braintree-payments' ), 
			'OK' => __( 'Oklahoma', 'braintree-payments' ), 
			'OR' => __( 'Oregon', 'braintree-payments' ), 
			'PA' => __( 'Pennsylvania', 'braintree-payments' ), 
			'RI' => __( 'Rhode Island', 'braintree-payments' ), 
			'SC' => __( 'South Carolina', 'braintree-payments' ), 
			'SD' => __( 'South Dakota', 'braintree-payments' ), 
			'TN' => __( 'Tennessee', 'braintree-payments' ), 
			'TX' => __( 'Texas', 'braintree-payments' ), 
			'UT' => __( 'Utah', 'braintree-payments' ), 
			'VT' => __( 'Vermont', 'braintree-payments' ), 
			'VA' => __( 'Virginia', 'braintree-payments' ), 
			'WA' => __( 'Washington', 'braintree-payments' ), 
			'WV' => __( 'West Virginia', 'braintree-payments' ), 
			'WI' => __( 'Wisconsin', 'braintree-payments' ), 
			'WY' => __( 'Wyoming', 'braintree-payments' ), 
			'AA' => __( 'Armed Forces (AA)', 'braintree-payments' ), 
			'AE' => __( 'Armed Forces (AE)', 'braintree-payments' ), 
			'AP' => __( 'Armed Forces (AP)', 'braintree-payments' ) 
	) );
}

function braintree_get_currency_symbol( $currency = 'USD' )
{
	$symbols = apply_filters( 'braintree_currency_symbols', array (
			'AED' => '&#x62f;.&#x625;', 
			'AFN' => '&#x60b;', 
			'ALL' => 'L', 
			'AMD' => 'AMD', 
			'ANG' => '&fnof;', 
			'AOA' => 'Kz', 
			'ARS' => '&#36;', 
			'AUD' => '&#36;', 
			'AWG' => '&fnof;', 
			'AZN' => 'AZN', 
			'BAM' => 'KM', 
			'BBD' => '&#36;', 
			'BDT' => '&#2547;&nbsp;', 
			'BGN' => '&#1083;&#1074;.', 
			'BHD' => '.&#x62f;.&#x628;', 
			'BIF' => 'Fr', 
			'BMD' => '&#36;', 
			'BND' => '&#36;', 
			'BOB' => 'Bs.', 
			'BRL' => '&#82;&#36;', 
			'BSD' => '&#36;', 
			'BTC' => '&#3647;', 
			'BTN' => 'Nu.', 
			'BWP' => 'P', 
			'BYR' => 'Br', 
			'BZD' => '&#36;', 
			'CAD' => '&#36;', 
			'CDF' => 'Fr', 
			'CHF' => '&#67;&#72;&#70;', 
			'CLP' => '&#36;', 
			'CNY' => '&yen;', 
			'COP' => '&#36;', 
			'CRC' => '&#x20a1;', 
			'CUC' => '&#36;', 
			'CUP' => '&#36;', 
			'CVE' => '&#36;', 
			'CZK' => '&#75;&#269;', 
			'DJF' => 'Fr', 
			'DKK' => 'DKK', 
			'DOP' => 'RD&#36;', 
			'DZD' => '&#x62f;.&#x62c;', 
			'EGP' => 'EGP', 
			'ERN' => 'Nfk', 
			'ETB' => 'Br', 
			'EUR' => '&euro;', 
			'FJD' => '&#36;', 
			'FKP' => '&pound;', 
			'GBP' => '&pound;', 
			'GEL' => '&#x10da;', 
			'GGP' => '&pound;', 
			'GHS' => '&#x20b5;', 
			'GIP' => '&pound;', 
			'GMD' => 'D', 
			'GNF' => 'Fr', 
			'GTQ' => 'Q', 
			'GYD' => '&#36;', 
			'HKD' => '&#36;', 
			'HNL' => 'L', 
			'HRK' => 'Kn', 
			'HTG' => 'G', 
			'HUF' => '&#70;&#116;', 
			'IDR' => 'Rp', 
			'ILS' => '&#8362;', 
			'IMP' => '&pound;', 
			'INR' => '&#8377;', 
			'IQD' => '&#x639;.&#x62f;', 
			'IRR' => '&#xfdfc;', 
			'ISK' => 'Kr.', 
			'JEP' => '&pound;', 
			'JMD' => '&#36;', 
			'JOD' => '&#x62f;.&#x627;', 
			'JPY' => '&yen;', 
			'KES' => 'KSh', 
			'KGS' => '&#x43b;&#x432;', 
			'KHR' => '&#x17db;', 
			'KMF' => 'Fr', 
			'KPW' => '&#x20a9;', 
			'KRW' => '&#8361;', 
			'KWD' => '&#x62f;.&#x643;', 
			'KYD' => '&#36;', 
			'KZT' => 'KZT', 
			'LAK' => '&#8365;', 
			'LBP' => '&#x644;.&#x644;', 
			'LKR' => '&#xdbb;&#xdd4;', 
			'LRD' => '&#36;', 
			'LSL' => 'L', 
			'LYD' => '&#x644;.&#x62f;', 
			'MAD' => '&#x62f;. &#x645;.', 
			'MAD' => '&#x62f;.&#x645;.', 
			'MDL' => 'L', 
			'MGA' => 'Ar', 
			'MKD' => '&#x434;&#x435;&#x43d;', 
			'MMK' => 'Ks', 
			'MNT' => '&#x20ae;', 
			'MOP' => 'P', 
			'MRO' => 'UM', 
			'MUR' => '&#x20a8;', 
			'MVR' => '.&#x783;', 
			'MWK' => 'MK', 
			'MXN' => '&#36;', 
			'MYR' => '&#82;&#77;', 
			'MZN' => 'MT', 
			'NAD' => '&#36;', 
			'NGN' => '&#8358;', 
			'NIO' => 'C&#36;', 
			'NOK' => '&#107;&#114;', 
			'NPR' => '&#8360;', 
			'NZD' => '&#36;', 
			'OMR' => '&#x631;.&#x639;.', 
			'PAB' => 'B/.', 
			'PEN' => 'S/.', 
			'PGK' => 'K', 
			'PHP' => '&#8369;', 
			'PKR' => '&#8360;', 
			'PLN' => '&#122;&#322;', 
			'PRB' => '&#x440;.', 
			'PYG' => '&#8370;', 
			'QAR' => '&#x631;.&#x642;', 
			'RMB' => '&yen;', 
			'RON' => 'lei', 
			'RSD' => '&#x434;&#x438;&#x43d;.', 
			'RUB' => '&#8381;', 
			'RWF' => 'Fr', 
			'SAR' => '&#x631;.&#x633;', 
			'SBD' => '&#36;', 
			'SCR' => '&#x20a8;', 
			'SDG' => '&#x62c;.&#x633;.', 
			'SEK' => '&#107;&#114;', 
			'SGD' => '&#36;', 
			'SHP' => '&pound;', 
			'SLL' => 'Le', 
			'SOS' => 'Sh', 
			'SRD' => '&#36;', 
			'SSP' => '&pound;', 
			'STD' => 'Db', 
			'SYP' => '&#x644;.&#x633;', 
			'SZL' => 'L', 
			'THB' => '&#3647;', 
			'TJS' => '&#x405;&#x41c;', 
			'TMT' => 'm', 
			'TND' => '&#x62f;.&#x62a;', 
			'TOP' => 'T&#36;', 
			'TRY' => '&#8378;', 
			'TTD' => '&#36;', 
			'TWD' => '&#78;&#84;&#36;', 
			'TZS' => 'Sh', 
			'UAH' => '&#8372;', 
			'UGX' => 'UGX', 
			'USD' => '&#36;', 
			'UYU' => '&#36;', 
			'UZS' => 'UZS', 
			'VEF' => 'Bs F', 
			'VND' => '&#8363;', 
			'VUV' => 'Vt', 
			'WST' => 'T', 
			'XAF' => 'Fr', 
			'XCD' => '&#36;', 
			'XOF' => 'Fr', 
			'XPF' => 'Fr', 
			'YER' => '&#xfdfc;', 
			'ZAR' => '&#82;', 
			'ZMW' => 'ZK' 
	) );
	
	$currency_symbol = isset( $symbols [ $currency ] ) ? $symbols [ $currency ] : '';
	return $currency_symbol;
}

function braintree_filter_sale_transactions( $transaction )
{
	return $transaction->type === Braintree_Transaction::SALE;
}

function braintree_filter_credit_transactions( $transaction )
{
	return $transaction->type === Braintree_Transaction::CREDIT;
}

/**
 * Echo an input field for a braintree payment_method_nonce.
 */
function braintree_nonce_field( $name = 'payment_method_nonce' )
{
	$field = '<input type="hidden" class="bfwc-nonce-value" id="' . $name . '" name="' . $name . '"/>';
	echo $field;
}

function braintree_payment_token_field( $id, $token = '' )
{
	$field = '<input type="hidden" class="bfwc-payment-method-token" id="' . $id . '" name="' . $id . '" value="' . $token . '"/>';
	echo $field;
}

/**
 * Return a class name for the payment method.
 *
 * @param array $method        	
 * @return mixed|string
 */
function braintree_get_payment_method_class( $method )
{
	switch( $method [ 'type' ] ) {
		case PaymentInstrumentType::CREDIT_CARD :
		case PaymentInstrumentType::PAYPAL_ACCOUNT :
			return str_replace( ' ', '', $method [ 'method_type' ] );
			break;
		case PaymentInstrumentType::APPLE_PAY_CARD :
			return preg_match( '/[a-z]+/i', $method [ 'payment_instrument_name' ], $matches ) ? $matches [ 0 ] : $method [ 'method_type' ];
			break;
	}
}

/**
 * Echo an input field for a braintree device data hidden field.
 *
 * @param string $name        	
 */
function braintree_device_data_field( $name = 'braintree_device_data' )
{
	$field = '<input type="hidden" class="bfwc-device-data" id="' . $name . '" name="' . $name . '"/>';
	echo $field;
}

/**
 * Return a custom message associated with the Braintree error code.
 *
 * @param number $code        	
 * @return string
 */
function braintree_get_error_code_message( $code = 0 )
{
	$errors = array (
			'91506' => __( 'Cannot refund a transaction unless it has a status of settled. To refund a partial amount, you must wait for the status to be updated. 
                    If you wish to refund the entire transaction then you can void the transaction.', 'braintree-payments' ) 
	);
	return isset( $errors [ $code ] ) ? preg_replace( '/\s\s+/', ' ', $errors [ $code ] ) : '';
}

/**
 * Globalize the Braintree errors so they can be used throughout the
 * application.
 *
 * @param Braintree_Result_Error $result        	
 */
function braintree_global_errors( $result )
{
	global $braintree_result_errors;
	$braintree_result_errors = array ();
	foreach ( $result->errors->deepAll() as $error ) {
		$braintree_result_errors [] = $error->code;
	}
}

/**
 * Return an array of PayPal buttons.
 */
function braintree_get_paypal_buttons()
{
	return include bt_manager()->plugin_include_path() . 'paypal-buttons.php';
}

function braintree_get_paypal_credit_buttons()
{
	return include bt_manager()->plugin_include_path() . 'paypal-credit-buttons.php';
}

function braintree_get_custom_donation_forms()
{
	return include bt_manager()->plugin_include_path() . 'braintree-custom-donation-forms.php';
}