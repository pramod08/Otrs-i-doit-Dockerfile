<table class="contentTable">
	<tr>
		<td class="key">[{isys type='f_label' name='C__CATG__GLOBAL_TITLE' ident="LC__CMDB__CATG__GLOBAL_TITLE"}]</td>
		<td class="value">
            [{isys type="f_text" name="C__CATG__GLOBAL_TITLE"}]
            [{if $placeholders_g_global}]
            <img src="images/icons/silk/help.png" class="vam" onclick="if($('placeholderHelper_g_global').visible() === false){$('placeholderHelper_g_global').slideDown({duration:0.2});}else{$('placeholderHelper_g_global').slideUp({duration:0.2});}" />

            <div class="box ml20 mt5 mb5 overflow-auto text-shadow" style="display:none;height:200px;" id="placeholderHelper_g_global">
                <table class="border-none m0 w100 listing hover" style="border:0;">
                    [{foreach from=$placeholders_g_global item="plholder" key="plkey"}]
                    <tr>
                        <td class="mouse-pointer">
                            <span><code>[{$plkey}]</code> = [{$plholder}]</span>
                        </td>
                    </tr>
                    [{/foreach}]
                </table>
            </div>
            [{/if}]
        </td>
	</tr>
	<tr>
		<td colspan="2">
			<hr />
		</td>
	</tr>
	<tr>
		<td class="key">[{isys type='f_label' name='C__CATG__GLOBAL_CATEGORY' ident="LC__CMDB__CATG__GLOBAL_CATEGORY"}]</td>
		<td class="value">[{isys type="f_popup" p_strPopupType="dialog_plus" name="C__CATG__GLOBAL_CATEGORY" p_strClass="input"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type='f_label' name='C__CATG__GLOBAL_PURPOSE' ident="LC__CMDB__CATG__GLOBAL_PURPOSE"}]</td>
		<td class="value">[{isys type="f_popup" p_strPopupType="dialog_plus" name="C__CATG__GLOBAL_PURPOSE" p_strClass="input"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type='f_label' name='C__OBJ__STATUS' ident="LC__UNIVERSAL__CONDITION"}]</td>
		<td class="value">[{isys type="f_dialog" default="n/a" p_bDbFieldNN="1" name="C__OBJ__STATUS" p_strClass="input"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type='f_label' name='C__OBJ__CMDB_STATUS' ident="LC__UNIVERSAL__CMDB_STATUS"}]</td>
		<td class="value">
			<div class="fl">[{isys type="f_dialog" default="n/a" p_bDbFieldNN="1" name="C__OBJ__CMDB_STATUS" p_strClass="input"}]</div>
			<div class="cmdb-marker" id="cmdb_status_color" style="margin-top:0;margin-left:5px;background-color:#[{$status_color}]; height:[{if isys_glob_is_edit_mode()}]18[{else}]14[{/if}]px;"></div>
			<br class="cb" />
		</td>
	</tr>
	<tr>
		<td colspan="2">
			<hr />
		</td>
	</tr>
	<tr>
		<td class="key">[{isys type='lang' ident="LC__CATG__ODEP_OBJ"}] ID</td>
		<td class="value">[{isys type="f_text" p_bDisabled="1" default="n/a" name="C__OBJ__ID"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" name="C__OBJ__TYPE" ident="LC_UNIVERSAL__OBJECT_TYPE"}]</td>
		<td class="value">[{isys type="f_dialog" name="C__OBJ__TYPE" p_bDbFieldNN="1" p_strClass="input"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type='f_label' name='C__CATG__GLOBAL_SYSID' ident="LC__CMDB__CATG__GLOBAL_SYSID"}]</td>
		<td class="value">[{isys type="f_text" name="C__CATG__GLOBAL_SYSID"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type='f_label' name='C__CATG__GLOBAL_CREATED' ident="LC__TASK__DETAIL__WORKORDER__CREATION_DATE"}]</td>
		<td class="value">[{isys type="f_text" name="C__CATG__GLOBAL_CREATED"}] ([{$created_by|default:"n/a"}])</td>
	</tr>
	<tr>
		<td class="key">[{isys type='f_label' name='C__CATG__GLOBAL_UPDATED' ident="LC__UNIVERSAL__DATE_OF_CHANGE"}]</td>
		<td class="value">[{isys type="f_text" name="C__CATG__GLOBAL_UPDATED"}] ([{$changed_by|default:"n/a"}])</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" name="C__CATG__GLOBAL_TAG" ident="LC__CMDB__CATG__GLOBAL_TAG"}]</td>
		<td class="value">[{isys type="f_popup" p_strPopupType="dialog_plus" name="C__CATG__GLOBAL_TAG"}]</td>
	</tr>
</table>

<script type="text/javascript">
	(function () {
		"use strict";

		var updateObjectTitle = function(ev) {

			function update_title_field () {
				var title;

				if ($person_firstname_field && $person_lasttname_field) {
					title = $object_title.getValue().split(' ');

					$person_firstname_field.setValue(title.shift());
					$person_lasttname_field.setValue(title.join(' '));
				} else if ($group_title_field) {
					$group_title_field.setValue($object_title.getValue());
				} else if ($organization_title_field) {
					$organization_title_field.setValue($object_title.getValue());
				}
			}

			var $object_title = $('C__CATG__GLOBAL_TITLE'),
				$cmdb_status = $('C__OBJ__CMDB_STATUS'),
				$person_firstname_field = $('C__CONTACT__PERSON_FIRST_NAME'),
				$person_lasttname_field = $('C__CONTACT__PERSON_LAST_NAME'),
				$group_title_field = $('C__CONTACT__GROUP_TITLE'),
				$organization_title_field = $('C__CONTACT__ORGANISATION_TITLE'),
				cmdb_status_colors = '[{$status_colors}]'.evalJSON();

			if ($object_title) {
				$object_title.focus();

				if ($('navMode').value != '[{$smarty.const.C__NAVMODE__EDIT}]') {
					$object_title.select();
				}

				$object_title.on('change', update_title_field);
				$object_title.on('keyup', update_title_field);
			}

			if ($cmdb_status) {
				$cmdb_status.on('change', function () {
					var selected_cmdb_status = $F(this);

					if (cmdb_status_colors.hasOwnProperty(selected_cmdb_status)) {
						$('cmdb_status_color').setStyle({backgroundColor: cmdb_status_colors[selected_cmdb_status]});
					}
				});

				$cmdb_status.simulate('change');
			}

			// stop this event observe so it doesn't get executed in other categories
			document.stopObserving('form:submitted');
		};

		document.observe('dom:loaded',     updateObjectTitle.bindAsEventListener());
		document.observe('form:submitted', updateObjectTitle.bindAsEventListener());

		// Function for tag
		var $tag = $('C__CATG__GLOBAL_TAG'),
			$tag_chosen = null;

		if($tag) {
			idoit.callbackManager
					.registerCallback('cmdb-catg-global-tag-update', function (selected) {
						if ($tag_chosen !== null) {
							$tag_chosen.destroy();
						}

						$tag.setValue(selected).fire('chosen:updated');
						$tag_chosen = new Chosen($tag, {
							disable_search_threshold: 10,
							search_contains:          true
						});
					})
					.triggerCallback('cmdb-catg-global-tag-update', $F('C__CATG__GLOBAL_TAG'));
		}
	}());
</script>