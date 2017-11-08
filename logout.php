<?php
if (!defined('INDEX')) { require dirname(__FILE__).'/index.php'; exit; }

if (!is_logged()) { redirect_to('/admin'); }

unset($_SESSION['uid']);
unset($_SESSION['AUID']);
unset($_SESSION['OID']);
success_message('You have been logged out');
redirect_to( '/' );