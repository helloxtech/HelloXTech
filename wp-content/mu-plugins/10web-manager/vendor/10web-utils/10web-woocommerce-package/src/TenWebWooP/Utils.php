<?php

namespace TenWebWooP;

use Automattic\WooCommerce\Utilities\OrderUtil;
use TenWebWooP\PaymentMethods\UUID;
use WC_Countries;

class Utils {

    public static function getAllHeaders() {
        $headers = array();
        $server = array_map('sanitize_text_field', $_SERVER);

        foreach ($server as $name => $value) {
            if (substr($name, 0, 5) === 'HTTP_') {
                $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
            }
        }

        return array_change_key_case($headers, CASE_UPPER);
    }

    /**
     * @return array
     */
    public static function getCountriesStatesList() {
        $country_states = array();
        $countries_obj = new WC_Countries();
        $countries = $countries_obj->__get('countries');

        foreach ($countries as $country_code => $country) {
            $states = $countries_obj->get_states($country_code);

            if (!empty($states)) {
                foreach ($states as $state_code => $state) {
                    $country_states[$country_code . ':' . $state_code] = $country . ' - ' . $state;
                }
            }
            $country_states[$country_code] = $country;
        }

        return $country_states;
    }

    /**
     * @param $country_code
     *
     * @return string|null
     */
    public static function getCurrencyCodeByCountry($country_code) {
        $json_currency_codes = '{"BD": "BDT", "BE": "EUR", "BF": "XOF", "BG": "BGN", "BA": "BAM", "BB": "BBD", "WF": "XPF", "BL": "EUR", "BM": "BMD", "BN": "BND", "BO": "BOB", "BH": "BHD", "BI": "BIF", "BJ": "XOF", "BT": "BTN", "JM": "JMD", "BV": "NOK", "BW": "BWP", "WS": "WST", "BQ": "USD", "BR": "BRL", "BS": "BSD", "JE": "GBP", "BY": "BYR", "BZ": "BZD", "RU": "RUB", "RW": "RWF", "RS": "RSD", "TL": "USD", "RE": "EUR", "TM": "TMT", "TJ": "TJS", "RO": "RON", "TK": "NZD", "GW": "XOF", "GU": "USD", "GT": "GTQ", "GS": "GBP", "GR": "EUR", "GQ": "XAF", "GP": "EUR", "JP": "JPY", "GY": "GYD", "GG": "GBP", "GF": "EUR", "GE": "GEL", "GD": "XCD", "GB": "GBP", "GA": "XAF", "SV": "USD", "GN": "GNF", "GM": "GMD", "GL": "DKK", "GI": "GIP", "GH": "GHS", "OM": "OMR", "TN": "TND", "JO": "JOD", "HR": "HRK", "HT": "HTG", "HU": "HUF", "HK": "HKD", "HN": "HNL", "HM": "AUD", "VE": "VEF", "PR": "USD", "PS": "ILS", "PW": "USD", "PT": "EUR", "SJ": "NOK", "PY": "PYG", "IQ": "IQD", "PA": "PAB", "PF": "XPF", "PG": "PGK", "PE": "PEN", "PK": "PKR", "PH": "PHP", "PN": "NZD", "PL": "PLN", "PM": "EUR", "ZM": "ZMK", "EH": "MAD", "EE": "EUR", "EG": "EGP", "ZA": "ZAR", "EC": "USD", "IT": "EUR", "VN": "VND", "SB": "SBD", "ET": "ETB", "SO": "SOS", "ZW": "ZWL", "SA": "SAR", "ES": "EUR", "ER": "ERN", "ME": "EUR", "MD": "MDL", "MG": "MGA", "MF": "EUR", "MA": "MAD", "MC": "EUR", "UZ": "UZS", "MM": "MMK", "ML": "XOF", "MO": "MOP", "MN": "MNT", "MH": "USD", "MK": "MKD", "MU": "MUR", "MT": "EUR", "MW": "MWK", "MV": "MVR", "MQ": "EUR", "MP": "USD", "MS": "XCD", "MR": "MRO", "IM": "GBP", "UG": "UGX", "TZ": "TZS", "MY": "MYR", "MX": "MXN", "IL": "ILS", "FR": "EUR", "IO": "USD", "SH": "SHP", "FI": "EUR", "FJ": "FJD", "FK": "FKP", "FM": "USD", "FO": "DKK", "NI": "NIO", "NL": "EUR", "NO": "NOK", "NA": "NAD", "VU": "VUV", "NC": "XPF", "NE": "XOF", "NF": "AUD", "NG": "NGN", "NZ": "NZD", "NP": "NPR", "NR": "AUD", "NU": "NZD", "CK": "NZD", "XK": "EUR", "CI": "XOF", "CH": "CHF", "CO": "COP", "CN": "CNY", "CM": "XAF", "CL": "CLP", "CC": "AUD", "CA": "CAD", "CG": "XAF", "CF": "XAF", "CD": "CDF", "CZ": "CZK", "CY": "EUR", "CX": "AUD", "CR": "CRC", "CW": "ANG", "CV": "CVE", "CU": "CUP", "SZ": "SZL", "SY": "SYP", "SX": "ANG", "KG": "KGS", "KE": "KES", "SS": "SSP", "SR": "SRD", "KI": "AUD", "KH": "KHR", "KN": "XCD", "KM": "KMF", "ST": "STD", "SK": "EUR", "KR": "KRW", "SI": "EUR", "KP": "KPW", "KW": "KWD", "SN": "XOF", "SM": "EUR", "SL": "SLL", "SC": "SCR", "KZ": "KZT", "KY": "KYD", "SG": "SGD", "SE": "SEK", "SD": "SDG", "DO": "DOP", "DM": "XCD", "DJ": "DJF", "DK": "DKK", "VG": "USD", "DE": "EUR", "YE": "YER", "DZ": "DZD", "US": "USD", "UY": "UYU", "YT": "EUR", "UM": "USD", "LB": "LBP", "LC": "XCD", "LA": "LAK", "TV": "AUD", "TW": "TWD", "TT": "TTD", "TR": "TRY", "LK": "LKR", "LI": "CHF", "LV": "EUR", "TO": "TOP", "LT": "LTL", "LU": "EUR", "LR": "LRD", "LS": "LSL", "TH": "THB", "TF": "EUR", "TG": "XOF", "TD": "XAF", "TC": "USD", "LY": "LYD", "VA": "EUR", "VC": "XCD", "AE": "AED", "AD": "EUR", "AG": "XCD", "AF": "AFN", "AI": "XCD", "VI": "USD", "IS": "ISK", "IR": "IRR", "AM": "AMD", "AL": "ALL", "AO": "AOA", "AQ": "", "AS": "USD", "AR": "ARS", "AU": "AUD", "AT": "EUR", "AW": "AWG", "IN": "INR", "AX": "EUR", "AZ": "AZN", "IE": "EUR", "ID": "IDR", "UA": "UAH", "QA": "QAR", "MZ": "MZN"}';
        $currency_codes = json_decode($json_currency_codes, true);

        if (isset($currency_codes[$country_code])) {
            return $currency_codes[$country_code];
        }

        return null;
    }

    public static function updateTenwebPayTestMode($mode, $method = 'payengine') {
        if ($mode === 'no' || $mode === 'yes') {
            if ('payengine' === $method) {
                PaymentMethods\Payengine\TenWebPaymentsPayengine::get_instance()->update_option('test_mode', $mode);
            }

            if ('stripe' === $method) {
                PaymentMethods\Stripe\TenWebPaymentsStripe::get_instance()->update_option('test_mode', $mode);
            }
        }
    }

    public static function isHposActive() {
        if (! class_exists('Automattic\WooCommerce\Utilities\OrderUtil')) {
            return false;
        }

        if (OrderUtil::custom_orders_table_usage_is_enabled()) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Retrieve the test or live mode status for the TenWeb Stripe payment gateway.
     *
     * If called from REST API, it checks if the 'tenweb_payments_stripe' gateway is enabled.
     * If the gateway is not present or is disabled, it returns null.
     * Otherwise, it retrieves the 'test_mode' option from the TenWeb Stripe payment gateway instance
     * and returns 'live' or 'test' based on the value of the option.
     *
     * @param bool $from_rest Determines if the call is coming from REST API. Default is false.
     *
     * @return string|null returns 'test' or 'live' based on the 'test_mode' setting, or null if the gateway is not enabled
     */
    public static function getTestMode($from_rest = false) {
        if ($from_rest) {
            if (! \TenWebWooP\Config::have_any_stripe_account()) {
                return null;
            }
        }

        // Retrieve the 'test_mode' option from the Stripe payment method instance
        $stripe_instance = PaymentMethods\Stripe\TenWebPaymentsStripe::get_instance();
        $test_mode = $stripe_instance->get_option('test_mode');

        // Return 'live' if test mode is 'no', otherwise return 'test'
        return $test_mode === 'no' ? 'live' : 'test';
    }

    public static function getIdempotencyKey($order_id) {
        $meta_key = 'twwp_idempotency_key';
        $key = get_post_meta($order_id, $meta_key, true);

        if (!$key) {
            $key = UUID::v4();
            update_post_meta($order_id, $meta_key, $key);
        }

        return $key;
    }

    /**
     * Recursively converts all objects in a given array or object to arrays.
     *
     * This function will traverse the input data, converting any stdClass objects
     * it encounters into associative arrays. It leaves any existing arrays untouched.
     *
     * @param mixed $data the input data, which can be an array or an object
     *
     * @return array the converted array with all objects transformed into arrays
     */
    public static function convert_objects_to_arrays($data) {
        if (is_object($data)) {
            // Convert object to array
            $data = (array) $data;
        }

        if (is_array($data)) {
            // Recursively check elements and convert objects to arrays
            foreach ($data as $key => $value) {
                $data[$key] = self::convert_objects_to_arrays($value);
            }
        }

        return $data;
    }
}
