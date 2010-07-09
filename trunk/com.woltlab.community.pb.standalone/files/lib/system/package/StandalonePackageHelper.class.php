<?php
// pb imports
require_once(PB_DIR.'lib/system/package/PackageHelper.class.php');

/**
 * Providing methods for standalone packages.
 *
 * @author	Tim Düsterhus
 * @copyright	2009-2010 WoltLab Community
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.community.pb
 * @subpackage	system
 * @category 	PackageBuilder
 */
class StandalonePackageHelper extends PackageHelper {

	/**
	 * Build filenames on a given pattern
	 *
	 * @param	string	$pattern
	 * @param	array	$data
	 * @return	string
	 */
	public static function getArchiveName($pattern, $data) {
		$name = '';
		$pattern = explode('_', $pattern);

		foreach ($pattern as $part) {
			if (isset($data[$part])) {
				// append seperator
				if (!empty($name)) $name .= '_';

				$name .= str_replace(' ', '_', $data[$part]);
			}
		}

		return $name.'.zip';
	}

}
?>