[{isys_group name="wf_generic"}]
<div>

	[{if !is_array($g_template_parameter) && ($g_workflow_type > 0)}]
		<h5>[{isys type="lang" ident="LC_WORKFLOW__NO_DEFINITIONS"}]</h5>
    [{elseif $g_workflow_type < 0}]
        <h5>[{isys type="lang" ident="LC_WORKFLOW__NO_WORKFLOW_TYPE_SELECTED"}]</h5>
	[{/if}]

	<input type="hidden" name="g_workflow_type" value="[{$g_workflow_type}]" />

	<table class="contentTable" style="border-top: none;">
		<tbody>
	[{foreach from=$g_template_parameter key=l_key item=l_param}]
		<tr>
			<td class="key">
				<label for="[{$l_param.key}]">[{$l_param.value}]</label>
			</td>
			<td class="value">
		[{if $l_param.type eq $smarty.const.C__WF__PARAMETER_TYPE__INT}]

			[{isys type="f_text" name=$l_param.key p_strValue="" p_additional=$l_param.check}]

		[{elseif $l_param.type eq $smarty.const.C__WF__PARAMETER_TYPE__STRING}]

			[{isys type="f_text" name=$l_param.key p_strValue="" p_additional=$l_param.check}]

		[{elseif $l_param.type eq $smarty.const.C__WF__PARAMETER_TYPE__TEXT}]

			[{isys type="f_textarea" name=$l_param.key p_strValue="" p_additional=$l_param.check}]

		[{elseif $l_param.type eq $smarty.const.C__WF__PARAMETER_TYPE__DATETIME}]

			[{isys type="f_popup" p_strPopupType="calendar" name=$l_param.key p_strValue="" p_additional=$l_param.check}]

		[{elseif $l_param.type eq $smarty.const.C__WF__PARAMETER_TYPE__YES_NO}]

			[{isys type="f_dialog" name=$l_param.key p_arData=$g_ar_yes_no p_strSelectedID="-1" p_additional=$l_param.check}]

		[{/if}]
			</td>
		</tr>
	[{/foreach}]
	[{if $g_occurrence}]
		<tr>
			<td class="key">
				<label for="f_occurence">
					[{isys type="lang" ident="LC__TASK__DETAIL__WORKORDER__OCCURRENCE"}]:
				</label>
			</td>
			<td class="value">
				[{isys type="f_dialog" name="f_occurrence" id="f_occurrence" p_arData=$g_occurrence_data p_additional="check=1"}]
			</td>
		</tr>
	[{/if}]
		</tbody>
	</table>
</div>

<div style="display:none;" class="mt10" id="workflow_exception">
	<table class="contentTable">
		<tr>
			<td class="key">
				[{isys type="lang" ident="LC_WORKFLOW_DETAIL__EXCEPTIONS"}]
			</td>
			<td class="value">
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
			</td>
		</tr>
	</table>
</div>
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

				exception_switch(0);
			});

			$occurence.simulate('change');
		}
	})();
</script>