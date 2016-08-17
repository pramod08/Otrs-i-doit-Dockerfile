<table class="contentTable">
    <tr>
        <td class="key">[{isys type='f_label' name='C__CATG__PLANNING__STATUS' ident="LC__UNIVERSAL__CMDB_STATUS"}]</td>
        <td class="value">
            <div class="fl">[{isys type="f_dialog" default="n/a" p_bDbFieldNN="1" name="C__CATG__PLANNING__STATUS" p_strTable="isys_cmdb_status"}]</div>
            <div class="cmdb-marker" id="cmdb_status_color" style="margin-top:0;margin-left:5px;background-color:#[{$status_color}]; height:[{if isys_glob_is_edit_mode()}]18[{else}]14[{/if}]px;"></div>
            <br class="cb"/>
        </td>
    </tr>
    <tr>
        <td class="key">[{isys type='f_label' name='C__CATG__PLANNING__START__VIEW' ident="LC__UNIVERSAL__VALIDITY"}]</td>
        <td class="value">
            [{isys type="f_popup" name="C__CATG__PLANNING__START" p_strPopupType="calendar" disablePastDate="true" p_calSelDate="" p_bTime="0"}]
            <span class="ml5 mr5">[{isys type="lang" ident="LC__UNIVERSAL_TO"}]</span>
            [{isys type="f_popup" name="C__CATG__PLANNING__END" p_strPopupType="calendar" disablePastDate="true" p_calSelDate="" p_bTime="0" p_bInfoIconSpacer="0"}]
        </td>
    </tr>
</table>

<script type="text/javascript">
	(function () {
		"use strict";

		var cmdb_status = $('C__CATG__PLANNING__STATUS'),
			cmdb_status_colors = '[{$status_colors}]'.evalJSON();

		if (cmdb_status) {
			cmdb_status.on('change', function () {
				var selected_cmdb_status = $F(this);

				if (cmdb_status_colors.hasOwnProperty(selected_cmdb_status)) {
					$('cmdb_status_color').setStyle({backgroundColor: cmdb_status_colors[selected_cmdb_status]});
				}
			});

			cmdb_status.simulate('change');
		}
	}());
</script>