<?php
if ( ! defined( 'ABSPATH' ) ) exit;

function cod_display_orders_control_center() {
    ?>
    <div class="wrap">
      <h1><?php esc_html_e( 'Orders Control Center', 'custom-order-details' ); ?></h1>
      <button id="cod-export-btn" class="button"><?php esc_html_e( 'Export Addresses', 'custom-order-details' ); ?></button>
      <?php
      echo '<table id="cod_order_table" class="widefat striped"><thead>…</thead><tbody>…</tbody></table>';
      ?>
    </div>
    <?php
}
