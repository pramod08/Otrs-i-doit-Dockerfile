<table class="contentTable">
	<tr>
		<td class="key">
			[{isys type="f_label" name="widget-popup-config-objid" ident="LC__WIDGET__OBJINFO__CONFIG__OBJECT"}]
		</td>
		<td class="value">
			[{isys
				id="widget-popup-config-objid_1"
				name="widget-popup-config-objid_1"
				type="f_popup"
				p_strPopupType="browser_object_ng"
				secondSelection=0
				p_strValue=$rules.objids.0
				p_onChange="window.on_value_change();"
				p_strStyle="width:73%;"}]
		</td>
	</tr>
	<tr>
		<td class="key">

		</td>
		<td class="value">
			[{isys
				id="widget-popup-config-objid_2"
				name="widget-popup-config-objid_2"
				type="f_popup"
				p_strPopupType="browser_object_ng"
                secondSelection=0
				p_strValue=$rules.objids.1
				p_onChange="window.on_value_change();"
				p_strStyle="width:73%;"}]
		</td>
	</tr>
	<tr>
		<td class="key">

		</td>
		<td class="value">
			[{isys
				id="widget-popup-config-objid_3"
				name="widget-popup-config-objid_3"
				type="f_popup"
				p_strPopupType="browser_object_ng"
                secondSelection=0
				p_strValue=$rules.objids.2
				p_onChange="window.on_value_change();"
				p_strStyle="width:73%;"}]
		</td>
	</tr>
</table>

<script type="text/javascript">
	window.on_value_change = function ()
	{
		$('widget-popup-config-hidden').setValue(
			Object.toJSON({
				objids: [$F('widget-popup-config-objid_1__HIDDEN'), $F('widget-popup-config-objid_2__HIDDEN'), $F('widget-popup-config-objid_3__HIDDEN')]
			})
		);
		$('widget-popup-config-changed').setValue('1');
	};

	$('widget-popup-accept').on('click', window.on_value_change.bindAsEventListener());
</script>

<style type="text/css">
	#widget-popup #widget-popup-config-container table.contentTable td.key
	{
		width:200px;
	}
</style>