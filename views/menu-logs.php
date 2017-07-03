<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/u/bs/dt-1.10.12/datatables.min.css"/>

<script type="text/javascript" src="https://cdn.datatables.net/u/bs/dt-1.10.12/datatables.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.8.4/moment.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/plug-ins/1.10.12/sorting/datetime-moment.js"></script>

<script type="text/javascript">
	jQuery(document).ready(function(){
		jQuery.fn.dataTable.moment('dd/mm/YY H:i');
		jQuery('.datatable').DataTable({
			'order': [[0, 'desc']],
			'displayLength': 15
		});
	});
</script>

<h1><?php _e('WooYellowCube Activity logs', 'wooyellowcube'); ?></h1>
<p><?php _e('If you have any question about error displayed, please contact YellowCube to get more informations (<a href="http://yellowcube.ch">www.yellowcube.ch</a> or by phone <strong>+41 58 386 48 08</strong>)', 'wooyellowcube'); ?></p>

<hr />

<?php
global $wpdb;

// Get last 20 activities from database
$yellowcube_activities = $wpdb->get_results('SELECT * FROM wooyellowcube_logs ORDER BY created_at DESC LIMIT 0, 600');

if(count($yellowcube_activities) == 0): ?>
<p>They is currently no recent activities</p>
<?php else: ?>
<div style="overflow-y: scroll; width: 100%; height: 890px">
<table class="wp-list-table widefat fixed striped datatable ">
  <thead>
    <tr>
      <th width="10%"><?php _e('Date', 'wooyellowcube'); ?></th>
      <th width="10%"><?php _e('Reference', 'wooyellowcube'); ?></th>
      <th width="15%"><?php _e('Action', 'wooyellowcube'); ?></th>
      <th width="10%"><?php _e('Order / Product', 'wooyellowcube'); ?></th>
      <th><?php _e('Message', 'wooyellowcube'); ?></th>
    </tr>
  </thead>
  <tbody>
    <?php foreach($yellowcube_activities as $activity): ?>
      <tr>
        <td><?php echo date('Y/m/d H:i', $activity->created_at); ?></td>
        <td><?php echo $activity->reference; ?></td>
        <td><?php echo $activity->type; ?></td>
        <td>
					<?php if(strpos($activity->type, 'ART') !== false): ?>
						<?php
						$wc_product = new WC_Product((int)$activity->object);

						if($wc_product){
							echo $wc_product->get_sku();
						}else{
							echo $activity->object;
						}
						?>
					<?php else: ?>
						#<?php echo $activity->object; ?>
					<?php endif; ?>
				</td>
        <td>
          <?php echo $activity->message; ?>
        </td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>
</div>
<?php endif; ?>
