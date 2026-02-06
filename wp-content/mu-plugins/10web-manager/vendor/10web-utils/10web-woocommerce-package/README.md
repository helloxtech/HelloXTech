# 10web-woocommerce-package

## Routes

##### /tenweb_woop/v1/reports/customers
To get all the clients we use `reports/customers` route, because WooCommerce rest doesn't give guests


### GET products
WC_REST_Products_Controller


### GET orders
WC_REST_Report_Orders_Totals_Controller



### GET orders/{id}
WC_REST_Orders_Controller


### GET customers
WC_REST_Customers_Controller



### GET customers/{id}
custom data :: get data from **wc_customer_lookup** table

### GET reports/customers
Automattic\WooCommerce\Admin\API\Reports\Customers


### GET jwt_token


### GET shop_info


### GET edit_product


###  POST shop_info

### POST product_with_variations


### POST product_with_variations/{id}


## JWT TOKEN
10WEB auth works only once when there is no jwt token,
then we send the jwt token and the authentication worked with jwt token.

## Code styling
`PHP_CS_FIXER_IGNORE_ENV=1 vendor/bin/php-cs-fixer fix ./src  --config=.php-cs-fixer.php   --show-progress=dots --diff --verbose`

