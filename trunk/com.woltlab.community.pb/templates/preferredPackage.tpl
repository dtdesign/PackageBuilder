{include file="documentHeader"}
<head>
	<title>{lang}pb.build.title{/lang} - {PAGE_TITLE}</title>

	{include file='headInclude' sandbox=false}
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

					<select name="{$packageData.hash}" id="{$packageData.hash}">
						{foreach from=$packageData.directories key=directory item=version}
							<option value="{$directory}">{$version} - {$directory}</option>
						{/foreach}
					</select>
				</div>
			{/foreach}

			{* <div class="type-check">
				<input type="checkbox" name="saveSelection" id="saveSelection" value="1"{if $saveSelection} checked="checked"{/if} />

				<label for="saveSelection">{lang}pb.build.saveSelection{/lang}</label>
			</div> *}
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