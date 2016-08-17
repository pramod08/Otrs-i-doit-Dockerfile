<div id="basic_auth">
	<h3 class="p5 gradient border-bottom">[{isys type="lang" ident="LC__CMDB__CATS__BASIC_AUTH"}]</h3>

	<p class="mt10">[{isys type="lang" ident="LC__AUTH_GUI__INHERITED_RIGHTS_MESSAGE" p_bHtmlEncode=false}]</p>

	<div id="path_table" class="border mt10 m10"></div>

	[{if $edit_mode}]
		<button class="btn m5 mt15" type="button" id="new_path">
			<img src="[{$dir_images}]icons/silk/add.png" class="mr5" />
			<span class="vam">[{isys type="lang" ident="LC__AUTH_GUI__NEW_RIGHT"}]</span>
		</button>
	[{/if}]
</div>

<script>
	[{include file="src/tools/js/auth/simple_configuration.js"}]

	// Setting some translations...
	idoit.Translate.set('LC__AUTH_GUI__REFERS_TO', '[{isys type="lang" ident="LC__AUTH_GUI__REFERS_TO"}]');
	idoit.Translate.set('LC__UNIVERSAL__REMOVE', '[{isys type="lang" ident="LC__UNIVERSAL__REMOVE"}]');
	idoit.Translate.set('LC__UNIVERSAL__COPY', '[{isys type="lang" ident="LC__UNIVERSAL__COPY"}]');
	idoit.Translate.set('LC__UNIVERSAL__LOADING', '[{isys type="lang" ident="LC__UNIVERSAL__LOADING"}]');
	idoit.Translate.set('LC__UNIVERSAL__ALL', '[{isys type="lang" ident="LC__UNIVERSAL__ALL"}]');
	// Translations for the table-header.
	idoit.Translate.set('LC__AUTH_GUI__AUTH_MODULES', '[{isys type="lang" ident="LC__AUTH_GUI__AUTH_MODULES"}]');
	idoit.Translate.set('LC__AUTH_GUI__ACTION', '[{isys type="lang" ident="LC__AUTH_GUI__ACTION"}]');
	window.dir_images = '[{$dir_images}]';

	var config = new SimpleAuthConfiguration('path_table', {
		rights:[{$rights}],
		modules:[{$modules}],
		paths:[{$paths}],
		inherited_paths:[{$inherited_paths}],
		edit_mode:[{$edit_mode}]
	});

	[{if $edit_mode}]
	$('new_path')
		.removeClassName('hide')
		.stopObserving()
		.on('click', config.create_new_path.bindAsEventListener(config));
	[{/if}]
</script>

<style type="text/css">
	#basic_auth #path_table {
		display:block;
	}

	#basic_auth #path_table thead {
		height: 30px;
	}

	#basic_auth #path_table th {
		text-align: center;
	}

	#basic_auth #path_table tr.inactive {
		background:#e8e8e8;
	}

	#basic_auth #path_table th,
	#basic_auth #path_table td {
		padding: 2px;
	}

	#basic_auth #path_table tbody td {
		border-top: 1px solid #888888;
		padding: 3px;
	}
</style>