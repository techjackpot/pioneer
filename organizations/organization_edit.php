<?php
if (!defined('INDEX')) { require dirname(__FILE__).'/../index.php'; exit; }
require_permission('organizations_edit');


$model = new Organization($path[3]);
if (!$model['id']) error_404();

$form = new FormHelper();
$form->data = &$model;

$form->prefix = 'organization_';

if ($_POST) {

	$values = $form->apply_post('name', 'parent_id', 'phone', 'phone_ext', 'phone_country_code', 'fax', 'fax_country_code', 'website', 'notes', 'customer_number', 'rep_ids');
	$model->validate_required('name', 'customer_number');
	
	if (!$model->errors) {
		if ($success = $model->save()) {
			success_message('Organization was successfully saved.');
		}
		else { error_message('Sorry, we were not able to save that organization.'); }
		if (is_json()) { json_response([ 'success' => $success ]); }
		else if ($success) { redirect_to( admin_url('organizations', $model['id']) ); }
	}
	else if (is_json()) { json_response([ 'success' => false, 'errors' => $model->errors ]);  }
	else {
		error_message('Sorry, we were not able to save that organization. Please check all required fields.');
	}

}

$form->activity_wall = true;
$pageTitle = ['<a href="'.admin_url($model).'">Organizations</a>', h($model->name())];
echo $form->start(array('class' => (is_modal() ? 'form-horizontal' : null), 'modal_class' => 'modal-large', 'multipart' => true)),
	$form->fieldset($pageTitle);
?>
<ul class="nav nav-tabs">
	<li class="active"><a href="#form-organization-info" data-toggle="tab" id="navtab">Company Info</a></li>
	<li><a href="#form-organization-people" data-toggle="tab" id="navtab">People</a></li>
	<li><a href="#form-organization-notes" data-toggle="tab" id="navtab">Notes</a></li>
</ul>
<?php
echo '<div class="tab-content">',
	'<div class="tab-pane active" id="form-organization-info">',
	$form->field('name'),
	$form->field('customer_number'),
	$form->field('rep_ids', ['class' => 'chosen chosen-block']),
	//$form->field('parent_id', array('class' => 'chosen chosen-block')),
	$form->block('phone', null),
	'<div id="phoneone">',
	$form->field('phone', array('class' => 'phonepicker', 'label' => 'Switch Board'), true),' ',
	$form->field('phone_ext', array('prepend' => "EXT", 'class' => 'input-small'), true),
	$form->field('phone_country_code', array('class' => 'country_code', 'hide' => true)),
	'</div>',
	$form->endblock(),
		
	$form->field('website', array('description' => blank($model['website'])?'':"<a href='{$form->h($model->website_url())}' target='_blank'>Visit Website</a>" )),
	$form->block('fax', null),
	'<div id="phonethree">',
	$form->field('fax', array('class' => 'phonepicker'), true),' ',
	$form->field('fax_country_code', array('class' => 'country_code', 'hide' => true)),
	'</div>',
	$form->endblock(),
	'</div><div class="tab-pane" id="form-organization-people">';

?>
<div class="btn-toolbar">
	<div class="btn-group">
		<a href="<?= admin_url('User', true) ?>?organization_id=<?= $model['id'] ?>" class="btn btn-small"><?= icon('plus', 'Create Person')?></a>
	</div>
</div>
<table class="table table-striped">
	<thead>
		<tr>
			<th>Name</th>
			<th class="span3">Title</th>
			<th class="span3">Type</th>
			<th class="span3">Modified</th>
		</tr>
	</thead>
	<tbody>
<?php foreach(User::find([ 'where' => "`users`.`organization_id` = {$model['id']}", 'sort' => 'last_name ASC']) as $index => $user): ?>
		<tr>
			<td><a href="<?= h(admin_url('User', $user['id'])) ?>"><?= h($user->display('name')) ?></a></td>
			<td class="span3"><?= h($user->display('title')) ?></td>
			<td class="span3 type_id"><?= h($user->display('type_id')) ?></td>
			<td class="span3"><?= h($user->display('updated_at')) ?></td>
		</tr>
<?php endforeach ?>
	</tbody>
</table>
<?php

echo '</div><div class="tab-pane" id="form-organization-notes">';
	
		echo $form->field('notes', [ 'type' => 'textarea', 'rows' => 14 ], true);
	
		echo '</div>',
	'</div>',
	$form->submit(),
	$form->end();	