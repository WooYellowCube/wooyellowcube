jQuery(document).ready(function($){

  /**
  * Setting disable authentification file on condition
  */
  $('.wooyellowcube_authentification').on('change', function(){

    if($(this).val() == 1){
      $('#authentificationFile').prop('disabled', false);
    }else{
      $('#authentificationFile').prop('disabled', true);
    }

  });

  /**
  * Metabox - Product - Send to YellowCube
  */
  $('#wooyellowcube-product-send').on('click', function(){

    // Ajax call
    $.ajax({
			url: wooyellowcube_ajax,
			type: 'POST',
			data: {
				action: 'product_send',
				post_id: jQuery('#wooyellowcube-product-id').val(),
				lotmanagement: jQuery('#lotmanagement').val()
			},
			success:function(data) {
				console.log(data);

        location.reload();
			},
			error: function(errorThrown){
				console.log(errorThrown);
			}
		});

  });

  /**
  * Metabox - Product - Update to YellowCube
  */
  $('#wooyellowcube-product-update').on('click', function(){

    // Ajax call
    $.ajax({
			url: wooyellowcube_ajax,
			type: 'POST',
			data: {
				action: 'product_update',
				post_id: jQuery('#wooyellowcube-product-id').val(),
				lotmanagement: jQuery('#lotmanagement').val()
			},
			success:function(data) {
				console.log(data);

        location.reload();
			},
			error: function(errorThrown){
				console.log(errorThrown);
			}
		});

  });

  /**
  * Metabox - Product - Remove to YellowCube
  */
  $('#wooyellowcube-product-remove').on('click', function(){

    // Ajax call
    $.ajax({
      url: wooyellowcube_ajax,
      type: 'POST',
      data: {
        action: 'product_remove',
        post_id: jQuery('#wooyellowcube-product-id').val()
      },
      success:function(data) {
        console.log(data);

        location.reload();
      },
      error: function(errorThrown){
        console.log(errorThrown);
      }
    });

  });

  /**
  * Metabox - Product - Refresh
  */
  $('#wooyellowcube-product-refresh').on('click', function(){

    // Refresh the page
    location.reload();

  });

  /**
  * Metabox - Order - Send order to YellowCube
  */
  $('#wooyellowcube-order-send').on('click', function(){

    // Ajax call
    $.ajax({
      url: wooyellowcube_ajax,
      type: 'POST',
      data: {
        action: 'order_send',
        post_id: jQuery('#wooyellowcube-order-id').val()
      },
      success:function(data) {
        console.log(data);

        location.reload();
      },
      error: function(errorThrown){
        console.log(errorThrown);
      }
    });

  });

  /**
  * Metabox - Order - Try again to send to YellowCube
  */
  $('#wooyellowcube-order-again').on('click', function(){

    // Ajax call
    $.ajax({
      url: wooyellowcube_ajax,
      type: 'POST',
      data: {
        action: 'order_send',
        post_id: jQuery('#wooyellowcube-order-id').val()
      },
      success:function(data) {
        console.log(data);

        location.reload();
      },
      error: function(errorThrown){
        console.log(errorThrown);
      }
    });

  });

  /**
  * Metabox - Order - Refresh
  */
  $('#wooyellowcube-order-refresh').on('click', function(){

    // Refresh the page
    location.reload();

  });

  $('.wooyellowcube-product-variation-send').on('click', function(){



    // Ajax call
    $.ajax({
			url: wooyellowcube_ajax,
			type: 'POST',
			data: {
				action: 'product_send',
				post_id: $(this).next('.wooyellowcube-product-variation-id').val(),
				lotmanagement: jQuery('#lotmanagement').val()
			},
			success:function(data) {
        location.reload();
			},
			error: function(errorThrown){
				console.log(errorThrown);
			}
		});
  });

  /**
  * Metabox - Product - Update to YellowCube
  */
  $('.wooyellowcube-product-variation-update').on('click', function(){

    // Ajax call
    $.ajax({
			url: wooyellowcube_ajax,
			type: 'POST',
			data: {
				action: 'product_update',
				post_id: jQuery(this).next('.wooyellowcube-product-variation-id').val(),
				lotmanagement: jQuery('#lotmanagement').val()
			},
			success:function(data) {
        location.reload();
			},
			error: function(errorThrown){
				console.log(errorThrown);
			}
		});

  });

  /**
  * Metabox - Product - Remove to YellowCube
  */
  $('.wooyellowcube-product-variation-desactivate').on('click', function(){

    // Ajax call
    $.ajax({
      url: wooyellowcube_ajax,
      type: 'POST',
      data: {
        action: 'product_remove',
        post_id: $(this).next('.wooyellowcube-product-variation-id').val()
      },
      success:function(data) {
        location.reload();
      },
      error: function(errorThrown){
        console.log(errorThrown);
      }
    });

  });

});
