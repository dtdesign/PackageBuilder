<?php
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
		$data = array();

		// get associated packages
		$sql = "SELECT	packageName,version,directory
				FROM	pb".PB_N."_sources_packages
				WHERE	sourceID = ".intval($sourceID)."
				ORDER	BY packageName";
		$result = WCF::getDB()->sendQuery($sql);

		// assign data ordered by package name
		while ($row = WCF::getDB()->fetchArray($result)) {
			$data[] = $row;
		}

		return $data;
	}
}
?>