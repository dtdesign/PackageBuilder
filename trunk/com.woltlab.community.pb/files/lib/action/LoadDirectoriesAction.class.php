<?php
// wcf imports
require_once(WCF_DIR.'lib/action/AbstractAction.class.php');

/**
 * Loads directory data for a given package.
 *
 * @author	Alexander Ebert
 * @copyright	2009-2011 WoltLab Community
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.community.pb
 * @subpackage	action
 * @category 	PackageBuilder
 */
class LoadDirectoriesAction extends AbstractAction {
	/**
	 * package name
	 * 
	 * @var	string
	 */
	public $packageName = '';
	
	/**
	 * source id
	 * 
	 * @var	integer
	 */
	public $sourceID = 0;
	
	/**
	 * @see	Action::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_POST['packageName'])) $this->packageName = StringUtil::trim($_POST['packageName']);
		if (isset($_POST['sourceID'])) $this->sourceID = intval($_POST['sourceID']);
	}
	
	/**
	 * @see	Action::execute()
	 */
	public function execute() {
		parent::execute();
		
		$directories = array();
		$sql = "SELECT	directory, version
			FROM	pb".PB_N."_source_package
			WHERE	sourceID = ".$this->sourceID."
				AND packageName = '".escapeString($this->packageName)."'";
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			$directories[] = array(
				'path' => $row['directory'],
				'version' => $row['version']
			);
		}
		
		usort($directories, array('self', 'compareVersion'));
		$directories = array_reverse($directories);
		
		$json = JSON::encode($directories);
		
		// send JSON response
		header('Content-type: application/json');
		echo $json;
		exit;
	}
	
	/
	protected function orderVersions($directories) {
		
	}
	
	public static function compareVersion($versionObj1, $versionObj2) {
		$version1 = self::formatVersionForCompare($versionObj1['version']);
		$version2 = self::formatVersionForCompare($versionObj2['version']);
		
		return version_compare($version1, $version2, '>');
	}
	
	private static function formatVersionForCompare($version) {
		// remove spaces
		$version = str_replace(' ', '', $version);
		
		// correct special version strings
		$version = str_ireplace('dev', 'dev', $version);
		$version = str_ireplace('alpha', 'alpha', $version);
		$version = str_ireplace('beta', 'beta', $version);
		$version = str_ireplace('RC', 'RC', $version);
		$version = str_ireplace('pl', 'pl', $version);
		
		return $version;
	}
}
?>