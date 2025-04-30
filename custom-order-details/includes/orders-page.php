<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Render the Orders Control Center table
 */
function cod_display_orders_control_center() {
    ?>
    <div class="wrap">
        <h1><?php esc_html_e( 'Orders Control Center', 'custom-order-details' ); ?></h1>
        <?php
        $orders = wc_get_orders( array(
            'status' => array(
                'wc-pending','wc-processing','wc-on-hold','wc-completed',
                'wc-cancelled','wc-refunded','wc-failed','wc-ready-to-ship','wc-shipped'
            ),
            'limit'  => -1,
            'type'   => 'shop_order',
        ) );
        if ( $orders ) {
            echo '<table class="widefat striped"><thead><tr>';
            $cols = array(
                'Order #','Customer','Billing Phone','Shipping Phone',
                'Billing Addr','Shipping Addr','Items','Total','Status',
                'Consignment','Courier'
            );
            foreach ( $cols as $col ) {
                echo '<th>' . esc_html__( $col, 'custom-order-details' ) . '</th>';
            }
            echo '</tr></thead><tbody>';
            foreach ( $orders as $order ) {
                $id    = $order->get_id();
                $name  = $order->get_billing_first_name() . ' ' . $order->get_billing_last_name();
                $billp = $order->get_billing_phone();
                $shipp = get_post_meta( $id, '_shipping_phone', true );
                $billA = $order->get_billing_address_1() . ', ' . $order->get_billing_city();
                $shipA = $order->get_shipping_address_1() . ', ' . $order->get_shipping_city();
                $total = wc_price( $order->get_total() );

                // status dropdown
                $status_dropdown  = '<select class="cod-order-status" data-order-id="' . $id . '">';
                foreach ( wc_get_order_statuses() as $slug => $label ) {
                    $sel = ( 'wc-' . $order->get_status() === $slug ) ? ' selected' : '';
                    $status_dropdown .= '<option value="' . esc_attr( $slug ) . '"' . $sel . '>' . esc_html( $label ) . '</option>';
                }
                $status_dropdown .= '</select>';

                // consignment edit
                $cons = get_post_meta( $id, '_consignment_number', true );
                $consignment = '<input class="cod-consignment-number" data-order-id="' . $id . '" value="' . esc_attr( $cons ) . '" disabled>'
                            . '<button class="cod-edit-consignment" data-order-id="' . $id . '">' . esc_html__( 'Edit', 'custom-order-details' ) . '</button>';

                // courier dropdown
                $partners = get_option( 'custom_courier_partners', array( 'ST','DTDC','IPS' ) );
                $dropdown = '<select class="cod-courier-partner" data-order-id="' . $id . '"><option value="">' . esc_html__( '--Select--', 'custom-order-details' ) . '</option>';
                foreach ( $partners as $opt ) {
                    $sel = ( $opt === get_post_meta( $id, '_courier_partner', true ) ) ? ' selected' : '';
                    $dropdown .= '<option value="' . esc_attr( $opt ) . '"' . $sel . '>' . esc_html( $opt ) . '</option>';
                }
                $dropdown .= '</select>';

                echo '<tr>';
                echo "<td>{$id}</td><td>" . esc_html( $name ) . "</td><td>" . esc_html( $billp ) . "</td><td>" . esc_html( $shipp ) . "</td>";
                echo "<td>" . esc_html( $billA ) . "</td><td>" . esc_html( $shipA ) . "</td>";
                echo "<td>";
                foreach ( $order->get_items() as $item ) {
                    echo esc_html( $item->get_name() . ' x ' . $item->get_quantity() ) . '<br>';
                }
                echo "</td>";
                echo "<td>{$total}</td>";
                echo "<td>{$status_dropdown}</td>";
                echo "<td>{$consignment}</td>";
                echo "<td>{$dropdown}</td>";
                echo '</tr>';
            }
            echo '</tbody></table>';
        } else {
            echo '<p>' . esc_html__( 'No orders found.', 'custom-order-details' ) . '</p>';
        }
        ?>
    </div>
    <?php
}
