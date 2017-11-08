<?php
if (!defined('INDEX')) { require dirname(__FILE__).'/../index.php'; exit; }

	session_start();
	$no_template = true;
	
	switch($path[1]):

		case 'settimezone':
			$_SESSION['timezoneoffset'] = $_POST['time'];
		break;
    
        case 'datainputorder':
            $context = $_POST['context'];
            $val = $_POST['val'];
            $name = preg_replace('/[^\w_-]/', '', $_POST['field']);
            
            sql("UPDATE `orders` SET `".$name."` = %s WHERE SHA1(MD5(id)) = %s", $val, $context);
            
		break;
    
        case 'datainputorganization':
            $context = $_POST['context'];
            $val = $_POST['val'];
            $name = preg_replace('/[^\w_-]/', '', $_POST['field']);
            
            sql("UPDATE `organizations` SET `".$name."` = %s WHERE SHA1(MD5(id)) = %s", $val, $context);
            
		break;
    
        case 'vendormapload':
			
            
			// Get parameters from URL
			$address = urldecode($_POST["context"]);
            
            $geocode=file_get_contents('https://maps.google.com/maps/api/geocode/json?address='.$address.'&sensor=false');
            $output= json_decode($geocode);
            if ($output->status == "OVER_QUERY_LIMIT")
            {
                echo "DAILY QUOTA EXCEEDED! STOP!";
                die();
            }
            if ($output->status == "ZERO_RESULTS")
            {
                $lat = "NOTFOUND";
                $lng = "NOTFOUND";
                $badaddr = 1;
            }
            else
            {
            $lat = $output->results[0]->geometry->location->lat;
            $lng = $output->results[0]->geometry->location->lng;
            }            

			$center_lat = $lat;
			$center_lng = $lng;
			//$radius = '200';
						
			$whereadd = array("`type` = 'dist'");
			//$addhaves = array("distance < ".sanitize($radius));
			$addselects[] = "( 3959 * acos( cos( radians(".sanitize($center_lat).") ) * cos( radians( lat ) ) * cos( radians( lng ) - radians(".sanitize($center_lng).") ) + sin( radians(".sanitize($center_lat).") ) * sin( radians( lat ) ) ) ) AS distance";
			$whereadd[] = "(`lat` IS NOT NULL AND `lng` IS NOT NULL)";
			$whereadd[] = "(`lat` != 'NOTFOUND' AND `lng` != 'NOTFOUND')";

            $params['where'] = implode(' AND ', $whereadd);
            //$params['having'] = implode(' AND ', $addhaves);
			$params['addselect'] = implode(', ', $addselects);
			$params['group'] = '`id`';
			$params['limit'] = '1';
						
			$dists = Vendor::find( $params );
            
            foreach ($dists as $dist)
			{					
				$returndist = ['vendor' => $dist->project_preview(), 'distance' => $dist['distance']];
			}
            
            
            $whereadd = array("`type` = 'sales'");
			//$addhaves = array("distance < ".sanitize($radius));
			$addselects[] = "( 3959 * acos( cos( radians(".sanitize($center_lat).") ) * cos( radians( lat ) ) * cos( radians( lng ) - radians(".sanitize($center_lng).") ) + sin( radians(".sanitize($center_lat).") ) * sin( radians( lat ) ) ) ) AS distance";
			$whereadd[] = "(`lat` IS NOT NULL AND `lng` IS NOT NULL)";
			$whereadd[] = "(`lat` != 'NOTFOUND' AND `lng` != 'NOTFOUND')";

            $params['where'] = implode(' AND ', $whereadd);
            //$params['having'] = implode(' AND ', $addhaves);
			$params['addselect'] = implode(', ', $addselects);
			$params['group'] = '`id`';
			$params['limit'] = '1';
						
			$sales = Vendor::find( $params );
            
            foreach ($sales as $sale)
			{					
				$returnsale = ['vendor' => $sale->project_preview(), 'distance' => $sale['distance']];
			}

			$return = ["ERRORS" => $errors, "DIST" => $returndist, "SALE" => $returnsale];
			echo json_encode($return);
			
		break;
	
		default:
			return false;
		break;
	endswitch;
	
?>