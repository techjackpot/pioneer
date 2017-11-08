<?php
if (!defined('INDEX')) { require dirname(__FILE__).'/../index.php'; exit; }

?>
<div class="homepage-hero-module">
    <div class="video-container">
		<div class="topright">
			<div class="myaccount"><a href="#">Search</a></div>
		</div>
		<div class="title-container">
			<h1>Big company capability.<br>Small Company Service.</h1>
			<h2>We’re big enough to handle any type and size project, yet small enough to provide superior hands-on personal service and support.</h2>
			<div class="bannernav">
				<ul>
					<li class="active"></li>
					<li></li>
					<li></li>
					<li></li>
				</ul>
			</div></div>
		<div class="homepage-hero-module">
			<video autoplay loop class="fillWidth">
				<source src="/images/pio_1.mp4" type="video/mp4" /></video>
			<div class="video-overlay-pattern">&nbsp;</div>
			<div class="video-overlay">&nbsp;</div>
		</div>
		<div class="bannermenu"><div class="continue">CONTINUE SCROLLING</div><div class="linebutton">CUSTOMER PORTAL</div></div>
	</div>
</div>
<div class="section grey leftpioneerman">
    <div class="aboutpioneer">
        <h2>Quality. Value. AND Personal attention.</h2>
        <p>This is how we manufacture the finest steel doors and frames in the industry.</p>
        <p>We go above and beyond SDI standards to make sure our doors are built to last.</p>
        <div><a class="linebutton arrowbutton" href="/about-pioneer">Learn More About Pioneer</a></div>
    </div>
</div>
<div class="section steel">
    <div class="landingmargins">
        <h2>Pioneer Products are used in some amazing places Around the world.</h2>
		<?php $projects = Project::find(['limit' => 2]); ?>
        <div class="projectshome">
			<?php foreach ($projects as $project) {
				echo $project->project_preview();
			} ?>
        </div>
        <a class="linebutton arrowbutton grey" href="/projects">See other projects</a>
    </div>
</div>
<div class="section doorsframes">
    <div class="landingmargins">
        <div class="left chalkarrow">
            <h2>Over 200 door and frame options in stock.</h2>
            <p>We are known for manufacturing the finest custom hollow metal doors for every application.</p>
            <p>Talk to one of our friendly sales representatives today for more information.</p>
        </div>
        <div class="right">
            <div class="productcallouts">
                <a href="#" class="pdoors">
                    <div><h1>Pioneer Doors</h1></div>
                </a>
                <a href="#" class="pframes">
                    <div><h1>Pioneer Frames</h1></div>
                </a>
                <a href="#" class="psystems">
                    <div><h1>Door Systems</h1></div>
                </a>
            </div>
        </div>
    </div>
</div>
<div class="section blue moreinformation">
    <div class="landingmargins">
        <h2>Looking for documents or technical information?</h2>
		<div class="presources">
			 <div class="indent iconresources">
				 <h3>Browse Resources</h3>
				 <p>Utilize our extensive technical data manual with all of the resources and information you need for hollow metal doors, frames, and assemblies.</p>
				 <div class="productcallouts">
					<a href="#" class="pdoors">
						<div><h1>Doors</h1></div>
					</a>
					<a href="#" class="pframes">
						<div><h1>Frames</h1></div>
					</a>
					<a href="#" class="psystems">
						<div><h1>Door Systems</h1></div>
					</a>
				</div>
			 </div>
			 
		</div>
		<div class="phelp">
			 <div class="indent iconhelp">
				 <h3>Still need Help?</h3>
				 <p>Call Kevin Koerner 201-548-5401 for Sales & Resources or Call 201-933-1900 for everything else.</p>
			 </div>
		</div>
    </div>
</div>
<div class="section steel">
    <div class="landingmargins">
        <h2>Pioneer Distribution and Sales Map</h2>
        <div class="themap">
            <input type="text" name="zoopcode" id="ziplookup" placeholder="Please enter your zip/postal code to find the closest location." />
        </div>
    </div>
</div>
<div class="section blue hometestimonials">
    <div class="landingmargins">
        <h2>Our Customers appreciate personalized support and attention to detail.</h2>
        <div class="testimonialslider">
            <div class="quote"><span>“</span>I look to Pioneer Industries for custom doors and frames. They'll build to spec and they never say no. Since 1978, I've depended on their quality and on-time delivery to get the job done.<span>”</span></div>
            <div class="custname">— Charlie Scott, President, <span class="company">American Door Company</span></div>
        </div>
    </div>
</div>
</div>
<div class="section steel contacthome">
    <div class="landingmargins">
		<div class="headline">
			<h2>Let’s Start a Conversation</h2>
			<h5>Call or e-mail us today, so we can help you with your next project.</h5>
		</div>
        
        <div class="contactcontents">
            <div class="left">
                <form class="contact-form" method="post" autocomplete="off">
					<div class="error_msg">
					<?php if(isset($_GET['CaptchaPass'])){?>
					<div>Message Sent</div>
					<?php }?>
					<?php if(isset($_GET['CaptchaFail'])){?>
					<div>Captcha Failed.  Please Try Again!</div>
					<?php }?>
					<?php if(isset($theerror)){?>
					<div><?=$theerror?></div>
					<?php }?>
					</div>
					<input type="text" name="name" id="name" value="<?=($name ? $name : '')?>" class="<?=($name ? 'active' : '')?>" data-value="Your Name" data-required="yes" />
					<input type="text" name="company" id="company" value="<?=($company ? $company : '')?>" class="<?=($company ? 'active' : '')?>" data-value="Company Name" data-required="yes" />
					<input type="text" name="email" id="email" value="<?=($email ? $email : '')?>" class="<?=($email ? 'active' : '')?>" data-value="E-mail" data-required="yes" data-email="yes" />
					<input type="text" name="phone" id="phone" value="<?=($phone ? $phone : '')?>" class="<?=($phone ? ' active' : '')?>" data-value="Phone" data-required="yes" />
					<textarea name="comments" id="comments" data-value="Brief Message" class="last-child<?=($comments ? ' active' : '')?>" style="height: 120px;"><?=($comments ? $comments : 'Brief Message')?></textarea>
					<div class="clear"></div>
					<div class="g-recaptcha" data-theme="light" data-sitekey="6LdR8xgUAAAAACyck59V7Hzt2dgJhYjasBJS0z7u" style="transform:scale(0.70);-webkit-transform:scale(0.70);transform-origin:0 0;-webkit-transform-origin:0 0;"></div>
					<input type="submit" name="submit" id="submit" class="button blue" value="SUBMIT" style="margin: 0px 0 0 0;" />
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
</div>
<?php ?>