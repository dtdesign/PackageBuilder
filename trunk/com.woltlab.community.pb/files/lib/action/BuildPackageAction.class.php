<?php
// pb imports
require_once(PB_DIR.'lib/data/source/Source.class.php');
require_once(PB_DIR.'lib/system/package/PackageBuilder.class.php');
require_once(PB_DIR.'lib/system/package/PackageHelper.class.php');
require_once(PB_DIR.'lib/system/package/PackageReader.class.php');

// wcf imports
require_once(WCF_DIR.'lib/action/AbstractAction.class.php');

/**
 * Build a package
 *
 * @package	info.dtcms.pb
 * @author	Alexander Ebert
 * @copyright	2009-2010 Alexander Ebert IT-Dienstleistungen
 * @license	GNU Lesser Public License <http://www.gnu.org/licenses/lgpl.html>
 * @subpackage	action
 * @category	PackageBuilder
 */
class BuildPackageAction extends AbstractAction {
	/**
	 * Target package resource location
	 *
	 * @var	string
	 */
	public $directory = '';

	/**
	 * Holds data for all referenced packages
	 *
	 * @var	array<array>
	 */
	public $packages = array();

	/**
	 * Save selection permanently
	 *
	 * @var	boolean
	 */
	public $saveSelection = false;

	/**
	 * Source object
	 *
	 * @var	Source
	 */
	public $source;

	/**
	 * @see Action::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();

		if (isset($_POST['saveSelection'])) $this->saveSelection = true;
		if (isset($_POST['sourceID'])) $this->source = new Source($_POST['sourceID']);

		// read selected resources
		$this->readPackageSelection();

		// handle current directory resource
		$this->directory = WCF::getSession()->getVar('source'.$this->source->sourceID);
		if ($this->directory === null) throw new SystemException('Resource directory missing');
	}

	/**
	 * Reads selected package resources
	 */
	protected function readPackageSelection() {
		if (!isset($_POST['packages']) || !is_array($_POST['packages'])) return;

		// handle package selection
		foreach ($_POST['packages'] as $package) {
			list($hash, $packageName) = explode('-', $package, 2);

			if (isset($_POST[$hash])) {
				$this->packages[$packageName] = array(
					'hash' => $hash,
					'directory' => $_POST[$hash]
				);
			}
		}
	}

	/**
	 * @see Action::execute()
	 */
	public function execute() {
		// call execute event
		parent::execute();

		// save selection
		if ($this->saveSelection) {
			$sql = '';

			foreach ($this->packages as $packageName => $packageData) {
				if (!empty($sql)) $sql .= ',';

				$sql .= "(".$this->source->sourceID.",
					'".escapeString($this->directory)."',
					'".escapeString($packageName)."',
					'".escapeString($packageData['hash'])."',
					'".escapeString($packageData['directory'])."'
					)";
			}

			if (!empty($sql)) {
				$sql = "INSERT INTO	pb".PB_N."_selected_packages
							(sourceID,
							directory,
							packageName,
							hash,
							resourceDirectory)
					VALUES		".$sql."
					ON DUPLICATE KEY UPDATE resourceDirectory = VALUES(resourceDirectory)";
				WCF::getDB()->sendQuery($sql);
			}
		}

		// set package resources
		PackageHelper::registerPackageResources($this->packages);

		// read package
		$pr = new PackageReader($this->source->sourceID, $this->directory);

		// build package
		$pkg = new PackageBuilder($this->source->sourceID, $pr, $this->directory, '.svn');

		// clear previously created archives
		PackageHelper::clearTemporaryFiles();

		// call executed event
		$this->executed();

		// forward
		HeaderUtil::redirect('index.php?page=SourceView&sourceID='.$this->source->sourceID.SID_ARG_2ND_NOT_ENCODED);
		exit;
	}
}
?>