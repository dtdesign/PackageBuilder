<?php
// pb imports
require_once(PB_DIR.'lib/data/source/Source.class.php');
require_once(PB_DIR.'lib/data/source/SourceList.class.php');
require_once(PB_DIR.'lib/system/package/PackageHelper.class.php');

// wcf imports
require_once(WCF_DIR.'lib/form/AbstractForm.class.php');

/**
 * Sets preferred packages for archive creation.
 *
 * @author	Tim Düsterhus, Alexander Ebert
 * @copyright	2009-2010 WoltLab Community
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.community.pb
 * @subpackage	form
 * @category 	PackageBuilder
 */
class PreferredPackageForm extends AbstractForm {
	public $templateName = 'preferredPackage';
	public $neededPermissions = array('user.source.general.canViewSources');

	// data
	public $cachedPackages = array();
	public $errors = array();
	public $filename = '';
	public $otherSources = false;
	public $packageDependencies = array();
	public $packages = array();
	public $source = null;
	public $sources = array();
	public $requestedPackageName = '';
	public $requestedPackageHash = '';
	public $saveSelection = 0;

	/**
	 * @see	Form::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();

		if (isset($_POST['filename'])) $this->filename = StringUtil::trim($_POST['filename']);
		if (isset($_POST['otherSources'])) $this->otherSources = (bool) $_POST['otherSources'];
		
		// read source
		if ($this->readSource() === false) throw new IllegalLinkException();
	}

	/**
	 * @see	Form::readData()
	 */
	public function readData() {
		parent::readData();
		
		// avoid problems when submit() is not called
		if ($this->source === null) throw new IllegalLinkException();
		
		// get available packages
		$this->cachedPackages = $this->getCache('packages-'.$this->source->sourceID, 'Packages');

  		// get all dependent packages
  		$this->packageDependencies = $this->getCache('package-dependency-'.$this->source->sourceID, 'PackageDependency');
  		
  		foreach($this->sources as $source) {
  			$directory = FileUtil::getRelativePath($this->source->sourceDirectory, $source->sourceDirectory);
  			
  			// packages
  			$packages = $this->getCache('packages-'.$source->sourceID, 'Packages');
	  		foreach($packages['packages'] as $key => $val) {
				$packages['packages'][$key]['directory'] = $directory.$packages['packages'][$key]['directory'];
			}
			
			$this->cachedPackages = array(
				'hashes' => array_merge($this->cachedPackages['hashes'], $packages['hashes']),
				'packages' => array_merge($this->cachedPackages['packages'], $packages['packages'])
			);
			
			// dependencies
			$this->packageDependencies = array_merge($this->packageDependencies, $this->getCache('package-dependency-'.$source->sourceID, 'PackageDependency'));
	  	}

		// get package information
		$this->getRequestedPackage();

    		// fetch all required packages
    		$this->fetchDependencies($this->requestedPackageHash);
	}

	protected function fetchPackage($packageHash, $packageName, $minVersion = '') {
		// try to find requested package
		if (!isset($this->cachedPackages['packages'][$packageHash])) {
			$this->errors[$packageHash] = array(
				'message' => 'notFound',
				'packageName' => $packageName
			);

			return;
		}

		$cachedPackage = $this->cachedPackages['packages'][$packageHash];
		if (!empty($minVersion)) {
			if (version_compare($minVersion, $cachedPackage['version'], '>')) {
				$this->errors[$packageHash] = array(
					'message' => 'insufficientVersion',
					'packageName' => $packageName
				);

				return;
			}
		}

		// ignore duplicate entries
		if (isset($this->packages[$packageName]['directories'][$cachedPackage['directory']])) {
			return;
		}

		// add current package
		$this->packages[$packageName]['hash'] = $packageHash;
		$this->packages[$packageName]['directories'][$cachedPackage['directory']] = $cachedPackage['version'];

		$this->fetchDependencies($packageHash);
	}

	protected function fetchDependencies($packageHash) {
		// check for dependencies
  		if (!isset($this->packageDependencies[$packageHash])) return;

  		// resolve dependencies
		foreach ($this->packageDependencies[$packageHash] as $package) {
			if (isset($this->cachedPackages['hashes'][$package['packageName']])) {
				foreach ($this->cachedPackages['hashes'][$package['packageName']] as $hash) {
					$this->fetchPackage($hash, $package['packageName'], $package['minVersion']);
				}
			}
		}
	}

	/**
	 * Reads a given cache
	 *
	 * @param	string	$cacheName
	 * @param	string	$cacheBuilder
	 * @return	array<array>
	 */
	protected function getCache($cacheName, $cacheBuilder) {
		WCF::getCache()->addResource(
			$cacheName,
			PB_DIR.'cache/cache.'.$cacheName.'.php',
			PB_DIR.'lib/system/cache/CacheBuilder'.$cacheBuilder.'.class.php'
		);

		return WCF::getCache()->get($cacheName);
	}
	
	/**
	 * gets the name and the hash of the requested package
	 *
	 * @return void
	 */
	protected function getRequestedPackage() {
		// get directory
		$directory = WCF::getSession()->getVar('source'.$this->source->sourceID);

		// get package name
		if ($directory !== null) {
			foreach ($this->cachedPackages['packages'] as $package) {
				if ($package['directory'] == $directory) {
					$this->requestedPackageName = $package['packageName'];
					$this->requestedPackageHash = PackageHelper::getHash($this->source->sourceID, $package['packageName'], $directory);
					return;
				}
			}
		}

		throw new IllegalLinkException();
	}

	/**
	 * Reads-in a source
	 *
	 * @return	boolean	source is valid
	 */
	public function readSource() {
		if (!isset($_POST['sourceID'])) return false;

		$this->source = new Source($_POST['sourceID']);
		if ($this->source->sourceID == 0) {
			return false;
		}

		// check permission
		if(!$this->source->hasAccess()) return false;
		
		// other sources
		if($this->otherSources) {
			$sourceList = new SourceList();
			$sourceList->checkHasAccess = true;
			$sourceList->sqlConditions = 'sourceID != '.$this->source->sourceID;
			$sourceList->readObjects();
			$this->sources = $sourceList->getObjects();
		}
	}

	public function assignVariables() {
		parent::assignVariables();

		WCF::getTPL()->assign(array(
			'errors' => $this->errors,
			'filename' => $this->filename,
			'packages' => $this->packages,
			'saveSelection' => $this->saveSelection,
			'source' => $this->source
		));
	}
}
?>