<table class="contentTable">
	<tr>
		<td class="key">[{isys type="f_label" name="C__CATG__ACCESS_TITLE" ident="LC__CMDB__CATG__ACCESS_TITLE"}]</td>
		<td class="value">[{isys type="f_text" name="C__CATG__ACCESS_TITLE"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" name="C__CATG__ACCESS_TYPE" ident="LC__CMDB__CATG__ACCESS_TYPE"}]</td>
		<td class="value">[{isys type="f_popup" p_strPopupType="dialog_plus" name="C__CATG__ACCESS_TYPE" p_strTable="isys_access_type"}]</td>
	</tr>
	<tr>
		<td class="key vat">[{isys type="f_label" name="C__CATG__ACCESS_URL" ident="LC__CMDB__CATG__ACCESS_URL"}]</td>
		<td class="value">
            [{isys type="f_link" name="C__CATG__ACCESS_URL"}] <img src="images/icons/silk/help.png" class="vam" onclick="$('accessPlaceholders').slideDown({duration:0.2});" />

            <div class="box ml20 mt5 mb5 overflow-auto text-shadow" style="display:none;height:200px;width:400px;" id="accessPlaceholders">
                <table class="border-none m0 listing hover" style="border:0;">
                    [{foreach from=$accessPlaceholders item="plholder" key="plkey"}]
                    <tr>
                        <td class="mouse-pointer">
                            <span><code>[{$plkey}]</code> = [{$plholder}]</span>
                        </td>
                    </tr>
                    [{/foreach}]
                </table>
            </div>
        </td>
	</tr>
	<tr>
        <td class="key">[{isys type="f_label" name="C__CATG__ACCESS_PRIMARY" ident="LC__CMDB__CATG__ACCESS_PRIMARY"}]</td>
        <td class="value">[{isys type="f_dialog" name="C__CATG__ACCESS_PRIMARY" p_bDbFieldNN="1"}]</td>
    </tr>
</table>
