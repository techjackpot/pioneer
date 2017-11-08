<?php
if (!defined('INDEX')) { require dirname(__FILE__).'/../index.php'; exit; }

$identifier = "order";

require_permission($identifier.'_view');

if ($path[2] == "markprocessed")
{
	$order = Order::get($path[3]);
	if ($order)
	{
		$order['status'] = "Processed";
		if ($order->save())
		{
			success_message('Order # QS'.$order->display('id') .' status changed to Processed.');
			redirect_to( admin_url('orders') );
		}
	}
	else
	{
		error_message('Order not found.');
		redirect_to( admin_url('orders') );
	}
} else if ($path[2] == "markpending")
{
	$order = Order::get($path[3]);
	if ($order)
	{
		$order['status'] = "Pending";
		if ($order->save())
		{
			success_message('Order # QS'.$order->display('id') .' status changed to Pending.');
			redirect_to( admin_url('orders') );
		}
	}
	else
	{
		error_message('Order not found.');
		redirect_to( admin_url('orders') );
	}
}

$table = new TableHelper(ucfirst($identifier));
$table->filters = array('search');

$params['where'] = '`status` != "With Customer"';

$data = Order::find( $table->find_params() + $params );
$pageTitle = "Orders";
	
?>
<?php if(!is_paginator()): ?>
<div class="table-header">
	<?= display_title($pageTitle) ?>
	<!--<p>
		<a class="btn" href="<?= admin_url($identifier.'s', true) ?>"><?=icon('plus')?> Add Frame Product</a>
	</p>-->
	<?php $table->filters() ?>
</div>
<div class="activity-wall-wrap">
	<div class="activity-wall-title"><?= $table->activity_wall_title() ?></div>
	<div class="activity-wall"><?php ChangeLog::display_model_all($table->model) ?></div>
</div>
<div class="data-table">
	<table class="table table-striped">
		<thead>
			<tr>
				<th><?= $table->header('id') ?></th>
				<th><?= $table->header('customer_po') ?></th>
				<th><?= $table->header('billto_name') ?></th>
				<th><?= $table->header('shipto_name') ?></th>
				<th><?= $table->header('shipping_code') ?></th>
				<th><?= $table->header('status') ?></th>
				<th class="span3"><?= $table->header('dateprocessed') ?></th>
				<th class="span3"><?= $table->header('created_at') ?></th>
				<th class="span3"><?= $table->header('updated_at') ?></th>
				<th class="actions"></th>
			</tr>
		</thead>
		<tbody>
	<?php endif; ?>
<?php foreach($data as $index => $model): ?>
			<tr>
				<td><a href="<?=admin_url($identifier.'s')?>/edit/<?=$model['id']?>"><?= h($model->display('id')) ?></a></td>
				<td><?= h($model->display('customer_po')) ?></td>
				<td><?= h($model->display('billto_name')) ?></td>
				<td><?= h($model->display('shipto_name')) ?></td>
				<td><?= h($model->display('shipping_code')) ?></td>
				<td><?= h($model['status']) ?></td>
				<td class="span3"><?= h($model->display('dateprocessed')) ?></td>
				<td class="span3"><?= h($model->display('created_at')) ?></td>
				<td class="span3"><?= h($model->display('updated_at')) ?></td>
				<td class="actions"><?= $table->actions($model, 'edit', ($model['status'] != "With Customer" ? '<a href="/orderconfirmation/'.$model['uniqueid'].'" target="_blank"><i class="icon-file"></i> Order Conf</a>' : ''), ($model['status'] != "With Customer" ? '<a href="/orderform/'.$model['uniqueid'].'" target="_blank"><i class="icon-file"></i> Order Form</a>' : ''), ($model['status'] == "Pending" ? '<a href="/admin/orders/markprocessed/'.$model['id'].'"><i class="icon-ok-sign"></i> Mark Processed</a>' : '<a href="/admin/orders/markpending/'.$model['id'].'"><i class="icon-question-sign"></i> Mark Pending</a>')) ?></td>
			</tr>
<?php endforeach ?>
<?php if(!is_paginator()): ?>
<?php $table->no_records_message($data->total(), 7) ?>
			<tr class="spacer"><td colspan="7"></td></tr>
		</tbody>
	</table>
</div>
<?= $table->ajax_pagination( $data->total() ) ?>
<?php endif; ?>
<?php
