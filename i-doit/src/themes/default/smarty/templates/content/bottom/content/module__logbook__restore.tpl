[{assign var="mod" value=$smarty.const.C__GET__MODULE_ID}]
<script type="text/javascript">
	function switchLocalRemote() {
        $$('.remoteSource').each(function(ele){
            if(ele.visible())
            {
                ele.hide();
            }
            else
            {
                ele.show();
            }
        });
	}

	function executeRestore() {
        aj_submit('?[{$smarty.const.C__GET__MODULE_ID}]=[{$smarty.const.C__MODULE__LOGBOOK}]&request=executeRestore', 'post', 'restoreResult', 'isys_form');
	}
</script>

<table class="contentTable">
	<tr>
		<td class="key">[{isys type="lang" ident="LC__LOGBOOK__ENTRIES_NEWER_THAN"}]</td>
		<td class="value">[{isys type="f_text" p_strStyle="width:150px;" name="restoreFrom" p_bEditMode="1" tab="10"}] [{isys type="lang" ident="LC__CMDB__UNIT_OF_TIME__DAY"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type="lang" ident="LC__CMDB__LOGBOOK__SOURCE"}]:</td>
		<td class="value">[{isys type="f_dialog" name="archiveSource" p_bEditMode="1" p_bDbFieldNN="1" p_onChange="switchLocalRemote();" tab="20"}]</td>
	</tr>
	<tr class="remoteSource" [{if ($archiveDest == 0)}]style="display:none;"[{/if}]>
		<td class="key">IP:</td>
		<td class="value">[{isys type="f_text" name="archiveHost" p_bEditMode="1" tab="30"}]</td>
	</tr>
	<tr class="remoteSource" [{if ($archiveDest == 0)}]style="display:none;"[{/if}]>
		<td class="key">Port:</td>
		<td class="value">[{isys type="f_text" name="archivePort" p_bEditMode="1" tab="40"}]</td>
	</tr>
	<tr class="remoteSource" [{if ($archiveDest == 0)}]style="display:none;"[{/if}]>
		<td class="key">[{isys type="lang" ident="LC__MODULE__NAGIOS__NDODB_SCHEMA"}]:</td>
		<td class="value">[{isys type="f_text" name="archiveDB" p_bEditMode="1" tab="50"}]</td>
	</tr>
	<tr class="remoteSource" [{if ($archiveDest == 0)}]style="display:none;"[{/if}]>
		<td class="key">[{isys type="lang" ident="LC__LOGIN__USERNAME"}]:</td>
		<td class="value">[{isys type="f_text" name="archiveUser" p_bEditMode="1" tab="60"}]</td>
	</tr>
	<tr class="remoteSource" [{if ($archiveDest == 0)}]style="display:none;"[{/if}]>
		<td class="key">[{isys type="lang" ident="LC__LOGIN__PASSWORD"}]:</td>
		<td class="value">[{isys type="f_text" p_bPassword="true" name="archivePass" p_bEditMode="1" tab="70"}]</td>
	</tr>
    <tr>
        <td class="key">
            [{isys type="f_button" p_bEditMode="1" p_onClick="executeRestore()" p_strValue=$btnLabelExecute}]
        </td>
        <td class="value">

            <!--<input value="[{$btnLabelExecute}]" type="button" onclick="executeRestore();" style="margin-left:5px; margin-bottom:5px;"/>-->
        </td>
    </tr>
</table>


<fieldset class="overview">
    <legend><span>[{isys type="lang" ident="LC__SETTINGS__SYSTEM__SYS_MSG"}]</span></legend>
    <div class="mt5 p10" id="restoreResult"></div>
</fieldset>

