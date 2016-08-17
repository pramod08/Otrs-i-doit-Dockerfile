<table class="contentTable">
	<tr>
		<td class="key">[{isys type="f_label" name="C__REPORT__VIEW__MAINTENANCE_EXPORT__TITLE" ident="LC__REPORT__VIEW__MAINTENANCE_EXPORT__TITLE"}]</td>
		<td class="value">[{isys type="f_text" name="C__REPORT__VIEW__MAINTENANCE_EXPORT__TITLE"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" name="C__REPORT__VIEW__MAINTENANCE_EXPORT__TYPE" ident="LC__REPORT__VIEW__MAINTENANCE_EXPORT__TYPE"}]</td>
		<td class="value">[{isys type="f_dialog" name="C__REPORT__VIEW__MAINTENANCE_EXPORT__TYPE"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" name="C__REPORT__VIEW__MAINTENANCE_EXPORT__FROM__VIEW" ident="LC__REPORT__VIEW__MAINTENANCE_EXPORT__FROM"}]</td>
		<td class="value">[{isys type="f_popup" name="C__REPORT__VIEW__MAINTENANCE_EXPORT__FROM"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" name="C__REPORT__VIEW__MAINTENANCE_EXPORT__TO__VIEW" ident="LC__REPORT__VIEW__MAINTENANCE_EXPORT__TO"}]</td>
		<td class="value">[{isys type="f_popup" name="C__REPORT__VIEW__MAINTENANCE_EXPORT__TO"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" name="C__REPORT__VIEW__MAINTENANCE_EXPORT__LOGO__VIEW" ident="LC__REPORT__VIEW__MAINTENANCE_EXPORT__LOGO"}]</td>
		<td class="value">[{isys type="f_popup" p_strPopupType="browser_file" name="C__REPORT__VIEW__MAINTENANCE_EXPORT__LOGO" p_bReadonly=false}]</td>
	</tr>
	<tr>
		<td class="key"></td>
		<td class="value">
			<button type="button" id="C__REPORT__VIEW__MAINTENANCE_EXPORT__BUTTON" class="btn m20">
				<img src="[{$dir_images}]icons/silk/arrow_down.png" class="mr5" /><span>[{isys type="lang" ident="LC__REPORT__VIEW__MAINTENANCE_EXPORT__EXPORT"}]</span>
			</button>
		</td>
	</tr>
</table>

<script type="text/javascript">
	(function () {
		'use strict';

		var $export_button = $('C__REPORT__VIEW__MAINTENANCE_EXPORT__BUTTON');

		if ($export_button) {
			$export_button.on('click', function () {
				var url_params = '[{$url}]'.parseQuery();

				url_params.title = $F('C__REPORT__VIEW__MAINTENANCE_EXPORT__TITLE');
				url_params.date_from = $F('C__REPORT__VIEW__MAINTENANCE_EXPORT__FROM__HIDDEN');
				url_params.date_to = $F('C__REPORT__VIEW__MAINTENANCE_EXPORT__TO__HIDDEN');
				url_params.type = $F('C__REPORT__VIEW__MAINTENANCE_EXPORT__TYPE');
				url_params.logo_obj_id = $F('C__REPORT__VIEW__MAINTENANCE_EXPORT__LOGO__HIDDEN');
				url_params.download_export = 1;

				document.location.href = '?' + Object.toQueryString(url_params);
			});
		}
	})();
</script>

<script type="text/javascript" src="[{$dir_tools}]js/ajax_upload/fileuploader.js"></script>