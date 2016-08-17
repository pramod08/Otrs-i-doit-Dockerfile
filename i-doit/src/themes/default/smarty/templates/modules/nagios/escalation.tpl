<input type="hidden" name="id" value="[{$eID}]" />

<table class="contentTable">
	<tr>
		<td class="key">[{isys type="f_label" ident="Name" name="C__MODULE__NAGIOS__TITLE"}]</td>
		<td class="value">[{isys type="f_text" name="C__MODULE__NAGIOS__TITLE"}]</td>
	</tr>
	<tr>
		<td class="key">contacts: </td>
		<td class="value">
			[{isys
				title="LC__BROWSER__TITLE__CONTACT"
				name="C__MODULE__NAGIOS__CONTACTS"
				type="f_popup"
				p_strPopupType="browser_object_ng"
				catFilter='C__CATS__PERSON;C__CATS__PERSON_GROUP;C__CATS__ORGANIZATION'
				multiselection="true"
				p_image="true"
				p_strFormSubmit="0"
				p_iEnabledPreselection=1}]
		</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" ident="first_notification" name="C__MODULE__NAGIOS__FIRST_NOTIFICATION"}]</td>
		<td class="value">[{isys type="f_text" name="C__MODULE__NAGIOS__FIRST_NOTIFICATION"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" ident="last_notification" name="C__MODULE__NAGIOS__LAST_NOTIFICATION"}]</td>
		<td class="value">[{isys type="f_text" name="C__MODULE__NAGIOS__LAST_NOTIFICATION"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" ident="notification_interval" name="C__MODULE__NAGIOS__NOTIFICATION_INTERVAL"}]</td>
		<td class="value">[{isys type="f_text" name="C__MODULE__NAGIOS__NOTIFICATION_INTERVAL"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" ident="escalation_period" name="C__MODULE__NAGIOS__ESCALATION_PERIOD"}]</td>
		<td class="value">
            <img src="[{$dir_images}]empty.gif" width="20" height="1" />
            [{if isys_glob_is_edit_mode()}]<input type="radio" name="C__CHECK_PERIOD_SELECTION" disabled="disabled" />[{/if}]
            [{isys type="f_dialog" name="C__MODULE__NAGIOS__ESCALATION_PERIOD" p_strClass="normal" p_bInfoIconSpacer=0}]<br />

            <img src="[{$dir_images}]empty.gif" width="20" height="1" />
            [{if isys_glob_is_edit_mode()}]<input type="radio" name="C__CHECK_PERIOD_SELECTION" disabled="disabled" />[{/if}]
            [{isys type="f_popup" p_strPopupType="dialog_plus" name="C__MODULE__NAGIOS__ESCALATION_PERIOD_PLUS" p_strTable="isys_nagios_timeperiods_plus" p_strClass="normal mt5" p_bInfoIconSpacer=0}]
		</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" ident="escalation_options" name="C__MODULE__NAGIOS__ESCALATION_OPTIONS"}]</td>
		<td class="value">[{isys type="f_dialog_list" name="C__MODULE__NAGIOS__ESCALATION_OPTIONS" p_bLinklist="1"}]</td>
	</tr>
</table>

<script>
    var check_period = $('C__MODULE__NAGIOS__ESCALATION_PERIOD'),
        check_period_plus = $('C__MODULE__NAGIOS__ESCALATION_PERIOD_PLUS');

    if (check_period && check_period_plus) {
        check_period.on('change', function () {
            $$('input[name="C__CHECK_PERIOD_SELECTION"]')[0].checked = true;
            $$('input[name="C__CHECK_PERIOD_SELECTION"]')[1].checked = false;
            check_period_plus.selectedIndex = 0;
        });

        check_period_plus.on('change', function () {
            $$('input[name="C__CHECK_PERIOD_SELECTION"]')[0].checked = false;
            $$('input[name="C__CHECK_PERIOD_SELECTION"]')[1].checked = true;
            check_period.selectedIndex = 0;
        });

        // Visual selection (has no effect on any logic, just "looks" right).
        if ($F(check_period) > 0) {
            check_period.previous('input[type="radio"]').checked = true;
        }

        if ($F(check_period_plus) > 0) {
            check_period_plus.previous('input[type="radio"]').checked = true;
        }
    }
</script>