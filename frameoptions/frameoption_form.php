<?php
if (!defined('INDEX')) { require dirname(__FILE__).'/../index.php'; exit; }

$identifier = "frameoption";
if ($path[2] === 'delete') {
	require_permission($identifier.'_delete');
	$model = new FrameOption($path[3]);
	if (!$model['id']) error_404();
	
	if ($model->delete()) { success_message(ucfirst($identifier).' was successfully deleted.'); }
	else { error_message('Sorry, we were not able to delete that '.$identifier.'.'); }
	redirect_to( admin_url($identifier.'s') );
}
else {
	require_permission($identifier.'_edit');

	if ($path[2] === 'edit') {
		$model = new FrameOption($path[3]);
		if (!$model['id']) error_404();
		$edit = true;
	}
	else {
		$model = new FrameOption();
		$edit = false;
	}

	$form = new FormHelper();
	$form->prefix = $identifier.'_';
	$form->data = &$model;
	
	$validTypes = $model::$properties['type_id']['values'];
	
	if ($_POST) {
		$values = $form->apply_post('name');
	
		$model->validate_required('name');
		
		if (!$model->errors) {
			
			if ($success = $model->save()) {
				success_message(ucfirst($identifier).' was successfully saved.');
			}
			else { error_message('Sorry, we were not able to save that '.$identifier.'.'); }
			if (is_json()) { json_response([ 'success' => $success ]); }
		    else { redirect_to( admin_url($identifier.'s') ); 
			} 
		}
		else if (is_json()) { json_response([ 'success' => false, 'errors' => $model->errors ]); }
	}

	$pageTitle = ['<a href="'.admin_url($model).'">'.ucfirst($identifier).'</a>'];
	if ($edit) { // Edit
		$pageTitle[] = h($model->name());
		$form->activity_wall = true;
	
		echo $form->start(array('class' => (is_modal() ? 'form-horizontal js-form' : null), 'modal_class' => 'modal-large', 'multipart' => true)),
			$form->fieldset($pageTitle);
	?>
	<?php
		echo $form->field('name'),
		$form->submit(),
		$form->end();	
	}
	else { // Add
		$pageTitle[] = 'Add';
		echo $form->start(array('class' => (is_modal() ? 'form-narrow form-horizontal js-form' : null), 'modal_class' => 'modal-large', 'multipart' => true)),
		$form->fieldset($pageTitle),
		$form->field('name'),
		$form->submit(),
		$form->end();	
	}
}