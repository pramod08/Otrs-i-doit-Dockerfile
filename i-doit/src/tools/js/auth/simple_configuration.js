var SimpleAuthConfiguration = Class.create({
	/**
	 * Constructor method, initializes everything necessary.
	 * @param  element
	 * @param  options
	 */
	initialize: function (element, options) {
		this.element_id = element;
		this.element = $(element);

		this.path_counter = 1;

		this.options = Object.extend({
			rights: {},
			modules: {},
			paths: {},
			inherited_paths: {},
			edit_mode: 0
		}, options || {});

		var thead = new Element('thead', {className:'gradient'});

		// Insert all "right"-checkboxes.
		for (var i in this.options.rights) {
			if (this.options.rights.hasOwnProperty(i)) {
				thead.insert(new Element('th', {style:'width:25px;'}).update(
					new Element('img', {src:window.dir_images + this.options.rights[i].icon, alt:this.options.rights[i].title, title:this.options.rights[i].title})
				));
			}
		}

		// Insert the "condition", "parameter" and "action" fields.
		thead.insert(
				new Element('th').update(idoit.Translate.get('LC__AUTH_GUI__AUTH_MODULES'))
			).insert(
				new Element('th', {style:'width:50px;'}).update(idoit.Translate.get('LC__AUTH_GUI__ACTION'))
			);

		this.element.update(
			new Element('table', {cellspacing:0, style:'width:100%;'}).update(
				thead
			).insert(
				new Element('tbody')
			)
		);

		 for (i in this.options.inherited_paths) {
			 if (this.options.inherited_paths.hasOwnProperty(i)) {
			    this.display_path(i, this.options.inherited_paths[i], true);
			 }
		 }

		for (i in this.options.paths) {
			if (this.options.paths.hasOwnProperty(i)) {
				this.display_path(i, this.options.paths[i]);
			}
		}

		// This has to be called only once.
		this.set_observer();
	},

	/**
	 * Method for displaying / adding a new path to the GUI.
	 * @param   module
	 * @param   rights
	 * @param   inherited
	 * @return  AuthConfiguration
	 */
	display_path: function (module, rights, inherited) {
		var i,
			right,
			tr = new Element('tr', {id: this.element_id + '-row-' + this.path_counter, 'data-counter': this.path_counter}),
			options = new Element('select', {id: this.element_id + '_module_' + this.path_counter, name: 'module_' + this.path_counter, className: 'input input-small module-select', disabled: !this.options.edit_mode});

		if (Object.isUndefined(inherited)) {
			inherited = false;
		}

		if (Object.isUndefined(rights)) {
			rights = [];
		}

		for (i in this.options.rights) {
			if (this.options.rights.hasOwnProperty(i)) {
				right = this.options.rights[i];

				// The first checkbox shall not be editable, since we always will have at least "view" rights, when adding a new path.
				if (i == 0) {
					tr.insert(new Element('td', {className: 'center'}).update(new Element('input', {type: 'checkbox', disabled: true, checked: true})));
				} else {
					tr.insert(new Element('td', {className: 'center'}).update(new Element('input', {type: 'checkbox', disabled: (!this.options.edit_mode || inherited), className: 'right-checkbox', name: 'right_' + this.path_counter + '[]', value: right.value, checked: rights.in_array(parseInt(right.value))})));
				}
			}
		}

		for (i in this.options.modules) {
			if (this.options.modules.hasOwnProperty(i)) {
				options.insert(new Element('option', {value:i}).update(this.options.modules[i]));
			}
		}

		if (options.down('option[value="' + module + '"]')) {
			options.down('option[value="' + module + '"]').writeAttribute('selected', 'selected');
		}

		if (inherited) {
			tr.addClassName(inherited ? 'inactive' : '');
			options.writeAttribute('disabled', 'disabled');
		}

		tr
			.writeAttribute('data-inherited', inherited ? 1 : 0)
			.insert(new Element('td').update(new Element('span', {className: 'mr5'}).update(idoit.Translate.get('LC__AUTH_GUI__REFERS_TO'))).insert(options))
			.insert(new Element('td')
				.update((this.options.edit_mode && ! inherited) ? new Element('button', {className: 'btn btn-small remove-path-button', type: 'button', title: idoit.Translate.get('LC__UNIVERSAL__REMOVE')}).update(new Element('img', {src: window.dir_images + 'icons/silk/cross.png'})) : ''));

		this.element.down('tbody').insert(tr);
		this.path_counter++;

		return this;
	},

	/**
	 * Basically does the same as display_path, but will be called by the GUI (not internally).
	 * Maybe we can combine the two methods without to much stress.
	 * @return  AuthConfiguration
	 */
	create_new_path: function () {
		var i,
			right,
			tr = new Element('tr', {id: this.element_id + '-row-' + this.path_counter, 'data-counter': this.path_counter}),
			options = new Element('select', {id: this.element_id + '_module_' + this.path_counter, name: 'module_' + this.path_counter, className: 'input input-small module-select', disabled: !this.options.edit_mode});

		for (i in this.options.rights) {
			if (this.options.rights.hasOwnProperty(i)) {
				right = this.options.rights[i];

				// The first checkbox shall not be editable, since we always will have at least "view" rights, when adding a new path.
				if (i == 0) {
					tr.insert(new Element('td', {className: 'center'}).update(new Element('input', {type: 'checkbox', disabled: true, checked: true})));
				} else {
					tr.insert(new Element('td', {className: 'center'}).update(new Element('input', {type: 'checkbox', disabled: !this.options.edit_mode, className: 'right-checkbox', name: 'right_' + this.path_counter + '[]', value: right.value})));
				}
			}
		}

		for (i in this.options.modules) {
			if (this.options.modules.hasOwnProperty(i)) {
				options.insert(new Element('option', {value:i}).update(this.options.modules[i]));
			}
		}

		tr
			.insert(new Element('td').update(new Element('span', {className: 'mr5'}).update(idoit.Translate.get('LC__AUTH_GUI__REFERS_TO'))).insert(options))
			.insert(new Element('td')
				.update((this.options.edit_mode) ? new Element('button', {className: 'btn btn-small remove-path-button', type: 'button', title: idoit.Translate.get('LC__UNIVERSAL__REMOVE')}).update(new Element('img', {src: window.dir_images + 'icons/silk/cross.png'})) : ''));

		this.element.down('tbody').insert(tr);
		this.path_counter++;

		return this;
	},

	/**
	 * Method for resetting all observers.
	 * @return  AuthConfiguration
	 */
	set_observer: function () {
		this.element.stopObserving();

		// The internet explorer has massive problems handling "onChange" events...
		if (Prototype.Browser.IE && Prototype.Browser.IEVersion < 9) {
			this.element.on('click', 'input.right-checkbox', this.update_rights);
		} else {
			this.element.on('change', 'input.right-checkbox', this.update_rights);
		}

		this.element.on('click', 'button.remove-path-button', this.remove_path);

		return this;
	},

	/**
	 * Method for removing a path.
	 * @param   ev
	 */
	remove_path: function (ev) {
		ev.findElement().up('tr').remove();
	},

	/**
	 * Checking and/or disabling other checkboxes, depending on the inheritance.
	 * @param   ev
	 */
	update_rights: function (ev) {
		var $checkbox = ev.findElement('input'),
			value = $checkbox.readAttribute('value'),
			$row = $checkbox.up('tr');

		if (value == 2048) {
			// The "Supervisor" right was checked, check all the other ones that are available!
			$row.select('input.right-checkbox').invoke($checkbox.checked ? 'disable' : 'enable').invoke('setValue', $checkbox.checked);
		}

		$checkbox.enable();
	}
});