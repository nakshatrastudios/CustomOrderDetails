<?php
if ( ! defined( 'ABSPATH' ) ) exit;

// … your cod_handle_save_settings() here …

// Save courier partner
add_action( 'wp_ajax_save_courier_partner', function() {
    check_ajax_referer( 'cod_nonce', 'nonce' );
    if ( ! current_user_can( 'manage_woocommerce' ) ) wp_send_json_error();
    $o = intval( $_POST['order_id'] );
    $p = sanitize_text_field( wp_unslash( $_POST['courier_partner'] ) );
    update_post_meta( $o, '_courier_partner', $p );
    wp_send_json_success();
});

// Save new courier partner
add_action( 'wp_ajax_save_new_courier_partner', function() {
    check_ajax_referer( 'cod_nonce', 'nonce' );
    if ( ! current_user_can( 'manage_woocommerce' ) ) wp_send_json_error();
    $new = sanitize_text_field( wp_unslash( $_POST['courier_partner'] ) );
    $opts = get_option( 'custom_courier_partners', ['ST','DTDC','IPS'] );
    if ( ! in_array( $new, $opts, true ) ) {
        $opts[] = $new; update_option( 'custom_courier_partners', $opts );
        wp_send_json_success();
    }
    wp_send_json_error( 'exists' );
});

// Remove courier partner
add_action( 'wp_ajax_remove_courier_partner', function() {
    check_ajax_referer( 'cod_nonce', 'nonce' );
    if ( ! current_user_can( 'manage_woocommerce' ) ) wp_send_json_error();
    $rem = sanitize_text_field( wp_unslash( $_POST['courier_partner'] ) );
    $opts = get_option( 'custom_courier_partners', ['ST','DTDC','IPS'] );
    if ( ( $k = array_search( $rem, $opts, true ) ) !== false ) {
        unset( $opts[ $k ] );
        update_option( 'custom_courier_partners', array_values( $opts ) );
        wp_send_json_success();
    }
    wp_send_json_error();
});

// Update order status
add_action( 'wp_ajax_update_order_status', function() {
    check_ajax_referer( 'cod_nonce', 'nonce' );
    if ( ! current_user_can( 'manage_woocommerce' ) ) wp_send_json_error();
    $o = intval( $_POST['order_id'] );
    $s = sanitize_text_field( wp_unslash( $_POST['new_status'] ) );
    $order = wc_get_order( $o );
    if ( $order ) {
        $order->update_status( str_replace('wc-','',$s) );
        wp_send_json_success();
    }
    wp_send_json_error();
});

// Save consignment number
add_action( 'wp_ajax_save_consignment_number', function() {
    check_ajax_referer( 'cod_nonce', 'nonce' );
    if ( ! current_user_can( 'manage_woocommerce' ) ) wp_send_json_error();
    $o = intval( $_POST['order_id'] );
    $c = sanitize_text_field( wp_unslash( $_POST['consignment_number'] ) );
    update_post_meta( $o, '_consignment_number', $c );
    wp_send_json_success();
});
