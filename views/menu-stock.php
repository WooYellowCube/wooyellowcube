<?php
global $wpdb, $wooyellowcube, $status;

// count the number of entries in wooyellowcube stocks
$total_entries = $wpdb->get_row('SELECT COUNT(DISTINCT yellowcube_articleno) AS count_entries FROM wooyellowcube_stock');

// pagination settings
$pagination_per_page = 10;
$pagination_total_pages = ceil($total_entries->count_entries / $pagination_per_page);

// get current pagination page
if(isset($_GET['paginate'])){
	$pagination_current_page = ($_GET['paginate'] > $pagination_total_pages) ? 1 : htmlspecialchars($_GET['paginate']);
}else{
	$pagination_current_page = 1;
}

$pagination_first = ($pagination_current_page - 1) * $pagination_per_page;


// get the product stock inventory from database
$stocks = $wpdb->get_results('SELECT * FROM wooyellowcube_stock GROUP BY yellowcube_articleno LIMIT '.$pagination_first.', '.$pagination_per_page);
?>

<h1><?php _e('WooYellowCube', 'wooyellowcube'); ?> - <?php _e('Stock management', 'wooyellowcube');?></h1>

<?php if(count($stocks) == 0): ?>
  <p><?php _e('No stock found in YellowCube', 'wooyellowcube');?></p>
<?php else: ?>

<?php if($status === 1): ?>
<p><?php _e('Bulking ART update applied', 'wooyellowcube'); ?></p>
<?php elseif($status === 2): ?>
<p><?php _e('Bulking WooCommerce stock change applied', 'wooyellowcube'); ?></p>
<?php endif; ?>

<form action="" method="post">
  <table class="wp-list-table widefat fixed striped pages">
    <thead>
      <tr>
        <th><strong><?php _e('Product name (SKU)', 'wooyellowcube'); ?></strong></th>
        <th><strong><?php _e('WooCommerce stock', 'wooyellowcube'); ?></strong></th>
        <th><strong><?php _e('YellowCube stock', 'wooyellowcube'); ?></strong></th>
        <th><strong><?php _e('YellowCube date', 'wooyellowcube'); ?></strong></th>
        <th><strong><?php _e('Shop & YellowCube Stock Similarity', 'wooyellowcube'); ?></strong></th>
        <?php if(get_option('wooyellowcube_lotmanagement') == 1): ?><th></th><?php endif; ?>
      </tr>
    </thead>
    <tbody>
      <?php foreach($stocks as $stock): ?>
		<?php
		// get WooCommerce stock
		$product = wc_get_product($stock->product_id);
		$woocommerce_stock = ($product) ? $product->get_stock_quantity() : false;
		?>
      <tr>
        <td><input type="checkbox" name="products[]" value="<?php echo $stock->product_id?>" /> <?php echo $stock->yellowcube_articleno?></td>
        <td><?php echo $woocommerce_stock?></td>
        <td>
	        <?php
			  $yellowcube_stock = $wpdb->get_var('SELECT SUM(yellowcube_stock) FROM wooyellowcube_stock WHERE product_id='.$stock->product_id);
		     ?>
			  <?php echo $yellowcube_stock?>

	     </td>
        <td><?php echo date('d/m/Y H:i', $stock->yellowcube_date)?></td>

        <td>
	       <?php if(!empty($product->post)): ?>
	          <?php if($yellowcube_stock == $woocommerce_stock): ?>
	          <span style="color: #14972B;"><strong><?php _e('Same stock', 'wooyellowcube'); ?></strong></span>
	          <?php else: ?>
	          <span style="color: #CE1A1A;"><strong><?php _e('Different stock', 'wooyellowcube'); ?></strong></span>
	          <?php endif; ?>
          <?php else: ?>
          <span><?php _e('Product not in WooCommerce', 'wooyellowcube'); ?></span>
          <?php endif; ?>
        </td>
        <?php if(get_option('wooyellowcube_lotmanagement') == 1): ?>
        <td>
	        <?php if(!empty($stock->product_id)): ?>
	        <a href="admin.php?page=wooyellowcube-stock-view&id=<?php echo $stock->product_id?>"><?php _e('View lots', 'wooyellowcube'); ?></a>
	        <?php endif; ?>
	    </td>
	    <?php endif; ?>

      </tr>


      <?php endforeach; ?>
    </tbody>
  </table>

  	<?php
	$url_page = 'admin.php?page=wooyellowcube-stock';

	if($pagination_current_page == 1){
		echo '<a href="'.$url_page.'&paginate=2" class="button">'.__('Next entries', 'wooyellowucbe').' ></a>';
	}elseif($pagination_current_page == $pagination_total_pages){
		echo '<a href="'.$url_page.'&paginate='.($pagination_current_page - 1).'" class="button">< '.__('Previous entries', 'wooyellowcube').'</a>';
	}else{
		echo '<a href="'.$url_page.'&paginate='.($pagination_current_page - 1).'" class="button">< '.__('Previous entries', 'wooyellowcube').'</a>';
		echo '<a href="'.$url_page.'&paginate='.($pagination_current_page + 1).'" class="button">'.__('Next entries', 'wooyellowcube').' ></a>';
	}


   ?>

	<div class="bulking-actions">
		<p>
			<strong><?php _e('Action on selected products', 'wooyellowcube'); ?></strong>
			<br />
			<select name="bulking_actions" id="bulking_actions">
				<option value="1"><?php _e('Send ART profile', 'wooyellowcube'); ?></option>
				<option value="2"><?php _e('Update WooCommerce Stock with YellowCube', 'wooyellowcube'); ?></option>
				<option value="3"><?php _e('Force to refresh inventory', 'wooyellowcube'); ?></option>
			</select>
		</p>
		<p>
			<input type="submit" name="bulking_execute" id="bulking_execute" value="<?php _e('Execute', 'wooyellowcube'); ?>" class="button" />
		</p>
	</div>

</form>

<?php endif; ?>
