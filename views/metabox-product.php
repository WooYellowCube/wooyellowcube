<?php
global $wpdb, $post;

// Get product information from database
$yellowcube_product = $wpdb->get_row('SELECT * FROM wooyellowcube_products WHERE id_product=\''.$post->ID.'\' AND id_variation=\'0\'');
?>
<!-- Product identification -->
<input type="hidden" name="wooyellowcube-product" id="wooyellowcube-product-id" value="<?php echo $post->ID?>" />

<?php if($yellowcube_product): ?>
<!-- Current status -->
<div>

  <h3><?php _e('Current status', 'wooyellowcube')?></h3>
	<?php
	switch($yellowcube_product->yc_response){
	    case 0: echo '<img src="'.plugin_dir_url('').'wooyellowcube/assets/images/yc-error.png" alt="'.__('Error', 'wooyellowcube').'" />'; break;
	    case 1: echo '<img src="'.plugin_dir_url('').'wooyellowcube/assets/images/yc-pending.png" alt="'.__('Pending', 'wooyellowcube').'" />'; break;
	    case 2: echo '<img src="'.plugin_dir_url('').'wooyellowcube/assets/images/yc-success.png" alt="'.__('Success', 'wooyellowcube').'" />'; break;
	}
	?>
  <p><a href="#" onclick="return false;" id="wooyellowcube-product-refresh" class="button"><?php _e('Refresh status', 'wooyellowcube')?></a></p>

  <p><strong><?php _e('Lot management', 'wooyellowcube'); ?> : </strong> <?php if($yellowcube_product->lotmanagement == '1'){ __('Enable', 'wooyellowcube'); } else { __('Disable', 'wooyellowcube'); }?></p>

  <p>
    <strong><?php _e('Status', 'wooyellowcube'); ?> :</strong>
    <?php
    switch($yellowcube_product->yc_response){
        case 0: echo __('Error', 'wooyellowcube'); break;
        case 1: echo __('Pending', 'wooyellowcube'); break;
        case 2: echo __('Success', 'wooyellowcube'); break;
    }
    ?>
    <br />
    <strong><?php _e('Message', 'wooyellowcube'); ?> :</strong> <em><?php echo $yellowcube_product->yc_status_text?></em>
  </p>

</div>
<?php endif; ?>

<?php if(!$yellowcube_product): ?>
<!-- Product has not been sent -->
<div>
  <h3><?php _e('Send this product to YellowCube', 'wooyellowcube'); ?></h3>
  <p><?php _e('Please save your product information before to send to YellowCube.', 'wooyellowcube'); ?></p>
  <p>
	  <label for="lotmanagement"><?php _e('Lot management', 'wooyellowcube'); ?></label>
	  <select name="lotmanagement" id="lotmanagement">
		<option value="0"><?php _e('Disable', 'wooyellowcube');?></option>
		<option value="1"><?php _e('Enable', 'wooyellowcube');?></option>
	  </select>
  </p>
  <p><a href="#" onclick="return false;" class="button" id="wooyellowcube-product-send"><?php _e('Send product to YellowCube', 'wooyellowcube'); ?></a></p>
</div>
<?php endif; ?>

<?php if($yellowcube_product): ?>
<!-- Product has been sent -->
<div>
  <h3><?php _e('Update YellowCube informations', 'wooyellowcube'); ?></h3>
  <p><?php _e('Please save your product information before to send to YellowCube.', 'wooyellowcube'); ?></p>
  <p>
	  <label for="lotmanagement"><?php _e('Lot management', 'wooyellowcube'); ?></label>
	  <select name="lotmanagement" id="lotmanagement">
		<option value="0" <?php if($yellowcube_product->lotmanagement == '0')  echo 'selected="selected"'; ?>><?php _e('Disable', 'wooyellowcube'); ?></option>
		<option value="1" <?php if($yellowcube_product->lotmanagement == '1') echo 'selected="selected"'; ?>><?php _e('Enable', 'wooyellowcube'); ?></option>
	  </select>
  </p>
  <p><a href="#" onclick="return false;" class="button" id="wooyellowcube-product-update"><?php _e('Update product to YellowCube', 'wooyellowcube'); ?></a></p>
</div>

<!-- Remove the product from YellowCube -->
<div>
  <h3><?php _e('Remove this product from YellowCube', 'wooyellowcube'); ?></h3>
  <p><?php _e('Your product will be desactivated in YellowCube', 'wooyellowcube'); ?></p>
  <p><a href="#" onclick="return false;" class="button" id="wooyellowcube-product-remove"><?php _e('Remove this product from YellowCube', 'wooyellowcube'); ?></a></p>
</div>
<?php endif; ?>

<?php
/**
* Get variations products
*/
$product_variable = new WC_Product_Variable($post);
$variations = $product_variable->get_available_variations();

if(count($variations)){
?>
<h3><?php _e('Manage variations ART', 'wooyellowcube'); ?></h3>
<p><?php _e('<strong>Information :</strong> Your product with variations need to be save before', 'wooyellowcube'); ?></p>

<table class="wp-list-table widefat fixed striped pages">
  <thead>
    <tr>
      <th width="30%"><strong><?php _e('Variation SKU', 'wooyellowcube'); ?></strong></th>
      <th width="60%"><strong><?php _e('Actions', 'wooyellowcube'); ?></strong></th>
      <th width="10%"><strong><?php _e('Status', 'wooyellowcube'); ?></strong></th>
    </tr>
  </thead>
  <tbody>
<?php
  foreach($variations as $variation){
    $variation_id = $variation['variation_id'];

    // Get information from YellowCube
    $yellowcube_variation = $wpdb->get_row('SELECT * FROM wooyellowcube_products WHERE id_product=\''.$variation_id.'\'');
  ?>
  <tr>
    <td><?php echo $variation['sku']?></td>
    <td>
      <?php if($yellowcube_variation): ?>

        <a href="#" onclick="return false;" class="button wooyellowcube-product-variation-update"><?php _e('Update', 'wooyellowcube'); ?></a>
        <input type="hidden" class="wooyellowcube-product-variation-id" value="<?php echo $variation_id?>" />

        <a href="#" onclick="return false;" class="button wooyellowcube-product-variation-desactivate"><?php _e('Desactivate', 'wooyellowcube');?></a>
        <input type="hidden" class="wooyellowcube-product-variation-id" value="<?php echo $variation_id?>" />
      <?php else: ?>
        <a href="#" onclick="return false;" class="button wooyellowcube-product-variation-send"><?php _e('Insert', 'wooyellowcube'); ?></a>
        <input type="hidden" class="wooyellowcube-product-variation-id" value="<?php echo $variation_id?>" />
      <?php endif; ?>
    </td>
    <td>
      <?php if($yellowcube_variation):

      switch($yellowcube_variation->yc_response){
        case 0: echo '<img src="'.plugin_dir_url('').'wooyellowcube/assets/images/yc-error.png" alt="'.__('Error', 'wooyellowcube').'" />'; break;
        case 1: echo '<img src="'.plugin_dir_url('').'wooyellowcube/assets/images/yc-pending.png" alt="'.__('Pending', 'wooyellowcube').'" />'; break;
        case 2: echo '<img src="'.plugin_dir_url('').'wooyellowcube/assets/images/yc-success.png" alt="'.__('Success', 'wooyellowcube').'" />'; break;
      }
      ?>
      <?php else: ?>
        <img src="<?php echo plugin_dir_url(''); ?>wooyellowcube/assets/images/yc-unlink.png" alt="<?php _e('Not linked', 'wooyellowcube');?>" />
      <?php endif; ?>
    </td>
  </tr>
  <?php
  }
?>
  </tbody>
</table>
<?php
}
?>
