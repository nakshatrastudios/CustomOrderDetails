<?php
if ( ! defined( 'ABSPATH' ) ) exit;

// Add shipping phone fields at checkout
add_filter( 'woocommerce_checkout_fields', function( $fields ) {
    $fields['shipping']['shipping_phone'] = [
        'type'     => 'tel',
        'label'    => __( 'Shipping Phone', 'custom-order-details' ),
        'required' => true,
        'class'    => ['form-row-wide'],
        'validate' => ['phone'],
        'priority' => 110,
    ];
    $fields['shipping']['shipping_phone_alternate'] = [
        'type'     => 'tel',
        'label'    => __( 'Alternate Shipping Phone', 'custom-order-details' ),
        'required' => false,
        'class'    => ['form-row-wide'],
        'validate' => ['phone'],
        'priority' => 115,
    ];
    return $fields;
});

// Save shipping phone fields
add_action( 'woocommerce_checkout_update_order_meta', function( $order_id ) {
    if ( ! empty( $_POST['shipping_phone'] ) ) {
        update_post_meta( $order_id, '_shipping_phone', sanitize_text_field( wp_unslash( $_POST['shipping_phone'] ) ) );
    }
    if ( ! empty( $_POST['shipping_phone_alternate'] ) ) {
        update_post_meta( $order_id, '_shipping_phone_alternate', sanitize_text_field( wp_unslash( $_POST['shipping_phone_alternate'] ) ) );
    }
});

// Display alternate phone in admin order
add_action( 'woocommerce_admin_order_data_after_shipping_address', function( $order ) {
    $alt = get_post_meta( $order->get_id(), '_shipping_phone_alternate', true );
    if ( $alt ) {
        echo '<p><strong>' . __( 'Alternate Shipping Phone', 'custom-order-details' ) . ':</strong> ' . esc_html( $alt ) . '</p>';
    }
}, 10 );
