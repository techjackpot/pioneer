<?php
if (!defined('INDEX')) { require dirname(__FILE__).'/../index.php'; exit; }
$identifier = "vendors";
$modelname = "Vendor";


/*
$vendors = Vendor::find();
foreach ($vendors as $vendor)
{
	$vendor->update_search_index();
}
*/

require_permission($identifier.'_view');
$export_fields = [
	'id' => true,
	'name' => true,
	'datepublished' => true,
	'topic_id' => true,
	'created_at' => true,
	'updated_at' => true,
];

$table = new TableHelper($modelname);
$table->filters = array('search');

if ($_GET['export'] === 'csv') {
	$data = $modelname::find( ['limit' => null, 'total' => false] + $table->find_params() );
	$csv = new CSV();
	$csv->conditional_columns( array_keys($export_fields) );
	$csv->output($data, $identifier);
}
else {
	$data = $modelname::find( $table->find_params() );
	$pageTitle = "Manage Vendors";
	
?>
<?php if(!is_paginator()): ?>
<div class="table-header">
	<?= display_title($pageTitle) ?>
	<p>
		<a class="btn" href="<?= admin_url($identifier, true) ?>"><?=icon('plus')?> Add Vendor</a>
	</p>
	<?php $table->filters() ?>
</div>
<div class="activity-wall-wrap">
	<div class="activity-wall-title"><?= $table->activity_wall_title() ?></div>
	<div class="activity-wall"><?php ChangeLog::display_model_all($table->model) ?></div>
</div>
<div class="data-table">
	<table class="table table-striped">
		<thead>
			<tr>
				<th><?= $table->header('name') ?></th>
				<th><?= $table->header('type') ?></th>
				<th><?= $table->header('rep_name') ?></th>
				<th><?= $table->header('city') ?></th>
				<th><?= $table->header('state') ?></th>
				<th class="span3"><?= $table->header('updated_at') ?></th>
				<th class="actions"></th>
			</tr>
		</thead>
		<tbody>
	<?php endif; ?>
<?php foreach($data as $index => $model): ?>
			<tr>
				<td><a href="<?=admin_url($identifier)?>/edit/<?=$model['id']?>"><?= h($model->display('name')) ?></a></td>
				<td><?= h($model->display('type')) ?></td>
				<td><?= h($model->display('rep_name')) ?></td>
				<td><?= h($model->display('city')) ?></td>
				<td><?= h($model->display('state')) ?></td>
				<td class="span3"><?= h($model->display('updated_at')) ?></td>
				<td class="actions"><?= $table->actions($model, 'edit') ?></td>
			</tr>
<?php endforeach ?>
<?php if(!is_paginator()): ?>
<?php $table->no_records_message($data->total(), 7) ?>
			<tr class="spacer"><td colspan="7"></td></tr>
		</tbody>
	</table>
</div>
<?= $table->ajax_pagination( $data->total() ) ?>
<?php endif; ?>
<?php
}
