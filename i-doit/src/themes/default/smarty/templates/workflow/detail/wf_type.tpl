<div id="workflow">
	<input type="hidden" name="g_workflow_type" value="[{$g_workflow_type}]"/>

	<div id="workflow_meta">
		<h3>[{isys type="lang" ident="LC_WORKFLOW__TYPE"}]:</h3>

		<table class="contentTable">
			[{if $g_data.isys_workflow_type__id}]
			<tr>
				<td class="key"><label for="">[{isys type="lang" ident="ID"}]</label></td>
				<td class="value">
					<strong>[{isys type="f_data" name="f_const" p_strValue=$g_data.isys_workflow_type__id}]</strong>
				</td>
			</tr>
			[{/if}]
			<tr>
				<td class="key"><label for="">[{isys type="lang" ident="LC__TASK__TITLE"}]</label></td>
				<td class="value">
					<strong>[{isys type="f_text" name="f_title" p_strValue=$g_data.isys_workflow_type__title}]</strong>
				</td>
			</tr>
			<tr>
				<td class="key"><label for="">[{isys type="lang" ident="LC__CMDB__OBJTYPE__CONST"}]</label></td>
				<td class="value">
					<strong>[{isys type="f_text" name="f_const" p_bNoTranslation="1" p_strValue=$g_data.isys_workflow_type__const}]</strong>
				</td>
			</tr>
			[{if $g_data.isys_workflow_type__datetime}]
			<tr>
				<td class="key"><label for="">[{isys type="lang" ident="LC__TASK__DETAIL__WORKORDER__CREATION_DATE"}]</label></td>
				<td class="value">
					<strong>[{isys type="f_data" name="f_const" p_strValue=$g_data.isys_workflow_type__datetime}]</strong>
				</td>
			</tr>
			[{/if}]
			<tr>
				<td class="key"><label for="">[{isys type="lang" ident="LC__TASK__DETAIL__WORKORDER__OCCURRENCE"}]</label></td>
				<td class="value">
					<strong>[{isys type="f_dialog" name="f_occurrence" p_arData=$g_occurrence p_strSelectedID=$g_data.isys_workflow_type__occurrence p_bDbFieldNN="1"}]</strong>
				</td>
			</tr>
		</table>
	</div>

	<div id="wf_template_list">
		<fieldset class="overview">
			<legend><span>Template Parameter</span></legend>
			<div class="mt10">
				[{$g_template_parameter|default:"No Parameters defined, yet."}]
			</div>
		</fieldset>
	</div>
</div>