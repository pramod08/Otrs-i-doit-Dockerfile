<table class="contentTable">
	<tr>
		<td class="key">[{isys type="f_label" name="C__ITSERVICE__CONFIG__TITLE" ident="LC__ITSERVICE__CONFIG__TITLE"}]</td>
		<td class="value">[{isys type="f_text" name="C__ITSERVICE__CONFIG__TITLE"}]</td>
	</tr>
	<tr>
		<td colspan="2"><hr style="margin:5px 0;" /></td>
	</tr>
</table>

<table id="itservice-filter-table" class="contentTable">
	<colgroup>
		<col style="width:5%;">
		<col style="width:15%;">
		<col style="width:80%;">
	</colgroup>
	<thead>
		<tr>
			<th><span class="ml20">[{isys type="lang" ident="LC__ITSERVICE__CONFIG__ACTIVE"}]</span></th>
			<th class="right">[{isys type="lang" ident="LC__ITSERVICE__CONFIG__CONDITION"}]</th>
			<th><span class="ml20">[{isys type="lang" ident="LC__ITSERVICE__CONFIG__PARAMETER"}]</span></th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td><input type="checkbox" name="config_relation-type" class="ml20 inputCheckbox" [{if $filter_config.formatted__data["relation-type"]}]checked="checked"[{/if}] /></td>
			<td class="key"><label for="C__ITSERVICE__CONFIG__RELATIONTYPE">[{isys type="lang" ident="LC__ITSERVICE__CONFIG_FILTER__RELATIONTYPE"}]</label></td>
			<td>
				[{isys type="f_dialog_list" name="C__ITSERVICE__CONFIG__RELATIONTYPE" p_bSort=true add_callback="idoit.callbackManager.triggerCallback('itservice_filter_check', 'relation-type');"}]
				<span class="ml10">[{isys type="lang" ident="LC__ITSERVICE__CONFIG_FILTER__CONDITION"}]</span>
			</td>
		</tr>
		<tr>
			<td><input type="checkbox" name="config_priority" class="ml20 inputCheckbox" [{if $filter_config.formatted__data["priority"]}]checked="checked"[{/if}] /></td>
			<td class="key"><label for="C__ITSERVICE__CONFIG__PRIORITY">[{isys type="lang" ident="LC__ITSERVICE__CONFIG_FILTER__PRIORITY"}]</label></td>
			<td>
				[{isys type="f_dialog" name="C__ITSERVICE__CONFIG__PRIORITY" p_bDbFieldNN=true p_onChange="idoit.callbackManager.triggerCallback('itservice_filter_check', 'priority');"}]
				<span class="ml10">[{isys type="lang" ident="LC__ITSERVICE__CONFIG_FILTER__CONDITION"}]</span>
			</td>
		</tr>
		<tr>
			<td><input type="checkbox" name="config_object-type" class="ml20 inputCheckbox" [{if $filter_config.formatted__data["object-type"]}]checked="checked"[{/if}] /></td>
			<td class="key"><label for="C__ITSERVICE__CONFIG__OBJECT_TYPE">[{isys type="lang" ident="LC__ITSERVICE__CONFIG_FILTER__OBJTYPE"}]</label></td>
			<td>
				[{isys type="f_dialog_list" name="C__ITSERVICE__CONFIG__OBJECT_TYPE" p_bSort=true add_callback="idoit.callbackManager.triggerCallback('itservice_filter_check', 'object-type');"}]
				<span class="ml10">[{isys type="lang" ident="LC__ITSERVICE__CONFIG_FILTER__CONDITION"}]</span>
			</td>
		</tr>
		<tr>
			<td><input type="checkbox" name="config_level" class="ml20 inputCheckbox" [{if $filter_config.formatted__data["level"]}]checked="checked"[{/if}] /></td>
			<td class="key"><label for="C__ITSERVICE__CONFIG__LEVEL">[{isys type="lang" ident="LC__ITSERVICE__CONFIG_FILTER__LEVEL"}]</label></td>
			<td>
				[{isys type="f_dialog" name="C__ITSERVICE__CONFIG__LEVEL" p_bDbFieldNN=true p_bSort=false p_onChange="idoit.callbackManager.triggerCallback('itservice_filter_check', 'level');"}]
				<span class="ml10">[{isys type="lang" ident="LC__ITSERVICE__CONFIG_FILTER__CONDITION"}]</span>
			</td>
		</tr>
		<tr>
			<td><input type="checkbox" name="config_cmdb-status" class="ml20 inputCheckbox" [{if $filter_config.formatted__data["cmdb-status"]}]checked="checked"[{/if}] /></td>
			<td class="key"><label for="C__ITSERVICE__CONFIG__CMDB_STATUS">[{isys type="lang" ident="LC__ITSERVICE__CONFIG_FILTER__CMDB_STATUS"}]</label></td>
			<td>
				[{isys type="f_dialog_list" name="C__ITSERVICE__CONFIG__CMDB_STATUS" p_bDbFieldNN=true p_bSort=false p_onChange="idoit.callbackManager.triggerCallback('itservice_filter_check', 'cmdb-status');"}]
				<span class="ml10">[{isys type="lang" ident="LC__ITSERVICE__CONFIG_FILTER__CONDITION"}]</span>
			</td>
		</tr>
	</tbody>
</table>

[{isys type="f_text" name="id" p_bInvisible=true}]

<script>
	(function () {
		"use strict";

		var $label = $('scroller').down('label[for="C__ITSERVICE__CONFIG__TITLE"]'),
			$title = $('C__ITSERVICE__CONFIG__TITLE'),
			$save_button = $('navbar_item_C__NAVMODE__SAVE');

		if ($label) {
			$label.insert(new Element('span', {className:'red bold vam'}).update('*'));
		}

		if ($save_button) {
			$save_button
				.writeAttribute('onclick', null)
				.on('click', function () {
					var $img = $title.removeClassName('error').previous('img')
						.removeClassName('mouse-pointer')
						.writeAttribute('src', '[{$dirs.images}]empty.gif');

					Tips.remove($img);

					$title.removeClassName('error');

					if ($title.getValue().blank()) {
						$title.addClassName('error');
						$img.addClassName('mouse-pointer')
							.writeAttribute('src', '[{$dirs.images}]icons/alert-icon.png');

						new Tip($img, new Element('p', {className:'p5', style:'font-size:12px;'}).update('[{isys type="lang" ident="LC__UNIVERSAL__MANDATORY_FIELD_IS_EMPTY"}]'), {showOn:'click', hideOn:'click', effect: 'appear', style:'darkgrey'});
						return;
					}

					$('navMode').setValue(10);
					form_submit();
				});
		}

		idoit.callbackManager.registerCallback('itservice_filter_mandatory_check', function (type) {

		});

		idoit.callbackManager.registerCallback('itservice_filter_check', function (type) {
			$$('input[name="config_' + type + '"]')[0].checked = true;
		});
	}());
</script>