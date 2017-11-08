<?php
if (!defined('INDEX')) { require dirname(__FILE__).'/../index.php'; exit; }
require_permission('users_permissions');

switch($path[3]):
case 'delete':
	$model = new PermissionGroup($path[4]);
	if (!$model['id']) error_404();
	
	if ($model->delete()) { success_message('Permission group was successfully deleted.'); }
	else { error_message('Sorry, we were not able to delete that permission group.'); }
	redirect_to( admin_url('users_groups') );
break;
case 'add':
case 'edit':
	$form = new FormHelper();
	$form->prefix = 'user_group_';
	if ($path[3] === 'edit') {
		$model = new PermissionGroup($path[4]);
		if (!$model['id']) error_404();
		$edit = true;
	}
	else {
		$model = new PermissionGroup();
		$edit = false;
	}
	$form->data = &$model;
	
	if ($_POST) {
		$values = $form->apply_post('name', 'permission_ids');

		$model->validate_required('name');
		
		if (!$model->errors) {
			if ($success = $model->save()) { success_message('Permission group was successfully saved.'); }
			else { error_message('Sorry, we were not able to save that permission group.'); }
			if (is_json()) { json_response([ 'success' => $success ]); }
			else if ($success) { redirect_to( admin_url('users_groups') ); }
		}
		else if (is_json()) { json_response([ 'success' => false, 'errors' => $model->errors ]); }
	}
	
	$pageTitle = ['<a href="'.admin_url($model).'">Permission Groups</a>', $edit ? h($model->name()) : 'Add'];
	
	$sql = sql("SELECT c.id, c.name, p.name parent FROM users_permissions c INNER JOIN users_permissions p ON c.parent_id = p.id WHERE c.parent_id is not null ORDER BY p.sort, c.sort");
	$last_parent = null;
	while($row = $sql->fetch_assoc()) {
		if ($last_parent !== $row['parent']) {
			$permissions .= "<label class='checkbox'><b>".h($row['parent'])."</b></label>";
			$last_parent = $row['parent'];
		}
		$permissions .= $form->checkbox_field("permission_ids[{$row['id']}]", array('label' => $row['name']));
	}
	$form->activity_wall = $edit;
	echo $form->start(array('class' => (is_modal() ? 'form-horizontal js-form' : null), 'modal_class' => 'modal-large')),
		$form->fieldset($pageTitle),
		$form->field('name', array('class' => 'input-xxlarge')),
		$form->block('permissions', $permissions),
		$form->submit(),
		$form->end();
break;
case "":

	$table = new TableHelper('PermissionGroup');
	$data = PermissionGroup::find( $table->find_params() );

	$pageTitle = 'Permission Groups';
?>
<?php if(!is_paginator()): ?>
<div class="table-header">
	<h2 class="page-title"><?= $pageTitle ?></h2>
	<p>
		<a class="btn" href="<?= admin_url('users_groups', true) ?>"><?=icon('plus')?> Add Group</a>
	</p>
</div>
<div class="activity-wall-wrap">
	<div class="activity-wall-title"><?= $table->activity_wall_title() ?></div>
	<div class="activity-wall"><?php ChangeLog::display_model_all($table->model) ?></div>
</div>
	<div class="data-table">
	<table class="table table-striped">
		<thead>
			<tr>
				<th class="span1"><?= $table->header('id') ?></th>
				<th><?= $table->header('name') ?></th>
				<th class="span3"><?= $table->header('updated_at') ?></th>
				<th class="actions"></th>
			</tr>
		</thead>
		<tbody>
<?php endif; ?>
<?php foreach($data as $index => $model): ?>
			<tr>
				<td class="span1"><a href="<?=admin_url('users_groups')?>/edit/<?=$model['id']?>"><?= h($model->display('id')) ?></a></td>
				<td><?= h($model->display('name')) ?></td>
				<td class="span3"><?= h($model->display('updated_at')) ?></td>
				<td class="actions"><?= $table->actions($model) ?></td>
			</tr>
<?php endforeach ?>
<?php if(!is_paginator()): ?>
<?php $table->no_records_message($data->total(), 4) ?>
			<tr class="spacer"><td colspan="4"></td></tr>
		</tbody>
	</table>
</div>
<?= $table->ajax_pagination( $data->total() ) ?>
<?php endif; ?>
<?php
	
break;
default:
	error_404();
break;
endswitch;