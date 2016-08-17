<table class="contentTable">
	<tr>
		<td class="key">[{isys type='f_label' name='C__CATG__MONITORING__ACTIVE' ident='LC__MONITORING__ACTIVE'}]</td>
		<td class="value">[{isys type='f_dialog' name='C__CATG__MONITORING__ACTIVE' p_strClass="small"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type='f_label' name='C__CATG__MONITORING__HOST' ident='LC__CATG__MONITORING__INSTANCE'}]</td>
		<td class="value">[{isys type='f_dialog' name='C__CATG__MONITORING__HOST' p_strClass="normal"}]</td>
	</tr>
	<tr>
		<td colspan="2"><hr class="mb5 mt5" /></td>
	</tr>
	<tr>
		<td class="key">[{isys type='f_label' name='C__CATG__MONITORING__HOSTNAME' ident='LC__CATG__MONITORING__HOSTNAME'}]</td>
		<td class="value">
			[{if isys_glob_is_edit_mode()}]
			<label><input type="radio" name="C__CATG__MONITORING__HOSTNAME" value="[{$smarty.const.C__MONITORING__NAME_SELECTION__OBJ_ID}]" class="ml20" [{if $host_name_selection == $smarty.const.C__MONITORING__NAME_SELECTION__OBJ_ID}]checked="checked"[{/if}] /> [{isys type="lang" ident="LC__UNIVERSAL__OBJECT_TITLE"}] ("<span class="grey">[{$hostname_obj_title}]</span>")</label><br />
			<label><input type="radio" name="C__CATG__MONITORING__HOSTNAME" value="[{$smarty.const.C__MONITORING__NAME_SELECTION__HOSTNAME_FQDN}]" class="ml20" [{if $host_name_selection == $smarty.const.C__MONITORING__NAME_SELECTION__HOSTNAME_FQDN}]checked="checked"[{/if}] /> [{isys type="lang" ident="LC__CATP__IP__HOSTNAME_FQDN"}] ("<span class="grey">[{$hostname_hostname_fqdn}]</span>")</label><br />
			<label><input type="radio" name="C__CATG__MONITORING__HOSTNAME" value="[{$smarty.const.C__MONITORING__NAME_SELECTION__HOSTNAME}]" class="ml20" [{if $host_name_selection == $smarty.const.C__MONITORING__NAME_SELECTION__HOSTNAME}]checked="checked"[{/if}] /> [{isys type="lang" ident="LC__CATP__IP__HOSTNAME"}] ("<span class="grey">[{$hostname_hostname}]</span>")</label><br />
			<input type="radio" name="C__CATG__MONITORING__HOSTNAME" value="[{$smarty.const.C__MONITORING__NAME_SELECTION__INPUT}]" class="ml20" [{if $host_name_selection == $smarty.const.C__MONITORING__NAME_SELECTION__INPUT}]checked="checked"[{/if}] /> [{isys type="f_text" name="C__CATG__MONITORING_HOST_NAME"}]
			[{else}]
			<span class="ml20">[{$host_name_view}]</span>
			[{/if}]
		</td>
	</tr>
</table>

[{if isys_glob_is_edit_mode()}]
<script type="text/javascript">
	(function () {
		"use strict";

		// This will be used to set the radiobox, when clicking in the input field
		var host_name = $('C__CATG__MONITORING_HOST_NAME'),
			host_name_radios = $$('input[name="C__CATG__MONITORING__HOSTNAME"]');

		if (host_name && host_name_radios.length == 4) {
			host_name.on('focus', function () {
				host_name_radios[0].checked = false;
				host_name_radios[1].checked = false;
				host_name_radios[2].checked = false;
				host_name_radios[3].checked = true;
			});
		}
	}());
</script>
[{/if}]