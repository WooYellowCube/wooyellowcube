<h1><?php _e('Shipping management', 'wooyellowcube'); ?></h1>
<?php
global $woocommerce, $wpdb;

$shipping_methods = $woocommerce->shipping->load_shipping_methods();
$shipping_yellowcube = unserialize(get_option('wooyellowcube_shipping'));
$additional_yellowcube = unserialize(get_option('wooyellowcube_shipping_additional'));

?>
<h2><?php _e('Allowed shipping methods', 'wooyellowcube'); ?></h2>
<p><?php _e('Only one parameter by Service can be used', 'wooyellowcube'); ?></p>
<p><strong>BasicShippingServices :</strong><br /><em>ECO - PRI - PICKUP</em></p>

<p><strong>AdditionalShippingService :</strong><br /><em>SI - SI:AZS - SA - APOST - INTPRI;GR - INTPRI;MX - INTECO;GR</em></p>

<h2>Shipping rules</h2>
<form action="" method="post">

  <!-- Shipping methods -->
  <table class="wp-list-table widefat fixed striped pages">
    <thead>
      <tr>
        <th><strong><?php _e('Shipping method', 'wooyellowcube'); ?></strong></th>
        <th><strong><?php _e('Shipping method ID', 'wooyellowcube'); ?></strong></th>
        <th><strong>BasicShippingServices</strong></th>
        <th><strong>AdditionalShippingServices</strong></th>
      </tr>
    </thead>
    <tbody>
      <?php foreach($shipping_methods as $method): ?>
      <tr>
        <input type="hidden" name="yellowcube_shipping_id[]" name="yellowcube_shipping_id[]" value="<?php echo $method->id?>" />
        <td><strong><?php echo $method->title?></strong></td>
        <td><?php echo $method->id?></td>
        <?php $method_shipping = (isset($shipping_yellowcube[$method->id])) ? $shipping_yellowcube[$method->id] : 'ECO'; ?>
        <td><input type="text" name="yellowcube_shipping[]" value="<?php echo $method_shipping; ?>" /></td>
        <?php $method_additional = (isset($additional_yellowcube[$method->id])) ? $additional_yellowcube[$method->id] : ''; ?>
        <td><input type="text" name="yellowcube_additionals[]" value="<?php echo $method_additional; ?>" /></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <br />

  <!-- Form validation -->
  <p>
    <input class="button-primary button-large wooyellowcube-right" type="submit" name="submit_shipping" id="submit_shipping" value="<?php _e('Save shipping informations', 'wooyellowcube'); ?>" style="margin-right: 40px;" />
  </p>

</form>
