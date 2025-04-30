<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Register the admin menu page for plugin settings
function cod_register_admin_page() {
    global $cod_admin_page_hook;
    $cod_admin_page_hook = add_menu_page(
        __( 'Custom Order Details', 'custom-order-details' ),  // Page title
        __( 'Custom Order Details', 'custom-order-details' ),  // Menu title
        'manage_woocommerce',                                  // Capability
        'custom-order-details',                                // Menu slug
        'cod_render_admin_page',                               // Callback function
        'dashicons-list-view',                                 // Icon (optional)
        56                                                     // Position (optional)
    );
}
add_action( 'admin_menu', 'cod_register_admin_page' );

// Render the content of the admin page
function cod_render_admin_page() {
    ?>
    <div class="wrap">
        <h1><?php echo esc_html__( 'Custom Order Details Settings', 'custom-order-details' ); ?></h1>
        <form id="cod-settings-form" method="post" action="javascript:void(0);">
            <p>
                <label>
                    <input type="checkbox" name="cod_require_field" value="1" <?php checked( 1, get_option( 'cod_require_field', 1 ) ); ?> />
                    <?php esc_html_e( 'Require custom checkout field', 'custom-order-details' ); ?>
                </label>
            </p>
            <p>
                <button type="submit" class="button button-primary"><?php esc_html_e( 'Save Changes', 'custom-order-details' ); ?></button>
            </p>
        </form>
        <div id="cod-message" style="display:none;"></div>
    </div>
    <?php
}
