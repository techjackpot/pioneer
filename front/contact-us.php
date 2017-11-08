<?php
if (!defined('INDEX')) { require dirname(__FILE__).'/../index.php'; exit; }

?>
<div class="page-hero contactus">
	<div class="mapcontainer" id="mapcontainer"></div>
    <h1>Contact Us</h1>
</div>
<div class="page-content">
	<div class="padd">
		<div class="headline">
			<h2>Let’s Start a Conversation</h2>
			<h5>Call or e-mail us today, so we can help you with your next project.</h5>
		</div>
        
        <div class="contactcontents">
            <div class="left">
                <form class="contact-form" method="post" autocomplete="off">
					<div class="error_msg" style="display: block;">Please fill in all required fields.</div>
					<input type="text" name="name" id="name" value="" class="" data-value="Your Name" data-required="yes">
					<input type="text" name="company" id="company" value="" class="error" data-value="Company Name" data-required="yes">
					<input type="text" name="email" id="email" value="" class="error" data-value="E-mail" data-required="yes" data-email="yes">
					<input type="text" name="phone" id="phone" value="" class="error" data-value="Phone" data-required="yes">
					<textarea name="comments" id="comments" data-value="Brief Message" class="last-child" style="height: 120px;">Brief Message</textarea>
					<div class="clear"></div>
					<div class="g-recaptcha" data-theme="light" data-sitekey="6LdR8xgUAAAAACyck59V7Hzt2dgJhYjasBJS0z7u" style="transform:scale(0.70);-webkit-transform:scale(0.70);transform-origin:0 0;-webkit-transform-origin:0 0;"></div>
					<input type="submit" name="submit" id="submit" class="button blue" value="SUBMIT" style="margin: 0px 0 0 0;">
				</form>
            </div>
            <div class="right contacttext">
                <div class="bigphone">201-933-1900</div>
                <div class="address">
                    <h6>Pioneer Industries</h6>
                    111 Kero Road<br>
                    Carlstadt, NJ 07072<br>
                    <span class="phone">T: 201-933-1900<br>
                    F: 201-933-9580</span>
                </div>
                <div class="address">
                    <h6>Pioneer Industries CENTRAL</h6>
                    221 West First Street<br>
                    Kewanee, IL 61443<br>
                    <span class="phone">T: 309-856-6000<br>
                    F: 309-852-2727</span>
                </div>
                <h6>Sales and Distribution Inquiries:</h6>
                Kevin M Koerner: <a href="mailto:kkoerner@pioneerindustries.com">kkoerner@pioneerindustries.com</a>
                <h6>Pioneer Industries</h6>
                <div class="italics bold">is a Division of Security Holdings, LLC</div>
            </div>
        </div>
             
             
    </div>
	
	<div class="maderighthere">
		<div class="america">Made. Right. Here.</div>
	</div>
	
</div>
<script type="text/javascript">
	// MAP MAP
function getMeters(i) {
	return i*1609.344;
}

var map;
var markers = [];
var minZoomLevel = 10;
var maxZoomLevel = 16;
var LatLng = {lat: 40.8289833, lng: -74.0618265};

function loadmap() {

map = new google.maps.Map(document.getElementById("mapcontainer"), {
  center: LatLng,
  zoom: 15,
  scrollwheel: false,
  disableDefaultUI: true,
  navigationControl: false,
  mapTypeControl: true,
  disableAutoPan: true,
  zoomControl: true,
  styles: [
    {
        "featureType": "landscape",
        "elementType": "geometry.fill",
        "stylers": [
            {
                "color": "#444444"
            }
        ]
    },
    {
        "featureType": "landscape.natural",
        "elementType": "geometry.fill",
        "stylers": [
            {
                "visibility": "on"
            },
            {
                "color": "#e0efef"
            }
        ]
    },
    {
        "featureType": "poi",
        "elementType": "geometry.fill",
        "stylers": [
            {
                "visibility": "on"
            },
            {
                "color": "#47bbbb"
            }
        ]
    },
    {
        "featureType": "road",
        "elementType": "geometry",
        "stylers": [
            {
                "lightness": 100
            },
            {
                "visibility": "simplified"
            }
        ]
    },
    {
        "featureType": "road",
        "elementType": "geometry.fill",
        "stylers": [
            {
                "color": "#757575"
            },
            {
                "visibility": "on"
            }
        ]
    },
    {
        "featureType": "road",
        "elementType": "labels",
        "stylers": [
            {
                "visibility": "off"
            }
        ]
    },
    {
        "featureType": "road",
        "elementType": "labels.text",
        "stylers": [
            {
                "visibility": "simplified"
            }
        ]
    },
    {
        "featureType": "road",
        "elementType": "labels.text.fill",
        "stylers": [
            {
                "visibility": "on"
            },
            {
                "color": "#f4f4f4"
            }
        ]
    },
    {
        "featureType": "road",
        "elementType": "labels.text.stroke",
        "stylers": [
            {
                "color": "#ffffff"
            },
            {
                "visibility": "off"
            }
        ]
    },
    {
        "featureType": "transit.line",
        "elementType": "geometry",
        "stylers": [
            {
                "visibility": "on"
            },
            {
                "lightness": 700
            }
        ]
    },
    {
        "featureType": "water",
        "elementType": "all",
        "stylers": [
            {
                "color": "#7dcdcd"
            }
        ]
    },
    {
        "featureType": "water",
        "elementType": "geometry.fill",
        "stylers": [
            {
                "color": "#30647f"
            }
        ]
    }
],
  
  mapTypeId: 'roadmap',
  mapTypeControlOptions: {style: google.maps.MapTypeControlStyle.DROPDOWN_MENU}
});

	var marker = new google.maps.Marker({
		position: LatLng,
		title: "Office",
		icon: 'images/mappin.png'
	});
	marker.setMap(map);

}


function doNothing() {}

loadmap();
</script>
<?php ?>