<?php

namespace TenWebWooP;

class SetUp {

    /**
     * @var int
     */
    private $is_country_edited = 0;

    /**
     * @var int
     */
    private $is_product_added = 0;

    /**
     * @var int
     */
    private $is_payment_added = 0;

    /**
     * @var int
     */
    private $is_website_edited = 0;

    /**
     * @return void
     */
    public function __construct() {
        $this->isCountryEdited();
        $this->isProductAdded();
        $this->isPaymentAdded();
        $this->isWebsiteEdited();
    }

    /**
     * @return int
     */
    public function getIsCountryEdited() {
        return $this->is_country_edited;
    }

    /**
     * @return int
     */
    public function getIsProductAdded() {
        return $this->is_product_added;
    }

    /**
     * @return int
     */
    public function getIsPaymentAdded() {
        return $this->is_payment_added;
    }

    /**
     * @return int
     */
    public function getIsWebsiteEdited() {
        return $this->is_website_edited;
    }

    /**
     * @return void
     */
    private function isCountryEdited() {
        $this->is_country_edited = (int) Settings::get('updated_default_country');
    }

    /**
     * @return void
     */
    private function isProductAdded() {
        $imported_product_ids = get_option('twbb_imported_product_ids', array());
        $products = wc_get_products(array());

        if ($products) {
            $product_ids = array_map(function ($value) {
                return $value->id;
            }, $products);

            if (!empty(array_diff($product_ids, $imported_product_ids))) {
                $this->is_product_added = 1;
            }
        }
    }

    /**
     * @return void
     */
    private function isPaymentAdded() {
        $payment_gateways = WC()->payment_gateways()->get_available_payment_gateways();

        if (!empty($payment_gateways)) {
            $this->is_payment_added = 1;
        }
    }

    /**
     * @return void
     */
    private function isWebsiteEdited() {
        $this->is_website_edited = (int) get_site_option('twbb_track_publish_button', '');
    }

    /**
     * @param $default_country
     * @param $update_currency
     *
     * @return void
     */
    public static function updateDefaultCountry($default_country, $update_currency = true) {
        if ($default_country) {
            update_option('woocommerce_default_country', $default_country);
            Settings::update('updated_default_country', 1);

            if ($update_currency) {
                $country_code = explode(':', $default_country);
                $currency = Utils::getCurrencyCodeByCountry($country_code[0]);

                if ($currency) {
                    update_option('woocommerce_currency', $currency);
                }
            }
        }
    }
}
