<table class="contentTable" cellpadding="0" cellspacing="0" style="border-top: none;">
	<tbody>
	<tr>
		<td class="key">
			[{isys type="lang" ident="LC__TASK__DETAIL__WORKORDER__ASSIGNED_PERSONS"}]
		</td>
		<td class="value">
			<input type="hidden" name="assign_action_id" value="[{$assign_id}]" />
			<input type="hidden" name="assign_contact_id" value="[{$g_assigned_contact_id}]" />

			[{isys title="LC__BROWSER__TITLE__CONTACT" name="contact_to" type="f_popup" p_strValue=$g_assigned_contact p_strPopupType="browser_object_ng" catFilter='C__CATS__PERSON;C__CATS__PERSON_GROUP;C__CATS__ORGANIZATION' multiselection="true"}]
		</td>
		[{if ($g_assign.me > 0 && empty($g_accepted)) && !($g_workflow_pack->get_occurrence() > 0)}]
		<td>
                    [{if ($closeable)}]
                            [{assign var="l_accept" value=$smarty.const.C__WORKFLOW__ACTION__TYPE__ACCEPT}]
                            [{isys type="f_submit" p_bDisabled="0" p_bInfoIconSpacer="0" p_onClick="document.getElementsByName('C__WF__ACTION')[0].value='$l_accept';" name="C__WF__ASSIGN_ACCEPT" p_strValue="LC__POPUP__DIALOG_PLUS__BUTTON_ACCEPT"}]
                        [{else}]
                            [{isys type="lang" ident="LC__TASK__DETAIL__WORKORDER__NOT_ASSIGNABLE"}]
                        [{/if}]
		</td>
		[{/if}]
	</tr>
	</tbody>
</table>