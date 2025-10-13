<?php
/**
 * TBO Hotels API Functions
 * 
 * Functions for country and city API integration
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Get all countries from API
 */
function tbo_hotels_get_countries() {
    // For now, return static country data
    // In production, make an API request to TBO API
    
    // Common countries
    $common_countries = array(
        'IN' => 'India',
        'TH' => 'Thailand',
        'SG' => 'Singapore',
        'MY' => 'Malaysia',
        'AE' => 'United Arab Emirates',
        'US' => 'United States',
        'GB' => 'United Kingdom',
        'FR' => 'France',
        'IT' => 'Italy',
        'ES' => 'Spain'
    );
    
    // All countries (sorted alphabetically)
    $all_countries = array(
        'AF' => 'Afghanistan',
        'AL' => 'Albania',
        'DZ' => 'Algeria',
        'AS' => 'American Samoa',
        'AD' => 'Andorra',
        'AO' => 'Angola',
        'AI' => 'Anguilla',
        'AQ' => 'Antarctica',
        'AG' => 'Antigua and Barbuda',
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
        'BA' => 'Bosnia and Herzegovina',
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
        'CD' => 'Congo, the Democratic Republic of the',
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
        'GN' => 'Guinea',
        'GW' => 'Guinea-Bissau',
        'GY' => 'Guyana',
        'HT' => 'Haiti',
        'HM' => 'Heard Island and Mcdonald Islands',
        'VA' => 'Holy See (Vatican City State)',
        'HN' => 'Honduras',
        'HK' => 'Hong Kong',
        'HU' => 'Hungary',
        'IS' => 'Iceland',
        'IN' => 'India',
        'ID' => 'Indonesia',
        'IR' => 'Iran, Islamic Republic of',
        'IQ' => 'Iraq',
        'IE' => 'Ireland',
        'IL' => 'Israel',
        'IT' => 'Italy',
        'JM' => 'Jamaica',
        'JP' => 'Japan',
        'JO' => 'Jordan',
        'KZ' => 'Kazakhstan',
        'KE' => 'Kenya',
        'KI' => 'Kiribati',
        'KP' => 'Korea, Democratic People\'s Republic of',
        'KR' => 'Korea, Republic of',
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
        'MK' => 'Macedonia, the Former Yugoslav Republic of',
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
        'FM' => 'Micronesia, Federated States of',
        'MD' => 'Moldova, Republic of',
        'MC' => 'Monaco',
        'MN' => 'Mongolia',
        'MS' => 'Montserrat',
        'MA' => 'Morocco',
        'MZ' => 'Mozambique',
        'MM' => 'Myanmar',
        'NA' => 'Namibia',
        'NR' => 'Nauru',
        'NP' => 'Nepal',
        'NL' => 'Netherlands',
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
        'SH' => 'Saint Helena',
        'KN' => 'Saint Kitts and Nevis',
        'LC' => 'Saint Lucia',
        'PM' => 'Saint Pierre and Miquelon',
        'VC' => 'Saint Vincent and the Grenadines',
        'WS' => 'Samoa',
        'SM' => 'San Marino',
        'ST' => 'Sao Tome and Principe',
        'SA' => 'Saudi Arabia',
        'SN' => 'Senegal',
        'SC' => 'Seychelles',
        'SL' => 'Sierra Leone',
        'SG' => 'Singapore',
        'SK' => 'Slovakia',
        'SI' => 'Slovenia',
        'SB' => 'Solomon Islands',
        'SO' => 'Somalia',
        'ZA' => 'South Africa',
        'GS' => 'South Georgia and the South Sandwich Islands',
        'ES' => 'Spain',
        'LK' => 'Sri Lanka',
        'SD' => 'Sudan',
        'SR' => 'Suriname',
        'SJ' => 'Svalbard and Jan Mayen',
        'SZ' => 'Swaziland',
        'SE' => 'Sweden',
        'CH' => 'Switzerland',
        'SY' => 'Syrian Arab Republic',
        'TW' => 'Taiwan, Province of China',
        'TJ' => 'Tajikistan',
        'TZ' => 'Tanzania, United Republic of',
        'TH' => 'Thailand',
        'TL' => 'Timor-Leste',
        'TG' => 'Togo',
        'TK' => 'Tokelau',
        'TO' => 'Tonga',
        'TT' => 'Trinidad and Tobago',
        'TN' => 'Tunisia',
        'TR' => 'Turkey',
        'TM' => 'Turkmenistan',
        'TC' => 'Turks and Caicos Islands',
        'TV' => 'Tuvalu',
        'UG' => 'Uganda',
        'UA' => 'Ukraine',
        'AE' => 'United Arab Emirates',
        'GB' => 'United Kingdom',
        'US' => 'United States',
        'UM' => 'United States Minor Outlying Islands',
        'UY' => 'Uruguay',
        'UZ' => 'Uzbekistan',
        'VU' => 'Vanuatu',
        'VE' => 'Venezuela',
        'VN' => 'Viet Nam',
        'VG' => 'Virgin Islands, British',
        'VI' => 'Virgin Islands, U.S.',
        'WF' => 'Wallis and Futuna',
        'EH' => 'Western Sahara',
        'YE' => 'Yemen',
        'ZM' => 'Zambia',
        'ZW' => 'Zimbabwe'
    );
    
    return array(
        'common_countries' => $common_countries,
        'all_countries' => $all_countries
    );
}

/**
 * Get cities by country code from API
 */
function tbo_hotels_get_cities($country_code) {
    if (empty($country_code)) {
        return array();
    }
    
    // For now, return static city data
    // In production, make an API request to TBO API
    
    // Sample cities data
    $cities = array();
    
    // India
    if ($country_code == 'IN') {
        $cities = array(
            '150184' => 'Mumbai',
            '150087' => 'Delhi',
            '150105' => 'Bangalore',
            '150345' => 'Kolkata',
            '150251' => 'Chennai',
            '150339' => 'Hyderabad',
            '150200' => 'Ahmedabad',
            '150191' => 'Pune',
            '150222' => 'Jaipur',
            '150269' => 'Lucknow',
            '150355' => 'Chandigarh',
            '150254' => 'Goa',
            '150314' => 'Kochi',
            '150267' => 'Agra',
            '150250' => 'Varanasi',
            '150090' => 'Shimla',
            '150255' => 'Amritsar',
            '150333' => 'Rishikesh',
            '150244' => 'Darjeeling',
            '150032' => 'Munnar'
        );
    }
    // Thailand
    elseif ($country_code == 'TH') {
        $cities = array(
            '150034' => 'Bangkok',
            '150067' => 'Phuket',
            '150089' => 'Pattaya',
            '150045' => 'Chiang Mai',
            '150120' => 'Krabi',
            '150123' => 'Koh Samui',
            '150210' => 'Hua Hin',
            '150219' => 'Koh Phi Phi',
            '150300' => 'Koh Tao',
            '150400' => 'Koh Phangan'
        );
    }
    // Singapore
    elseif ($country_code == 'SG') {
        $cities = array(
            '150001' => 'Singapore'
        );
    }
    // Malaysia
    elseif ($country_code == 'MY') {
        $cities = array(
            '150023' => 'Kuala Lumpur',
            '150046' => 'Penang',
            '150079' => 'Langkawi',
            '150130' => 'Malacca',
            '150178' => 'Johor Bahru',
            '150189' => 'Kota Kinabalu',
            '150234' => 'Kuching'
        );
    }
    // United Arab Emirates
    elseif ($country_code == 'AE') {
        $cities = array(
            '150002' => 'Dubai',
            '150003' => 'Abu Dhabi',
            '150025' => 'Sharjah',
            '150056' => 'Ras Al Khaimah',
            '150078' => 'Ajman',
            '150091' => 'Fujairah'
        );
    }
    // United States
    elseif ($country_code == 'US') {
        $cities = array(
            '150004' => 'New York',
            '150005' => 'Los Angeles',
            '150006' => 'Chicago',
            '150007' => 'San Francisco',
            '150008' => 'Miami',
            '150009' => 'Las Vegas',
            '150010' => 'Orlando',
            '150011' => 'Washington DC',
            '150012' => 'Boston',
            '150013' => 'Seattle'
        );
    }
    // United Kingdom
    elseif ($country_code == 'GB') {
        $cities = array(
            '150014' => 'London',
            '150015' => 'Manchester',
            '150016' => 'Edinburgh',
            '150017' => 'Glasgow',
            '150018' => 'Birmingham',
            '150019' => 'Liverpool',
            '150020' => 'Bristol',
            '150021' => 'Oxford',
            '150022' => 'Cambridge',
            '150024' => 'Belfast'
        );
    }
    
    // Sort cities alphabetically
    asort($cities);
    
    return $cities;
}

/**
 * Ajax handler for getting countries
 */
function tbo_hotels_get_countries_ajax() {
    // Check nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'tbo_hotels_nonce')) {
        wp_send_json_error('Invalid nonce');
        return;
    }
    
    // Get countries
    $countries = tbo_hotels_get_countries();
    
    if (is_wp_error($countries)) {
        wp_send_json_error($countries->get_error_message());
        return;
    }
    
    wp_send_json_success($countries);
}
add_action('wp_ajax_tbo_hotels_get_countries', 'tbo_hotels_get_countries_ajax');
add_action('wp_ajax_nopriv_tbo_hotels_get_countries', 'tbo_hotels_get_countries_ajax');

/**
 * Ajax handler for getting cities
 */
function tbo_hotels_get_cities_ajax() {
    // Check nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'tbo_hotels_nonce')) {
        wp_send_json_error('Invalid nonce');
        return;
    }
    
    // Get country code
    $country_code = isset($_POST['country_code']) ? sanitize_text_field($_POST['country_code']) : '';
    
    if (empty($country_code)) {
        wp_send_json_error('Country code is required');
        return;
    }
    
    // Get cities
    $cities = tbo_hotels_get_cities($country_code);
    
    if (is_wp_error($cities)) {
        wp_send_json_error($cities->get_error_message());
        return;
    }
    
    wp_send_json_success($cities);
}
add_action('wp_ajax_tbo_hotels_get_cities', 'tbo_hotels_get_cities_ajax');
add_action('wp_ajax_nopriv_tbo_hotels_get_cities', 'tbo_hotels_get_cities_ajax');