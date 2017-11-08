<?php
if (!defined('INDEX')) { require dirname(__FILE__).'/../index.php'; exit; }


$ignore_classes = "'Notification','InvoiceTransaction','Membership','Attendee'";
	
$per_page = 20;
$page = (int) $_GET['page'] or $page = 1;

if (blank($path[2])) {
	$pageTitle = 'Dashboard';
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
		<div class="row-fluid">
			<div class="span6">
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
<?php
while($row = $query->fetch_assoc()) {
	ChangeLog::output_row( $row );
}
?>
						<tr class="spacer"><td colspan="6"></td></tr>
					</tbody>
				</table>
				<?php if ($query->num_rows >= $per_page): ?><p class="text-center"><a href="<?= url('admin', 'changelog') ?>" class="load-more">View more</a></p><?php endif ?>
			</div>
			<div class="span6">
				<h3 class="page-title">Notifications</h3>
				<table class="table table-striped data-table notifications">
					<thead>
						<tr>
							<th>Item</th>
							<th>Notification</th>
							<th class="span3">At</th>
						</tr>
					</thead>
					<tbody>
					<?php foreach(Notification::recent_for_user() as $notification): ?>
						<tr>
							<td><a href="<?=h( admin_url($notification['model_class'], $notification['model_id'])  )?>"><?=h( $notification->model->name() )?></a></td>
							<td><?=h( $notification->display('text') )?></td>
							<td><?=h( $notification->display('created_at') )?></td>
						</tr>
					<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		</div>
<?php
