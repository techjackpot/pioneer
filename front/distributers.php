<?php
if (!defined('INDEX')) { require dirname(__FILE__).'/../index.php'; exit; }

/*
$profiles = Vendor::find();

foreach($profiles as $index => $model){

	// Get lat and long by address         
	$address = (strpos($model['address1'], "#") ? trim(substr($model['address1'], 0, strpos($model['address1'], "#"))) : $model['address1']).", ".$model['city']." ".$model['state'].(!empty($model['zip']) ? " ".$model['zip'] : "").(!empty($model['country']) ? ", ".$model['country'] : "");
	$prepAddr = str_replace(' ','+',$address);
	
	//echo '<a href="https://maps.google.com/maps/api/geocode/json?address='.$prepAddr.'&sensor=false">'.$model['id'].' - https://maps.google.com/maps/api/geocode/json?address='.$prepAddr.'&sensor=false</a><br><br>';
	
	//echo 'https://maps.google.com/maps/api/geocode/json?address='.$prepAddr.'&sensor=false'; die();
	
	$geocode=file_get_contents('https://maps.google.com/maps/api/geocode/json?address='.$prepAddr.'&sensor=false');
	$output= json_decode($geocode);
	if ($output->status == "OVER_QUERY_LIMIT")
	{
		echo "DAILY QUOTA EXCEEDED! STOP!";
		die();
	}
	if ($output->status == "ZERO_RESULTS")
	{
		$model['lat'] = "NOTFOUND";
		$model['lng'] = "NOTFOUND";
		$model['badaddr'] = 1;
	}
	else
	{
	$model['lat'] = $output->results[0]->geometry->location->lat;
	$model['lng'] = $output->results[0]->geometry->location->lng;
	}

	$model->save();
	sleep(1);
}
*/

?>
<div class="page-hero aboutus">
    <h1>Distribution and Sales</h1>
	<div class="darkoverlay"></div>
</div>
<div class="section steel">
    <div class="landingmargins">
        <h2>Pioneer Distribution and Sales Map</h2>
        <div class="themap">
            <input type="text" name="zoopcode" id="ziplookup" placeholder="Please enter your zip/postal code to find the closest location." />
        </div>
    </div>
</div>
<div class="maderighthere">
    <div class="america">Made. Right. Here.</div>
</div>

<?php ?>