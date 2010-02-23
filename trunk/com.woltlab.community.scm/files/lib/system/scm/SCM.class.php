<?php
/**
 * General API for source code management
 *
 * @author	Alexander Ebert
 * @copyright	2009-2010 WoltLab Community
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.community.scm
 * @subpackage	system.scm
 * @category 	PackageBuilder
 */
interface SCM {
	/**
	 * Checkout or clone a repository
	 *
	 * @param	string	$url		Repository url
	 * @param	string	$directory	Target directory
	 * @param	array	$loginDetails	Login details if repository does not allow anonymous access
	 * @param	array	$options	Additional options
	 */
	public static function checkout($url, $directory, $loginDetails = array(), $options = array());

	/**
	 * Returns latest repository revision identifier
	 *
	 * @param	string	$url		Repository url
	 * @param	array	$loginDetails	Login details if repository does not allow anonymous access
	 * @param	array	$options	Additionals options
	 * @return	string	Revision identifier
	 */
	public static function getHeadRevision($url, $loginDetails = array(), $options = array());
}
?>