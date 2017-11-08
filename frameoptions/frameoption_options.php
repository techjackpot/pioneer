<?php
if (!defined('INDEX')) { require dirname(__FILE__).'/../index.php'; exit; }

$identifier = "frameoptionvalue";

require_permission($identifier.'_view');
$export_fields = [
	'id' => true,
	'name' => true,
	'datepublished' => true,
	'topic_id' => true,
	'created_at' => true,
	'updated_at' => true,
];

$table = new TableHelper(ucfirst($identifier));
$table->filters = array('search');

if ($_GET['export'] === 'csv') {
	$data = FrameOptionValue::find( ['limit' => null, 'total' => false] + $table->find_params() );
	$csv = new CSV();
	$csv->conditional_columns( array_keys($export_fields) );
	$csv->output($data, $identifier);
}
else {
	$frameoption = FrameOption::get($path[3]);
	
	$params = $table->find_params();
	
	$params['where'] = "`frameoption_id` = ".sanitize($path[3]);
	
	$data = FrameOptionValue::find( $params );
	
	$pageTitle = "Frame Option Values (".$frameoption->display('name').")";
	
?>
<?php if(!is_paginator()): ?>
<div class="table-header">
	<?= display_title('<a href="'.admin_url('frameoptions').'">Frame Options</a> › '.$pageTitle) ?>
	<p>
		<a class="btn" href="<?= admin_url('frameoptions') ?>/addvalue/<?=$path[3]?>"><?=icon('plus')?> Add Frame Option Value</a>
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
				<th><?= $table->header('abbreviation') ?></th>
				<th><?= $table->header('name') ?></th>
				<th><?= $table->header('sort_order') ?></th>
				<th class="span3"><?= $table->header('updated_at') ?></th>
				<th class="actions"></th>
			</tr>
		</thead>
		<tbody>
	<?php endif; ?>
<?php foreach($data as $index => $model): ?>
			<tr>
				<td><a href="<?=admin_url('frameoptions')?>/editvalue/<?=$model['id']?>"><?= h($model->display('abbreviation')) ?></a></td>
				<td><a href="<?=admin_url('frameoptions')?>/editvalue/<?=$model['id']?>"><?= h($model->display('name')) ?></a></td>
				<td><?= h($model->display('sort_order')) ?></td>
				<td class="span3"><?= h($model->display('updated_at')) ?></td>
				<td class="actions"><?= $table->actions($model, '<a href="'.admin_url('frameoptions').'/editvalue/'.$model['id'].'">'.icon('pencil').' Edit</a>', '<a href="'.admin_url('frameoptions').'/deletevalue/'.$model['id'].'">'.icon('trash').' Delete</a>')?></td>
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
