<?php
// wcf imports
require_once(WCF_DIR.'lib/page/AbstractPage.class.php');

// pb imports
require_once(PB_DIR.'lib/data/source/SourceList.class.php');

class ProfilesPage extends AbstractPage {
	// system
	public $templateName = 'profiles';
	
	public $accessibleSources = array();
	public $packages = array(
		'plugin' => array(),
		'standalone' => array()
	);
	
	/**
	 * @see	Page::readData()
	 */
	public function readData() {
		parent::readData();
		
		// get accessible sources
		$sourceList = new SourceList();
		$sourceList->sqlLimit = 0;
		$sourceList->hasAccessCheck = true;
		$sourceList->readObjects();
		
		foreach ($sourceList->getObjects() as $source) {
			$this->accessibleSources[] = $source->sourceID;
			
			$cache = $this->getCache('packages-'.$source->sourceID, 'Packages');
			foreach ($cache['packages'] as $package) {
				if (!in_array($package['packageName'], $this->packages[$package['packageType']])) {
					$this->packages[$package['packageType']][] = $package['packageName'];
				}
			}
		}
		
		sort($this->packages['plugin'], SORT_STRING);
		sort($this->packages['standalone'], SORT_STRING);
	}
	
	/**
	 * @see	Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'packages' => $this->packages
		));
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