{include file='header'}
<script src="{@RELATIVE_WCF_DIR}js/TabMenu.class.js" type="text/javascript"></script>
<script type="text/javascript">
	//<![CDATA[
	var tabMenu = new TabMenu();
	onloadEvents.push(function() { tabMenu.showSubTabMenu('system') });
	//]]>
</script>
<div class="mainHeadline">
	<img src="{@RELATIVE_PB_DIR}icon/acpL.png" alt="" />
	<div class="headlineContainer">
		<h2>{lang}pb.acp.index.title{/lang}</h2>
	</div>
</div>

<div class="tabMenu">
	<ul>
		<li id="system"><a onclick="tabMenu.showSubTabMenu('system');"><span>{lang}pb.acp.index.system{/lang}</span></a></li>
		<li id="stat"><a onclick="tabMenu.showSubTabMenu('stat');"><span>{lang}pb.acp.index.stat{/lang}</span></a></li>
		<li id="credits"><a onclick="tabMenu.showSubTabMenu('credits');"><span>{lang}pb.acp.index.credits{/lang}</span></a></li>
		{if $additionalTabs|isset}{@$additionalTabs}{/if}
	</ul>
</div>
<div class="subTabMenu">
	<div class="containerHead"><div></div></div>
</div>

<div class="border tabMenuContent" id="system-content">
	<div class="container-1">
		<h3 class="subHeadline">
			{lang}pb.acp.index.system{/lang}
		</h3>

		{if $functionErrorType == 'success'}
			<p class="success">{lang}pb.acp.index.system.status.working{/lang}</p>
		{else}
			{if $functionErrorType == 'warning'}
				<p class="warning">{lang}pb.acp.index.system.status.recommendFunctions{/lang}</p>
			{else}
				<p class="error">{lang}pb.acp.index.system.status.disabledFunctions{/lang}</p>
			{/if}

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
						<tr class="{@$function['type']}" style="background-image: none;">
							{assign var=phpManualEntry value="http://www.php.net/"|concat:$function['function']}
							<td><a href="{@RELATIVE_WCF_DIR}acp/dereferrer.php?url={$phpManualEntry|rawurlencode}" class="externalURL">{$function['function']}</a></td>
							<td>{lang}pb.acp.index.system.status.function.{$functionType}.{$function['function']}{/lang}</td>
						</tr>
					{/foreach}
					</tbody>
				</table>
			</div>
			{/foreach}
		{/if}
	</div>
</div>

<div class="border tabMenuContent hidden" id="stat-content">
	<div class="container-1">
		<h3 class="subHeadline">
			{lang}pb.acp.index.stat{/lang}
		</h3>

		<div class="formElement">
			<p class="formFieldLabel">{lang}pb.acp.index.stat.repository{/lang}</p>
			<p class="formField">{@$size['repository']}</p>
		</div>
		<div class="formElement">
			<p class="formFieldLabel">{lang}pb.acp.index.stat.build{/lang}</p>
			<p class="formField">{@$size['build']}</p>
		</div>
	</div>
</div>

<div class="border tabMenuContent hidden" id="credits-content">
	<div class="container-1">
		<h3 class="subHeadline">
			{lang}pb.acp.index.credits{/lang}
		</h3>
		<div class="formElement">
			<p class="formFieldLabel">{lang}pb.acp.index.credits.developedBy{/lang}</p>
			<p class="formField"><a href="{@RELATIVE_WCF_DIR}acp/dereferrer.php?url={"http://community.woltlab.com"|rawurlencode}" class="externalURL">WoltLab&reg; Community&trade;</a></p>
		</div>
		<div class="formElement">
			<p class="formFieldLabel">{lang}pb.acp.index.credits.productManager{/lang}</p>
			<p class="formField">Alexander Ebert</p>
		</div>
		<div class="formElement">
			<p class="formFieldLabel">{lang}pb.acp.index.credits.developer{/lang}</p>
			<p class="formField">Tim D&uuml;sterhus, Alexander Ebert</p>
		</div>
		<div class="formGroup">
			<div class="formGroupLabel">{lang}pb.acp.index.credits.license{/lang}</div>
			<div class="formGroupField container-2">
				<fieldset>
					This program is free software: you can redistribute it and/or modify
					it under the terms of the GNU Lesser General Public License as published by
					the Free Software Foundation, either version 3 of the License, or
					(at your option) any later version.<br /><br />
					
					This program is distributed in the hope that it will be useful,
					but WITHOUT ANY WARRANTY; without even the implied warranty of
					MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
					GNU Lesser General Public License for more details.<br /><br />
					
					You should have received a copy of the GNU Lesser General Public License
					along with this program.  If not, see <a href="{@RELATIVE_WCF_DIR}acp/dereferrer.php?url={"http://www.gnu.org/licenses/lgpl.html"|rawurlencode}" class="externalURL">http://www.gnu.org</a>
				</fieldset>
			</div>
		</div>
	</div>
</div>

{if $additionalTabContents|isset}{@$additionalTabContents}{/if}

{include file='footer'}