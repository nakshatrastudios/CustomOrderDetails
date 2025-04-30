<?php
if ( ! defined( 'ABSPATH' ) ) exit;

function cod_enqueue_admin_assets( $hook ) {
    // Orders page
    if ( 'toplevel_page_cod-orders-control' === $hook ) {
        // SheetJS for export (optional)
        wp_enqueue_script(
            'sheetjs',
            'https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.16.9/xlsx.full.min.js',
            [], '0.16.9', true
        );
        // Orders‐table actions
        wp_enqueue_script(
            'cod-admin-orders',
            COD_PLUGIN_URL . 'assets/js/admin-orders.js',
            ['jquery'], COD_VERSION, true
        );
        wp_localize_script( 'cod-admin-orders', 'cod_orders_data', [
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce'    => wp_create_nonce( 'cod_nonce' ),
        ] );
    }

    // Settings page
    if ( 'cod-orders-control_page_custom-order-details' === $hook ) {
        wp_enqueue_script(
            'cod-admin-settings',
            COD_PLUGIN_URL . 'assets/js/admin.js',
            ['jquery'], COD_VERSION, true
        );
        wp_localize_script( 'cod-admin-settings', 'cod_data', [
            'ajax_url'  => admin_url( 'admin-ajax.php' ),
            'nonce'     => wp_create_nonce( 'cod_nonce' ),
            'error_msg' => __( 'An error occurred.', 'custom-order-details' ),
        ] );
    }
}
add_action( 'admin_enqueue_scripts', 'cod_enqueue_admin_assets' );
