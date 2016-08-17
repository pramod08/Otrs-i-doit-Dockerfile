<table class="contentTable">
	<tr>
		<td class="key">[{isys type="f_label" ident="LC__CMDB__CATS__MAINTENANCE_CONTRACT_NUMMER" name="C__CATS__MAINTENANCE_CONTRACT_NUMBER"}]</td>
		<td class="value">[{isys type="f_text" name="C__CATS__MAINTENANCE_CONTRACT_NUMBER" tab="10"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" ident="LC__CMDB__CATS__MAINTENANCE_CUSTOMER_NUMBER" name="C__CATS__MAINTENANCE_CUSTOMER_NUMBER"}]</td>
		<td class="value">[{isys type="f_text" name="C__CATS__MAINTENANCE_CUSTOMER_NUMBER" tab="15"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" ident="LC__CMDB__CATS__MAINTENANCE_CONTRACT_DURATION_START" name="C__CATS__MAINTENANCE_CONTRACT_DURATION_START__VIEW"}]</td>
		<td class="value">
			[{isys
				type="f_popup"
				name="C__CATS__MAINTENANCE_CONTRACT_DURATION_START"
				p_strPopupType="calendar"
				p_calSelDate=""
				p_bTime="0"
				tab="20"}]
		</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" ident="LC__CMDB__CATS__MAINTENANCE_CONTRACT_DURATION_END" name="C__CATS__MAINTENANCE_CONTRACT_DURATION_END__VIEW"}]</td>
		<td class="value">
			[{isys
				type="f_popup"
				name="C__CATS__MAINTENANCE_CONTRACT_DURATION_END"
				p_strPopupType="calendar"
				p_calSelDate=""
				p_bTime="0"
				tab="30"}]
		</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" ident="LC__CMDB__CATS__MAINTENANCE_CONTRACT_REACTION_RATE" name="C__CATS__MAINTENANCE_CONTRACT_REACTION_RATE"}]</td>
		<td class="value">
			[{isys
				type="f_popup"
				p_strPopupType="dialog_plus"
				p_strTable="isys_maintenance_reaction_rate"
				name="C__CATS__MAINTENANCE_CONTRACT_REACTION_RATE"
				tab="40"}]
		</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" ident="LC__UNIVERSAL__COSTS" name="C__CATS__MAINTENANCE_CONTRACT_COSTS"}]</td>
		<td class="value">[{isys type="f_text" name="C__CATS__MAINTENANCE_CONTRACT_COSTS" tab="50"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" ident="Support-URL" name="C__CATS__MAINTENANCE_CONTRACT_SUPPORT_URL"}]</td>
		<td class="value">[{isys type="f_link" p_strTarget="_new" name="C__CATS__MAINTENANCE_CONTRACT_SUPPORT_URL" tab="60"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" ident="LC__CMDB__CATG__DESCRIPTION" name="C__CATS__MAINTENANCE_CONTRACT_CONTRACT_DESCRIPTION"}]</td>
		<td class="value">[{isys type="f_textarea" name="C__CATS__MAINTENANCE_CONTRACT_CONTRACT_DESCRIPTION" tab="70" p_strStyle="width:215px"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" ident="LC__CMDB__CATS__MAINTENANCE_CONTRACT_TYPE" name="C__CMDB__CATS__MAINTENANCE_CONTRACT_TYPE"}]</td>
		<td class="value">
			[{isys
				type="f_popup"
				p_strPopupType="dialog_plus"
				p_strTable="isys_maintenance_contract_type"
				name="C__CMDB__CATS__MAINTENANCE_CONTRACT_TYPE"
				tab="80"}]
		</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" ident="LC__UNIVERSAL__STATUS" name="C__CMDB__CATS__MAINTENANCE_STATUS"}]</td>
		<td class="value">
			[{isys
				type="f_popup"
				p_strPopupType="dialog_plus"
				name="C__CMDB__CATS__MAINTENANCE_STATUS"
				p_strTable="isys_maintenance_status"
				p_bDbFieldNN="0"
				tab="90"}]
		</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" ident="LC__CMDB__CATS__MAINTENANCE_PRODUCT" name="C__CMDB__CATS__MAINTENANCE_PRODUCT"}]</td>
		<td class="value">[{isys type="f_text" name="C__CMDB__CATS__MAINTENANCE_PRODUCT" tab="100"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" ident="LC__CMDB__CATS__MAINTENANCE_CONTRACT_DISTRIBUTOR" name="C__CMDB__CATS__MAINTENANCE_CONTRACT_DISTRIBUTOR__VIEW"}]</td>
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
				tab="110"}]
		</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" ident="LC__CMDB__CATS__MAINTENANCE_TERMINATED_ON" name="C__CMDB__CATS__MAINTENANCE_TERMINATED_ON__VIEW"}]</td>
		<td class="value">
			[{isys
				type="f_popup"
				name="C__CMDB__CATS__MAINTENANCE_TERMINATED_ON"
				p_strPopupType="calendar"
				p_calSelDate=""
				p_bTime="0"
				tab="120"}]
		</td>
	</tr>
</table>