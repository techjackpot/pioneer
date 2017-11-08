<?php
if (!defined('INDEX')) { require dirname(__FILE__).'/../index.php'; exit; }

if (!is_logged())
redirect_to( '/' );

$model = Order::first("uniqueid = ".sanitize($path[1]));
$user = User::get($model['user_id']);

if ($model)
{
	$form = new FormHelper();
	$form->prefix = 'order_';
	$form->data = &$model;

	$table = new TableHelper('OrderLine');
	$params = $table->find_params();
	$params['where'] = '`order_id` = '.sanitize($model['id']);
	$params['sort'] = 'created_at ASC'; 
	$data = OrderLine::find( $params );
}
else
{
	error_message('No order found.');
	redirect_to( '/' );
}

if ($path[0] == "finalizeorder") {    
	$model->validate_required('billto_name', 'billto_address1', 'billto_city', 'billto_state', 'billto_zip', 'shipto_name', 'shipto_address1', 'shipto_city', 'shipto_state', 'shipto_zip', 'shipping_code');
    
    if (!$model->errors) {
		
		$model['uniqueid'] = sha1(md5($model['id'].$model['user_id'].strtotime('today GMT')));
        $model['status'] = "Pending";
        
        if ($success = $model->save()) {
            success_message('Your order has been successfully submitted. Thank You.');
            $user['billto_name'] = $model->display('billto_name');
            $user['billto_address1'] = $model->display('billto_address1');
            $user['billto_address2'] = $model->display('billto_address2');
            $user['billto_city'] = $model->display('billto_city');
            $user['billto_state'] = $model->display('billto_state');
            $user['billto_zip'] = $model->display('billto_zip');
            $user->save();
        }
        else { error_message('Sorry, we were not able to submit your order at this time. Please try again later.');  }
        if (is_json()) { json_response([ 'success' => $success ]); }
        else { redirect_to( '/guidelines');
        } 
    }
    else if (is_json()) { json_response([ 'success' => false, 'errors' => $model->errors ]); }
    else { error_message('There were errors submitting your order. Please see below.'); }
}

?>
<div class="margins">
    <div class="qsrightcol">
        <div class="qscontainer middlealign">
            <?php quickship_order_summary($model); ?>
        </div>
    </div>
    <div class="qsleftcol">
        <div class="qscontainer redstripbottom">
            <? quickship_navigation($path); ?>
            <div class="qsintrotext">
                <h1>Thank You for Your Order</h1>
                <!--<p>Once an order is submitted, it CANNOT be changed. <span class="darkblue">Click Finalize Order</span> once you are satisfied with the order details below.</p>-->
            </div>
            <div class="padding">
                <div class="twoleftcol">
                    <h2>Bill To:</h2>
                    <?php echo $model->billto(); ?>
                </div>
                <div class="tworightcol">
                    <h2>Ship To:</h2>
                    <?php echo $model->billto(); ?>
                </div>
                <p>&nbsp;</p>
                <p>&nbsp;</p>
                <p>&nbsp;</p>
                <div class="clear"></div>
				<?php echo "<b>".$form->label_from_name('shipping_code').":</b><br>".$model->display('shipping_code')."<br><br>";
				echo "<b>".$form->label_from_name('shipping_notes').":</b><br>".$model->display('shipping_notes')."<br><br><br>";
				?>
                <?php if(!is_paginator()): ?>
                <div class="data-table">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Line</th>
                                <th>Item</th>
                                <th>Quantity</th>
                            </tr>
                        </thead>
                        <tbody>
                    <?php endif; ?>
                <?php foreach($data as $index => $model2):
                $line = $index+1;
                ?>
					<tr>
						<td><?= h($line) ?></td>
						<td><?= $model2->display('item') ?></td>
						<td><?= h($model2->display('quantity')) ?></td>
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
                <div class="glbottom">&nbsp;</div>
            </div>

        </div>
        <div class="clear"></div>
    </div>
</div>