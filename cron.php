<?php
define('INDEX', true);

require_once 'common.php';

header('Content-type: text/plain');

ChangeLog::$record_user = false;

function logger($msg) {
	echo date('H:i'), '> ', $msg , PHP_EOL;
}

//if (user_type() < User::ADMIN) { error_403(); }

$__cron_daily = [];
function cron_daily($callable) {
	global $__cron_daily;
	
	$__cron_daily[] = $callable;
}

require PROJ_ROOT.'/billing/membership_cron.php';

$last_daily = settings('cron_last_daily');

if (is_null($last_daily) || strtotime("-1 day +1 minute") > strtotime("$last_daily GMT")) {
	logger('Daily cron jobs');
	foreach($__cron_daily as $callable) {
		if (is_callable($callable)) {
			logger('Executing '.var_export($callable, true));
			$callable();
		}
	}
	settings_update('cron_last_daily', sql_time());
}
else {
	logger('Not time for daily cron yet');
}
