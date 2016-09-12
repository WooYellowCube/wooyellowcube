<?php
global $wpdb, $post;

$yellowcube_order = $wpdb->get_row('SELECT * FROM wooyellowcube_orders WHERE id_order=\''.$post->ID.'\'');
?>

<!-- Order identification -->
<input type="hidden" name="wooyellowcube-order-id" id="wooyellowcube-order-id" value="<?php echo $post->ID?>" />

<?php if($yellowcube_order): ?>
<!-- Current status -->
<div>

  <h3><?php _e('Current status', 'wooyellowcube'); ?></h3>
	<?php
	switch($yellowcube_order->yc_response){
	    case 0: echo '<img src="'.plugin_dir_url('').'wooyellowcube/assets/images/yc-error.png" alt="'.__('Error', 'wooyellowcube').'" />'; break;
	    case 1: echo '<img src="'.plugin_dir_url('').'wooyellowcube/assets/images/yc-pending.png" alt="'.__('Pending', 'wooyellowcube').'" />'; break;
	    case 2: echo '<img src="'.plugin_dir_url('').'wooyellowcube/assets/images/yc-success.png" alt="'.__('Success', 'wooyellowcube').'" />'; break;
	}
	?>
  <p>
    <a href="#" onclick="return false;" id="wooyellowcube-order-refresh" class="button"><i class="fa fa-refresh"></i> <?php _e('Refresh status', 'wooyellowcube'); ?></a>
  </p>

  <p>
    <strong><?php _e('Status', 'wooyellowcube'); ?> : </strong>
	<?php
	switch($yellowcube_order->yc_response){
	    case 0: echo __('Error', 'wooyellowcube'); break;
	    case 1: echo __('Pending', 'wooyellowcube'); break;
	    case 2: echo __('Success', 'wooyellowcube'); break;
	}
	?>


    <br />
    <strong><?php _e('Message', 'wooyellowcube'); ?> :</strong> <em><?php echo $yellowcube_order->yc_status_text; ?></em>
    <br />

	<?php
	$yellowcube_order_lots = $wpdb->get_results('SELECT * FROM wooyellowcube_orders_lots WHERE id_order=\''.$post->ID.'\'');

	if(count($yellowcube_order_lots) > 0){

		echo '<h4>'.__('Lot management for this order', 'wooyellowcube').'</h4>';
		echo '<table class="wp-list-table widefat fixed striped posts">
		<tr>
			<th>'.__('ArticleNo', 'wooyellowcube').'</th>
			<th>'.__('Lot', 'wooyellowcube').'</th>
			<th>'.__('Quantity', 'wooyellowcube').'</th>
		</tr>';

		foreach($yellowcube_order_lots as $lot){
			echo '<tr>
				<td>'.$lot->product_no.'</td>
				<td>'.$lot->product_lot.'</td>
				<td>'.$lot->product_quantity.'</td>
			</tr>';
		}

		echo '</table><br />';
	}
	?>
    <?php if($yellowcube_order->yc_response == 2): ?>

	    <?php $yc_shipping = rtrim($yellowcube_order->yc_shipping); ?>
      <strong><?php _e('Track & trace', 'wooyellowcube'); ?> :</strong>

      <?php if(empty($yc_shipping)){ ?>
        <?php _e('The track & trace is not ready yet, please come back later', 'wooyellowcube'); ?>
      <?php }else{ ?>
        <a href="http://www.post.ch/swisspost-tracking?p_language=en&formattedParcelCodes=<?php echo $yellowcube_order->yc_shipping; ?>" target="_blank"><?php echo $yellowcube_order->yc_shipping; ?></a>
      <?php } ?>

    <?php endif; ?>

</div>

  <?php if($yellowcube_order->yc_response != 2): ?>
  <!-- Order status is not 100 -->
  <div>
    <h3><?php _e('Try again to send this order to YellowCube', 'wooyellowcube'); ?></h3>
    <p><?php _e('Please save your order informations before to send to YellowCube.', 'wooyellowcube'); ?></p>
    <p><a href="#" onclick="return false;" class="button" id="wooyellowcube-order-again"><?php _e('Send order to YellowCube', 'wooyellowcube'); ?></a></p>
  </div>
  <?php endif; ?>
<?php endif; ?>

<?php if(!$yellowcube_order): ?>
<!-- Order has not been sent -->
<div>
  <h3><?php _e('Send this order to Yellowcube', 'wooyellowcube'); ?></h3>
  <p><?php _e('Please save your order informations before to send to YellowCube.');?></p>
  <p><a href="#" onclick="return false;" class="button" id="wooyellowcube-order-send"><?php _e('Send order to YellowCube', 'wooyellowcube');?></a></p>
</div>
<?php endif; ?>
