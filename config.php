<?php
if (!defined('INDEX')) { require dirname(__FILE__).'/index.php'; exit; }
define('DEV', true);
ini_set("memory_limit", "256M");
ini_set('session.gc_maxlifetime', 60 * 60 * 12); // 12 hours
ini_set("session.cache_expire", 60 * 60 * 24); // 24 hours


// Define database connection information
define("MYSQL_HOST","localhost");
define("MYSQL_USER","root");
define("MYSQL_PASS","root");
define("MYSQL_NAME","quickshi_live");

$mysql = new mysqli(MYSQL_HOST, MYSQL_USER, MYSQL_PASS, MYSQL_NAME);
$mysql->set_charset("utf8");

define('PROJ_ROOT', __DIR__);
define('ADMIN_EMAIL', 'mdorf@pioneerindustries.com');
define('DEV_EMAIL', 'kenh@dzineit.net');
define('SUPERVISOR_EMAIL', "mattdorf@pioneerindustries.com");
define('FROM_EMAIL', 'no-reply@pioneerindustries.com');
define('MAILDEV', true); // If defined, all SMTP mail gets sent to DEV_EMAIL
define('SITE_URL', 'http://dev.pioneer.mydzineit');
define('QS_URL', 'http://quickship.pioneerindustries.com');
define('SITE_TITLE', 'Pioneer Industries');
define('BASE_PATH', '/');
define('SITEID', 'PIONEER');

define('HOSTNAME', str_replace('www.', '', $_SERVER['SERVER_NAME']));

error_reporting(1);
