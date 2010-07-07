{include file='header'}
<script type="text/javascript" src="{@RELATIVE_WCF_DIR}js/ItemListEditor.class.js"></script>
<script type="text/javascript">
	//<![CDATA[
	function init() {
		{if $sources|count > 0 && $this->user->getPermission('admin.source.canEditSources')}
			new ItemListEditor('sourceList', { itemTitleEdit: true, itemTitleEditURL: 'index.php?action=SourceRename&sourceID=' });
		{/if}
	}

	// when the dom is fully loaded, execute these scripts
	document.observe("dom:loaded", init);
	//]]>
</script>

<div class="mainHeadline">
	<img src="{@RELATIVE_PB_DIR}icon/sourceListL.png" alt="" />
	<div class="headlineContainer">
		<h2>{lang}pb.acp.source.list{/lang}</h2>
	</div>
</div>

{if $deletedSourceID}
	<p class="success">{lang}pb.acp.source.delete.success{/lang}</p>
{/if}

{if $successfulSorting}
	<p class="success">{lang}pb.acp.source.sort.success{/lang}</p>
{/if}

{if $this->user->getPermission('admin.source.canAddSources')}
	<div class="contentHeader">
		<div class="largeButtons">
			<ul><li><a href="index.php?form=SourceAdd&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}" title="{lang}pb.acp.source.add{/lang}"><img src="{@RELATIVE_PB_DIR}icon/sourceAddM.png" alt="" /> <span>{lang}pb.acp.source.add{/lang}</span></a></li></ul>
		</div>
	</div>
{/if}

{if $sources|count > 0}
	{if $this->user->getPermission('admin.source.canEditSources')}
	<form method="post" action="index.php?action=SourceSort">
	{/if}
		<div class="border content">
			<div class="container-1">
				<ol class="itemList" id="sourceList">
					{foreach from=$sources item=source}
						<li id="item_{@$source->sourceID}" class="deletable">
							<div class="buttons">
								{if $this->user->getPermission('admin.source.canEditSources')}
									<a href="index.php?form=SourceEdit&amp;sourceID={@$source->sourceID}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}"><img src="{@RELATIVE_WCF_DIR}icon/editS.png" alt="" title="{lang}pb.acp.source.edit{/lang}" /></a>
								{else}
									<img src="{@RELATIVE_WCF_DIR}icon/editDisabledS.png" alt="" title="{lang}pb.acp.source.edit{/lang}" />
								{/if}
								{if $this->user->getPermission('admin.source.canDeleteSources')}
									<a href="index.php?action=SourceDelete&amp;sourceID={@$source->sourceID}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}" title="{lang}pb.acp.source.delete{/lang}" class="deleteButton"><img src="{@RELATIVE_WCF_DIR}icon/deleteS.png" alt="" longdesc="{lang}pb.acp.source.delete.sure{/lang}"  /></a>
								{else}
									<img src="{@RELATIVE_WCF_DIR}icon/deleteDisabledS.png" alt="" title="{lang}pb.acp.source.delete{/lang}" />
								{/if}
							</div>

							<h3 class="itemListTitle">
								{if $this->user->getPermission('admin.source.canEditSources')}
									<select name="sourceListPositions[{@$source->sourceID}]">
										{section name='positions' loop=$maxPosition}
											<option value="{@$positions+1}"{if $positions+1 == $source->position} selected="selected"{/if}>{@$positions+1}</option>
										{/section}
									</select>
								{/if}

								ID-{@$source->sourceID} <a href="index.php?form=SourceEdit&amp;sourceID={@$source->sourceID}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}" class="title">{lang}{$source->name}{/lang}</a>
							</h3>
					{/foreach}
				</ol>
			</div>
		</div>
	{if $this->user->getPermission('admin.source.canEditSources')}
		<div class="formSubmit">
			<input type="submit" accesskey="s" value="{lang}wcf.global.button.submit{/lang}" />
			<input type="reset" accesskey="r" id="reset" value="{lang}wcf.global.button.reset{/lang}" />
			<input type="hidden" name="packageID" value="{@PACKAGE_ID}" />
	 		{@SID_INPUT_TAG}
	 	</div>
	</form>
	{/if}
{else}
	<div class="border content">
		<div class="container-1">
			<p>{lang}pb.acp.source.count.noEntries{/lang}</p>
		</div>
	</div>
{/if}

{include file='footer'}