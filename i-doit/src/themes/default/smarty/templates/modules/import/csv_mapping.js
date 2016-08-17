window.CSVMappingTable = Class.create({
	$table: null,
	$tbody: null,
	$panel: null,
	profile: null,
	// We include these options static, because they are only used once and we can save a lot of unnecessary AJAX calls.
	specialAssignmentData: {
		'{"select":"isys_obj__id","search":"isys_obj__title","table":"isys_obj"}': '[{isys type="lang" ident="LC__UNIVERSAL__OBJECT_TITLE"}]',
		'{"select":"isys_obj__id","search":"isys_obj__id","table":"isys_obj"}': '[{isys type="lang" ident="LC__AUTH_GUI__OBJ_ID_CONDITION"}]',
		'{"select":"isys_catg_accounting_list__isys_obj__id","search":"isys_catg_accounting_list__inventory_no","table":"isys_catg_accounting_list"}': '[{isys type="lang" ident="LC__CMDB__CATG__ACCOUNTING_INVENTORY_NO"}]',
		'{"select":"isys_cats_room_list__isys_obj__id","search":"isys_cats_room_list__number","table":"isys_cats_room_list"}': '[{isys type="lang" ident="LC__CMDB__CATS__ROOM_NUMBER"}]'
	},

	initialize: function ($table, options) {
		var $select_object_type,
			$select_panel_attribute_selection = new Element('select', {id: 'panel-selection', name: 'panel-selection', className: 'input chosen-select'});
		this.$table = $table;
		this.$tbody = this.$table.down('tbody');

		if (options.hasOwnProperty('$selectObjectType') && Object.isElement(options.$selectObjectType)) {
			$select_object_type = options.$selectObjectType.clone(true);
			delete options.$selectObjectType;

			// After we cloned the object type select, we need to change name and ID.
			$select_object_type.writeAttribute({
				id: 'panel-object-type-assignment',
				name: 'panel-object-type-assignment',
				className: 'input input-small',
				style: null
			});

			$select_object_type.down('option[value="-1"]').update('[{isys type="lang" ident="LC__UNIVERSAL__CSV_REFERENCE_OBJ_OF_TYPE_AUTOMATIC"}]');
		} else {
			$select_object_type = new Element('select', {id:'panel-object-type-assignment', name:'panel-object-type-assignment', className:'input input-small'});
		}

		this.options = {
			headline: true,
			multivalue: 'row',
			ajaxUrl: '?',
			profileTrigger: false,
			callbackBeforeRender: Prototype.emptyFunction,
			callbackAfterRender: Prototype.emptyFunction
		};

		Object.extend(this.options, options || {});

		this.$panel = new Element('tr', {className: 'active'})
			.update(new Element('td', {colspan: 2}))
			.insert(new Element('td')
				.update($select_panel_attribute_selection)
				.insert(new Element('div', {className: 'hide mt5 mb5 object-type-assignment'})
					.update(new Element('label', {for:'panel-object-type-assignment'}).update('[{isys type="lang" ident="LC__UNIVERSAL__CSV_REFERENCE_OBJ_OF_TYPE"}]'))
					.insert($select_object_type)
					.insert(new Element('label', {style: 'width:560px;'})
						.update('[{isys type="lang" ident="LC__UNIVERSAL__CSV_CREATE_OBJ_OF_TYPE_IF_NECESSARY_LABEL"}]')
						.insert(new Element('input', {type:'checkbox', id:'panel-create-object', name:'panel-create-object', className:'mt5 fr create-object'}))))
				.insert(new Element('div', {className: 'hide mt5 mb5 special-assignment'})
					.update(new Element('label', {for: 'panel-selection-extra'}).update('[{isys type="lang" ident="LC__MODULE__IMPORT__CSV__SPECIAL_ASSIGNMENT"}]'))
					.insert(new Element('select', {id: 'panel-selection-extra', name: 'panel-selection-extra', className: 'input input-small'})))
				.insert(new Element('div', {className: 'mt5 cb'})
					.update(new Element('button', {type: 'button', className: 'btn mr5', 'data-panelaction': 'apply'})
						.update(new Element('img', {src: '[{$dir_images}]icons/silk/tick.png', className: 'mr5'}))
						.insert(new Element('span').update('[{isys type="lang" ident="LC_UNIVERSAL__ACCEPT" p_bHtmlEncode=false}]')))
					.insert(new Element('button', {type: 'button', className: 'btn', 'data-panelaction': 'cancel'})
						.update(new Element('img', {src: '[{$dir_images}]icons/silk/cross.png', className: 'mr5'}))
						.insert(new Element('span').update('[{isys type="lang" ident="LC_UNIVERSAL__ABORT" p_bHtmlEncode=false}]')))));

		// Add chosen fields. We can't use the "$('xyz')" function because these Elements are not yet implemented in the DOM.
        new Chosen($select_object_type, {
            width:           '280px',
            search_contains: true
        });

        new Chosen($select_panel_attribute_selection, {
            width:           '560px',
            search_contains: true
        });

		$select_panel_attribute_selection.on('chosen:showing_dropdown', function () {
			$select_panel_attribute_selection.store('chosen', 'open');
		});

		$select_panel_attribute_selection.on('chosen:hiding_dropdown', function () {
			$select_panel_attribute_selection.store('chosen', 'closed');
		});

		this.$panel.on('keydown', function (ev) {
			// Trigger this, when we select a value by hitting enter - and then hitting enter again.
			if ($select_panel_attribute_selection.retrieve('chosen') == 'closed' && ev.key == 'Enter' && ev.target.match('input') && ev.target.up('div.chosen-search')) {
				this.$tbody.down('button[data-panelaction="apply"]').simulate('click');
			}
		}.bind(this));

		this.data = {
			csv_first_line: [],
			csv_second_line: [],
			categories: {}
		};

		this.resetObserver();
	},

	setOptions: function(options) {
		Object.extend(this.options, options || {});

		return this;
	},

	setData: function (data) {
		this.data = data;

		this.prepareData();

		return this;
	},

	setMultivalueMode: function (mode) {
		var $select = this.$panel.down('select'),
			$optgroup = $select.down('optgroup[label="[{isys type="lang" ident="LC__UNIVERSAL__EXTRAS"}]"]'),
			$option_separator = $select.down('[value="separator"]');

		if (mode === 'column') {
			if (! $option_separator) {
				if (! $optgroup) {
					$optgroup = new Element('optgroup', {label:'[{isys type="lang" ident="LC__UNIVERSAL__EXTRAS"}]'});
					$select.insert({top: $optgroup});
				}

				$optgroup.insert({bottom:new Element('option', {value:'separator'}).update('Separator')});
			}
		} else {
			if ($option_separator) {
				$option_separator.remove();
			}
		}

		$select.fire('chosen:updated');
	},

	prepareData: function () {
		var $select = this.$panel.down('select').update(),
			$optgroup,
			i, i2;

		for (i in this.data.categories) {
			if (this.data.categories.hasOwnProperty(i)) {
				$optgroup = new Element('optgroup', {label:i});

				for (i2 in this.data.categories[i]) {
					if (this.data.categories[i].hasOwnProperty(i2)) {
						$optgroup.insert(new Element('option', {value:i2}).update(this.data.categories[i][i2]))
					}
				}

				$select.insert($optgroup);
			}
		}

		$select.fire('chosen:updated');

		return this;
	},

	setProfile: function (profile) {
		this.profile = profile;

		this.options.profileTrigger = (profile !== null);

		return this;
	},

	setHeader: function (header) {
		if (header) {
			this.$table
				.down('th').update('[{isys type="lang" ident="LC__UNIVERSAL__CSV_HEADER"}]')
				.next().update('[{isys type="lang" ident="LC__UNIVERSAL__FIRST_LINE"}]');
		} else {
			this.$table
				.down('th').update('[{isys type="lang" ident="LC__UNIVERSAL__FIRST_LINE"}]')
				.next().update('[{isys type="lang" ident="LC__UNIVERSAL__SECOND_LINE"}]');
		}

		return this;
	},

	resetObserver: function () {
		this.$tbody.stopObserving();
		this.$tbody.on('click', 'button[data-action="edit-assignment"]', this.actionEditAssignment.bind(this));
		this.$tbody.on('click', 'button[data-action="reset-assignment"]', this.actionResetAssignment.bind(this));
		this.$tbody.on('click', 'button[data-panelaction="apply"]', this.actionApplyPanelOptions.bind(this));
		this.$tbody.on('click', 'button[data-panelaction="cancel"]', this.actionCancelPanelOptions.bind(this));
		this.$panel.on('change', 'select.chosen-select', this.checkSpecialAssignments.bind(this));

		return this;
	},

	actionEditAssignment: function (ev) {
		var $button = ev.findElement('button'),
			$tr = $button.up('tr'),
			$select_panel_selection = this.$panel.down('select.chosen-select'),
			selected_category = $tr.down('.category-selection').getValue(),
			selected_property = $tr.down('.property-selection').getValue(),
			value = selected_category + (! selected_property.blank() ? '::' + selected_property : '');

		this.$tbody.select('[data-action]').invoke('enable');
		this.$tbody.select('tr.active').invoke('removeClassName', 'active');

		$tr.addClassName('active').select('button').invoke('disable');

		this.$panel.addClassName('active').writeAttribute('data-row-index', $tr.readAttribute('data-index'))
			.down('.object-type-assignment').addClassName('hide')
			.next('.special-assignment').addClassName('hide');

		$select_panel_selection.setValue(value.blank() ? '-' : value).fire('chosen:updated');
		$select_panel_selection.simulate('change');

		$tr.insert({after: this.$panel});

		$select_panel_selection.fire('chosen:open');
	},

	actionApplyPanelOptions: function (ev) {
		var $tr = this.$tbody.down('tr[data-index="' + this.$panel.readAttribute('data-row-index') + '"]'),
			$select_panel = this.$panel.down('select'),
			value = $select_panel.getValue().split('::'),
			$special_selection = $('panel-selection-extra'),
			create_obj_if_necessary = this.$panel.down('.create-object').checked,
			$obj_type_selection = $('panel-object-type-assignment');

		$tr.highlight()
			.down('.attribute-box').update($select_panel.down(':selected').innerHTML)
			.next().enable() // Edit button.
			.next().enable() // Reset button.
			.next().down('input').setValue(value[0]) // "Category" input.
			.next().setValue(value.length > 1 ? value[1] : ''); // "Property" input.

		if ($tr.down('.special-selection')) {
			$tr.down('.attribute-box').insert(' via <strong>' + ($special_selection.down(':selected').innerHTML) + '</strong>');
			$tr.down('.special-selection').setValue($special_selection.getValue())
		}

		if ($tr.down('.object-type-assignment') && $tr.down('.create-object')) {
			$tr.down('.object-type-assignment').setValue($obj_type_selection.getValue());
			$tr.down('.create-object').setValue(create_obj_if_necessary ? '1' : '0');

			$tr.down('.attribute-box')
				.insert('. [{isys type="lang" ident="LC__UNIVERSAL__CSV_OBJ_TYPE_ASSIGNMENT"}]: ')
				.insert(new Element('strong').update($obj_type_selection.down(':selected').innerHTML));

			if (create_obj_if_necessary) {
				$tr.down('.attribute-box').insert(', [{isys type="lang" ident="LC__UNIVERSAL__CSV_CREATE_OBJ_OF_TYPE_IF_NECESSARY"}]');
			}
		}

		this.$panel.writeAttribute('data-row-index', null).remove();

		this.$tbody.select('tr.active').invoke('removeClassName', 'active');
	},

	actionCancelPanelOptions: function (ev) {
		this.$panel.writeAttribute('data-row-index', null).remove();

		this.$tbody.select('tr.active').invoke('removeClassName', 'active');
		this.$tbody.select('[data-action]').invoke('enable');
	},

	actionResetAssignment: function (ev) {
		var $tr = ev.findElement('button').up('tr');

		$tr.select('input').invoke('setValue', '');
		$tr.down('.attribute-box').update(new Element('span', {className:'grey'}).update('[{isys type="lang" ident="LC__UNIVERSAL__CSV_NO_ASSIGNMENT" p_bHtmlEncode=false}]'));
	},

	render: function () {
		var i,
			$input_category_tpl = new Element('input', {className:'category-selection'}),
			$input_property_tpl = new Element('input', {className:'property-selection'});

		this.options.callbackBeforeRender();

		// Remove the panel, if it's assigned to prevent all (chosen-) observer to be removed.
		if (this.$panel.up()) {
			this.$panel.remove();
		}

		this.$tbody.update();

		for (i in this.data.csv_first_line) {
			if (this.data.csv_first_line.hasOwnProperty(i)) {
				this.$tbody.insert(
					new Element('tr', {'data-index': i})
						.update(new Element('td').update(this.data.csv_first_line[i]))
						.insert(new Element('td', {className:'grey'}).update(this.data.csv_second_line[i]))
						.insert(new Element('td')
							.insert(new Element('div', {className:'fl attribute-box mr5'})
								.update(new Element('span', {className:'grey'}).update('[{isys type="lang" ident="LC__UNIVERSAL__CSV_NO_ASSIGNMENT"}]')))
							.insert(new Element('button', {type:'button', className:'btn fl mr5', 'data-action':'edit-assignment', title:'[{isys type="lang" ident="LC__UNIVERSAL__EDIT" p_bHtmlEncode=false}]'})
								.update(new Element('img', {src:'[{$dir_images}]icons/silk/pencil.png'})))
							.insert(new Element('button', {type:'button', className:'btn fl', 'data-action':'reset-assignment', title:'[{isys type="lang" ident="LC__UNIVERSAL__RESET" p_bHtmlEncode=false}]'})
								.update(new Element('img', {src:'[{$dir_images}]icons/silk/detach.png'})))
							.insert(new Element('div', {className:'hide'})
								// Insert a invisible DIV with the "old" controls, so don't need to change everything.
								.update($input_category_tpl.writeAttribute('name', 'cat_' + i).writeAttribute('id', 'cat_' + i).clone(true))
								.insert($input_property_tpl.writeAttribute('name', 'prop_' + i).writeAttribute('id', 'prop_' + i).clone(true))
						)));
			}
		}

		if (this.options.profileTrigger) {
			this.preselection();
		} else {
			this.options.callbackAfterRender();
		}

		return this;
	},

	findAttributeInData: function (key) {
		var i;

		if (key.blank()) {
			return false;
		}

		for (i in this.data.categories) {
			if (this.data.categories.hasOwnProperty(i)) {
				if (this.data.categories[i].hasOwnProperty(key)) {
					return this.data.categories[i][key];
				}
			}
		}

		return false;
	},

	preselection: function () {
		var i, $tr, value, $input_cat, $input_prop;

		for (i in this.profile.assignments) {
			if (this.profile.assignments.hasOwnProperty(i)) {
				$tr = this.$tbody.down('tr[data-index="' + i + '"]');
				$input_cat = $tr.down('.category-selection');
				$input_prop = $tr.down('.property-selection');
				value = this.findAttributeInData(this.profile.assignments[i].category + (this.profile.assignments[i].hasOwnProperty('property') && this.profile.assignments[i].property !== null && !this.profile.assignments[i].property.blank() ? '::' + this.profile.assignments[i].property : ''));

				if (value !== false) {
					$tr.down('.attribute-box').update(value);
					$input_cat.setValue(this.profile.assignments[i].category);
					$input_prop.setValue(this.profile.assignments[i].property);

					// Add the special assignment, if existent.
					if (this.profile.additionalPropertySearch.hasOwnProperty(i)) {
						try {
							value = Object.toJSON(this.profile.additionalPropertySearch[i]);

							$tr.down('div.hide').insert(new Element('input', {name: 'prop_search[' + this.profile.assignments[i].category + '_' + i + ']', className: 'special-selection', value: value}));

							if (this.profile.assignments[i].category == 'C__CATG__LOCATION' && this.profile.assignments[i].property == 'parent') {
								if (this.specialAssignmentData.hasOwnProperty(value)) {
									$tr.down('.attribute-box').insert(' via <strong>' + this.specialAssignmentData[value] + '</strong>');
								}
							}
						} catch (e) {
							idoit.Notify.warning(e, {life:7.5});
						}
					}

					if (this.profile.assignments[i].hasOwnProperty('object_type') && this.profile.assignments[i].hasOwnProperty('create_object')) {
						value = this.$panel.down('.object-type-assignment option[value="' + this.profile.assignments[i].object_type + '"]');

						$tr.down('div.hide')
							.insert(new Element('input', {id: 'object_type_assignment_' + i,name: 'object_type_assignment_' + i, className: 'object-type-assignment', value: value.readAttribute('value')}))
							.insert(new Element('input', {id: 'object_creation_' + i, name: 'object_creation_' + i, className: 'create-object', value: (this.profile.assignments[i].create_object ? '1' : '0')}));

						if (value) {
							$tr.down('.attribute-box')
								.insert('. [{isys type="lang" ident="LC__UNIVERSAL__CSV_OBJ_TYPE_ASSIGNMENT"}]: ')
								.insert(new Element('strong').update(value.innerHTML));
						}

						if (this.profile.assignments[i].create_object) {
							$tr.down('.attribute-box').insert(', [{isys type="lang" ident="LC__UNIVERSAL__CSV_CREATE_OBJ_OF_TYPE_IF_NECESSARY"}]');
						}
					}
				}
			}
		}

		this.options.callbackAfterRender();

		return this;
	},

	checkSpecialAssignments: function (ev) {
		var $select = ev.findElement('select'),
			index = $select.up('tr').readAttribute('data-row-index'),
			value = $select.getValue(),
			$div_selection = $select.up('td').down('.special-assignment').addClassName('hide'),
			$select_panel_extra = $div_selection.down('select'),
			$div_destination = this.$tbody.down('tr[data-index="' + index + '"] div.hide'),
			preselectionValue, i;

		// This is our special assignment #1.
		if (value === 'C__CATG__LOCATION::parent') {
			$div_selection.removeClassName('hide');

			$select_panel_extra.update();

			for (i in this.specialAssignmentData) {
				if (this.specialAssignmentData.hasOwnProperty(i)) {
					$select_panel_extra.insert(new Element('option', {value: i}).update(this.specialAssignmentData[i]))
				}
			}

			if (!$div_destination.down('.special-selection')) {
				$div_destination.insert(new Element('input', {name: 'prop_search[C__CATG__LOCATION_' + index + ']', className: 'special-selection'}));
			} else {
				preselectionValue = $div_destination.down('.special-selection').getValue();
			}

			if (preselectionValue !== null) {
				$select_panel_extra.setValue(preselectionValue);
			}
		} else {
			$select_panel_extra.update();

			if ($div_destination.down('.special-selection')) {
				$div_destination.down('.special-selection').remove()
			}
		}

		if (! $select.next('img')) {
			$div_selection.insert({before:new Element('img', {className:'ml5 vam', src:'[{$dir_images}]ajax-loading.gif'})});
		}

		value = value.split('::');

		new Ajax.Request(this.options.ajaxUrl + '&func=load_special_assignment', {
			method: 'post',
			parameters: {
				categoryConst: value[0],
				propertyKey: value[1]
			},
			onComplete: function (r) {
				var json = r.responseJSON,
					$select_obj_type = $('panel-object-type-assignment'),
					$options_obj_types = $select_obj_type.select('option').invoke('writeAttribute', 'disabled', null),
					preselectionValue = -1;

				if ($select.next('img')) {
					$select.next('img').remove();
				}

				if (json.success) {
					if (json.data.hasOwnProperty('connection')) {
						this.$panel.down('.object-type-assignment').removeClassName('hide');

						if (!$div_destination.down('.create-object')) {
							$div_destination.insert(new Element('input', {id: 'object_creation_' + index, name: 'object_creation_' + index, className: 'create-object'}));
						} else {
							preselectionValue = $div_destination.down('.create-object').getValue();
						}

						$('panel-create-object').setValue((preselectionValue == "1") ? '1' : '');
						preselectionValue = -1;

						if (!$div_destination.down('.object-type-assignment')) {
							$div_destination.insert(new Element('input', {id: 'object_type_assignment_' + index, name: 'object_type_assignment_' + index, className: 'object-type-assignment'}));
						} else {
							preselectionValue = $div_destination.down('.object-type-assignment').getValue();
						}

						// Optionally filter the object types, which were not whitelisted.
						if (Object.isArray(json.data.obj_type_whitelist) && json.data.obj_type_whitelist.length) {
							$options_obj_types
								.filter(function($option) {
									var value = $option.readAttribute('value');

									return ! (value === -1 || json.data.obj_type_whitelist.indexOf(value) > -1);
								})
								.invoke('writeAttribute', 'disabled', 'disabled');
						}
					}
				} else {
					idoit.Notify.error(json.message);
				}

				$select_obj_type.setValue(preselectionValue).fire('chosen:updated');
			}.bind(this)
		});

		return this;
	}
});