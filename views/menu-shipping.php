<?php
global $woocommerce, $wpdb;

$shipping_methods = $woocommerce->shipping->load_shipping_methods();
$yellowcubeShippingMethods = unserialize(get_option('wooyellowcube_shipping_methods'));

if(isset($_POST['shippingUpdate'])){
    $shippingMethodID = $_POST['method_instance'];
    $shippingMethodStatus = $_POST['shippingStatus'];
    $shippingMethodBasic = $_POST['shippingBasic'];
    $shippingMethodAdditional = $_POST['shippingAdditional'];

    // shipping informations are empty
    if(!is_array($yellowcubeShippingMethods)){
        $yellowcubeShippingMethods = array();
    }

    // check if an entry is already existing for this shipping method
    $yellowcubeShippingMethods[$shippingMethodID] = array();
    $yellowcubeShippingMethods[$shippingMethodID]['status'] = $shippingMethodStatus;
    $yellowcubeShippingMethods[$shippingMethodID]['basic'] = $shippingMethodBasic;
    $yellowcubeShippingMethods[$shippingMethodID]['additional'] = $shippingMethodAdditional;
    update_option('wooyellowcube_shipping_methods', serialize($yellowcubeShippingMethods));
}
?>

<h1><?php _e('Shipping management', 'wooyellowcube'); ?></h1>
<h2>Zones shipping</h2>
<p>Attribute YellowCube shipping methods to zone shipping methods.</p>

<?php
$shippingZones = WC_Shipping_Zones::get_zones();

if(is_array($shippingZones)){

    if(count($shippingZones) == 0){
        echo '<p>There is no shipping zone configured on your WooCommerce installation</p>';
    }else{

        foreach($shippingZones as $zone){

            echo '<h3>' . $zone['zone_name'] . '</h3>';
            $zoneShippingMethods = $zone['shipping_methods'];

            if(count($zoneShippingMethods) > 0): ?>
                <table class="wp-list-table widefat striped pages">
                    <thead>
                        <tr>
                            <th><strong>Name</strong></th>
                            <th><strong>Status</strong></th>
                            <th><strong>BasicShippingServices</strong></th>
                            <th><strong>AdditionalShippingServices</strong></th>
                            <th><strong>Action</strong></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach($zoneShippingMethods as $method): ?>
                        <form action="" method="post">
                            <tr>
                                <td  width="400"><?=$method->title?></td>
                                <td>
                                    <select name="shippingStatus" id="shippingStatus" style="width: 100%;">
                                        <option value="0" <?php if(isset($yellowcubeShippingMethods[$method->instance_id]) && $yellowcubeShippingMethods[$method->instance_id]['status'] == 0) echo 'selected="selected"'; ?>>Desactivated</option>
                                        <option value="1" <?php if(isset($yellowcubeShippingMethods[$method->instance_id]) && $yellowcubeShippingMethods[$method->instance_id]['status'] == 1) echo 'selected="selected"'; ?>>Activated</option>
                                    </select>
                                </td>
                                <td>
                                    <select name="shippingBasic" id="shippingBasic" style="width: 100%;">
                                        <option value="ECO" <?php if(isset($yellowcubeShippingMethods[$method->instance_id]) && $yellowcubeShippingMethods[$method->instance_id]['basic'] == 'ECO') echo 'selected="selected"'; ?>>ECO</option>
                                        <option value="PRI" <?php if(isset($yellowcubeShippingMethods[$method->instance_id]) && $yellowcubeShippingMethods[$method->instance_id]['basic'] == 'PRI') echo 'selected="selected"'; ?>>PRI</option>
                                        <option value="PICKUP" <?php if(isset($yellowcubeShippingMethods[$method->instance_id]) && $yellowcubeShippingMethods[$method->instance_id]['basic'] == 'PICKUP') echo 'selected="selected"'; ?>>PICKUP</option>
                                    </select>
                                </td>
                                <td>
                                    <select name="shippingAdditional" id="shippingAdditional" style="width: 100%;">
                                        <option value="NONE" <?php if(isset($yellowcubeShippingMethods[$method->instance_id]) && $yellowcubeShippingMethods[$method->instance_id]['additional'] == 'NONE') echo 'selected="selected"'; ?>>None</option>
                                        <option value="SI" <?php if(isset($yellowcubeShippingMethods[$method->instance_id]) && $yellowcubeShippingMethods[$method->instance_id]['additional'] == 'SI') echo 'selected="selected"'; ?>>SI</option>
                                        <option value="SI:AZS" <?php if(isset($yellowcubeShippingMethods[$method->instance_id]) && $yellowcubeShippingMethods[$method->instance_id]['additional'] == 'SI:AZS') echo 'selected="selected"'; ?>>SI:AZS</option>
                                        <option value="SA" <?php if(isset($yellowcubeShippingMethods[$method->instance_id]) && $yellowcubeShippingMethods[$method->instance_id]['additional'] == 'SA') echo 'selected="selected"'; ?>>SA</option>
                                        <option value="APOST" <?php if(isset($yellowcubeShippingMethods[$method->instance_id]) && $yellowcubeShippingMethods[$method->instance_id]['additional'] == 'APOST') echo 'selected="selected"'; ?>>APOST</option>
                                        <option value="INTPRI;GR" <?php if(isset($yellowcubeShippingMethods[$method->instance_id]) && $yellowcubeShippingMethods[$method->instance_id]['additional'] == 'INTPRI;GR') echo 'selected="selected"'; ?>>INTPRI;GR</option>
                                        <option value="INTPRI;MX" <?php if(isset($yellowcubeShippingMethods[$method->instance_id]) && $yellowcubeShippingMethods[$method->instance_id]['additional'] == 'INTPRI;MX') echo 'selected="selected"'; ?>>INTPRI;MX</option>
                                        <option value="INTECO;GR" <?php if(isset($yellowcubeShippingMethods[$method->instance_id]) && $yellowcubeShippingMethods[$method->instance_id]['additional'] == 'INTECO;GR') echo 'selected="selected"'; ?>>INTECO;GR</option>
                                    </select>
                                </td>
                                <td>
                                    <input type="hidden" name="method_instance" id="method_instance" value="<?=$method->instance_id?>" />
                                    <input type="submit" id="shippingUpdate" name="shippingUpdate" value="Update" class="button-primary button-large" />
                                </td>
                            </tr>
                        </form>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif;
        }
    }
}








$defaultZone = new WC_Shipping_Zone(0);
$defaultZoneDatas = $defaultZone->get_data();

echo '<h2>'.$defaultZoneDatas['zone_name'].'</h2>';
?>
<table class="wp-list-table widefat striped pages">
    <thead>
        <tr>
            <th><strong>Name</strong></th>
            <th><strong>Status</strong></th>
            <th><strong>BasicShippingServices</strong></th>
            <th><strong>AdditionalShippingServices</strong></th>
            <th><strong>Action</strong></th>
        </tr>
    </thead>
    <tbody>
    <?php foreach($defaultZone->get_shipping_methods() as $method): ?>
        <form action="" method="post">
            <tr>
                <td width="400"><?=$method->title?></td>
                <td>
                    <select name="shippingStatus" id="shippingStatus" style="width: 100%;">
                        <option value="0" <?php if(isset($yellowcubeShippingMethods[$method->instance_id]) && $yellowcubeShippingMethods[$method->instance_id]['status'] == 0) echo 'selected="selected"'; ?>>Desactivated</option>
                        <option value="1" <?php if(isset($yellowcubeShippingMethods[$method->instance_id]) && $yellowcubeShippingMethods[$method->instance_id]['status'] == 1) echo 'selected="selected"'; ?>>Activated</option>
                    </select>
                </td>
                <td>
                    <select name="shippingBasic" id="shippingBasic" style="width: 100%;">
                        <option value="ECO" <?php if(isset($yellowcubeShippingMethods[$method->instance_id]) && $yellowcubeShippingMethods[$method->instance_id]['basic'] == 'ECO') echo 'selected="selected"'; ?>>ECO</option>
                        <option value="PRI" <?php if(isset($yellowcubeShippingMethods[$method->instance_id]) && $yellowcubeShippingMethods[$method->instance_id]['basic'] == 'PRI') echo 'selected="selected"'; ?>>PRI</option>
                        <option value="PICKUP" <?php if(isset($yellowcubeShippingMethods[$method->instance_id]) && $yellowcubeShippingMethods[$method->instance_id]['basic'] == 'PICKUP') echo 'selected="selected"'; ?>>PICKUP</option>
                    </select>
                </td>
                <td>
                    <select name="shippingAdditional" id="shippingAdditional" style="width: 100%;">
                        <option value="NONE" <?php if(isset($yellowcubeShippingMethods[$method->instance_id]) && $yellowcubeShippingMethods[$method->instance_id]['additional'] == 'NONE') echo 'selected="selected"'; ?>>None</option>
                        <option value="SI" <?php if(isset($yellowcubeShippingMethods[$method->instance_id]) && $yellowcubeShippingMethods[$method->instance_id]['additional'] == 'SI') echo 'selected="selected"'; ?>>SI</option>
                        <option value="SI:AZS" <?php if(isset($yellowcubeShippingMethods[$method->instance_id]) && $yellowcubeShippingMethods[$method->instance_id]['additional'] == 'SI:AZS') echo 'selected="selected"'; ?>>SI:AZS</option>
                        <option value="SA" <?php if(isset($yellowcubeShippingMethods[$method->instance_id]) && $yellowcubeShippingMethods[$method->instance_id]['additional'] == 'SA') echo 'selected="selected"'; ?>>SA</option>
                        <option value="APOST" <?php if(isset($yellowcubeShippingMethods[$method->instance_id]) && $yellowcubeShippingMethods[$method->instance_id]['additional'] == 'APOST') echo 'selected="selected"'; ?>>APOST</option>
                        <option value="INTPRI;GR" <?php if(isset($yellowcubeShippingMethods[$method->instance_id]) && $yellowcubeShippingMethods[$method->instance_id]['additional'] == 'INTPRI;GR') echo 'selected="selected"'; ?>>INTPRI;GR</option>
                        <option value="INTPRI;MX" <?php if(isset($yellowcubeShippingMethods[$method->instance_id]) && $yellowcubeShippingMethods[$method->instance_id]['additional'] == 'INTPRI;MX') echo 'selected="selected"'; ?>>INTPRI;MX</option>
                        <option value="INTECO;GR" <?php if(isset($yellowcubeShippingMethods[$method->instance_id]) && $yellowcubeShippingMethods[$method->instance_id]['additional'] == 'INTECO;GR') echo 'selected="selected"'; ?>>INTECO;GR</option>
                    </select>
                </td>
                <td>
                    <input type="hidden" name="method_instance" id="method_instance" value="<?=$method->instance_id?>" />
                    <input type="submit" id="shippingUpdate" name="shippingUpdate" value="Update" class="button-primary button-large" />
                </td>
            </tr>
        </form>
    <?php endforeach; ?>
    </tbody>
</table>
