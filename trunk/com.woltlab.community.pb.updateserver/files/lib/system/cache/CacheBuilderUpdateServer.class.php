<?php
// pb imports
require_once(PB_DIR.'lib/data/source/Source.class.php');
require_once(PB_DIR.'lib/system/package/PackageReader.class.php');

// wcf imports
require_once(WCF_DIR.'lib/system/cache/CacheBuilder.class.php');

class CacheBuilderUpdateServer implements CacheBuilder {
	
	/**
	 * Source Object
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
	 * @see CacheBuilder::getData()
	 */
	public function getData($cacheResource) {
		list($cache, $sourceID, $this->type) = explode('-', $cacheResource['cache']);
		$this->source = new Source($sourceID);
		$this->readPackages();
		return simplexml_load_string($this->renderXML());
	}
	/**
	 * builds the XML out of the provided data
	 *
	 * @return 	string		the built xml
	 */
	public function renderXML() {
		$xml = '<?xml version="1.0" encoding="utf-8"?>';
		$xml .= "\n".'<section name="packages">';
		foreach($this->packages as $package) {
			foreach($package as $key => $val) {
				// get the data of the first package for general data (packagename, description, authorinfos etc.)
				break;
			}
			$data = $package[$key]['xml']->getElementTree('data');
			$xml .= "\n\t".'<package name="'.$data['attrs']['name'].'">';
			$xml .= "\n\t\t<packageinformation>";
			// packageinformation
			foreach($data['children'] as $child) {
				$$child['name'] = $child;
			}
			$packagename = $packageDescription = $plugin = false;
			foreach($packageinformation['children'] as $e) {
				if(strtolower($e['name']) == 'packagename' && !$packagename) {
					$packagename = true;
					$xml .= "\n\t\t\t<packagename><![CDATA[".$e['cdata']."]]></packagename>";
				}
				else if(strtolower($e['name']) == 'packagedescription' && !$packageDescription) {
					$packageDescription = true;
					$xml .= "\n\t\t\t<packagedescription><![CDATA[".$e['cdata']."]]></packagedescription>";
				}
				else if(strtolower($e['name']) == 'plugin' && !$plugin) {
					$plugin = true;
					$xml .= "\n\t\t\t<plugin><![CDATA[".$e['cdata']."]]></plugin>";
				}
			}
			$xml .= "\n\t\t</packageinformation>";
			// /packageinformation
			
			// authorinformation
			$xml .= "\n\t\t<authorinformation>";
			$author = $authorurl =  false;
			foreach($authorinformation['children'] as $e) {
				if(strtolower($e['name']) == 'author' && !$author) {
					$author = true;
					$xml .= "\n\t\t\t<author><![CDATA[".$e['cdata']."]]></author>";
				}
				else if(strtolower($e['name']) == 'authorurl' && !$authorurl) {
					$authorurl = true;
					$xml .= "\n\t\t\t<authorurl><![CDATA[".$e['cdata']."]]></authorurl>";
				}
			}
			$xml .= "\n\t\t</authorinformation>";
			// /authorinformation
			// versions
			$xml .= "\n\t\t<versions>";
			// list each version
			// TODO: remove package if no version is displayed
			foreach($package as $key => $val) {
				// get type, dont display if this type is not wanted
				if(		stripos($key, 'a') !== false
					||	stripos($key, 'alpha') !== false
					||	stripos($key, 'b') !== false
					||	strpos($key, 'beta') !== false
					||	strpos($key, 'dev') !== false) $type = 'unstable';
				else if(stripos($key, 'rc') !== false) $type = 'testing';
				else $type = 'stable';
				if($type != $this->type) continue;
				
				$data = $val['xml']->getElementTree('data');
				// get Updatetype & fromversions
				$isUpdate = false;
				$fromVersions = array();
				foreach($data['children'] as $child) {
					$$child['name'] = $child;
					if($child['name'] == 'instructions') {
						if($child['attrs']['type'] == 'update') {
							$isUpdate = true;
							$fromVersions[] = $child['attrs']['fromversion'];
						}
					}
				}
				
				$xml .= "\n\t\t\t".'<version name="'.$key.'">';
				$xml .= "\n\t\t\t\t<requiredpackages>";
				foreach($requiredpackages['children'] as $required) {
					$xml .= "\n\t\t\t\t\t".'<requiredpackage'.((isset($required['attrs']['minversion'])) ? ' minversion="'.$required['attrs']['minversion'].'"' : '').'><![CDATA['.$required['cdata'].']]></requiredpackage>';
				}
				$xml .= "\n\t\t\t\t</requiredpackages>";
				// use the built time of that package as timestamp
				$xml .= "\n\t\t\t\t<timestamp><![CDATA[".filemtime($val['link'])."]]></timestamp>";
				$xml .= "\n\t\t\t\t<versiontype><![CDATA[".$type."]]></versiontype>";
				$xml .= "\n\t\t\t\t<file><![CDATA[".PAGE_URL.'/'.$val['link']."]]></file>";
				// get updatetype
				if($isUpdate && stripos($key, 'pl')) $updateType = 'security';
				elseif($isUpdate) $updateType = 'update';
				else $updateType = 'install';
				$xml .= "\n\t\t\t\t<updatetype><![CDATA[".$updateType."]]></updatetype>";
				if(!empty($fromVersions)) {
					$xml .= "\n\t\t\t\t<fromversions>";
					foreach($fromVersions as $fromVersion) {
						$xml .= "\n\t\t\t\t\t<fromversion><![CDATA[".$fromVersion."]]></fromversion>";
					}
					$xml .= "\n\t\t\t\t</fromversions>";
				}
				$xml .= "\n\t\t\t</version>";
			}
			$xml .= "\n\t\t</versions>";
			// /versions
			$xml .= "\n\t</package>";
		}
		
		$xml .= "\n</section>";
		return $xml;
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
						$link = str_replace(FileUtil::unifyDirSeperator(PB_DIR), '', $this->source->buildDirectory);
						$this->packages[$data['name']][$data['version']] = array(
							'link' => $link.$file,
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