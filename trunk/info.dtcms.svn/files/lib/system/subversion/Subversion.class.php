<?php
// wcf imports
if (!defined('NO_IMPORTS')) {
	require_once(WCF_DIR.'lib/system/subversion/SubversionException.class.php');
}

/**
 * Subversion is a version control system.
 *
 * @package		info.dtcms.svn
 * @author		Alexander Ebert
 * @copyright	2009 Alexander Ebert IT-Dienstleistungen
 * @license		GNU Lesser Public License <http://www.gnu.org/licenses/lgpl.html>
 * @subpackage	system.subversion
 */
class Subversion {
	/**
	 * Validates if the subversion path is set and correct
	 */
	private static function validateSubversionPath() {
		// check wether path is given
		if (!defined('SUBVERSION_PATH') || SUBVERSION_PATH == '') {
			throw new SubversionException('Subversion path not set.');
		}

		// check if file exists
		if (!file_exists(SUBVERSION_PATH)) {
			throw new SubversionException('Subversion path seems to be wrong, no file found.');
		}

		// check wether a temporary folder is given
		if (!defined('SUBVERSION_TEMPORARY_FOLDER') || SUBVERSION_TEMPORARY_FOLDER == '') {
			throw new SubversionException('Missing temporary folder for subversion.');
		}

		// verify that the folder exist and is writable
		$temporaryFolder = realpath(SUBVERSION_TEMPORARY_FOLDER);
		if (!$temporaryFolder || !is_writeable($temporaryFolder)) {
			throw new SubversionException('Temporary folder for subversion does not exist or is not writable.');
		}
	}

	/**
	 * Checks out a new working copy
	 *
	 * @param	string	$url
	 * @param	string	$folder
	 * @param	string	$username
	 * @param	string	$password
	 * @param	integer	$revision
	 * @param	boolean	$trustServerCert
	 * @return	string
	 */
	public static function checkout($url, $folder, $username = null, $password = null, $revision = null, $trustServerCert = false) {
		// check for folder
		if (empty($folder)) {
			throw new SubversionException('Subersion checkout: target folder missing.');
		}

		// in order to avoid more than 2 double quotes, we replace all backslashes with forward
		// slashes, thus there's no need to escape folderpath with escapeshellarg()
		$folder = str_replace('\\', '/', $folder);

		return self::executeCommand('checkout', false, $url, $username, $password, $revision, $trustServerCert, ' '.escapeshellcmd($folder));
	}

	/**
	 * Returns the Revision of the given resource
	 *
	 * @param	string	$url
	 * @param	string	$username
	 * @param	string	$password
	 * @param	boolean	$trustServerCert
	 * @return	int
	 */
	public static function getHeadRevision($url, $username = null, $password = null, $trustServerCert = false) {
		$output = self::info($url, $username, $password, false, $trustServerCert);

		foreach ($output as $line) {
			if (preg_match('/Revision\:\ [0-9]{1,5}/isU', $line)) {
				$tmp = explode(':', $line);
				return intval($tmp[1]);
			}
		}

		return 0;
	}

	/**
	 * Returns commit information about a specific file
	 *
	 * @param	string	$url
	 * @param	string	$username
	 * @param	string	$password
	 * @param	string	$revision
	 * @param	boolean	$asXML
	 * @param	boolean	$trustServerCert
	 * @return	array
	 */
	public static function blame($url, $username = null, $password = null, $revision = null, $asXML = true, $trustServerCert = false) {
		return self::executeCommand('blame', $asXML, $url, $username, $password, $revision, $trustServerCert);
	}

	/**
	 * Returns content of a specific file
	 *
	 * @param	string	$url
	 * @param	string	$username
	 * @param	string	$password
	 * @param	string	$revision
	 * @param	boolean	$trustServerCert
	 * @return	array
	 */
	public static function cat($url, $username = null, $password = null, $revision = null, $trustServerCert = false) {
		return self::executeCommand('cat', false, $url, $username, $password, $revision, $trustServerCert);
	}

	/**
	 * Returns information for a specific file or directory
	 *
	 * @param	string	$url
	 * @param	string	$username
	 * @param	string	$password
	 * @param	boolean	$asXML
	 * @param	boolean	$trustServerCert
	 * @return	array
	 */
	public static function info($url, $username = null, $password = null, $asXML = true, $trustServerCert = false) {
		return self::executeCommand('info', $asXML, $url, $username, $password, null, $trustServerCert);
	}

	/**
	 * Performs a svn action
	 *
	 * @param	string	$action
	 * @param	boolean	$asXML
	 * @param	string	$url
	 * @param	string	$username
	 * @param	string	$password
	 * @param	integer	$revision
	 * @param	boolean	$trustServerCert
	 * @param	string	$additionalParameters
	 * @return	array
	 */
	private static function executeCommand($action, $asXML, $url, $username, $password, $revision, $trustServerCert, $additonalParameters = null) {
		self::validateSubversionPath();

		// check for url
		if (empty($url)) {
			throw new SubversionException('Subversion checkout: URL missing.');
		}

		// that's odd, we can't escape $password using escapeshellarg() since windows would
		// blow once we have more than 2 double quotes present. Anyway it is not safe to use
		// escapeshellcmd() as it wipes out special chars which are valid for passwords
		$command = escapeshellarg(SUBVERSION_PATH).' '.$action.' --non-interactive --config-dir '.SUBVERSION_TEMPORARY_FOLDER;
		if ($asXML) $command .= ' --xml';
		if ($username !== null) $command .= ' --username '.escapeshellcmd($username);
		if ($password !== null) $command .= ' --password '.$password;
		if ($revision !== null) $command .= ' --revision '.$revision;
		if ($trustServerCert) $command .= ' --trust-server-cert';
		$command .= ' '.$url;
		if ($additonalParameters !== null) $command .= $additonalParameters;
		$command .= ' 2>&1';

		exec($command, $output);

		return $output;
	}

	/**
	 * Returns all available branches
	 *
	 * @param	string	$directory
	 * @return	array
	 */
	public static function getBranches($directory) {
		return self::getDirectories($directory.'/branches/');
	}

	/**
	 * Returns all available tags
	 *
	 * @param	string	$directory
	 * @return	array
	 */
	public static function getTags($directory) {
		return self::getDirectories($directory.'/tags/');
	}

	/**
	 * Returns all sub directories
	 *
	 * @param	string	$directory
	 * @return	array
	 */
	private static function getDirectories($directory) {
		$directories = array();

		if (is_dir($directory)) {
			if ($dh = opendir($directory)) {
				while (($file == readdir($dh)) !== null) {
					if (is_dir($directory.$file)) {
						$directories[] = $file;
					}
				}

				closedir($dh);
			}
		}

		return $directories;
	}
}