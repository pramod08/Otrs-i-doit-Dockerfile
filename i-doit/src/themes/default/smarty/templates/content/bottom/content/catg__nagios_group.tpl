<h3 class="p5 gradient border-bottom"">[{isys type="lang" ident="LC__CMDB__CATG__NAGIOS_EXPORT"}]</h3>

<table class="contentTable">
	<tr>
	    <td class="key">[{isys type='f_label' name='C__CATG__NAGIOS_GROUP_IS_EXPORTABLE' ident="LC__CATG__NAGIOS_CONFIG_EXPORT"}]</td>
	    <td class="value">[{isys type="f_dialog" name="C__CATG__NAGIOS_GROUP_IS_EXPORTABLE" p_bDbFieldNN=1}]</td>
	</tr>
	<tr>
	    <td colspan="2">
	        <hr class="mt5 mb5"/>
	    </td>
	</tr>
	<tr>
	    <td class="key">[{isys type='f_label' name='C__CATG__NAGIOS_GROUP_NAME' ident="LC__CATG__NAGIOS_GROUP_NAME"}]</td>
	    <td class="value">
	        [{if isys_glob_is_edit_mode()}]
	        <input type="radio" name="C__CATG__NAGIOS_GROUP_NAME_SELECTION" value="[{$smarty.const.C__CATG_NAGIOS__NAME_SELECTION__INPUT}]" style="margin-left: 20px;" [{if $group_name_selection == $smarty.const.C__CATG_NAGIOS__NAME_SELECTION__INPUT}]checked="checked"[{/if}] /> [{isys type="f_text" name="C__CATG__NAGIOS_GROUP_NAME" p_strClass="normal" p_bInfoIconSpacer=0}]<br/>
	        <label><input type="radio" name="C__CATG__NAGIOS_GROUP_NAME_SELECTION" value="[{$smarty.const.C__CATG_NAGIOS__NAME_SELECTION__OBJ_ID}]" style="margin-left: 20px;" [{if $group_name_selection == $smarty.const.C__CATG_NAGIOS__NAME_SELECTION__OBJ_ID}]checked="checked"[{/if}] /> [{isys type="lang" ident="LC__UNIVERSAL__OBJECT_TITLE"}]</label>
	        [{else}]
	        <img src="[{$dir_images}]empty.gif" width="20" height="1"/>[{$group_name_view}]
	        [{/if}]
	    </td>
	</tr>
    <tr>
        <td class="key">[{isys type='f_label' name='C__CATG__NAGIOS_GROUP_TYPE' ident="LC__CATG__NAGIOS_GROUP_TYPE"}]</td>
        <td class="value">[{isys type="f_dialog" name="C__CATG__NAGIOS_GROUP_TYPE"}]</td>
    </tr>
	<tr>
	    <td class="key">[{isys type='f_label' name='C__CATG__NAGIOS_GROUP_ALIAS' ident="Alias"}]</td>
	    <td class="value">[{isys type="f_text" name="C__CATG__NAGIOS_GROUP_ALIAS" p_strPlaceholder="alias"}]</td>
	</tr>
    <tr>
        <td class="key">[{isys type='f_label' name='C__CATG__NAGIOS_GROUP_NOTES' ident="Notes"}]</td>
        <td class="value">[{isys type="f_text" name="C__CATG__NAGIOS_GROUP_NOTES" p_strPlaceholder="notes"}]</td>
    </tr>
    <tr>
        <td class="key">[{isys type='f_label' name='C__CATG__NAGIOS_GROUP_NOTES_URL' ident="Notes URL"}]</td>
        <td class="value">[{isys type="f_text" name="C__CATG__NAGIOS_GROUP_NOTES_URL" p_strPlaceholder="notes_url"}]</td>
    </tr>
    <tr>
        <td class="key">[{isys type='f_label' name='C__CATG__NAGIOS_GROUP_ACTION_URL' ident="Action URL"}]</td>
        <td class="value">[{isys type="f_text" name="C__CATG__NAGIOS_GROUP_ACTION_URL" p_strPlaceholder="action_url"}]</td>
    </tr>
</table>

<script>
	(function () {
		"use strict";

		var group_name = $('C__CATG__NAGIOS_GROUP_NAME'),
			group_name_radios = $$('input[name="C__CATG__NAGIOS_GROUP_NAME_SELECTION"]');

		if (group_name && group_name_radios.length == 2) {
			group_name.on('focus', function () {
				group_name_radios[0].checked = true;
				group_name_radios[1].checked = false;
			});
		}
	}());
</script>