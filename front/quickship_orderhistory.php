<?php
if (!defined('INDEX')) { require dirname(__FILE__).'/../index.php'; exit; }

if (!is_logged())
redirect_to( '/?return='.$_SERVER['REQUEST_URI'] );

$model = Order::get($_SESSION['OID']);

if ($_SESSION['AUID'] && (user_type() > User::CUSTOMER))
{
	$userid = $_SESSION['AUID'];
}
else
{
	$userid = user_id();
}

if ($path[0] == "copyorder")
{
	
	if (!empty($model['id']))
    {
        if ($success = $model->delete()) {
            unset($_SESSION['OID']);
        }
    }
	
	$copyorder = Order::first(['id' => $path[1], 'user_id' => $userid]);
	if ($copyorder)
	{
		$neworder = new Order();
		foreach ($copyorder as $id=>$val)
		{
			if ($id == "id" || $id == "uniqueid" || $id == "status" || $id == "customer_po" || $id == "first_name" || $id == "last_name" || $id == "organization_id" || $id == "product_name" || $id == "turnaround_days" || $id == "quantity_limit")
			{ }
			else {
				$neworder[$id] = $val;
			}
		}
		
		$neworder['status'] = "With Customer";
		
		$neworder->save();
		
		if ($neworder['id'])
		{
			$copylines = OrderLine::find(['where' => ['order_id' => $path[1]]]);
			if ($copylines)
			{
				foreach($copylines as $index => $newline)
				{
					$lineadd = new OrderLine();
					foreach ($newline as $id=>$val)
					{
						if ($id == "id" || $id == "order_id" || $id == "frameproduct_id" || $id == "add")
						{ }
						else {
							$lineadd[$id] = $val;
						}
					}
					
					$lineadd['order_id'] = $neworder['id'];
					$lineadd->save();
				}
				
			}
		}
		
		$_SESSION['OID'] = $neworder['id'];
		success_message('This order has been copied and you may now modify it and/or click Finalize to submit as a new order.');
		redirect_to( '/revieworder' );
	}
	else
	{
		error_message('This order could not be copied. Please try again.');
		redirect_to( '/orderhistory' );
	}
	
}

$table = new TableHelper('Order');
$params = $table->find_params();

$params['where'] = '`user_id` = '.sanitize($userid).' AND (`status` = "Processed" OR `status` = "Pending")';

$params['sort'] = 'dateprocessed DESC'; 
$data = Order::find( $params );

?>
<div class="margins">
	<?php if ($path[0] != "orderconfirmation") { ?>
    <div class="qsrightcol">
        <div class="qscontainer middlealign">
            <?php
				quickship_order_summary($model);
			?>
        </div>
		<?php important_notice() ?>
    </div>
	<?php } ?>
    <div class="qsleftcol">
        <div class="qscontainer redstripbottom">
            <? quickship_navigation($path); ?>
            <div class="qsintrotext">
                <h1>Order History</h1>
                <p>Below is a summary of your order history. If you would like to re-order or start a new order using a previous order as a template, please click the copy button next to that order below. Only Processed orders can be copied.</p>
            </div>
            <div class="padding">
                <?php if(!is_paginator()): ?>
                <div class="data-table reviewordergrid">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>#</th>
								<th>Status</th>
                                <th>Customer PO #</th>
								<th>Components</th>
                                <th>Date</th>
                                <th class="actions span3"></th>
                            </tr>
                        </thead>
                        <tbody>
                    <?php endif; ?>
                <?php foreach($data as $index => $model2):
                $line = $index+1;
                ?>
					<tr>
						<td>QS<?= h($model2->display('id')) ?></td>
						<td><?= h($model2->display('status')) ?></td>
						<td><?= h($model2->display('customer_po')) ?></td>
						<td><?= h($model2->cart_quantity()) ?></td>
						<td><?= h($model2->display_dateprocessed()) ?></td>
						<td class="actions span3">
						<a href="/copyorder/<?=h($model2['id'])?>" class="btn btn-mini btn-success" data-confirm="Are you sure you would like to copy this and start a new order? If you have a current order you are working on it will be cancelled."><i class="icon-white icon-share"></i> Copy</a>
						<a href="/orderconfirmation/<?=h($model2['uniqueid'])?>" class="btn btn-mini btn-info" target="_blank"><i class="icon-white icon-list"></i> Conf.</a>
						<a href="/orderform/<?=h($model2['uniqueid'])?>" class="btn btn-mini btn-info" target="_blank"><i class="icon-white icon-file"></i> Form</a>
						</td>
					</tr>
                <?php endforeach; ?>
                <?php if(!is_paginator()): ?>
                <?php $table->no_records_message($data->total(), 7) ?>
                            <tr class="spacer"><td colspan="7"></td></tr>
                        </tbody>
                    </table>
                </div>
                <?= $table->ajax_pagination( $data->total() ) ?>
                <?php endif; ?>
				<? if ($path[0] == "orderconfirmation") { ?>
					<p><a href="/orderform/<?=$model->display('uniqueid')?>" class="btn btn-blue submitbtn" target="_blank" style="width: 300px;"><i class="icon-white icon-list-alt"></i> &nbsp;View / Print Pioneer Order Form</a></p>
				<?php } ?>
                <div class="glbottom">&nbsp;</div>
            </div>
        </div>
        <div class="clear"></div>
    </div>
</div>