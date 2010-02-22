<?php
// pb imports
require_once(PB_DIR.'lib/data/source/Source.class.php');
require_once(PB_DIR.'lib/system/package/PackageHelper.class.php');

// wcf imports
require_once(WCF_DIR.'lib/form/AbstractForm.class.php');
require_once(WCF_DIR.'lib/util/StringUtil.class.php');

/**
 * Sets preferred packages for creating archives.
 *
 * @package	info.dtcms.pb
 * @author	Alexander Ebert
 * @copyright	2010 Alexander Ebert IT-Dienstleistungen
 * @license	GNU Lesser Public License <http://www.gnu.org/licenses/lgpl.html>
 * @subpackage	lib.form
 * @category	PackageBuilder
 */
class PreferredPackageForm extends AbstractForm {
	public $templateName = 'preferredPackage';

	// data
	public $package = '';
	public $packages = array();
	public $saveSelection = false;
	public $selectedPackages = array();
	public $source = array();
	public $sourceID = 0;

	/**
	 * @see	Page::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();

		if (isset($_REQUEST['sourceID'])) $this->sourceID = intval($_REQUEST['sourceID']);
	}

	/**
	 * @see	Page::readData()
	 */
	public function readData() {
		// register source
		$this->source = new Source($this->sourceID);
		if (!$this->source->sourceID) throw new IllegalLinkException();

		// get package directory
		$directory = WCF::getSession()->getVar('source'.$this->sourceID);
		if ($directory === null) throw new IllegalLinkException();

		// read cache
		$packages = $this->formatCache();

		// find packageName
		$name = null;
		foreach ($packages as $packageName => $data) {
			foreach ($data as $version => $directories) {
				if (in_array($directory, $directories)) {
					$name = $packageName;
					break 2;
				}
			}
		}

		// break if packageName is unknown
		if ($name === null) throw new IllegalLinkException();

		// get hash
  		$hash = PackageHelper::getHash($this->source->sourceID, $name, $directory);

		// get all referenced packages
		$sql = "SELECT	packageName, minVersion, file
			FROM	pb".PB_N."_referenced_packages
			WHERE	sourceID = ".$this->source->sourceID."
			AND	hash = '".$hash."'
			AND	file != ''";
		$result = WCF::getDB()->sendQuery($sql);

		while ($row = WCF::getDB()->fetchArray($result)) {
			// continue with next package if referenced package exists
			if (file_exists($this->source->sourceDirectory.$directory.$row['file'])) continue;

			// break if we're unable to build this package
			if (!array_key_exists($row['packageName'], $packages)) {
				throw new SystemException('Unable to build package, cannot find referenced package "'.$row['packageName'].'"');
			}

			// register package
			$this->packages[$row['packageName']] = array(
				'minVersion' => $row['minVersion'],
				'simpleHash' => sha1($row['packageName']),
				'directories' => array()
			);

			// filter available resources by version
			if (!empty($row['minVersion'])) {
	   			foreach ($packages[$row['packageName']] as $version => $directories) {
	   				if (version_compare($row['minVersion'], $version) !== 1) {
	   					$this->packages[$row['packageName']]['directories'][$version] = $directories;
	   				}
	   			}

	   			if (empty($this->packages[$row['packageName']]['directories'])) {
	   				throw new SystemException('Unable to build package, no available resource for "'.$row['packageName'].'" in version "'.$row['minVersion'].'" or later found.');
	   			}
			}
			else {
				$this->packages[$row['packageName']]['directories'] = $packages[$row['packageName']];
			}

			// add default entry to package selection
			$this->selectedPackages[$row['packageName']] = array();
		}

		// pre-select packages
		$this->readPreSelectedPackages($directory);
	}

	/**
	 * Pre-selects directory resources if applicable
	 *
	 * @param	string	$directory
	 */
	protected function readPreSelectedPackages($directory) {
		// no packages available for selection
		if (empty($this->packages)) return;

		$sql = "SELECT	packageName, hash, resourceDirectory
			FROM	pb".PB_N."_selected_packages
			WHERE	sourceID = ".$this->source->sourceID."
			AND	directory = '".escapeString($directory)."'";
		$result = WCF::getDB()->sendQuery($sql);

		while ($row = WCF::getDB()->fetchArray($result)) {
   			if (array_key_exists($row['packageName'], $this->packages)) {
       				$this->selectedPackages[$row['packageName']] = $row['resourceDirectory'];
   			}
		}

		// toggle save settings
		if (!empty($this->selectedPackages)) $this->saveSelection = true;
	}

	/**
	 * Formates cache structure
	 *
	 * @return	array<array>
	 */
	protected function formatCache() {
		$data = array();

		// register cache
		WCF::getCache()->addResource(
			'packages-'.$this->source->sourceID,
			PB_DIR.'cache/cache.packages-'.$this->source->sourceID.'.php',
			PB_DIR.'lib/system/cache/CacheBuilderPackages.class.php'
		);
		$packages = WCF::getCache()->get('packages-'.$this->source->sourceID);

		// format array structure
		foreach ($packages as $package) {
			$data[$package['packageName']][$package['version']][$package['directory']] = $package['directory'];
		}

		return $data;
	}

	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();

		WCF::getTPL()->assign(array(
			'allowSpidersToIndexThisPage' => false,
			'packages' => $this->packages,
			'saveSelection' => $this->saveSelection,
			'selectedPackages' => $this->selectedPackages,
			'source' => $this->source
		));
	}
}
?>