{include file="documentHeader"}
<head>
	<title>{$source->name} - {PAGE_TITLE}</title>

	{include file='headInclude' sandbox=false}
	
	<script type="text/javascript" src="{@RELATIVE_PB_DIR}js/DirectoryLoader.class.js"></script>
	<script type="text/javascript" src="{@RELATIVE_PB_DIR}js/RevisionLoader.class.js"></script>
	<script type="text/javascript">
		//<![CDATA[
		var SOURCE_ID = {@$source->sourceID};
		
		var directoryLoader = new DirectoryLoader('packageName', 'directory', '{$currentDirectory}');
		var revisionLoader = new RevisionLoader();
		//]]>
	</script>
</head>
<body>
{include file='header' sandbox=false}

<div class="mainHeadline">
	<img src="{icon}indexL.png{/icon}" alt = "" />
	<div class="headlineContainer">
		<h2>{$source->name}</h2>
		<p></p>
	</div>
</div>

{if $userMessages|isset}{@$userMessages}{/if}

{if $builds|count > 0}
	<div class="yform">
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
								{lang}pb.build.fileDate{/lang}
							</th>
							<th>
								{lang}pb.global.action{/lang}
							</th>
						</tr>
					</thead>
	
					<tbody>
						{foreach from=$builds item=build}
						<tr class="deletable">
							<td>
								<a href="index.php?action=DownloadFile&amp;fileID={@$build->fileID}&amp;t={@SECURITY_TOKEN}{@SID_ARG_2ND}">{$build->filename}</a>
							</td>
							<td>
								{if $build->fileType == 'wcfsetup'}
									{$build->profileName}
								{else}
									{$build->packageName}
								{/if}
							</td>
							<td>
								{$build->fileVersion}
							</td>
							<td>
								{@$build->fileDate|shorttime}
							</td>
							<td class="sourceGo">
								<a href="index.php?action=DeleteFile&amp;sourceID={$source->sourceID}&amp;fileID={@$build->fileID}&amp;t={@SECURITY_TOKEN}{@SID_ARG_2ND}" class="deleteButton"><img src="{@RELATIVE_WCF_DIR}icon/deleteS.png" alt="{lang itemName=$build->filename}pb.global.action.delete.sure{/lang}" title="{lang itemName=$build->filename}pb.global.action.delete.sure{/lang}" longdesc="{lang itemName=$build->filename}pb.global.action.delete.sure{/lang}" /></a>
							</td>
						</tr>
						{/foreach}
					</tbody>
				</table>
			</div>
		</fieldset>
	</div>
{/if}

<form method="post" action="index.php?action=SetBuildOptions" class="yform columnar">
	<fieldset>
		<legend>{lang}pb.build.option.title{/lang}</legend>

		<div class="type-text">
			<label for="buildDirectory">{lang}pb.build.option.buildDirectory{/lang}</label>

			{$source->buildDirectory}
		</div>

		{if $source->scm != 'none'}
			<div class="type-text">
				<label for="revision">{lang}pb.build.option.revision{/lang}</label>
				
				<span id="sourceRevision{@$source->sourceID}"><img src="{@RELATIVE_WCF_DIR}images/spinner.gif" alt="" style="height: 12px; width: 12px;" /></span>
				<script type="text/javascript">
					//<![CDATA[
					revisionLoader.registerSource({@$source->sourceID});
					//]]>
				</script>
			</div>
		{/if}
		
		<div class="type-select">
			<label for="packageName">{lang}pb.build.option.packages{/lang}</label>

			{if $directories|empty}
				{lang}pb.source.error.directories.noData{/lang}
			{else}
				<select name="packageName" id="packageName">
	    				{htmloptions options=$directories selected=$currentPackageName}
				</select>
			{/if}
		</div>
		
		<div class="type-select" style="display: none;">
			<label for="directory">{lang}pb.build.option.directories{/lang}</label>
			
			<select name="directory" id="directory"></select>
		</div>
		
		<div class="type-button">
			<input type="hidden" name="sourceID" value="{$source->sourceID}" />
			{@SID_INPUT_TAG}
			<input type="submit" name="changeBuildOptions" value="{lang}pb.build.option.change{/lang}" />

		</div>
	</fieldset>
</form>

<form method="post" action="index.php?action=Checkout" class="yform columnar">
	<fieldset>
		<legend>{lang}pb.source.checkout.title{/lang}</legend>
		
		{if $source->enableCheckout}
			<div class="type-check">
				<input type="checkbox" name="checkoutRepository" id="checkoutRepository" value="1" />
				<label for="checkoutRepository">{lang}pb.source.checkoutRepository{/lang}</label>
			</div>
		{/if}
		
		<div class="type-check">
			<input type="checkbox" name="rebuildPackageData" id="rebuildPackageData" value="1" />
			<label for="rebuildPackageData">{lang}pb.source.rebuildPackageData{/lang}</label>
		</div>
		
		<div class="type-button">
			<input type="hidden" name="sourceID" value="{$source->sourceID}" />
			{@SID_INPUT_TAG}
			<input type="submit" name="changeBuildOptions" value="{lang}pb.source.checkout.do{/lang}" />

		</div>
	</fieldset>
</form>

{if $currentDirectory !== null}
<form method="post" action="index.php?form=PreferredPackage" class="yform columnar">
	<fieldset>
		<legend>{lang}pb.build.title{/lang}</legend>

		<div class="type-select">
			<label for="filename">{lang}pb.build.option.filename{/lang}</label>

			<select name="filename" id="filename">
    				{htmloptions options=$filenames selected=$currentFilename}
			</select>
		</div>
		<div class="type-check">
			<input type="checkbox" name="otherSources" id="otherSources" value="1" />
			<label for="otherSources">{lang}pb.build.option.otherSources{/lang}</label>
		</div>
		<div class="type-button">
			<input type="hidden" name="sourceID" value="{$source->sourceID}" />
			{@SID_INPUT_TAG}
			<input type="submit" name="buildPackage" value="{lang}pb.build.do{/lang}" />
			<input type="reset" />
		</div>
	</fieldset>
</form>
{/if}

{include file='footer' sandbox=false}

</body>
</html>