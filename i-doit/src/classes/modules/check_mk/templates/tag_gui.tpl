<div class="check-mk-tags">
	<ul id="check-mk-tags-tabs" class="browser-tabs m0 gradient">
		<li><a href="#check_mk-cmdb-tags">[{isys type="lang" ident="LC__MODULE__CHECK_MK__TAGS__CMDB_TAG_CONFIG"}]</a></li>
		<li><a href="#check_mk-dynamic-tags">[{isys type="lang" ident="LC__MODULE__CHECK_MK__TAGS__DYNAMIC_TAG_ASSIGNMENT"}]</a></li>
	</ul>

	<div id="check_mk-cmdb-tags">
		[{include file=$tpl_cmdb_tags}]
	</div>

	<div id="check_mk-dynamic-tags" class="m5">
		[{include file=$tpl_dynamic_tags}]
	</div>
</div>

<script type="text/javascript">
	(function () {
		"use strict";

		new Tabs('check-mk-tags-tabs', {
			wrapperClass: 'browser-tabs',
			contentClass: '',
			tabClass:'text-shadow mouse-pointer'
		});
	}());
</script>

<style type="text/css">
	#check_mk-dynamic-tags thead {
		height: 30px;
	}


	#check_mk-dynamic-tags table th,
	#check_mk-dynamic-tags table td {
		padding: 2px;
	}

	#check_mk-dynamic-tags table td {
		border-top: 1px solid #888888;
		padding: 3px;
	}

	div.check-mk-tags label {
		line-height: 18px;
	}

	div.check-mk-tags label span {
		margin-left: 20px;
		margin-top: 5px;
		display: block;
	}

	#check-mk-tags-tabs {
		list-style: none;
	}
</style>