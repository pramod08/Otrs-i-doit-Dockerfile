(function () {
	'use strict';

	var $select_profiles = $('profile_sbox'),
		$input_profile_name = $('csv-profile-title'),
		$button_profile_save = $('import-csv-save-profile'),
		$button_profile_load = $('import-csv-load-profile'),
		$button_profile_delete = $('import-csv-delete-profile'),
		$button_process_options = $('csv_import_process_options'),
		$code_csv_preview = $('csv-preview'),
		$input_csv_separator = $('csv_separator'),
		$table_csv_options = $('import-csv-options'),
		$table_csv_assignment = $('csv_assignment_table'),
		$div_assignment_modal = $('import-csv-assignment-modal'),
		$button_add_identificator = $('import-csv-add-identificator'),
		$div_identificators = $('identificators'),
		$div_identificators_tpl = $('identificators_hidden'),
		$button_start_import = $('import-start-button'),
		$div_result_container = $('import-result-container'),
		log_levels = '[{$log_levels}]'.evalJSON(),
		log_icons = '[{$log_icons}]'.evalJSON(),
		log_colors = '[{$log_colors}]'.evalJSON(),
		preselectedProfile = '[{$selected_profile}]',
		CSVMapper = new window.CSVMappingTable($table_csv_assignment, {ajaxUrl:'[{$csvmapping_ajax_url}]', $selectObjectType:$('object_type')});

	$select_profiles.on('profiles:reload', function (ev) {
		new Ajax.Request('[{$ajax_url_csvprofiles}]', {
			method: 'post',
			onSuccess: function (r) {
				var json = r.responseJSON, i;

				if (json && Object.isArray(json)) {
					$select_profiles.update();

					for (i in json) {
						if (json.hasOwnProperty(i)) {
							$select_profiles.insert(new Element('option', {value: json[i].id, 'data-profile': json[i].data}).update(json[i].title));
						}
					}
				}

				if (!! ev.memo.preselectProfile) {
					$select_profiles.fire('profiles:preselect', ev.memo);
				}
			}
		});
	});

	$button_profile_save.on('click', function () {
		var profileID = $select_profiles.getValue(),
			title = $input_profile_name.getValue();

		if (title.blank() && isNaN(profileID)) {
			return idoit.Notify.error('[{isys type="lang" ident="LC__MODULE__IMPORT__CSV__MSG__SAVE_MSG_EMPTY"}]');
		}

		var $identificators = $div_identificators.select('div'),
			profileData = {
				title: (title.blank() ? '' : title + ' (' + $F('csv_filename') + ')'),
				data: {
					globalObjectType: $F('object_type'),
					separator: $F('csv_separator'),
					headlines: $('csv_header').checked,
					singlevalueOverwriteEmptyValues: $table_csv_options.down('input[name="singlevalue_overwrite_empty_values"]:checked').getValue(),
					multivalue: $table_csv_options.down('input[name="multivalue"]:checked').getValue(),
					multivalueUpdateMode: $table_csv_options.down('input[name="multivalue_mode"]:checked').getValue(),
					assignments: {},
					identificationKeys: [],
					additionalPropertySearch: {}
				}
			};

		$table_csv_assignment.select('tr[data-index]').each(function ($tr) {
			var index = $tr.readAttribute('data-index'),
				$special_selection = $tr.down('.special-selection'),
				$object_type_selection = $tr.down('.object-type-assignment'),
				$create_object = $tr.down('.create-object');

			profileData.data.assignments[index] = {
				category: $F('cat_' + index),
				property: $F('prop_' + index)
			};

			if ($special_selection && !$special_selection.getValue().blank()) {
				try {
					profileData.data.additionalPropertySearch[index] = JSON.parse($special_selection.getValue());
				} catch (e) {
					idoit.Notify.warning(e, {life:7.5});
				}
			}

			if ($object_type_selection && !$object_type_selection.getValue().blank()) {
				profileData.data.assignments[index].object_type = $object_type_selection.getValue();
			}

			if ($create_object) {
				profileData.data.assignments[index].create_object = ($create_object.getValue() == "1");
			}
		});

		$identificators.each(function($div) {
			profileData.data.identificationKeys.push({
				csvIdent: $div.down('select').getValue(),
				localIdent: $div.down('select', 1).getValue()
			});
		});

		new Ajax.Request('?[{$smarty.const.C__GET__MODULE_ID}]=[{$smarty.const.C__MODULE__IMPORT}]&param=[{$smarty.const.C__IMPORT__GET__CSV}]&ajax=1&request=call_csv_handler&[{$smarty.const.C__CMDB__GET__CSV_AJAX}]=save_profile', {
			method: 'post',
			parameters: {
				profileData: JSON.stringify(profileData),
				profileID: profileID
			},
			onSuccess: function() {
				$input_profile_name.setValue('');

				$select_profiles.fire('profiles:reload', {selectLatest:true, preselectProfile:true});
			}
		});
	});

	$button_profile_load.on('click', function () {
		try {
			if (!$select_profiles.getValue() > 0) {
				throw 'You need to select a profile';
			}

			var options = $select_profiles.down('option:selected').readAttribute('data-profile').evalJSON();

			CSVMapper.setProfile(options).setOptions({
				callbackAfterRender: function () {
					var i, $div_identificator_container;

					if (options.identificationKeys.length > 0) {
						if (!$div_identificators.visible()) {
							$div_identificators.show();
						}

						// Here we add the identificators.
						for (i in options.identificationKeys) {
							if (options.identificationKeys.hasOwnProperty(i)) {
								$div_identificator_container = $div_identificators_tpl.down().clone(true);

								$div_identificator_container
									.down('select').setValue(options.identificationKeys[i].csvIdent)
									.next('select').setValue(options.identificationKeys[i].localIdent);

								$div_identificators.insert($div_identificator_container);
							}
						}

						$div_identificators.select('select').invoke('enable');
					}

					$select_profiles.enable();
					$button_profile_load.enable().down('img').writeAttribute('src', '[{$dir_images}]icons/silk/text_horizontalrule.png');
					$button_profile_delete.enable();

					CSVMapper.setProfile(null).setOptions({callbackAfterRender: Prototype.emptyFunction});
				}
			});

			$select_profiles.disable();
			$button_profile_load.disable().down('img').writeAttribute('src', '[{$dir_images}]ajax-loading.gif');
			$button_profile_delete.disable();
			$div_assignment_modal.show();

			// At first we set the "options", so we can trigger the mapping process.
			$('object_type').setValue(options.globalObjectType || -1).fire('chosen:updated');
			$('csv_separator').setValue(options.separator || ';').simulate('change');
			$('csv_header').setValue(options.headlines ? 'on' : '');
			$('multivalue_' + (options.multivalue || 'column')).setValue('on');
			$table_csv_options.down('input[name="multivalue_mode"][value="' + (options.multivalueUpdateMode || 'm_untouched') + '"]').setValue('on');
			$table_csv_options.down('input[name="singlevalue_overwrite_empty_values"][value="' + (options.singlevalueOverwriteEmptyValues || '1') + '"]').setValue('on');

			$button_process_options.simulate('click');
		} catch (e) {
			idoit.Notify.error('[{isys type="lang" ident="LC__MODULE__IMPORT__CSV__MSG__LOAD_FAIL"}]<br />' + e);
		}
	});

	$button_profile_delete.on('click', function () {
		new Ajax.Request('[{$csvmapping_ajax_url}]&func=delete_profile', {
			method: 'post',
			parameters: {
				profileID: $select_profiles.getValue()
			},
			onComplete: function() {
				// Notification will be sent by the ajax handler.
				$select_profiles.fire('profiles:reload');
			}
		});
	});

	$button_add_identificator.on('click', function () {
		if (!$div_identificators.visible()) {
			$div_identificators.show();
		}

		$div_identificators.insert($div_identificators_tpl.innerHTML).select('select').invoke('enable');
	});

	$div_identificators.on('click', 'button', function (ev) {
		ev.findElement('button').up('div').remove();

		if (! $div_identificators.down('div')) {
			$('identificators').hide();
		}
	});

	$button_process_options.on('click', function () {
		var url = document.location.href,
			parameters = {
				object_type: $F('object_type'),
				csv_filename: $F('csv_filename'),
				csv_separator: $F('csv_separator'),
				csv_header: $F('csv_header'),
				multivalue: ($('multivalue_row').checked ? 'row' : 'column')
			};

		url = url.parseQuery();

		if (url.hasOwnProperty('profile')) {
			delete url.profile;
		}

		if (url.hasOwnProperty('file')) {
			delete url.file;
		}

		// Display the modal while loading...
		$div_assignment_modal.show();

		$button_process_options.disable()
			.down('img').writeAttribute('src', '[{$dir_images}]ajax-loading.gif')
			.next('span').update('[{isys type="lang" ident="LC__UNIVERSAL__LOADING"}]');

		new Ajax.Request('?' + Object.toQueryString(url), {
			method: 'post',
			parameters: parameters,
			onComplete: function (r) {
				var json = r.responseJSON,
					$select_column_identificator = $div_identificators_tpl.down('select'),
					i;

				$button_process_options.enable()
					.down('img').writeAttribute('src', '[{$dir_images}]icons/silk/arrow_down.png')
					.next('span').update('[{isys type="lang" ident="LC__MODULE__IMPORT__CSV__PROCESS_OPTIONS"}]');

				CSVMapper.setHeader($('csv_header').checked);

				try {
					if (json.success) {
						// Select all direct children that are not an <ul> element.
						$div_identificators.select('div').invoke('remove');

						// Empty the identificator select.
						$select_column_identificator.update();

						// And fill the identificator select with current values.
						for (i in json.data.csv_first_line) {
							if (json.data.csv_first_line.hasOwnProperty(i)) {
								$select_column_identificator.insert(
									new Element('option', {value: i})
										.update(json.data.csv_first_line[i])
								);
							}
						}

						// Trigger the CSV Mapper!
						CSVMapper.setData(json.data).render();

						// Hide the modal.
						$div_assignment_modal.hide();
					} else {
						idoit.Notify.error(json.message);
					}
				} catch (e) {
					idoit.Notify.error(e);
				}
			}
		});
	});

	$button_start_import.on('click', function () {
		var title_property, object_type_property, special_title = false;

		title_property = !!$table_csv_assignment.select('.category-selection').filter(function ($select) {
			return $select.getValue() == 'object_title';
		}).length;

		object_type_property = ($F('object_type') != "-1" || $table_csv_assignment.select('.category-selection').filter(function ($select) {
			return $select.getValue() == 'object_type_dynamic';
		}).length > 0);

		// We have no "title" property - so we'll check if the user has assigned a specific person, persongroup or organization category + property.
		if (! title_property) {
			var categories = $table_csv_assignment.select('.category-selection').invoke('getValue'),
				properties = $table_csv_assignment.select('.property-selection').invoke('getValue'),
				cat_indexes,
				i;

			// Check for selected "C__CATS__PERSON" categories.
			cat_indexes = categories.map(function(val, i) { return (val === 'C__CATS__PERSON' ? i : null); }).compact();
			if (cat_indexes.length > 0) {
				for (i in cat_indexes) {
					if (cat_indexes.hasOwnProperty(i) && (properties[cat_indexes[i]] === 'first_name' || properties[cat_indexes[i]] === 'last_name')) {
						if (special_title === false) {
							special_title = [];
						}
						// We found a first- or last-name property for the person category.
						special_title.push(cat_indexes[i]);
					}
				}
			}

			// Check for selected "C__OBJTYPE__PERSON_GROUP" categories.
			cat_indexes = categories.map(function(val, i) { return (val === 'C__OBJTYPE__PERSON_GROUP' ? i : null); }).compact();
			if (cat_indexes.length > 0) {
				for (i in cat_indexes) {
					if (cat_indexes.hasOwnProperty(i) && properties[cat_indexes[i]] === 'title') {
						if (special_title === false) {
							special_title = [];
						}
						// We found a title property for the persongroup category.
						special_title.push(cat_indexes[i]);
					}
				}
			}

			// Check for selected "C__CATS__ORGANIZATION" categories.
			cat_indexes = categories.map(function(val, i) { return (val === 'C__CATS__ORGANIZATION' ? i : null); }).compact();
			if (cat_indexes.length > 0) {
				for (i in cat_indexes) {
					if (cat_indexes.hasOwnProperty(i) && properties[cat_indexes[i]] === 'title') {
						if (special_title === false) {
							special_title = [];
						}
						// We found a title property for the organization category.
						special_title.push(cat_indexes[i]);
					}
				}
			}

			if (special_title === false) {
				// The title property is missing and none of the above checks have worked.
				idoit.Notify.error('[{isys type="lang" ident="LC__UNIVERSAL__CSV_IMPORT_NO_OBJECT_TITLE" p_bHtmlEncode="1"}]');
				return;
			}
		}

		if (! object_type_property) {
			idoit.Notify.error('[{isys type="lang" ident="LC__UNIVERSAL__CSV_IMPORT_NO_OBJECT_TYPE" p_bHtmlEncode="1"}]');
			return;
		}

		$button_start_import.disable()
			.down('img').writeAttribute('src', '[{$dir_images}]ajax-loading.gif')
			.next('span').update('[{isys type="lang" ident="LC__UNIVERSAL__LOADING"}]');

		$div_result_container
			.update(new Element('input', {name:'import', type:'hidden', value:1}));

		$table_csv_assignment.select('tbody tr[data-index]').each(function($tr) {
			var index = $tr.readAttribute('data-index'),
				$select_category = $('cat_' + index),
				$select_property = $('prop_' + index),
				$input_obj_type_assignment = $('object_type_assignment_' + index),
				$input_object_creation = $('object_creation_' + index),
				cat_value = $select_category.getValue(),
				prop_value = $select_property.getValue(),
				multivalue = $table_csv_options.down('[name="multivalue"]:checked').getValue();

			if (cat_value === cat_value.toUpperCase() && cat_value != 0) {
				if (multivalue != 'column') {
					$div_result_container
						.insert(new Element('input', {name:'assignment[' + cat_value + '][' + prop_value + ']', type:'hidden', value:index}));
				} else {
					$div_result_container
						.insert(new Element('input', {name:'assignment[' + index + '][category]', type:'hidden', value:cat_value}))
						.insert(new Element('input', {name:'assignment[' + index + '][property]', type:'hidden', value:prop_value}));
				}

				if ($input_obj_type_assignment && $input_object_creation) {
					$div_result_container
						.insert(new Element('input', {name: 'obj_type_assignment[' + index + '][object-type]', type: 'hidden', value: $input_obj_type_assignment.getValue()}))
						.insert(new Element('input', {name: 'obj_type_assignment[' + index + '][create-object]', type: 'hidden', value: $input_object_creation.getValue()}));
				}
			} else if (cat_value == 'separator') {
				$div_result_container.insert(new Element('input', {name:'assignment[' + index + '][category]', type:'hidden', value:'separator'}));
			} else {
				$div_result_container.insert(new Element('input', {name:cat_value, type:'hidden', value:index}));
			}
		});

		if (special_title !== false) {
			$div_result_container.insert(new Element('input', {name:'special-title', type:'hidden', value:special_title.join()}))
		}

		$div_result_container.removeClassName('error').addClassName('info').addClassName('p5')
			.insert(new Element('img', {src:'[{$dir_images}]icons/silk/information.png', className:'vam mr5'}))
			.insert(new Element('span').update('[{isys type="lang" ident="LC__UNIVERSAL__IMPORT_IN_PROGRESS" p_bHtmlEncode=true}]'));

		new Ajax.Request('[{$url_ajax_import}]', {
			method: 'post',
			parameters: $('isys_form').serialize().parseQuery(),
			onComplete: function (r) {
				var json = r.responseJSON,
					$div_log_filter = new Element('div', {className:'fr'}),
					$ul_log_filter_list = new Element('ul', {className:'list-style-none right m0 mr5'}),
					item, i;

				$button_start_import.enable()
					.down('img').writeAttribute('src', '[{$dir_images}]icons/silk/database_copy.png')
					.next('span').update('[{isys type="lang" ident="LC__UNIVERSAL__IMPORT"}]');

				if (json === null || json === undefined) {
					idoit.Notify.error('[{isys type="lang" ident="LC__EXCEPTION__GENERAL"}]!');

					$div_result_container.removeClassName('info').addClassName('error')
						.update(new Element('h3', {className:'mb10'}).update('[{isys type="lang" ident="LC__UNIVERSAL__ERROR"}]: ' + r.responseText));
					return;
				}

				if (json.success) {
					for (i in log_icons) {
						if (log_icons.hasOwnProperty(i)) {
							$ul_log_filter_list
								.insert(new Element('li')
									.update(new Element('label', {className:log_colors[i]})
										.update(new Element('img', {src:log_icons[i], className:'mr5'}))
										.insert(new Element('span', {className:'mr5'}).update(log_levels[i]))
										.insert(new Element('input', {type:'checkbox', value:i}).setValue(1))));
						}
					}

					$ul_log_filter_list.on('change', 'input', function (ev) {
						var $checkbox = ev.findElement('input'),
							level = $checkbox.readAttribute('value');

						$div_result_container.select('div[data-level="' + level + '"]').invoke($checkbox.checked ? 'show' : 'hide');
					});

					// Add the imported objects.
					$div_result_container.removeClassName('info').removeClassName('error').addClassName('box')
						.update(new Element('h3', {className:'mb10'}).update('[{isys type="lang" ident="LC__MODULE__IMPORT__CSV__IMPORTED_OBJECTS"}] (' + Object.keys(json.data.csv_objects).length + ')'));

					for (i in json.data.csv_objects) {
						if (json.data.csv_objects.hasOwnProperty(i)) {
							item = json.data.csv_objects[i];

							$div_result_container
								.insert(new Element('div')
									.update(new Element('a', {href: '?[{$smarty.const.C__CMDB__GET__OBJECT}]=' + item.id, id: 'csv-import-result-obj-' + item.id, className: 'quickinfo-link', 'data-obj-id': item.id})
										.update(new Element('img', {src: '[{$dir_images}]icons/silk/link.png', className: 'vam mr5'}))
										.insert(new Element('span').update(item.type + ' > ' + item.title))
										.insert(new Element('span', {className:'ml5 grey'}).update('(#' + item.id + ')'))));

							new Tip('csv-import-result-obj-' + item.id, '', {ajax: {url: '?ajax=1&call=quick_info&objID=' + item.id}, delay: '0.5', stem: 'topLeft', style: 'default', className: 'objectinfo'});
						}
					}

					// Add the log.
					$div_result_container
						.insert($div_log_filter.update($ul_log_filter_list))
						.insert(new Element('h3', {className:'mt10 mb10'}).update('[{isys type="lang" ident="LC__MODULE__IMPORT__CSV__LOG"}]'));

					for (i in json.data.csv_log) {
						if (json.data.csv_log.hasOwnProperty(i)) {
							item = json.data.csv_log[i];

							$div_result_container
								.insert(new Element('div', {'data-level': item.level})
									.update(new Element('img', {src: (log_icons[item.level] || '[{$dir_images}]empty.gif'), className: 'vam mr5', width: '16px', height: '16px'}))
									.insert(new Element('span', {className: log_colors[item.level]}).update(log_levels[item.level] + ': ' + item.formatted)));
						}
					}

					$div_result_container.insert(new Element('br', {className:'cb'}));
				} else {
					idoit.Notify.error(json.message);

					$div_result_container.removeClassName('info').addClassName('error')
						.update(new Element('h3', {className:'mb10'}).update('[{isys type="lang" ident="LC__UNIVERSAL__ERROR"}]: ' + json.message));
				}
			}
		});
	});

	// Update the "preview" if the separation is changed.
	$input_csv_separator.on('change', function () {
		var separator = $input_csv_separator.getValue();

		// Do not use ".update()" because this would render any given HTML.
		$code_csv_preview.textContent = '"Wert"' + separator + '"Wert2"' + separator + '"..."';
	});

	$div_identificators_tpl.select('select').invoke('disable');

	// Reload the profiles and preselect if necessary.
	if (preselectedProfile > 0) {
		// Load the profiles on startup.
		$select_profiles.fire('profiles:reload', {preselectProfile:true, preselection:preselectedProfile, simulateClick:true});
	} else {
		$select_profiles.fire('profiles:reload');
	}

	// Only reload the mapping if the upper options have changed (the radio boxes at the bottom are fine).
	$table_csv_options.on('change', 'input:not([type="radio"]),select', function () {
		$div_assignment_modal.show();
	});

	// This will add the "separator" selection "on-the-fly" when selecting differend multivalue options.
	$('multivalue_column', 'multivalue_row', 'multivalue_comma').invoke('on', 'change', function(ev){
		CSVMapper.setMultivalueMode(ev.findElement('input').readAttribute('value'));
	});

	// Set the contentArea to overflow: auto.
	$('contentArea').setStyle({overflow: 'auto'});
})();