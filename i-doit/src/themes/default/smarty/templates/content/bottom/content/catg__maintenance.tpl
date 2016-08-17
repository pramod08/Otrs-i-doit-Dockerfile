<table class="contentTable">
	<colgroup>
		<col width="20%" />
		<col width="35%" />
		<col width="45%" />

	</colgroup>
	<tr>
		<td class="key">[{isys type="lang" ident="LC__CMDB__CATG__MAINTENANCE_OBJ_MAINTENANCE"}]: </td>
		<td class="value">
			<span>
			[{isys
				name="C__CATG__MAINTENANCE_OBJ_MAINTENANCE"
				type="f_popup"
				p_strPopupType="browser_object_ng"
				typeFilter="C__OBJTYPE__MAINTENANCE;C__OBJTYPE__FILE"}]
			</span>
		</td>
		<td>
			<table class="fl" style="margin-left:10px;border-left:1px solid #ccc;padding:5px;">
				<tr>
					<td>Support-URL: </td>
					<td>[{isys type="f_data" name="maintenance_object_support_url" tab="15"}]</td>
				</tr>
				<tr>
					<td>[{isys type="lang" ident="LC__CMDB__CATS__MAINTENANCE_CONTRACT_DURATION_START"}]:</td>
					<td><strong>[{isys type="f_data" name="maintenance_object_start" tab="10"}]</strong></td>
				</tr>
				<tr>
					<td>[{isys type="lang" ident="LC__CMDB__CATS__MAINTENANCE_CONTRACT_DURATION_END"}]:</td>
					<td><strong>[{isys type="f_data" name="maintenance_object_end" tab="10"}]</strong></td>
				</tr>
				<tr>
					<td>[{isys type="lang" ident="LC__CMDB__CATS__MAINTENANCE_CONTRACT_REACTION_RATE"}]: </td>
					<td>[{isys type="f_data" name="maintenance_object_reaction_rate" tab="15"}]</td>
				</tr>
				<tr>
					<td>[{isys type="lang" ident="LC__CMDB__CATS__MAINTENANCE_CONTRACT_NUMMER"}]: </td>
					<td>[{isys type="f_data" name="maintenance_object_contract_number" tab="10"}]</td>
				</tr>
				<tr>
					<td>[{isys type="lang" ident="LC__CMDB__CATS__MAINTENANCE_CUSTOMER_NUMBER"}]: </td>
					<td>[{isys type="f_data" name="maintenance_object_customer_number" tab="15"}]</td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
	<td colspan="3">
		[{if isset($sub) || $smarty.post.sub}]
		<label><input checked="checked" type="checkbox" onclick="new Effect.toggle('sub','appear',{duration:0.2});" name="sub" value="1" /> Sub-[{isys type="lang" ident="LC__CMDB__CATG__SERVICE_CONTRACT"}]</label>
		<script type="text/javascript">
			new Event.observe(window,'load',function(){
				$('sub').show();
			});
		</script>
		[{else}]
		<label><input type="checkbox" onclick="new Effect.toggle('sub','appear',{duration:0.2});" name="sub" value="1" /> Sub-[{isys type="lang" ident="LC__CMDB__CATG__SERVICE_CONTRACT"}]</label>
		[{/if}]
	</td>
	</tr>
</table>

<div [{if !isset($sub)}]style="display:none;"[{/if}] id="sub">
	<hr />

	<table class="contentTable">
		<tr>
			<td class="key">[{isys type="lang" ident="LC__CMDB__CATG__MAINTENANCE_TITLE"}]: </td>
			<td class="value">[{isys type="f_text" name="C__CATG__MAINTENANCE_TITLE" tab="10"}]</td>
		</tr>
		<tr>
			<td class="key">
				[{isys
					type="lang"
					ident="LC__CMDB__CATS__MAINTENANCE_CONTRACT_DURATION_START"}]:
			</td>
			<td class="value">
				[{isys
					type="f_popup"
					name="C__CATG__MAINTENANCE_CONTRACT_DURATION_START"
					p_strPopupType="calendar"
					p_calSelDate=""
					p_bTime="0"
					tab="30"}]
			</td>
		</tr>
		<tr>
			<td class="key">
				[{isys
					type="lang"
					ident="LC__CMDB__CATS__MAINTENANCE_CONTRACT_DURATION_END"}]:
			</td>
			<td class="value">
				[{isys
					type="f_popup"
					name="C__CATG__MAINTENANCE_CONTRACT_DURATION_END"
					p_strPopupType="calendar"
					p_calSelDate=""
					p_bTime="0"
					tab="40"}]
			</td>
		</tr>
		<tr>
			<td class="key">
				[{isys
					type="lang"
					ident="LC__CMDB__CATS__MAINTENANCE_CONTRACT_REACTION_RATE"}]:
			</td>
			<td class="value">
				[{isys
					type="f_popup"
					p_strPopupType="dialog_plus"
					p_strTable="isys_maintenance_reaction_rate"
					name="C__CATG__MAINTENANCE_CONTRACT_REACTION_RATE"
					tab="50"}]
			</td>
		</tr>

		<tr>
			<td class="key">
				[{isys
					type="lang"
					ident="LC__CMDB__CATS__MAINTENANCE_CONTRACT_TYPE"}]:
			</td>
			<td class="value">
				[{isys
					type="f_popup"
					p_strPopupType="dialog_plus"
					p_strTable="isys_maintenance_contract_type"
					name="C__CMDB__CATS__MAINTENANCE_CONTRACT_TYPE"
					tab="50"}]
			</td>
		</tr>
		<tr>
			<td class="key">[{isys type="lang" ident="LC__UNIVERSAL__STATUS"}]: </td>
			<td class="value">
				[{isys
					type="f_popup"
					p_strPopupType="dialog_plus"
					name="C__CMDB__CATS__MAINTENANCE_STATUS"
					p_strTable="isys_maintenance_status"
					p_bDbFieldNN="0"
					tab="50"}]
			</td>
		</tr>
		<tr>
				<td>[{isys type="lang" ident="LC__CMDB__CATS__MAINTENANCE_PRODUCT"}]: </td>
				<td>[{isys type="f_text" name="C__CMDB__CATS__MAINTENANCE_PRODUCT" tab="15"}]</td>
		</tr>

		<tr>
			<td class="key">[{isys type="lang" ident="LC__CMDB__CATS__MAINTENANCE_CONTRACT_DISTRIBUTOR"}]: </td>
			<td class="value">
				[{isys
					title="LC__BROWSER__TITLE__CONTACT"
					name="C__CMDB__CATS__MAINTENANCE_CONTRACT_DISTRIBUTOR"
					type="f_popup"
					p_strPopupType="browser_object_ng"
					catFilter='C__CATS__PERSON;C__CATS__PERSON_GROUP;C__CATS__ORGANIZATION'
					multiselection="true"
					p_bReadonly="1"
					p_image="true"
					p_strFormSubmit="0"
					p_iSelectedTab="1"
					p_iEnabledPreselection="1"
					tab="50"}]
			</td>
		</tr>

		<tr>
			<td>[{isys type="lang" ident="LC__CMDB__CATS__MAINTENANCE_TERMINATED_ON"}]: </td>
			<td>[{isys
					type="f_popup"
					name="C__CMDB__CATS__MAINTENANCE_TERMINATED_ON"
					p_strPopupType="calendar"
					p_calSelDate=""
					p_bTime="0" tab="50"}]
			</td>
		</tr>
	</table>
</div>

