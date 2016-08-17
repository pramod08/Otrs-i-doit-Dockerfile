[{isys_group name="workflows"}]

	[{if is_object($g_workflow_pack)}]
	<div id="workflow">
		<h2 class="gradient border-bottom p5 text-shadow">[{$g_workflow_type}]: <span style="color:#666;">[{$g_workflow_pack->get_title()}]</span></h2>

		<div id="workflow_meta">
			<table cellspacing="0" cellpadding="0" class="contentTable" style="border-top: none;">
				<tr>
					<td class="key"><label for="C__WF__ID">ID</label></td>
					<td class="value">
						<strong>[{isys type="f_data" p_strStyle="color:#444;" p_strValue=$g_workflow_pack->get_id()}]</strong>
					</td>
				</tr>
				<tr>
					<td class="key"><label for="C__WF__TITLE">[{isys type="lang" ident="LC__TASK__DETAIL__WORKORDER__TITLE"}]</label></td>
					<td class="value">
						<strong>[{isys type="f_text" name="C__WF__TITLE" p_strValue=$g_workflow_pack->get_title()}]</strong>
					</td>
				</tr>
				<tr>
					<td class="key"><label for="C__WF__CATEGORY">[{isys type="lang" ident="LC__TASK__DETAIL__WORKORDER__CATEGORY"}]</label></td>
					<td class="value">
						<strong>[{isys type="f_popup" p_strPopupType="dialog_plus" p_strSelectedID=$g_workflow_pack->get_category() name="C__WF__CATEGORY" p_strTable="isys_workflow_category" p_bDbFieldNN="0" tab=""}]</strong>
					</td>
				</tr>
				<tr>
					<td class="key"><label for="C__WF__AUTHOR">[{isys type="lang" ident="LC__TASK__DETAIL__WORKORDER__INITIATOR"}]</label></td>
					<td class="value">
						<strong>[{isys type="f_text" p_bDisabled="1" name="C__WF__AUTHOR__NAME" p_strValue=$g_initiator_name}]</strong>
						<input type="hidden" name="C__WF__AUTHOR" value="[{$g_current_user__id}]" />
					</td>
				</tr>
				<tr>
					<td class="key"><label for="C__WF__AUTHOR">[{isys type="lang" ident="LC__TASK__DETAIL__WORKORDER__OBJECT"}]</label></td>
					<td class="value">
						[{isys type="f_popup" p_strPopupType="browser_object_ng" name="C__WORKFLOW__OBJECT" tab="1" multiselection="true"}]
					</td>
				</tr>
                                <tr>
					<td class="key"><label for="C__WF__PARENT_WORKFLOW">[{isys type="lang" ident="LC__TASK__DETAIL__WORKORDER__PARENT_WORKFLOW"}]</label></td>
					<td class="value">
						[{isys type="f_dialog" p_arData=$workflow_list name="C__WF__PARENT_WORKFLOW" p_strSelectedID=$g_workflow_pack->get_parent()}]
					</td>
				</tr>
				<tr>
					<td class="key">Status</td>
					<td class="value">
						[{isys type="f_textarea" p_bDisabled="1" p_strValue=$g_current_status p_nCols="5" p_nRows="2"}]
					</td>
				</tr>
				[{if $g_workflow_pack->get_circular() > 0}]
				<tr>
					<td class="key">[{isys type="lang" ident="LC__TASK__DETAIL__WORKORDER__OCCURRENCE"}]</td>
					<td class="value">
						<strong>
							[{isys type="f_dialog" name="f_occurrence" p_arData=$g_occurrence_data p_strSelectedID=$g_workflow_pack->get_occurrence()}]
						</strong>

						[{if $g_workflow_pack->get_occurrence() == $smarty.const.C__TASK__OCCURRENCE__DAILY}]
							[{if isys_glob_is_edit_mode()}]
								<div id="workflow_exception">
								<label class="ml20">
									<input style="width:15px;" type="checkbox" name="f_workflow_exception[1]" value="1" id="mon" [{$g_exception_check.1}] />
									<span>[{isys type="lang" ident="LC__UNIVERSAL__CALENDAR__DAYS_MONDAY"}]</span>
								</label>

								<label class="ml10">
									<input style="width:15px;" type="checkbox" name="f_workflow_exception[2]" value="2" id="tue" [{$g_exception_check.2}] />
									<span>[{isys type="lang" ident="LC__UNIVERSAL__CALENDAR__DAYS_TUESDAY"}]</span>
								</label>

								<label class="ml10">
									<input style="width:15px;" type="checkbox" name="f_workflow_exception[3]" value="3" id="wed" [{$g_exception_check.3}] />
									<span>[{isys type="lang" ident="LC__UNIVERSAL__CALENDAR__DAYS_WEDNESDAY"}]</span>
								</label>

								<label class="ml10">
									<input style="width:15px;" type="checkbox" name="f_workflow_exception[4]" value="4" id="thu" [{$g_exception_check.4}] />
									<span>[{isys type="lang" ident="LC__UNIVERSAL__CALENDAR__DAYS_THURSDAY"}]</span>
								</label>

								<label class="ml10">
									<input  style="width:15px;" type="checkbox" name="f_workflow_exception[5]" value="5" id="fri" [{$g_exception_check.5}] />
									<span>[{isys type="lang" ident="LC__UNIVERSAL__CALENDAR__DAYS_FRIDAY"}]</span>
								</label>

								<label class="ml10">
									<input style="width:15px;" type="checkbox" name="f_workflow_exception[6]" value="6" id="sat" [{$g_exception_check.6}] />
									<span>[{isys type="lang" ident="LC__UNIVERSAL__CALENDAR__DAYS_SATURDAY"}]</span>
								</label>

								<label class="ml10">
									<input style="width:15px;" type="checkbox" name="f_workflow_exception[0]" value="0" id="sun" [{$g_exception_check.0}] />
									<span>[{isys type="lang" ident="LC__UNIVERSAL__CALENDAR__DAYS_SUNDAY"}]</span>
								</label>
								</div>
							[{elseif !empty($g_exceptions)}]
								, [{isys type="lang" ident="LC_UNIVERSAL__EXCEPT"}]: <span class="grey">[{$g_exceptions}]</span>
							[{/if}]
						[{/if}]
					</td>
				</tr>
				[{/if}]

				[{if $g_workflow_pack->get_parent()}]
				<tr>
					<td class="key"><img src="[{$dir_images}]task/task.gif" alt="Checklist" /></td>
					<td class="value">
						<span class="ml20">
							[{if $workflow_has_parent}]
								[{isys type="lang" ident="LC__WORKFLOWS__COMPONENT_OF_CHECKLIST"}]: <a href="?tvMode=4101&viewMode=4051&wid=[{$g_workflow_pack->get_parent()}]">[{$g_workflow_pack->get_title()}] ([{$g_workflow_pack->get_parent()}])</a>
							[{else}]
								[{isys type="lang" ident="LC__WORKFLOWS__COMPONENT_OF_CHECKLIST"}]: [{isys_tenantsettings::get('gui.empty_value', '-')}]
							[{/if}]
						</span>
					</td>
				</tr>
				[{/if}]
			</table>

			<input type="hidden" name="C__WF__ACTION" value="" />
		</div>

		<div id="workflow_content">
			[{include file="workflow/detail/actions.tpl"}]
		</div>
	</div>
	[{/if}]
[{/isys_group}]

<script type="text/javascript">
	(function () {
		'use strict';

		var $occurence         = $('f_occurrence'),
		    $workflowException = $('workflow_exception');

		if ($occurence && $workflowException)
		{
			$occurence.on('change', function () {
				if ($occurence.getValue() == '[{$smarty.const.C__TASK__OCCURRENCE__DAILY}]')
				{
					$workflowException.show();
				}
				else
				{
					$workflowException.hide();
				}
			});

			$occurence.simulate('change');
		}
	})();
</script>