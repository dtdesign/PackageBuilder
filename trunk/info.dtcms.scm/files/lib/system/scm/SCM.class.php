<?php
/**
 * General API for source code management
 *
 * @author	Alexander Ebert
 * @copyright	2009-2010 Alexander Ebert IT-Dienstleistungen
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	info.dtcms.scm
 * @subpackage	system.scm
 * @category 	PackageBuilder
 */
interface SCM {
	/**
	 * Checkout or clone a repository
	 *
	 * @param	string	$url		Repository url
	 * @param	array	$loginDetails	Login details if repository does not allow anonymous access
	 * @param	array	$options	Additional options
	 */
	public static function checkout($url, $loginDetails = array(), $options = array());

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