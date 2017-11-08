<?php
if (!defined('INDEX')) { require dirname(__FILE__).'/../index.php'; exit; }

if (!is_logged())
redirect_to( '/?return='.$_SERVER['REQUEST_URI'] );

if (!empty($_SESSION['OID']) || ($path[0] == "orderconfirmation" && !empty($path[1])))
{
	if ($path[0] == "orderconfirmation")
	{
		$model = Order::first("uniqueid = ".sanitize($path[1]));
		$user = User::get($model['user_id']);
	}
	else
	{
		$model = Order::get($_SESSION['OID']);
		if ($_SESSION['AUID'] && (user_type() > User::CUSTOMER))
		{
			$user = User::get($_SESSION['AUID']);
		}
		else
		{
			$user = User::get(user_id());
		}
	}
	
    if ($model)
    {
        
        if ($path[0] == "deleteframe")
        {
            $modeldel = new OrderLine($path[1]);
            if (!$modeldel['id']) { error_message('It looks like this line has already been deleted or no longer exists.'); }
            else {
                if ($modeldel->delete()) { success_message('The line was successfully deleted.'); }
                else { error_message('Sorry, we were not able to delete that line. Please try again later.'); }
            }
            redirect_to( '/revieworder' );
        }
        
        $form = new FormHelper();
        $form->prefix = 'order_';
        $form->data = &$model;

        $table = new TableHelper('OrderLine');
        $params = $table->find_params();
        $params['where'] = '`order_id` = '.sanitize($model['id']);
        $params['sort'] = 'created_at ASC'; 
        $data = OrderLine::find( $params );
		
		if (empty($model['billto_name']))
		$model['billto_name'] = $user['billto_name'];
		if (empty($model['billto_address1']))
		$model['billto_address1'] = $user['billto_address1'];
		if (empty($model['billto_address2']))
		$model['billto_address2'] = $user['billto_address2'];
		if (empty($model['billto_city']))
		$model['billto_city'] = $user['billto_city'];
		if (empty($model['billto_state']))
		$model['billto_state'] = $user['billto_state'];
		if (empty($model['billto_zip']))
		$model['billto_zip'] = $user['billto_zip'];
		
    }
    else
    {
        error_message('You must start a new order first.');
        redirect_to( '/guidelines' );
    }
   
}
else
{
    error_message('You must start a new order first.');
    redirect_to( '/guidelines' );
}

if ($path[0] == "finalizeorder" && $model['status'] == "With Customer") {
	
	$model->validate_required('billto_name', 'billto_address1', 'billto_city', 'billto_state', 'billto_zip', 'shipto_name', 'shipto_address1', 'shipto_city', 'shipto_state', 'shipto_zip', 'customer_po', 'shipping_code');
    $qtylimit = $model->display("quantity_limit");
	$qtycart = $model->cart_quantity();
	
	if ($qtycart > $qtylimit)
	{
	error_message('Sorry, but you have reached your limit of '.$qtylimit.' components for this order. Please remove or change lines.');
	redirect_to( '/revieworder' );
	}
	
    if (!$model->errors) {
				
		$model['uniqueid'] = sha1(md5($model['id'].$model['user_id'].strtotime('today GMT')));
        $model['status'] = "Pending";
		$model['dateprocessed'] = format_dt(time(), 'Y-m-d H:i:s');
        
        if ($success = $model->save()) {
            success_message('Your order has been successfully submitted. Thank You.');
            $user['billto_name'] = $model->display('billto_name');
            $user['billto_address1'] = $model->display('billto_address1');
            $user['billto_address2'] = $model->display('billto_address2');
            $user['billto_city'] = $model->display('billto_city');
            $user['billto_state'] = $model->display('billto_state');
            $user['billto_zip'] = $model->display('billto_zip');
            $user->save();
			
			if ($user['email'])
			$model->emailconfirmation();
			
			unset($_SESSION['OID']);
			redirect_to( '/orderconfirmation/'.$model['uniqueid']);
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
	<?php if ($path[0] != "orderconfirmation") { ?>
    <div class="qsrightcol">
        <div class="qscontainer middlealign">
            <?php
				quickship_order_summary($model);
			?>
        </div>
		<?php important_notice() ?>
    </div>
	<?php }

	?>
    <div class="qsleftcol">
        <div class="qscontainer redstripbottom">
            <? quickship_navigation($path); ?>
            <div class="qsintrotext">
                <h1><?=($path[0] == "orderconfirmation" ? 'Order Confirmation' : 'Please Review Your Order')?></h1>
                <p><? if ($path[0] == "orderconfirmation") { ?>Thank you for your order, we will review your order shortly and email you once it is finalized.<?php } else { ?>Once an order is submitted, it CANNOT be changed. <span class="darkblue">Click Finalize Order</span> once you are satisfied with the order details below.<?php } ?></p>
            </div>
            <div class="padding">
                <?php echo $form->start(array('class' => (is_modal() ? 'form-horizontal js-form' : null), 'modal_class' => 'modal-large', 'multipart' => true, 'context' => SHA1(MD5($model['id'])), 'contexttwo' => SHA1(MD5($model['organization_id'])) )); ?>
                <div class="twoleftcol">
                    <h2>Bill To:</h2>
                    <?php
					if ($path[0] == "orderconfirmation") {
						echo $model->billto();
					} else {
						echo $form->field('billto_name', ['placeholder' => "Name", 'class' => "addspacing savetype", 'value' => (empty($model['billto_name']) ? $user->display('billto_name') : $model->display('billto_name')), 'required' => true, 'nolabel' => true]),
						$form->field('billto_address1', ['placeholder' => "Address Line 1", 'class' => "addspacing savetype", 'value' => (empty($model['billto_address1']) ? $user->display('billto_address1') : $model->display('billto_address1')), 'required' => true, 'nolabel' => true]),
						$form->field('billto_address2', ['placeholder' => "Address Line 2", 'class' => "addspacing savetype", 'value' => (empty($model['billto_address2']) ? $user->display('billto_address2') : $model->display('billto_address2')), 'nolabel' => true]),
						$form->field('billto_city', ['placeholder' => "City", 'class' => "addspacing savetype", 'value' => (empty($model['billto_city']) ? $user->display('billto_city') : $model->display('billto_city')), 'required' => true, 'nolabel' => true]),
						$form->field('billto_state', ['placeholder' => "State / Province", 'class' => "addspacing savetype", 'value' => (empty($model['billto_state']) ? $user->display('billto_state') : $model->display('billto_state')), 'required' => true, 'nolabel' => true]),
						$form->field('billto_zip', ['placeholder' => "Zip/Postal Code", 'class' => "addspacing savetype", 'value' => (empty($model['billto_zip']) ? $user->display('billto_zip') : $model->display('billto_zip')), 'required' => true, 'nolabel' => true]);
						?>
						<label class="checkbox-label"><input type="checkbox" class="shippingthesame" /> Ship to is the same address.</label>
						<?php
					}
                    ?>
                </div>
                <div class="tworightcol">
                    <h2>Ship To:</h2>
                    <?php
					if ($path[0] == "orderconfirmation") {
						echo $model->shipto();
					} else {
						echo $form->field('shipto_name', ['placeholder' => "Name", 'class' => "addspacing savetype", 'required' => true, 'nolabel' => true]),
						$form->field('shipto_address1', ['placeholder' => "Address Line 1", 'class' => "addspacing savetype", 'required' => true, 'nolabel' => true]),
						$form->field('shipto_address2', ['placeholder' => "Address Line 2", 'class' => "addspacing savetype", 'nolabel' => true]),
						$form->field('shipto_city', ['placeholder' => "City", 'class' => "addspacing savetype", 'required' => true, 'nolabel' => true]),
						$form->field('shipto_state', ['placeholder' => "State / Province", 'class' => "addspacing savetype", 'required' => true, 'nolabel' => true]),
						$form->field('shipto_zip', ['placeholder' => "Zip / Postal Code", 'class' => "addspacing savetype", 'required' => true, 'nolabel' => true]);
					}
                    ?>
                </div>
                <p>&nbsp;</p>
                <p>&nbsp;</p>
                <p>&nbsp;</p>
                <div class="clear"></div>
				<?php echo
				
				//$form->field('customer_number', ['required' => true, 'class' => 'large-text savetypeorg', 'disabled' => ($path[0] == "orderconfirmation" || !empty($user['customer_number']) ? true : false), 'value' => (empty($user['customer_number']) ? null : $user->display('customer_number'))]),
				$form->field('customer_po', ['required' => true, 'class' => 'large-text savetype', 'disabled' => ($path[0] == "orderconfirmation" ? true : false)]),
				$form->field('shipping_code', ['required' => true, 'class' => 'large-text savetype', 'disabled' => ($path[0] == "orderconfirmation" ? true : false)]),
				$form->field('shipping_notes', [ 'type' => 'textarea', 'rows' => 5, 'class' => 'large-text savetype', 'disabled' => ($path[0] == "orderconfirmation" ? true : false) ]);?>
                <?php $form->end(); ?>
                <?php if(!is_paginator()): ?>
                <div class="data-table reviewordergrid">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Line</th>
                                <th>Item</th>
                                <th>Quantity</th>
                                <? if ($path[0] != "orderconfirmation") { ?><th class="actions"></th><?php } ?>
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
                                <? if ($path[0] != "orderconfirmation") { ?><td class="actions">
								<a href="/editframe/<?=h($model2['id'])?>" class="btn btn-mini btn-success"><i class="icon-white icon-edit"></i> Edit</a>
								<a href="/copyline/<?=h($model2['id'])?>" class="btn btn-mini btn-info"><i class="icon-white icon-share"></i> Copy</a>
								<a href="/deleteframe/<?=h($model2['id'])?>" data-confirm="Are you sure you want to delete this line?" class="btn btn-mini btn-danger"><i class="icon-white icon-remove"></i> Del</a></td><?php } ?>
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