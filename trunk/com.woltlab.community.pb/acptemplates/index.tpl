{include file='header'}

<div class="mainHeadline">
	<img src="{RELATIVE_PB_DIR}/icon/acpL.png" alt="" />
	<div class="headlineContainer">
		<h2>{lang}pb.acp.index.title{/lang}</h2>
	</div>
</div>

<div class="tabMenu">
	<ul>
		<li id="system"><a onclick="tabMenu.showSubTabMenu('system');"><span>{lang}pb.acp.index.system{/lang}</span></a></li>
		<li id="credits"><a onclick="tabMenu.showSubTabMenu('credits');"><span>{lang}pb.acp.index.credits{/lang}</span></a></li>
	</ul>
</div>
<div class="subTabMenu">
	<div class="containerHead"><div> </div></div>
</div>

<div class="border tabMenuContent system" id="system-content">
	<div class="container-1">
		<h3 class="subHeadline">
			{lang}pb.acp.index.system.status{/lang}
		</h3>

		{if $disabledFunctions|empty}
			<p class="success">{lang}pb.acp.index.system.status.working{/lang}</p>
		{else}
			<p class="error">{lang}pb.acp.index.system.status.disabledFunctions{/lang}</p>

			{foreach from=$disabledFunctions key=functionType item=functions}
			<div class="border titleBarPanel">
				<div class="containerHead">
					<h3>{lang}pb.acp.index.system.status.function.{$functionType}{/lang}</h3>
				</div>
			</div>

			<div class="border borderMarginRemove">
				<table class="tableList">
					<colgroup>
						<col width="20%" />
						<col width="80%" />
					</colgroup>
					<tbody>
				{foreach from=$functions item=function}
						<tr class="{cycle values="container-1,container-2"}">
							{assign var=phpManualEntry value="http://www.php.net/$function"}
							<td><a href="{@RELATIVE_WCF_DIR}acp/dereferrer.php?url={$phpManualEntry|rawurlencode}" class="externalURL">{$function}</a></td>
							<td>{lang}pb.acp.index.system.status.function.{$functionType}.{$function}{/lang}</td>
						</tr>
				{/foreach}
					</tbody>
				</table>
			</div>
			{/foreach}
		{/if}
	</div>
</div>

{include file='footer'}