{include file="documentHeader"}
<head>
	<title>{*{lang}pb.source.view.title{/lang}*} - {PAGE_TITLE}</title>

	{include file='headInclude' sandbox=false}
	<link rel="alternate" type="application/rss+xml" href="index.php?page=Feed&amp;type=RSS2" title="RSS2" />
	<link rel="alternate" type="application/atom+xml" href="index.php?page=Feed&amp;type=Atom" title="Atom" />
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

<form method="post" action="index.php?action=SetBuildOptions" class="yform columnar">
	{if $builds|isset && $builds|count > 0}
	<fieldset>
		<legend>
			{lang}pb.build.existingArchives{/lang}
		</legend>

		<div id="existingArchivesScroll">
			<table class="full">
				<thead>
					<tr>
						<th>
							{lang}pb.build.filename{/lang}
						</th>
						<th>
							{lang}pb.build.packageName{/lang}
						</th>
						<th>
							{lang}pb.build.packageVersion{/lang}
						</th>
						<th>
							{lang}pb.build.action{/lang}
						</th>
					</tr>
				</thead>

				<tbody>
					{foreach from=$builds item=build}
					<tr class="deletable">
						<td>
							<a href="{$build.link}">{$build.filename}</a>
						</td>
						<td>
							{$build.name}
						</td>
						<td>
							{$build.version}
						</td>
						<td class="sourceGo">
							{assign var=filenameEncoded value=$build.filename|urlencode}
							<a href="index.php?action=DeleteArchive&amp;sourceID={$source.sourceID}&amp;filename={$filenameEncoded}" class="deleteButton"><img src="{@RELATIVE_WCF_DIR}icon/deleteS.png" alt="{lang}pb.build.action.delete{/lang}" title="{lang}pb.build.action.delete{/lang}" longdesc="{lang}pb.build.action.delete.sure{/lang}" /></a>
						</td>
					</tr>
					{/foreach}
				</tbody>
			</table>
		</div>
	</fieldset>
{/if}

	<fieldset>
		<legend>{lang}pb.source.buildOptions{/lang}</legend>

		<div class="type-text">
			<label for="buildDirectory">{lang}pb.source.buildDirectory{/lang}</label>

			<input type="text" name="buildDirectory" value="{$source->buildDirectory}" />
		</div>

		<div class="type-select">
			<label for="directory">{lang}pb.source.directories{/lang}</label>

			<select name="directory" id="directory">
				{htmloptions options=$directories selected=$currentDirectory}
			</select>
		</div>

		<div class="type-select">
			<label for="filename">{lang}pb.source.filename{/lang}</label>

			<select name="filename" id="filename">
				{htmloptions options=$filenames selected=$currentFilename}
			</select>
		</div>

		<div class="type-button">
			<input type="hidden" name="sourceID" value="{$source->sourceID}" />
			<input type="submit" value="{lang}pb.source.changeBuildOptions{/lang}" />
		</div>
	</fieldset>
</form>

<form method="post" action="index.php?form=PreferredPackage&amp;sourceID={$source->sourceID}" class="yform">
	<fieldset>
		<legend>
			{lang}pb.source.buildPackage{/lang}
		</legend>

		{if $currentDirectory|empty}
		<p class="warning">
			{lang}pb.source.error.selectDirectory{/lang}
		</p>
		{else}
		<table class="full">
			<thead>
				<tr>
					<th scope="col">
						{lang}pb.source.buildPackage.option{/lang}
					</th>
					<th scope="col">
						{lang}pb.source.buildPackage.optionValue{/lang}
					</th>
				</tr>
			</thead>

			<tbody>
				<tr>
					<td>
						{lang}pb.source.currentDirectory{/lang}
					</td>
					<td>
						{$source->sourceDirectory}{$currentDirectory}
					</td>
				</tr>
				<tr>
					<td>
						{lang}pb.source.buildDirectory{/lang}
					</td>
					<td>
						{$source.buildDirectory}{$currentDirectory}
					</td>
				</tr>
				{if $source->scm != 'none'}
				<tr>
					<td>
						{lang}pb.source.revision{/lang}
					</td>
					<td>
						{$source->revision}{if $source->availableRevision > $source->revision} <strong>({lang}pb.source.availableRevision{/lang})</strong>{/if}
					</td>
				</tr>
				{/if}
			</tbody>
		</table>

		<div class="type-button">
			<input type="submit" value="{lang}pb.source.build{/lang}" />
		</div>
		{/if}
	</fieldset>
</form>

<form method="post" action="index.php?action=SubversionCheckout&amp;sourceID={$source->sourceID}" class="yform">
	<fieldset>
		<legend>{lang}pb.source.viewSubversion{/lang}</legend>

		{*
		{if $source.message}
			<div id="subversion">
				<pre>{@$source.message}</pre>
			</div>
		{/if}
		*}

		<div class="type-check">
			<input type="checkbox" name="rebuildPackageData" id="rebuildPackageData" value="1" />
			<label for="rebuildPackageData">{lang}pb.source.rebuildPackageData{/lang}</label>
		</div>

		<div class="type-button">
			<input type="submit" value="{lang}pb.subversion.checkout{/lang}" />
		</div>
	</fieldset>
</form>

{include file='footer' sandbox=false}

</body>
</html>