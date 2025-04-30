<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Add custom checkout field to WooCommerce checkout page
function cod_add_checkout_field( $checkout ) {
    echo '<div id="cod_custom_field"><h3>' . __( 'Additional Information', 'custom-order-details' ) . '</h3>';
    woocommerce_form_field( 'cod_order_detail', array(
        'type'        => 'text',
        'required'    => ( get_option( 'cod_require_field', 1 ) ? true : false ),
        'label'       => __( 'Custom Order Detail', 'custom-order-details' ),
        'placeholder' => __( 'Enter detail here', 'custom-order-details' ),
    ), $checkout->get_value( 'cod_order_detail' ) );
    echo '</div>';
}
add_action( 'woocommerce_after_order_notes', 'cod_add_checkout_field' );

// Validate the custom checkout field input
function cod_validate_checkout_field() {
    // If field is required and not filled, display an error notice
    if ( get_option( 'cod_require_field', 1 ) && empty( $_POST['cod_order_detail'] ) ) {
        wc_add_notice( __( 'Please enter a value for the custom order detail.', 'custom-order-details' ), 'error' );
    }
}
add_action( 'woocommerce_checkout_process', 'cod_validate_checkout_field' );

// Save the custom checkout field value to order meta
function cod_save_checkout_field( $order_id ) {
    if ( ! empty( $_POST['cod_order_detail'] ) ) {
        update_post_meta( $order_id, '_cod_order_detail', sanitize_text_field( $_POST['cod_order_detail'] ) );
    }
}
add_action( 'woocommerce_checkout_update_order_meta', 'cod_save_checkout_field' );

// Display the custom field value in the admin order edit page
function cod_display_order_detail_admin( $order ) {
    $detail = get_post_meta( $order->get_id(), '_cod_order_detail', true );
    if ( $detail ) {
        echo '<p><strong>' . __( 'Custom Order Detail', 'custom-order-details' ) . ':</strong> ' . esc_html( $detail ) . '</p>';
    }
}
add_action( 'woocommerce_admin_order_data_after_billing_address', 'cod_display_order_detail_admin' );
