<?php
// wcf imports
require_once(WCF_DIR.'lib/system/cache/CacheBuilder.class.php');

/**
 * Caches all source code management systems
 *
 * @author	Alexander Ebert
 * @copyright	2009-2010 WoltLab Community
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.community.scm
 * @subpackage	system.cache
 * @category 	PackageBuilder
 */
class CacheBuilderSCM implements CacheBuilder {
	/**
	 * @see CacheBuilder::getData()
	 */
	public function getData($cacheResource) {
		$data = array();

		// read available scm
		$sql = "SELECT	scm
			FROM	wcf".WCF_N."_scm";
		$result = WCF::getDB()->sendQuery($sql);

		while ($row = WCF::getDB()->fetchArray($result)) {
			$key = StringUtil::toLowerCase($row['scm']);
			$data[$key] = $row['scm'];
		}

		return $data;
	}
}
?>