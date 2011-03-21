<?php
// wcf imports
require_once(WCF_DIR.'lib/action/AbstractSecureAction.class.php');

/**
 * Creates a new build profile.
 * 
 * @author	Alexander Ebert
 * @copyright	2009-2011 WoltLab Community
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.community.pb
 * @subpackage	action
 * @category 	PackageBuilder
 */
class SaveProfileAction extends AbstractSecureAction {
	/**
	 * list of associated packages
	 * 
	 * @var	array
	 */
	public $packages = array();
	
	/**
	 * target package hash
	 * 
	 * @var	string
	 */
	public $packageHash = '';
	
	/**
	 * target package name
	 * 
	 * @var	string
	 */
	public $packageName = '';
	
	/**
	 * profile name
	 * 
	 * @var	string
	 */
	public $profileName = '';
	
	/**
	 * WCFSetup resource
	 * 
	 * @var	string
	 */
	public $resource = '';
	
	/**
	 * @see	Page::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (!WCF::getUser()->getPermission('user.source.profiles.canManageProfiles')) {
			throw new PermissionDeniedException();
		}
		
		if (isset($_POST['packages'])) {
			$packages = JSON::decode($_POST['packages']);
			if (!is_array($packages)) $this->sendResponse('pb.build.profile.error.packages.empty', true);
			
			$this->packages = $packages;
		}
		if (isset($_POST['packageHash'])) $this->packageHash = StringUtil::trim($_POST['packageHash']);
		if (isset($_POST['packageName'])) $this->packageName = StringUtil::trim($_POST['packageName']);
		if (isset($_POST['profileName'])) {
			$this->profileName = StringUtil::trim($_POST['profileName']);
			if (empty($this->profileName)) $this->sendResponse('wcf.global.error.empty', true);
		}
		if (isset($_POST['resource'])) $this->resource = StringUtil::trim($_POST['resource']);
	}
	
	/**
	 * @see	Action::execute()
	 */
	public function execute() {
		parent::execute();
		
		$response = array();
		
		// validate profile name
		$sql = "SELECT	COUNT(*) AS count
			FROM	pb".PB_N."_build_profile
			WHERE	profileName = '".escapeString($this->profileName)."'";
		$row = WCF::getDB()->getFirstRow($sql);
		
		if ($row['count'] == 0) {
			// create new profile
			$sql = "INSERT INTO	pb".PB_N."_build_profile
						(packages, packageHash, packageName, profileHash, profileName, resource)
				VALUES		('".escapeString(serialize($this->packages))."',
						'".escapeString($this->packageHash)."',
						'".escapeString($this->packageName)."',
						'".escapeString(StringUtil::getRandomID())."',
						'".escapeString($this->profileName)."',
						'".escapeString($this->resource)."')";
			WCF::getDB()->sendQuery($sql);
			
			// clear cache
			WCF::getCache()->clear(PB_DIR . 'cache/', 'cache.build-profiles.php');
			
			// call executed event
			$this->executed();
			
			// send notification
			$this->sendResponse('pb.build.profile.success');
		}
		else {
			// profile is not unique
			$this->sendResponse('pb.build.profile.error.notUnique', true);
		}
	}
	
	/**
	 * Sends a JSON-encoded response.
	 * 
	 * @param	string		$lang
	 * @param	boolean		$isError
	 */
	protected function sendResponse($lang, $isError = false) {
		$type = ($isError) ? 'error' : 'success';
		$json = JSON::encode(array($type => WCF::getLanguage()->get($lang)));
		
		// send JSON response
		header('Content-type: application/json');
		echo $json;
		exit;
	}
}
?>