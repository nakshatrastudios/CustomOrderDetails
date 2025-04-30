<?php
/**
 * Plugin Name: Custom Order Details
 * Description: Adds custom order statuses and a custom checkout field to WooCommerce, with an admin settings page.
 * Version:     1.0.0
 * Author:      Example Author
 * Text Domain: custom-order-details
 * Domain Path: /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Define plugin constants
define( 'CUSTOM_ORDER_DETAILS_VERSION', '1.0.0' );
define( 'CUSTOM_ORDER_DETAILS_DIR',     plugin_dir_path( __FILE__ ) );
define( 'CUSTOM_ORDER_DETAILS_URL',     plugin_dir_url( __FILE__ ) );

// ** Alias the constants your includes expect **
define( 'COD_VERSION',     CUSTOM_ORDER_DETAILS_VERSION );
define( 'COD_PLUGIN_URL',  CUSTOM_ORDER_DETAILS_URL );
define( 'COD_PLUGIN_DIR',  CUSTOM_ORDER_DETAILS_DIR );

// Include plugin files
require_once COD_PLUGIN_DIR . 'includes/order-statuses.php';
require_once COD_PLUGIN_DIR . 'includes/checkout-fields.php';
require_once COD_PLUGIN_DIR . 'includes/ajax-handlers.php';
require_once COD_PLUGIN_DIR . 'includes/orders-page.php';
require_once COD_PLUGIN_DIR . 'includes/admin-page.php';
require_once COD_PLUGIN_DIR . 'includes/admin-assets.php';

// Load text domain for translations
add_action( 'plugins_loaded', function() {
    load_plugin_textdomain(
        'custom-order-details',
        false,
        dirname( plugin_basename( __FILE__ ) ) . '/languages'
    );
} );
