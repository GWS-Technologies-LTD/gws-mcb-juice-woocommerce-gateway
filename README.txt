=== GWS Juice by MCB WooCommerce Gateway ===
Contributors: davidcommarmond
Author URI: https://www.linkedin.com/in/jcommarmond/
Plugin URI: https://www.gws-technologies.com/
Tags: gws-mcb-huice
Requires at least: 6.0.0
Tested up to: 6.4.1
Requires PHP: 8.0
Stable tag: 1.1
License: GPLv2

# Intro
Add MCB Juice Payment Gateway to your WooCommerce Checkout

# Requirements
- You will 1st need to install WooCommerce for this to work

# Setup
- Once you've done the above requirements, activate the plugin
- Go to WooCommerce > Settings > Payments to activate the Gateway
- On the Gateway page, fill in the required informations

# Usage
- Your store customers can now checkout with Juice by MCB
- You will need to manually mark payments received in WooCommerce > Orders ( Just like for Bank Transfer ) once you've confirmed you received the payment

#Compatibility
The plugin is compatible with the “Checkout shortcode” and not the new “Checkout gutenberg block”.
If you see an error saying no payment method is available after enabling the gatewaly, you can follow the following steps to fix this in the latest versions of woocommerce:

- Go to Appearance > Editor > Templates > Checkout
- Remove the Checkout block
- Insert the Shortcode block and write inside it [woocommerce_checkout]
- Save the template
- The gateway should now appear on the checkout page


# Contribution
- You can contribute by going to https://github.com/GWS-Technologies-LTD/mcb-juice-woocommerce-gateway

# Feature Request
- You may send your feature requests by e-mail on support@gws-technologies.com

# Custom Development
If you need plugin customization, ERP integration, or custom WooCommerce or WordPress Development, get in touch with us at https://www.gws-technologies.com/