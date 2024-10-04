<?php
require_once FV_COUNTRY_BLOCKER_PLUGIN_DIR . '/vendor/autoload.php'; // Update path

use GeoIp2\Database\Reader;

class FV_GeoIP {
  public static function get_countries_list() {
    return [
      'AF' => ['name' => 'Afghanistan', 'long_name' => 'Afghanistan'],
      'AL' => ['name' => 'Albania', 'long_name' => 'Albania'],
      'DZ' => ['name' => 'Algeria', 'long_name' => 'Algeria'],
      'AS' => ['name' => 'American Samoa', 'long_name' => 'American Samoa'],
      'AD' => ['name' => 'Andorra', 'long_name' => 'Andorra'],
      'AO' => ['name' => 'Angola', 'long_name' => 'Angola'],
      'AI' => ['name' => 'Anguilla', 'long_name' => 'Anguilla'],
      'AQ' => ['name' => 'Antarctica', 'long_name' => 'Antarctica'],
      'AG' => ['name' => 'Antigua & Barbuda', 'long_name' => 'Antigua and Barbuda'],
      'AR' => ['name' => 'Argentina', 'long_name' => 'Argentina'],
      'AM' => ['name' => 'Armenia', 'long_name' => 'Armenia'],
      'AW' => ['name' => 'Aruba', 'long_name' => 'Aruba'],
      'AU' => ['name' => 'Australia', 'long_name' => 'Australia'],
      'AT' => ['name' => 'Austria', 'long_name' => 'Austria'],
      'AZ' => ['name' => 'Azerbaijan', 'long_name' => 'Azerbaijan'],
      'BS' => ['name' => 'Bahamas', 'long_name' => 'Bahamas'],
      'BH' => ['name' => 'Bahrain', 'long_name' => 'Bahrain'],
      'BD' => ['name' => 'Bangladesh', 'long_name' => 'Bangladesh'],
      'BB' => ['name' => 'Barbados', 'long_name' => 'Barbados'],
      'BY' => ['name' => 'Belarus', 'long_name' => 'Belarus'],
      'BE' => ['name' => 'Belgium', 'long_name' => 'Belgium'],
      'BZ' => ['name' => 'Belize', 'long_name' => 'Belize'],
      'BJ' => ['name' => 'Benin', 'long_name' => 'Benin'],
      'BM' => ['name' => 'Bermuda', 'long_name' => 'Bermuda'],
      'BT' => ['name' => 'Bhutan', 'long_name' => 'Bhutan'],
      'BO' => ['name' => 'Bolivia', 'long_name' => 'Bolivia'],
      'BA' => ['name' => 'Bosnia Herzegovina', 'long_name' => 'Bosnia and Herzegovina'],
      'BW' => ['name' => 'Botswana', 'long_name' => 'Botswana'],
      'BV' => ['name' => 'Bouvet Island', 'long_name' => 'Bouvet Island'],
      'BR' => ['name' => 'Brazil', 'long_name' => 'Brazil'],
      'IO' => ['name' => 'British Indian Ocean', 'long_name' => 'British Indian Ocean Territory'],
      'BN' => ['name' => 'Brunei Darussalam', 'long_name' => 'Brunei Darussalam'],
      'BG' => ['name' => 'Bulgaria', 'long_name' => 'Bulgaria'],
      'BF' => ['name' => 'Burkina Faso', 'long_name' => 'Burkina Faso'],
      'BI' => ['name' => 'Burundi', 'long_name' => 'Burundi'],
      'KH' => ['name' => 'Cambodia', 'long_name' => 'Cambodia'],
      'CM' => ['name' => 'Cameroon', 'long_name' => 'Cameroon'],
      'CA' => ['name' => 'Canada', 'long_name' => 'Canada'],
      'CV' => ['name' => 'Cape Verde', 'long_name' => 'Cape Verde'],
      'KY' => ['name' => 'Cayman Islands', 'long_name' => 'Cayman Islands'],
      'CF' => ['name' => 'Central African Republic', 'long_name' => 'Central African Republic'],
      'TD' => ['name' => 'Chad', 'long_name' => 'Chad'],
      'CL' => ['name' => 'Chile', 'long_name' => 'Chile'],
      'CN' => ['name' => 'China', 'long_name' => 'China'],
      'CX' => ['name' => 'Christmas Island', 'long_name' => 'Christmas Island'],
      'CC' => ['name' => 'Cocos Islands', 'long_name' => 'Cocos Islands'],
      'CO' => ['name' => 'Colombia', 'long_name' => 'Colombia'],
      'KM' => ['name' => 'Comoros', 'long_name' => 'Comoros'],
      'CG' => ['name' => 'Congo', 'long_name' => 'Congo'],
      'CD' => ['name' => 'Zaire (Congo)', 'long_name' => 'Zaire (Congo)'],
      'CK' => ['name' => 'Cook Islands', 'long_name' => 'Cook Islands'],
      'CR' => ['name' => 'Costa Rica', 'long_name' => 'Costa Rica'],
      'CI' => ['name' => "Ivory coast", 'long_name' => "Côte d'Ivoire"],
      'HR' => ['name' => 'Croatia', 'long_name' => 'Croatia'],
      'CU' => ['name' => 'Cuba', 'long_name' => 'Cuba'],
      'CY' => ['name' => 'Cyprus', 'long_name' => 'Cyprus'],
      'CZ' => ['name' => 'Czech Republic', 'long_name' => 'Czech Republic'],
      'DK' => ['name' => 'Denmark', 'long_name' => 'Denmark'],
      'DJ' => ['name' => 'Djibouti', 'long_name' => 'Djibouti'],
      'DM' => ['name' => 'Dominica', 'long_name' => 'Dominica'],
      'DO' => ['name' => 'Dominican Republic', 'long_name' => 'Dominican Republic'],
      'EC' => ['name' => 'Ecuador', 'long_name' => 'Ecuador'],
      'EG' => ['name' => 'Egypt', 'long_name' => 'Egypt'],
      'SV' => ['name' => 'El Salvador', 'long_name' => 'El Salvador'],
      'GQ' => ['name' => 'Equatorial Guinea', 'long_name' => 'Equatorial Guinea'],
      'ER' => ['name' => 'Eritrea', 'long_name' => 'Eritrea'],
      'EE' => ['name' => 'Estonia', 'long_name' => 'Estonia'],
      'ET' => ['name' => 'Ethiopia', 'long_name' => 'Ethiopia'],
      'FK' => ['name' => 'Falkland', 'long_name' => 'Falkland Islands, Malvinas'],
      'FO' => ['name' => 'Faroe Islands', 'long_name' => 'Faroe Islands'],
      'FJ' => ['name' => 'Fiji', 'long_name' => 'Fiji'],
      'FI' => ['name' => 'Finland', 'long_name' => 'Finland'],
      'FR' => ['name' => 'France', 'long_name' => 'France'],
      'GF' => ['name' => 'French Guiana', 'long_name' => 'French Guiana'],
      'PF' => ['name' => 'French Polynesia', 'long_name' => 'French Polynesia'],
      'TF' => ['name' => 'French South Terr.', 'long_name' => 'French Southern Territories'],
      'GA' => ['name' => 'Gabon', 'long_name' => 'Gabon'],
      'GM' => ['name' => 'Gambia', 'long_name' => 'Gambia'],
      'GE' => ['name' => 'Georgia', 'long_name' => 'Georgia'],
      'DE' => ['name' => 'Germany', 'long_name' => 'Germany'],
      'GH' => ['name' => 'Ghana', 'long_name' => 'Ghana'],
      'GI' => ['name' => 'Gibraltar', 'long_name' => 'Gibraltar'],
      'GR' => ['name' => 'Greece', 'long_name' => 'Greece'],
      'GL' => ['name' => 'Greenland', 'long_name' => 'Greenland'],
      'GD' => ['name' => 'Grenada', 'long_name' => 'Grenada'],
      'GP' => ['name' => 'Guadeloupe', 'long_name' => 'Guadeloupe'],
      'GU' => ['name' => 'Guam', 'long_name' => 'Guam'],
      'GT' => ['name' => 'Guatemala', 'long_name' => 'Guatemala'],
      'GG' => ['name' => 'Guernsey', 'long_name' => 'Guernsey'],
      'GN' => ['name' => 'Guinea', 'long_name' => 'Guinea'],
      'GW' => ['name' => 'Guinea-Bissau', 'long_name' => 'Guinea-Bissau'],
      'GY' => ['name' => 'Guyana', 'long_name' => 'Guyana'],
      'HT' => ['name' => 'Haiti', 'long_name' => 'Haiti'],
      'HM' => ['name' => 'Heard & McDonald', 'long_name' => 'Heard Island and McDonald Islands'],
      'VA' => ['name' => 'Vatican City', 'long_name' => 'Vatican City'],
      'HN' => ['name' => 'Honduras', 'long_name' => 'Honduras'],
      'HK' => ['name' => 'Hong Kong', 'long_name' => 'Hong Kong'],
      'HU' => ['name' => 'Hungary', 'long_name' => 'Hungary'],
      'IS' => ['name' => 'Iceland', 'long_name' => 'Iceland'],
      'IN' => ['name' => 'India', 'long_name' => 'India'],
      'ID' => ['name' => 'Indonesia', 'long_name' => 'Indonesia'],
      'IR' => ['name' => 'Iran', 'long_name' => 'Iran'],
      'IQ' => ['name' => 'Iraq', 'long_name' => 'Iraq'],
      'IE' => ['name' => 'Ireland', 'long_name' => 'Ireland'],
      'IM' => ['name' => 'Isle of Man', 'long_name' => 'Isle of Man'],
      'IL' => ['name' => 'Israel', 'long_name' => 'Israel'],
      'IT' => ['name' => 'Italy', 'long_name' => 'Italy'],
      'JM' => ['name' => 'Jamaica', 'long_name' => 'Jamaica'],
      'JP' => ['name' => 'Japan', 'long_name' => 'Japan'],
      'JE' => ['name' => 'Jersey', 'long_name' => 'Jersey'],
      'JO' => ['name' => 'Jordan', 'long_name' => 'Jordan'],
      'KZ' => ['name' => 'Kazakhstan', 'long_name' => 'Kazakhstan'],
      'KE' => ['name' => 'Kenya', 'long_name' => 'Kenya'],
      'KI' => ['name' => 'Kiribati', 'long_name' => 'Kiribati'],
      'KP' => ['name' => 'North Korea', 'long_name' => 'North Korea'],
      'KR' => ['name' => 'Korea, South', 'long_name' => 'Korea, South'],
      'KW' => ['name' => 'Kuwait', 'long_name' => 'Kuwait'],
      'KG' => ['name' => 'Kyrgyzstan', 'long_name' => 'Kyrgyzstan'],
      'LA' => ['name' => 'Laos', 'long_name' => 'Laos'],
      'LV' => ['name' => 'Latvia', 'long_name' => 'Latvia'],
      'LB' => ['name' => 'Lebanon', 'long_name' => 'Lebanon'],
      'LS' => ['name' => 'Lesotho', 'long_name' => 'Lesotho'],
      'LR' => ['name' => 'Liberia', 'long_name' => 'Liberia'],
      'LY' => ['name' => 'Libya', 'long_name' => 'Libya'],
      'LI' => ['name' => 'Liechtenstein', 'long_name' => 'Liechtenstein'],
      'LT' => ['name' => 'Lithuania', 'long_name' => 'Lithuania'],
      'LU' => ['name' => 'Luxembourg', 'long_name' => 'Luxembourg'],
      'MO' => ['name' => 'Macao', 'long_name' => 'Macao'],
      'MK' => ['name' => 'Macedonia', 'long_name' => 'Macedonia'],
      'MG' => ['name' => 'Madagascar', 'long_name' => 'Madagascar'],
      'MW' => ['name' => 'Malawi', 'long_name' => 'Malawi'],
      'MY' => ['name' => 'Malaysia', 'long_name' => 'Malaysia'],
      'MV' => ['name' => 'Maldives', 'long_name' => 'Maldives'],
      'ML' => ['name' => 'Mali', 'long_name' => 'Mali'],
      'MT' => ['name' => 'Malta', 'long_name' => 'Malta'],
      'MH' => ['name' => 'Marshall Islands', 'long_name' => 'Marshall Islands'],
      'MQ' => ['name' => 'Martinique', 'long_name' => 'Martinique'],
      'MR' => ['name' => 'Mauritania', 'long_name' => 'Mauritania'],
      'MU' => ['name' => 'Mauritius', 'long_name' => 'Mauritius'],
      'YT' => ['name' => 'Mayotte', 'long_name' => 'Mayotte'],
      'MX' => ['name' => 'Mexico', 'long_name' => 'Mexico'],
      'FM' => ['name' => 'Micronesia', 'long_name' => 'Micronesia'],
      'MD' => ['name' => 'Moldova', 'long_name' => 'Moldova'],
      'MC' => ['name' => 'Monaco', 'long_name' => 'Monaco'],
      'MN' => ['name' => 'Mongolia', 'long_name' => 'Mongolia'],
      'ME' => ['name' => 'Montenegro', 'long_name' => 'Montenegro'],
      'MS' => ['name' => 'Montserrat', 'long_name' => 'Montserrat'],
      'MA' => ['name' => 'Morocco', 'long_name' => 'Morocco'],
      'MZ' => ['name' => 'Mozambique', 'long_name' => 'Mozambique'],
      'MM' => ['name' => 'Myanmar', 'long_name' => 'Myanmar'],
      'NA' => ['name' => 'Namibia', 'long_name' => 'Namibia'],
      'NR' => ['name' => 'Nauru', 'long_name' => 'Nauru'],
      'NP' => ['name' => 'Nepal', 'long_name' => 'Nepal'],
      'NL' => ['name' => 'Netherlands', 'long_name' => 'Netherlands'],
      'NC' => ['name' => 'New Caledonia', 'long_name' => 'New Caledonia'],
      'NZ' => ['name' => 'New Zealand', 'long_name' => 'New Zealand'],
      'NI' => ['name' => 'Nicaragua', 'long_name' => 'Nicaragua'],
      'NE' => ['name' => 'Niger', 'long_name' => 'Niger'],
      'NG' => ['name' => 'Nigeria', 'long_name' => 'Nigeria'],
      'NU' => ['name' => 'Niue', 'long_name' => 'Niue'],
      'NF' => ['name' => 'Norfolk Island', 'long_name' => 'Norfolk Island'],
      'MP' => ['name' => 'North Mariana Isles', 'long_name' => 'Northern Mariana Islands'],
      'NO' => ['name' => 'Norway', 'long_name' => 'Norway'],
      'OM' => ['name' => 'Oman', 'long_name' => 'Oman'],
      'PK' => ['name' => 'Pakistan', 'long_name' => 'Pakistan'],
      'PW' => ['name' => 'Palau', 'long_name' => 'Palau'],
      'PS' => ['name' => 'Palestine', 'long_name' => 'Palestinian Territory, Occupied'],
      'PA' => ['name' => 'Panama', 'long_name' => 'Panama'],
      'PG' => ['name' => 'Papua New Guinea', 'long_name' => 'Papua New Guinea'],
      'PY' => ['name' => 'Paraguay', 'long_name' => 'Paraguay'],
      'PE' => ['name' => 'Peru', 'long_name' => 'Peru'],
      'PH' => ['name' => 'Philippines', 'long_name' => 'Philippines'],
      'PN' => ['name' => 'Pitcairn', 'long_name' => 'Pitcairn'],
      'PL' => ['name' => 'Poland', 'long_name' => 'Poland'],
      'PT' => ['name' => 'Portugal', 'long_name' => 'Portugal'],
      'PR' => ['name' => 'Puerto Rico', 'long_name' => 'Puerto Rico'],
      'QA' => ['name' => 'Qatar', 'long_name' => 'Qatar'],
      'RE' => ['name' => 'Réunion', 'long_name' => 'Réunion'],
      'RO' => ['name' => 'Romania', 'long_name' => 'Romania'],
      'RU' => ['name' => 'Russia', 'long_name' => 'Russian Federation'],
      'RW' => ['name' => 'Rwanda', 'long_name' => 'Rwanda'],
      'BL' => ['name' => 'St. Barthélemy', 'long_name' => 'Saint Barthélemy'],
      'SH' => ['name' => 'St. Helena', 'long_name' => 'Saint Helena'],
      'KN' => ['name' => 'St. Kitts & Nevis', 'long_name' => 'Saint Kitts and Nevis'],
      'LC' => ['name' => 'St. Lucia', 'long_name' => 'Saint Lucia'],
      'MF' => ['name' => 'St. Martin', 'long_name' => 'Saint Martin (French part)'],
      'PM' => ['name' => 'St. Pierre & Miquelon', 'long_name' => 'Saint Pierre and Miquelon'],
      'VC' => ['name' => 'St. Vincent & Grenadines', 'long_name' => 'Saint Vincent and the Grenadines'],
      'WS' => ['name' => 'Samoa', 'long_name' => 'Samoa'],
      'SM' => ['name' => 'San Marino', 'long_name' => 'San Marino'],
      'ST' => ['name' => 'Sao Tome and Principe', 'long_name' => 'Sao Tome and Principe'],
      'SA' => ['name' => 'Saudi Arabia', 'long_name' => 'Saudi Arabia'],
      'SN' => ['name' => 'Senegal', 'long_name' => 'Senegal'],
      'RS' => ['name' => 'Serbia', 'long_name' => 'Serbia'],
      'SC' => ['name' => 'Seychelles', 'long_name' => 'Seychelles'],
      'SL' => ['name' => 'Sierra Leone', 'long_name' => 'Sierra Leone'],
      'SG' => ['name' => 'Singapore', 'long_name' => 'Singapore'],
      'SX' => ['name' => 'Sint Maarten', 'long_name' => 'Sint Maarten (Dutch part)'],
      'SK' => ['name' => 'Slovakia', 'long_name' => 'Slovakia'],
      'SI' => ['name' => 'Slovenia', 'long_name' => 'Slovenia'],
      'SB' => ['name' => 'Solomon Islands', 'long_name' => 'Solomon Islands'],
      'SO' => ['name' => 'Somalia', 'long_name' => 'Somalia'],
      'ZA' => ['name' => 'South Africa', 'long_name' => 'South Africa'],
      'GS' => ['name' => 'S.Georgia & Sandwich Isles.', 'long_name' => 'South Georgia and the South Sandwich Islands'],
      'SS' => ['name' => 'South Sudan', 'long_name' => 'South Sudan'],
      'ES' => ['name' => 'Spain', 'long_name' => 'Spain'],
      'LK' => ['name' => 'Sri Lanka', 'long_name' => 'Sri Lanka'],
      'SD' => ['name' => 'Sudan', 'long_name' => 'Sudan'],
      'SR' => ['name' => 'Suriname', 'long_name' => 'Suriname'],
      'SJ' => ['name' => 'Svalbard & Jan Mayen', 'long_name' => 'Svalbard and Jan Mayen'],
      'SZ' => ['name' => 'Swaziland', 'long_name' => 'Swaziland'],
      'SE' => ['name' => 'Sweden', 'long_name' => 'Sweden'],
      'CH' => ['name' => 'Switzerland', 'long_name' => 'Switzerland'],
      'SY' => ['name' => 'Syria', 'long_name' => 'Syrian Arab Republic'],
      'TW' => ['name' => 'Taiwan', 'long_name' => 'Taiwan, Province of China'],
      'TJ' => ['name' => 'Tajikistan', 'long_name' => 'Tajikistan'],
      'TZ' => ['name' => 'Tanzania', 'long_name' => 'Tanzania'],
      'TH' => ['name' => 'Thailand', 'long_name' => 'Thailand'],
      'TL' => ['name' => 'Timor-Leste', 'long_name' => 'Timor-Leste'],
      'TG' => ['name' => 'Togo', 'long_name' => 'Togo'],
      'TK' => ['name' => 'Tokelau', 'long_name' => 'Tokelau'],
      'TO' => ['name' => 'Tonga', 'long_name' => 'Tonga'],
      'TT' => ['name' => 'Trinidad & Tobago', 'long_name' => 'Trinidad and Tobago'],
      'TN' => ['name' => 'Tunisia', 'long_name' => 'Tunisia'],
      'TR' => ['name' => 'Turkey', 'long_name' => 'Turkey'],
      'TM' => ['name' => 'Turkmenistan', 'long_name' => 'Turkmenistan'],
      'TC' => ['name' => 'Turks & Caicos Isles', 'long_name' => 'Turks and Caicos Islands'],
      'TV' => ['name' => 'Tuvalu', 'long_name' => 'Tuvalu'],
      'UG' => ['name' => 'Uganda', 'long_name' => 'Uganda'],
      'UA' => ['name' => 'Ukraine', 'long_name' => 'Ukraine'],
      'AE' => ['name' => 'United Arab Emirates', 'long_name' => 'United Arab Emirates'],
      'GB' => ['name' => 'United Kingdom', 'long_name' => 'United Kingdom'],
      'US' => ['name' => 'United States', 'long_name' => 'United States'],
      'UM' => ['name' => 'United States MOI', 'long_name' => 'United States Minor Outlying Islands'],
      'UY' => ['name' => 'Uruguay', 'long_name' => 'Uruguay'],
      'UZ' => ['name' => 'Uzbekistan', 'long_name' => 'Uzbekistan'],
      'VU' => ['name' => 'Vanuatu', 'long_name' => 'Vanuatu'],
      'VE' => ['name' => 'Venezuela', 'long_name' => 'Venezuela'],
      'VN' => ['name' => 'Viet Nam', 'long_name' => 'Viet Nam'],
      'VG' => ['name' => 'Virgin Isles, British', 'long_name' => 'Virgin Islands, British'],
      'VI' => ['name' => 'Virgin Isles, U.S.', 'long_name' => 'Virgin Islands, U.S.'],
      'WF' => ['name' => 'Wallis & Futuna', 'long_name' => 'Wallis and Futuna'],
      'EH' => ['name' => 'Western Sahara', 'long_name' => 'Western Sahara'],
      'YE' => ['name' => 'Yemen', 'long_name' => 'Yemen'],
      'ZM' => ['name' => 'Zambia', 'long_name' => 'Zambia'],
      'ZW' => ['name' => 'Zimbabwe', 'long_name' => 'Zimbabwe']
    ];
  }

  public static function get_user_ip() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
      return $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
      return $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
      return $_SERVER['REMOTE_ADDR'];
    }
  }

  public static function get_visitor_country($ip) {
    // Path to the GeoLite2 Country database
    $dbPath = FV_Country_Blocker_Updater::get_mmdb_path();

    if (!file_exists($dbPath)) {
      return false;
    }

    // Create a Reader object
    $reader = new Reader($dbPath);

    try {
      // Get the country information based on the IP
      $record = $reader->country($ip);

      // Return the country ISO code (e.g., "US", "GB")
      return $record->country->isoCode;
    } catch (Exception $e) {
      // Handle any errors (e.g., IP not found, database issues)
      error_log($e->getMessage() . "\n" . $e->getTraceAsString());
      return false;
    }
  }
}