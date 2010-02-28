<?php
// wcf imports
require_once(WCF_DIR.'lib/system/scm/SCM.class.php');

/**
 * Does nothing, but is required for sources that use the "None" scm
 *
 * @author	Tim Düsterhus
 * @copyright	2009-2010 WoltLab Community
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.community.scm
 * @subpackage	system.scm
 * @category 	PackageBuilder
 */
class None implements SCM {
	/**
	 * @see	SCM::checkout()
	 */
	public static function checkout($url, $directory, $loginDetails = array(), $options = array()) {
		return;
	}

	/**
	 * @see	SCM::getHeadRevision()
	 */
	public static function getHeadRevision($url, $loginDetails = array(), $options = array()) {
		return;
	}
}
?>