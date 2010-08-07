<?php
// wcf imports
require_once(WCF_DIR.'lib/system/scm/SCM.class.php');
require_once(WCF_DIR.'lib/system/scm/GitException.class.php');

/**
 * Provides git access
 *
 * @author	Tim Düsterhus, Alexander Ebert
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
	public static function checkout($url, $directory, Array $loginDetails = array(), Array $options = array()) {
		self::validateGitPath();
		if (empty($directory)) throw new GitException('git clone: target directory missing.');

		// append directory
		$directory = FileUtil::unifyDirSeperator($directory);
		// gets the directory git will create
		$dir = explode('/', $url);
		$dir = str_replace('.git', '', $dir[(count($dir) - 1)]);
		if (file_exists(FileUtil::addTrailingSlash($directory).$dir)) {
			$dir = DirectoryUtil::getInstance(FileUtil::addTrailingSlash($directory).$dir);
			$dir->removeComplete();
		}

		chdir(FileUtil::addTrailingSlash($directory));
		$shellCommand = escapeshellarg(GIT_PATH).' clone '.$url.' 2>&1';
		// execute command
		exec ($shellCommand, $output);
		return $output;
	}

	/**
	 * @see	SCM::getHeadRevision()
	 */
	public static function getHeadRevision($url, Array $loginDetails = array(), Array $options = array()) {
		try {
			self::validateGitPath();
			// not very nice or fast method to find out, but it should work
			self::checkout($url, GIT_TEMPORARY_DIRECTORY, $loginDetails, $options);
			$dir = explode('/', $url);
			$dir = str_replace('.git', '', $dir[(count($dir) - 1)]);
			$headdir = explode(" ", file_get_contents(FileUtil::addTrailingSlash(FileUtil::unifyDirSeperator(GIT_TEMPORARY_DIRECTORY)).$dir.'/.git/HEAD'));
			$return = file_get_contents(FileUtil::addTrailingSlash(FileUtil::unifyDirSeperator(GIT_TEMPORARY_DIRECTORY)).$dir.'/.git/'.trim($headdir[1]));
			$dir = DirectoryUtil::getInstance(FileUtil::addTrailingSlash(GIT_TEMPORARY_DIRECTORY).$dir);
			$dir->removeComplete();
			return $return;
		}
		catch(GitException $e) {
			throw $e;
		}
		catch(SystemException $e) {

		}
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
	protected static function executeCommand($command, $url, $loginDetails, Array $options = array()) {
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

		// check wether a temporary directory is given
		if (!defined('GIT_TEMPORARY_DIRECTORY') || GIT_TEMPORARY_DIRECTORY == '') {
			throw new GitException('Missing temporary folder for git.');
		}

		// verify that the folder exist and is writable
		$temporaryDirectory = realpath(GIT_TEMPORARY_DIRECTORY);
		if (!$temporaryDirectory || !is_writeable($temporaryDirectory)) {
			throw new GitException('Temporary directory for git does not exist or is not writable.');
		}
	}
}
?>