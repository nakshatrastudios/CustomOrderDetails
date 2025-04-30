<?php
/**
 * Plugin Name: Advanced WooCommerce Admin Control Center For Orders and Shipment
 * Description: A custom admin page displaying WooCommerce orders with advanced column toggling, export features, and server-side filtering under each column header.
 * Version: 1.0
 * Author: Infinity Syntax
 * License: GPLv2 or later
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Display the custom order details page in the WP Admin.
 */
// Function to display the order details on the custom admin page
function display_custom_order_details_page() {
    ?>
    <div class="wrap">
        <h1>Advanced WooCommerce Admin Control Center For Orders and Shipment</h1>

        <!-- Column Selector Checkboxes -->
        <div id="column_selector">
            <label><input type="checkbox" class="column-toggle" data-column="1" checked> Order Number</label>
            <label><input type="checkbox" class="column-toggle" data-column="2" checked> Customer</label>
            <label><input type="checkbox" class="column-toggle" data-column="3" checked>Billing Contact</label>
            <label><input type="checkbox" class="column-toggle" data-column="4" checked>Shipping Contact</label>
            <label><input type="checkbox" class="column-toggle" data-column="5" checked> Billing Address</label>
            <label><input type="checkbox" class="column-toggle" data-column="6" checked> Shipping Address</label>
            <label><input type="checkbox" class="column-toggle" data-column="7" checked> Ordered Items</label>
            <label><input type="checkbox" class="column-toggle" data-column="8" checked> Amount</label>
            <label><input type="checkbox" class="column-toggle" data-column="9" checked> Order Status</label>
            <label><input type="checkbox" class="column-toggle" data-column="10" checked> Consignment Number</label>
            <label><input type="checkbox" class="column-toggle" data-column="11" checked> Courier Partner</label>
        </div>

        <!-- Export to Excel Button -->
        <div id="export_buttons">
            <button id="export_table" class="button button-primary">Export to Excel</button>
            <button id="export_addresses" class="button button-primary">Export From and To Addresses</button>
            <!-- Add Courier Partner -->
            <input type="text" id="new_courier_partner" placeholder="Add New Courier Partner">
            <button id="add_courier_partner" class="button">Add</button>
			<select id="remove_courier_partner_dropdown">
				<option value="">--Select Courier Partner to Remove--</option>
				<?php
				$existing_partners = get_option('custom_courier_partners', array('IPS', 'DTDC', 'ST'));
				foreach ($existing_partners as $partner) {
					echo '<option value="' . esc_attr($partner) . '">' . esc_html($partner) . '</option>';
				}
				?>
			</select>
			<button id="remove_courier_partner" class="button">Remove</button>
        </div>

        <style>
            /* Adjust the width of Billing Address and Shipping Address columns */
            table.widefat th:nth-child(5), 
            table.widefat td:nth-child(5) {
                width: 10% !important; /* Set fixed width for Billing Address column */
                word-wrap: break-word;
                white-space: normal;
            }

            table.widefat th:nth-child(6), 
            table.widefat td:nth-child(6) {
                width: 15% !important; /* Set fixed width for Shipping Address column */
                word-wrap: break-word;
                white-space: normal;
            }
            
            table.widefat th:nth-child(7), 
            table.widefat td:nth-child(7) {
                width: 15% !important; /* Set fixed width for Ordered Items column */
                word-wrap: break-word;
                white-space: normal;
            }
            
            /* Adjust the width of Consignment Number column and input */
            table.widefat th:nth-child(10), 
            table.widefat td:nth-child(10) {
                width: 10% !important; /* Adjust the width of the column for Consignment Number */
            }

            /* Reduce width of the Consignment Number text box */
            .consignment-number-input {
                width: 90% !important; /* Set the width for the input field */
                padding: 2px 4px;
                box-sizing: border-box;
            }
            
            /* Adjust the width of the Courier Partner dropdown */
            .courier-partner-dropdown {
                width: 100% !important; /* Set fixed width for the dropdown */
                padding: 2px 4px;
                box-sizing: border-box;
            }
            
            /* Adjust the width of the Order Status and Payment Status dropdowns */
            .order-status-dropdown {
                width: 100% !important; /* Set fixed width for the dropdowns */
                padding: 2px 4px;
                box-sizing: border-box;
            }

            /* Style the filter inputs and dropdowns */
            .filter-input, .filter-dropdown {
                width: 90%; /* Ensure filter width matches the column width */
                box-sizing: border-box;
                padding: 0px 0px;
            }
            
            /* Adjust text alignment to match table header */
            .filter-input, .filter-dropdown {
                text-align: left;
            }
            
            /* Optionally set table layout to fixed for more control over widths */
            table.widefat {
                table-layout: fixed; /* Ensure the table respects set widths */
            }
        </style>

        <script type="text/javascript">
            jQuery(document).ready(function($) {

                // Handle column toggling
                $('.column-toggle').on('change', function() {
                    var column = $(this).data('column');
                    var isChecked = $(this).is(':checked');
                    if (isChecked) {
                        $('td:nth-child(' + column + '), th:nth-child(' + column + ')').show();
                    } else {
                        $('td:nth-child(' + column + '), th:nth-child(' + column + ')').hide();
                    }
                });
				
				// Automatically capitalize text in consignment number input fields
				$(document).on('input', '.consignment-number-input', function() {
					$(this).val($(this).val().toUpperCase());
				});

                // Handle adding new courier partner
                $('#add_courier_partner').on('click', function() {
                    var newCourierPartner = $('#new_courier_partner').val().trim();
                    if (newCourierPartner) {
                        // Save the new courier partner via AJAX
                        $.post(ajaxurl, {
                            action: 'save_new_courier_partner',
                            courier_partner: newCourierPartner
                        }, function(response) {
                            if (response.success) {
                                $('.courier-partner-dropdown').each(function() {
                                    $(this).append('<option value="' + newCourierPartner + '">' + newCourierPartner + '</option>');
                                });
                                displayTemporaryMessage('New courier partner added successfully!', 'success');
                            } else {
                                displayTemporaryMessage('Failed to add courier partner.', 'error');
                            }
                        });
                    } else {
                        displayTemporaryMessage('Invalid courier partner name.', 'error');
                    }
                    $('#new_courier_partner').val('');
                });

                // Filter functionality - adding filter row below header row
                $('#order_table thead tr').after('<tr class="filter-row"></tr>');
                $('#order_table thead th').each(function(index) {
                    if (index === 8) { // Updated index for Order Status Dropdown
                        var select = $('<select class="filter-dropdown"><option value="">All</option><option value="wc-pending">Pending</option><option value="wc-processing">Processing</option><option value="wc-on-hold">On Hold</option><option value="wc-completed">Completed</option><option value="wc-cancelled">Cancelled</option><option value="wc-refunded">Refunded</option><option value="wc-failed">Failed</option><option value="wc-ready-to-ship">Ready to Ship</option><option value="wc-shipped">Shipped</option></select>');
                        $('.filter-row').append($('<th></th>').append(select));
                    } else {
                        var input = $('<input type="text" class="filter-input" />');
                        $('.filter-row').append($('<th></th>').append(input));
                    }
                });

                // Filter table based on input values
                $('.filter-input, .filter-dropdown').on('change keyup', function() {
                    var filters = [];
                    $('.filter-row th').each(function(index) {
                        if (index === 8) { // Order Status dropdown filter
                            filters[index] = $(this).find('select').val();
                        } else {
                            filters[index] = $(this).find('input').val().toLowerCase();
                        }
                    });

                    $('#order_table tbody tr').each(function() {
                        var match = true;
                        $(this).find('td').each(function(index) {
                            if (filters[index]) {
                                if (index === 8) { // Order Status filter check
                                    if ($(this).find('select').val() !== filters[index]) {
                                        match = false;
                                    }
                                } else if (index === 9 || index === 10) { // Consignment Number and Courier Partner filter check
                                    var inputValue = $(this).find('input').val().toLowerCase();
                                    if (inputValue.indexOf(filters[index]) === -1) {
                                        match = false;
                                    }
                                } else {
                                    if ($(this).text().toLowerCase().indexOf(filters[index]) === -1) {
                                        match = false;
                                    }
                                }
                            }
                        });
                        $(this).toggle(match);
                    });
                });

                // Save consignment number via AJAX
                $('.consignment-number-input').on('blur', function() {
                    var orderId = $(this).data('order-id');
                    var consignmentNumber = $(this).val();

                    $.post(ajaxurl, {
                        action: 'save_consignment_number',
                        order_id: orderId,
                        consignment_number: consignmentNumber
                    }, function(response) {
                        if (response.success) {
                            displayTemporaryMessage('Consignment number saved successfully!', 'success');
                        } else {
                            displayTemporaryMessage('Failed to save consignment number.', 'error');
                        }
                    });

                    // Disable the input field after saving
                    $(this).prop('disabled', true);
                });
                
                // Edit consignment number button functionality
                $(document).on('click', '.edit-consignment-btn', function() {
                    var $row = $(this).closest('td');
                    var $input = $row.find('.consignment-number-input');
                    
                    // Enable the input field for editing and focus on it
                    $input.prop('disabled', false).focus();
                });

                // Save courier partner via AJAX
                $('.courier-partner-dropdown').on('change', function() {
                    var orderId = $(this).data('order-id');
                    var courierPartner = $(this).val();

                    $.post(ajaxurl, {
                        action: 'save_courier_partner',
                        order_id: orderId,
                        courier_partner: courierPartner
                    }, function(response) {
                        if (response.success) {
                            displayTemporaryMessage('Courier partner saved successfully!', 'success');
                        } else {
                            displayTemporaryMessage('Failed to save courier partner.', 'error');
                        }
                    });
                });

                // Function to display temporary messages in the center of the screen
                function displayTemporaryMessage(message, type) {
                    var bgColor = type === 'success' ? '#d4edda' : '#f8d7da';
                    var textColor = type === 'success' ? '#155724' : '#721c24';

                    var messageDiv = $('<div class="temporary-message"></div>').text(message)
                        .css({
                            'background-color': bgColor,
                            'color': textColor,
                            'padding': '15px',
                            'position': 'fixed',
                            'top': '50%',
                            'left': '50%',
                            'transform': 'translate(-50%, -50%)',
                            'z-index': '1000',
                            'border-radius': '10px',
                            'font-weight': 'bold',
                            'text-align': 'center',
                            'box-shadow': '0 2px 10px rgba(0, 0, 0, 0.2)'
                        });

                    // Append to the body to ensure it overlays everything
                    $('body').append(messageDiv);

                    // Fade out after 2 seconds
                    setTimeout(function() {
                        messageDiv.fadeOut(500, function() {
                            $(this).remove();
                        });
                    }, 2000);
                }

                // Export Table to Excel (skip hidden columns)
                $('#export_table').on('click', function() {
                    exportTableToExcel('order_table', 'orders_export');
                });

                // Export From and To Addresses
                $('#export_addresses').on('click', function() {
                    exportAddressesToWord();
                });

                // Handle the change event for Order Status dropdown
                $('.order-status-dropdown').on('change', function() {
                    var orderId = $(this).data('order-id');
                    var newStatus = $(this).val();
                    $.post(ajaxurl, {
                        action: 'update_order_status',
                        order_id: orderId,
                        new_status: newStatus
                    }, function(response) {
                        if (response.success) {
                            displayTemporaryMessage('Order status updated successfully!', 'success');
                        } else {
                            displayTemporaryMessage('Failed to update order status.', 'error');
                        }
                    });
                });
				
				// Handle removing a courier partner
				$('#remove_courier_partner').on('click', function() {
					var partnerToRemove = $('#remove_courier_partner_dropdown').val();
					if (partnerToRemove) {
						// Send AJAX request to remove the selected courier partner
						$.post(ajaxurl, {
							action: 'remove_courier_partner',
							courier_partner: partnerToRemove
						}, function(response) {
							if (response.success) {
								// Remove the option from all dropdowns
								$('.courier-partner-dropdown option[value="' + partnerToRemove + '"]').remove();
								$('#remove_courier_partner_dropdown option[value="' + partnerToRemove + '"]').remove();
								displayTemporaryMessage('Courier partner removed successfully!', 'success');
							} else {
								displayTemporaryMessage('Failed to remove courier partner.', 'error');
							}
						});
					} else {
						displayTemporaryMessage('Please select a courier partner to remove.', 'error');
					}
				});

                // Function to export HTML table to Excel, skipping hidden columns
                function exportTableToExcel(tableID, filename = '') {
                    var tableSelect = document.getElementById(tableID);
                    var tableHTML = '<table>';

                    // Create the header row while skipping hidden columns
                    tableHTML += '<thead><tr>';
                    $(tableSelect).find('th').each(function(index) {
                        if ($(this).is(':visible')) {
                            tableHTML += '<th>' + $(this).html() + '</th>';
                        }
                    });
                    tableHTML += '</tr></thead>';

                    // Create the body rows while skipping hidden columns
                    tableHTML += '<tbody>';
                    $(tableSelect).find('tbody tr').each(function() {
                        tableHTML += '<tr>';
                        $(this).find('td').each(function(index) {
                            if ($(this).is(':visible')) {
                                // If the current cell has a dropdown (select element), get the selected value
                                var cellContent = $(this).find('select').length > 0
                                    ? $(this).find('select option:selected').text()  // Get the selected option text
                                    : $(this).html().replace(/<br\s*[\/ ]?>/gi, '\n'); // Replace <br> with new lines for other cells
                                tableHTML += '<td>' + cellContent + '</td>';
                            }
                        });
                        tableHTML += '</tr>';
                    });
                    tableHTML += '</tbody></table>';

                    var BOM = '\uFEFF';
                    var downloadLink = document.createElement("a");
                    document.body.appendChild(downloadLink);

                    if (navigator.msSaveOrOpenBlob) {
                        var blob = new Blob([BOM + tableHTML], { type: 'application/vnd.ms-excel;charset=UTF-8' });
                        navigator.msSaveOrOpenBlob(blob, filename ? filename + '.xls' : 'excel_data.xls');
                    } else {
                        downloadLink.href = 'data:application/vnd.ms-excel;charset=UTF-8,' + encodeURIComponent(BOM + tableHTML);
                        downloadLink.download = filename ? filename + '.xls' : 'excel_data.xls';
                        downloadLink.click();
                    }
                }

                // Function to export From and To Addresses of filtered rows to a Word document
                function exportAddressesToWord() {
                    var rows = [];
                    var fromAddress = "From,\nVibi's Little World,\nNo.8/97 - Sakthi Nagar,\nKavundampalayam,\nCoimbatore - 641030,\nPh: 9656000642"; // Static From address

                    // Fetch each visible row's To address
                    $('#order_table tbody tr:visible').each(function() {
                        var shippingContact = $(this).find('td:nth-child(4)').text().trim();
                        var shippingAddress = $(this).find('td:nth-child(6)').html().replace(/<br\s*[\/ ]?>/gi, '\n').trim();
                        var toAddress = "To,\n" + shippingAddress + "\nPh: " + shippingContact;

                        rows.push([fromAddress, toAddress]);
                    });

                    // Create the HTML structure to export as Word
                    var wordContent = `
                        <html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:w="urn:schemas-microsoft-com:office:word" xmlns="http://www.w3.org/TR/REC-html40">
                        <head>
                            <meta charset="utf-8">
                            <title>Addresses Export</title>
                            <style>
                                body {
                                    margin: 0.2in; /* Narrow margins */
                                }
                                table {
                                    width: 95%;
                                    border-collapse: collapse;
                                }
                                th, td {
                                    width: 50%;
                                    padding: 10px;
                                    vertical-align: top;
                                    border: 1px solid black;
                                    box-sizing: border-box;
                                    white-space: pre-wrap; /* Maintain text wrapping */
                                }
                            </style>
                        </head>
                        <body>
                            <!-- Page setup for narrow margins -->
                            <xml>
                                <w:WordDocument>
                                    <w:View>Print</w:View>
                                    <w:Zoom>100</w:Zoom>
                                    <w:DoNotOptimizeForBrowser/>
                                    <w:DisplayBackgroundShape>false</w:DisplayBackgroundShape>
                                    <w:Compatibility>
                                        <w:SpaceForUL/>
                                        <w:BalanceSingleByteDoubleByteWidth/>
                                        <w:DoNotLeaveBackslashAlone/>
                                        <w:DoNotSuppressParagraphBorders/>
                                        <w:ShapeLayoutLikeWW8/>
                                        <w:AlignTablesRowByRow/>
                                    </w:Compatibility>
                                    <w:MarginTop>720</w:MarginTop> <!-- 0.5in in twips -->
                                    <w:MarginBottom>720</w:MarginBottom> <!-- 0.5in in twips -->
                                    <w:MarginLeft>720</w:MarginLeft> <!-- 0.5in in twips -->
                                    <w:MarginRight>720</w:MarginRight> <!-- 0.5in in twips -->
                                </w:WordDocument>
                            </xml>
                            <table>
                                
                                <tbody>`;

                    rows.forEach(function(row) {
                        wordContent += `
                                    <tr>
                                        <td>${row[0].replace(/\n/g, '<br>')}</td>
                                        <td>${row[1].replace(/\n/g, '<br>')}</td>
                                    </tr>`;
                    });

                    wordContent += `
                                </tbody>
                            </table>
                        </body>
                        </html>`;

                    // Create the Blob object for Word content
                    var blob = new Blob(['\ufeff', wordContent], { type: 'application/msword' });

                    // Create the download link element and trigger download
                    var downloadLink = document.createElement("a");
                    downloadLink.href = URL.createObjectURL(blob);
                    downloadLink.download = 'addresses_export.doc';

                    // Append to the body to ensure it works across browsers
                    document.body.appendChild(downloadLink);

                    // Trigger the download link
                    downloadLink.click();

                    // Clean up the DOM by removing the link after the download
                    document.body.removeChild(downloadLink);
                }
            });
        </script>

        <!-- Display the orders table -->
        <?php
        $args = array('status' => array('wc-pending', 'wc-processing', 'wc-on-hold', 'wc-completed', 'wc-cancelled', 'wc-refunded', 'wc-failed', 'wc-ready-to-ship', 'wc-shipped'), 'limit' => -1, 'type' => 'shop_order');
        $orders = wc_get_orders($args);

        if ($orders) {
            echo '<table id="order_table" class="widefat striped">';
            echo '<thead><tr><th>Order Number</th><th>Customer</th><th>Billing Contact</th><th>Shipping Contact</th><th>Billing Address</th><th>Shipping Address</th><th>Ordered Items</th><th>Amount</th><th>Order Status</th><th>Consignment Number</th><th>Courier Partner</th></tr></thead>';

            echo '<tbody>';
            foreach ($orders as $order) {
                $order_id = $order->get_id();
                $customer_name = $order->get_billing_first_name() . ' ' . $order->get_billing_last_name();
                $customer_billing_phone = $order->get_billing_phone();
                $customer_phone = $order->get_shipping_phone();
                $billing_address = $order->get_billing_address_1() . ', ' . $order->get_billing_city() . ', ' . $order->get_billing_postcode();
                $shipping_address = $order->get_shipping_first_name() . ' ' . $order->get_shipping_last_name() . '<br>' . $order->get_shipping_address_1() . '<br>' . ($order->get_shipping_address_2() ? $order->get_shipping_address_2() . '<br>' : '') . $order->get_shipping_city() . ', ' . $order->get_shipping_postcode();
                $order_total = wc_price($order->get_total());

                $items = $order->get_items();
                $ordered_items = '';
                foreach ($items as $item) {
                    $product_name = $item->get_name();
                    $quantity = $item->get_quantity();
                    $ordered_items .= $product_name . ' x ' . $quantity . '<br>';
                }

                $consignment_number = get_post_meta($order_id, '_consignment_number', true);
                $courier_partner = get_post_meta($order_id, '_courier_partner', true);

                $order_statuses = wc_get_order_statuses();
                $order_status_dropdown = '<select class="order-status-dropdown" data-order-id="' . $order_id . '">';
                foreach ($order_statuses as $status_slug => $status_name) {
                    $selected = ($status_slug === 'wc-' . $order->get_status()) ? 'selected' : '';
                    $order_status_dropdown .= '<option value="' . esc_attr($status_slug) . '" ' . $selected . '>' . esc_html($status_name) . '</option>';
                }
                $order_status_dropdown .= '</select>';

                // Load saved courier partners
				$courier_partner_options = get_option('custom_courier_partners', array('ST', 'DTDC', 'IPS'));

				// Reorder to ensure IPS is first and ST is last
				$courier_partner_options = array_unique($courier_partner_options);
				if (in_array('IPS', $courier_partner_options)) {
					// Remove IPS from the list and add it to the start
					unset($courier_partner_options[array_search('IPS', $courier_partner_options)]);
					array_unshift($courier_partner_options, 'IPS');
				}
				if (in_array('ST', $courier_partner_options)) {
					// Remove ST from the list and add it to the end
					unset($courier_partner_options[array_search('ST', $courier_partner_options)]);
					$courier_partner_options[] = 'ST';
				}

				// Generate the dropdown
				$courier_partner_dropdown = '<select class="courier-partner-dropdown" data-order-id="' . $order_id . '" id="courier_partner_list">';
				$courier_partner_dropdown .= '<option value="">--Select--</option>';
				foreach ($courier_partner_options as $option) {
					$selected = ($option === $courier_partner) ? 'selected' : '';
					$courier_partner_dropdown .= '<option value="' . esc_attr($option) . '" ' . $selected . '>' . esc_html($option) . '</option>';
				}
				$courier_partner_dropdown .= '</select>';



                echo '<tr>';
                echo '<td>' . esc_html($order_id) . '</td>';
                echo '<td>' . esc_html($customer_name) . '</td>';
                echo '<td>' . esc_html($customer_billing_phone) . '</td>';
                echo '<td>' . esc_html($customer_phone) . '</td>';
                echo '<td>' . esc_html($billing_address) . '</td>';
                echo '<td>' . $shipping_address . '</td>';
                echo '<td>' . $ordered_items . '</td>';
                echo '<td>' . $order_total . '</td>';
                echo '<td>' . $order_status_dropdown . '</td>';
                echo '<td>
                        <input type="text" class="consignment-number-input" data-order-id="' . $order_id . '" value="' . esc_attr($consignment_number) . '" disabled />
                        <button class="edit-consignment-btn" data-order-id="' . $order_id . '">Edit</button>
                      </td>';
                echo '<td>' . $courier_partner_dropdown . '</td>';
                echo '</tr>';
            }
            echo '</tbody></table>';
        } else {
            echo '<p>No orders found.</p>';
        }
        ?>
    </div>
    <?php
}

// Handle AJAX request for saving courier partner
add_action('wp_ajax_save_courier_partner', 'save_courier_partner_callback');
function save_courier_partner_callback() {
    if (isset($_POST['order_id']) && isset($_POST['courier_partner'])) {
        $order_id = intval($_POST['order_id']);
        $courier_partner = sanitize_text_field($_POST['courier_partner']);
        update_post_meta($order_id, '_courier_partner', $courier_partner);
        wp_send_json_success('Courier partner saved successfully!');
    } else {
        wp_send_json_error('Invalid request.');
    }
}

// Handle AJAX request for saving new courier partner
add_action('wp_ajax_save_new_courier_partner', 'save_new_courier_partner_callback');
function save_new_courier_partner_callback() {
    if (isset($_POST['courier_partner'])) {
        $new_partner = sanitize_text_field($_POST['courier_partner']);
        
        // Get existing partners
        $existing_partners = get_option('custom_courier_partners', array('IPS', 'DTDC', 'ST'));
        
        // Add new partner if it doesn't already exist
        if (!in_array($new_partner, $existing_partners)) {
            $existing_partners[] = $new_partner;
            update_option('custom_courier_partners', $existing_partners);
            wp_send_json_success('Courier partner saved successfully!');
        } else {
            wp_send_json_error('Courier partner already exists.');
        }
    } else {
        wp_send_json_error('Invalid request.');
    }
}

// Handle AJAX request for removing a courier partner
add_action('wp_ajax_remove_courier_partner', 'remove_courier_partner_callback');
function remove_courier_partner_callback() {
    if (isset($_POST['courier_partner'])) {
        $partner_to_remove = sanitize_text_field($_POST['courier_partner']);
        
        // Get existing partners
        $existing_partners = get_option('custom_courier_partners', array('IPS', 'DTDC', 'ST'));

        // Remove the selected partner if it exists
        if (($key = array_search($partner_to_remove, $existing_partners)) !== false) {
            unset($existing_partners[$key]);
            update_option('custom_courier_partners', array_values($existing_partners)); // Save updated list
            wp_send_json_success('Courier partner removed successfully!');
        } else {
            wp_send_json_error('Courier partner not found.');
        }
    } else {
        wp_send_json_error('Invalid request.');
    }
}

// Handle AJAX request for updating order status
add_action('wp_ajax_update_order_status', 'update_order_status_callback');
function update_order_status_callback() {
    if (isset($_POST['order_id']) && isset($_POST['new_status'])) {
        $order_id = intval($_POST['order_id']);
        $new_status = sanitize_text_field($_POST['new_status']);
        $order = wc_get_order($order_id);
        if ($order) {
            $order->update_status(str_replace('wc-', '', $new_status));
            wp_send_json_success(['message' => 'Order status updated successfully!']);
        } else {
            wp_send_json_error(['message' => 'Order not found.']);
        }
    } else {
        wp_send_json_error(['message' => 'Invalid request.']);
    }
}

// Handle AJAX request for saving consignment number
add_action('wp_ajax_save_consignment_number', 'save_consignment_number_callback');
function save_consignment_number_callback() {
    if (isset($_POST['order_id']) && isset($_POST['consignment_number'])) {
        $order_id = intval($_POST['order_id']);
        $consignment_number = sanitize_text_field($_POST['consignment_number']);
        update_post_meta($order_id, '_consignment_number', $consignment_number);
        wp_send_json_success('Consignment number saved successfully!');
    } else {
        wp_send_json_error('Invalid request.');
    }
}

// Register custom order statuses "Ready to Ship" and "Shipped"
function register_custom_order_statuses() {
    // Register "Ready to Ship" status
    register_post_status('wc-ready-to-ship', array(
        'label'                     => _x('Ready to Ship', 'Order status', 'text_domain'),
        'public'                    => true,
        'exclude_from_search'       => false,
        'show_in_admin_all_list'    => true,
        'show_in_admin_status_list' => true,
        'label_count'               => _n_noop('Ready to Ship <span class="count">(%s)</span>', 'Ready to Ship <span class="count">(%s)</span>'),
    ));

    // Register "Shipped" status
    register_post_status('wc-shipped', array(
        'label'                     => _x('Shipped', 'Order status', 'text_domain'),
        'public'                    => true,
        'exclude_from_search'       => false,
        'show_in_admin_all_list'    => true,
        'show_in_admin_status_list' => true,
        'label_count'               => _n_noop('Shipped <span class="count">(%s)</span>', 'Shipped <span class="count">(%s)</span>'),
    ));
}
add_action('init', 'register_custom_order_statuses');

// Add custom statuses "Ready to Ship" and "Shipped" to WooCommerce order statuses
function add_custom_order_statuses($order_statuses) {
    $new_order_statuses = array();

    // Insert custom statuses after "Processing"
    foreach ($order_statuses as $key => $status) {
        $new_order_statuses[$key] = $status;
        if ('wc-processing' === $key) {
            $new_order_statuses['wc-ready-to-ship'] = _x('Ready to Ship', 'Order status', 'text_domain');
            $new_order_statuses['wc-shipped'] = _x('Shipped', 'Order status', 'text_domain');
        }
    }

    return $new_order_statuses;
}
add_filter('wc_order_statuses', 'add_custom_order_statuses');

function enqueue_sheetjs() {
    // Only load on the custom order details page
    $current_screen = get_current_screen();
    if ($current_screen->id === 'toplevel_page_custom-order-details') {
        wp_enqueue_script('sheetjs', 'https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.16.9/xlsx.full.min.js', array(), null, true);
    }
}
add_action('admin_enqueue_scripts', 'enqueue_sheetjs');



function add_custom_order_details_menu() {
    add_menu_page(
        'Order Details', // Page title
        'Order Details', // Menu title
        'manage_options', // Capability
        'custom-order-details', // Menu slug
        'display_custom_order_details_page', // Callback function
        'dashicons-list-view', // Icon
        6 // Position
    );
}
add_action('admin_menu', 'add_custom_order_details_menu');




add_filter('woocommerce_checkout_fields', 'add_shipping_phone_field');

function add_shipping_phone_field($fields) {
    // Add Shipping Phone field
    $fields['shipping']['shipping_phone'] = array(
        'type'        => 'tel',
        'label'       => __('Shipping Phone', 'woocommerce'),
        'required'    => true,
        'class'       => array('form-row-wide'),
        'validate'    => array('phone'),
        'priority'    => 110,
    );

    return $fields;
}

add_action('woocommerce_checkout_update_order_meta', 'save_shipping_phone_field');

function save_shipping_phone_field($order_id) {
    if (!empty($_POST['shipping_phone'])) {
        update_post_meta($order_id, '_shipping_phone', sanitize_text_field($_POST['shipping_phone']));
    }
}

add_filter('woocommerce_checkout_fields', 'add_alternate_shipping_phone_field');

function add_alternate_shipping_phone_field($fields) {
    // Add Alternate Shipping Phone field
    $fields['shipping']['shipping_phone_alternate'] = array(
        'type'        => 'tel',
        'label'       => __('Alternate Shipping Phone', 'woocommerce'),
        'placeholder' => __('Enter an alternate phone number', 'woocommerce'),
        'required'    => false,
        'class'       => array('form-row-wide'),
        'validate'    => array('phone'),
        'priority'    => 115,
    );

    return $fields;
}

add_action('woocommerce_checkout_update_order_meta', 'save_alternate_shipping_phone_field');

function save_alternate_shipping_phone_field($order_id) {
    if (!empty($_POST['shipping_phone_alternate'])) {
        update_post_meta($order_id, '_shipping_phone_alternate', sanitize_text_field($_POST['shipping_phone_alternate']));
    }
}

add_action('woocommerce_admin_order_data_after_shipping_address', 'display_alternate_shipping_phone_admin_order', 10, 1);

function display_alternate_shipping_phone_admin_order($order) {
    $alternate_phone = get_post_meta($order->get_id(), '_shipping_phone_alternate', true);
    if ($alternate_phone) {
        echo '<p><strong>' . __('Alternate Shipping Phone', 'woocommerce') . ':</strong> ' . esc_html($alternate_phone) . '</p>';
    }
}

// Register a custom admin menu page
function custom_orders_admin_menu_page() {
    add_menu_page(
        'Advanced WooCommerce Orders', // Page title
        'Orders Control Center',       // Menu title
        'manage_woocommerce',          // Capability
        'custom-orders-admin',         // Menu slug
        'display_custom_order_details_page', // Callback function
        'dashicons-list-view',         // Icon (choose any Dashicon)
        56                              // Position (after WooCommerce menu)
    );
}
add_action('admin_menu', 'custom_orders_admin_menu_page');


function allow_shop_manager_access_to_custom_page() {
    $role = get_role('shop_manager');
    if ($role && !$role->has_cap('access_custom_orders_page')) {
        $role->add_cap('access_custom_orders_page');
    }
}
add_action('admin_init', 'allow_shop_manager_access_to_custom_page');
