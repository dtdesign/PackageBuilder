{include file='documentHeader'}
<head>
	<title>{lang}pb.profiles.title{/lang} - {PAGE_TITLE}</title>

	{include file='headInclude' sandbox=false}
	
	<script type="text/javascript" src="{@RELATIVE_PB_DIR}js/ProfileBuilder.class.js"></script>
	<script type="text/javascript">
		//<![CDATA[
		var profileBuilder = new ProfileBuilder();
		//]]>
	</script>
</head>
<body>
{include file='header' sandbox=false}

<div class="mainHeadline">
	<img src="{icon}indexL.png{/icon}" alt = "" />
	<div class="headlineContainer">
		<h2>{PAGE_TITLE}</h2>
		<p>{PAGE_DESCRIPTION}</p>
	</div>
</div>

{if $userMessages|isset}{@$userMessages}{/if}

<div class="yform columnar">
	<fieldset>
		<legend>{lang}pb.profile.package.title{/lang}</legend>
		
		<div class="type-select">
			<label for="packageType">{lang}pb.profile.package.packageType{/lang}</label>
			<select id="packageType">
				<option></option>
				<option value="plugin">{lang}pb.profile.package.packageType.plugin{/lang}</option>
				<option value="standalone">{lang}pb.profile.package.packageType.standalone{/lang}</option>
			</select>
		</div>
		
		<div class="type-select" id="pluginSelect" style="display: none;">
			<label for="plugin">{lang}pb.profile.package.packageName{/lang} <img src="{@RELATIVE_WCF_DIR}images/spinner.gif" alt="" style="display: none; height: 13px; vertical-align: middle; width: 13px;" id="pluginLoading" /></label>
			<select id="plugin">
				<option></option>
				{foreach from=$packages['plugin'] item=pluginName}
					<option value="{$pluginName}">{$pluginName}</option>
				{/foreach}
			</select>
		</div>
		
		<div class="type-select" id="standaloneSelect" style="display: none;">
			<label for="standalone">{lang}pb.profile.package.packageName{/lang} <img src="{@RELATIVE_WCF_DIR}images/spinner.gif" alt="" style="display: none; height: 13px; vertical-align: middle; width: 13px;" id="standaloneLoading" /></label>
			<select id="standalone">
				<option></option>
				{foreach from=$packages['standalone'] item=standaloneName}
					<option value="{$standaloneName}">{$standaloneName}</option>
				{/foreach}
			</select>
		</div>
		
		<div class="type-button" id="loadProfilesDiv" style="display: none;">
			<input type="button" id="loadProfiles" value="{lang}pb.profile.loadProfiles{/lang}" />
		</div>
		
		<div class="type-select" id="versionSelect" style="display: none;">
			<label for="version">{lang}pb.profile.package.version{/lang}</label>
			<select id="version"></select>
		</div>
		
		<div class="type-button" id="createProfileDiv" style="display: none;">
			<input type="button" id="createProfile" value="{lang}pb.profile.createProfile{/lang}" />
		</div>
	</fieldset>
	
	<form method="post" action="index.php?action=BuildProfile">
		<fieldset style="display: none;" id="profileList">
			<legend>{lang}pb.profile.list.title{/lang}</legend>
			
			<div id="profileListContent"></div>
			
			<div class="type-button">
				<input type="submit" id="buildProfile" value="{lang}pb.profile.buildProfile{/lang}" disabled="disabled" />
				{@SID_INPUT_TAG}
				{@SECURITY_TOKEN_INPUT_TAG}
			</div>
		</fieldset>
	</form>
	
	<fieldset style="display: none;" id="profileBuilder">
		<legend>{lang}pb.profile.builder.title{/lang}</legend>
		
		<div id="profileBuilderContent"></div>
		
		<div class="type-text" style="margin-top: 2em;">
			<label for="profileName">{lang}pb.profile.builder.profileName{/lang}</label>
			<input type="text" id="profileName" value="" />
		</div>
		<div class="type-button">
			<input type="button" id="saveProfile" value="{lang}pb.profile.builder.saveProfile{/lang}" />
		</div>
	</fieldset>
</div>
{include file='footer' sandbox=false}

</body>
</html>