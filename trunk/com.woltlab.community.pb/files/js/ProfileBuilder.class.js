/**
 * @author	Alexander Ebert
 * @copyright	2009-2011 WoltLab Community
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
var ProfileBuilder = Class.create({
	/**
	 * Initializes the ProfileBuilder.
	 */
	initialize: function() {
		this.packageCache = $H();
		
		// bind variables and events once DOM is fully loaded
		document.observe('dom:loaded', this.init.bind(this));
	},
	/**
	 * Initializes class once DOM is fully loaded.
	 */
	init: function() {
		this.createProfile = $('createProfile');
		this.loadProfilesButton = $('loadProfiles');
		this.loadProfilesDiv = $('loadProfilesDiv');
		this.packageTypeSelect = $('packageType');
		this.plugin = $('plugin');
		this.pluginSelect = $('pluginSelect');
		this.profileBuilder = $('profileBuilder');
		this.profileBuilderContent = $('profileBuilderContent');
		this.profileList = $('profileList');
		this.profileListContent = $('profileListContent');
		this.saveProfileButton = $('saveProfile');
		this.standalone = $('standalone');
		this.standaloneSelect = $('standaloneSelect');
		this.version = $('version');
		this.versionSelect = $('versionSelect');
		
		// initialize profile builder
		this.createProfile.observe('click', this.createBuilder.bind(this));
		
		// toggle select fields for plugin and standalone
		this.packageTypeSelect.observe('change', function() {
			if (this.packageTypeSelect.getValue() == 'plugin') {
				this.standaloneSelect.hide();
				this.pluginSelect.show();
			}
			else if (this.packageTypeSelect.getValue() == 'standalone') {
				this.standaloneSelect.show();
				this.pluginSelect.hide();
			}
			else {
				this.loadProfilesDiv.hide();
				this.standaloneSelect.hide();
				this.pluginSelect.hide();
			}
		}.bind(this));
		
		// enable save button for profiles
		this.saveProfileButton.observe('click', this.saveProfile.bind(this));
		
		[ this.plugin, this.standalone ].each(function(selectField) {
			selectField.observe('change', this.showPackage.bind(this));
		}.bind(this));
		
		this.version.observe('change', function() {
			var value = this.version.getValue().strip();
			if (value == '') {
				$('createProfileDiv').hide();
			}
			else {
				$('createProfileDiv').show();
			}
		}.bind(this));
		
		this.loadProfilesButton.observe('click', this.loadProfiles.bind(this));
	},
	/**
	 * Loads data for profile selection form.
	 */
	createBuilder: function() {
		var hash = $('version').getValue().strip();
		
		// break if no package is selected
		if (hash == '') return;
		
		new Ajax.Request('index.php?action=PrepareBuilder&t=' + SECURITY_TOKEN + SID_ARG_2ND, {
			method: 'post',
			parameters: { hash: hash },
			onSuccess: function(transport) {
				var data = transport.responseText.evalJSON(true);
				this.buildSelection(data);
			}.bind(this)
		});
	},
	/**
	 * Builds the profile selection form.
	 */
	buildSelection: function(data) {
		// insert WCFSetup resources
		var div = new Element('div').addClassName('type-select').setStyle({ marginBottom: '2em' });
		var label = new Element('label', { 'for': 'resource' }).update('LANG_RESOURCE');
		var select = new Element('select', { id: 'resource' });
		
		this.profileBuilderContent.insert(div);
		div.insert(label).insert(select);
		
		for (var i = 0, size = data[1].length; i < size; i++) {
			var resource = data[1][i];
			
			var option = new Element('option', { value: resource.path }).update(resource.label);
			select.insert(option);
		}
		
		// use ordinary for-loop, as it's way much faster
		// than using an expensive each()
		for (var i = 0, size = data[0].length; i < size; i++) {
			var pckg = data[0][i];
			
			// create entry
			var div = new Element('div').addClassName('type-select');
			var label = new Element('label', { 'for': pckg.packageName }).update(pckg.packageName);
			var select = new Element('select', { id: pckg.packageName }).addClassName('packageSelection');
			
			// insert elements
			this.profileBuilderContent.insert(div);
			div.insert(label).insert(select);
			
			// insert options
			for (var j = 0, innerSize = pckg.versions.length; j < innerSize; j++) {
				var version = pckg.versions[j];
				var option = new Element('option', { value: version.hash }).update(version.label);
				select.insert(option);
			}
		}
		
		new Effect.Parallel([
			new Effect.Appear('profileBuilder', { sync: true }),
			new Effect.BlindDown('profileBuilder', { sync: true })
		]);
	},
	/**
	 * Saves or updates a profile.
	 */
	saveProfile: function() {
		var packageName = this.getPackageName();
		var profileName = $('profileName').getValue().strip();
		var resource = $('resource').getValue().strip();
		var packageHash = this.version.getValue().strip();
		
		if (profileName == '') {
			alert('profileName is empty!');
			return;
		}
		
		var packages = $H();
		
		$$('.packageSelection').each(function(selectField) {
			packages.set(selectField.identify(), selectField.getValue());
		});
		
		new Ajax.Request('index.php?action=SaveProfile&t=' + SECURITY_TOKEN + SID_ARG_2ND, {
			method: 'post',
			parameters: {
				packages: packages.toJSON(),
				packageHash: packageHash,
				packageName: packageName,
				profileName: profileName,
				resource: resource
			},
			onSuccess: function(transport) {
				alert(transport.responseText);
				
				this.profileBuilderContent.childElements().each(function(childElement) { childElement.remove(); });
				this.profileBuilder.hide();
			}.bind(this)
		});
	},
	/**
	 * Loads packages via AJAX or from cache if previously loaded.
	 */
	showPackage: function(event) {
		var selectField = event.findElement();
		var packageName = selectField.getValue();
		
		if (packageName == '') {
			this.version.childElements().each(function(childElement) { childElement.remove(); });
			this.versionSelect.hide();
			this.loadProfilesDiv.hide();
			
			return;
		}
		
		// display button
		this.loadProfilesDiv.show();
		
		var cache = this.packageCache.get(packageName);
		if (cache) {
			// display version selection
			this.showVersions(packageName);
			return;
		}
		
		// show spinner
		this.loading(selectField);
		
		new Ajax.Request('index.php?action=LoadPackage&t=' + SECURITY_TOKEN + SID_ARG_2ND, {
			method: 'post',
			parameters: { packageName: packageName },
			onSuccess: function(transport) {
				// hide spinner
				this.loading(selectField);
				
				// store versions in cache
				var cache = transport.responseText.evalJSON(true);
				this.packageCache.set(packageName, cache);
				
				// display version selection
				this.showVersions(packageName);
			}.bind(this)
		});
	},
	/**
	 * Displays available versions for a given package name.
	 */
	showVersions: function(packageName) {
		var cache = this.packageCache.get(packageName);
		
		// remove previous options
		this.version.childElements().each(function(childElement) { childElement.remove(); });
		
		// create empty option
		var option = new Element('option');
		this.version.insert(option);
		
		for (var i = 0, size = cache.length; i < size; i++) {
			var packageVersion = cache[i];
			var option = new Element('option', { value: packageVersion.hash }).update(packageVersion.version + ' - ' + packageVersion.directory);
			this.version.insert(option);
		}
		
		this.versionSelect.show();
	},
	/**
	 * Shows or hides the spinner.
	 */
	loading: function(selectField) {
		$(selectField.identify() + 'Loading').toggle();
	},
	loadProfiles: function() {
		var packageName = this.getPackageName();
		
		if (packageName == '') {
			alert('packageName is empty');
			return; 
		}
		
		new Ajax.Request('index.php?action=LoadProfiles&t=' + SECURITY_TOKEN + SID_ARG_2ND, {
			method: 'post',
			parameters: { packageName: packageName },
			onSuccess: function(transport) {
				var profiles = transport.responseText.evalJSON(true);
				this.showProfileList(profiles);
			}.bind(this),
			onFailure: function(transport) {
				alert(transport.responseText);
			}
		});
	},
	showProfileList: function(profiles) {
		for (var i = 0, size = profiles.length; i < size; i++) {
			var profile = profiles[i];
			
			var div = new Element('div').addClassName('type-check');
			var input = new Element('input', {
				type: 'radio',
				name: 'profile',
				value: profile.profileHash,
				id: profile.profileHash 
			}).addClassName('profileSelection');
			var label = new Element('label', { 'for': profile.profileHash }).update(profile.profileName);
			
			this.profileListContent.insert(div);
			div.insert(input).insert(label);
			
			input.observe('click', function() { $('buildProfile').disabled = ''; }.bind(this));
		}
		
		new Effect.Parallel([
			new Effect.Appear('profileList', { sync: true }),
			new Effect.BlindDown('profileList', { sync: true })
		]);
	},
	getPackageName: function() {
		if (this.pluginSelect.visible()) {
			return this.plugin.getValue().strip();
		}
		else {
			return this.standalone.getValue().strip();
		}
	}
});