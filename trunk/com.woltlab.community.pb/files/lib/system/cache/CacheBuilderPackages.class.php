<?php
// pb imports
require_once(PB_DIR.'lib/system/package/PackageHelper.class.php');

// wcf imports
require_once(WCF_DIR.'lib/system/cache/CacheBuilder.class.php');

/**
 * Caches packages within a specific source.
 *
 * @author	Alexander Ebert
 * @copyright	2009-2010 WoltLab Community
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.community.pb
 * @subpackage	cache
 * @category 	PackageBuilder
 */
class CacheBuilderPackages implements CacheBuilder {
	/**
	 * @see CacheBuilder::getData()
	 */
	public function getData($cacheResource) {
		list($cache, $sourceID) = explode('-', $cacheResource['cache']);
		$data = array('packages' => array(), 'hashes' => array());
		
		// get associated packages
		$sql = "SELECT		packageName, version, directory, packageType
			FROM		pb".PB_N."_source_package
			WHERE		sourceID = ".intval($sourceID)."
			ORDER BY	packageName ASC";
		$result = WCF::getDB()->sendQuery($sql);
		
		// assign data ordered by package name
		while ($row = WCF::getDB()->fetchArray($result)) {
			$hash = PackageHelper::getHash($sourceID, $row['packageName'], $row['directory']);
			$row['sourceID'] = $sourceID;
			
			$data['packages'][$hash] = $row;
			$data['hashes'][$row['packageName']][] = $hash;
		}
		
		return $data;
	}
}
?>