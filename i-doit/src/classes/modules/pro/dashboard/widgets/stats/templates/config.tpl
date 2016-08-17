<table class="contentTable">
	<tr>
		<td class="key">
			[{isys type="f_label" name="widget-popup-config-title" ident="LC__WIDGET__STATS__CONFIG_TITLE"}]
		</td>
		<td class="value">
			[{isys type="f_text" id="widget-popup-config-title" p_strClass="normal" p_strValue=$rules.title}]
		</td>
	</tr>
	<tr>
		<td class="key">
			[{isys type="f_label" name="widget-popup-config-legend" ident="LC__WIDGET__STATS__CONFIG_LEGEND"}]
		</td>
		<td class="value">
			<input name="widget-popup-config-legend" id="widget-popup-config-legend" class="inputCheckbox ml20 mt5" type="checkbox" [{if $rules.legend}]checked="checked"[{/if}] />
		</td>
	</tr>
	<tr>
		<td class="key">
			[{isys type="f_label" name="widget-popup-config-type" ident="LC__WIDGET__STATS__CONFIG_TYPE"}]
		</td>
		<td class="value">
			[{isys type="f_dialog" id="widget-popup-config-type" name="widget-popup-config-type" p_arData=$rules.chart_types p_strSelectedID=$rules.selected_type p_strClass="normal" p_bDbFieldNN=true}]
		</td>
	</tr>
	<tr>
		<td class="key">
			[{isys type="f_label" name="widget-popup-config-objtypes" ident="LC__WIDGET__STATS__CONFIG_OBJ_TYPES"}]
		</td>
		<td class="value">
			[{isys type="f_dialog_list" id="objtypes" p_arData=$rules.obj_types p_strClass="normal" p_strStyle="width:250px;" add_callback="window.widget_stat_config_callback();"}]
		</td>
	</tr>
</table>

<script type="text/javascript">
	window.widget_stat_config_callback = function () {
		window.remember_values();
	};

	window.remember_values = function () {
		var config = {
			title: $F('widget-popup-config-title'),
			legend: $F('widget-popup-config-legend'),
			obj_types: $F('SelectBox__selected_values').split(','),
			chart_type: $F('widget-popup-config-type')
		};

		$('widget-popup-config-changed').setValue('1');
		$('widget-popup-config-hidden').setValue(Object.toJSON(config));
	};

	$('widget-popup-config-title', 'widget-popup-config-legend', 'widget-popup-config-type').invoke('on', 'change', window.remember_values);
</script>