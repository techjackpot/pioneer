<?php
if (!defined('INDEX')) { require dirname(__FILE__).'/../index.php'; exit; }

$identifier = "order";
if ($path[2] === 'delete') {
	require_permission($identifier.'_delete');
	$model = new Order($path[3]);
	if (!$model['id']) error_404();
	
	if ($model->delete()) { success_message(ucfirst($identifier).' was successfully deleted.'); }
	else { error_message('Sorry, we were not able to delete that '.$identifier.'.'); }
	redirect_to( admin_url($identifier.'s') );
}
else {
	require_permission($identifier.'_edit');

	if ($path[2] === 'edit') {
		$model = new Order($path[3]);
		if (!$model['id']) error_404();
		$edit = true;
		$_SESSION['AUID'] = $model['user_id'];
		$_SESSION['OID'] = $model['id'];
		redirect_to( '/revieworder' );
	}
	else {
		$model = new Order();
		$edit = false;
	}

}