<fieldset class="overview">
	<legend><span style="border-top:none;">[{isys type="lang" ident="LC__MASS_CHANGE__OPTIONS"}]</span></legend>
	<table class="contentTable">
		<tr >
			<td class="key">
				[{isys type="f_label" name="C__MODULE__IMPORT__CABLING__CABLING_TYPE" ident="LC__MODULE__IMPORT__CABLING_TYPE" description="LC__MODULE__IMPORT__CABLING__DESCRIPTION__CABLING_TYPE"}]
			</td>
			<td class="value">
				[{isys type="f_dialog" name="C__MODULE__IMPORT__CABLING__CABLING_TYPE" p_bDbFieldNN="1"}]
			</td>
		</tr>
		<tr>
			<td class="key">
				[{isys type="f_label" name="C__MODULE__IMPORT__CABLING__CABLE_TYPE" ident="LC__CATG__CONNECTOR__CONNECTION_TYPE" description="LC__MODULE__IMPORT__CABLING__DESCRIPTION__CONNECTOR_TYPE"}]
			</td>
			<td class="value">
				[{isys type="f_dialog" name="C__MODULE__IMPORT__CABLING__CABLE_TYPE" p_strTable="isys_connection_type" p_bDbFieldNN="1"}]
			</td>
		</tr>
		<tr>
			<td class="key">
				[{isys type="f_label" name="C__MODULE__IMPORT__CABLING__OBJECT_CHECK_EXIST" ident="LC__MODULE__IMPORT__CABLING__OPTION_TEXT_TWO"}]
			</td>
			<td class="value">
				[{isys type="checkbox" p_strOnClick="Cabling.check_all_objects_in_between(this);" id="C__MODULE__IMPORT__CABLING__OBJECT_CHECK_EXIST" name="C__MODULE__IMPORT__CABLING__OBJECT_CHECK_EXIST"}]
			</td>
		</tr>
		<tr>
			<td class="key">
				[{isys type="f_label" name="C__MODULE__IMPORT__CABLING__OBJTYPE" ident="LC__MODULE__IMPORT__CABLING__OBJECTTYPE_FOR_AUTO_GENERATED_OBJECTS"}]
			</td>
			<td class="value">
				[{isys type="f_dialog" name="C__MODULE__IMPORT__CABLING__OBJTYPE" p_bDbFieldNN=1}]
			</td>
		</tr>
	</table>

	<a href="javascript:" class="m10" onclick="if(!this.next('table').visible()){this.next('table').show();}else{this.next('table').hide();}">[{isys type="lang" ident="LC__MODULE__IMPORT__CABLING__ADVANCED_OPTIONS"}]</a>

	<table class="contentTable" [{if !$advanced_options}]style="display:none;"[{/if}]>
		<tr class="import_cabling_advanced_options" >
			<td class="key">
				[{isys type="f_label" name="C__MODULE__IMPORT__CABLING__ADVANCED_OP_WIRING_SYSTEM" ident="LC__CATG__CONNECTOR__CONNECTED_NET" description="Gilt nur für die Verkabelungsart 'Anschlüsse'"}]
			</td>
			<td class="value">
				[{isys
					title="LC__BROWSER__TITLE__WIRING_SYSTEM"
					name="C__MODULE__IMPORT__CABLING__ADVANCED_OP_WIRING_SYSTEM"
					type="f_popup"
					p_strPopupType="browser_object_ng"
					typeFilter="C__OBJTYPE__WIRING_SYSTEM"
					groupFilter="C__OBJTYPE_GROUP__INFRASTRUCTURE"}]
			</td>
		</tr>
		<tr class="import_cabling_advanced_options" >
			<td class="key">
				[{isys type="f_label" name="C__MODULE__IMPORT__CABLING__ADVANCED_OP_CABLE_TYPE" ident="LC__CMDB__CATS__CABLE__TYPE"}]
			</td>
			<td class="value">
				[{isys type="f_dialog" name="C__MODULE__IMPORT__CABLING__ADVANCED_OP_CABLE_TYPE" p_strTable="isys_cable_type"}]
			</td>
		</tr>
	</table>
</fieldset>

<div width="100%">
	<table width="100%" >
		<tr>
			<td width="28%">
				<fieldset class="overview" style="height:125px;">
					<legend><span>[{isys type="lang" ident="LC__MODULE__IMPORT__CABLING__LOAD_CSV_FILE"}]</span></legend>

					<div style="float:left; width:100%;" class="m10">
						<strong style="float: left;">[{isys type="lang" ident="LC__CMDB__CATG__IMAGE_OBJ_FILE"}]: </strong><br />

						<input type="file" name="import_file" />
						<div class="mt10">
							<button type="button" class="btn" onclick="$('upload_loading').show();$('import_submitter').value='load_csv';document.forms[0].submit()">
								<img src="[{$dir_images}]icons/silk/table_edit.png" class="mr5" /><span>[{isys type="lang" ident="LC__MODULE__IMPORT__CABLING__LOAD_CSV_FILE"}]</span>
							</button>

							[{if $cabling_import_result}]
								<a class="btn" href="[{$download_link}]" type="application/octet-stream">
									<img src="[{$dir_images}]icons/silk/table_save.png" class="mr5" /><span>[{isys type="lang" ident="LC__UNIVERSAL__DOWNLOAD_FILE"}]</span>
								</a>
							[{/if}]
							<img src="images/please_wait.gif" style="vertical-align: middle; display:none; margin-left:10px; margin-top:5px;" id="upload_loading"/>
						</div>
					</div>
					<input type="hidden" id="import_submitter" name="import_submitter">
					<br />
				</fieldset>
			</td>

			<td width="20%">
				<fieldset class="m5 overview" style="height:125px;">
					<legend><span>[{isys type="lang" ident="LC__UNIVERSAL__IMPORT"}]</span></legend>

					<div class="mt10">
						<button type="button" class="btn mt10" onclick="$('loading_import').show();$('import_submitter').value='import';document.forms[0].submit()">
							<img src="[{$dir_images}]icons/silk/tick.png" class="mr5" /><span>[{isys type="lang" ident="LC__MODULE__IMPORT__CABLING__START_CABLING"}]</span>
						</button>

						<img src="images/please_wait.gif" style="vertical-align: middle; display:none; margin-left:10px" id="loading_import"/>
						<br />
						<br />

						<table style="display:none;border-width:1px;border-style:dotted" width="100%" id="import_messages">
							<tr>
								<td style="height:20px;background:#C2FFBC;">
									<label class="ml5"></label>
								</td>
							</tr>
						</table>
					</div>
				</fieldset>
			</td>

			<td width="*">
				<fieldset class="overview" style="height:125px;">
					<legend><span>[{isys type="lang" ident="LC__MODULE__IMPORT__CABLING__HELP"}]</span></legend>

					<div style="position:relative;" class="m10">
						<img src='[{$img_dir}]icons/infoicon/info.png'> [{isys type="lang" ident="LC__MODULE__IMPORT__CABLING__INFO_TEXT_ONE"}]<br />
						<img src='[{$img_dir}]icons/infoicon/error.png'> [{isys type="lang" ident="LC__MODULE__IMPORT__CABLING__INFO_TEXT_TWO"}]<br />
						<img src='[{$img_dir}]icons/infoicon/warning.png'> [{isys type="lang" ident="LC__MODULE__IMPORT__CABLING__INFO_TEXT_THREE"}]<br />
						<img src='[{$img_dir}]icons/infoicon/ok.png'> [{isys type="lang" ident="LC__MODULE__IMPORT__CABLING__INFO_TEXT_FOUR"}]<br />
						<img src='[{$img_dir}]icons/silk/zoom.png'> [{isys type="lang" ident="LC__MODULE__IMPORT__CABLING__INFO_TEXT_FIVE"}]<br />
						<img src='[{$img_dir}]icons/silk/arrow_switch.png'> [{isys type="lang" ident="LC__MODULE__IMPORT__CABLING__INFO_TEXT_SIX"}]
					</div>
				</fieldset>
			</td>
		</tr>
	</table>

	<div style="width:100%">
		<ul id="tabs" class="gradient browser-tabs m0">
			<li>
				<a class="text-shadow" href="#csv_content" onclick="$('add_cabling_row').show();">[{isys type="lang" ident="LC__UNIVERSAL__CONTENT"}]</a>
			</li>
			[{if $cabling_import_result}]
			<li>
				<a class="text-shadow" href="#import_result" onclick="$('add_cabling_row').hide();">[{isys type="lang" ident="LC__MODULE__IMPORT__CABLING__IMPORT_LOG"}]</a>
			</li>
			[{/if}]
		</ul>

		<div class="popup" style="display:none;" id="multiedit_options">
			<h3 class="popup-header">
				<img class="fr mouse-pointer" alt="x" src="[{$dir_images}]prototip/styles/default/close.png" onclick="popup_close('multiedit_options');" />
				<span>Suffix</span>
			</h3>

			<div class="popup-content p10">
				<table class="contentTable">
					[{include file="content/bottom/content/title_suffix.tpl"}]
				</table>
			</div>

			<div class="popup-footer">
				[{isys type="f_button" p_onClick="Cabling.set_suffix_format();Cabling.change_column($($('title_identifier').value));popup_close('multiedit_options');" p_strValue="Anwenden"}]
				[{isys type="f_button" p_onClick="popup_close('multiedit_options');" p_strValue="Abbrechen"}]
			</div>
		</div>

		<div class="ml5" id="add_cabling_row">

			<div class="m10 fl" style="position:relative;z-index: 1;">
				<button type="button" class="btn" onclick="Cabling.add_row();">
					<img src="[{$dir_images}]icons/silk/add.png" class="mr5" /><span>[{isys type="lang" ident="LC__MODULE__IMPORT__CABLING__ADD_NEW_ROW"}]</span>
				</button>
			</div>
			<div style="position:relative;z-index: 0;top:5px;">
			[{isys type="f_count" name="C__MODULE__IMPORT__CABLING__ADD_ROWS" id="C__MODULE__IMPORT__CABLING__ADD_ROWS"}]
			</div>
		</div>
		<br />
		<br />
		<div style="overflow: auto;" class="m5" id="csv_content">
			[{$content}]
		</div>
		<div style="overflow: auto;display:none;" class="m5" id="import_result">
			<fieldset>
				<pre>[{$import_log}]</pre>
			</fieldset>
		</div>
		<br />
	</div>
</div>

<br />

<script type="text/javascript">

	var Cabling = {
		objectsExists:                  [],
		objectsNotExists:               [],
		type_filter:                    '[{$typefilter_as_string}]',

		// This method checks if the title exists as an object (only for object fields)
		check_object:                   function (p_element, p_is_cable, p_suggestion) {

			var object_in_between = false;
			var current_column = parseInt(p_element.up('td').getAttribute('data-column'));
			var current_row = p_element.up('td').getAttribute('data-row');
			var data_type = p_element.up('td').getAttribute('data-type');
			var default_bg = '';

			if (p_element.up().previous().down().id != '' && p_element.up().next('td', 3) != undefined) {
				if (p_element.up().next('td', 3).down().value != '') {
					// Zwischenobjekt
					var object_in_between = true;
					if ($('C__MODULE__IMPORT__CABLING__OBJECT_CHECK_EXIST').checked == true) {
						p_is_cable = true;
					}
				}
			}

			if (!p_is_cable && p_element.value != '') {
				if (Cabling.objectsExists.indexOf(p_element.value) >= 0) {
					if (current_row % 2 == 0) {
						default_bg = '#00AB00';
					}
					else {
						default_bg = "#00CF00";
					}
					$(p_element.id).up('td').setStyle({
						cursor:          "default",
						background:      '',
						backgroundColor: default_bg
					});

					Cabling.change_siblings(current_column, current_row, p_element.value);
				}
				else if (Cabling.objectsNotExists.indexOf(p_element.value) >= 0) {
					$(p_element.id).up('td').setStyle({
						cursor:     "default",
						background: "#e77777 url('[{$img_dir}]gradient.png') repeat-x"
					});
					Cabling.change_siblings(current_column, current_row, p_element.value);
				}
				else {
					new Ajax.Request('[{$ajax_link}]' + 'check_object',
							{
								parameters: {
									func:     'check_object',
									title:    p_element.value,
									is_cable: p_is_cable
								},
								method:     'post',
								onSuccess:  function (transport) {
									var ajax_result = transport.responseText;

									if (!ajax_result) {
										$(p_element.id).up('td').setStyle({
											cursor:     "default",
											background: "#e77777 url('[{$img_dir}]gradient.png') repeat-x"
										});
										Cabling.objectsNotExists.push(p_element.value);
										Cabling.change_siblings(current_column, current_row, p_element.value);
									}
									else {
										if (current_row % 2 == 0) {
											default_bg = '#00AB00';
										}
										else {
											default_bg = "#00CF00";
										}
										$(p_element.id).up('td').setStyle({
											cursor:          "default",
											background:      '',
											backgroundColor: default_bg
										});

										/*$(p_element.id).up().insert(new Element('a',
										 {
										 href: '?objID='+ajax_result

										 }).update('test'));*/

										if (Cabling.objectsExists.indexOf(p_element.value) < 0) {
											Cabling.objectsExists.push(p_element.value);
										}

										Cabling.change_siblings(current_column, current_row, p_element.value);
									}
								}
							})
				}
			}
			else if (data_type != 'cabling_cable') {
				if (object_in_between) {
					if (p_element.value != '') {
						if (current_row % 2 == 0) {
							default_bg = '#EFEF00';
						}
						else {
							default_bg = "#FFFF00";
						}
					}
					else {
						default_bg = $(p_element.id).up('td').getAttribute('data-default-background');
					}
					$(p_element.id).up('td').setStyle({
						cursor:          "default",
						background:      '',
						backgroundColor: default_bg
					});
					Cabling.change_siblings(current_column, current_row, p_element.value);
				}
				else {
					if (current_column == 0) {
						$(p_element.id).up('td').setStyle({
							cursor:     "default",
							background: "#e77777 url('[{$img_dir}]gradient.png') repeat-x"
						});
					}
					else {
						default_bg = $(p_element.id).up('td').getAttribute('data-default-background');
						$(p_element.id).up('td').setStyle({
							cursor:          "default",
							background:      '',
							backgroundColor: default_bg
						});
						Cabling.change_siblings(current_column, current_row, p_element.value);
					}
				}
			}
		},

		// Checks all objects in a row between the start and end object
		change_siblings:                function (p_column, p_row, p_value) {
			while (p_column > 4) {
				p_column = p_column - 4;
				Cabling.check_object($('row_' + p_row + '_' + p_column), false, false);
			}
		},

		// Checks all objects between the start and end object
		check_all_objects_in_between:   function (chb_ele) {
			var rows = $$('.import_row').length;
			var columns = $$('.cabling_table_cell_head').length - 1;
			var last_ele = '';
			var counter = 0;

			while (rows > 0) {
				last_ele = '';
				counter = columns;
				while (last_ele == '') {
					if (counter <= 4)
						break;

					if ($('row_' + rows + '_' + counter).value != '') {
						last_ele = $('row_' + rows + '_' + counter);
					}
					counter = counter - 4;
				}
				if (last_ele != '') {
					Cabling.check_object(last_ele, false, false);
				}
				rows--;
			}
		},

		// This method changes all values for the current column
		change_column:                  function (p_element) {
			var current_column = parseInt(p_element.up().getAttribute('data-column'));
			var field_type = p_element.up().getAttribute('data-type');
			var l_is_cable = true;

			if (field_type == 'cabling_object') {
				var l_is_cable = false;
			}

			var suffix_type = '';

			if ($('title_identifier').value == p_element.id) {
				window.show_preview();
			}

			var counter = 0;
			$$('.import_row').each(function (ele) {
				if (current_column > 0) {
					var changed_field = ele.down().next('td', current_column).down('input');
					changed_field.value = Cabling.format_by_suffix(p_element, counter);
				}
				else {
					var changed_field = ele.down('input');
					changed_field.value = Cabling.format_by_suffix(p_element, counter);
				}

				if (l_is_cable === false) {
					Cabling.check_object(changed_field, false, false);
				}
				counter++;
			});
		},

		// This method sets the suffix for the current column
		set_suffix_format:              function () {
			var ele_id = $('title_identifier').value;
			var ele = $(ele_id);
			var suffix_type = '';

			$$('.suf_options').each(function (ele) {
				if (ele.checked) {
					suffix_type = ele.value;
				}
			});
			ele.setAttribute('format-suffix-type', suffix_type);
			switch (suffix_type) {
				case '##COUNT##':
					ele.setAttribute('format-suffix-custom', '##COUNT##');
					ele.setAttribute('format-suffix-start', $('count_starting_at').value);
					ele.setAttribute('format-suffix-add-zero', (($('zero_point_calc').checked) ? 'true' : 'false'));
					ele.setAttribute('format-suffix-zeros', $('zero_points').value);
					break;
				case '-1':
					ele.setAttribute('format-suffix-custom', $('object_appending_own').value);
					ele.setAttribute('format-suffix-start', $('count_starting_at').value);
					ele.setAttribute('format-suffix-add-zero', (($('zero_point_calc').checked) ? 'true' : 'false'));
					ele.setAttribute('format-suffix-zeros', $('zero_points').value);
					break;
				default:
					ele.setAttribute('format-suffix-custom', '##COUNT##');
					ele.setAttribute('format-suffix-start', '');
					ele.setAttribute('format-suffix-add-zero', '');
					ele.setAttribute('format-suffix-zeros', '');
					break;
			}
		},

		// This method sets the options for the format in the overview
		set_suffix_format_preselection: function (p_element) {
			var suffix_type = p_element.getAttribute('format-suffix-type');
			if (suffix_type != null) {
				var suffix_custom = p_element.getAttribute('format-suffix-custom');
				var suffix_start = p_element.getAttribute('format-suffix-start');
				var suffix_add_zero = p_element.getAttribute('format-suffix-add-zero');
				var suffix_zeros = p_element.getAttribute('format-suffix-zeros');

				$$('.suf_options').each(function (ele) {
					if (ele.value == suffix_type) {
						ele.checked = true;
						return null;
					}
				});

				$('object_appending_own').value = suffix_custom;
				$('count_starting_at').value = suffix_start;
				$('zero_point_calc').checked = ((suffix_add_zero == 'true') ? true : false);
				$('zero_points').value = suffix_zeros;
				window.show_preview();
			}
			else {

				$$('.suf_options').each(function (ele) {
					if (ele.value == '') {
						ele.checked = true;
						return null;
					}
				});

				$('object_appending_own').value = '##COUNT##';
				$('count_starting_at').value = 0;
				$('zero_point_calc').checked = true;
				$('zero_points').value = 2;
				window.show_preview();
			}
		},

		// This method formats all values for the current column with the specified options
		format_by_suffix:               function (p_element, p_counter) {

			var suffix_type = p_element.getAttribute('format-suffix-type');
			var suffix_custom = p_element.getAttribute('format-suffix-custom');
			var suffix_start = parseInt(p_element.getAttribute('format-suffix-start')) + p_counter;
			var suffix_add_zero = p_element.getAttribute('format-suffix-add-zero');
			var suffix_zeros = parseInt(p_element.getAttribute('format-suffix-zeros'));

			var start_with_as_string = suffix_start.toString();

			var l_value = p_element.value;

			if (suffix_type != '') {
				var appending_zeros = "";
				var additional = "";

				if (suffix_add_zero == 'true') {
					for (n = 0; n < suffix_zeros; n++) {
						appending_zeros += 0;
					}
					if (suffix_start > 9) {
						appending_zeros = appending_zeros.substr(0,
								(appending_zeros.length - (start_with_as_string.length - 1)));
					}
					additional = appending_zeros;
				}

				switch (suffix_type) {
					case '##COUNT##':
						additional = additional + suffix_start;
						start_with_as_string = String(suffix_start);
						break;
					case '-1':
						additional = suffix_custom.replace('##COUNT##', additional + suffix_start);
						start_with_as_string = String(suffix_start);
						break;
				}
				l_value = l_value + additional;
			}
			return l_value;
		},

		// This method removes a row
		remove_row:                     function (p_row) {
			$('row_' + p_row).remove();
		},

		add_row:                  function () {

			$('row_template').show();

			var add_rows = $('C__MODULE__IMPORT__CABLING__ADD_ROWS').value;
			while (add_rows > 0) {
				var new_row = $('row_template').clone(true);
				var row = ($$('.import_row').length + 1);
				new_row.id = 'row_' + row;
				$('cabling_table').down().next().insert(new_row);

				var new_element = $(new_row.id);

				new_element.addClassName('import_row ' +
				                         ((row % 2 == 0) ? 'CMDBListElementsOdd' : 'CMDBListElementsEven'));
				new_element.down('a').setAttribute('onclick', 'Cabling.remove_row(' + row + ')');
				new_element.setStyle({
					cursor: 'default'
				});

				var element_collection = $A(new_element.children);
				var counter = 0;
				var new_id = '';
				element_collection.each(function (ele) {
					if (ele.down().type == 'text') {
						ele.setAttribute('data-row', row);
						if (row % 2 == 0) {
							if (counter % 2 == 0) {
								ele.setStyle({
									background: '#DEDEDE'
								});
								ele.setAttribute('data-default-background', '#DEDEDE');
							}
						}
						else {
							if (counter % 2 == 0) {
								ele.setStyle({
									background: '#EFEFEF'
								});
								ele.setAttribute('data-default-background', '#EFEFEF');
							}
						}

						new_id = ele.down().id.replace(/skip2/g, row);
						ele.down().id = new_id;

						var child_ele = $('row_' + row + '_' + counter);
						child_ele.name = 'csv_row[' + row + '][' + counter + ']';

						if (counter == 0 || counter % 2 == 0) {
							new idoit.Suggest('object_with_no_type', new_id, '',
									{
										paramerters:    {
											typeFilter: Cabling.type_filter
										},
										selectCallback: "Cabling.check_object($('" + new_id + "'), false, true);"
									});
							$(new_id).next().observe('click', function () {
								Cabling.check_object(this.previous(), false, false)
							});
						}

						counter++;
					}
				});
				add_rows--;
			}
			$('row_template').hide();
		},

		// This method triggers all needed functions for adding new columns
		add_columns:              function () {
			// Last element of header
			var last_ele_head = $$('.cabling_table_cell_head').last();
			var last_ele_multiedit = $$('.cabling_table_cell_multiedit').last();
			var rows = $$('.import_row').length;
			var columns = $$('.cabling_table_cell_head').length;
			var counter = 0;

			var data_type = last_ele_head.getAttribute('data-type');

			if (data_type == 'cabling_object') {
				// ADD output, cable, input, object, output
				counter = 5;
			}
			else if (data_type == 'connector_output' || data_type == 'connector_input') {
				// ADD cable, input, object, output
				counter = 4;
			}

			Cabling.add_column_head(last_ele_head, counter, columns);
			Cabling.add_column_multiedit(last_ele_multiedit, counter, columns);
			Cabling.add_columns_cabling(rows, columns, counter);
			Cabling.add_columns_template_row(counter, columns);
		},

		// This method adds new columns to the template row when pressing the add button
		add_columns_template_row: function (counter, columns) {
			var template_ele = $('row_template');
			while (counter > 0) {

				var input_name = 'csv_row[skip2][' + columns + ']';
				var input_id = 'row_skip2_' + columns;
				var td_tag = new Element('td', {className: 'cabling_table_cell_import'});
				var td_input_field = new Element('input',
						{
							type:      'text',
							size:      '15',
							maxlength: '35',
							name:      input_name,
							id:        input_id
						});
				var td_img_field = new Element('img',
						{
							className: 'vam',
							src:       '[{$img_dir}]icons/silk/zoom.png',
							style:     'cursor:pointer;'
						}
				);
				td_input_field.addClassName('inputText');
				if (counter == 5 || counter == 1) {
					// connector output
					td_tag.setAttribute("data-type", "connector_output");
				}
				else if (counter == 3) {
					// connector input
					td_tag.setAttribute("data-type", "connector_input");
				}
				else if (counter == 4) {
					// cable
					td_tag.setAttribute("data-type", "cabling_cable");
				}
				else if (counter == 2) {
					// object
					td_tag.setAttribute("data-type", "cabling_object");
					td_tag.setStyle({
						borderLeft:  '1px solid #000000',
						borderRight: '1px solid #000000'
					});
				}
				td_tag.setAttribute("data-column", columns);
				td_tag.setAttribute("data-row", 'skip2');
				td_tag.insert(td_input_field);

				if (counter == 2) {
					td_tag.insert(td_img_field);
				}

				template_ele.appendChild(td_tag);

				counter--;
				columns++;
			}
		},

		// This method adds new columns which will be imported when pressing the add button
		add_columns_cabling:      function (rows, columns, counter) {
			var row_counter = 1;
			while (row_counter <= rows) {
				var add_columns = counter;
				var column_index = columns;
				var append_to = $('row_' + row_counter);
				while (add_columns > 0) {
					var input_name = 'csv_row[' + row_counter + '][' + column_index + ']';
					var input_id = 'row_' + row_counter + '_' + column_index;
					var td_tag = new Element('td', {className: 'cabling_table_cell_import'});
					var td_input_field = new Element('input',
							{
								type:      'text',
								size:      '15',
								maxlength: '35',
								name:      input_name,
								value:     '',
								id:        input_id
							});
					var td_img_field = new Element('img',
							{
								className: 'vam',
								src:       '[{$img_dir}]icons/silk/zoom.png',
								onclick:   'Cabling.check_object(this.previous(), false, false)',
								style:     'cursor:pointer;'
							}
					);
					td_input_field.addClassName('inputText');

					if (add_columns == 5 || add_columns == 1) {
						// connector output
						td_tag.setAttribute("data-type", "connector_output");
					}
					else if (add_columns == 3) {
						// connector input
						td_tag.setAttribute("data-type", "connector_input");
					}
					else if (add_columns == 4) {
						// cable
						td_tag.setAttribute("data-type", "cabling_cable");
					}
					else if (add_columns == 2) {
						// object
						td_tag.setAttribute("data-type", "cabling_object");
						td_tag.setStyle({
							borderLeft:  '1px solid #000000',
							borderRight: '1px solid #000000'
						});
					}

					if (add_columns == 4 || add_columns == 2) {
						if (row_counter % 2 == 0) {
							td_tag.setStyle({
								background: '#DEDEDE'
							});
							td_tag.setAttribute('data-default-background', '#DEDEDE');
						}
						else {
							td_tag.setStyle({
								background: '#EFEFEF'
							});
							td_tag.setAttribute('data-default-background', '#EFEFEF');
						}
					}

					td_tag.setAttribute("data-column", column_index);
					td_tag.setAttribute("data-row", row_counter);
					td_tag.insert(td_input_field);
					append_to.appendChild(td_tag);

					if (add_columns == 2) {
						td_tag.insert(td_img_field);
						new idoit.Suggest('object_with_no_type', input_id, '',
								{
									paramerters:    {
										typeFilter: Cabling.type_filter
									},
									selectCallback: "Cabling.check_object($(" + input_id + "), false, true)"
								});
					}

					column_index++;
					add_columns--;
				}
				row_counter++;
			}
		},

		// This method adds the multiedit header when pressing the add button
		add_column_multiedit:     function (p_element, counter, columns) {
			while (counter > 0) {

				var input_name = 'csv_row[skip][' + columns + ']';
				var input_id = 'row_skip_' + columns;
				var td_tag = new Element('td',
						{className: 'cabling_table_cell_multiedit', style: 'border-bottom:#000000 solid 1px;'});
				var td_input_field = new Element('input',
						{
							type:      'text',
							size:      '15',
							maxlength: '35',
							name:      input_name,
							id:        input_id
						}).observe('change', function () {
							Cabling.change_column(this);
						});
				td_input_field.addClassName('inputText');
				var img_field = new Element('img',
				{
					src: '[{$img_dir}]icons/silk/cog.png'
				}).observe('click', function () {
					$('title_identifier').value = this.previous().id;
					Cabling.set_suffix_format_preselection(this.previous());

					popup_open($('multiedit_options'), 700, 280);

					$$('.suf').each(function (e) {
						e.appear();
					})
				});
				img_field.addClassName('vam');
				if (counter == 5 || counter == 1) {
					// connector output
					td_tag.setAttribute("data-type", "connector_output");
					td_input_field.setAttribute("value", "[{$lang_all_connectors}]");
				}
				else if (counter == 3) {
					// connector input
					td_tag.setAttribute("data-type", "connector_input");
					td_input_field.setAttribute("value", "[{$lang_all_connectors}]");
				}
				else if (counter == 4) {
					// cable
					td_tag.setAttribute("data-type", "cabling_cable");
					td_input_field.setAttribute("value",
							"[{isys type='lang' ident='LC__MODULE__IMPORT__CABLING__ALL_OBJECTS'}]");
				}
				else if (counter == 2) {
					// object
					td_tag.setAttribute("data-type", "cabling_object");
					td_input_field.setAttribute("value",
							"[{isys type='lang' ident='LC__MODULE__IMPORT__CABLING__ALL_OBJECTS'}]");
				}
				td_tag.setAttribute("data-column", columns);
				td_tag.insert(td_input_field);
				td_tag.insert(img_field);

				p_element.up().appendChild(td_tag);

				counter--;
				columns++;
			}
		},

		// This method adds the header when pressing the add button
		add_column_head:          function (p_element, counter, columns) {
			counter2 = 2;
			while (counter > 0) {
				//alert($$('.cabling_table_cell_head')[counter].down('span').innerHTML);

				var th_tag = new Element('th', {className: 'cabling_table_cell_head'});
				var th_hidden_field = new Element('input', {type: 'hidden', name: 'csv_row[0][' + columns + ']'});
				if (counter == 5 || counter == 1) {
					// connector output
					th_tag.setAttribute("data-type", "connector_output");
					th_hidden_field.setAttribute("value", "[{isys type='lang' ident='LC__CATG__CONNECTOR__OUTPUT'}]");
					th_tag.insert(new Element('span').insert($$('.cabling_table_cell_head')[counter2].down('span').innerHTML));
					//th_tag.innerHTML = ;
					th_tag.insert(th_hidden_field);
				}
				else if (counter == 3) {
					// connector input
					th_tag.setAttribute("data-type", "connector_input");
					th_hidden_field.setAttribute("value", "[{isys type='lang' ident='LC__CATG__CONNECTOR__INPUT'}]");
					th_tag.insert(new Element('span').insert($$('.cabling_table_cell_head')[counter2].down('span').innerHTML));
					//th_tag.innerHTML = "[{isys type='lang' ident='LC__CATG__CONNECTOR__INPUT'}]";
					th_tag.insert(th_hidden_field);
				}
				else if (counter == 4) {
					// cable
					th_tag.setAttribute("data-type", "cabling_cable");
					th_hidden_field.setAttribute("value", "[{isys type='lang' ident='LC__CMDB__OBJTYPE__CABLE'}]");
					th_tag.insert(new Element('span').insert($$('.cabling_table_cell_head')[counter2].down('span').innerHTML));
					//th_tag.innerHTML = "[{isys type='lang' ident='LC__CMDB__OBJTYPE__CABLE'}]";
					th_tag.insert(th_hidden_field);
				}
				else if (counter == 2) {
					// object
					th_tag.setAttribute("data-type", "cabling_object");
					th_hidden_field.setAttribute("value", "[{isys type='lang' ident='LC_UNIVERSAL__OBJECT'}]");
					th_tag.insert(new Element('span').insert($$('.cabling_table_cell_head')[counter2].down('span').innerHTML));
					th_tag.insert(new Element('img', {
						style:   'margin-left:10px;position:relative;top:3px;cursor:pointer;',
						src:     '[{$img_dir}]icons/silk/arrow_switch.png',
						onclick: 'Cabling.swap_columns(this);'
					}));
					//th_tag.innerHTML = "[{isys type='lang' ident='LC_UNIVERSAL__OBJECT'}]";
					th_tag.insert(th_hidden_field);
				}
				th_tag.setAttribute("data-column", columns);
				p_element.up().insertBefore(th_tag, $('add_button'));
				counter--;
				columns++;
				counter2++;
			}
		},

		swap_columns: function (img_element) {

			// Header
			var root = img_element.up();
			var previous_column = root.previous();
			var next_column = root.next();

			var root_data_column = parseInt(root.getAttribute('data-column'));
			var next_data_column = parseInt(next_column.getAttribute('data-column'));
			var previous_data_column = parseInt(previous_column.getAttribute('data-column'));
			var previous_name = '';
			var next_name = '';

			if (previous_column.getAttribute('data-type') == 'connector_input') {
				next_column_key = (root_data_column + 1);
				previous_column_key = (root_data_column - 1);
			}
			else {
				next_column_key = (root_data_column - 1);
				previous_column_key = (root_data_column + 1);
			}

			var next_clone = next_column.clone();
			next_clone.setAttribute('data-column', previous_data_column);
			next_clone.insert(previous_column.down('span').clone().insert(next_column.down('span').innerHTML));
			next_clone.insert(new Element('input', {
				type:  'hidden',
				value: next_column.down('span').innerHTML,
				name:  'csv_row[0][' + next_column_key + ']'
			}));

			var previous_clone = previous_column.clone();
			previous_clone.setAttribute('data-column', next_data_column);
			previous_clone.insert(next_column.down('span').clone().insert(previous_column.down('span').innerHTML));
			previous_clone.insert(new Element('input', {
				type:  'hidden',
				value: previous_column.down('span').innerHTML,
				name:  'csv_row[0][' + previous_column_key + ']'
			}));
			previous_column.replace(next_clone);
			next_column.replace(previous_clone);

			// Template
			var template_root = $('row_template').down().next('td', (root_data_column));
			var template_prev_column = template_root.previous();
			var template_next_column = template_root.next();

			var template_next_clone = template_next_column.clone();
			template_next_clone.setAttribute('data-column', previous_data_column);
			template_next_clone.insert(new Element('input', {
						type:      'text',
						className: 'inputText',
						size:      15,
						value:     '',
						name:      'csv_row[skip2][' + next_column_key + ']',
						id:        'row_skip2_' + previous_data_column
					})
			);

			var template_prev_clone = template_prev_column.clone();
			template_prev_clone.setAttribute('data-column', next_data_column);
			template_prev_clone.insert(new Element('input', {
				type:      'text',
				className: 'inputText',
				size:      15,
				value:     '',
				name:      'csv_row[skip2][' + previous_column_key + ']',
				id:        'row_skip2_' + next_data_column
			}));

			template_prev_column.replace(template_next_clone);
			template_next_column.replace(template_prev_clone);

			// Cabling
			var counter = 1;
			var previous_input_val = '';
			var next_input_val = '';

			$$('.import_row').each(function (ele) {
				var root_ele = ele.down().next('td', root_data_column);
				var previous_ele = root_ele.previous();
				previous_input_val = previous_ele.down('input').value;
				var next_ele = root_ele.next();
				next_input_val = next_ele.down('input').value;

				previous_input_val = previous_ele.down('input').value;
				next_input_val = next_ele.down('input').value;

				var next_ele_clone = next_ele.clone();
				next_ele_clone.setAttribute('data-column', previous_data_column);
				next_ele_clone.insert(new Element('input', {
					type:      'text',
					className: 'inputText',
					size:      15,
					id:        'row_' + counter + '_' + previous_data_column,
					name:      'csv_row[' + counter + '][' + next_column_key + ']',
					value:     previous_input_val
				}));

				var prev_ele_clone = previous_ele.clone();
				prev_ele_clone.setAttribute('data-column', next_data_column);
				prev_ele_clone.insert(new Element('input', {
					type:      'text',
					className: 'inputText',
					size:      15,
					id:        'row_' + counter + '_' + next_data_column,
					name:      'csv_row[' + counter + '][' + previous_column_key + ']',
					value:     next_input_val
				}));
				counter++;

				previous_ele.replace(next_ele_clone);
				next_ele.replace(prev_ele_clone);
			});
		}
	};

	new Tabs('tabs', {
		wrapperClass: 'browser-tabs',
		contentClass: 'browser-tab-content',
		tabClass:     'text-shadow'
	});
</script>
