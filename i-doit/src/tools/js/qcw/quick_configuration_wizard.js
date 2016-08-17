var QuickConfWizard = Class.create({
	options:{},
	current_group:'',
	current_obj_type:'',

	// Constructor method, sets all initial observer.
	initialize:function (options) {

		this.options = Object.extend({
			ajax_url:'',
			confirm_delete_file:'',
			confirm_objtypegroup_delete:'',
			confirm_objtype_delete:'',
			message_obj_type_sorting_notice: '',
			object_type_sort: 'manual',
			$objTypeHider: false,
			$categoryHider: false
		}, options || {});

		this.$objTypeGroupList = $('obj_type_group_list');
		this.$objTypeList = $('obj_type_list');
		this.$categoryList = $('category_list');

		this.$objTypeLoading = $('objtype_loading');

		// Save the selected group or object-type.
		$('C__MODULE__QCW__OBJTYPEGROUP_BUTTON').on('click', this.objtypegroup_save.bindAsEventListener(this));
		$('C__MODULE__QCW__OBJTYPE_BUTTON').on('click', this.objtype_save.bindAsEventListener(this));

		// Delete the selected group.
		$('C__MODULE__QCW__OBJTYPEGROUP_BUTTON_DELETE').on('click', this.objtypegroup_delete.bindAsEventListener(this));
 		$('C__MODULE__QCW__OBJTYPE_BUTTON_DELETE').on('click', this.objtype_delete.bindAsEventListener(this));

		// Create a new group.
		$('C__MODULE__QCW__OBJTYPEGROUP_NEW_BUTTON').on('click', this.objtypegroup_new.bindAsEventListener(this));
		$('C__MODULE__QCW__OBJTYPE_NEW_BUTTON').on('click', this.objtype_new.bindAsEventListener(this));

		this.reset_observer();
	},

	// Method for resetting the various observers. @todo this can be completely avoided by using "$parent.on('change', 'child-selector', ...);".
	reset_observer:function () {
		Sortable.destroy(this.$objTypeGroupList);
		Sortable.destroy(this.$objTypeList);
		this.$objTypeGroupList.select('input.objtypegroup_active').invoke('stopObserving');
		this.$objTypeGroupList.select('li span.icon.edit').invoke('stopObserving');
		this.$objTypeGroupList.select('li').invoke('stopObserving');
		this.$objTypeList.select('input.objtype_active').invoke('stopObserving');
		this.$objTypeList.select('li span.icon.edit').invoke('stopObserving');
		this.$objTypeList.select('li').invoke('stopObserving');
		this.$objTypeList.select('span.used_in').invoke('stopObserving');
		this.$categoryList.select('li input.category_active').invoke('stopObserving');

		Position.includeScrollOffsets = true;

		Sortable.create(this.$objTypeGroupList, {tag:'li', handle:'handle', onUpdate:this.objtypegroup_change_sorting.bindAsEventListener(this)});
		Sortable.create(this.$objTypeList, {tag:'li', handle:'handle', onUpdate:this.objtype_change_sorting.bindAsEventListener(this), scroll:'obj_type_list_div'});
		this.$objTypeGroupList.select('input.objtypegroup_active').invoke('on', 'change', this.objtypegroup_change_status.bindAsEventListener(this));
		this.$objTypeGroupList.select('li span.icon.edit').invoke('on', 'click', this.objtypegroup_prepare_edit.bindAsEventListener(this));
		this.$objTypeGroupList.select('li').invoke('on', 'click', this.load_obj_type_by_group.bindAsEventListener(this));
		this.$objTypeList.select('input.objtype_active').invoke('on', 'change', this.objtype_change_status.bindAsEventListener(this));
		this.$objTypeList.select('li span.icon.edit').invoke('on', 'click', this.objtype_prepare_edit.bindAsEventListener(this));
		this.$objTypeList.select('li').invoke('on', 'click', this.load_categories_by_obj_type.bindAsEventListener(this));
		this.$categoryList.select('li input.category_active').invoke('on', 'change', this.category_change_status.bindAsEventListener(this));
	},

	// Method for changing the visibility of an object-type group.
	objtypegroup_change_status:function (ev) {
		var el = ev.findElement();

		this.$objTypeLoading.show();

        $('infoBox').down('span').innerHTML = '';
        new Ajax.Request(this.options.ajax_url, {
            parameters:{
                func:'objtypegroup_change_status',
                status:(el.checked ? 1 : 0),
                id:el.up('li').readAttribute('data-const')
            },
            method:'post',
            onSuccess:function () {
                this.$objTypeLoading.hide();
            }.bind(this)
        });
	},

	objtype_change_status: function (ev) {
		var input = ev.findElement();

		this.$objTypeLoading.show();

		new Ajax.Request(this.options.ajax_url, {
			parameters: {
				func: 'objtype_change_status',
				status: (input.checked ? 1 : 0),
				id: input.up('li').readAttribute('data-const'),
				group_id: this.current_group
			},
			method: 'post',
			onSuccess: function () {
				var new_label = '';

				if (input.checked) {
					new_label = '(' + $('objtypegroup_name').innerHTML + ')';
				}

				input.previous('.obj-type').down('.used_in').update(new_label);
			},
			onComplete: function () {
				this.$objTypeLoading.hide();
			}.bind(this)
		});
	},

	category_change_status: function (ev) {
		var input = ev.findElement();

		$('category_loading').show();

		var global_category = 1;

		if (input.up('li').className.search(/selfdefined/g) != -1) {
			global_category = 0;
		}

		new Ajax.Request(this.options.ajax_url, {
			parameters: {
				func: 'category_change_status',
				status: (input.checked ? 1 : 0),
				id: input.up('li').readAttribute('data-const'),
				obj_type_id: this.current_obj_type,
				catg: global_category
			},
			method: 'post',
			onSuccess: function () {
				$('category_loading').hide();
			}
		});
	},

	objtypegroup_change_sorting: function () {
		var sorting = [];

		// Display the loading icon.
		$('objtypegroup_loading').show();

		this.$objTypeGroupList.select('li').each(function (el) {
			sorting.push(el.readAttribute('data-const'));
		});

		new Ajax.Request(this.options.ajax_url, {
			parameters: {
				func: 'objtypegroup_change_sorting',
				sorting: sorting.join(',')
			},
			method: 'post',
			onComplete: function () {
				$('objtypegroup_loading').hide();
			}
		});
	},

	objtype_change_sorting: function () {
		var sorting = [];

		if (this.options.object_type_sort != 'manual') {
			idoit.Notify.info(this.options.message_obj_type_sorting_notice, {sticky: true});
			return;
		}

		// Display the loading icon.
		this.$objTypeLoading.show();

		this.$objTypeList.select('li').each(function (el) {
			sorting.push(el.readAttribute('data-const'));
		});

		new Ajax.Request(this.options.ajax_url, {
			parameters: {
				func: 'objtype_change_sorting',
				sorting: sorting.join(',')
			},
			method: 'post',
			onSuccess: function () {
				this.$objTypeLoading.hide();
			}.bind(this)
		});
	},

	objtypegroup_prepare_edit: function (ev) {
		var li = ev.findElement().up('li'),
			title = li.down('span.title').innerHTML;

		$('objtypegroup_new_edit').hide();

		$('C__MODULE__QCW__OBJTYPEGROUP_NAME').writeAttribute('data-const', li.readAttribute('data-const')).value = title;

		new Effect.BlindDown('objtypegroup_new_edit', {
			duration: 0.5,
			afterFinish: function () {
				new Effect.Highlight('objtypegroup_new_edit');
				$('C__MODULE__QCW__OBJTYPEGROUP_NAME').focus();
			}
		});
	},

	objtype_prepare_edit:function (ev) {
		var li = ev.findElement().up('li'),
			title = li.down('span.title').innerHTML;

		$('objtype_new_edit').hide();

		$('C__MODULE__QCW__OBJTYPE_NAME').writeAttribute('data-const', li.readAttribute('data-const')).value = title;
		$('C__MODULE__QCW__CONTAINER_OBJECT_EDIT').checked = (li.readAttribute('data-container') == 1);
		$('C__MODULE__QCW__INSERTION_OBJECT_EDIT').checked = (li.readAttribute('data-insertion') == 1);

		new Effect.BlindDown('objtype_new_edit', {
			duration:0.5,
			afterFinish:function () {
				new Effect.Highlight('objtype_new_edit');
				$('C__MODULE__QCW__OBJTYPE_NAME').focus();
			}});
	},

	objtypegroup_save:function () {
		var input = $('C__MODULE__QCW__OBJTYPEGROUP_NAME'),
			title = input.value,
			constant = input.readAttribute('data-const');

		// Display the loading icon.
		$('objtypegroup_loading').show();

		new Ajax.Request(this.options.ajax_url,
			{
				parameters:{
					func:'objtypegroup_save',
					id:constant,
					title:title
				},
				method:'post',
				onSuccess:function () {
					$('objtypegroup_loading').hide();

					$('objtypegroup_' + constant).down('span.title').innerHTML = title;
					new Effect.Highlight('objtypegroup_' + constant, {startcolor:'#88ff88', afterFinish:function() {
						// This is important to remove the inline-color from the animation.
						$('objtypegroup_' + constant).setStyle({backgroundColor:''})
					}.bind(this)});

					new Effect.SlideUp('objtypegroup_new_edit', {duration:0.5});
				}
			});
	},

	objtype_save:function () {
		var input = $('C__MODULE__QCW__OBJTYPE_NAME'),
			title = input.value,
			constant = input.readAttribute('data-const'),
			container = $('C__MODULE__QCW__CONTAINER_OBJECT_EDIT').checked?1: 0,
			insertion = $('C__MODULE__QCW__INSERTION_OBJECT_EDIT').checked?1:0;

		// Display the loading icon.
		this.$objTypeLoading.show();

		new Ajax.Request(this.options.ajax_url,
			{
				parameters:{
					func:'objtype_save',
					id:constant,
					title:title,
					insertion:insertion,
					container:container
				},
				method:'post',
				onSuccess:function () {
					this.$objTypeLoading.hide();

					$('objtype_' + constant)
						.writeAttribute('data-insertion', insertion)
						.writeAttribute('data-container', container)
						.down('span.title').innerHTML = title;
					new Effect.Highlight('objtype_' + constant, {startcolor:'#88ff88'});
					new Effect.SlideUp('objtype_new_edit', {duration:0.5});
				}.bind(this)
			});
	},

	objtypegroup_delete: function () {
		var input = $('C__MODULE__QCW__OBJTYPEGROUP_NAME'),
			confirm_text = (this.options.confirm_objtypegroup_delete).replace('%s', input.value);

		if (confirm(confirm_text)) {
			var id = input.readAttribute('data-const');
			new Ajax.Request(this.options.ajax_url, {
				parameters: {
					func: 'objtypegroup_delete',
					id: id
				},
				method: 'post',
				onSuccess: function (transport) {
					if (transport.responseJSON.success) {
						$('objtypegroup_loading').hide();

						new Effect.Highlight('objtypegroup_' + id, {startcolor: '#ffB7B7'});
						new Effect.BlindUp('objtypegroup_' + id, {
							afterFinish: function () {
								$('objtypegroup_' + id).remove();
							}
						});
						new Effect.SlideUp('objtypegroup_new_edit', {duration: 0.5});
					} else {
						new Effect.Highlight('objtypegroup_new_edit', {startcolor: '#ffB7B7'});
					}
				}
			});
		}
	},

	objtype_delete:function () {
		var input = $('C__MODULE__QCW__OBJTYPE_NAME'),
			confirm_text = (this.options.confirm_objtype_delete).replace('%s', input.value);

		if (confirm(confirm_text)) {
			var id = input.readAttribute('data-const');
			new Ajax.Request(this.options.ajax_url,
				{
					parameters:{
						func:'objtype_delete',
						id:id
					},
					method:'post',
					onSuccess:function (transport) {
						if (transport.responseJSON.success) {
							$('objtypegroup_loading').hide();

							this.$categoryList.select('li').each(function (el) {
								el.addClassName('disabled').down('input').disable().checked = false;
							});

							new Effect.Highlight('objtype_' + id, {startcolor:'#ffB7B7'});
							new Effect.BlindUp('objtype_' + id, {
								afterFinish:function () {
									$('objtype_' + id).remove();
								}
							});
							new Effect.SlideUp('objtype_new_edit', {duration:0.5});
						} else {
							new Effect.Highlight('objtype_new_edit', {startcolor:'#ffB7B7'});
						}
					}.bind(this)
				});
		}
	},

	objtypegroup_new: function () {
		var input = $('C__MODULE__QCW__OBJTYPEGROUP_NEW'),
			title;

		if (input.value.blank()) {
			new Effect.Highlight(input.up('li'), {startcolor: '#ffB7B7', restorecolor: '#eeeeee'});
		} else {
			$('objtypegroup_loading').show();
			title = input.value;

			new Ajax.Request(this.options.ajax_url, {
				parameters: {
					func: 'objtypegroup_new',
					title: title
				},
				method: 'post',
				onSuccess: function (transport) {
					var json = transport.responseJSON,
						constant = json.constant,
						li = new Element('li', {id: 'objtypegroup_' + constant, 'data-const': constant, className: 'p5 selfdefined', style: 'position:relative'});

					li.update(new Element('span', {className: 'handle'}))
						.insert(' ')
						.insert(new Element('span', {className: 'title'}).update(title))
						.insert(new Element('input', {className: 'objtypegroup_active', type: 'checkbox', checked: 'checked'}))
						.insert(new Element('span', {className: 'icon edit'}));

					$('C__MODULE__QCW__OBJTYPEGROUP_NEW').value = '';
					this.$objTypeGroupList.insert(li);
					$('objtypegroup_loading').hide();

					this.reset_observer();

					new Effect.Highlight('objtypegroup_' + constant, {
						startcolor: '#88ff88', afterFinish: function () {
							// This is important to remove the inline-color from the animation.
							$('objtypegroup_' + constant).setStyle({backgroundColor: ''})
						}.bind(this)
					});
				}.bind(this)
			});
		}
	},

	load_obj_type_by_group: function (ev) {
		var el = ev.findElement(),
			title,
			constant;

		if (el.tagName.toUpperCase() != 'LI') {
			el = el.up('li')
		}

		if (this.options.$categoryHider) {
			this.options.$categoryHider.removeClassName('hide');
		}

		title = el.down('span.title').innerHTML;
		constant = el.readAttribute('data-const');

		if (this.current_group != constant) {
			this.current_group = constant;
			$('C__MODULE__QCW__OBJTYPE_NEW').writeAttribute('data-group-id', constant);
			this.$objTypeLoading.show();
			new Ajax.Request(this.options.ajax_url, {
				parameters: {
					func: 'objecttype_list',
					objTypeGroup: constant
				},
				method: 'post',
				onSuccess: function (transport) {
					var json = transport.responseJSON,
						objtypes = transport.responseJSON.length,
						i;

					if (this.options.$objTypeHider) {
						this.options.$objTypeHider.addClassName('hide');
					}

					this.$objTypeList.select('span.used_in').invoke('on', 'click', this.objtype_remove_group_assignment.bindAsEventListener(this));

					this.$categoryList.select('li').each(function (el) {
						el.addClassName('disabled').down('input').disable().checked = false;
					});

					this.$objTypeList.select('li').each(function (el) {
						var input = el.down('input');

						// Only disable object types, which are already attached.
						if (input.checked) {
							el.addClassName('disabled');
							el.down('input').disable();
						} else {
							el.removeClassName('disabled');
							el.down('input').enable();
						}
					});

					for (i = 0; i < objtypes; i++) {
						if (json[i] && $('objtype_' + json[i].id)) {
							$('objtype_' + json[i].id).removeClassName('disabled').down('input').enable();
						}
					}

					this.$objTypeLoading.hide();
				}.bind(this)
			});

			new Effect.Highlight($('objtypegroup_name').update(title).setStyle({color: '#589C8D'}).morph('color:#000;').up('div.box'), {startcolor: '#d4ffde', restorecolor: '#fff'});

			$$('li.active').invoke('removeClassName', 'active');

			el.addClassName('active');
		}
	},

	load_categories_by_obj_type: function (ev) {
		var el = ev.findElement(),
			title,
			constant;

		if (this.current_group.blank()) {
			return;
		}

		if (el.tagName.toUpperCase() != 'LI') {
			el = el.up('li')
		}

		title = el.down('span.title').innerHTML;
		constant = el.readAttribute('data-const');

		if (this.current_obj_type != constant) {
			this.current_obj_type = constant;

			$('category_loading').show();

			new Ajax.Request(this.options.ajax_url, {
				parameters: {
					func: 'category_list',
					obj_type: constant
				},
				method: 'post',
				onSuccess: function (transport) {
					var json = transport.responseJSON,
						categories = json.length,
						input,
						i;

					if (this.options.$categoryHider) {
						this.options.$categoryHider.addClassName('hide');
					}

					this.$categoryList.select('li').each(function (el) {
						el.removeClassName('disabled').down('input').enable().checked = false;
					});

					for (i = 0; i < categories; i++) {
						if (input = $('category_' + json[i])) {
							input.down('input').checked = true;
						}
					}

					$('category_loading').hide();
				}.bind(this)
			});

			new Effect.Highlight($('objtype_name').update(title).setStyle({color: '#589C8D'}).morph('color:#000;').up('div.box'), {startcolor: '#d4ffde', restorecolor: '#fff'});

			$('obj_type_list_div').select('li.active').invoke('removeClassName', 'active');

			el.addClassName('active');
		}
	},

	objtype_new:function () {
		var $input = $('C__MODULE__QCW__OBJTYPE_NEW'),
			title = $input.getValue();

		this.$objTypeLoading.show();

		if (title.blank()) {
			new Effect.Highlight($input.up('div'), {startcolor:'#ffB7B7', restorecolor:'#fff'});
		} else {
			new Ajax.Request(this.options.ajax_url,
				{
					parameters:{
						func:'objtype_new',
						title:title,
						container:$('C__MODULE__QCW__CONTAINER_OBJECT').checked ? 1 : 0,
						insertion:$('C__MODULE__QCW__INSERTION_OBJECT').checked ? 1 : 0
					},
					method:'post',
					onSuccess:function (transport) {
						var json = transport.responseJSON,
							constant = json.constant,
							li = new Element('li', {id:'objtype_' + constant, 'data-const':constant, className:'p5 selfdefined', style:'position:relative', 'data-insertion': ($('C__MODULE__QCW__INSERTION_OBJECT').checked ? 1 : 0), 'data-container': ($('C__MODULE__QCW__CONTAINER_OBJECT').checked ? 1 : 0)});

						if (json.success) {
							li.update(new Element('span', {className:'handle'}))
								.insert(' ')
								.insert(new Element('span', {className:'title'}).update(title))
								.insert(new Element('input', {className:'objtype_active', type:'checkbox'}))
								.insert(new Element('span', {className:'icon edit'}));

							$('C__MODULE__QCW__OBJTYPEGROUP_NEW').setValue('');
							this.$objTypeList.insert(li);
							this.$objTypeLoading.hide();
							this.reset_observer();

							new Effect.Highlight('objtypegroup_' + constant, {startcolor:'#88ff88'});
						}
					}.bind(this),
					onComplete:function() {

						// This will scroll the object type list to the bottom.
						this.$objTypeList.scrollTop = (this.$objTypeList.scrollHeight - this.$objTypeList.getHeight());
						$input.setValue('');
					}.bind(this)
				});
		}
	},

	objtype_remove_group_assignment: function (ev) {
		var li = ev.findElement().up('li'),
			constant = li.readAttribute('data-const');

		this.$objTypeLoading.show();

		new Ajax.Request(this.options.ajax_url, {
			parameters: {
				func: 'remove_group_assignment',
				id: constant
			},
			method: 'post',
			onSuccess: function (transport) {
				this.$objTypeLoading.hide();

				if (transport.responseJSON.success) {
					li.down('span.used_in').update();
					li.down('input').checked = false;

					if (!this.current_group.blank()) {
						li.removeClassName('disabled');
						li.down('input').enable();
					}
				}
			}.bind(this)
		});
	},


	// ################################### OLD METHODS - KEEP!
	delete_config_file: function (file_name, p_row_id) {
		if (confirm(this.options.confirm_delete_file)) {
			new Ajax.Request(this.options.ajax_url, {
				parameters: {
					func: 'delete_file',
					file: file_name
				},
				method: 'post',
				onSuccess: function () {
					$(p_row_id).remove();
				}
			});
		}
	}
});