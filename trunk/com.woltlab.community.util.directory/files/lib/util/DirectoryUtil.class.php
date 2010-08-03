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
	 * @var RecursiveDirectoryIterator
	 */
	protected $obj = NULL;

	/**
	 * Stores all files with fullpath
	 *
	 * @var array
	 */
	protected $files = array();

	/**
	 * Stores all files with filename as key and DirectoryIterator object as value
	 *
	 * @var array
	 */
	protected $filesObj = array();

	/**
	 * Directory filesize
	 *
	 * @var integer
	 */
	protected $size = 0;

	/**
	 * Directory path
	 *
	 * @var string
	 */
	protected $directory = '';

	/**
	 * Determines wether scan should be recursive
	 *
	 * @var boolean
	 */
	protected $recursive = true;

	/**
	 * All recursive and non-recursive instances
	 *
	 * @var array<array>
	 */
	protected static $instances = array(
		true => array(),	// recursive instances
		false => array()	// non-recursive instances
	);

	/**
	 * Creates a new instance of DirectoryUtil
	 *
	 * @param	string	$directory	directory path
	 * @param	boolean	$recursive	created a recursive directory iterator
	 * @see		DirectoryUtil::getInstance()
	 */
	protected function __construct($directory, $recursive = true) {
		$this->directory = $directory;
		$this->recursive = $recursive;

		// handle iterator type
		if ($this->recursive) {
			$this->obj = new RecursiveDirectoryIterator($directory);
		}
		else {
			$this->obj = new DirectoryIterator($directory);
		}

		// read files
		$this->scanFiles();
	}

	/**
	 * @see	DirectoryUtil::getInstance()
	 */
	private final function __clone() {}

	/**
	 * Clears an instance
	 *
	 * @param 	string		$directory	directory path
	 * @param 	boolean		$recursive	destroy a recursive instance
	 * @return	boolean				successfully killed the instance?
	 */
	public static function destroy($directory, $recursive = true) {
		$directory = realpath(FileUtil::unifyDirSeperator($directory));
		if (!isset(self::$instances[$recursive][$directory])) return false;

		unset (self::$instances[$recursive][$directory]);
		return true;
	}

	/**
	 * returns an instance of DirectoryUtil
	 *
	 * @param 	string		$directory 	directorypath
	 * @param	boolean		$recursive	should the directory be walked through recursive
	 * @return 	object				DirectoryUtil object
	 */
	public static function getInstance($directory, $recursive = true) {
		$directory = realpath(FileUtil::unifyDirSeperator($directory));
		if ($directory === false) throw new SystemException('Invalid directory');

		if (!isset(self::$instances[$recursive][$directory])) {
			self::$instances[$recursive][$directory] = new DirectoryUtil($directory, $recursive);
		}

		return self::$instances[$recursive][$directory];
	}

	/**
	 * Executes a callback on each file
	 *
	 * @param 	callback 	$callback 	Valid callback
	 * @param 	string 		$pattern 	Apply callback only to files matching the given pattern
	 * @return	boolean 			Returns false if callback is missing or no files available
	 */
	public function executeCallback($callback, $pattern = '') {
		if (!is_callable($callback) || empty($this->files)) return false;

		$files = $this->getFiles();
		foreach ($files as $filename) {
			if (!empty($pattern) && !preg_match($pattern, $filename)) continue;

			call_user_func($callback, $filename);
		}

		return true;
	}

	/**
	 * returns a sorted list of files
	 *
	 * @param 	integer	$order	the order the files should be sorted
	 * @return 	array		sorted file list
	 */
	public function getFiles($order = SORT_ASC) {
		$files = $this->files;

		if ($order == SORT_ASC) {
			asort($files);
		}
		else {
			arsort($files);
		}

		return $files;
	}

	/**
	 * returns a sorted list of files, with DirectoryIterator object as value
	 *
	 * @param 	integer	$order	the order the files should be sorted
	 * @return 	array		sorted filelist
	 */
	public function getFilesObj($order = SORT_ASC) {
		$this->scanFilesObj();
		$objects = $this->filesObj;

		if ($order == SORT_ASC) {
			ksort($objects);
		}
		else {
			krsort($objects);
		}

		return $objects;
	}

	/**
	 * fills the list of availible files
	 *
	 * @return void
	 */
	protected function scanFiles() {
		if (!empty($this->files)) return;

		if ($this->recursive) {
			$it = new RecursiveIteratorIterator($this->obj, RecursiveIteratorIterator::CHILD_FIRST);

			foreach ($it as $filename => $obj) {
				if ($it->isDot()) continue;

				$this->files[] = $filename;
			}
		}
		else {
			foreach ($this->obj as $filename => $obj) {
				if ($this->obj->isDot()) continue;

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
		if (!empty($this->filesObj)) return;

		if ($this->recursive) {
			$it = new RecursiveIteratorIterator($this->obj, RecursiveIteratorIterator::CHILD_FIRST);

			foreach ($it as $filename => $obj) {
				if ($it->isDot()) continue;

				$this->filesObj[$filename] = $obj;
			}
		}
		else {
			foreach ($this->obj as $filename => $obj) {
				if ($this->obj->isDot()) continue;

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
		if (!$this->recursive) return false;

		$files = $this->getFilesObj(SORT_DESC);
		foreach ($files as $filename => $obj) {
			if (!is_writable($obj->getPath())) {
				throw new SystemException('Could not remove dir: "'.$obj->getPath().'" is not writable');
			}

			if ($obj->isDir()) {
				rmdir($filename);
			}
			else if ($obj->isFile()) {
				unlink($filename);
			}
		}

		rmdir($this->directory);
		unset(self::$instances[$this->recursive][$this->directory]);
	}

	/**
	 * removes all files that match the pattern
	 *
	 * @param  string	$pattern	regex pattern
	 * @return mixed
	 */
	public function removePattern($pattern) {
		if (!$this->recursive) return false;

		$files = $this->getFilesObj(SORT_DESC);
		foreach ($files as $filename => $obj) {
			if (!preg_match($pattern, $filename)) continue;

			if (!is_writable($obj->getPath())) {
				throw new SystemException('Could not remove dir: "'.$obj->getPath().'" is not writable');
			}

			if ($obj->isDir()) {
				rmdir($filename);
			}
			else if ($obj->isFile()) {
				unlink($filename);
			}
		}

		$this->filesObj = array();
		$this->scanFilesObj();

		$this->files = array();
		$this->scanFiles();
	}

	/**
	 * calculates the size of the directory
	 *
	 * @return mixed	directorysize
	 */
	public function getSize() {
		if (!$this->recursive) return false;

		if ($this->size) return $this->size;

		$files = $this->getFilesObj(SORT_DESC);
		foreach ($files as $filename => $obj) {
			$this->size += $obj->getSize();
		}

		return $this->size;
	}
}
?>