<?php
// define paths
define('RELATIVE_PB_DIR', '../');

// include config
$packageDirs = array();
require_once(dirname(dirname(__FILE__)).'/config.inc.php');

// include WCF
require_once(RELATIVE_WCF_DIR.'global.php');
if (!count($packageDirs)) $packageDirs[] = PB_DIR;
$packageDirs[] = WCF_DIR;

// starting pb acp
require_once(PB_DIR.'lib/system/PBACP.class.php');
new PBACP();
?>