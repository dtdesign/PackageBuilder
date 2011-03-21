<?php
// wcf imports
require_once(WCF_DIR.'lib/action/AbstractSecureAction.class.php');

/**
 * Loads build profiles by package name.
 *
 * @author	Alexander Ebert
 * @copyright	2009-2011 WoltLab Community
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.community.pb
 * @subpackage	action
 * @category 	PackageBuilder
 */
class LoadProfilesAction extends AbstractSecureAction {
	/**
	 * Package version hash
	 * 
	 * @var	string
	 */
	public $packageHash = '';
	
	/**
	 * Package name
	 * 
	 * @var	string
	 */
	public $packageName = '';
	
	/**
	 * @see	Action::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (!WCF::getUser()->getPermission('user.source.profiles.canUseProfiles')) {
			throw new PermissionDeniedException();
		}
		
		if (isset($_POST['packageHash'])) $this->packageHash = StringUtil::trim($_POST['packageHash']);
		if (isset($_POST['packageName'])) $this->packageName = StringUtil::trim($_POST['packageName']);
	}
	
	/**
	 * @see	Action::execute()
	 */
	public function execute() {
		parent::execute();
		
		switch ($this->packageHash) {
			case '':
				WCF::getCache()->addResource(
					'build-profiles',
					PB_DIR . 'cache/cache.build-profiles.php',
					PB_DIR . 'lib/system/cache/CacheBuilderBuildProfiles.class.php'
				);
				$cache = WCF::getCache()->get('build-profiles');
				
				$profiles = array();
				if (isset($cache[$this->packageName])) {
					$profiles = $cache[$this->packageName];
				}
				
				$json = JSON::encode($profiles);
			break;
			
			default:
				$sql = "SELECT	packages, resource
					FROM	pb".PB_N."_build_profile
					WHERE	packageName = '".escapeString($this->packageName)."'
						AND packageHash = '".escapeString($this->packageHash)."'";
				$row = WCF::getDB()->sendQuery($sql);
				
				$json = JSON::encode(array(unserialize($row['packages']), $row['resource']));
			break;
		}
		
		// send JSON response
		header('Content-type: application/json');
		echo $json;
		exit;
	}
}
?>