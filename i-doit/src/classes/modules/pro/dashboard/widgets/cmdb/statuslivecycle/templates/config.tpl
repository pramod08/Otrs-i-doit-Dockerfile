<table class="contentTable">
	<tr>
		<td class="key">
			[{isys type="f_label" name="widget-monitoring-config-host" ident="LC__POPUP__BROWSER__UI_OBJ_SELECTION"}]
		</td>
		<td class="value">
			[{isys
				id="widget-popup-config-objects"
				name="widget-popup-config-objects"
				type="f_popup"
				multiselection=true
				p_strPopupType="browser_object_ng"
				p_strValue=$objects
				callback_accept="idoit.callbackManager.triggerCallback('widget-cmdb-status-livecycle-config-change');"
				callback_abort="idoit.callbackManager.triggerCallback('widget-cmdb-status-livecycle-config-change');"
				callback_detach="idoit.callbackManager.triggerCallback('widget-cmdb-status-livecycle-config-change');"
				p_strClass="input-small"}]
		</td>
	</tr>
</table>

<script type="text/javascript">
	(function() {
		"use strict";

		idoit.callbackManager
			.registerCallback('widget-cmdb-status-livecycle-config-change', function () {
			    $('widget-popup-config-changed').setValue('1');
			    $('widget-popup-config-hidden').setValue(Object.toJSON({
			        objects:$F('widget-popup-config-objects__HIDDEN')
			    }));
			})
				.triggerCallback('widget-cmdb-status-livecycle-config-change');
	})();
</script>