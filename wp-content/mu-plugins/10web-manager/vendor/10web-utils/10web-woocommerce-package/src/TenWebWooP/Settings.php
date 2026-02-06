<?php

namespace TenWebWooP;

class Settings {

    const PREFIX = 'tenweb_woop_';

    public static function get($name) {
        return get_option(self::PREFIX . $name);
    }

    public static function update($name, $value) {
        update_option(self::PREFIX . $name, $value);
    }

    public static function trackIfOptionUpdated($option_name) {
        add_action('update_option_' . $option_name, function ($old_value, $value, $option) {
            $updated_option_name = '';

            if ($option === 'woocommerce_default_country') {
                $updated_option_name = 'updated_default_country';
            }

            if ($updated_option_name && $old_value !== $value) {
                Settings::update($updated_option_name, 1);
            }
        }, 1, 3);
    }
}
