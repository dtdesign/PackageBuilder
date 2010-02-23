<?php
// pb imports
require_once(PB_DIR.'lib/data/source/SourceEditor.class.php');

// wcf imports
require_once(WCF_DIR.'lib/action/AbstractAction.class.php');
require_once(WCF_DIR.'lib/system/subversion/Subversion.class.php');
require_once(WCF_DIR.'lib/util/FileUtil.class.php');

/**
 * Checks out a repository.
 *
 * @author	Alexander Ebert
 * @copyright	2009-2010 WoltLab Community
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.community.pb
 * @subpackage	action
 * @category 	PackageBuilder
 */
class SubversionCheckoutAction extends AbstractAction {
	public $rebuildPackageData = false;
	public $sourceID = 0;

	/**
	 * @see Action::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();

		if (isset($_POST['rebuildPackageData'])) $this->rebuildPackageData = true;
		if (isset($_GET['sourceID'])) $this->sourceID = intval($_GET['sourceID']);
	}

	/**
	 * @see Action::execute()
	 */
	public function execute() {
		// call execute event
		parent::execute();

		// fetch data
		$source = new SourceEditor($this->sourceID);

		// checkout repository
		$message = Subversion::checkout($source->url, $source->sourceDirectory, $source->username, $source->password);
		$revision = 0;

		// remove sourceDirectory path
		for ($i = 0, $size = sizeof($message); $i < $size; $i++) {
			$message[$i] = str_replace($source->sourceDirectory, '', FileUtil::unifyDirSeperator($message[$i]));
		}

		// this part is a bit tricky, since we remove the last line in order to
		// determine the revision number. Unfortunately if the checkout directory
		// is up to date, svn will return only one line with the revision number
		// or with the error message.

		// extract revision if possible
		if (count($message) == 1 && strpos('Revision', $message[0]) === null) {
			// subversion returned an error
			$revision = null;

			// replace lame entities from windows svn.exe error
			$message = str_replace(array("\xAF", "\xAE"), '', $message);
		}

		// omit error messages
		if ($revision !== null) {
			if (count($message) > 1) {
				$revision = array_pop($message);
			}
			else {
				// Revision string is the only line
				$revision = $message[0];
			}

			$revision = intval(substr($revision, strrpos($revision, ' ')));

			if ($revision) {
				$source->update(null, null, null, null, null, null, null, $revision);
			}
		}

		$message = implode("\n", $message);

		// log message
		$sql = "REPLACE INTO pb".PB_N."_subversion
				(sourceID, message)
				VALUES
				(".$source->sourceID.", '".escapeString($message)."')";
		WCF::getDB()->sendQuery($sql);

		// rebuild package data if requested
		if ($this->rebuildPackageData) {
			require_once(PB_DIR.'lib/system/package/PackageHelper.class.php');
			PackageHelper::readPackages($source);
		}

		// call executed event
		$this->executed();

		// forward
		HeaderUtil::redirect('index.php?page=SourceView&sourceID=' . $source->sourceID . SID_ARG_2ND_NOT_ENCODED);
		exit;
	}
}
?>