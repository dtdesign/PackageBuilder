<?php
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
	public $path = '';
	
	/**
	 * build profile name
	 * 
	 * @var	string
	 */
	public $profileName = '';
	
	/**
	 * resource directory containing complete data for a WCFSetup
	 * 
	 * @var	string
	 */
	public $resourceDirectory = '';
	
	/**
	 * Source object
	 * 
	 * @var	Source
	 */
	public $source = null;
	
	/**
	 * Initializes a new WCFSetup-build
	 *
	 * @param	Source	$source
	 * @param	string	$resourceDirectory
	 * @param	string	$profileName
	 */
	public function __construct(Source $source, $resourceDirectory, $profileName) {
		$this->source = $source;
		$this->resourceDirectory = FileUtil::addTrailingSlash($resourceDirectory);
		$this->profileName = $profileName;
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
		$this->cloneDirectory($buildDirectory, 'install/packages');
		
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
		$wcfSetup->add(array($buildDirectory . 'install', $buildDirectory . 'setup'), '', $buildDirectory);
		$wcfSetup->create();
		
		// remove temoprarily directory
		$this->deleteDirectory($buildDirectory);
		@rmdir($buildDirectory);
		
		// set path
		$path = $outputDirectory . 'WCFSetup.tar.gz';
		require_once(PB_DIR.'lib/data/source/file/SourceFileEditor.class.php');
		$sourceFile = SourceFileEditor::create($this->source->sourceID, $path, 'wcfsetup', $this->profileName);
		
		$this->path = $sourceFile->getPath();
	}
	
	/**
	 * Clones a directory.
	 * 
	 * @param	string		$buildDirectory
	 * @param	string		$path
	 */
	protected function cloneDirectory($buildDirectory, $path) {
		$path = FileUtil::addTrailingSlash($path);
		
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
				// ignore .svn directories
				$tmp = explode('/', FileUtil::unifyDirSeperator($it->getSubPath()));
				if (in_array('.svn', $tmp)) {
					$it->next();
					continue;
				}
				
				$subPath = FileUtil::addTrailingSlash($it->getSubPath());
				if (!is_dir($buildDirectory . $path . $subPath)) {
					FileUtil::makePath($buildDirectory . $path . $subPath);
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
	
	/**
	 * Recursively deletes a directory.
	 * 
	 * @param	string		$directory
	 */
	protected function deleteDirectory($directory) {
		if ($dir = opendir($directory)) {
			while (($file = readdir($dir)) !== false) {
				if ($file == '.' || $file == '..') continue;
				
				$file = FileUtil::addTrailingSlash($directory) . $file;
				
				if (is_dir($file)) {
					$this->deleteDirectory($file);
					
					rmdir($file);
				}
				else {
					unlink($file);
				}
			}
			
			closedir($dir);
		}
}
}
?>