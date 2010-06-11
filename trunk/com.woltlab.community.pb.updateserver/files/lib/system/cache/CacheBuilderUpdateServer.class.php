<?php
// pb imports
require_once(PB_DIR.'lib/data/source/Source.class.php');
require_once(PB_DIR.'lib/system/package/PackageReader.class.php');

// wcf imports
require_once(WCF_DIR.'lib/system/cache/CacheBuilder.class.php');
require_once(WCF_DIR.'lib/acp/package/Package.class.php');

/**
 * Renders the XML for update-server
 *
 * @author	Tim Düsterhus
 * @copyright	2009-2010 WoltLab Community
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.community.pb.updateserver
 * @subpackage	cache
 * @category 	PackageBuilder
 */
class CacheBuilderUpdateServer implements CacheBuilder {
	
	/**
	 * Source Object
	 *
	 * @var Source
	 */
	public $source = NULL;
	
	/**
	 * the packages found in build directory
	 *
	 * @var array<array>
	 */
	public $packages = array();
	
	/**
	 * Type of packages wanted
	 * can be stable, unstable or testing
	 *
	 * @var string
	 */
	public $type = 'stable';

	/**
	 * should the xml be intended
	 *
	 * @var boolean
	 */
	const NICE_XML = true;

	/**
	 * should there be an PackageBuilder Signature in the XML
	 *
	 * @var boolean
	 */
	const SIGNATURE = true;
	
	/**
	 * @see CacheBuilder::getData()
	 */
	public function getData($cacheResource) {
		list($cache, $sourceID, $this->type) = explode('-', $cacheResource['cache']);
		$this->source = new Source($sourceID);
		$this->readPackages();
		
		// no use of XML class of WCF because it is overkill
		return simplexml_load_string($this->renderXML());
	}
	
	/**
	 * builds the XML out of the provided data
	 *
	 * @return 	string		the built xml
	 */
	public function renderXML() {
		$w = new XMLWriter();
		$w->openMemory();
		
		// only indent when NICE_XML is true
		$w->setIndent(self::NICE_XML);
		// use tabs as indents
		$w->setIndentString("	");

		$w->startDocument("1.0", CHARSET);
			$w->startElement('section');
			$w->writeAttribute('name', 'packages');
			if (self::SIGNATURE) $w->writeComment('Generated by PackageBuilder @ '.gmdate('r'));
				foreach($this->packages as $package) {
					// if package has no valid versions just continue
					if (self::countValidVersions($package) == 0) continue;
					$generalData = self::getData($package);
					
					$w->startElement('package');
					$w->writeAttribute('name', $generalData['packageIdentifier']);
						$w->startElement('packageinformation');
							if ($generalData['packageName'] !== null) {
								$w->startElement('packagename');
									$w->writeCData($generalData['packageName']);
								$w->endElement();
							}
							if ($generalData['packageDescription'] !== null) {
								$w->startElement('packagedescription');
									$w->writeCData($generalData['packageDescription']);
								$w->endElement();
							}
							if ($generalData['plugin'] !== 'null') {
								$w->startElement('plugin');
									$w->writeCData($generalData['plugin']);
								$w->endElement();
							}
							if ($generalData['standalone']) {
								$w->startElement('standalone');
									$w->writeCData($generalData['standalone']);
								$w->endElement();
							}
						// packageinformation
						$w->endElement();

						$w->startElement('authorinformation');
							if ($generalData['author'] !== null) {
								$w->startElement('author');
									$w->writeCData($generalData['author']);
								$w->endElement();
							}
							if ($generalData['authorURL'] !== null) {
								$w->startElement('authorurl');
									$w->writeCData($generalData['authorURL']);
								$w->endElement();
							}
						// authorinformation
						$w->endElement();
						
						$w->startElement('versions');
						// list each version
						foreach($package as $key => $val) {
							// get type, dont display if this type is not wanted
							if(self::getTypeByVersion($key) != $this->type) continue;
							$data = self::getData($package, $key);

							$w->startElement('version');
							$w->writeAttribute('name', $key);

								if(!empty($data['fromVersions'])) {
									$w->startElement('fromversions');
									foreach($data['fromVersions'] as $fromVersion) {
										$w->startElement('requiredpackage');
											$w->writeCData($fromVersion);
										$w->endElement();
									}
									// fromversions
									$w->endElement();
								}

								if (!empty($data['requirements'])) {
									$w->startElement('requiredpackages');
									foreach($data['requirements'] as $required) {
										$w->startElement('requiredpackage');
											if (isset($required['minversion'])) {
												$w->writeAttribute('minversion', $required['minversion']);
											}
											$w->writeCData($required['name']);
										$w->endElement();
									}
									// requiredpackages
									$w->endElement();
								}

								// determine updatetype
								if ($data['isUpdate'] && stripos($key, 'pl')) {
									$updateType = 'security';
								}
								else if ($data['isUpdate'])  {
									$updateType = 'update';
								}
								else {
									$updateType = 'install';
								}
								$w->startElement('updatetype');
									$w->writeCData($updateType);
								$w->endElement();

								// use the build time of that package as timestamp
								$w->startElement('timestamp');
									$w->writeCData(filemtime($val['link']));
								$w->endElement();

								$w->startElement('versiontype');
									$w->writeCData($type);
								$w->endElement();

								$w->startElement('file');
									$w->writeCData(PAGE_URL.'/index.php?page=DownloadPackage&sourceID='.$this->source->sourceID.$val['filename']);
								$w->endElement();

							// version
							$w->endElement();
						}
						// versions
						$w->endElement();
					// package
					$w->endElement();
				}
			// section
			$w->endElement();
		$w->endDocument();

		return $w->outputMemory();
	}

	/**
	 * determine the version type
	 * @see self::$type
	 * 
	 * @param	string	$version	the version string to check
	 * @return	string			an string like self::$type
	 */
	public static function getTypeByVersion($version) {
		if (	stripos($version, 'a') !== false
		||	stripos($version, 'alpha') !== false
		||	stripos($version, 'b') !== false
		||	stripos($version, 'beta') !== false
		||	stripos($version, 'dev') !== false) $type = 'unstable';
		else if (stripos($version, 'rc') !== false) {
			$type = 'testing';
		}
		else {
			$type = 'stable';
		}
		
		return $type;
	}

	/**
	 * counts versions that match the current version type
	 * 
	 * @param	array<array>	$package	the package array generated in self::readPackages()
	 * @return	integer				valid versions
	 */
	public static function countValidVersions(Array $package) {
		$versions = 0;
		foreach($package as $key => $val) {
			// count versions
			if(self::getTypeByVersion($key) != $this->type) continue;
			$versions++;
		}
		return $versions;
	}

	/**
	 * Reads out PackageData
	 * 
	 * @param	array<array>	$package	the package array generated in self::readPackages()
	 * @param	mixed		$version	the version to check
	 * @param	mixed		$field		should only one information be returned?
	 * @return	mixed				either an array with data, or the data wanted in $field
	 */
	public static function getData(Array $package, $version = null, $field = null) {
		$data = array();
		
		if ($version === null) {
			// read firest package for general information
			$key = array_keys($package);
			$xml = $package[$key[0]]['xml']->getElementTree('data');
		}
		else {
			$xml = $package[$version]['xml']->getElementTree('data');
		}

		$data['packageIdentifier'] = $data['attrs']['name'];
		$data['isUpdate'] = false;
		foreach ($data['children'] as $child) {
			switch (StringUtil::toLowerCase($child['name'])) {
				// read in package information
				case 'packageinformation':
					foreach ($child['children'] as $packageInformation) {
						switch (StringUtil::toLowerCase($packageInformation['name'])) {
							case 'packagename':
								if (!isset($this->packageInfo['packageName'])) $data['packageName'] = null;

								if (!isset($data['packageName'])) $data['packageName'] = $packageInformation['cdata'];
							break;
							case 'packagedescription':
								if (!isset($this->packageInfo['packageDescription'])) $data['packageDescription'] = null;

								if (!isset($data['packageDescription'])) $data['packageDescription'] = $packageInformation['cdata'];
							break;
							case 'standalone':
								$data['standalone'] = intval($packageInformation['cdata']);
							break;
							case 'promptparent':
							case 'plugin':
								if (!Package::isValidPackageName($packageInformation['cdata'])) $data['plugin'] = null;
								$data['plugin'] = $packageInformation['cdata'];
							break;
						}
					}
				break;
				// read in author information
				case 'authorinformation':
					foreach ($child['children'] as $authorInformation) {
						switch (StringUtil::toLowerCase($authorInformation['name'])) {
							case 'author':
								$data['author'] = $authorInformation['cdata'];
							break;
							case 'authorurl':
								$data['authorURL'] = $authorInformation['cdata'];
							break;
						}
					}
				break;
				// read in requirements
				case 'requiredpackages':
					foreach ($child['children'] as $requiredPackage) {
						if (Package::isValidPackageName($requiredPackage['cdata'])) {
							$data['requirements'][$requiredPackage['cdata']] = (array('name' => $requiredPackage['cdata']) + $requiredPackage['attrs']);
						}
					}
				break;
				// get installation and update instructions
				case 'instructions':
					if ($child['attrs']['type'] == 'update') {
						$data['isUpdate'] = true;
						$data['fromVersions'][] = $child['attrs']['fromversion'];
					}				
				break;
			}
		}
		
		if ($field === null) return $data;
		else return $data[$field];
	}

	public function readPackages() {
		WCF::getCache()->addResource(
			'packages-'.$this->source->sourceID,
			PB_DIR.'cache/cache.packages-'.$this->source->sourceID.'.php',
			PB_DIR.'lib/system/cache/CacheBuilderPackages.class.php'
		);
		$packages = WCF::getCache()->get('packages-'.$this->source->sourceID);
		
		// read current builds
		// TODO: Use DirectoryUtil
		if (is_dir($this->source->buildDirectory)) {
			if ($dh = opendir($this->source->buildDirectory)) {
				while (($file = readdir($dh)) !== false) {
					if (strrpos($file, '.tar.gz') !== false) {
						$package = new PackageReader($this->source->sourceID, $this->source->buildDirectory.$file, true);
						$data = $package->getPackageData();
						$this->packages[$data['name']][$data['version']] = array(
							'filename' => $file,
							'xml' => $package->xml
						);
					}
				}

				closedir($dh);
			}
		}
	}
}
?>