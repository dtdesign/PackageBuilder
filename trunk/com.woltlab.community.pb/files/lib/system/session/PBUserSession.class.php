<?php
// wcf imports
require_once(WCF_DIR.'lib/system/session/UserSession.class.php');

/**
 * UserSession implementation.
 *
 * @author	Alexander Ebert
 * @copyright	2009-2010 WoltLab Community
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.community.pb
 * @subpackage	session
 * @category 	PackageBuilder
 */
class PBUserSession extends UserSession {
	protected $accessibleSourceIDs = null;
	
	public function getAccessibleSourceIDs() {
		if ($this->accessibleSourceIDs === null) {
			$this->accessibleSourceIDs = array();
			
			$sql = "SELECT	sourceID
				FROM	pb".PB_N."_source";
			$result = WCF::getDB()->sendQuery($sql);
			while ($row = WCF::getDB()->fetchArray($result)) {
				if ($this->getPermission('user.source.dynamic.canUseSource'.$row['sourceID'])) {
					$this->accessibleSourceIDs[] = $row['sourceID'];
				}
			}
		}
		
		return $this->accessibleSourceIDs;
	}
}
?>