<h3 class="p5 gradient text-shadow border-bottom">[{isys type="lang" ident="LC_WORKFLOW__TEMPL_PARAMS"}]</h3>

<div id="workflow">
	<div id="workflow_meta">
		<input type="hidden" name="g_workflow_type" value="[{$g_workflow_type}]" />

		<table class="contentTable">
			[{if $g_data.isys_workflow_template_parameter__id}]
			<tr>
				<td class="key"><label for="">[{isys type="lang" ident="ID"}]</label></td>
				<td class="value">
					<strong>[{isys type="f_text" p_bDisabled="1" p_strValue=$g_data.isys_workflow_template_parameter__id}]</strong>
				</td>
			</tr>
			[{/if}]
			<tr>
				<td class="key"><label for="">Parameter [{isys type="lang" ident="LC__CMDB__CATG__TYPE"}]</label></td>
				<td class="value">
					<img class="infoIcon" src="[{$dir_images}]empty.gif" alt="" height="15px" width="15px" style="margin-right:5px;"/>[{$g_parameter_types}]
				</td>
			</tr>
			<tr>
				<td class="key"><label for="">[{isys type="lang" ident="LC_WORKFLOW__TYPE"}]</label></td>
				<td class="value">
					<strong>[{isys type="f_dialog" p_strTable="isys_workflow_type" name="f_workflow_type" p_strSelectedID=$g_data.isys_workflow_type__id}]</strong>
				</td>
			</tr>
			<tr>
				<td class="key"><label for="">[{isys type="lang" ident="LC__TASK__TITLE"}]</label></td>
				<td class="value">
					<strong>[{isys type="f_text" name="f_title" p_strValue=$g_data.isys_workflow_template_parameter__title}]</strong>
				</td>
			</tr>
			<tr>
				<td class="key"><label for="">[{isys type="lang" ident="LC_WORKFLOW__KEY"}]</label></td>
				<td class="value">
					[{if !preg_match("/^(.*?)(start_date|end_date)(.*?)$/i", $g_data.isys_workflow_template_parameter__key)}]
					<strong>[{isys type="f_text" name="f_key" p_strValue=$g_data.isys_workflow_template_parameter__key}]</strong>
					[{else}]
					<strong>[{isys type="f_data" p_strValue=$g_data.isys_workflow_template_parameter__key}] (*)</strong>
					[{isys type="f_text" p_bInvisible=1 name="f_key" p_strValue=$g_data.isys_workflow_template_parameter__key}]
					[{/if}]
				</td>
			</tr>
			<tr>
				<td class="key"><label for="">[{isys type="lang" ident="LC_WORKFLOW__CHECK"}]</label></td>
				<td class="value">
					<strong>
						<img class="infoIcon" src="[{$dir_images}]empty.gif" alt="" style="margin-right: 5px;" height="15" width="15">
						<input style="width:14px;" type="checkbox" name="f_check" id="f_check" value="1" [{if $g_data.isys_workflow_template_parameter__property == 1}]checked="checked"[{/if}]>
						<label for="f_check" style="float:none;vertical-align:top;;">[{isys type="lang" ident="LC_WORKFLOW__ACTIVE"}]</label>
					</strong>
				</td>
			</tr>
			<tr>
				<td class="key"><label for="">[{isys type="lang" ident="LC_UNIVERSAL__ORDER"}]</label></td>
				<td class="value">
					<strong>[{isys type="f_text" name="f_sort" p_strValue=$g_data.isys_workflow_template_parameter__sort}]</strong>
				</td>
			</tr>
		</table>
	</div>
</div>