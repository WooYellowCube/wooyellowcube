<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/u/bs/dt-1.10.12/datatables.min.css"/>

<script type="text/javascript" src="https://cdn.datatables.net/u/bs/dt-1.10.12/datatables.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.8.4/moment.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/plug-ins/1.10.12/sorting/datetime-moment.js"></script>

<script type="text/javascript">
	jQuery(document).ready(function(){
		jQuery.fn.dataTable.moment('dd/mm/YY HH:ii');
		jQuery('.datatable').DataTable();
	});
</script>

<h1><?=__('WooYellowCube Activity logs', 'wooyellowcube')?></h1>
<p><?=__('If you have any question about the error displayed, please contact us on our website <a href="http://www.wooyellowcube.com" title="WooYellowCube" target="_blank">www.wooyellowcube.com</a>', 'wooyellowcube')?></p>

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
      <th width="10%"><?=__('Date', 'wooyellowcube')?></th>
      <th width="10%"><?=__('Reference', 'wooyellowcube')?></th>
      <th width="15%"><?=__('Action', 'wooyellowcube')?></th>
      <th width="10%"><?=__('Order / Product', 'wooyellowcube')?></th>
      <th><?=__('Message', 'wooyellowcube')?></th>
    </tr>
  </thead>
  <tbody>
    <?php foreach($yellowcube_activities as $activity): ?>
      <tr>
        <td><?=date('Y/m/d H:i', $activity->created_at)?></td>
        <td><?=$activity->reference?></td>
        <td><?=$activity->type?></td>
        <td>#<?=$activity->object?></td>
        <td>
          <?php switch($activity->response){
            case 0: echo '<img src="'.plugin_dir_url('').'wooyellowcube/assets/images/yc-error.png" alt="'.__('Error', 'wooyellowcube').'" />'; break;
            case 1: echo '<img src="'.plugin_dir_url('').'wooyellowcube/assets/images/yc-success.png" alt="'.__('Success', 'wooyellowcube').'" />'; break;
          } ?>
          <?=$activity->message?>
        </td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>
</div>
<?php endif; ?>
