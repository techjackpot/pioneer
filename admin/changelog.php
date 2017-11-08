<?php
if (!defined('INDEX')) { require dirname(__FILE__).'/../index.php'; exit; }

if ($path[2] === 'comment') {
	$model_name = model_from_table($_POST['name']);
	if ($model_name && class_exists($model_name)) {
		$model = $model_name::get($_POST['id']);
		if (!$model) { error_404(); }
		
		$c_id = ChangeLog::comment($model, unslash($_POST['message']));
		$model->after_comment();
		
		if (is_json()) {
			if ($c_id) {
				json_response([ 'success' => true, 'contents' => ChangeLog::display_item($c_id) ]);
			}
			else {
				json_response([ 'success' => false ]);
			}
		}
		else {
			redirect_to('/admin/');
		}
	}
}
else {
	$ignore_classes = "'Notification','InvoiceTransaction','Membership','Attendee'";

	$per_page = 20;
	$page = (int) $_GET['page'] or $page = 1;
	
	if (blank($path[2])) {
		$pageTitle = 'Change Log';
		$query = sql("SELECT changelog.*, users.username FROM changelog LEFT JOIN users ON changelog.user_id = users.id WHERE model_class NOT IN ($ignore_classes) ORDER BY updated_at DESC LIMIT ".(($page-1)*$per_page).", $per_page");
	}
	else {
		$model_class = model_from_table($path[2]);
		if (!$model_class) error_404();
	
		if (blank($path[3])) {
			$pageTitle = 'Change Log for '.$model_class::human_names();
	
			$query = sql("SELECT changelog.*, users.username FROM changelog LEFT JOIN users ON changelog.user_id = users.id WHERE model_class = %s ORDER BY updated_at DESC LIMIT ".(($page-1)*$per_page).", $per_page", $model_class);
		}
		else {
			$model_id = (int) $path[3];
			$model = $model_class::get($model_id);
			$pageTitle = 'Change Log for '.$model_class::human_name().($model ? ": ".h($model->name()) : ' #'.$model_id);
	
			$query = sql("SELECT changelog.*, users.username FROM changelog LEFT JOIN users ON changelog.user_id = users.id WHERE model_class = %s AND model_id = %s ORDER BY updated_at DESC LIMIT ".(($page-1)*$per_page).", $per_page", $model_class, $model_id);
		}
	}
?>
<?php if(!is_paginator()): ?>
		<h2 class="page-title"><?= $pageTitle ?></h2>
		<table class="table table-striped data-table changelog">
			<thead>
				<tr>
					<th class="span4">Change</th>
					<th>State</th>
					<th class="span3">Modified</th>
				</tr>
			</thead>
			<tbody>
<?php endif; ?>
<?php
while($row = $query->fetch_assoc()) {
	echo ChangeLog::output_item( $row );
}
?>
<?php if(!is_paginator()): ?>
				<tr class="spacer"><td colspan="3"></td></tr>
			</tbody>
		</table>
		<?php if ($query->num_rows >= $per_page): ?><p class="js-paginator text-center"><a href="#" class="load-more">Scroll to load more</a></p><?php endif ?>
<?php endif; ?>
<?php
}