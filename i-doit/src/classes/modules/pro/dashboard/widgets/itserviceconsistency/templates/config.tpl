<table class="contentTable" id="config_[{$unique_id}]">
	<tr>
		<td class="key">[{isys type="f_label" name="widget-popup-config-service-selection" ident="LC__WIDGET__IT_SERVICE_CONSISTENCY__CONFIG__SERVICE_SELECTION"}]</td>
		<td class="value">[{isys
				type="f_popup"
				p_strPopupType="browser_object_ng"
				id="widget-popup-config-service-selection"
				name="widget-popup-config-service-selection"
				p_strSelectedID=$rules.service_selection
				p_strClass="input-small"
				catFilter="C__CATG__SERVICE"
				multiselection=true
				callback_accept="on_value_change();"
				callback_detach="on_value_change();"}]
			<!--
			<strong class="mt5 ml20 p5 info display-block"><img src="[{$dir_images}]icons/silk/information.png" class="mr5 vam" /><span class="vam">[{isys type="lang" ident="LC__WIDGET__IT_SERVICE_CONSISTENCY__CONFIG__SERVICE_SELECTION_DESCRIPTION"}]</span></strong>
			-->
		</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" name="widget-popup-config-show-all" ident="LC__WIDGET__IT_SERVICE_CONSISTENCY__CONFIG__SHOW_ALL"}]</td>
		<td class="value">[{isys type="f_dialog" id="widget-popup-config-show-all" name="widget-popup-config-show-all" p_arData=$dialog_show_all p_strSelectedID=$rules.show_all p_strClass="input-mini" p_bDbFieldNN=true}]</td>
	</tr>
</table>

<script type="text/javascript">
	on_value_change = function () {
		var data = {
			show_all:$F('widget-popup-config-show-all'),
			service_selection:$F('widget-popup-config-service-selection__HIDDEN')
		};

		$('widget-popup-config-hidden').setValue(Object.toJSON(data));
		$('widget-popup-config-changed').setValue('1');
	};

	on_value_change();

	$('widget-popup-config-show-all').on('change', on_value_change);
</script>