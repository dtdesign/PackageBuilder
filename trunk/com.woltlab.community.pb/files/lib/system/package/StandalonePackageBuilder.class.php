<?php
// pb imports
require_once(PB_DIR.'lib/system/package/StandalonePackageHelper.class.php');

// wcf imports
require_once(WCF_DIR.'lib/system/io/TarWriter.class.php');
require_once(WCF_DIR.'lib/system/io/ZipWriter.class.php');

/**
 * Builds a package.
 *
 * @author	Tim Düsterhus, Alexander Ebert
 * @copyright	2009-2011 WoltLab Community
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.community.pb
 * @subpackage	package
 * @category 	PackageBuilder
 */
class StandalonePackageBuilder {
	/**
	 * location of WCFSetup.tar.gz
	 * 
	 * @var	string
	 */
	protected $path = '';
	
	/**
	 * resource directory containing complete data for a WCFSetup
	 * 
	 * @var	string
	 */
	protected $resourceDirectory = '';
	
	/**
	 * Source object
	 * 
	 * @var	Source
	 */
	protected $source = null;
	
	/**
	 * Initializes a new WCFSetup-build
	 *
	 * @param	Source	$source
	 * @param	string	$resourceDirectory
	 */
	public function __construct(Source $source, $resourceDirectory) {
		$this->source = $source;
		$this->resourceDirectory = FileUtil::addTrailingSlash($resourceDirectory);
	}
	
	/**
	 * Builds a WCFSetup.
	 * 
	 * @param	array	$packages
	 * @param	string	$outputDirectory
	 */
	public function createWcfSetup(array $packages, $outputDirectory = '') {
		// ensure output directory is set and exists
		if (empty($outputDirectory)) {
			$outputDirectory = $this->source->buildDirectory;
		}
		else if (!is_dir($outputDirectory)) {
			FileUtil::makePath($outputDirectory);
		}
		$outputDirectory = FileUtil::addTrailingSlash($outputDirectory);
		
		// create temporarily directory
		$hash = StringUtil::getRandomID();
		$buildDirectory = $outputDirectory . $hash . '/';
		
		// populate install directory
		$this->cloneDirectory($buildDirectory, 'install/files');
		$this->cloneDirectory($buildDirectory, 'install/lang');
		
		// populate setup directory
		$this->cloneDirectory($buildDirectory, 'setup/db');
		$this->cloneDirectory($buildDirectory, 'setup/lang');
		$this->cloneDirectory($buildDirectory, 'setup/license');
		$this->cloneDirectory($buildDirectory, 'setup/template');
		
		// copy packages
		foreach ($packages as $package) {
			if (!file_exists($package)) {
				throw new SystemException("Required package '".$package."' not found.");
			}
			
			copy ($package, $buildDirectory . 'install/packages/' . basename($package));
		}
		
		// create wcf setup
		$wcfSetup = new TarWriter($outputDirectory . 'WCFSetup.tar.gz', true); 
		$wcfSetup->add(array($buildDirectory . 'install', $buildDirectory . 'install'), '', $buildDirectory);
		$wcfSetup->create();
		
		$this->path = $outputDirectory . 'WCFSetup.tar.gz';
	}
	
	/**
	 * Clones a directory.
	 * 
	 * @param	string		$buildDirectory
	 * @param	string		$path
	 */
	protected function cloneDirectory($buildDirectory, $path) {
		// ensure source directory exists
		if (!is_dir($this->resourceDirectory . $path)) {
			throw new SystemException("Required path '".$path."' within resource directory is not available.");
		}
		
		// create path
		if (!is_dir($buildDirectory . $path)) {
			FileUtil::makePath($buildDirectory . $path);
		}
		
		// copy files recursively
		$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($this->resourceDirectory . $path));
		while ($it->valid()) {
			if (!$it->isDot()) {
				if (!is_dir($buildDirectory . $path . $it->getSubPath())) {
					FileUtil::makePath($buildDirectory . $path . $it->getSubPath());
				}
				
				copy ($it->key(), $buildDirectory . $path . $it->getSubPathName());
			}
			
			$it->next();
		}
	}

	/**
	 * Returns the archive location
	 *
	 * @return	string
	 */
	public function getArchiveLocation() {
		return $this->path;
	}
}
?>