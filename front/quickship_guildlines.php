<?php
if (!defined('INDEX')) { require dirname(__FILE__).'/../index.php'; exit; }

if (!is_logged())
redirect_to( '/' );

$fiveday = FrameProduct::get(1);
$tenday = FrameProduct::get(2);

if ($_POST['neworder'])
{
    if (empty($_SESSION['OID']))
    {
        $model = new Order();
        
        $model['frameproduct_id'] = $_POST['frameproduct_id'];
        if ($_SESSION['AUID'] && (user_type() > User::CUSTOMER))
		{
			$model['user_id'] = $_SESSION['AUID'];
		}
		else
		{
			$model['user_id'] = user_id();
		}
        $model['status'] = "With Customer";
        
        if ($success = $model->save()) {
            success_message('New order created.');
            $_SESSION['OID'] = $model['id'];
            redirect_to( '/revieworder' );
        }
        else { error_message('Sorry, there was an error creating your order please try again.'); }
    }
    else
    {
        error_message('You already have an order in process, please cancel this order first if you would like to start a new one.');
    }
}

if (!empty($_SESSION['OID']))
{
    $model = new Order($_SESSION['OID']);
    
    if ($model)
    $turnaround_days = $model->display("turnaround_days");
    else
    $turnaround_days = '5';
       
}
else
{
    if ($_SESSION['AUID'] && (user_type() > User::CUSTOMER))
    {
        $ordercheck = Order::first(['user_id' => $_SESSION['AUID'], 'status' => "With Customer"]);
    }
    else
    {
        $ordercheck = Order::first(['user_id' => user_id(), 'status' => "With Customer"]);
    }
    
    
    if ($ordercheck)
    {
        $model = $ordercheck;
        $_SESSION['OID'] = $model['id'];
        redirect_to( '/guidelines' );
    }
    
    $turnaround_days = '5';
}

if ($_REQUEST['cancel'])
{
    if (!empty($model['id']))
    {
        if ($success = $model->delete()) {
            success_message('Order has been cancelled.');
            unset($_SESSION['OID']);
            redirect_to( '/guidelines' );
        }
        else { error_message('Sorry, there was an error cancelling order please try again.'); }
    }
}

if ($_SESSION['AUID'] && (user_type() > User::CUSTOMER))
{
	$user = User::get($_SESSION['AUID']);
}
else
{
	$user = User::get(user_id());
}

?>
<div class="margins">
    <div class="qsrightcol">
        <div class="qscontainer middlealign">
            <?php quickship_order_summary($model); ?>
        </div>
        <?php important_notice($path) ?>
    </div>
    <div class="qsleftcol">
        <div class="qscontainer redstripbottom">
            <? quickship_navigation($path); ?>
            <div class="qsintrotext">
                <?php if (empty($_SESSION['OID'])) { ?>
                <h1>Welcome, <?=h($user->display("first_name"))?></h1>
                <?php } ?>
                <p>Our Quickship program provides an easy method for processing orders to be delivered on a tight timeline. Please follow the guidelines below so you understand the restrictions for each delivery method.<?php if (empty($_SESSION['OID'])) { ?><br><br>Once you are ready please look to the right, choose your timeline, and click "PROCEED WITH ORDER".<?php } ?></p>
            </div>
            <div class="smallbanner <?=(empty($_SESSION['OID']) ? 'banner2' : 'banner3')?>">&nbsp;</div>
            <div class="padding">
                <div class="qsguildlinesgrid">
                    <div class="glcol glcol1">
                        <div class="glcell glheader" data-index="1"></div>
                        <div class="glcell" data-index="2"><div>Discount Structure</div></div>
                        <div class="glcell" data-index="3"><div>Quantity of Pieces Per Week</div></div>
                        <div class="glcell" data-index="4"><div>Multiple Orders Allowed</div></div>
                        <div class="glcell" data-index="5"><div>Series</div></div>
                        <div class="glcell" data-index="6"><div>Gages</div></div>
                        <div class="glcell" data-index="7"><div>Material</div></div>
                        <div class="glcell" data-index="8"><div>Door Thickness / Hardware Rabbet</div></div>
                        <div class="glcell" data-index="9"><div>Rabbet Type</div></div>
                        <div class="glcell" data-index="10"><div>Maximum Jamb Depth</div></div>
                        <div class="glcell" data-index="11"><div>Components</div></div>
                        <div class="glcell" data-index="12"><div>3-Sided Frames (3pcs)</div></div>
                        <div class="glcell" data-index="13"><div>Borrowed Lite Units (4pcs)</div></div>
                        <div class="glcell" data-index="14"><div>CNN - Special Units</div></div>
                        <div class="glcell" data-index="15"><div>10ft Sticks</div></div>
                        <div class="glcell" data-index="16"><div>10ft Mullions (2pcs)</div></div>
                        <div class="glcell" data-index="17"><div>Intermediate Mullions (2pcs)</div></div>
                        <div class="glcell" data-index="18"><div>Strike Preparations</div></div>
                        <div class="glcell" data-index="19"><div>Faces</div></div>
                        <div class="glcell" data-index="20"><div>UL or WHL applied label</div></div>
                        <div class="glcell" data-index="21"><div>Assembly</div></div>
                        <div class="glcell" data-index="22"><div>Masonry Anchors (F-Series)</div></div>
						<div class="glcell" data-index="23"><div>Wood Stud Anchors (F-Series)</div></div>
						<div class="glcell" data-index="24"><div>Metal Stud Anchors (F-Series)</div></div>
						<div class="glcell" data-index="25"><div>Existing Opening Anchors (F-Series)</div></div>
                        <div class="glcell" data-index="26"><div>Anchors (DW Series)</div></div>
                        <div class="glcell" data-index="27"><div>Hinge Preparations</div></div>
                        <div class="glcell" data-index="28"><div>Closer Reinforcements</div></div>
                        <div class="glcell" data-index="29"><div>Bolt Preparations and Reinforcements</div></div>
                        <div class="glcell" data-index="30"><div>Glazing Bead</div></div>
                        <div class="glcell" data-index="31"><div>Additional Preps</div></div>
                        <div class="glcell" data-index="32"><p>&nbsp;</p></div>
                    </div>
                    <div class="glcol glcol2">
                        <div class="glcell glheader" data-index="1"><div><?=$fiveday->display('turnaround_days')?> Day Guidelines</div></div>
                        <div class="glcell" data-index="2"><div><h6>Discount Structure</h6>15 Less Discount Points than Standard</div></div>
                        <div class="glcell" data-index="3"><div><h6>Quantity of Pieces Per Week</h6><?=$fiveday->display('quantity_limit')?></div></div>
                        <div class="glcell" data-index="4"><div><h6>Multiple Orders Allowed</h6><?=$fiveday->display('quantity_limit')?> piece MAX TOTAL per week</div></div>
                        <div class="glcell" data-index="5"><div><h6>Series</h6>F-Series (Single or Double Return), DW (Double Return)</div></div>
                        <div class="glcell" data-index="6"><div><h6>Gages</h6>14 Gage or 16 Gage</div></div>
                        <div class="glcell" data-index="7"><div><h6>Material</h6>Galvannealed only</div></div>
                        <div class="glcell" data-index="8"><div><h6>Door Thickness / Hardware Rabbet</h6>1-3/8" or 1-3/4" Door</div></div>
                        <div class="glcell" data-index="9"><div><h6>Rabbet Type</h6>ER, UR, SR, CO</div></div>
                        <div class="glcell" data-index="10"><div><h6>Maximum Jamb Depth</h6>12-3/4"</div></div>
                        <div class="glcell" data-index="11"><div><h6>Components</h6>Single and Paired Header (max 8-0), Blank Jambs, Hinge Jambs, Strike Jambs, Borrowed Light Jambs (Max 9-0)</div></div>
                        <div class="glcell" data-index="12"><div><h6>3-Sided Frames (3pcs)</h6>Maximum Opening: 8-0 x 9-0. Standard Only</div></div>
                        <div class="glcell" data-index="13"><div><h6>Borrowed Lite Units (4pcs)</h6>Maximum Opening: 4-0 x 4-0 (No Mullions)</div></div>
                        <div class="glcell" data-index="14"><div><h6>CNN - Special Units</h6>N/A</div></div>
                        <div class="glcell" data-index="15"><div><h6>10ft Sticks</h6>Blank, Hinge, Strike, and 4" Face</div></div>
                        <div class="glcell" data-index="16"><div><h6>10ft Mullions (2pcs)</h6>Blank, One Side Hinge, One Side Strike</div></div>
                        <div class="glcell" data-index="17"><div><h6>Intermediate Mullions (2pcs)</h6>Up to 8-0: Blank, One Side Hinge, One Side Strike</div></div>
                        <div class="glcell" data-index="18"><div><h6>Strike Preparations</h6>4-7/8 ASA Strike, 2-3/4 Strike, Surface Applied Strike, Electric Strike (Template Required), 2-3/4 Deadlock Strike</div></div>
                        <div class="glcell" data-index="19"><div><h6>Faces</h6>2" Jambs and 2" or 4" Heads (No unequal faces)</div></div>
                        <div class="glcell" data-index="20"><div><h6>UL or WHL applied label</h6>UL and WHI only where applicable (Embossed Frame Labels Not Available)</div></div>
                        <div class="glcell" data-index="21"><div><h6>Assembly</h6>Knock Down Only</div></div>
                        <div class="glcell" data-index="22"><div><h6>Masonry Anchors (F-Series)</h6>Wire Anchors up to 8-3/4", Yoke and Strap up to 12-3/4"</div></div>
						<div class="glcell" data-index="23"><div><h6>Wood Stud Anchors (F-Series)</h6>Wood Stud Strap Anchor</div></div>
						<div class="glcell" data-index="24"><div><h6>Metal Stud Anchors (F-Series)</h6>"Steel Stud Hi Hat for CO and up to 7" ER, 6-5/8 UR, Z-Clips for over 7" ER, 6-5/8 UR, Steel Stud Stepped Hi Hat for SR"</div></div>
						<div class="glcell" data-index="25"><div><h6>Existing Opening Anchors (F-Series)</h6>N/A</div></div>
                        <div class="glcell" data-index="26"><div><h6>Anchors (DW Series)</h6>GRIP LOK compression anchors and welded sill straps</div></div>
                        <div class="glcell" data-index="27"><div><h6>Hinge Preparations</h6>4-1/2" or 5"; Square Corner Only, Standard or Heavyweight, BLANK</div></div>
                        <div class="glcell" data-index="28"><div><h6>Closer Reinforcements</h6>Regular Arm, Parallel Arm, Regular and Parallel Arm</div></div>
                        <div class="glcell" data-index="29"><div><h6>Bolt Preparations and Reinforcement</h6>Universal Mortised Flush Bolt, Standard Flush Bolt Knockout, Surface Bolt Reinforcement in Face or Soffit</div></div>
                        <div class="glcell" data-index="30"><div><h6>Glazing Bead</h6>10' Lengths with screw holes</div></div>
                        <div class="glcell" data-index="31"><div><h6>Additional Preps</h6>N/A</div></div>
                        <div class="glcell" data-index="32"><p>&nbsp;</p></div>
                    </div>
                    <div class="glcol glcol3">
                        <div class="glcell glheader" data-index="1"></div>
                        <div class="glcell" data-index="2"></div>
                        <div class="glcell" data-index="3"></div>
                        <div class="glcell" data-index="4"></div>
                        <div class="glcell" data-index="5"></div>
                        <div class="glcell" data-index="6"></div>
                        <div class="glcell" data-index="7"></div>
                        <div class="glcell" data-index="8"></div>
                        <div class="glcell" data-index="9"></div>
                        <div class="glcell" data-index="10"></div>
                        <div class="glcell" data-index="11"></div>
                        <div class="glcell" data-index="12"></div>
                        <div class="glcell" data-index="13"></div>
                        <div class="glcell" data-index="14"></div>
                        <div class="glcell" data-index="15"></div>
                        <div class="glcell" data-index="16"></div>
                        <div class="glcell" data-index="17"></div>
                        <div class="glcell" data-index="18"></div>
                        <div class="glcell" data-index="19"></div>
                        <div class="glcell" data-index="20"></div>
                        <div class="glcell" data-index="21"></div>
                        <div class="glcell" data-index="22"></div>
                        <div class="glcell" data-index="23"></div>
                        <div class="glcell" data-index="24"></div>
                        <div class="glcell" data-index="25"></div>
                        <div class="glcell" data-index="26"></div>
                        <div class="glcell" data-index="27"></div>
                        <div class="glcell" data-index="28"></div>
						<div class="glcell" data-index="29"></div>
						<div class="glcell" data-index="30"></div>
						<div class="glcell" data-index="31"></div>
                        <div class="glcell" data-index="32"><p>&nbsp;</p></div>
                    </div>
                    <div class="glcol glcol4">
                        <div class="glcell glheader" data-index="1"><div><?=$tenday->display('turnaround_days')?> Day Guidelines</div></div>
                        <div class="glcell" data-index="2"><div><h6>Discount Structure</h6>10 Less Discount Points than Standard</div></div>
                        <div class="glcell" data-index="3"><div><h6>Quantity of Pieces Per Week</h6><?=$tenday->display('quantity_limit')?></div></div>
                        <div class="glcell" data-index="4"><div><h6>Multiple Orders Allowed</h6><?=$tenday->display('quantity_limit')?> piece MAX TOTAL per week</div></div>
                        <div class="glcell" data-index="5"><div><h6>Series</h6>F-Series (Single or Double Return), DW (Double Return)</div></div>
                        <div class="glcell" data-index="6"><div><h6>Gages</h6>14 Gage or 16 Gage</div></div>
                        <div class="glcell" data-index="7"><div><h6>Material</h6>Galvannealed only</div></div>
                        <div class="glcell" data-index="8"><div><h6>Door Thickness / Hardware Rabbet</h6>1-3/8" or 1-3/4" Door</div></div>
                        <div class="glcell" data-index="9"><div><h6>Rabbet Type</h6>ER, UR, SR, CO, and DE (3-Sided Frames Only)</div></div>
                        <div class="glcell" data-index="10"><div><h6>Maximum Jamb Depth</h6>16 Gage (12-3/4”),14 Gage (15”)</div></div>
                        <div class="glcell" data-index="11"><div><h6>Components</h6>Single and Paired Header (max 8-0), Blank Jambs, Hinge Jambs, Strike Jambs, Borrowed Light Jambs (Max 10-0)</div></div>
                        <div class="glcell" data-index="12"><div><h6>3-Sided Frames (3pcs)</h6>Maximum Opening: 8-0 x 10-0. Standard, Dutch Frames, Communicating Frames</div></div>
                        <div class="glcell" data-index="13"><div><h6>Borrowed Lite Units (4pcs)</h6>Maximum Opening: 6-0 x 4-0 (Order CNN for Mullions)</div></div>
                        <div class="glcell" data-index="14"><div><h6>CNN - Special Units</h6>Max Overall Width: 8-0, Max Overall Height: 10-0, [MAX 6 Holes] (Elevation Required)</div></div>
                        <div class="glcell" data-index="15"><div><h6>10ft Sticks</h6>Blank, Hinge, Strike, and 4" Face</div></div>
                        <div class="glcell" data-index="16"><div><h6>10ft Mullions (2pcs)</h6>Blank, One Side Hinge, One Side Strike</div></div>
                        <div class="glcell" data-index="17"><div><h6>Intermediate Mullions (2pcs)</h6>Up to 8-0: Blank, One Side Hinge, One Side Strike</div></div>
                        <div class="glcell" data-index="18"><div><h6>Strike Preparations</h6>4-7/8 ASA Strike, 2-3/4 Strike, Surface Applied Strike, Electric Strike (Template Required), 2-3/4 Deadlock Strike</div></div>
                        <div class="glcell" data-index="19"><div><h6>Faces</h6>2” Jambs and 2" or 4” Heads (No unequal faces)</div></div>
                        <div class="glcell" data-index="20"><div><h6>UL or WHL applied label</h6>UL and WHI only where applicable (Embossed Frame Labels Not Available)</div></div>
                        <div class="glcell" data-index="21"><div><h6>Assembly</h6>Knock Down or Setup and Face Weld</div></div>
                        <div class="glcell" data-index="22"><div><h6>Masonry Anchors (F-Series)</h6>Wire Anchors up to 8-3/4", Yoke and Strap up to 12-3/4"</div></div>
						<div class="glcell" data-index="23"><div><h6>Wood Stud Anchors (F-Series)</h6>Wood Stud Strap Anchor</div></div>
						<div class="glcell" data-index="24"><div><h6>Metal Stud Anchors (F-Series)</h6>Steel Stud Hi Hat for CO and up to 7" ER, 6-5/8 UR, Z-Clips for over 7" ER, 6-5/8 UR, Steel Stud Stepped Hi Hat for SR and DE</div></div>
						<div class="glcell" data-index="25"><div><h6>Existing Opening Anchors (F-Series)</h6>Punch and Dimple Only, P&D w/Expansions Bolt Hat Anchor, P&D w/Pipe and Plate (UL RATED) up to 8-3/4"</div></div>
                        <div class="glcell" data-index="26"><div><h6>Anchors (DW Series)</h6>GRIP LOK compression anchors and welded sill straps</div></div>
                        <div class="glcell" data-index="27"><div><h6>Hinge Preparations</h6>4-1/2" or 5"; Square Corner Only, Standard or Heavyweight, BLANK</div></div>
                        <div class="glcell" data-index="28"><div><h6>Closer Reinforcements</h6>Regular Arm, Parallel Arm, Regular and Parallel Arm</div></div>
                        <div class="glcell" data-index="29"><div><h6>Bolt Preparations and Reinforcement</h6>Universal Mortised Flush Bolt, Standard Flush Bolt Knockout, Surface Bolt Reinforcement in Face or Soffit</div></div>
                        <div class="glcell" data-index="30"><div><h6>Glazing Bead</h6>10' Lengths with screw holes</div></div>
                        <div class="glcell" data-index="31"><div><h6>Additional Preps</h6>Door Position Switches, Power Transfers (Templates Require)</div></div>
                        <div class="glcell" data-index="32"><p>&nbsp;</p></div>
                    </div>
                    
                </div>
                <div class="clear"></div>
                <div class="glbottom">&nbsp;</div>
            </div>

        </div>
        <div class="clear"></div>
    </div>
</div>