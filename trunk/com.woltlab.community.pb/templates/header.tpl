<div class="page_margins">
	<div class="page">
		<div id="header">
			<a href="{RELATIVE_PB_DIR}index.php?page=Index"><img src="style/yaml/screen/images/packageBuilder.png" alt="PackageBuilder" title="PackageBuilder" /></a>
			<div id="topnav">
				<!-- start: skip link navigation -->
				<a class="skip" title="skip link" href="#navigation">Skip to the navigation</a><span class="hideme">.</span>
				<a class="skip" title="skip link" href="#content">Skip to the content</a><span class="hideme">.</span>
				<!-- end: skip link navigation -->

				{if $this->user->userID == 0}
					<a href="index.php?form=UserLogin"><img src="{icon}loginS.png{/icon}" alt="" title="{lang}wcf.user.login{/lang}" /> {lang}wcf.user.login{/lang}</a>
					{if !REGISTER_DISABLED}
					| <a href="index.php?page=Register{@SID_ARG_2ND}"><img src="{icon}registerS.png{/icon}" alt="" /> {lang}pb.header.userMenu.register{/lang}</a></li>
					{/if}
				{else}
					{lang}wcf.acp.user.userNote{/lang}
					<img src="{icon}logoutS.png{/icon}" alt="" title="{lang}wcf.user.logout{/lang}" style="margin-bottom: -3px;" /> <a href="index.php?action=UserLogout&amp;t={@SECURITY_TOKEN}">{lang}wcf.user.logout{/lang}</a>
					{if $this->user->getPermission('admin.general.canUseAcp')}
					| <a href="{@RELATIVE_PB_DIR}acp/index.php?form=Login">{lang}pb.header.userMenu.acp{/lang}</a>
					{/if}
				{/if}
			</div>
		</div>

		{include file='headerMenu'}

		<div id="main">
