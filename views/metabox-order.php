<?php
global $wpdb, $post;

$yellowcube_order = $wpdb->get_row('SELECT * FROM wooyellowcube_orders WHERE id_order=\''.$post->ID.'\'');
?>
<div class="wooyellowcube-overflow">
    <div class="wooyellowcube-middle-left">

    <!-- Order identification -->
    <input type="hidden" name="wooyellowcube-order-id" id="wooyellowcube-order-id" value="<?php echo $post->ID?>" />

    <?php if($yellowcube_order): ?>
    <!-- Current status -->
    <div>
        <?php
        switch($yellowcube_order->yc_response){
            case 0: echo '<div class="yellowcube-error"><u>Error</u>'.$yellowcube_order->yc_status_text.'</div>'; break;
            case 1: echo '<div class="yellowcube-pending"><u>Pending</u>'.$yellowcube_order->yc_status_text.'</div>'; break;
            case 2: echo '<div class="yellowcube-success"><u>Success</u>'.$yellowcube_order->yc_status_text.'</div>'; break;
        }
        ?>

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
            <div>
                <p>
    	    <?php $yc_shipping = rtrim($yellowcube_order->yc_shipping); ?>
          <strong><?php _e('Track & trace', 'wooyellowcube'); ?> :</strong>

          <?php if(empty($yc_shipping)){ ?>
            <?php _e('The track & trace is not ready yet, please come back later', 'wooyellowcube'); ?>
          <?php }else{ ?>
            <a href="http://www.post.ch/swisspost-tracking?p_language=en&formattedParcelCodes=<?php echo $yellowcube_order->yc_shipping; ?>" target="_blank"><?php echo $yellowcube_order->yc_shipping; ?></a>
          <?php } ?>
        </p>
        </div>
        <?php endif; ?>

    </div>

      <?php if($yellowcube_order->yc_response != 2): ?>
      <!-- Order status is not 100 -->
      <div>
        <p><small><strong><u>Important:</u></strong> <?php _e('All order informations has to be saved before sending it to YellowCube');?></p>
        <p><small>There is an error sent from YellowCube. Refer to the error message above or contact YellowCube to get help.</small></p>
        <p><a href="#" onclick="return false;" class="button-primary" id="wooyellowcube-order-again"><?php _e('Send again this order to YellowCube', 'wooyellowcube'); ?></a></p>
      </div>
      <?php endif; ?>
    <?php endif; ?>

    <?php if(!$yellowcube_order): ?>
    <!-- Order has not been sent -->
    <div>
      <h3><?php _e('This order has not been sent to YellowCube', 'wooyellowcube'); ?></h3>
      <p><small><strong><u>Important:</u></strong> <?php _e('All order informations has to be saved before sending it to YellowCube');?></p>
      <p><a href="#" onclick="return false;" class="button-primary" id="wooyellowcube-order-send"><?php _e('Send order to YellowCube', 'wooyellowcube');?></a></p>
    </div>
    <?php endif; ?>
    </div>

    <div class="wooyellowcube-middle-right">
        <h3>YellowCube last activites for this product</h3>
        <div class="wooyellowcube-activities">
            <?php
            $productLogs = $wpdb->get_results('SELECT * FROM wooyellowcube_logs WHERE object=\''.get_the_ID().'\' ORDER BY created_at DESC');

            if(count($productLogs) == 0){
                echo '<p>There is no previous logs</p>';
            }else{
                foreach($productLogs as $log){
                    echo '<div class="wooyellowcube-activity">';
                        echo '<div class="wooyellowcube-activity-status">'.$log->type.'</div>';
                        echo '<div class="wooyellowcube-activity-msg"><span class="date">'.date('d/m/Y H:i:s', $log->created_at).'</span><br />'.$log->message.'</div>';
                    echo '</div>';
                }
            }
            ?>
        </div>
    </div>
</div>
