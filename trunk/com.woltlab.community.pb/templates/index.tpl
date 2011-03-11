{include file="documentHeader"}
<head>
	<title>{lang}pb.global.index.title{/lang} - {PAGE_TITLE}</title>

	{include file='headInclude' sandbox=false}
	
	<script type="text/javascript" src="{@RELATIVE_PB_DIR}js/RevisionLoader.class.js"></script>
	<script type="text/javascript">
		//<![CDATA[
		var revisionLoader = new RevisionLoader();
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

<table class="full">
	<thead>
		<tr>
			<th scope="col">
				{lang}pb.source.name{/lang}
			</th>
			<th scope="col">
				{lang}pb.source.sourceDirectory{/lang}
			</th>
			<th scope="col">
				{lang}pb.source.scm{/lang}
			</th>
			<th scope="col">
				{lang}pb.source.revision{/lang} <sup>1</sup>
			</th>
			<th scope="col">
				{lang}pb.global.action{/lang}
			</th>
		</tr>
	</thead>

	<tbody>
{if $sources|isset}
	{foreach from=$sources item=source}
		{if $source->hasAccess()}
			<tr>
				<td>
					<a href="index.php?page=SourceView&amp;sourceID={$source->sourceID}">{$source->name}</a>
				</td>
				<td>
					{assign var=length value=$source->sourceDirectory|strlen}
					{if $length > 40}
						{$source->sourceDirectory|substr:0:15}&hellip;<strong>{$source->sourceDirectory|substr:-25}</strong>
					{else}
						{$source->sourceDirectory}
					{/if}
				</td>
				<td>
					{lang}wcf.scm.{$source->scm|strtolower}{/lang}
				</td>
				<td>
					{if $source->scm == 'none'}
						{lang}pb.source.scm.disabled{/lang}
					{else}
						<span id="sourceRevision{@$source->sourceID}"><img src="{@RELATIVE_WCF_DIR}images/spinner.gif" alt="" style="height: 12px; width: 12px;" /></span>
						<script type="text/javascript">
							//<![CDATA[
							revisionLoader.registerSource({@$source->sourceID});
							//]]>
						</script>
					{/if}
				</td>
				<td class="sourceGo">
					<a href="index.php?page=SourceView&amp;sourceID={$source->sourceID}"><img src="{icon}cronjobExecuteS.png{/icon}" alt="{lang}pb.source.go{/lang}" title="{lang}pb.source.go{/lang}" /></a>
				</td>
			</tr>
		{/if}
	{/foreach}
{/if}
	</tbody>
</table>
<p class="info">
	{lang}pb.source.revision.description{/lang}
</p>

{include file='footer' sandbox=false}

</body>
</html>