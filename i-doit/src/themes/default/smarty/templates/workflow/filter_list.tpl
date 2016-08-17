<div id="query">
	<fieldset class="overview border-top-none">
		<legend><span>[{isys type="lang" ident="LC_UNIVERSAL__FILTERS"}]</span></legend>

		<table class="m10" style="float:left; width:460px;">
			<tbody>
			<tr>
				<td><label for="status">Status</label></td>
				<td class="filter" colspan="2">
					<select id="status" name="status" class="input input-mini">
						<option [{if $g_post.status == 0}]selected="selected" [{/if}] value="0">[{isys type="lang" ident="LC__CMDB__RECORD_STATUS__ALL"}]</option>
						<option [{if $g_post.status == $smarty.const.C__TASK__STATUS__ASSIGNMENT}]selected="selected" [{/if}] value="[{$smarty.const.C__TASK__STATUS__ASSIGNMENT}]">[{isys type="lang" ident="LC__TASK__STATUS__ASSIGNMENT__SHORT"}]</option>
						<option [{if $g_post.status == $smarty.const.C__TASK__STATUS__OPEN}]selected="selected" [{/if}] value="[{$smarty.const.C__TASK__STATUS__OPEN}]">[{isys type="lang" ident="LC__TASK__STATUS__OPEN__SHORT"}]</option>
						<option [{if $g_post.status == $smarty.const.C__TASK__STATUS__CLOSE}]selected="selected" [{/if}] value="[{$smarty.const.C__TASK__STATUS__CLOSE}]">[{isys type="lang" ident="LC__TASK__STATUS__CLOSE__SHORT"}]</option>
						<option [{if $g_post.status == $smarty.const.C__TASK__STATUS__CANCEL}]selected="selected" [{/if}] value="[{$smarty.const.C__TASK__STATUS__CANCEL}]">[{isys type="lang" ident="LC__TASK__STATUS__CANCEL__SHORT"}]</option>
					</select>
				</td>
			</tr>
			<tr>
				<td><label>[{isys type="lang" ident="LC__WORKFLOW__TYPES"}]</label></td>
				<td class="filter">
					[{foreach from=$g_workflow_types item="l_workflow_type"}]
					[{if $l_workflow_type.isys_workflow_type__id eq $smarty.post.wf_type && $l_workflow_type.isys_workflow_type__occurrence eq 1}]
					[{assign var="l_appear" value="1"}]
					[{/if}]

					<label class="mr10">
						<input type="radio"
						       [{if !empty($l_workflow_type.selected)}][{assign var="l_sel" value="1"}]checked="checked" [{/if}]
						       id="wf_type_[{$l_workflow_type.isys_workflow_type__id}]" name="wf_type" value="[{$l_workflow_type.isys_workflow_type__id}]"
						       onchange="$('datings').[{if $l_workflow_type.isys_workflow_type__occurrence == 1}]show[{else}]hide[{/if}]();" />
						[{$l_workflow_type.isys_workflow_type__title}]
					</label>
					[{/foreach}]

					<label>
						<input type="radio" [{if empty($l_sel)}]checked="checked"[{/if}] id="wf_type_0" name="wf_type" value="0" onchange="$('datings').hide();" />
						[{isys type="lang" ident="LC__WORKFLOWS__ALL"}]
					</label>

				</td>
			</tr>
			<tr class="status" id="datings" [{if $l_appear != 1}]style="display:none;" [{/if}]>
				<td class="vat"><label for="f_date_from">[{isys type="lang" ident="LC_UNIVERSAL__DATE"}]</label></td>
				<td class="filter" colspan="2">
					<a href="javascript:void(0)"
					   onclick="$('f_date_from__VIEW').setValue('');$('f_date_from__HIDDEN').setValue('');$('f_date_to__VIEW').setValue('');$('f_date_to__HIDDEN').setValue('');">
						<img src="[{$dir_images}]icons/silk/cross.png" alt="x" class="mr5 vam"/>
					</a>

					[{assign var="sel_date_from" value=$g_post.f_date_from__HIDDEN}]
					[{assign var="sel_date_to" value=$g_post.f_date_to__HIDDEN}]

					[{isys
						type="f_popup"
						name="f_date_from"
						p_strPopupType="calendar"
						p_calSelDate=$sel_date_from
						p_strValue=$sel_date_from
						p_bTime="0"
						p_bDisabled="0"
						p_bInfoIconSpacer="0"
						p_strStyle="width:100px;"
						p_bEditMode="1"}]

					<strong class="ml5 mr5">[{isys type="lang" ident="LC__UNIVERSAL_TO"}]</strong>

					[{isys
						type="f_popup"
						name="f_date_to"
						p_strPopupType="calendar"
						p_calSelDate=$sel_date_to
						p_strValue=$sel_date_to
						p_bTime="0"
						p_bDisabled="0"
						p_bInfoIconSpacer="0"
						p_strStyle="width:100px;"
						p_bEditMode="1"}]
				</td>
			</tr>
			<tr id="userfilter" [{if !empty($g_post.my)}]style="display:none;" [{/if}]>
				<td><label for="f_uid">[{isys type="lang" ident="LC__CREATOR"}]</label></td>
				<td class="filter" colspan="2">
					<select name="f_owner_mode" class="input input-mini">
						<option value="">[{isys type="lang" ident="LC__UNIVERSAL__IS"}]</option>
						<option value="!" [{if $g_post.f_owner_mode}] selected="selected" [{/if}]>[{isys type="lang" ident="LC__UNIVERSAL__IS"}] [{isys type="lang" ident="LC__UNIVERSAL__NOT"}]
						</option>
					</select>
					[{*isys name="f_uid" type="f_dialog" p_bInfoIconSpacer="0" p_strSelectedID=$g_post.f_uid p_strTable="isys_person_intern" p_bEditMode="1"*}]
				</td>
			</tr>
			</tbody>
		</table>

		<table summary="filters" style="width:400px;padding-left:5px;border-left:1px solid #ccc;">
			<tbody>
			<tr>
				<th scope="row"><label>[{isys type="lang" ident="LC__EXTENDED"}]</label></th>
				<td class="filter" colspan="2">
					<label class="mr10">
						<input type="checkbox" [{if !empty($g_post.my)}]checked="checked"[{/if}] id="my" name="my" value="my" class="mr5 vam" onchange="$('userfilter').toggle();" />
						[{isys type="lang" ident="LC__WORKFLOWS__MY"}]
					</label>

					<label>
						<input type="checkbox" [{if !empty($g_post.today)}]checked="checked"[{/if}] id="today" name="today" value="today" class="mr5 vam" />
						[{isys type="lang" ident="LC__OF_TODAY"}]
					</label>
				</td>
			</tr>
			<tr>
				<td><label for="max">[{isys type="lang" ident="LC__WORKFLOWS__OVERVIEW"}]</label></td>
				<td>
					<input type="input" class="input mr5" value="[{$g_max_workflows}]" onchange="if(this.value>100)this.value='99';else if(this.value<=0)this.value='1';else if(isNaN(this.value))this.value='1';" id="max" name="max" style="width:25px" />
					[{isys type="lang" ident="LC__UNIVERSAL__DAYS"}]
				</td>
			</tr>
			<tr>
				<td><label>[{isys type="lang" ident="LC__WORKFLOWS__STARTING_NOW"}]</label></td>
				<td>
					<label>
						<input type="checkbox" [{if !empty($g_post.from_now)}]checked="checked"[{/if}] id="from_now" name="from_now" value="from_now" />
						[{isys type="lang" ident="LC__UNIVERSAL_TODAY"}]
					</label>
				</td>
			</tr>
			</tbody>
		</table>

	</fieldset>

	<div class="m10">
		<input type="hidden" name="f_order_by" value="start_date" />
		<input value="[{isys type="lang" ident="LC_UNIVERSAL__UPDATE"}]" class="btn" type="submit" name="submit">
	</div>
</div>
<div id="workflow_list" class="m10">

[{if $g_num_rows}]
	[{isys_workflow_list->get_columns 	assign="l_columns"}]
	[{isys_workflow_list->get_headers 	assign="l_headers"}]
	[{isys_workflow_list->get_links 	assign="l_links"}]
	<input type="hidden" value="[{$order_field}]" name="order_field" id="order_field">
    <input type="hidden" value="[{$order_dir}]" name="order_dir" id="order_dir">
	<table cellpadding="0" cellspacing="0" class="mainTable border">
		<tbody>
			<tr>
				[{foreach from=$l_headers item="l_header" key="l_header_key"}]
					[{if $l_header.active}]
						<th id="[{$l_header_key}]" onclick="$('order_field').value='[{$l_header_key}]';form_submit()">[{$l_header.title}]</th>
					[{/if}]
				[{/foreach}]
			</tr>

			[{foreach from=$l_columns item="l_value" key="l_colindex"}]
			[{assign var="current_link" value=$l_links.$l_colindex}]

			<tr[{if isset($current_link)}] onclick="document.location='[{$current_link}]';"[{/if}] class="[{cycle values="listRow CMDBListElementsEven,listRow CMDBListElementsOdd"}]" style="cursor:pointer;">
				[{foreach from=$l_value item="l_item" key="l_header_key"}]
				<td>[{$l_item}]</td>
				[{/foreach}]
			</tr>
			[{/foreach}]
		</tbody>
	</table>
    <script type="text/javascript">
        if($('order_field').value != '' && $('order_dir').value != '')
        {
            switch($('order_dir').value)
            {
                case 'DESC':
                    $($('order_field').value).addClassName('desc on');
                    break;
                default:
                    $($('order_field').value).addClassName('asc on');
                    break;
            }
        }
    </script>
[{else}]
	<p class="m5">[{isys type="lang" ident="LC__CMDB__FILTER__NOTHING_FOUND_STD"}]</p>
[{/if}]
</div>