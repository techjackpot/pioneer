<?php
if (!defined('INDEX')) { require dirname(__FILE__).'/../index.php'; exit; }
if ($path[2] === 'delete') {
	require_permission('pages_delete');
	$model = new Page($path[3]);
	if (!$model['id']) error_404();
	
	if ($model->delete()) { success_message('Page was successfully deleted.'); }
	else { error_message('Sorry, we were not able to delete that page.'); }
	redirect_to( admin_url('pages') );
}

require_permission('pages_edit');

if ($path[2] === 'edit') {
	$model = new Page($path[3]);
	if (!$model['id']) error_404();
	$edit = true;
}
else {
	$model = new Page();
	$edit = false;
}

$form = new FormHelper();
$form->prefix = 'page_';
$form->data = &$model;

if ($_POST) {
	//var_dump($_POST); die();
	$values = $form->apply_post('name', 'menu_title', 'content', 'parent_id', 'show_menu', 'sort_id', 'meta_title', 'meta_description', 'meta_keywords', 'uri', 'external_link');
	
	$model->validate_required('name', 'uri');
	
	$model->validate_valid_file('image');
	
	if (!$model->errors) {
		$form->process_files('image');
		
		if ($success = $model->save()) { success_message('Page was successfully saved.'); }
		else { error_message('Sorry, we were not able to save that page.'); }
		if (is_json()) { json_response([ 'success' => $success ]); }
		else if ($success) { redirect_to( admin_url('pages') ); }
	}
	else if (is_json()) { json_response([ 'success' => false, 'errors' => $model->errors ]); }
	
}

$pageTitle = ['<a href="'.admin_url($model).'">Pages</a>', $edit ? h($model->name()) : 'Add'];
$form->activity_wall = $edit;

echo $form->start(array('class' => (is_modal() ? 'form-narrow form-horizontal js-form' : null), 'modal_class' => 'modal-large', 'multipart' => true)),
	$form->fieldset($pageTitle);
	
if (!$edit) echo '<div class="row-fluid"><div class="span6">';
echo $form->field('name'),
	$form->field('menu_title'),
	$form->field('show_menu'),
	$form->field('content', ['class' => 'ckeditor']),
	$form->field('image', [ 'type' => 'file']),
	'<div style="margin:-20px 0 20px 180px;"><small>Ideal Image Size: 429px by 519px</small></div>',
	$form->field('parent_id', ['class' => 'chosen chosen-block']),
	$form->field('sort_id', ['class' => 'input-small']),
	$form->field('meta_title'),
	$form->field('meta_description'),
	$form->field('meta_keywords'),
	$form->field('uri'),
	$form->field('external_link');
	
if (!$edit) echo '</div></div>';
echo $form->submit(),
	$form->end();	
