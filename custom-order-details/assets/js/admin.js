jQuery(document).ready(function($) {
    $('#cod-settings-form').on('submit', function(e) {
        e.preventDefault();
        // Get checkbox value
        var requireField = $('#cod-settings-form input[name="cod_require_field"]').is(':checked') ? 1 : 0;
        // Send AJAX request
        $.post(cod_data.ajax_url, {
            action: 'cod_save_settings',
            nonce: cod_data.nonce,
            require_field: requireField
        }, function(response) {
            // Show success or error message
            if (response.success) {
                $('#cod-message').removeClass('error').addClass('updated').text(response.data.message).show();
            } else {
                var errMsg = response.data && response.data.message ? response.data.message : cod_data.error_msg;
                $('#cod-message').removeClass('updated').addClass('error').text(errMsg).show();
            }
        });
    });
});
