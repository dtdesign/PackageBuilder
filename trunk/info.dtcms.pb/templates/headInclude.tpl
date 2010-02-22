		<meta http-equiv="content-type" content="text/html; charset={@CHARSET}" />
		<meta http-equiv="content-script-type" content="text/javascript" />
		<meta http-equiv="content-style-type" content="text/css" />
		<meta name="description" content="{META_DESCRIPTION}" />
		<meta name="keywords" content="{META_KEYWORDS}" />
		{if !$allowSpidersToIndexThisPage|isset}<meta name="robots" content="noindex,nofollow" />{/if}

		{* WCF JavaScript *}
		<script type="text/javascript" src="{@RELATIVE_WCF_DIR}js/3rdParty/protoaculous.1.8.2.min.js"></script>
		<script type="text/javascript" src="{@RELATIVE_WCF_DIR}js/default.js"></script>
		<script type="text/javascript" src="{@RELATIVE_WCF_DIR}js/AjaxRequest.class.js"></script>
		{* /WCF JavaScript *}

		{* dynamic JavaScript *}
		{if $additionalJavaScript|isset}{@$additionalJavaScript}{/if}
		{* /dynamic JavaScript *}

		{* PB stylesheets *}
		<link rel="stylesheet" type="text/css" href="{@RELATIVE_PB_DIR}style/pb.css" />
		{* /PB stylesheets *}

		{* ie fixes *}
		<!--[if lte IE 7]>
		<link rel="stylesheet" type="text/css" href="{@RELATIVE_PB_DIR}style/pb-patch.css" />
		<![endif]-->
		{* /ie fixes *}

		{* dynamic stylesheets *}
		{if $additionalCSS|isset}{@$additionalCSS}{/if}
		{* /dynamic stylesheets *}

		{if $this->getStyle()->getVariable('global.favicon')}<link rel="shortcut icon" href="{@RELATIVE_WCF_DIR}icon/favicon/favicon{$this->getStyle()->getVariable('global.favicon')|ucfirst}.ico" type="image/x-icon" />{/if}