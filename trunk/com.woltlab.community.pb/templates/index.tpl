{include file="documentHeader"}
<head>
	<title>{lang}pb.index.title{/lang} - {PAGE_TITLE}</title>

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
				{lang}pb.source.revision{/lang} ¹
			</th>
			<th scope="col">
				{lang}pb.global.action{/lang}
			</th>
		</tr>
	</thead>

	<tbody>
{if $sources|isset}
	{foreach from=$sources item=source}
		<tr>
			<td>
				<a href="index.php?page=SourceView&amp;sourceID={$source.sourceID}">{$source.name}</a>
			</td>
			<td>
				{$source.sourceDirectory}
			</td>
			<td>
				{lang}pb.source.scm.{$source.scm}{/lang}
			</td>
			<td>
				{if $source.scm == 'none'}
					{lang}pb.source.scm.disabled{/lang}
				{else}
					{if $source.availableRevision != $source.revision}
						<strong class="red">{lang}pb.source.scm.higherRevisionAvailable{/lang}</strong>
					{else}
						{$source.revision}
					{/if}
				{/if}
			</td>
			<td class="sourceGo">
				<a href="index.php?page=SourceView&amp;sourceID={$source.sourceID}"><img src="{@RELATIVE_WCF_DIR}icon/cronjobExecuteS.png" alt="{lang}pb.source.go{/lang}" title="{lang}pb.source.go{/lang}" /></a>
			</td>
		</tr>
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