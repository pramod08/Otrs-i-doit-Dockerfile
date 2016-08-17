<table class="contentTable">
	<tr>
		<td class="key">
			[{isys type="f_label" name="widget-popup-config-report" ident="LC__WIDGET__REPORT__CONFIG__REPORT"}]
		</td>
		<td class="value">
			[{isys type="f_dialog" id="widget-popup-config-report" name="widget-popup-config-report" p_arData=$rules.report_list p_strSelectedID=$rules.selected_report p_onChange="on_value_change();" p_bDbFieldNN=true p_strClass="normal"}]
		</td>
	</tr>
	<tr>
		<td class="key">
			[{isys type="f_label" name="widget-popup-config-count" ident="LC__WIDGET__REPORTS__CONFIG__OBJECT_COUNTER"}]
		</td>
		<td class="value">
			[{isys type="f_count" id="widget-popup-config-count" name="widget-popup-config-count" p_strValue=$rules.count p_onChange="on_value_change();"}]
		</td>
	</tr>
    <tr>
        <td class="key">
            [{isys type="f_label" name="widget-popup-config-limit" ident="LC__WIDGET__REPORTS__CONFIG__OBJECT_LIMIT"}]
        </td>
        <td class="value">
            [{isys type="f_count" id="widget-popup-config-limit" name="widget-popup-config-limit" p_strValue=$rules.limit p_onChange="on_value_change();"}]
        </td>
    </tr>
</table>

<script type="text/javascript">
    $('widget-popup-config-changed').setValue('1');
    $('widget-popup-config-hidden').setValue(Object.toJSON({
        report_id:$F('widget-popup-config-report'),
        count:$F('widget-popup-config-count'),
        limit:$F('widget-popup-config-limit')
    }));

    on_value_change = function () {
		$('widget-popup-config-changed').setValue('1');
		$('widget-popup-config-hidden').setValue(Object.toJSON({
			report_id:$F('widget-popup-config-report'),
			count:$F('widget-popup-config-count'),
            limit:$F('widget-popup-config-limit')
		}));
	};
</script>