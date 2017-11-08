<?php
if (!defined('INDEX')) { require dirname(__FILE__).'/../index.php'; exit; }

$form = new FormHelper();

$dataSql = sql("SELECT `key`, `value` FROM `settings`");
while (list($key, $value) = $dataSql->fetch_row()) { $form->data[$key] = $value; }

if ($_POST) {
	foreach($__settings as $key => $row) {
		if (!array_key_exists($key, $_POST)) { continue; }
		if ($row['type'] === 'file') {
			$form->process_files($key);
		}
		else {
			$form->apply_post($key);
		}
		if ($row) {
			sql("INSERT INTO `settings` (`key`, `value`, `updated_at`) VALUES (%s, %s, %s) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`), `updated_at` = VALUES(`updated_at`)", $key, $form->data[$key], sql_time());
		}
	}
	success_message('Content was successfully saved.');
}

$pageTitle = 'Settings';
echo "<h2 class='page-title'>$pageTitle</h2>";

echo $form->start(array('multipart' => true));

$groups = array();
foreach($__settings as $key => $row) {
	$display = $row['display'];
	if ($display === false) { continue; }
	if ($display) { $groups[$display] .= $form->{$row['type']}($key, $row); }
	else { echo $form->{$row['type']}($key, $row); }
}
if ($groups) {
	echo '<ul class="nav nav-tabs">';
	$first = true;
	foreach($groups as $key => $content) {
		echo '<li'.active_if($first).'><a href="#content-'.slugify($key).'" data-toggle="tab" id="navtab">'.$form->label_from_name($key).'</a></li>';
		$first = false;
	}
	echo '</ul>';
	echo '<div class="tab-content">';

	$first = true;
	foreach($groups as $key => $content) {
		echo '<div class="tab-pane'.($first?' active':'').'" id="content-'.slugify($key).'">'.$content.'</div>';
		$first = false;
	}
	echo '</div>';
}

echo $form->submit(), $form->end();