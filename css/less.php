<?php

require_once 'lessc.inc.php';

$filename = basename($_SERVER['SCRIPT_URL'], '.css');
if (!$filename) {
	list($filename) = explode('?', $_SERVER['REQUEST_URI'], 2);
	$filename = basename($filename, '.css');
}

function autoCompileLess($inputFile, $outputFile) {
  // load the cache
  $cacheFile = $inputFile.".cache";

  if (file_exists($cacheFile)) {
    $cache = unserialize(file_get_contents($cacheFile));
  } else {
    $cache = $inputFile;
  }

  $less = new lessc;
  $newCache = $less->cachedCompile($cache);

  if (!is_array($cache) || $newCache["updated"] > $cache["updated"]) {
    file_put_contents($cacheFile, serialize($newCache));
    file_put_contents($outputFile, $newCache['compiled']);
  }
}

autoCompileLess("$filename.less", "$filename.css");

header('Content-Type: text/css');
readfile("$filename.css");
