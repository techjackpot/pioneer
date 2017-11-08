<?php
if (!defined('INDEX')) { require dirname(__FILE__).'/../index.php'; exit; }

if (!is_logged())
redirect_to( '/' );

if (!empty($_SESSION['OID']))
{
    $order = Order::get($_SESSION['OID']);
	
	$qtylimit = $order->display("quantity_limit");
	$qtycart = $order->cart_quantity();
	
	if ($path[0] == "copyline")
	{	
		$copyline = OrderLine::first(['id' => $path[1], 'order_id' => $order['id']]);
		if ($copyline)
		{
			$model = new OrderLine();
			foreach ($copyline as $id=>$val)
			{
				if ($id == "id" || $id == "add")
				{ }
				else {
					$model[$id] = $val;
				}
			}
			$edit = false;
			$form = new FormHelper();
			$form->prefix = 'orderline_';
			$form->data = &$model;
			
		}
		else
		{
			error_message('This line could not be copied. Please try again.');
			redirect_to( '/revieworder' );
		}
	}
    else if ($order)
    {
        $turnaround_days = $order->display("turnaround_days");
        
		if ($path[0] == "editframe")
		{
			$model = new OrderLine($path[1]);
			if (!$model['id']) error_404();
			$edit = true;
			
			$qtyitemnow = $model->totalcomponents();
			$qtycart = $qtycart - $qtyitemnow;
		}
		else
		{
			
			
			$model = new OrderLine();
			$edit = false;
		}
        
        $model['frameproduct_id'] = $order['frameproduct_id'];
        
        $form = new FormHelper();
        $form->prefix = 'orderline_';
        $form->data = &$model;
        
    }
    else
    {
        error_message('You must start a new order before adding new lines.');
        redirect_to( '/guidelines' );
    }
    
    
       
}
else
{
    error_message('You must start a new order before adding new lines.');
    redirect_to( '/guidelines' );
}

if ($qtycart >= $qtylimit && $path[0] != "editframe")
{
	error_message('Sorry, but you have reached your limit of '.$qtylimit.' components for this order.');
	redirect_to( '/revieworder' );
}

if ($_POST) {
	
	$typehidearray = [
		'10' => "'width', 'height', 'strike', 'loc', 'second', 'hand', 'assy', 'anc', 'hinge', 'hingeqty', 'hingeloc', 'closer', 'dps', 'ptp'",
		'11' => "'width', 'height', 'hand', 'assy', 'anc', 'hinge', 'hingeqty', 'hingeloc', 'closer', 'bolt', 'dps', 'ptp'",
		'20' => "'width', 'height', 'assy', 'anc'",
		'13' => "'width', 'hand', 'closer'",
		'14' => "'width', 'closer', 'bolt'",
		'17' => "'height', 'hand', 'anc', 'hinge', 'hingeqty', 'hingeloc'",
		'18' => "'height', 'strike', 'loc', 'second', 'hand', 'anc'",
		'19' => "'height', 'anc'",
		'22' => "'height', 'anc'",
		'25' => "'width', 'height', 'strike', 'loc', 'second', 'hand', 'assy', 'anc', 'hinge', 'hingeqty', 'hingeloc', 'closer', 'bolt'",
		'26' => "",
		'27' => "'height', 'hand', 'hinge', 'hingeqty', 'hingeloc'",
		'28' => "'height', 'strike', 'loc', 'second', 'hand'",
		'39' => "",
		'29' => "",
		'31' => "'height', 'hand', 'hinge', 'hingeqty', 'hingeloc'",
		'174' => "'height', 'strike', 'loc', 'second', 'hand'",
		'175' => "'height'",
		'176' => "'height', 'hand', 'hinge', 'hingeqty', 'hingeloc'",
		'177' => "'height', 'strike', 'loc', 'second', 'hand'"
	];
		
    $values = $form->apply_post('quantity', 'series', 'backbend', 'gage', 'matl', 'thk', 'rabbet', 'type', 'drawing', 'depth', 'width', 'height', 'strike', 'hand', 'profile', 'label', 'assy', 'anc', 'hinge', 'closer', 'bolt', 'add', 'dps', 'ptp');
	
	if ($_POST['type'] && ($_POST['type'] == '20' || $_POST['type'] == '25'))
	{
		$form->apply_post('gb');
	}
	
	if ($_POST['type'] && ($_POST['type'] == '10' || $_POST['type'] == '11'))
	{
		$form->apply_post('cj', 'dtch');
		
		if ($_FILES['dps']) // need drawing
		{
			$model->validate_valid_file('dps');
			$model->validate_file_pdf('dps');
		}
		
		if ($_FILES['ptp']) // need drawing
		{
			$model->validate_valid_file('ptp');
			$model->validate_file_pdf('ptp');
		}
	}
	
	if ($_POST['series'] && $_POST['series'] == '171')
	{
		$form->apply_post('backbend');
		$model->validate_required('backbend');
	}
    
    if ($_POST['strike'] && $_POST['strike'] != '97')
	{
		$form->apply_post('loc', 'second');
		$model->validate_required('loc', 'second');
	}
    
    if ($_POST['hinge'] && $_POST['hinge'] != '146')
	{
		$form->apply_post('hingeqty', 'hingeloc');
		$model->validate_required('hingeqty', 'hingeloc');
	}
	
	if ($_POST['depth'] && $_POST['depth'] == '65')
	{
		$form->apply_post('specialdepth');
		$model->validate_required('specialdepth');
	}
	
	if ($_POST['width'] && $_POST['width'] == '87')
	{
		$form->apply_post('specialwidth');
		$model->validate_required('specialwidth');
	}
	
	if ($_POST['height'] && $_POST['height'] == '94')
	{
		$form->apply_post('specialheight');
		$model->validate_required('specialheight');
	}
	
	if ($_POST['type'] && is_numeric($_POST['type']) == "---")
	{
		$type = FrameOptionValue::get($_POST['type']);
		if ($type)
		$typename = $type->display("name");
		if ($typename  == "---")
		{
			$model->errors["type"][] = "This is an invalid type.";
		}
	}
	
	if (is_array($typehidearray[$_POST['type']]))
	{
		$getrequired = $typehidearray[$_POST['type']];
		if ($_POST['strike'] == '97')
		{
			$getrequired = str_replace(", 'loc', 'second'","",$getrequired);
		}
		if ($_POST['hinge'] == '146')
		{
			$getrequired = str_replace(", 'hingeqty', 'hingeloc'","",$getrequired);
		}
		$model->validate_required($getrequired);
	}
	
	$checkanc = array();
	if ($model['frameproduct_id'] == '1') // 5 Day
	{
		if ($_POST['series'] == '171') // F-- Series
		{
			$checkanc = ['143','144','145','139'];
		}
		else if ($_POST['series'] == '172') // DW- Series
		{
			$checkanc = ['140','141','142','143','144','145'];
		}
	}
	else if ($model['frameproduct_id'] == '2') // 10 Day
	{
		if ($_POST['series'] == '171') // F-- Series
		{
			$checkanc = ['139'];
		}
		else if ($_POST['series'] == '172') // DW- Series
		{
			$checkanc = ['140','141','142','143','144','145'];
		}
	}
	
	if (in_array($_POST['anc'], $checkanc))
	{
		$model->errors["anc"][] = "This option is not allowed with this series.";
	}

	$model->validate_required('quantity', 'series', 'gage', 'matl', 'thk', 'rabbet', 'type', 'depth', 'profile', 'label');
	
	if ($_POST['type'] == '25') // need drawing
	{
		$form->apply_post('openings');
		$model->validate_required('openings');
		$model->validate_valid_file('drawing');
		$model->validate_required_file('drawing');
		$model->validate_file_pdf('drawing');
	}
	
	if ($_POST['strike'] == '98') // need drawing
	{
		$model->validate_valid_file('estkdrawing');
		$model->validate_required_file('estkdrawing');
		$model->validate_file_pdf('estkdrawing');
	}

    $model['order_id'] = $_SESSION['OID'];
	
	$qtyitem = $model->totalcomponents();
	$qtytemptotal = $model->totalcomponents() + $qtycart;
	
	if ($qtytemptotal > $qtylimit)
	{
		$model->errors = true;
		error_message('This line item contains '.$qtyitem.' components which is '.($qtytemptotal - $qtylimit).' components over the limit of '.$qtylimit.' components for this order.');
	}
    
    unset($model['frameproduct_id']);
	
    if (!$model->errors) {
		if ($_POST['type'] == '25') // need drawing
		{
		$form->process_files('drawing');
		}
		
		if ($_POST['strike'] == '98') // need drawing
		{
		$form->process_files('estkdrawing');
		}
		
		if ($_POST['type'] && ($_POST['type'] == '10' || $_POST['type'] == '11'))
		{
			if ($_FILES['dps']) // need drawing
			{
				$form->process_files('dps');
			}
			
			if ($_FILES['ptp']) // need drawing
			{
				$form->process_files('ptp');
			}
		}
		
        if ($success = $model->save()) {
			if ($edit)
			{
				success_message('The line has been successfully changed.');
				redirect_to( '/revieworder');
			}
			else
            success_message('The line has been successfully added to your order.');
        }
        else { if ($edit) { error_message('Sorry, we were not able to save your line. Please try again later.'); } else { error_message('Sorry, we were not able to add that line. Please try again later.'); } }
        if (is_json()) { json_response([ 'success' => $success ]); }
        else { redirect_to( '/revieworder');
        } 
    }
    else if (is_json()) { json_response([ 'success' => false, 'errors' => $model->errors ]); }
    else { error_message('There were errors '.($edit ? 'saving' : 'submitting').' your line. Please see below.'); }
}



$model['frameproduct_id'] = $order['frameproduct_id'];
$form->data = &$model;

?>
<div class="margins">
    <div class="qsrightcol">
        <div class="qscontainer middlealign">
            <?php quickship_order_summary($order); ?>
        </div>
		<?php important_notice() ?>
    </div>
    <div class="qsleftcol">
        <div class="qscontainer redstripbottom">
            <?php quickship_navigation($path); ?>
            <div class="qsintrotext">
                <h1>Add New <?=$turnaround_days?> Day Engineered Frame Line</h1>
                <p>Configure the new line below and click the Add To Order button below when finished. Once you have all of your lines added click on the Review Order button above to review your order before finalizing.</p>
            </div>
            <div class="smallbanner banner4">&nbsp;</div>
            <div class="padding addframeform">
                <?php
                echo $form->start(array('class' => (is_modal() ? 'form-horizontal js-form' : null), 'modal_class' => 'modal-large', 'multipart' => true)),
                
                '<div class="quantitystyle">',
                $form->field('quantity', ['class' => 'chosen chosen-block', 'values' => $model->quantityvalues($edit)]),
                '</div>',
                
                '<div class="toggle"><div class="togheader">FRAME INFORMATION</div>',
                $form->field('series', ['class' => 'chosen chosen-block']),
				'<div style="display: none" id="backbend">',
				$form->field('backbend', ['value' => '187']),
				'</div>',
                $form->field('gage', ['class' => 'chosen chosen-block']),
                $form->field('matl', ['class' => 'chosen chosen-block', 'value' => '3']),
                $form->field('thk', ['class' => 'chosen chosen-block']),
                $form->field('rabbet', ['class' => 'chosen chosen-block']),
                $form->field('type', ['class' => 'chosen chosen-block']),
				'<div style="display: none" id="typewarning">',
				'<div class="controls" style="margin: -20px 0px 20px 180px;"><span>WARNING: Selected type is 10ft in height, use nominal size - height option for hardware location where applicable</span></div>',
				'</div>',
				'<div style="display: none" id="hardwarelocations">',
				$form->field('cj'),
				$form->field('dtch'),
				'</div>',
				'<div style="display: none" id="glazingbead">',
				$form->field('gb'),
				'</div>',
				'<div style="display: none" id="unitselected">',
				$form->field('openings'),
				$form->field('drawing', ['accept' => 'application/pdf']),
				'</div>',
                $form->field('depth', ['class' => 'chosen chosen-block']),
				'<div style="display: none" id="specialdepth">',
				$form->field('specialdepth'),
				'</div>',
                '</div>',
                
                '<div class="toggle" id="nominalblock"><div class="togheader">NOMINAL SIZE</div>',
                $form->field('width', ['class' => 'chosen chosen-block']),
				'<div style="display: none" id="specialwidth">',
				$form->field('specialwidth'),
				'</div>',
                $form->field('height', ['class' => 'chosen chosen-block']),
				'<div style="display: none" id="specialheight">',
				$form->field('specialheight'),
				'</div>',
                '</div>',
                
                '<div class="toggle" id="strikeblock"><div class="togheader">STRIKE INFORMATION</div>',
                $form->field('strike', ['class' => 'chosen chosen-block']),
				'<div style="display: none" id="estkdrawing">',
				$form->field('estkdrawing', ['accept' => 'application/pdf']),
				'</div>',
                $form->field('loc', ['class' => 'chosen chosen-block']),
                $form->field('second', ['class' => 'chosen chosen-block']),
                '</div>',
                
                '<div class="toggle" id="handblock"><div class="togheader">HAND</div>',
                $form->field('hand', ['class' => 'chosen chosen-block']),
                '</div>',
                
                '<div class="toggle"><div class="togheader">PROFILE</div>',
                $form->field('profile', ['class' => 'chosen chosen-block']),
                '</div>',
                
                '<div class="toggle"><div class="togheader">LABEL, ASSEMBLY, ANCHOR</div>',
                $form->field('label', ['class' => 'chosen chosen-block']),
                $form->field('assy', ['class' => 'chosen chosen-block']),
                $form->field('anc', ['class' => 'chosen chosen-block']),
				//'<div style="hidden">'
				//$form->field('ancf5', ['class' => 'chosen chosen-block ancf5']),
				//$form->field('ancdw', ['class' => 'chosen chosen-block ancdw']),
				//'</div>',
                '</div>',
                
                '<div class="toggle" id="hingeblock"><div class="togheader">HINGE</div>',
                $form->field('hinge', ['class' => 'chosen chosen-block']),
                $form->field('hingeqty', ['class' => 'chosen chosen-block']),
                $form->field('hingeloc', ['class' => 'chosen chosen-block']),
                '</div>',
                
                '<div class="toggle"><div class="togheader">OTHER</div>',
                $form->field('closer', ['class' => 'chosen chosen-block']),
                $form->field('bolt', ['class' => 'chosen chosen-block']),
                $form->field('add', ['class' => 'larger']),
                '</div>',
				
				'<div class="toggle" id="hardwarepreps"><div class="togheader">ADDITIONAL HARDWARE PREPS</div>',
				$form->field('dps', ['accept' => 'application/pdf']),
				$form->field('ptp', ['accept' => 'application/pdf']),
                '</div>',
				
				
                $form->submit(($edit ? "SAVE LINE CHANGES" :"ADD LINE TO ORDER"), NULL, NULL, 'btn btn-blue'),
                $form->end();
                ?>
                <div class="clear"></div>
                <div class="glbottom">&nbsp;</div>
            </div>

        </div>
        <div class="clear"></div>
    </div>
</div>