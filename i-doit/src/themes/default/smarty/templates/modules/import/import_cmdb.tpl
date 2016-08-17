<style type="text/css">
	#import_cmdb tr .btn {
		visibility: hidden;
	}

	#import_cmdb tr:hover {
		background: #eee;
	}

	#import_cmdb tr:hover .btn {
		visibility: visible;
	}
</style>

<table class="mainTable" cellpadding="0" cellspacing="0">
    <thead>
        <tr>
            <th>[{isys type="lang" ident="LC__UNIVERSAL__FILE_TITLE"}]</th>
            <th>[{isys type="lang" ident="LC__MODULE__IMPORT__EXPORT_TYPE"}]</th>
            <th>[{isys type="lang" ident="LC__DASHBOAD__TOTAL_COUNT_OBJECTS"}]</th>
            <th>[{isys type="lang" ident="LC__MODULE__IMPORT__OCS__IMPORTED"}]</th>
            <th>[{isys type="lang" ident="LC__MODULE__IMPORT__EXPORT_SCAN_TIME"}]</th>
            <th>[{isys type="lang" ident="LC__SETTINGS__SYSTEM__OPTIONS"}]</th>
        </tr>
    </thead>
    <tbody>
        [{foreach from=$import_files item="im"}]
            [{if ($im.type == 'isys_export_type_xml')}]
            [{cycle values="line1,line0" assign="eoclass"}]
            <tr class="[{$eoclass}]" id="[{$im.stripped}]">
                <td>
                    <strong>[{$im.stripped}]</strong>
                </td>
                <td>
                    [{$im.type}]
                </td>
                <td>
                    [{$im.count}]
                </td>
	            <td>
		            [{if $im.importtime != ""}]
		               [{$im.importtime}]
		            [{else}]
		              [{isys type="lang" ident="LC__MODULE__IMPORT__NOT_IMPORTED"}]
		            [{/if}]
	            </td>
                <td>
                    [{$im.scantime|date_format:"%d.%m.%Y - %H:%M:%S"}]
                </td>
                <td>
	                <button type="button" class="fr btn btn-small mr5" onclick="delete_import('[{$im.filename}]');new Effect.SlideUp('[{$im.stripped}]')">
		                <img src="[{$dir_images}]icons/silk/cross.png" class="mr5" /><span>[{isys type="lang" ident="LC__UNIVERSAL__DELETE_FILE"}]</span>
	                </button>
	                <a class="fr btn btn-small mr5" href="[{$im.download}]">
		                <img src="[{$dir_images}]icons/silk/disk.png" class="mr5" /><span>[{isys type="lang" ident="LC__UNIVERSAL__DOWNLOAD_FILE"}]</span>
	                </a>
	                <button type="button" class="fr btn btn-small mr5" id="cmdb_import_button" onClick="$('type').setValue('cmdb');$('selected_file').setValue('./imports/[{$im.filename}]');submit_import('cmdb', 'import_result_cmdb')">
		                <img src="[{$dir_images}]icons/silk/table_row_insert.png" class="mr5" /><span>[{isys type="lang" ident="LC__MODULE__IMPORT__CSV__USE_FILE"}]</span>
	                </button>
                </td>
            </tr>
            [{/if}]
        [{/foreach}]
    </tbody>
</table>

<pre id="import_result_cmdb" class="bg-lightgrey border mt20 m5 p5" style="height:400px;display:none;overflow:scroll;font-family:Courier New, Monospace;"></pre>