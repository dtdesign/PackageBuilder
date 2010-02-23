<?php
// wcf imports
require_once(WCF_DIR.'lib/system/scm/SCM.class.php');
require_once(WCF_DIR.'lib/system/scm/GitException.class.php');
require_once(WCF_DIR.'lib/util/FileUtil.class.php');

/**
 * Provides git access
 *
 * @author	Alexander Ebert
 * @copyright	2009-2010 WoltLab Community
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.community.scm.git
 * @subpackage	system.scm
 * @category 	PackageBuilder
 */
class Git implements SCM {
	/**
	 * @see	SCM::checkout()
	 */
	public static function checkout($url, $directory, $loginDetails, $options) {
		if (empty($directory)) throw new GitException('git clone: target directory missing.');

		// append directory
		$directory = FileUtil::unifyDirSeperator($directory);
		$options['directory'] = $directory;

		return self::executeCommand('clone', $url, $loginDetails, $options);
	}

	/**
	 * @see	SCM::getHeadRevision()
	 */
	public static function getHeadRevision($url, $loginDetails, $options) {
		throw new GitException('IMPLEMENT ME! getHeadRevision()');
	}

	/**
	 * Executes a git command
	 *
	 * @param	string		$command	Command
	 * @param	string		$url		Repository url
	 * @param	array		$loginDetails	Login details if required
	 * @param	array<array>	$options	Additional options
	 * @return	array
	 */
	protected static function executeCommand($command, $url, $loginDetails, $options) {
		self::validateGitPath();

		// break if repository url is empty
		if (empty($url)) throw new GitException('git: URL missing.');

		// handle login details
		if (isset($loginDetails['username']) || $loginDetails['password']) {
			throw new GitException('git: Access to repository with username and/or password is not yet supported.');
		}

		// handle options
		$directory = (isset($options['directory'])) ? $options['directory'] : '';

		// build complete shell command
		$shellCommand = escapeshellarg(GIT_PATH).' '.$command.' '.$url.' '.$directory.' 2>&1';

		// execute command
		exec ($shellCommand, $output);

		return $output;
	}

	/**
	 * Validates if the git path is correctly set
	 */
	private static function validateGitPath() {
		// check wether path is given
		if (!defined('GIT_PATH') || GIT_PATH == '') {
			throw new GitException('git path not set.');
		}

		// check if file exists
		if (!file_exists(GIT_PATH)) {
			throw new GitException('git path seems to be wrong, no file found.');
		}
	}
}