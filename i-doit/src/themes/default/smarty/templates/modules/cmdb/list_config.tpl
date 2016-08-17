[{if isset($g_list)}]
	[{$g_list}]
[{else}]
	<input type="hidden" name="list_objtype_id" value="[{$list_obj_type_id}]">

	<div class="p5">
		<p>[{isys type="checkbox" p_bInfoIconSpacer=0 name="row_clickable" p_bEditMode=1 p_bChecked=$row_clickable p_strTitle="LC__MODULE__CMDB__ROW_CLICK_FEATURE"}]</p>

		[{isys type="f_label" name="sorting_direction" ident="LC__REPORT__INFO__SORTING"}][{isys type="f_dialog" name="sorting_direction" p_bDbFieldNN='1' p_arData=$sorting_data p_strSelectedID=$defined_sorting p_bEditMode=1 p_strClass="input-mini"}]
	</div>

	[{isys
		type="f_property_selector"
		obj_type_id=$list_obj_type_id
		p_bInfoIconSpacer=0
		name="list"
		p_strStyle="margin-left:5px;"
		custom_fields=true
		grouping=false
		sortable=true
		preselection=$selected_properties
		dynamic_properties=true
		provide=$provides
		default_sorting=$default_sorting
		check_sorting=true
		p_consider_rights=true}]

	<p class="m5">
		<a href="?[{$smarty.const.C__CMDB__GET__VIEWMODE}]=[{$smarty.const.C__CMDB__VIEW__LIST_OBJECT}]&&objTypeID=[{$smarty.get.objTypeID}]" class="btn">
			<img src="[{$dir_images}]icons/silk/table.png" class="vam" /> [{isys type="lang" ident="LC__MODULE__CMDB__LIST__JUMP_TO_LIST"}]
		</a>
		<button type="button" id="resetter" class="btn redbg">
			<img src="[{$img_dir}]icons/silk/cross.png" class="vam" /> [{isys type="lang" ident="LC__MODULE__CMDB__RESTORE_DEFAULT_LIST_CONFIG"}]
		</button>
	</p>

	[{if $has_right_to_overwrite || $has_right_to_define_standard}]
	<hr class="mt10 mb10" />

	<table class="two-col" style="width:100%;">
		<tr>
			[{if $has_right_to_overwrite}]
			<td class="p5 vat">
				<h3 class="p5 gradient border">[{isys type="lang" ident="LC__MODULE__CMDB__SET_FOR_USER"}]</h3>
				<p class="mt5 mb5">[{isys type="lang" ident="LC__MODULE__CMDB__SET_FOR_USER_DESCRIPTION"}]</p>

				[{isys
					type="f_popup"
					p_strPopupType="browser_object_ng"
					name="C__CMDB__PERSON__SELECTION"
					catFilter="C__CATS__PERSON;C__CATS__PERSON_LOGIN"
					multiselection=true
					p_bInfoIconSpacer=0
					p_strClass="mb5 input input-small"}]<br />

				<button type="button" id="C__CMDB__BUTTON_SET_FOR_USER" class="btn"><img src="[{$dir_images}]icons/silk/table_add.png" class="mr5" /><span>[{isys type="lang" ident="LC__MODULE__CMDB__SET_FOR_USER_BUTTON"}]</span></button>
			</td>
			[{/if}]

			[{if $has_right_to_define_standard}]
			<td class="p5 vat">
				<h3 class="p5 gradient border">[{isys type="lang" ident="LC__MODULE__CMDB__SET_AS_DEFAULT"}]</h3>
				<p class="mt5 mb5">[{isys type="lang" ident="LC__MODULE__CMDB__SET_AS_DEFAULT_DESCRIPTION"}]</p>
				<button type="button" id="C__CMDB__BUTTON_SET_AS_DEFAULT" class="btn"><img src="[{$dir_images}]icons/silk/accept.png" class="mr5" /><span>[{isys type="lang" ident="LC__MODULE__CMDB__SET_AS_DEFAULT_BUTTON"}]</span></button>
			</td>
			[{/if}]
		</tr>
	</table>
	[{/if}]

	<script type="text/javascript">
	(function () {
		'use strict';

		var $button_set_for_user = $('C__CMDB__BUTTON_SET_FOR_USER'),
			$button_set_as_default = $('C__CMDB__BUTTON_SET_AS_DEFAULT'),
			$person_selection = $('C__CMDB__PERSON__SELECTION__HIDDEN');

		$('resetter').on('click', function () {
			if (confirm('[{isys type="lang" p_bHtmlEncode=0 ident="LC__MODULE__CMDB__RESTORE_DEFAULT_LIST_CONFIG_CONFIRM"}]'))
			{
				$('sort').setValue('default_values');
				$('isys_form').submit();
			}
		});

		if ($button_set_for_user && $person_selection) {
			$button_set_for_user.on('click', function () {
				var default_sorting = $('list_selection_field').down('input:checked');

				if ($person_selection.getValue().blank()) {
					idoit.Notify.info('[{isys type="lang" ident="LC__MODULE__CMDB__SET_FOR_USER_NOTICE"}]', {life:10});
					return;
				}

				if (confirm('[{isys type="lang" ident="LC__MODULE__CMDB__SET_FOR_USER_CONFIRM" p_bHtmlEncode=false}]')) {
					new Ajax.Request('[{$ajax_url}]', {
						parameters: {
							'[{$smarty.const.C__GET__NAVMODE}]': '[{$smarty.const.C__NAVMODE__SAVE}]',
							list__HIDDEN: $F('list__HIDDEN'),
							list__HIDDEN_IDS: $F('list__HIDDEN_IDS'),
							row_clickable: $('row_clickable').checked ? 'on' : '',
							default_sorting: (default_sorting ? default_sorting.getValue() : null),
							sorting_direction: $F('sorting_direction'),
							for_users: '1',
							users: $person_selection.getValue(),
							object_type: '[{$objecttype.isys_obj_type__const}]'
						},
						onComplete: function (response) {
							// Nothing to do here. Notify popups will be triggered by PHP.
						}
					})
				}
			});
		}

		if ($button_set_as_default) {
			$button_set_as_default.on('click', function () {
				if (confirm('[{isys type="lang" ident="LC__MODULE__CMDB__SET_AS_DEFAULT_CONFIRM" p_bHtmlEncode=false}]')) {
					new Ajax.Request('[{$ajax_url}]', {
						parameters: {
							'[{$smarty.const.C__GET__NAVMODE}]': '[{$smarty.const.C__NAVMODE__SAVE}]',
							list__HIDDEN: $F('list__HIDDEN'),
							list__HIDDEN_IDS: $F('list__HIDDEN_IDS'),
							as_default: '1',
							object_type: '[{$objecttype.isys_obj_type__const}]'
						},
						onComplete: function (response) {
							// Nothing to do here. Notify popups will be triggered by PHP.
						}
					});
				}
			});
		}
	})();
	</script>
[{/if}]