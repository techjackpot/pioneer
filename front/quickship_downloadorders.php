<?php
if (!defined('INDEX')) { require dirname(__FILE__).'/../index.php'; exit; }

$no_template = true;

if(is_numeric($path[3]) && is_numeric($path[2]) && is_numeric($path[1]))
$date = date("Y-m-d", strtotime($path[3].'-'.$path[1].'-'.$path[2]));

$params = array();
$params['where'] = '`status` = "Processed"'.($date ? " AND `dateprocessed` LIKE '".$date."%'" : "");
$params['sort'] = 'dateprocessed DESC'; 
$data = Order::find( $params );

foreach($data as $index => $model2):
    $orderlines = OrderLine::find(['where' => ['order_id' => $model2['id']]]);
    $line = 0;
    foreach ($orderlines as $index2 => $model3):
    $line = $index2+1;
    
?>
INSERT INTO dbo.OrderInFromWeb (ConcatOrderLine, OrderIn, LineIn, F1, F2, F3, F4, F5, F6, F7, F8, F9, F10, F11, F12, F13, F14, F15, F16, F17, F18, F19, F20, F21, F22, F23, F24, F25, SplDepth, SplWidth, SplHeight, Tags, DwgUnitElev, DwgStrike, DwgDPS, DwgPowerTrans, Date, BackBend, CommunicatingHdwe, DutchHdwe, GlazingBead, NoOpenings) VALUES (
<?=sanitize('QS'.h(sprintf('%08d', $model2->display('id'))).'-'.$line)?>, <?=sanitize('QS'.h(sprintf('%08d', $model2->display('id')))) ?>, <?= sanitize($line) ?>, <?= sanitize($line) ?>, <?= sanitize(h($model3->display('quantity'))) ?>, <?= sanitize(h($model3->display('series'))) ?>, <?= sanitize(h($model3->display('gage'))) ?>, <?= sanitize(h($model3->display('matl'))) ?>, <?= sanitize(h($model3->display('thk'))) ?>, <?= sanitize(h($model3->display('rabbet'))) ?>, <?= sanitize(h($model3->display('type'))) ?>, <?= sanitize(h($model3->display('depth'))) ?>, <?= sanitize(h($model3->display('width'))) ?>, <?= sanitize(h($model3->display('height'))) ?>, <?= sanitize(h($model3->display('strike'))) ?>, <?= sanitize(h($model3->display('loc'))) ?>, <?= sanitize(h($model3->display('second'))) ?>, <?= sanitize(h($model3->display('hand'))) ?>, <?= sanitize(h($model3->display('profile'))) ?>, <?= sanitize(h($model3->display('label'))) ?>, <?= sanitize(h($model3->display('assy'))) ?>, <?= sanitize(h($model3->display('anc'))) ?>, <?= sanitize(h($model3->display('hinge'))) ?>, <?= sanitize(h($model3->display('hingeqty'))) ?>, <?= sanitize(h($model3->display('hingeloc'))) ?>, <?= sanitize(h($model3->display('closer'))) ?>, <?= sanitize(h($model3->display('bolt'))) ?>, <?="'".(!empty($model3['backbend']) && $model3['backbend'] != 187 ? " +".h($model3->display('backbend')) : '').($model3['cj'] ? " +CJ" : '').($model3['dtch'] ? " +DTCH" : '').($model3['gb'] ? " +GB" : '').(!empty($model3['openings']) ? " +".h($model3->display('openings'))." openings" : '')."'"?>, <?= sanitize(h($model3->display('specialdepth'))) ?>, <?= sanitize(h($model3->display('specialwidth'))) ?>, <?= sanitize(h($model3->display('specialheight'))) ?>, <?= sanitize(h($model3->display('add'))) ?>, <?= sanitize(h($model3->display('drawing'))) ?>, <?= sanitize(h($model3->display('estkdrawing'))) ?>, <?= sanitize(h($model3->display('dps'))) ?>, <?= sanitize(h($model3->display('ptp'))) ?>, <?=sanitize(h($model2->display_dateprocessed()));?>, <?= sanitize(h($model3->display('backbend'))) ?>, '<?=($model3['cj'] ? "true" : '')?>', '<?=($model3['dtch'] ? "true" : '')?>', '<?=($model3['gb'] ? "true" : '')?>', <?= sanitize(h($model3->display('openings'))) ?>);

    <?php
    endforeach;
endforeach; 