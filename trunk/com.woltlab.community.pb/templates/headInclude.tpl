<meta http-equiv="content-type" content="text/html; charset={@CHARSET}" />
<meta http-equiv="content-script-type" content="text/javascript" />
<meta http-equiv="content-style-type" content="text/css" />
<meta name="description" content="{META_DESCRIPTION}" />
<meta name="keywords" content="{META_KEYWORDS}" />
{if !$allowSpidersToIndexThisPage|isset}<meta name="robots" content="noindex,nofollow" />{/if}

<script type="text/javascript" src="{@RELATIVE_WCF_DIR}js/3rdParty/protoaculous.1.8.2.min.js"></script>
<script type="text/javascript" src="{@RELATIVE_WCF_DIR}js/default.js"></script>
<script type="text/javascript" src="{@RELATIVE_WCF_DIR}js/AjaxRequest.class.js"></script>

<script type="text/javascript">
	//<![CDATA[
	var RELATIVE_PB_DIR = '{@RELATIVE_PB_DIR}';
	var RELATIVE_WCF_DIR = '{@RELATIVE_WCF_DIR}';
	var SID_ARG_2ND	= '{@SID_ARG_2ND_NOT_ENCODED}';
	//]]>
</script>

{if $additionalJavaScript|isset}{@$additionalJavaScript}{/if}

<link rel="stylesheet" type="text/css" href="{@RELATIVE_PB_DIR}style/pb.css" />

<!--[if lte IE 7]>
	<link rel="stylesheet" type="text/css" href="{@RELATIVE_PB_DIR}style/pb-patch.css" />
<![endif]-->

{if $additionalCSS|isset}{@$additionalCSS}{/if}

{if $this->getStyle()->getVariable('global.favicon')}<link rel="shortcut icon" href="{@RELATIVE_WCF_DIR}icon/favicon/favicon{$this->getStyle()->getVariable('global.favicon')|ucfirst}.ico" type="image/x-icon" />{/if}