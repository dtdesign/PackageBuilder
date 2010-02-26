<?php
/**
 * Handles scm cache.
 *
 * @author	Alexander Ebert
 * @copyright	2009-2010 WoltLab Community
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.community.scm
 * @subpackage	system.scm
 * @category 	PackageBuilder
 */
class SCMHelper {
	/**
	 * Stores known scm
	 *
	 * @var	array
	 */
	protected static $data = array();

	/**
	 * Returns class name if scm is known
	 *
	 * @param	string	$scm	Source Code Management System
	 * @return	string	Class name
	 */
	public static function getSCM($scm = '') {
		$scm = StringUtil::toLowerCase($scm);

		if (empty($scm)) {
			return self::$data;
		}

		if (isset(self::$data[$scm])) {
			return self::$data[$scm];
		}

		return null;
	}

	/**
	 * Registers cache
	 */
	protected static function registerCache() {
		WCF::getCache()->addResource(
			'scm',
			WCF_DIR.'cache/cache.scm.php',
			WCF_DIR.'lib/system/cache/CacheBuilderSCM.class.php'
		);
	}

	/**
	 * Read cached data
	 */
	protected static function getCache() {
		if (!empty(self::$data)) return;

		self::registerCache();
		self::$data = WCF::getCache()->get('scm');
	}

	/**
	 * Deletes cache
	 */
	public static function clearCache() {
		if (empty(self::$data)) self::registerCache();

		WCF::getCache()->clearResource('scm');
		WCF::getCache()->clear(WCF_DIR.'cache/', 'cache.scm.php');

		self::$data = array();
	}
}
?>