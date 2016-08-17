<input type="hidden" name="new_action_id" value="[{$l_action->get_id()}]" />

<table cellpadding="0" cellspacing="0" class="contentTable" style="border-top: none;">
<tbody>
[{foreach from=$l_action->get_parameters() key=l_key item=l_param}]

	[{assign var="the_id" 			value=$l_param->get_id()}]
	[{assign var="l_key" 			value=$l_param->get_key()}]
	[{assign var="l_value" 			value=$l_param->get_value()}]
	[{assign var="l_tpl_parameter"	value=$l_param->get_template_parameter()}]

	[{assign var="l_tpl_key" value=$g_template_parameter.$l_tpl_parameter}]

	<tr>
		<td class="key">
			[{$l_tpl_key.value}]
		</td>
		<td class="value">
			<input type="hidden" name="keys[]" value="[{$the_id}]" />

			<!-- [{$l_key}] -->
			[{if $l_param->get_type() eq $smarty.const.C__WF__PARAMETER_TYPE__INT ||
					$l_param->get_type() eq $smarty.const.C__WF__PARAMETER_TYPE__YES_NO ||
					$l_param->get_type() eq $smarty.const.C__WF__PARAMETER_TYPE__STRING}]

				[{isys type="f_text" name=$l_key p_strValue=$l_value p_bInfoIconSpacer="1"}]

			[{elseif $l_param->get_type() eq $smarty.const.C__WF__PARAMETER_TYPE__TEXT}]

				[{isys type="f_textarea" name=$l_key p_strValue=$l_value p_bInfoIconSpacer="1" htmlEnabled="1"}]

			[{elseif $l_param->get_type() eq $smarty.const.C__WF__PARAMETER_TYPE__DATETIME}]

				[{isys type="f_popup" p_strPopupType="calendar" disablePastDate="true" p_bDisabled=0 name=$l_key p_strValue=$l_value p_bInfoIconSpacer="1"}]

			[{/if}]
		</td>
	</tr>
[{/foreach}]
</tbody>
</table>