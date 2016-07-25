<?php

global $wpdb, $wooyellowcube, $status;

/**
* Count total entries from wooyellowcube_stock
*/
$total_entries = $wpdb->get_row('SELECT count(id) AS count_entries GROUP BY FROM wooyellowcube_stock GROUP BY yellowcube_articleno');

/**
* Pagination
*/
$pagination_per_page = 10;
$pagination_total_pages = ceil($total_entries->count_entries / $pagination_per_page);

// Get current pagination page
if(isset($_GET['paginate'])){
	$pagination_current_page = ($_GET['paginate'] > $pagination_total_pages) ? 1 : htmlspecialchars($_GET['paginate']);
}else{
	$pagination_current_page = 1;
}

$pagination_first = ($pagination_current_page - 1) * $pagination_per_page;

// Get all the products
$stocks = $wpdb->get_results('SELECT * FROM wooyellowcube_stock GROUP BY yellowcube_articleno LIMIT '.$pagination_first.', '.$pagination_per_page);

?>

<h1><?=__('WooYellowCube', 'wooyellowcube')?> - <?=__('Stock management', 'wooyellowcube')?></h1>
<?php if(count($stocks) == 0): ?>

  <p><?=__('No stock found in YellowCube', 'wooyellowcube')?></p>

<?php else: ?>

<?php if($status === 1): ?>
<p><?=__('Bulking ART update applied', 'wooyellowcube')?></p>
<?php elseif($status === 2): ?>
<p><?=__('Bulking WooCommerce stock change applied', 'wooyellowcube')?></p>
<?php endif; ?>

<form action="" method="post">
  <table class="wp-list-table widefat fixed striped pages">
    <thead>
      <tr>
        <th><strong><?=__('Product name (SKU)', 'wooyellowcube')?></strong></th>
        <th><strong><?=__('WooCommerce stock', 'wooyellowcube')?></strong></th>
        <th><strong><?=__('YellowCube stock', 'wooyellowcube')?></strong></th>
        <th><strong><?=__('YellowCube date', 'wooyellowcube')?></strong></th>
        <th><strong><?=__('Shop & YellowCube Stock Similarity', 'wooyellowcube')?></strong></th>
        <?php if(get_option('wooyellowcube_lotmanagement') == 1): ?><th></th><?php endif; ?>
      </tr>
    </thead>
    <tbody>
      <?php foreach($stocks as $stock): ?>
      <?php
	     $product = new WC_Product($stock->product_id);

	     $woocommerce_stock = (isset($product)) ? $product->get_stock_quantity() : false;
	   ?>
      <tr>
        <td><input type="checkbox" name="products[]" value="<?=$stock->product_id?>" /> <?=$stock->yellowcube_articleno?></td>
        <td><?=$woocommerce_stock?></td>
        <td>
	        <?php
			  $yellowcube_stock = $wpdb->get_var('SELECT SUM(yellowcube_stock) FROM wooyellowcube_stock WHERE product_id='.$stock->product_id);
		     ?>
			  <?=$yellowcube_stock?>

	     </td>
        <td><?=date('d/m/Y H:i', $stock->yellowcube_date)?></td>

        <td>
	       <?php if(!empty($product->post)): ?>
	          <?php if($yellowcube_stock == $woocommerce_stock): ?>
	          <span style="color: #14972B;"><strong><?=__('Same stock', 'wooyellowcube')?></strong></span>
	          <?php else: ?>
	          <span style="color: #CE1A1A;"><strong><?=__('Different stock', 'wooyellowcube')?></strong></span>
	          <?php endif; ?>
          <?php else: ?>
          <span><?=__('Product not in WooCommerce', 'wooyellowcube')?></span>
          <?php endif; ?>
        </td>
        <?php if(get_option('wooyellowcube_lotmanagement') == 1): ?>
        <td>
	        <?php if(!empty($stock->product_id)): ?>
	        <a href="admin.php?page=wooyellowcube-stock-view&id=<?=$stock->product_id?>"><?=__('View lots', 'wooyellowcube')?></a>
	        <?php endif; ?>
	    </td>
	    <?php endif; ?>

      </tr>


      <?php endforeach; ?>
    </tbody>
  </table>

	<div class="bulking-actions">
		<p>
			<strong><?=__('Action on selected products', 'wooyellowcube')?></strong>
			<br />
			<select name="bulking_actions" id="bulking_actions">
				<option value="1"><?=__('Send ART profile', 'wooyellowcube')?></option>
				<option value="2"><?=__('Update WooCommerce Stock with YellowCube', 'wooyellowcube')?></option>
				<option value="3"><?=__('Force to refresh inventory', 'wooyellowcube')?></option>
			</select>
		</p>
		<p>
			<input type="submit" name="bulking_execute" id="bulking_execute" value="<?=__('Execute', 'wooyellowcube')?>" class="button" />
		</p>
	</div>

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
</form>

<?php endif; ?>
