<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Register custom order statuses and integrate with WooCommerce
function cod_register_order_statuses() {
    // Register "Shipped" status
    register_post_status( 'wc-custom-shipped', array(
        'label'                     => __( 'Shipped', 'custom-order-details' ),
        'public'                    => true,
        'exclude_from_search'       => false,
        'show_in_admin_all_list'    => true,
        'show_in_admin_status_list' => true,
        'label_count'               => _n_noop( 'Shipped <span class="count">(%s)</span>', 'Shipped <span class="count">(%s)</span>', 'custom-order-details' )
    ) );

    // Register "Delivered" status
    register_post_status( 'wc-custom-delivered', array(
        'label'                     => __( 'Delivered', 'custom-order-details' ),
        'public'                    => true,
        'exclude_from_search'       => false,
        'show_in_admin_all_list'    => true,
        'show_in_admin_status_list' => true,
        'label_count'               => _n_noop( 'Delivered <span class="count">(%s)</span>', 'Delivered <span class="count">(%s)</span>', 'custom-order-details' )
    ) );
}
add_action( 'init', 'cod_register_order_statuses' );

// Add custom statuses to WooCommerce status list
function cod_add_order_statuses( $order_statuses ) {
    $order_statuses['wc-custom-shipped']   = __( 'Shipped', 'custom-order-details' );
    $order_statuses['wc-custom-delivered'] = __( 'Delivered', 'custom-order-details' );
    return $order_statuses;
}
add_filter( 'wc_order_statuses', 'cod_add_order_statuses' );
