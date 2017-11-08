<?php
if (!defined('INDEX')) { require dirname(__FILE__).'/../index.php'; exit; }
require_permission('users_view');

$export_fields = [
	'id' => false,
	'name' => true, 
	'group_id' => false,
	'status_id' => false,
	'type_id' => false,
	'username' => false,
	'email' => true,
	'first_name' => true,
	'last_name' => true,
	'title' => true,
	'phone_country_code' => true,
	'phone' => true,
	'phone_ext' => true,
	'mobile_country_code' => true,
	'mobile' => true,
	'fax_country_code' => true,
	'fax' => false,
	'notes' => true,
	'created_at' => false,
	'updated_at' => false,
];

$table = new TableHelper('User');
$table->filters = array('type_id', 'status_id', 'search');

if ($_GET['export'] === 'csv') {
	$data = User::find( ['limit' => null, 'total' => false] + $table->find_params() );
	$csv = new CSV();
	$csv->conditional_columns( array_keys($export_fields) );
	$csv->output($data, 'people');
}
else {
	$data = User::find( $table->find_params() );
	$pageTitle = 'People';
	
?>
<?php if(!is_paginator()): ?>
<div class="table-header">
	<?= display_title($pageTitle) ?>
	<p>
		<a class="btn" href="<?= admin_url('users', true) ?>"><?=icon('plus')?> Add Person</a>
		<a class="btn js-data-table-export" href="<?=h( $table->export_url() )?>" data-fields="<?=h(json_encode( $table->export_fields($export_fields) ))?>"><?=icon('download')?> Export</a>
		<?php if (has_permission('users_permissions')): ?><a class="btn" href="<?= admin_url('users_groups') ?>"><?=icon('asterisk')?> Manage Permissions</a><?php endif ?>
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
				<th><?= $table->header('title') ?></th>
				<th><?= $table->header('organization_id') ?></th>
				<th><?= $table->header('email') ?></th>
				<th><?= $table->header('status_id') ?></th>
				<th><?= $table->header('type_id') ?></th>
				<th class="span3"><?= $table->header('updated_at') ?></th>
				<th class="actions"></th>
			</tr>
		</thead>
		<tbody>
	<?php endif; ?>
<?php foreach($data as $index => $model): ?>
			<tr>
				<td><?= '<a href="'.h(admin_url('User', $model['id'])).'">'.h($model->name()).'</a>' ?></td>
				<td><?= h($model->display('title')) ?></td>
				<td><?= h($model->display('organization_id')) ?></td>
				<td><a href="mailto:<?= h($model['email']) ?>"><?= h($model->display('email')) ?></a></td>
				<td><?= h($model->display('status_id')) ?></td>
				<td><?= h($model->display('type_id')) ?></td>
				<td class="span3"><?= h($model->display('updated_at')) ?></td>
				<td class="actions"><?= $table->actions($model, 'edit', 'delete', '<a href="/admin/people/access/'.$model['id'].'"><i class="icon-file"></i> Access Account</a>') ?></td>
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
