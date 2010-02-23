<?php
// pb imports
require_once(PB_DIR.'lib/data/source/SourceEditor.class.php');

// wcf imports
require_once(WCF_DIR.'lib/acp/form/ACPForm.class.php');
require_once(WCF_DIR.'lib/util/FileUtil.class.php');

/**
 * A form to create new sources
 *
 * @package	info.dtcms.pb
 * @author	Alexander Ebert
 * @copyright	2009-2010 Alexander Ebert IT-Dienstleistungen
 * @license	GNU Lesser Public License <http://www.gnu.org/licenses/lgpl.html>
 * @subpackage	acp.form
 * @category	PackageBuilder
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
		if (isset($_POST['buildDirectory'])) $this->buildDirectory = StringUtil::trim($_POST['buildDirectory']);
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

		if (empty($this->name)) throw new UserInputException('name');
		if (empty($this->sourceDirectory)) throw new UserInputException('sourceDirectory');
		if (empty($this->buildDirectory)) throw new UserInputException('buildDirectory');

		// validate SCM
		$this->validateSCM();
	}

	/**
	 * Validates SCM and resets input fields if unused
	 */
	protected function validateSCM() {
		$this->scm = Source::validateSCM($this->scm);

		// reset input if no SCm is active
		if ($this->scm == 'none') {
			$this->username = $this->password = '';
			$this->trustServerCert = 0;
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

		// append trailing slashes
		if (substr($this->sourceDirectory, -1) != '/') {
			$this->sourceDirectory .= '/';
		}

		if (substr($this->buildDirectory, -1) != '/') {
			$this->buildDirectory .= '/';
		}

		$this->sourceDirectory = FileUtil::unifyDirSeperator($this->sourceDirectory);
		$this->buildDirectory = FileUtil::unifyDirSeperator($this->buildDirectory);

		// create source
		SourceEditor::create($this->name, $this->sourceDirectory, $this->buildDirectory, $this->scm, $this->url, $this->username, $this->password, $this->trustServerCert);

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