<input type="hidden" name="report_id" value="[{$report_id}]" />

[{if !empty($querybuilder_warning)}]
<div class="p10">
    [{$querybuilder_warning}]
</div>
[{/if}]
<table class="contentTable">
	<tr>
		<td class="key">[{isys type="f_label" ident="LC__REPORT__FORM__TITLE" name="title"}]</td>
		<td class="value">[{isys type="f_text" name="title"}]</td>
	</tr>
    <tr>
        <td class="key vat">
            [{isys type="f_label" name="report_category" ident="LC_UNIVERSAL__CATEGORY"}]
        </td>
        <td class="value">
            [{isys type="f_dialog" p_bDbFieldNN=1 name="report_category" id="report_category" p_bEditMode=1}]
        </td>
    </tr>
	<tr>
		<td class="key vat">[{isys type="f_label" ident="LC__REPORT__FORM__DESCRIPTION" name="description"}]</td>
		<td class="value">[{isys type="f_textarea" p_nCols="100" p_nRows="5" name="description"}]</td>
	</tr>
    <tr>
        <td class="key vat">
        </td>
        <td class="value">
            <img width="15px" height="15px" style="float:left;margin-right:5px;" title="" alt="" src="[{$dir_images}]empty.gif" class="infoIcon vam">
            [{isys type="f_button" icon="images/icons/silk/table_refresh.png" name="preview_button" p_strValue="LC__UNIVERSAL__PREVIEW" p_bInfoIconSpacer="1" p_onClick=""}]
        </td>
    </tr>
	<tr>
		<td class="key vat">[{isys type="f_label" ident="LC__REPORT__FORM__QUERY" name="query"}]</td>
		<td class="value">
			<pre id="editor" data-name="query">[{isys type="f_data" name="query"}]</pre>
			[{isys type="f_textarea" p_nCols="100" p_nRows="15" name="query" id="query" p_bInfoIconSpacer=0 p_strStyle="display:none;"}]
		</td>
	</tr>
</table>

<style type="text/css" media="screen">
	.ace_editor {
		position: relative !important;
		border: 1px solid #aaa;
		margin: 0 0 0 20px;
		height: 450px;
		width: 97%;
	}
	.scrollmargin {
		height: 100px;
        text-align: center;
	}
</style>

<script type="text/javascript">
	'use strict';
    [{include file="./report.js"}]
	(function() {

		function initAce() {
			window.ace.require("ace/ext/language_tools");
			if (Prototype.Browser.IE) {
				window.ace.require("ace/ext/old_ie");
			}
			window.ace.require("ace/ext/textarea");

			var queryEditor = window.ace.edit('editor');
			queryEditor.setAutoScrollEditorIntoView(true);

			queryEditor.setTheme("ace/theme/clouds");
			queryEditor.setOptions({
				maxLines: 45,
		        enableBasicAutocompletion: true,
		        enableSnippets: false,
		        enableLiveAutocompletion: true
		    });
			queryEditor.getSession().setMode("ace/mode/mysql");

			queryEditor.on("change", function(e) {
				$('query').innerHTML = queryEditor.getValue();
			});
		}

		if (!window.ace)
		{
			var script = new Element('script', { type: 'text/javascript', src: '[{$config.www_dir}]src/classes/modules/report/js/ace/src-min-noconflict/ace.js' });
			$$('head')[0].appendChild(script);

			script.on('load', function() {
				var aceExtensions = [
					new Element('script', { type: 'text/javascript', src: '[{$config.www_dir}]src/classes/modules/report/js/ace/src-min-noconflict/ext-language_tools.js' }),
					new Element('script', { type: 'text/javascript', src: '[{$config.www_dir}]src/classes/modules/report/js/ace/src-min-noconflict/ext-old_ie.js' })
				];
				aceExtensions.each(function(ext) {
					$$('head')[0].appendChild(ext);
				});

				aceExtensions[aceExtensions.length-1].on('load', function() {
					initAce();
				});
			});
		}
		else
		{
			initAce();
		}

		$('preview_button').on('click', function () {
			get_popup('report', '', 800, 508, {func: 'report_preview_sql'});
		});
	})();
</script>