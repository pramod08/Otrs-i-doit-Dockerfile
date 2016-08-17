[{isys_group name="tom.popup.visualization"}]
<table class="contentTable">
	<tr>
		<td class="key">[{isys type="f_label" name="C_VISUALIZATION_OBJ_SELECTION" ident="LC_UNIVERSAL__OBJECT"}]</td>
		<td class="value">[{isys name="C_VISUALIZATION_OBJ_SELECTION" type="f_popup" p_strPopupType="browser_object_ng" p_onChange="window.on_value_change();"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" name="C_VISUALIZATION_SERVICE_FILTER" ident="LC__ITSERVICE__CONFIG"}]</td>
		<td class="value">[{isys name="C_VISUALIZATION_SERVICE_FILTER" type="f_dialog"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" name="C_VISUALIZATION_PROFILE" ident="LC__VISUALIZATION_PROFILES"}]</td>
		<td class="value">[{isys name="C_VISUALIZATION_PROFILE" type="f_dialog"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" name="C_VISUALIZATION_ORIENTATION" ident="LC__MODULE__CMDB__VISUALIZATION__ORIENTATION"}]</td>
		<td class="value">[{isys name="C_VISUALIZATION_ORIENTATION" type="f_dialog"}]</td>
	</tr>
</table>
[{/isys_group}]

<script type="text/javascript">
	(function () {
		'use strict';

		var $popup = $('widget-popup-config-container');

		$popup.on('change', 'select', function () {
			window.on_value_change();
		});
	})();

	window.on_value_change = function () {
		$('widget-popup-config-hidden').setValue(
			Object.toJSON({
				objid: $F('C_VISUALIZATION_OBJ_SELECTION__HIDDEN'),
				servicefilter_id: $F('C_VISUALIZATION_SERVICE_FILTER'),
				profile_id: $F('C_VISUALIZATION_PROFILE'),
				orientation: $F('C_VISUALIZATION_ORIENTATION')
			})
		);

		$('widget-popup-config-changed').setValue('1');
	};

	$('widget-popup-accept').on('click', window.on_value_change.bindAsEventListener());
</script>

<style type="text/css">
	#widget-popup-config-container td.key {
		width:200px;
	}
</style>