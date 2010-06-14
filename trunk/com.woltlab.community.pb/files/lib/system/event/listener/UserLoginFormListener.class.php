<?php
// wcf imports
require_once(WCF_DIR.'lib/system/event/EventListener.class.php');

/**
 * Provides css style information for UserLoginForm.
 *
 * @author	Alexander Ebert
 * @copyright	2009-2010 WoltLab Community
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.community.pb
 * @subpackage	event.listener
 * @category 	PackageBuilder
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