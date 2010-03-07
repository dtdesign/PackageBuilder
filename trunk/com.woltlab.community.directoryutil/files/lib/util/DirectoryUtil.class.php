<?php

/**
 * Provides functions for handling directories
 *
 * @author	Tim Düsterhus
 * @copyright	2009-2010 WoltLab Community
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.community.pb
 * @subpackage	util
 * @category 	PackageBuilder
 */
class DirectoryUtil {
	
	/**
	 * holds the RecursiveDirectoryIterator object
	 *
	 * @var object
	 */
	protected $obj = NULL;
	
	/**
	 * all files with fullpath
	 *
	 * @var array
	 */
	protected $files = array();
	
	/**
	 * all files with filename as key and DirectoryIterator object as value
	 *
	 * @var array
	 */
	protected $filesObj = array();
	
	/**
	 * filesize of the diretory
	 *
	 * @var int
	 */
	protected $size = 0;
	
	/**
	 * path to directory
	 *
	 * @var string
	 */
	protected $directory = '';
	
	/**
	 * should it be a recursiv scan
	 *
	 * @var bool
	 */
	protected $recursiv = true;
	
	/**
	 * all recursiv instances
	 *
	 * @var array
	 */
	protected static $instances = array();
	
	/**
	 * all non-recursiv instances
	 *
	 * @var array
	 */
	protected static $instancesNonRecursiv = array();
	
	protected function __construct($directory, $recursiv = true) {
		$this->directory = $directory;
		$this->recursiv = $recursiv;
		if($recursiv) $this->obj = new RecursiveDirectoryIterator($directory);
		else $this->obj = new DirectoryIterator($directory);
		// fill the files
		$this->scanFiles();
	}
	/**
	 * returns a (new) instance of DirectoryUtil
	 *
	 * @param 	string		$directory 	directorypath
	 * @return 	object				DirectoryUtil object
	 */
	public function getInstance($directory, $recursiv = true) {
		$directory = realpath(FileUtil::unifyDirSeperator($directory));
		if($directory === false) throw new SystemException('Invalid directory');
		if(array_key_exists($directory, self::$instances) && $recursiv) return self::$instances[$directory];
		if(array_key_exists($directory, self::$instancesNonRecursiv) && !$recursiv) return self::$instances[$directory];
		if($recursiv) {
			self::$instances[$directory] = new self($directory, $recursiv);
			return self::$instances[$directory];
		}
		self::$instancesNonRecursiv[$directory] = new self($directory, $recursiv);
		return self::$instancesNonResursiv[$directory];
	}
	
	/**
	 * returns a (sorted) list of files
	 *
	 * @param 	string 	$order	the order the files should be sorted
	 * @return 	array		sorted filelist
	 */
	public function getFiles($order = 'ASC') {
		$tmp = $this->files;
		if($order == 'ASC') asort($tmp);
		elseif($order == 'DESC') arsort($tmp);
		return $tmp;
	}
	
	/**
	 * returns a (sorted) list of files, with DirectoryIterator object as value
	 *
	 * @param 	string 	$order	the order the files should be sorted
	 * @return 	array		sorted filelist
	 */
	public function getFilesObj($order = 'ASC') {
		$this->scanFilesObj();
		$tmp = $this->filesObj;
		if($order == 'ASC') ksort($tmp);
		elseif($order == 'DESC') krsort($tmp);
		return $tmp;
	}
	
	/**
	 * fills the list of availible files
	 *
	 * @return void
	 */
	protected function scanFiles() {
		if(!empty($this->files)) return;
		if($this->recursiv) {
			foreach (new RecursiveIteratorIterator($this->obj) as $filename=>$obj) {
				$this->files[] = $filename;
			}
		}
		else {
			foreach ($this->obj as $filename=>$obj) {
				$this->files[] = $obj->getFilename();
			}
		}
	}
	
	/**
	 * fills the list of availible files, with DirectoryIterator object as value
	 *
	 * @return void
	 */
	protected function scanFilesObj() {
		if(!empty($this->filesObj)) return;
		if($this->recursiv) {
			foreach (new RecursiveIteratorIterator($this->obj) as $filename=>$obj) {
				$this->filesObj[$filename] = $obj;
			}
		}
		else {
			foreach ($this->obj as $filename=>$obj) {
				$this->filesObj[$obj->getFilename()] = $obj;
			}
		}
	}
	
	/**
	 * recursiv remove of directory
	 *
	 * @return mixed
	 */
	public function removeComplete() {
		if(!$this->recursiv) return false;
		$files = $this->getFilesObj('DESC');
		foreach($files as $filename=>$obj) {
			if(!is_writable($obj->getPath())) throw new SystemException('Could not remove dir: "'.$obj->getPath().'" is not writable');
			if($obj->isDir()) rmdir($filename);
			elseif($obj->isFile()) unlink($filename);
		}
		rmdir($this->directory);
		unset(self::$instances[$this->directory]);
	}
	
	/**
	 * removes all files that match the pattern
	 *
	 * @param  string	$pattern	regex pattern
	 * @return mixed
	 */
	public function removePattern($pattern) {
		if(!$this->recursiv) return false;
		$files = $this->getFilesObj('DESC');
		foreach($files as $filename=>$obj) {
			if(!preg_match($pattern, $filename)) continue;
			if(!is_writable($obj->getPath())) throw new SystemException('Could not remove dir: "'.$obj->getPath().'" is not writable');
			if($obj->isDir()) rmdir($filename);
			elseif($obj->isFile()) unlink($filename);
		}
		$this->filesObj = array();
		$this->scanFilesObj();
	}
	
	/**
	 * calculates the size of the directory
	 *
	 * @return mixed	directorysize
	 */
	public function getSize() {
		if(!$this->recursiv) return false;
		if($this->size != 0) return $this->size;
		$this->scanFilesObj();
		foreach($this->fileObj as $filename=>$obj) {
			$this->size += $obj->getSize();
		}
		return $this->size;
	}
}
?>