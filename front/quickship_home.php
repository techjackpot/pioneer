<?php
if (!defined('INDEX')) { require dirname(__FILE__).'/../index.php'; exit; }

if (is_logged())
redirect_to('/guidelines');

?>
<div class="margins">
    <div class="padding">
        <div class="qscontainer redstripbottom">
            <div class="largebanner banner1">&nbsp;</div>
            <div class="qshomecontent">
                <h1>Welcome To The New Pioneer Quick Ship Program.</h1>
                <p>Please log in using the secure form below to access your account. If you need access or have any questions please <a href="http://pioneerindustries.com/contact-us" target="_blank">contact us</a>.</p>
                <?php include dirname(__FILE__).'/../loginfront.php'; ?>
            </div>
        </div>
    </div>
</div>
<?php ?>