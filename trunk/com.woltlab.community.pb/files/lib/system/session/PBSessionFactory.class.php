<?php
// pb imports
require_once(PB_DIR.'lib/system/session/PBSession.class.php');
require_once(PB_DIR.'lib/system/session/PBUserSession.class.php');

// wcf imports
require_once(WCF_DIR.'lib/system/session/CookieSessionFactory.class.php');

/**
 * Session factory to create PackageBuilder sessions
 *
 * @author	Alexander Ebert
 * @copyright	2009-2010 WoltLab Community
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.community.pb
 * @subpackage	session
 * @category 	PackageBuilder
 */
class PBSessionFactory extends CookieSessionFactory {
	protected $guestClassName = 'PBUserSession';
	protected $sessionClassName = 'PBSession';
	protected $userClassName = 'PBUserSession';
}
?>