<table class="contentTable">
	<tr >
		<td class="key" style="vertical-align: top;padding-top: 2px;">[{isys type="lang" ident="LC__CMDB__CATG__MAINTENANCE_OBJ_MAINTENANCE"}]: </td>
		<td class="value" style="vertical-align: top;">
			<span>
			[{isys
				title="LC__BROWSER__TITLE__CONTRACTS"
				name="C__CATG__CONTRACT_ASSIGNMENT__CONNECTED_CONTRACTS"
				type="f_popup"
				p_strPopupType="browser_object_ng"
				typeFilter="C__OBJTYPE__MAINTENANCE"
				callback_accept="idoit.callbackManager.triggerCallback('contract_assignment__get_contract');"}]
			</span>
		</td>
		<td>
			<table class="fl ml10 p5 border-left" id="contract_information_table">
			[{if (isset($contract_information))}]
				[{foreach from=$contract_information item="row" key="title"}]
					<tr>
						<td style="text-align:right">[{isys type="lang" ident=$title}]: </td>
						<td style="text-align:right; padding-left:5px;">
						[{if (strstr($contract[$row], '00:00:00'))}]
							[{$contract[$row]|date_format:"%d.%m.%Y"}]
						[{elseif strstr($row, 'costs') || strstr($row, 'sum')}]
							[{isys type="f_money_number" p_strValue=$contract[$row] p_bEditMode="0"}]
						[{elseif $title == 'LC__CMDB__CATS__CONTRACT__NOTICE_VALUE' || $title == 'LC__CMDB__CATS__CONTRACT__MAINTENANCE_PERIOD'}]
							[{$row}]
						[{else}]
							[{isys type="lang" ident=$contract[$row]}]
						[{/if}]
						</td>
					</tr>
				[{/foreach}]
			[{/if}]
			</table>
		</td>
	</tr>
	<tr>
		<td class="key">
			<label for="subcontract" class="fr">[{isys type="lang" ident="LC__CMDB__CATG__CONTRACT_ASSIGNMENT__ACHIEVEMENT_CERTIFICATE"}]</label>
			<input class="m5 fr" type="checkbox" id="subcontract" name="subcontract" value="1" onClick="idoit.callbackManager.triggerCallback('contract_assignment__handle_subcontract', this);" [{if !$smarty.get.editMode}]disabled="disabled"[{/if}]  [{if ($subcontract)}]checked="checked"[{/if}]/>
		</td>
	</tr>
</table>

<table class="contentTable mt5 [{if ! $subcontract}]hide[{/if}]" id="subcontract_table">
	<tr>
		<td class="key">[{isys type='f_label' name='C__CATG__CONTRACT_ASSIGNMENT__CONTRACT_START'  ident="LC__CMDB__CATG__CONTRACT_ASSIGNMENT__CONTRACT_START"}]</td>
		<td class="value">[{isys type="f_popup" name="C__CATG__CONTRACT_ASSIGNMENT__CONTRACT_START" p_strPopupType="calendar" p_bTime="0"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type='f_label' name='C__CATG__CONTRACT_ASSIGNMENT__CONTRACT_END' ident="LC__CMDB__CATG__CONTRACT_ASSIGNMENT__CONTRACT_END"}]</td>
		<td class="value">[{isys type="f_popup" name="C__CATG__CONTRACT_ASSIGNMENT__CONTRACT_END" p_strPopupType="calendar" p_bTime="0"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type='f_label' name='C__CATG__CONTRACT_ASSIGNMENT__MAINTENANCE_PERIOD' ident="LC__CMDB__CATS__CONTRACT__MAINTENANCE_END"}]</td>
		<td class="value">[{isys type="f_data" name="C__CATG__CONTRACT_ASSIGNMENT__MAINTENANCE_PERIOD"}]</td>
	</tr>
    <tr>
        <td class="key">[{isys type="f_label" ident="LC__CMDB__CATS__CONTRACT__REACTION_RATE" name="C__CATG__CONTRACT_ASSIGNMENT__REACTION_RATE"}]</td>
        <td class="value">[{isys type="f_popup" p_strPopupType="dialog_plus" p_strTable="isys_contract_reaction_rate" name="C__CATG__CONTRACT_ASSIGNMENT__REACTION_RATE"}]</td>
    </tr>
</table>

<script type='text/javascript'>
	(function () {
		"use strict";

		idoit.callbackManager
			.registerCallback('contract_assignment__get_contract', function () {
				var l_contractID = $F('C__CATG__CONTRACT_ASSIGNMENT__CONNECTED_CONTRACTS__HIDDEN');

				if (l_contractID > 0) {
					new Ajax.Request('?ajax=1&call=contract', {
						method: 'post',
						parameters: {
							contractID: l_contractID
						},
						onSuccess: function (transport) {
							$("contract_information_table").update(transport.responseText);

							var l_start_hidden = $F('assigned_contract__startdate'),
								l_start_view = $('assigned_contract__startdate').getAttribute('data-view'),
								l_end_hidden = $F('assigned_contract__enddate'),
								l_end_view = $('assigned_contract__enddate').getAttribute('data-view'),
                                l_reaction_rate = $('reaction_rate').value;

							if (l_start_view != "01.01.1970") {
								$('C__CATG__CONTRACT_ASSIGNMENT__CONTRACT_START__VIEW').setValue(l_start_view);
								$('C__CATG__CONTRACT_ASSIGNMENT__CONTRACT_START__HIDDEN').setValue(l_start_hidden);
							} else {
								$('C__CATG__CONTRACT_ASSIGNMENT__CONTRACT_START__VIEW').setValue("");
								$('C__CATG__CONTRACT_ASSIGNMENT__CONTRACT_START__HIDDEN').setValue("");
							}

							if (l_end_view != "01.01.1970") {
								$('C__CATG__CONTRACT_ASSIGNMENT__CONTRACT_END__VIEW').setValue(l_end_view);
								$('C__CATG__CONTRACT_ASSIGNMENT__CONTRACT_END__HIDDEN').setValue(l_end_hidden);
							} else {
								$('C__CATG__CONTRACT_ASSIGNMENT__CONTRACT_END__VIEW').setValue("");
								$('C__CATG__CONTRACT_ASSIGNMENT__CONTRACT_END__HIDDEN').setValue("");
							}

                            if(l_reaction_rate > 0)
                            {
                                $('C__CATG__CONTRACT_ASSIGNMENT__REACTION_RATE').setValue(l_reaction_rate);
                            }
						}
					});
				}
			})
			.registerCallback('contract_assignment__handle_subcontract', function ($checkbox) {
				if ($checkbox.disabled) {
					return;
				}
				if ($checkbox.checked) {
					$('subcontract_table').removeClassName('hide');
					$('C__CATG__CONTRACT_ASSIGNMENT__CONTRACT_START__VIEW').disabled = false;
					$('C__CATG__CONTRACT_ASSIGNMENT__CONTRACT_START__HIDDEN').disabled = false;
					$('C__CATG__CONTRACT_ASSIGNMENT__CONTRACT_END__VIEW').disabled = false;
					$('C__CATG__CONTRACT_ASSIGNMENT__CONTRACT_END__HIDDEN').disabled = false;
                    $('C__CATG__CONTRACT_ASSIGNMENT__REACTION_RATE').disabled = false;
				} else {
					$('subcontract_table').addClassName('hide');
					$('C__CATG__CONTRACT_ASSIGNMENT__CONTRACT_START__VIEW').disabled = true;
					$('C__CATG__CONTRACT_ASSIGNMENT__CONTRACT_START__HIDDEN').disabled = true;
					$('C__CATG__CONTRACT_ASSIGNMENT__CONTRACT_END__VIEW').disabled = true;
					$('C__CATG__CONTRACT_ASSIGNMENT__CONTRACT_END__HIDDEN').disabled = true;
                    $('C__CATG__CONTRACT_ASSIGNMENT__REACTION_RATE').disabled = true;
				}
			})
			.triggerCallback('contract_assignment__handle_subcontract', $('subcontract'));
	}());
</script>