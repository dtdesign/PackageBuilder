<?php
// wcf imports
require_once(WCF_DIR.'lib/system/cache/CacheBuilder.class.php');

/**
 * Caches package dependency within a given source.
 *
 * @author	Alexander Ebert
 * @copyright	2009-2010 WoltLab Community
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.community.pb
 * @subpackage	cache
 * @category 	PackageBuilder
 */
class CacheBuilderPackageDependency implements CacheBuilder {
	/**
	 * @see CacheBuilder::getData()
	 */
	public function getData($cacheResource) {
		list($dummy, $dummy2, $sourceID) = explode('-', $cacheResource['cache']);
		$data = array();

		// get referenced packages
		$sql = "SELECT	*
			FROM	pb".PB_N."_referenced_packages
			WHERE	sourceID = ".intval($sourceID)."
			AND	file != ''";
		$result = WCF::getDB()->sendQuery($sql);

		// assign data ordered by package name
		while ($row = WCF::getDB()->fetchArray($result)) {
			$hash = $row['hash'];
			unset($row['hash']);

			$data[$hash][] = $row;
		}

		return $data;
	}
}
?>