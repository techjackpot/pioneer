<?php
if (!defined('INDEX')) { require dirname(__FILE__).'/../index.php'; exit; }
if ($path[2] === 'delete') {
	require_permission('users_delete');
	$model = new User($path[3]);
	if (!$model['id']) error_404();
	
	if ($model->delete()) { success_message('Person was successfully deleted.'); }
	else { error_message('Sorry, we were not able to delete that person.'); }
	redirect_to( admin_url('users') );
}
else if ($path[2] === 'reset_password') {
	require_permission('users_edit');
	$model = new User($path[3]);
	if (!$model['id']) error_404();
	
	$model->send_password_reset();
	success_message('Password reset was sent.');
	
	if (is_json()) { json_response([ 'success' => true ]); }
	else if ($success) { redirect_to( admin_url('users', $user['id']) ); }
	
}
else {
	require_permission('users_edit');

	if ($path[2] === 'access') {
		$model = new User($path[3]);
		if (!$model['id']) error_404();
		unset($_SESSION['OID']);
		$_SESSION['AUID'] = $model['id'];
		redirect_to( '/guidelines' );
	}
	else if ($path[2] === 'edit') {
		$model = new User($path[3]);
		if (!$model['id']) error_404();
		$edit = true;
	}
	else {
		$model = new User();
		$edit = false;
	}

	$form = new FormHelper();
	$form->prefix = 'people_';
	$form->data = &$model;
	
	$validTypes = $model::$properties['type_id']['values'];
	
	if ($_POST) {
		$values = $form->apply_post('group_id', 'type_id', 'status_id', 'organization_id', 'username', 'first_name', 'last_name', 'title', 'password', 'password_confirmation', 'email', 'phone', 'phone_ext', 'phone_country_code', 'mobile', 'mobile_country_code', 'fax', 'fax_country_code');
		
		$model->validate_email('email');
		
		if ($edit) {
			$form->apply_post('notes');
		}
	
		if ( user_type() < User::SUPER_ADMIN && ($model['type_id'] >= user_type() || $model->_initial['type_id'] >= user_type()) && $model['type_id'] !== $model->_initial['type_id'] ) {
			$model->errors['type_id'][] = "you don't have permission to do that";
		}
		
		if ( user_type() < User::ADMIN && $model['group_id'] !== $model->_initial['group_id'] ) {
			$model->errors['group_id'][] = "you don't have permission to do that";
		}

		$model->validate_required('type_id', 'organization_id', 'status_id', 'username', 'first_name', 'last_name', 'email');
				
		$model->validate_uniqueness('users', null, 'username', 'email');
		$model->validate_inclusion(array_keys($validTypes), 'type_id');
		if (!$edit && $model['type_id'] >= User::STAFF) $model->validate_required('password');
		$model->validate_confirmation('password');
	
		//if (!$model->errors && !$membership->errors) {
		if (!$model->errors) {
			
			$model->hash_password();
		
			if ($success = $model->save()) {
				success_message('Person was successfully saved.');
			}
			else { error_message('Sorry, we were not able to save that person.'); }
			if (is_json()) { json_response([ 'success' => $success ]); }
		    else { redirect_to( admin_url('users') ); 
			} 
		}
		else if (is_json()) { json_response([ 'success' => false, 'errors' => $model->errors ]); }
	}

	$pageTitle = ['<a href="'.admin_url($model).'">People</a>'];
	if ($edit) { // Edit
		$pageTitle[] = h($model->name());
		$form->activity_wall = true;
	
		echo $form->start(array('class' => (is_modal() ? 'form-horizontal js-form' : null), 'modal_class' => 'modal-large', 'multipart' => true)),
			$form->fieldset($pageTitle);
	?>
	<ul class="nav nav-tabs">
		<li class="active"><a href="#form-people-info" data-toggle="tab" id="navtab">User Info</a></li>
		<li><a href="#form-people-notes" data-toggle="tab" id="navtab">Private Notes</a></li>
	</ul>
	<?php
		echo '<div class="tab-content">',
			'<div class="tab-pane active" id="form-people-info">',
			$form->field('type_id'),
			$form->field('group_id', array('class' => 'input-xlarge', 'disabled' => $model['type_id'] !== User::STAFF, 'hide' => $model['type_id'] !== User::STAFF)),
			$form->field('status_id'),
			$form->field('first_name', array('required' => true)),
			$form->field('last_name', array('required' => true)),
			$form->field('organization_id'),
			$form->field('title'),
			$form->field('username'),
			$form->password('password', array('validate' => 'confirmation', 'description' => '<a href="'.admin_url('users', 'reset_password', $model['id']).'" data-confirm="Are you sure you would like to send a password reset email to '.h($model->display('email')).' ?" data-remote="true">Password Reset</a>')),
			$form->password('password_confirmation', array('label' => 'Repeat Password')),
			$form->field('email', ['description' =>(!empty($model['email'])? '<a href="mailto:'.h($model->display('email')).'">Send Email</a>' : '')]),
			$form->block('phone', null),
			'<div id="phoneone">',
			$form->field('phone', array('class' => 'phonepicker'), true),' ',
			$form->field('phone_ext', array('prepend' => "EXT", 'class' => 'input-small'), true),
			$form->field('phone_country_code', array('class' => 'country_code', 'hide' => true)),
			'</div>',
			$form->endblock(),
			
			$form->block('mobile', null),
			'<div id="phonetwo">',
			$form->field('mobile', array('class' => 'phonepicker'), true),' ',
			$form->field('mobile_country_code', array('class' => 'country_code', 'hide' => true)),
			'</div>',
			$form->endblock(),
			
			$form->block('fax', null),
			'<div id="phonethree">',
			$form->field('fax', array('class' => 'phonepicker'), true),' ',
			$form->field('fax_country_code', array('class' => 'country_code', 'hide' => true)),
			'</div>',
			$form->endblock();

	
			echo '</div><div class="tab-pane" id="form-people-notes">';
	
		echo $form->field('notes', [ 'type' => 'textarea', 'rows' => 14 ], true);
	
		echo '</div>',
			'</div>',
			$form->submit(),
			$form->end();	
	}
	else { // Add
		$pageTitle[] = 'Add';
		echo $form->start(array('class' => (is_modal() ? 'form-narrow form-horizontal js-form' : null), 'modal_class' => 'modal-large', 'multipart' => true)),
			$form->fieldset($pageTitle),
			'<div class="row-fluid">',
			'<div class="span6">',
			$form->field('type_id'),
			$form->field('group_id', array('class' => 'input-xlarge', 'disabled' => true, 'hide' => true)),
			$form->field('status_id'),
			$form->field('first_name', array('required' => true)),
			$form->field('last_name', array('required' => true)),
			$form->field('organization_id'),
			$form->field('title'),
			$form->field('username', array('required' => true)),
			$form->password('password', array('validate' => 'confirmation')),
			$form->password('password_confirmation', array('label' => 'Repeat Password')),
			$form->field('email'),
			$form->block('phone', null),
			'<div id="phoneone">',
			$form->field('phone', array('class' => 'phonepicker'), true),' ',
			$form->field('phone_ext', array('prepend' => "EXT", 'class' => 'input-small'), true),
			$form->field('phone_country_code', array('class' => 'country_code', 'hide' => true)),
			'</div>',
			$form->endblock(),
			
			$form->block('mobile', null),
			'<div id="phonetwo">',
			$form->field('mobile', array('class' => 'phonepicker'), true),' ',
			$form->field('mobile_country_code', array('class' => 'country_code', 'hide' => true)),
			'</div>',
			$form->endblock(),
			
			$form->block('fax', null),
			'<div id="phonethree">',
			$form->field('fax', array('class' => 'phonepicker'), true),' ',
			$form->field('fax_country_code', array('class' => 'country_code', 'hide' => true)),
			'</div>',
			$form->endblock(),
			'</div></div>',
			$form->submit(),
			$form->end();	
	}
}