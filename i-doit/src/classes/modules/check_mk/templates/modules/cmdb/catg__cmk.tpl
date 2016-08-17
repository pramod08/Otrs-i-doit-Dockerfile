<table class="contentTable">
	<tr>
		<td class="key">[{isys type='f_label' name='C__CATG__CMK__ACTIVE' ident='LC__CATG__CMK__ACTIVE'}]</td>
		<td class="value">[{isys type='f_dialog' name='C__CATG__CMK__ACTIVE' p_strClass="input-mini"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type='f_label' name='C__CATG__CMK__EXPORT_CONFIG' ident='LC__MONITORING__EXPORT__CONFIGURATION'}]</td>
		<td class="value">[{isys type='f_dialog' name='C__CATG__CMK__EXPORT_CONFIG' p_strClass="input-small"}]</td>
	</tr>
	<tr>
		<td colspan="2"><hr class="mb5 mt5" /></td>
	</tr>
    <tr>
        <td class="key">[{isys type='f_label' name='C__CATG__CMK__ALIAS' ident='LC__CATG__CMK__ALIAS'}]</td>
        <td class="value">[{isys type='f_text' name='C__CATG__CMK__ALIAS'}]</td>
    </tr>
	<tr>
		<td class="key">[{isys type='f_label' name='C__CATG__CMK__HOSTNAME' ident='LC__CATG__CMK__HOSTNAME'}]</td>
		<td class="value">
			[{if isys_glob_is_edit_mode()}]
			<label><input type="radio" name="C__CATG__CMK__HOSTNAME" class="ml20 mr5 input" value="[{$smarty.const.C__CATG_CHECK_MK__NAME_SELECTION__OBJ_ID}]" [{if $host_name_selection == $smarty.const.C__CATG_CHECK_MK__NAME_SELECTION__OBJ_ID}]checked="checked"[{/if}] /><span>[{isys type="lang" ident="LC__UNIVERSAL__OBJECT_TITLE"}] ("<span class="grey">[{$hostname_obj_title}]</span>")</span></label><br />
			<label><input type="radio" name="C__CATG__CMK__HOSTNAME" class="ml20 mr5 input" value="[{$smarty.const.C__CATG_CHECK_MK__NAME_SELECTION__HOSTNAME_FQDN}]" [{if $host_name_selection == $smarty.const.C__CATG_CHECK_MK__NAME_SELECTION__HOSTNAME_FQDN}]checked="checked"[{/if}] /><span>[{isys type="lang" ident="LC__CATP__IP__HOSTNAME_FQDN"}] ("<span class="grey">[{$hostname_hostname_fqdn}]</span>")</span></label><br />
			<label><input type="radio" name="C__CATG__CMK__HOSTNAME" class="ml20 mr5 input" value="[{$smarty.const.C__CATG_CHECK_MK__NAME_SELECTION__HOSTNAME}]" [{if $host_name_selection == $smarty.const.C__CATG_CHECK_MK__NAME_SELECTION__HOSTNAME}]checked="checked"[{/if}] /><span>[{isys type="lang" ident="LC__CATP__IP__HOSTNAME"}] ("<span class="grey">[{$hostname_hostname}]</span>")</span></label><br />
				   <input type="radio" name="C__CATG__CMK__HOSTNAME" class="ml20 mr5 input" value="[{$smarty.const.C__CATG_CHECK_MK__NAME_SELECTION__INPUT}]" [{if $host_name_selection == $smarty.const.C__CATG_CHECK_MK__NAME_SELECTION__INPUT}]checked="checked"[{/if}] />[{isys type="f_text" name="C__CATG__CMK_HOST_NAME"}]
			[{else}]
			<span class="ml20">[{$host_name_view}]</span>
			[{/if}]
		</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" name="C__CATG__CMK__EXPORT_IP" ident="LC__CATG__CMK__EXPORT_IP"}]</td>
		<td class="value">[{isys type='f_dialog' name='C__CATG__CMK__EXPORT_IP'}]</td>
	</tr>
	[{if $export_ip || isys_glob_is_edit_mode()}]
	<tr>
		<td class="key">[{isys type="f_label" name="C__CATG__CMK__HOSTADDRESS" ident="LC__CATG__IP_ADDRESS"}]</td>
		<td class="value">[{isys type='f_dialog' name='C__CATG__CMK__HOSTADDRESS' p_strClass="input-small"}]</td>
	</tr>
	[{/if}]
</table>

[{if isys_glob_is_edit_mode()}]
<script type="text/javascript">
	(function () {
		'use strict';

		// This will be used to set the radiobox, when clicking in the input field
		var $hostnameField = $('C__CATG__CMK_HOST_NAME'),
			$hostnameRadioBoxes = $$('input[name="C__CATG__CMK__HOSTNAME"]'),
			$exportIpField = $('C__CATG__CMK__EXPORT_IP'),
			$hostaddressField = $('C__CATG__CMK__HOSTADDRESS');

		if ($hostnameField && $hostnameRadioBoxes.length == 4) {
			$hostnameField.on('focus', function () {
				$hostnameRadioBoxes[0].checked = false;
				$hostnameRadioBoxes[1].checked = false;
				$hostnameRadioBoxes[2].checked = false;
				$hostnameRadioBoxes[3].checked = true;
			});
		}

		if ($exportIpField && $hostaddressField) {
			$exportIpField.on('change', function () {
				if ($exportIpField.getValue() == '0') {
					$hostaddressField.disable();
				} else {
					$hostaddressField.enable();
				}
			});

			$exportIpField.simulate('change');
		}
	})();
</script>
[{/if}]