<?php
if (!defined('INDEX')) { require dirname(__FILE__).'/../index.php'; exit; }
if ($path[2] === 'delete') {
	require_permission('files_delete');
	$model = new File($path[3]);
	if (!$model['id']) error_404();
	
	if ($model->delete()) { success_message('File was successfully deleted.'); }
	else { error_message('Sorry, we were not able to delete that file.'); }
	redirect_to( admin_url('files') );
}

require_permission('files_edit');

if ($path[2] === 'edit') {
	$model = new File($path[3]);
	if (!$model['id']) error_404();
	$edit = true;
}
else {
	$model = new File();
	$edit = false;
}

$form = new FormHelper();
$form->prefix = 'file_';
$form->data = &$model;

if ($_POST) {
	//var_dump($_POST); die();
	$values = $form->apply_post('name','content', 'sort_id');
	
	$model->validate_required('name');
	
	$model->validate_valid_file('file');
	
	if (!$model->errors) {
		$form->process_files('file');
		
		if ($success = $model->save()) { success_message('File was successfully saved.'); }
		else { error_message('Sorry, we were not able to save that file.'); }
		if (is_json()) { json_response([ 'success' => $success ]); }
		else if ($success) { redirect_to( admin_url('files') ); }
	}
	else if (is_json()) { json_response([ 'success' => false, 'errors' => $model->errors ]); }
	
}

$fileTitle = ['<a href="'.admin_url($model).'">Files</a>', $edit ? h($model->name()) : 'Add'];
$form->activity_wall = $edit;

echo $form->start(array('class' => (is_modal() ? 'form-narrow form-horizontal js-form' : null), 'modal_class' => 'modal-large', 'multipart' => true)),
	$form->fieldset($fileTitle);
	
if (!$edit) echo '<div class="row-fluid"><div class="span6">';
echo $form->field('file', [ 'type' => 'file']),
	$form->field('name'),
	$form->field('content'),
	$form->field('sort_id', ['class' => 'input-small']);
	
if (!$edit) echo '</div></div>';
echo $form->submit(),
	$form->end();	
