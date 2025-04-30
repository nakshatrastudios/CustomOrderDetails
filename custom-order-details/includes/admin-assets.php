<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Enqueue admin scripts and styles for the plugin page
function cod_enqueue_admin_assets( $hook ) {
    global $cod_admin_page_hook;
    if ( empty( $cod_admin_page_hook ) || $hook !== $cod_admin_page_hook ) {
        return;
    }
    // Enqueue JavaScript for the admin page
    wp_enqueue_script(
        'cod-admin-script',
        CUSTOM_ORDER_DETAILS_URL . 'assets/js/admin.js',
        array( 'jquery' ),
        CUSTOM_ORDER_DETAILS_VERSION,
        true
    );
    // Localize script with data for AJAX
    wp_localize_script(
        'cod-admin-script',
        'cod_data',
        array(
            'ajax_url'  => admin_url( 'admin-ajax.php' ),
            'nonce'     => wp_create_nonce( 'cod_nonce' ),
            'error_msg' => __( 'An error occurred.', 'custom-order-details' )
        )
    );
    // Enqueue CSS for admin page (if needed)
    // wp_enqueue_style( 'cod-admin-style', CUSTOM_ORDER_DETAILS_URL . 'assets/css/admin.css', array(), CUSTOM_ORDER_DETAILS_VERSION );
}
add_action( 'admin_enqueue_scripts', 'cod_enqueue_admin_assets' );
