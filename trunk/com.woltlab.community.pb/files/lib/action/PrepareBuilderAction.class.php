<?php
// wcf imports
require_once(WCF_DIR.'lib/action/AbstractSecureAction.class.php');

// pb imports
require_once(PB_DIR.'lib/data/source/SourceList.class.php');

/**
 * Loads package data for profile builder.
 *
 * @author	Alexander Ebert
 * @copyright	2009-2011 WoltLab Community
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.community.pb
 * @subpackage	action
 * @category 	PackageBuilder
 */
class PrepareBuilderAction extends AbstractSecureAction {
	/**
	 * package hash
	 * 
	 * @var	string
	 */
	public $hash = '';
	
	public $cachedPackages = array('hashes' => array(), 'packages' => array());
	public $cachedDependencies = array();
	
	public $packages = array();
	
	/**
	 * @see	Action::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['hash'])) $this->hash = StringUtil::trim($_REQUEST['hash']);
	}
	
	/**
	 * @see	Action::execute()
	 */
	public function execute() {
		parent::execute();
		
		$this->readCache();
		$this->resolveDependencies($this->hash);
		$packages = $this->buildPackageData();
		$resources = $this->getResources();
		
		// send JSON response
		$json = JSON::encode(array($packages, $resources));
		header('Content-type: application/json');
		echo $json;
		exit;
	}
	
	/**
	 * Returns a list of WCFSetup resources.
	 * 
	 * @return	array<array>
	 */
	protected function getResources() {
		$cache = $this->getCache('wcfSetupResource', 'WcfSetupResource');
		$resources = array();
		
		$sourceList = new SourceList();
		$sourceList->sqlConditions = "source.sourceID IN (".implode(',', WCF::getUser()->getAccessibleSourceIDs()).")";
		$sourceList->sqlLimit = 0;
		$sourceList->readObjects();
		
		foreach ($sourceList->getObjects() as $source) {
			if (!isset($cache[$source->sourceID])) continue;
			
			foreach ($cache[$source->sourceID] as $resource) {
				$resources[] = array(
					'label' => $source->name . ' :: ' . FileUtil::getRelativePath($source->sourceDirectory, $resource),
					'path' => $resource
				);
			}
		}
		
		return $resources;
	}
	
	/**
	 * Restructures package data for direct display including neccessary changes
	 * for use with JSON (numerical arrays).
	 * 
	 * @return	array
	 */
	protected function buildPackageData() {
		$packages = array();
		$index = 0;
		
		foreach ($this->packages as $packageName => $packageVersions) {
			$currentIndex = $index;
			
			if (!in_array($packageName, $packages)) {
				$packages[$index++] = array(
					'packageName' => $packageName,
					'versions' => array()
				);
			}
			
			$versions = array_keys($packageVersions);
			natsort($versions);
			foreach ($versions as $version) {
				foreach ($packageVersions[$version] as $hash) {
					$packages[$currentIndex]['versions'][] = array(
						'hash' => $hash,
						'label' => $version . ' - ' . $this->cachedPackages['packages'][$hash]['directory']
					);
				}
			}
		}
		
		return $packages;
	}
	
	/**
	 * Recursively resolves dependencies based upon a package hash.
	 * 
	 * @param	string		$hash
	 */
	protected function resolveDependencies($hash) {
		if (!isset($this->cachedDependencies[$hash])) return;
		
		foreach ($this->cachedDependencies[$hash] as $dependency) {
			// validate if a package with given minversion exists
			if (isset($this->packages[$dependency['packageName']])) {
				continue;
			}
			
			// try to get packages matching package name with given minVersion
			if (!isset($this->cachedPackages['hashes'][$dependency['packageName']])) {
				throw new SystemException("Could not resolve dependencies for package '".$dependency['packageName']."'");
			}
			
			foreach ($this->cachedPackages['hashes'][$dependency['packageName']] as $packageHash) {
				$packageVersion = $this->cachedPackages['packages'][$packageHash];
				
				if (version_compare($packageVersion['version'], $dependency['minVersion'], '>=')) {
					$this->packages[$dependency['packageName']][$packageVersion['version']][] = $packageHash;
					
					// resolve dependencies for this package
					$this->resolveDependencies($packageHash);
				}
			}
			
			if (!isset($this->packages[$dependency['packageName']])) {
				throw new SystemException("Unable to satisfy dependency on package '".$dependency['packageName']."' in version '".$dependency['minVersion']."' or later.");
			}
			else {
				foreach ($this->packages[$dependency['packageName']] as &$data) {
					$data = array_unique($data);
				}
				unset($data);
			}
		}
	}
	
	/**
	 * Reads cache for all accessible sources.
	 */
	protected function readCache() {
		foreach (WCF::getUser()->getAccessibleSourceIDs() as $sourceID) {
			// packages
			$packages = $this->getCache('packages-'.$sourceID, 'Packages');
			$this->cachedPackages = array(
				'hashes' => array_merge($this->cachedPackages['hashes'], $packages['hashes']),
				'packages' => array_merge($this->cachedPackages['packages'], $packages['packages'])
			);
			
			// dependencies
			$this->cachedDependencies = array_merge($this->cachedDependencies, $this->getCache('package-dependency-'.$sourceID, 'PackageDependency'));
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
}
?>