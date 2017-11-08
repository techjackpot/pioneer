<?php
if (!defined('INDEX')) { require dirname(__FILE__).'/../index.php'; exit; }
if ($path[2] === 'delete') {
	require_permission('organizations_delete');
	$model = new Organization($path[3]);
	if (!$model['id']) error_404();
	
	if ($model->delete()) { success_message('Organization was successfully deleted.'); }
	else { error_message('Sorry, we were not able to delete that organization.'); }
	redirect_to( admin_url('organizations') );
}
else if ($path[2] === 'edit') {
	require 'organization_edit.php';
}
else {
	require_permission('organizations_edit');

	$form = new FormHelper();
	$model = new Organization();

	$form->prefix = 'organization_';
	$form->data = &$model;

	if ($_POST) {
		$values = $form->apply_post('name', 'parent_id', 'phone', 'phone_ext', 'phone_country_code', 'fax', 'fax_country_code', 'website', 'customer_number', 'rep_ids');
	
		$model->validate_required('name', 'customer_number');

		if (!$model->errors) {
			if ($success = $model->save()) {
				success_message('Organization was successfully saved.');
			}
			else { error_message('Sorry, we were not able to save that organization.'); }
			if (is_json()) { json_response([ 'success' => $success ]); }
			else if ($success) { redirect_to( admin_url('organizations', $model['id']) ); }
		}
		else if (is_json()) { json_response([ 'success' => false, 'errors' => $model->errors ]); }

	}


	$pageTitle = ['<a href="'.admin_url($model).'">Organizations</a>', 'Add'];
	echo $form->start(array('class' => (is_modal() ? 'form-narrow form-horizontal js-form' : null), 'modal_class' => 'modal-large', 'multipart' => true)),
		$form->fieldset($pageTitle),
		$form->field('name'),
		$form->field('customer_number'),
		$form->field('rep_ids', ['class' => 'chosen chosen-block']),
		//$form->field('parent_id', array('class' => 'chosen chosen-block')),
		$form->block('phone', null),
		'<div id="phoneone">',
		$form->field('phone', array('class' => 'phonepicker', 'label' => 'Switch Board'), true),' ',
		$form->field('phone_ext', array('prepend' => "EXT", 'class' => 'input-small'), true),
		'<span id="valid-msg" class="hide">✓ Valid</span><span id="error-msg" class="hide">Invalid number</span>',
		$form->field('phone_country_code', array('class' => 'country_code', 'hide' => true)),
		'</div>',
		$form->endblock(),
		$form->field('website'),	
		$form->block('fax', null),
		'<div id="phonethree">',
		$form->field('fax', array('class' => 'phonepicker'), true),' ',
		$form->field('fax_country_code', array('class' => 'country_code', 'hide' => true)),
		'</div>',
		$form->endblock(),	
		$form->submit(),
		$form->end();	
}