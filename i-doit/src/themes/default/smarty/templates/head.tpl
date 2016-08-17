<head>
	[{if $title}]
<title>i-doit - [{$title}]</title>
	[{else}]
<title>i-doit - [{isys type="breadcrumb_navi" name="breadcrumb" p_plain=true p_append=" > "}]</title>
	[{/if}]

	<meta name="author" content="synetics gmbh" />
	<meta name="description" content="i-doit" />
	<meta name="keywords" content="i-doit, CMDB, ITSM, ITIL, NMS, Netzwerk, Dokumentation, Documentation" />
	<meta http-equiv="content-type" content="text/html; charset=[{$html_encoding|default:"utf-8"}];" />
	<meta name="robots" content="noindex" />

	<!-- This meta tag will force the internet explorer to disable the "compability" mode -->
	<!--[if IE]><meta http-equiv="X-UA-Compatible" content="IE=edge"><![endif]-->

	<link rel="icon" type="image/png" href="images/favicon.png">
	<!--[if IE]><link rel="shortcut icon" href="images/favicon.ico"/><![endif]-->

	<link rel="stylesheet" type="text/css" media="print" href="[{$dir_theme}]css/print.css" />
	<link rel="stylesheet" type="text/css" media="screen" href="?load=css&theme=[{$theme|default:"default"}]&token=[{isys_settings::get('system.last-change','1337')}]" />
	<link rel="stylesheet" type="text/css" media="screen" href="?load=mod-css&theme=[{$theme|default:"default"}]" />

    <script type="text/javascript">
        var C__CMDB__GET__OBJECT = "[{$object}]";
        var C__CMDB__GET__TREEMODE = "[{$treemode}]";
        var C__CMDB__GET__VIEWMODE = "[{$viewmode}]";
        var C__CMDB__GET__OBJECTTYPE = "[{$objtype}]";
        var C__CMDB__GET__OBJECTGROUP = "[{$objgroup}]";
        var C__GET__NAVMODE = "[{$smarty.const.C__GET__NAVMODE}]";
        var C__NAVMODE__JS_ACTION = "[{$smarty.const.C__NAVMODE__JS_ACTION}]";
    </script>

	<script type="text/javascript" src="[{$dir_tools}]js/prototype/prototype.js"></script>
	<script type="text/javascript" src="[{$dir_tools}]js/scriptaculous/src/scriptaculous.js?load=effects,dragdrop,controls"></script>
	<script type="text/javascript" src="[{$dir_tools}]js/taborder/taborder.js"></script>
    <script type="text/javascript" src="[{$dir_tools}]js/ckeditor/ckeditor.js"></script>
	<script type="text/javascript" src="[{$dir_tools}]js/bluff/js-class.js"></script>
	<script type="text/javascript" src="[{$dir_tools}]js/bluff/bluff-min.js"></script>

	<script type="text/javascript" src="[{$dir_tools}]js/scripts.js"></script>
	<script type="text/javascript" src="[{$dir_tools}]js/prototype/carousel.js"></script>
	<script type="text/javascript" src="[{$dir_tools}]js/prototip/prototip.js"></script>

	<!--[if IE]><script type="text/javascript" src="src/tools/js/excanvas/excanvas.compiled.js"></script><![endif]-->
	<script type="text/javascript" src="[{$dir_tools}]js/jit/jit.js"></script>

	<script type="text/javascript">
	/* <![CDATA[ */
		globalize(C__CMDB__GET__TREEMODE, 	'[{$smarty.get.$treemode}]');
		globalize(C__CMDB__GET__OBJECT, 	'[{$smarty.get.$object}]');
		globalize(C__CMDB__GET__OBJECTTYPE,	'[{$smarty.get.$objtype}]');

		[{include file="lang.js"}]

		Event.observe(window, 'load', function(){ onload_process(); });
	/* ]]> */
	</script>

	[{foreach from=$jsFiles item="jsFile"}]
		<script type="text/javascript" src="[{$jsFile}]"></script>
	[{/foreach}]
</head>

<body id="body">