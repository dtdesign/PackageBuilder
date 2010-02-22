{include file='header'}

<div class="mainHeadline">
	<img src="{@RELATIVE_PB_DIR}icon/source{$action|ucfirst}L.png" alt="" />
	<div class="headlineContainer">
		<h2>{lang}pb.acp.content.source.{$action}{/lang}</h2>
	</div>
</div>

{if $errorField}
	<p class="error">{lang}wcf.global.form.error{/lang}</p>
{/if}

{if $success|isset && $success}
	<p class="success">{lang}pb.acp.content.source.{$action}.success{/lang}</p>
{/if}

<div class="contentHeader">
	<div class="largeButtons">
		<ul><li><a href="index.php?page=SourceList&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}"><img src="{@RELATIVE_PB_DIR}icon/sourceM.png" alt="" title="{lang}pb.acp.content.source.listsources{/lang}" /> <span>{lang}pb.acp.content.source.listsources{/lang}</span></a></li></ul>
	</div>
</div>
<form method="post" action="index.php?form=Source{$action|ucfirst}">

	<div class="border content">
		<div class="container-1">

			<fieldset>
				<legend>{lang}pb.acp.content.source.data{/lang}</legend>

				<div class="formElement{if $errorField == 'name'} formError{/if}" id="nameDiv">
					<div class="formFieldLabel">
						<label for="name">{lang}pb.acp.content.source.name{/lang}</label>
					</div>
					<div class="formField">
						<input type="text" class="inputText" name="name" id="name" value="{$name}" />
						{if $errorField == 'name'}
							<p class="innerError">
								{if $errorType == 'empty'}{lang}wcf.global.error.empty{/lang}{/if}
							</p>
						{/if}
					</div>
					<div class="formFieldDesc hidden" id="nameHelpMessage">
						{lang}pb.acp.content.source.name.description{/lang}
					</div>
				</div>
				<script type="text/javascript">//<![CDATA[
					inlineHelp.register('name');
				//]]></script>

				<div class="formElement{if $errorField == 'sourceDirectory'} formError{/if}" id="sourceDirectoryDiv">
					<div class="formFieldLabel">
						<label for="sourceDirectory">{lang}pb.acp.content.source.sourceDirectory{/lang}</label>
					</div>
					<div class="formField">
						<input type="text" class="inputText" name="sourceDirectory" id="sourceDirectory" value="{$sourceDirectory}" />
						{if $errorField == 'sourceDirectory'}
							<p class="innerError">
								{if $errorType == 'empty'}{lang}wcf.global.error.empty{/lang}{/if}
							</p>
						{/if}
					</div>
					<div class="formFieldDesc hidden" id="sourceDirectoryHelpMessage">
						{lang}pb.acp.content.source.sourceDirectory.description{/lang}
					</div>
				</div>
				<script type="text/javascript">//<![CDATA[
					inlineHelp.register('sourceDirectory');
				//]]></script>

				<div class="formElement{if $errorField == 'buildDirectory'} formError{/if}" id="buildDirectoryDiv">
					<div class="formFieldLabel">
						<label for="buildDirectory">{lang}pb.acp.content.source.buildDirectory{/lang}</label>
					</div>
					<div class="formField">
						<input type="text" class="inputText" name="buildDirectory" id="buildDirectory" value="{$buildDirectory}" />
						{if $errorField == 'buildDirectory'}
							<p class="innerError">
								{if $errorType == 'empty'}{lang}wcf.global.error.empty{/lang}{/if}
							</p>
						{/if}
					</div>
					<div class="formFieldDesc hidden" id="buildDirectoryHelpMessage">
						{lang}pb.acp.content.source.buildDirectory.description{/lang}
					</div>
				</div>
				<script type="text/javascript">//<![CDATA[
					inlineHelp.register('buildDirectory');
				//]]></script>

			</fieldset>

			<fieldset>
				<legend>{lang}pb.acp.content.source.subversion{/lang}</legend>

				<div class="formElement" id="useSubversionDiv">
					<div class="formField">
						<label id="useSubversion"><input type="checkbox" name="useSubversion" value="1" {if $useSubversion}checked="checked" {/if}/> {lang}pb.acp.content.source.useSubversion{/lang}</label>
					</div>
					<div class="formFieldDesc hidden" id="useSubversionHelpMessage">
						<p>{lang}pb.acp.content.source.useSubversion.description{/lang}</p>
					</div>
				</div>
				<script type="text/javascript">//<![CDATA[
					inlineHelp.register('useSubversion');
				//]]></script>

				<div class="formElement" id="trustServerCertDiv">
					<div class="formField">
						<label id="trustServerCert"><input type="checkbox" name="trustServerCert" value="1" {if $trustServerCert}checked="checked" {/if}/> {lang}pb.acp.content.source.trustServerCert{/lang}</label>
					</div>
					<div class="formFieldDesc hidden" id="trustServerCertHelpMessage">
						<p>{lang}pb.acp.content.source.trustServerCert.description{/lang}</p>
					</div>
				</div>
				<script type="text/javascript">//<![CDATA[
					inlineHelp.register('trustServerCert');
				//]]></script>

				<div class="formElement">
					<div class="formFieldLabel">
						<label for="url">{lang}pb.acp.content.source.url{/lang}</label>
					</div>
					<div class="formField">
						<input type="text" class="inputText" name="url" id="url" value="{$url}" />
					</div>
					<div class="formFieldDesc hidden" id="urlHelpMessage">
						<p>{lang}pb.acp.content.source.url.description{/lang}</p>
					</div>
				</div>
				<script type="text/javascript">//<![CDATA[
					inlineHelp.register('url');
				//]]></script>

				<div class="formElement">
					<div class="formFieldLabel">
						<label for="username">{lang}pb.acp.content.source.username{/lang}</label>
					</div>
					<div class="formField">
						<input type="text" class="inputText" name="username" id="username" value="{$username}" />
					</div>
					<div class="formFieldDesc hidden" id="usernameHelpMessage">
						{lang}pb.acp.content.source.username.description{/lang}
					</div>
				</div>
				<script type="text/javascript">//<![CDATA[
					inlineHelp.register('username');
				//]]></script>

				<div class="formElement">
					<div class="formFieldLabel">
						<label for="password">{lang}pb.acp.content.source.password{/lang}</label>
					</div>
					<div class="formField">
						<input type="password" class="inputText" name="password" id="password" value="" />
					</div>
					<div class="formFieldDesc hidden" id="passwordHelpMessage">
						{lang}pb.acp.content.source.password.description{/lang}
					</div>
				</div>
				<script type="text/javascript">//<![CDATA[
					inlineHelp.register('password');
				//]]></script>

			</fieldset>

			{if $additionalFields|isset}{@$additionalFields}{/if}
		</div>
	</div>

	<div class="formSubmit">
		<input type="submit" accesskey="s" value="{lang}wcf.global.button.submit{/lang}" />
		<input type="reset" accesskey="r" value="{lang}wcf.global.button.reset{/lang}" />
		<input type="hidden" name="packageID" value="{@PACKAGE_ID}" />
		{if $sourceID|isset}
			<input type="hidden" name="sourceID" value="{@$sourceID}" />
		{/if}
 		{@SID_INPUT_TAG}
 	</div>
</form>

{include file='footer'}