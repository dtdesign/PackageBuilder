{include file="documentHeader"}
<head>
	<title>{lang}pb.build.title{/lang} - {PAGE_TITLE}</title>

	{include file='headInclude' sandbox=false}
	
	<script type="text/javascript" src="{@RELATIVE_PB_DIR}js/ProfileLoader.class.js"></script>
	<script type="text/javascript">
		//<![CDATA[
		var profileLoader = new ProfileLoader();
		var profiles = $H();
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

<form method="post" action="index.php?action=BuildPackage" class="yform columnar">
	<fieldset id="profileContainer">
		<legend>{lang}pb.build.profile{/lang}</legend>
		
		{if $profiles|count > 0}
			<div class="type-select">
				<label for="profiles">{lang}pb.build.profile.availableProfiles{/lang}</label>
				<select id="profiles">
					<option></option>
					{foreach from=$profiles item=profile}
						<option value="{$profile.profileName}">{$profile.profileName}</option>
					{/foreach}
				</select>
			</div>
			
			<script type="text/javascript">
				//<![CDATA[
				{foreach from=$profiles item=profile}
					profiles.set('{$profile.profileName}', $H({
						packages: $H({
							{implode from=$profile.packages key=packageHash item=directory}'{$packageHash}': '{$directory}'{/implode}
						}),
						resource: '{$profile.resource}'
					}));
				{/foreach}
				//]]>
			</script>
		{/if}
		
		<div class="type-text">
			<label for="profileName">{lang}pb.build.profile.profileName{/lang}</label>
			<input type="text" id="profileName" value="" />
		</div>
		<div class="type-button">
			<input type="button" id="saveProfile" value="{lang}pb.build.profile.saveProfile{/lang}" />
		</div>
	</fieldset>
	
	<fieldset>
		<legend>
			{lang}pb.build.packageSelection{/lang}
		</legend>
		
		{if $packages|empty}
			<p class="info">
				{lang}pb.build.continue{/lang}
			</p>
		{else}
			<p class="important">
				{lang}pb.build.selectDirectories{/lang}
			</p>
			
			{foreach from=$packages key=packageName item=packageData}
				<div class="type-select">
					<label for="{$packageData.hash}">{$packageName}</label>
					<input type="hidden" name="packages[]" value="{$packageData.hash}-{$packageName}" />

					<select name="{$packageData.hash}" id="{$packageData.hash}" class="packageSelection">
						{foreach from=$packageData.directories key=directory item=data}
							<option value="{$directory}"{if $preSelection[$packageName]|isset && $preSelection[$packageName] == $directory} selected="selected"{/if}>{$data.version} - {$data.directoryShown}</option>
						{/foreach}
					</select>
				</div>
			{/foreach}
			
			<div class="type-check">
				<input type="checkbox" name="saveSelection" id="saveSelection" value="1"{if $saveSelection} checked="checked"{/if} />

				<label for="saveSelection">{lang}pb.build.saveSelection{/lang}</label>
			</div>
		{/if}
		
		<div class="type-button">
			<input type="hidden" name="filename" value="{@$filename}" />
			<input type="hidden" name="sourceID" value="{@$source->sourceID}" />
			<input type="submit" value="{lang}pb.source.build{/lang}" />
		</div>
	</fieldset>
</form>
{include file='footer' sandbox=false}

</body>
</html>