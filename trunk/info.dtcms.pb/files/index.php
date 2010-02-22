<?php
require_once('./global.php');
RequestHandler::handle(ArrayUtil::appendSuffix($packageDirs, 'lib/'));
?>