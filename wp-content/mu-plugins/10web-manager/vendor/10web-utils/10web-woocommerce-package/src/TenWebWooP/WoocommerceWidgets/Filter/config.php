<?php

if (!defined('ABSPATH')) {
    exit;
}
define('TWW_PRODUCT_FILTER_VERSION', '1.2.2'); // WRCS: DEFINED_VERSION.

define('TWW_PRODUCT_FILTER_FILE', __FILE__);
define('TWW_FIELD_TEMPLATES_DIR', dirname(__DIR__) . '/Filter/Includes/Field/Templates');

define('TWW_PRODUCT_FILTER_URL', plugin_dir_url(TWW_PRODUCT_FILTER_FILE));

define('TWW_FILTER_POST_TYPE', 'tww_filter');

define('TWW_FILTER_ITEM_POS_TYPE', 'tww_filter_item');
