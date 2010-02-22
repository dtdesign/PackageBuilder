<?php
// pb imports
require_once(PB_DIR.'lib/system/session/PBSession.class.php');

// wcf imports
require_once(WCF_DIR.'lib/system/session/CookieSessionFactory.class.php');

class PBSessionFactory extends CookieSessionFactory {	
	protected $sessionClassName = 'PBSession';
}
?>