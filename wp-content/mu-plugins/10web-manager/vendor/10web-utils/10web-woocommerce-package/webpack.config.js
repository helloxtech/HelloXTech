const defaultConfig = require("@wordpress/scripts/config/webpack.config");
const path = require('path');

module.exports = {
    ...defaultConfig,
    entry: {
        'block-tenwebwoop-paymentmethodblock': './src/TenWebWooP/PaymentMethods/Stripe/assets/block.js'
    },
    output: {
        path: path.join(__dirname, './src/TenWebWooP/PaymentMethods/Stripe/assets/build/'),
        filename: 'block-compiled.js'
    },
    devtool: 'inline-source-map'
}