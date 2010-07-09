<?php
// pb imports
require_once(PB_DIR.'lib/system/package/StandalonePackageHelper.class.php');

// wcf imports
require_once(WCF_DIR.'lib/system/io/TarWriter.class.php');
require_once(WCF_DIR.'lib/system/io/ZipWriter.class.php');

/**
 * Builds a package.
 *
 * @author	Tim Düsterhus
 * @copyright	2009-2010 WoltLab Community
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.community.pb
 * @subpackage	package
 * @category 	PackageBuilder
 */
class PackageBuilder {
	private $archive = null;
	private $excludeFiles = array('.', '..');
	private $ignoreDoFiles = true;
	private $filename = '';
	private $location = '';
	private $package = array();
	private $source = null;

	/**
	 * Builds a package
	 *
	 * @param	integer	$source			id or instance of a source
	 */
	public function __construct($filename, $source, Array $packages, Array $readMeFiles, $dataBaseStructure, Array $setupLanguages, Array $licenses, Array $templates, $files, Array $languages) {
		$this->source = ($source instanceof Source) ? $source : new Source($source);
		
		$buildDirectory = $this->source->buildDirectory.'/';
		foreach($packages as $package) {
			if(!file_exists($buildDirectory.$package)) throw new SystemException('Package "'.$package.'" not found');
		}
		
		foreach($readMeFiles as $readMe) {
			if(!file_exists($readMe)) throw new SystemException('Readme "'.basename($readMe).'" not found');
		}
		
		if(!file_exists($dataBaseStructure)) throw new SystemException('DataBaseStructure not found');
		
		foreach($setupLanguages as $setupLanguage) {
			if(!file_exists($setupLanguage)) throw new SystemException('SetupLanguage "'.basename($setupLanguage).'" not found');
		}
		
		foreach($licenses as $license) {
			if(!file_exists($licenses)) throw new SystemException('License "'.basename($license).'" not found');
		}
		
		foreach($templates as $template) {
			if(!file_exists($template)) throw new SystemException('Template "'.basename($template).'" not found');
		}
		if(!file_exists($files)) throw new SystemException('Files not found');
		
		foreach($languages as $language) {
			if(!file_exists($language)) throw new SystemException('Language "'.basename($language).'" not found');
		}
		$data = array(
			'pn' => $this->package['name'],
			'pv' => $this->package['version'],
			'pr' => 'r'.$this->source->revision,
			't' => 	DateUtil::formatTime('%D %T', TIME_NOW, false)
		);
		$this->filename = StandalonePackageHelper::getArchiveName($filename, $data);
		$location = $buildDirectory.'standalone/'.$this->filename;
		
		// intialize archive
		$this->location = $this->createArchive($location, $packages, $readMeFile, $dataBaseStructure, $setupLanguages, $licenses, $templates, $files, $languages);
	}

	/**
	 * Creates complete archive.
	 *
	 * @param	string	$directory
	 * @param	string	$filename
	 */
	public function createArchive($location, $packages, $readMeFile, $dataBaseStructure, $setupLanguages, $licenses, $templates, $files, $languages) {
		
		$package = new TarWriter(dirname($location).'/WCFSetup.tar.gz', true);
		FileUtil::makePath(dirname($location).'/tmp/upload/setup/db');
		FileUtil::makePath(dirname($location).'/tmp/upload/setup/lang');
		FileUtil::makePath(dirname($location).'/tmp/upload/setup/license');
		FileUtil::makePath(dirname($location).'/tmp/upload/setup/template');
		FileUtil::makePath(dirname($location).'/tmp/upload/install/lang');
		FileUtil::makePath(dirname($location).'/tmp/upload/install/packages');
		copy($dataBaseStructure, dirname($location).'/tmp/upload/setup/db/mysql.sql');
		foreach($setupLanguages as $setupLanguage) {
			copy($setupLanguage, dirname($location).'/tmp/upload/setup/lang/'.basename($setupLanguage));
		}
		foreach($licenses as $license) {
			copy($license, dirname($location).'/tmp/upload/setup/license/'.basename($license));
		}
		foreach($templates as $template) {
			copy($template, dirname($location).'/tmp/upload/setup/template/'.basename($template));
		}
		self::dircopy($files, dirname($location).'/tmp/upload/install/files');
		foreach($languages as $language) {
			copy($language, dirname($location).'/tmp/upload/install/lang/'.basename($language));
		}
		foreach($packages as $package) {
			copy($package, dirname($location).'/tmp/upload/install/packages/'.basename($package));
		}
		$package->add(array(dirname($location).'/tmp/upload/setup', dirname($location).'/tmp/upload/install'), '', dirname($location).'/tmp/upload');
		
		$file = new ZipWriter();
		
		// add readmes
		foreach($readMeFiles as $readMe) {
			$file->addFile(file_get_contents($readMe), basename($readMe), filemtime($readMe));
		}
		$file->addDir('upload');
		$file->addFile(file_get_contents(dirname($location).'/WCFSetup.tar.gz'), 'upload/WCFSetup.tar.gz', filemtime(dirname($location).'/WCFSetup.tar.gz'));
		
		file_put_contents($location, $file->getFile());
		$dir = DirectoryUtil::getInstance(dirname($location).'/tmp');
		$dir->removeAll();
		unlink(dirname($location).'/WCFSetup.tar.gz');
		return $location;
	}
	public static function dircopy($srcdir, $dstdir, $verbose = false) {
	  $num = 0;
	  if(!is_dir($dstdir))FileUtil::makePath($dstdir);
	  if($curdir = opendir($srcdir)) {
	   while($file = readdir($curdir)) {
	     if($file != '.' && $file != '..') {
	       $srcfile = $srcdir . '/' . $file;
	       $dstfile = $dstdir . '/' . $file;
	       if(is_file($srcfile)) {
	         if(is_file($dstfile)) $ow = filemtime($srcfile) - filemtime($dstfile); else $ow = 1;
	         if($ow > 0) {
	           if(copy($srcfile, $dstfile)) {
	             touch($dstfile, filemtime($srcfile)); $num++;
	           }
	           else throw new SystemException('Error: File "'.$srcfile.'" could not be copied!');
	         }
	       }
	       else if(is_dir($srcfile)) {
	         $num += self::dircopy($srcfile, $dstfile, $verbose);
	       }
	     }
	   }
	   closedir($curdir);
	  }
	  return $num;
	}
	
	/**
	 * Returns the archive location
	 *
	 * @return	string
	 */
	public function getArchiveLocation() {
		return $this->location;
	}
}
?>