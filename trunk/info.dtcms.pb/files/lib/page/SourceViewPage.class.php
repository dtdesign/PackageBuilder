<?php
// pb imports
require_once(PB_DIR.'lib/system/package/PackageHelper.class.php');
require_once(PB_DIR.'lib/system/package/PackageReader.class.php');

// wcf imports
require_once(WCF_DIR.'lib/page/AbstractPage.class.php');
require_once(WCF_DIR.'lib/util/FileUtil.class.php');

/**
 * Shows details for a given source.
 *
 * @package		info.dtcms.pb
 * @author		Alexander Ebert
 * @copyright	2009 Alexander Ebert IT-Dienstleistungen
 * @license		GNU Lesser Public License <http://www.gnu.org/licenses/lgpl.html>
 * @subpackage	lib.page
 * @category	PackageBuilder
 */
class SourceViewPage extends AbstractPage {
	public $templateName = 'sourceView';

	// data
	public $builds = array();
	public $currentDirectory = '';
	public $currentFilename = 'pn_pv';
	public $directories = array();
	public $filenames = array();
	public $packages = array();
	public $source = array();
	public $sourceID = 0;

	/**
	 * @see	Page::readParameters()
	 */
	public function readParameters() {
		if (isset($_GET['sourceID'])) $this->sourceID = intval($_GET['sourceID']);
	}

	/**
	 * @see	Page::readData()
	 */
	public function readData() {
		// read database entry
		$sql = "SELECT		source.*, svn.message
			FROM		pb".PB_N."_sources source
			LEFT JOIN	pb".PB_N."_subversion svn
			ON			(source.sourceID = svn.sourceID)
			WHERE		source.sourceID = ".$this->sourceID;
		$this->source = WCF::getDB()->getFirstRow($sql);

		// fetch available revision if subversion is used
		if ($this->source['useSubversion']) {
			require_once(WCF_DIR.'lib/system/subversion/Subversion.class.php');
			$availableRevision = Subversion::getHeadRevision($this->source['url'], $this->source['username'], $this->source['password']);
			$this->source['availableRevision'] = $availableRevision;
		}

		// read cache
		WCF::getCache()->addResource(
			'packages-'.$this->source['sourceID'],
			PB_DIR.'cache/cache.packages-'.$this->source['sourceID'].'.php',
			PB_DIR.'lib/system/cache/CacheBuilderPackages.class.php'
		);
		$packages = WCF::getCache()->get('packages-'.$this->source['sourceID']);

		// handle packages
		foreach ($packages as $package) {
			$this->directories[$package['packageName']][$package['directory']] = $package['version'].' - '.$package['directory'];
			$this->packages[$package['directory']] = array(
				'packageName' => $package['packageName'],
				'version' => $package['version']
			);
		}

		// set current sourceDirectory
		$currentDirectory = WCF::getSession()->getVar('source'.$this->source['sourceID']);

		// set current filename
		$currentFilename = WCF::getSession()->getVar('filename'.$this->source['sourceID']);

		if ($currentDirectory !== null) {
			$this->currentDirectory = $currentDirectory;
		}

		if ($currentFilename !== null) {
			$this->currentFilename = $currentFilename;
		}

		// read current builds
		if (is_dir($this->source['buildDirectory'])) {
			if ($dh = opendir($this->source['buildDirectory'])) {
				while (($file = readdir($dh)) !== false) {
					if (strrpos($file, '.tar.gz') !== false) {
						$package = new PackageReader($this->source['sourceID'], $this->source['buildDirectory'].$file, true);
						$data = $package->getPackageData();
						$link = str_replace(FileUtil::unifyDirSeperator(PB_DIR), '', $this->source['buildDirectory']);

						$this->builds[] = array(
							'link' => $link.$file,
							'filename' => $file,
							'name' => $data['name'],
							'version' => $data['version']
						);
					}
				}

				closedir($dh);
			}
		}
	}

	/**
	 * Recursivly search folders for package.xml
	 *
	 * @param	string	$directory
	 * @param	integer	$maxDimension
	 */
	private function readDirectories($directory, $maxDimension) {
		// scan current dir for package.xml
		if (file_exists($directory.'/package.xml')) {
			$directory = str_replace($this->source['sourceDirectory'], '', $directory);;
			$this->directories[$directory] = $directory;
		}
		else if ($maxDimension) {
			if (is_dir($directory)) {
				if ($dh = opendir($directory)) {
					$maxDimension--;

					while (($file = readdir($dh)) !== false) {
						if (!in_array($file, array('.', '..', '.svn'))) {
							$this->readDirectories($directory.'/'.$file, $maxDimension);
						}
					}

					closedir($dh);
				}
			}
		}
	}

	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();

		// assign package name
		$this->generateArchiveName(array(
			'pn',		// packageName.tar.gz
			'pn_pv',	// packageName_packageVersion.tar.gz
			'pn_pr',	// packageName_packageRevision.tar.gz
			'pn_pv_pr'	// packageName_packageVersion_packageRevision.tar.gz
		));

		// assign variables to template
		WCF::getTPL()->assign(array(
			'allowSpidersToIndexThisPage' => false,
			'builds' => $this->builds,
			'currentDirectory' => $this->currentDirectory,
			'currentFilename' => $this->currentFilename,
			'directories' => $this->directories,
			'filenames' => $this->filenames,
			'source' => $this->source
		));
	}

	/**
	 * Builds any combination of archive names
	 *
	 * @param	string	$pattern
	 */
	protected function generateArchiveName($pattern) {
		// recursively call method if pattern is an array
		if (is_array($pattern)) {
			foreach ($pattern as $filename) {
				$this->generateArchiveName($filename);
			}

			return;
		}

		// dummy values
		if (empty($this->currentDirectory) || !isset($this->packages[$this->currentDirectory])) {
			$data = array(
				'pn' => 'packageName',
				'pv' => 'packageVersion',
				'pr' => 'revision'
			);
		}
		else {
			$data = array(
				'pn' => $this->packages[$this->currentDirectory]['packageName'],
				'pv' => $this->packages[$this->currentDirectory]['version'],
				'pr' => 'r'.$this->source['revision']
			);
		}

		// get filename
		$this->filenames[$pattern] = PackageHelper::getArchiveName($pattern, $data);
	}
}
?>