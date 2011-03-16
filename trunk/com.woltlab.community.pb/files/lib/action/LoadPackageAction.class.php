<?php
// wcf imports
require_once(WCF_DIR.'lib/action/AbstractSecureAction.class.php');

/**
 * Loads package versions for a given package.
 *
 * @author	Alexander Ebert
 * @copyright	2009-2011 WoltLab Community
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.community.pb
 * @subpackage	action
 * @category 	PackageBuilder
 */
class LoadPackageAction extends AbstractSecureAction {
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
		
		if (isset($_POST['packageName'])) $this->packageName = StringUtil::trim($_POST['packageName']);
	}
	
	/**
	 * @see	Action::execute()
	 */
	public function execute() {
		parent::execute();
		
		$packages = array();
		$sql = "SELECT	hash, version, directory
			FROM	pb".PB_N."_source_package
			WHERE	packageName = '".escapeString($this->packageName)."'";
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			$packages[] = $row;
		}
		
		$packages = self::orderVersions($packages);
		
		$json = JSON::encode($packages);
		
		// send JSON response
		header('Content-type: application/json');
		echo $json;
		exit;
	}
	
	/**
	 * Orders versions (DESC)
	 * 
	 * @param	array		$versions
	 * @return	array
	 */
	protected static function orderVersions(array $versions) {
		usort($versions, array('self', 'compareVersion'));
		return array_reverse($versions);
	}
	
	/**
	 * Compares two version number strings.
	 * 
	 * @see version_compare()
	 */
	protected static function compareVersion($versionObj1, $versionObj2) {
		$version1 = self::formatVersionForCompare($versionObj1['version']);
		$version2 = self::formatVersionForCompare($versionObj2['version']);
		
		return version_compare($version1, $version2, '>');
	}
	
	protected static function formatVersionForCompare($version) {
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