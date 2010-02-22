<?php
// wcf imports
require_once(WCF_DIR.'lib/system/event/EventListener.class.php');

/**
 * Provides css style information for UserLoginForm
 *
 * @package	info.dtcms.pb
 * @author	Alexander Ebert
 * @copyright	2010 Alexander Ebert IT-Dienstleistungen
 * @license	GNU Lesser Public License <http://www.gnu.org/licenses/lgpl.html>
 * @subpackage	lib.system.event.listener
 * @category	PackageBuilder
 */
class UserLoginFormListener implements EventListener {
	/**
	 * @see	EventListener::execute()
	 */
	public function execute($eventObj, $className, $eventName) {
		WCF::getTPL()->append('additionalCSS', '
			<link rel="stylesheet" type="text/css" href="'.RELATIVE_WCF_DIR.'style/containers.css" />
			<link rel="stylesheet" type="text/css" href="'.RELATIVE_WCF_DIR.'style/forms.css" />
		');
	}
}
?>