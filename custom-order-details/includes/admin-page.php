<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function cod_register_admin_pages() {
    // 1) Top‐level: Orders Control Center
    $hook1 = add_menu_page(
        __( 'Orders Control Center', 'custom-order-details' ),
        __( 'Orders Control Center', 'custom-order-details' ),
        'manage_woocommerce',
        'cod-orders-control',
        'cod_display_orders_control_center',
        'dashicons-list-view',
        56
    );

    // 2) Submenu: Settings under the same top‐level
    add_submenu_page(
        'cod-orders-control',
        __( 'Settings', 'custom-order-details' ),
        __( 'Settings', 'custom-order-details' ),
        'manage_woocommerce',
        'custom-order-details',
        'cod_render_admin_page'
    );
}
add_action( 'admin_menu', 'cod_register_admin_pages' );
