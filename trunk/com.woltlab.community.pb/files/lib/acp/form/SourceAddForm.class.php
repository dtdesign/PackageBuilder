<?php
// pb imports
require_once(PB_DIR.'lib/data/source/SourceEditor.class.php');

// wcf imports
require_once(WCF_DIR.'lib/acp/form/ACPForm.class.php');
require_once(WCF_DIR.'lib/util/FileUtil.class.php');

/**
 * A form to create new sources.
 *
 * @author	Alexander Ebert
 * @copyright	2009-2010 WoltLab Community
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.community.pb
 * @subpackage	acp.form
 * @category 	PackageBuilder
 */
class SourceAddForm extends ACPForm {
	public $neededPermissions ='admin.source.canAddSources';
	public $templateName = 'sourceAdd';
	public $action = 'add';
	public $activeMenuItem = 'pb.acp.menu.link.content.source.add';

	// data
	public $name = '';
	public $sourceDirectory = '';
	public $buildDirectory = '';
	public $position = '';
	public $scm = 'none';
	public $url = '';
	public $username = '';
	public $password = '';
	public $trustServerCert = 0;

	/**
	 * @see Form::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();

		if (isset($_POST['name'])) $this->name = StringUtil::trim($_POST['name']);
		if (isset($_POST['sourceDirectory'])) $this->sourceDirectory = StringUtil::trim($_POST['sourceDirectory']);
		$this->sourceDirectory = FileUtil::addTrailingSlash(FileUtil::unifyDirSeperator($this->sourceDirectory));
		if (isset($_POST['buildDirectory'])) $this->buildDirectory = StringUtil::trim($_POST['buildDirectory']);
		$this->buildDirectory = FileUtil::addTrailingSlash(FileUtil::unifyDirSeperator($this->buildDirectory));
		if (isset($_POST['position'])) $this->position = intval($_POST['position']);
		if (isset($_POST['scm'])) $this->scm = StringUtil::trim($_POST['scm']);
		if (isset($_POST['useSubversion'])) $this->useSubversion = intval($_POST['useSubversion']);
		if (isset($_POST['url'])) $this->url = StringUtil::trim($_POST['url']);
		if (isset($_POST['username'])) $this->username = StringUtil::trim($_POST['username']);
		if (isset($_POST['password'])) $this->password = StringUtil::trim($_POST['password']);
		if (isset($_POST['trustServerCert'])) $this->trustServerCert = intval($_POST['trustServerCert']);
	}

	/**
	 * @see	Form::validate()
	 */
	public function validate() {
		parent::validate();

		if (empty($this->name)) throw new UserInputException('name', 'empty');

		// validate directories
		$this->validateDirectory($this->sourceDirectory, 'sourceDirectory');
		$this->validateDirectory($this->buildDirectory, 'buildDirectory');

		// validate SCM
		$this->validateSCM();
	}

	/**
	 * Validates a given directory and tries to create it
	 *
	 * @param	string	$directory	Target directory
	 * @param	stromg	$fieldName	Input fieldname, required for exception handling
	 */
	protected function validateDirectory($directory, $fieldName) {
		if (empty($directory)) throw new UserInputException($fieldName, 'empty');

		// create directory
		@mkdir($directory, 0770);

		// verify previously created directory
		if (!is_dir($directory) || !is_writeable($directory)) {
			// try to cleanup
			@rmdir($directory);

			throw new UserInputException($fieldName, 'invalid');
		}
	}

	/**
	 * Validates SCM and resets input fields if unused
	 */
	protected function validateSCM() {
		$this->scm = Source::validateSCM($this->scm);

		switch ($this->scm) {
			case 'none':
				// reset input if no SCM is active
				$this->username = $this->password = '';
				$this->trustServerCert = 0;
			break;

			default:
				if (empty($this->url)) throw new UserInputException('url', 'empty');
			break;
		}
	}

	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();

		if (empty($this->sourceDirectory)) $this->sourceDirectory = Source::getRandomDirectory('repository');
		if (empty($this->buildDirectory)) $this->buildDirectory = Source::getRandomDirectory('build');

		WCF::getTPL()->assign(array(
			'name' => $this->name,
			'sourceDirectory' => $this->sourceDirectory,
			'buildDirectory' => $this->buildDirectory,
			'position' => $this->position,
			'scm' => $this->scm,
			'url' => $this->url,
			'username' => $this->username,
			'trustServerCert' => $this->trustServerCert
		));
	}

	/**
	 * @see Form::save()
	 */
	public function save() {
		parent::save();

		// any numeric value is valid for $position thus we replace an empty value with null
		if (empty($this->position)) $this->position = null;

		// create source
		SourceEditor::create($this->name, $this->sourceDirectory, $this->buildDirectory, $this->scm, $this->url, $this->username, $this->password, $this->trustServerCert, $this->position);

		// call saved event
		$this->saved();

		// reset values
		$this->sourceDirectory = Source::getRandomDirectory('repository');
		$this->buildDirectory = Source::getRandomDirectory('build');
		$this->name = $this->scm = $this->url = $this->username = $this->password = '';
		$this->trustServerCert = 0;

		WCF::getTPL()->assign('success', true);
	}

	/**
	 * @see Page::show()
	 */
	public function show() {
		// check master password
		WCFACP::checkMasterPassword();

		parent::show();
	}
}
?>