[{assign var="l_accept" value=$smarty.const.C__WORKFLOW__ACTION__TYPE__COMPLETE}]
[{assign var="l_cancel" value=$smarty.const.C__WORKFLOW__ACTION__TYPE__CANCEL}]

<script type="text/javascript">
	function switch_cancel() {
		var l_action = document.getElementsByName('C__WF__ACTION')[0];
		var l_check  = document.getElementById('check_cancel');

		if (l_action.value=='[{$l_cancel}]') {
			l_action.value='[{$l_accept}]';
			document.getElementById('cancel_description').style.display='none';
			l_check.checked='';
		} else {
			l_action.value='[{$l_cancel}]';
			document.getElementById('cancel_description').style.display='';
			l_check.checked='checked';
		}
	}
	document.getElementsByName('C__WF__ACTION')[0].value = '[{$l_accept}]';
</script>

[{if !($g_workflow_pack->get_occurrence() > 0)}]
	<table class="contentTable" cellpadding="0" cellspacing="0" style="border-top: none;">
		<tbody>
			<tr>
				<td class="key">
					[{isys type="lang" ident="LC_UNIVERSAL__FROM"}]
				</td>
				<td class="value">
					[{isys type="f_text" p_bDisabled="1" p_strValue=$g_accepted_users}]
				</td>
			</tr>

			[{if $g_assign.me > 0 && empty($g_completed) && empty($g_cancelled)}]
			<tr>
				<td class="key">
					[{isys type="lang" ident="LC_WORKFLOW_DETAIL__DESCRIBE"}]
				</td>
				<td class="value">
					[{isys type="f_textarea" name="C__WF__COMPLETE_DESCRIPTION" p_bInfoIconSpacer="1" p_nRows="4" p_nCols="5" p_bEditMode="1"}]
					<!--<textarea name="C__WF__COMPLETE_DESCRIPTION" class="inputTextarea" style="margin:0;background-color:#fff;width:400px;"></textarea>-->
				</td>
			</tr>
			<tr id="cancel_description" style="display:none;">
				<td class="key">
					[{isys type="lang" ident="LC_WORKFLOW_DETAIL__MEASURES"}]
				</td>
				<td class="value">
					[{isys type="f_textarea" name="C__WF__METHOD_DESCRIPTION" p_bInfoIconSpacer="1" p_nRows="4" p_nCols="5" p_bEditMode="1"}]
					<!--<textarea name="C__WF__METHOD_DESCRIPTION" class="inputTextarea" style="margin:0;background-color:#fff;width:400px;"></textarea>-->
				</td>
			</tr>
			<tr>
				<td class="key">
					[{isys type="lang" ident="LC_WORKFLOW_DETAIL__SEND_REPORT"}]
				</td>
				<td class="value">

					[{isys
						title="LC__BROWSER__TITLE__CONTACT"
						name="WF__REPORT"
						type="f_popup"
						p_strValue=$g_assigned_contact
						p_strPopupType="browser_object_ng"
						catFilter='C__CATS__PERSON;C__CATS__PERSON_GROUP;C__CATS__ORGANIZATION'
						multiselection="true"
						p_image="true"
						p_strFormSubmit="0"
						p_iSelectedTab="1"
						p_editmode=true
						p_iEnabledPreselection="1"
						tab="4"}]
				</td>
			</tr>
			<tr>
				<td class="key">
					[{isys type="lang" ident="LC_WORKFLOW_DETAIL__COMPLETE"}]
				</td>
				<td class="value" valign="middle">
					<img class="infoIcon" src="[{$dir_images}]/empty.gif" alt="" height="15px" width="15px" style="margin-right:5px;" />
					<input type="checkbox" style="width:20px;margin-top:5px;" name="check_cancel" id="check_cancel" onclick="switch_cancel();" />
					<span onclick="switch_cancel();" style="margin-top:5px;"><strong>[{isys type="lang" ident="LC__WORKFLOW__ACTION__TYPE__CANCEL"}]</strong></span>
				</td>
				<td class="value" valign="middle">
					[{isys type="f_submit" p_bDisabled="0" p_bInfoIconSpacer="0" name="C__WF__COMPLETE" p_strStyle="width:300px;" p_strValue="LC__WORKFLOWS__SEND_REPORT"}]
				</td>
			</tr>
			[{/if}]
		</tbody>
	</table>
[{/if}]