<?php
if (!defined('INDEX')) { require dirname(__FILE__).'/../index.php'; exit; }

$identifier = "frameoptionvalue";
if ($path[2] === 'deletevalue') {
	require_permission($identifier.'_delete');
	$model = new FrameOptionValue($path[3]);
	if (!$model['id']) error_404();
	
	if ($model->delete()) { success_message(ucfirst($identifier).' was successfully deleted.'); }
	else { error_message('Sorry, we were not able to delete that '.$identifier.'.'); }
	 redirect_to( admin_url('frameoptions').'/values/'.$model['frameoption_id'] );
}
else {
	require_permission($identifier.'_edit');

	if ($path[2] === 'editvalue') {
		$model = new FrameOptionValue($path[3]);
		if (!$model['id']) error_404();
		$edit = true;
		$frameoption = FrameOption::get($model['frameoption_id']);
		
		$opvaluessort = FrameOptionValue::first("`frameoption_id` = ".sanitize($model['frameoption_id'])."", ['sort' => 'sort_order DESC']);
	}
	else {
		$model = new FrameOptionValue();
		$edit = false;
		$frameoption = FrameOption::get($path[3]);
		
		$opvaluessort = FrameOptionValue::first("`frameoption_id` = ".sanitize($path[3])."", ['sort' => 'sort_order DESC']);
	}
	
	$maxsort = (int) $opvaluessort->data['sort_order'];
	
	$form = new FormHelper();
	$form->prefix = $identifier.'_';
	$form->data = &$model;
	
	if ($_POST) {
		$values = $form->apply_post('name', 'abbreviation', 'frameoption_id', 'frameproduct_ids', 'addcomponents', 'sort_order');
	
		$model->validate_required('name', 'abbreviation', 'frameoption_id');
		
		if (!$model->errors) {
			
			if ($success = $model->save()) {
				success_message(ucfirst($identifier).' was successfully saved.');
			}
			else { error_message('Sorry, we were not able to save that '.$identifier.'.'); }
			if (is_json()) { json_response([ 'success' => $success ]); }
		    else { redirect_to( admin_url('frameoptions').'/values/'.$model['frameoption_id'] ); 
			} 
		}
		else if (is_json()) { json_response([ 'success' => false, 'errors' => $model->errors ]); }
	}

	$pageTitle = ['<a href="'.admin_url('frameoptions').'/values/'.$frameoption['id'].'">Frame Option Values ('.$frameoption->display('name').')</a>'];
	if ($edit) { // Edit
		$pageTitle[] = h($model->name());
		$form->activity_wall = true;
	
		echo $form->start(array('class' => (is_modal() ? 'form-horizontal js-form' : null), 'modal_class' => 'modal-large', 'multipart' => true)),
			$form->fieldset($pageTitle);
	?>
	<?php
		echo $form->field('abbreviation'),
		$form->field('name'),
		$form->field('frameproduct_ids', array('class' => 'chosen chosen-block', 'multiple' => true)),
		$form->field('frameoption_id',['default' => $path[3]]),
		$form->field('addcomponents'),
		$form->field('sort_order', ['value' => (empty($model['sort_order']) ? $maxsort+1 : $model->display('sort_order'))]),
		$form->submit(),
		$form->end();	
	}
	else { // Add
		$pageTitle[] = 'Add';
		echo $form->start(array('class' => (is_modal() ? 'form-narrow form-horizontal js-form' : null), 'modal_class' => 'modal-large', 'multipart' => true)),
		$form->fieldset($pageTitle),
		$form->field('abbreviation'),
		$form->field('name'),
		$form->field('frameproduct_ids', array('class' => 'chosen chosen-block', 'multiple' => true)),
		$form->field('frameoption_id',['default' => $path[3]]),
		$form->field('addcomponents'),
		$form->field('sort_order', ['value' => $maxsort+1]),
		$form->submit(),
		$form->end();	
	}
}