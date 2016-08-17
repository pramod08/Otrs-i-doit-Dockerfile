<style type="text/css">
	#csv-import tr .btn {
		visibility: hidden;
	}

	#csv-import tr:hover {
		background: #eee;
	}

	#csv-import tr:hover .btn {
		visibility: visible;
	}
</style>

<div id="csv-import">
	[{include file="modules/import/import_fileupload.tpl"}]

	<hr/>

	<ul id="csv-import-tabs" class="m0 gradient browser-tabs">
		<li><a href="#import_csv">CSV</a></li>
		[{foreach $import_filter as $key => $filter}]<li><a href="#import_[{$key}]">[{$filter}]</a></li>[{/foreach}]
	</ul>

	<div id="import_csv">
		<table class="mainTable" cellpadding="0" cellspacing="0">
			<thead>
			<tr>
				<th>[{isys type="lang" ident="LC__UNIVERSAL__FILE_TITLE"}] / [{isys type="lang" ident="LC__SETTINGS__SYSTEM__OPTIONS"}]</th>
			</tr>
			</thead>
			<tbody>
			[{foreach from=$import_files item="im"}]
				[{if ($im.type == 'csv')}]
				<tr class="[{cycle values="line1,line0"}]" data-filename="[{$im.filename}]">
					<td>
						<button type="button" class="fr btn btn-small mr5 delete-import">
							<img src="[{$dir_images}]icons/silk/cross.png" class="mr5"/><span>[{isys type="lang" ident="LC__UNIVERSAL__DELETE_FILE"}]</span>
						</button>
						<a href="[{$im.download}]" class="fr btn btn-small mr5">
							<img src="[{$dir_images}]icons/silk/disk.png" class="mr5"><span>[{isys type="lang" ident="LC__UNIVERSAL__DOWNLOAD_FILE"}]</span>
						</a>
						<button type="button" class="fr btn btn-small start-import mr5">
							<img src="[{$dir_images}]icons/silk/table_row_insert.png" class="mr5"/><span>[{isys type="lang" ident="LC__MODULE__IMPORT__CSV__USE_FILE"}]</span>
						</button>

						<strong>[{$im.stripped}]</strong>
					</td>
				</tr>
				[{/if}]
				[{/foreach}]
			</tbody>
		</table>
	</div>

	[{foreach from=$import_filter key="key" item="filter"}]
	<div id="import_[{$key}]">
		<table class="mainTable" cellpadding="0" cellspacing="0" data-key="[{$key}]">
			<thead>
			<tr>
				<th>[{isys type="lang" ident="LC__UNIVERSAL__FILE_TITLE"}] / [{isys type="lang" ident="LC__SETTINGS__SYSTEM__OPTIONS"}]</th>
			</tr>
			</thead>
			<tbody>
			[{foreach $import_files as $file}]
				[{if ($file.type == 'csv')}]
				<tr class="[{cycle values="line1,line0"}]" data-filename="./imports/[{$file.filename}]">
					<td>
						<button type="button" class="fr btn btn-small mr5 start-import-handler">
							<img src="[{$dir_images}]icons/silk/table_row_insert.png" class="mr5"/><span>[{isys type="lang" ident="LC__UNIVERSAL__IMPORT"}]</span>
						</button>
						<strong>[{$file.stripped}]</strong>
					</td>
				</tr>
				[{/if}]
				[{/foreach}]
			</tbody>
		</table>

		<pre id="import_result_[{$key}]" class="bg-lightgrey border m5" style="height:400px;display:none;overflow:scroll;font-family:Courier New, Monospace;"></pre>
	</div>
	[{/foreach}]

	<input type="hidden" name="file" id="selected_file" />
	<input type="hidden" name="type" id="type" />
	<input type="hidden" name="verbose" id="1" />
</div>


<script type="text/javascript">
	(function () {
		'use strict';

		var $container = $('csv-import');

		new Tabs('csv-import-tabs', {
			wrapperClass: 'browser-tabs',
			contentClass: 'browser-tab-content',
			tabClass:     'text-shadow'
		});

		$container.select('.start-import').invoke('on', 'click', function (ev) {
			var filename = ev.findElement('button').up('tr').readAttribute('data-filename');

			// Set import type and filename.
			$('type').setValue('csv');
			$('selected_file').setValue(filename);
			$('isys_form').writeAttribute('action', '[{$form_action_url}]').submit();
		});

		$container.select('.start-import-handler').invoke('on', 'click', function (ev) {
			var $tr = ev.findElement('button').up('tr'),
				key = $tr.up('table').readAttribute('data-key'),
				$result = $('import_result_' + key);

			// Set import type and filename.
			$('type').setValue(key);
			$('selected_file').setValue($tr.readAttribute('data-filename'));

			$result.update('<img src="[{$dir_images}]ajax-loading.gif" class="m5 vam" /> <span>[{isys type="lang" ident="LC__UNIVERSAL__LOADING"}]</span>').show();

			aj_submit('[{$config.www_dir}]controller.php?load=import', 'get', $result, 'isys_form');
		});

		$container.select('.delete-import').invoke('on', 'click', function (ev) {
			var $tr = ev.findElement('button').up('tr');

			new Ajax.Request(document.location.href, {
				method: 'post',
				parameters: {
					delete_import: $tr.readAttribute('data-filename')
				},
				onSuccess: function (r) {
					$tr.remove();
					$('infoBox').down('div:not(.version)')
						.update(new Element('img', {src:'[{$dir_images}]icons/silk/information.png', className:'vat mr5'}))
						.insert(r.responseText).highlight();
				}
			});
		});
	})();
</script>