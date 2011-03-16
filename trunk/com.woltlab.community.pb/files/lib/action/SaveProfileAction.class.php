<?php
// wcf imports
require_once(WCF_DIR.'lib/action/AbstractSecureAction.class.php');

class SaveProfileAction extends AbstractSecureAction {
	public $packages = array();
	public $profileName = '';
	public $resource = '';
	
	/**
	 * @see	Page::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_POST['packages'])) {
			$packages = JSON::decode($_POST['packages']);
			if (!is_array($packages)) $this->sendResponse('pb.build.profile.error.packages.empty', true);
			
			$this->packages = $packages;
		}
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
						(packages, profileName, resource)
				VALUES		('".escapeString(serialize($this->packages))."',
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