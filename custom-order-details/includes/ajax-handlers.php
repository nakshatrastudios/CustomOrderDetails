<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// AJAX handler for saving settings from admin page
function cod_handle_save_settings() {
    // Verify nonce and user capabilities
    if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'cod_nonce' ) ) {
        wp_send_json_error( array( 'message' => __( 'Security check failed.', 'custom-order-details' ) ) );
    }
    if ( ! current_user_can( 'manage_woocommerce' ) ) {
        wp_send_json_error( array( 'message' => __( 'You are not allowed to do this.', 'custom-order-details' ) ) );
    }
    // Get and save the setting (require field checkbox)
    $require_field = isset( $_POST['require_field'] ) ? intval( $_POST['require_field'] ) : 0;
    update_option( 'cod_require_field', $require_field );
    // Respond with success
    wp_send_json_success( array( 'message' => __( 'Settings saved successfully.', 'custom-order-details' ) ) );
}
add_action( 'wp_ajax_cod_save_settings', 'cod_handle_save_settings' );
