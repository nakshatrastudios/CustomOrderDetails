jQuery(function($){
    // Export table
    $('#cod-export-btn').on('click', function(){
      var html = '<html xmlns:o="urn:schemas-microsoft-com:office:office" '+
                 'xmlns:w="urn:schemas-microsoft-com:office:word" '+
                 'xmlns="http://www.w3.org/TR/REC-html40">'+
                 '<head><meta charset="utf-8"><title>Export</title></head><body>'+
                 '<table>'+ $('#cod_order_table').html() +'</table></body></html>';
      var blob = new Blob([ '\ufeff', html ], { type: 'application/msword' });
      var url = URL.createObjectURL(blob);
      var a = document.createElement('a');
      a.href = url;
      a.download = 'OrderAddresses.doc';
      document.body.appendChild(a);
      a.click();
      a.remove();
    });
  
    // Update status
    $(document).on('change', '.cod-order-status', function(){
      var $t = $(this), id = $t.data('order-id'), ns = $t.val();
      $.post(cod_orders_data.ajax_url, {
        action: 'update_order_status',
        nonce: cod_orders_data.nonce,
        order_id: id,
        new_status: ns
      });
    });
  
    // Save courier partner
    $(document).on('change', '.cod-courier-partner', function(){
      var $t = $(this), id = $t.data('order-id'), cp = $t.val();
      $.post(cod_orders_data.ajax_url, {
        action: 'save_courier_partner',
        nonce: cod_orders_data.nonce,
        order_id: id,
        courier_partner: cp
      });
    });
  
    // Edit / Save consignment
    $(document).on('click', '.cod-edit-consignment', function(){
      var $btn = $(this), id = $btn.data('order-id'),
          $inp = $('.cod-consignment-number[data-order-id="'+id+'"]'),
          val = $inp.val();
  
      if ( $inp.prop('disabled') ) {
        $inp.prop('disabled', false).focus();
        $btn.text('Save');
      } else {
        $.post(cod_orders_data.ajax_url, {
          action: 'save_consignment_number',
          nonce: cod_orders_data.nonce,
          order_id: id,
          consignment_number: val
        }, function(){
          $inp.prop('disabled', true);
          $btn.text('Edit');
        });
      }
    });
  });
  