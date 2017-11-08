<?php
if (!defined('INDEX')) { require dirname(__FILE__).'/../index.php'; exit; }
require_permission('organizations_view');

$export_fields = [
	'id' => false,
	'name' => true,
	'phone_country_code' => true,
	'phone' => true,
	'phone_ext' => true,
	'website' => true,
	'created_at' => true,
	'updated_at' => true,
];

$table = new TableHelper('Organization');
$table->filters = array('search');

if ($_GET['export'] === 'csv') {
	$data = Organization::find( ['limit' => null, 'total' => false] + $table->find_params() );
	$csv = new CSV();
	$csv->conditional_columns( array_keys($export_fields) );
	$csv->output($data, 'organizations');
}
else {
	$data = Organization::find( $table->find_params() );
	
	$pageTitle = 'Organizations';
?>
<?php if(!is_paginator()): ?>
<div class="table-header">
	<h2 class="page-title"><?= $pageTitle ?></h2>
	<p>
		<a class="btn" href="<?= admin_url('organizations', true) ?>"><?=icon('plus')?> Add Organization</a>
		<a class="btn js-data-table-export" href="<?=h( $table->export_url() )?>" data-fields="<?=h(json_encode( $table->export_fields($export_fields) ))?>"><?=icon('download')?> Export</a>
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
				<th><?= $table->header('phone') ?></th>
				<th><?= $table->header('website') ?></th>
				<th class="span3"><?= $table->header('updated_at') ?></th>
				<th class="actions"></th>
			</tr>
		</thead>
		<tbody>
	<?php endif; ?>
<?php foreach($data as $index => $model): ?>
			<tr>
				<td><a href="<?=admin_url('organizations')?>/edit/<?=$model['id']?>"><?= h($model->display('name')) ?></a></td>
				<td><?= h($model->display('phone')) ?></td>
				<td><a href="<?= h($model->website_url()) ?>" target="_blank"><?= h($model['website']) ?></a></td>
				<td class="span2"><?= h($model->display('updated_at')) ?></td>
				<td class="actions"><?= $table->actions($model, "<a href='".admin_url('organizations')."/edit/%d'>".icon('edit')." Edit</a>", 'delete') ?></td>
			</tr>
<?php endforeach ?>
<?php if(!is_paginator()): ?>
<?php $table->no_records_message($data->total(), 6) ?>
			<tr class="spacer"><td colspan="6"></td></tr>
		</tbody>
	</table>
</div>
<?= $table->ajax_pagination( $data->total() ) ?>
<?php endif; ?>
<?php
}
