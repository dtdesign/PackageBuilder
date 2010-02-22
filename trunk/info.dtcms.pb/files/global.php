<?php
// include config
$packageDirs = array();
require_once(dirname(__FILE__).'/config.inc.php');

// include WCF
require_once(RELATIVE_WCF_DIR.'global.php');
if (!count($packageDirs)) $packageDirs[] = PB_DIR;
$packageDirs[] = WCF_DIR;

// starting pb core
require_once(PB_DIR.'lib/system/PBCore.class.php');
new PBCore();
?>