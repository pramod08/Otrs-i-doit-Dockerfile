(function() {
	'use strict';

	var $visualization = $('C_VISUALIZATION_CANVAS'),
		$visualization_top = $('C_VISUALIZATION_TOP'),
		$visualization_top_options = $('C_VISUALIZATION_TOP_OPTIONS'),
		$visualization_top_options_label = $('C_VISUALIZATION_TOP_OPTIONS_LABEL'),
		$service_filter = $('C_VISUALIZATION_SERVICE_FILTER'),
		$button_fullscreen = $('C_VISUALIZATION_FULLSCREEN'),
		$export_button = $('C_VISUALIZATION_EXPORT_BUTTON'),
		$print_button = $('C_VISUALIZATION_PRINT_BUTTON'),
		$infobox = $('C_VISUALIZATION_LEFT'),
		$infobox_header = $('C_VISUALIZATION_LEFT_HEADER'),
		$infobox_content = $('C_VISUALIZATION_LEFT_CONTENT'),
		$infobox_functions = $('C_VISUALIZATION_LEFT_FUNCTIONS'),
		$infobox_legend = $('C_VISUALIZATION_LEFT_LEGEND'),
		$zoom_in = $('C_VISUALIZATION_ZOOM_IN_BUTTON'),
		$zoom = $('C_VISUALIZATION_ZOOM_BUTTON'),
		$zoom_out = $('C_VISUALIZATION_ZOOM_OUT_BUTTON'),
		$profile_selection = $('C_VISUALIZATION_PROFILE'),
		$orientation = $('C_VISUALIZATION_ORIENTATION_BUTTON'),
		$switch_vis_type = $('C_VISUALIZATION_SWITCH_VIS_TYPE_BUTTON'),
		responsive_options = Prototype.emptyFunction,
		refresh_profile_dialog = Prototype.emptyFunction,
		load_infobox = Prototype.emptyFunction,
		display_infobox = Prototype.emptyFunction,
		object_infos = {},
		loaded_object = 0;

	$('navBar').hide();
	$('contentWrapper').setStyle({marginTop:'-36px'});
	$('mainMenu').select('li').each(function($el) {
		if ($el.hasClassName('cmdb-explorer')) {
			$el.addClassName('active');
		} else {
			$el.removeClassName('active');
		}
	});

	if ($visualization_top && $visualization_top_options) {
		responsive_options = function () {
			var options_width = $visualization_top.getWidth(),
				toggle_width = 1015,
				has_responsive_class = $visualization_top_options.hasClassName('responsive'),
				$svg = $visualization.down('svg');

			if (options_width < toggle_width && !has_responsive_class) {
				// The width has sunk under 920px - we make the options responsive.

				$visualization_top_options
					.addClassName('responsive')
					.select('button,a.btn,span.mr20')
					.invoke('removeClassName', 'fr')
					.invoke('setStyle', {display:'block'});

				$visualization_top_options.down('select').setStyle({width:'170px'});

				$visualization_top_options.select('button,a.btn').each(function ($el) {
					$el.addClassName('btn-block').down('span').show();
				});

				$visualization_top_options_label.show();
			} else if (options_width >= toggle_width && has_responsive_class) {
				$visualization_top_options
					.removeClassName('responsive')
					.select('button,a.btn,span.mr20')
					.invoke('addClassName', 'fr')
					.invoke('setStyle', {display:null});

				$visualization_top_options.down('select').setStyle({width:null});

				$visualization_top_options.select('button,a.btn').each(function ($el) {
					$el.removeClassName('btn-block').down('span').hide();
				});

				$visualization_top_options_label.hide();
			}

			// One last thing: If changing the height of the browser window, we need to change the $infobox_content aswell.
			$infobox_content.setStyle({height: ($infobox.getHeight() - ($infobox_header.getHeight() + $infobox_functions.getHeight() + $infobox_legend.getHeight())) + 'px'});

			// We can not use "$svg.hasClassName(...)" because Prototype 1.7 can't handle SVG elements :(
			if ($svg && $svg.getAttribute('class') == 'responsive-observe') {
				$visualization.down('svg').setAttribute('width', $visualization.getWidth());
				$visualization.down('svg').setAttribute('height', $visualization.getHeight());
			}
		};

		$visualization_top_options.select('button,a.btn').each(function ($el) {
			$el.removeClassName('btn-block').down('span').hide();
		});

		// We need to listen to "window resizing" and dragging the navbar.
		Event.observe(window, 'resize', responsive_options);
		idoit.callbackManager
			.registerCallback('idoit-dragbar-update', responsive_options)
			.triggerCallback('idoit-dragbar-update');
	}

	if ($infobox_content && $infobox_functions.down('div') && $infobox_legend) {
		load_infobox = function (object_data, node_id) {
			var object_id = object_data.obj_id,
				relation_id = object_data.relation_obj_id;

			if (object_infos.hasOwnProperty(object_id + '-' + relation_id)) {
				display_infobox(object_infos[object_id + '-' + relation_id]);
			} else {
				loaded_object = object_id;

				$infobox_content
					.removeClassName('error')
					.update(new Element('img', {src:'[{$dir_images}]ajax-loading.gif', className:'vam mr5'}))
					.insert(new Element('span', {className:'vam'}).update('[{isys type="lang" ident="LC__UNIVERSAL__LOADING"}]'));

				$infobox_functions.down('div').update($infobox_content.innerHTML);

				new Ajax.Request('[{$ajax_url_visualization}]&func=load-object-infobox', {
					parameters: {
						object: object_id,
						relation: relation_id,
						'profile-id': $profile_selection.getValue()
					},
					onComplete: function (response) {
						var json = response.responseJSON;

						if (!is_json_response(response, true)) {
							return;
						}

						if (json.success) {
							object_infos[json.data.obj_id + '-' + relation_id] = json.data;

							display_infobox(object_infos[json.data.obj_id + '-' + relation_id]);
						} else {
							$infobox_content.addClassName('error').update(json.message);
							$infobox_functions.down('div').update();
						}
					}
				});
			}
		};

		display_infobox = function (data) {
			var $table, i, item;

			$infobox_content
				.update(new Element('table', {className:'mb10 pb0', width:'100%'})
					.update(new Element('tr')
						.update(new Element('td', {className:'vat', width: 105}).update(new Element('img', {src:data.image, className:'object-image pr5'})))
						.insert(new Element('td', {className:'vat'})
							.update(new Element('div', {className:'cmdb-marker', style:'background:' + data.obj_type_color + ';'}))
							.insert(new Element('strong', {className:'obj-title'}).update(data.obj_type_title + ' &raquo; ' + data.obj_title))
							.insert(new Element('hr', {className:'mt5 mb5'}))
							.insert(new Element('p')
								.update(new Element('div', {className:'cmdb-marker', style:'background:' + data.cmdb_status_color + ';'}))
								.insert(new Element('span').update(data.cmdb_status_title)))
							.insert(new Element('p', {className:'cb mt10'})
								.update(new Element('strong', {className:'mr5'}).update(data.relation_type)))
					)));

			$infobox_functions.down('div')
				.update(new Element('button', {type:'button', className:'btn btn-block mb5 set-root-button', 'data-obj-id':data.obj_id})
					.update(new Element('img', {src:'[{$dir_images}]icons/silk/chart_organisation.png', className:'mr5'}))
					.insert(new Element('span').update('[{isys type="lang" ident="LC__CMDB_EXPLORER__SET_AS_ROOT"}]')))
				.insert(new Element('button', {type:'button', className:'btn btn-block mb5 filter-obj-type', 'data-obj-type-id':data.obj_type_id})
					.update(new Element('img', {src:'[{$dir_images}]icons/silk/sitemap_color.png', className:'mr5'}))
					.insert(new Element('span').update('[{isys type="lang" ident="LC__MODULE__CMDB__VISUALIZATION__FILTER_OBJECT_TYPE"}]')))
				.insert(new Element('a', {href:'?[{$smarty.const.C__CMDB__GET__OBJECT}]=' + data.obj_id, target:'_blank', className:'btn btn-block mb5'})
					.update(new Element('img', {src:'[{$dir_images}]icons/silk/link.png', className:'mr5'}))
					.insert(new Element('span').update('[{isys type="lang" ident="LC__MODULE__CMDB__VISUALIZATION__OPEN_OBJECT"}]')));

			// Certain functions shall not be visible for the root object.
			if ($F('C_VISUALIZATION_OBJ_SELECTION__HIDDEN') == data.obj_id) {
				$infobox_functions.select('button.set-root-button').invoke('remove');
			}

			if (Object.isArray(data.dynamic_data)) {
				$table = $infobox_content
					.insert(new Element('h5', {className:'gradient p5 border'}).update('[{isys type="lang" ident="LC__VISUALIZATION_PROFILES__TAB__OBJECT_INFO_CONFIGURATION"}]'))
					.insert(new Element('table', {className:'obj-info mainTable pb0 border-left border-right border-bottom border-ccc', width:'100%'}))
					.down('table.obj-info');

				for (i in data.dynamic_data) {
					if (data.dynamic_data.hasOwnProperty(i)) {
						item = data.dynamic_data[i];

						$table.insert(new Element('tr', {className:(i%2 ? 'line0' : 'line1')})
							.update(new Element('td').update(item[0]))
							.insert(new Element('td').update(item[1])));
					}
				}
			}
		};

		$infobox_functions.on('click', 'button.set-root-button', function (ev) {
			var $button = ev.findElement('button');

			$('C_VISUALIZATION_OBJ_SELECTION__VIEW').setValue($infobox_content.down('strong.obj-title').innerHTML);
			$('C_VISUALIZATION_OBJ_SELECTION__HIDDEN').setValue($button.readAttribute('data-obj-id'));

			idoit.callbackManager.triggerCallback('visualization-init-explorer');
		});

		$infobox_functions.on('click', 'button.filter-obj-type', function (ev) {
			var $button = ev.findElement('button'),
				$checkbox = $infobox_legend.down('input#obj-type-filter-' + $button.readAttribute('data-obj-type-id'));

			$checkbox.checked = !$checkbox.checked;

			$checkbox.simulate('change');
		});

		$infobox_legend.on('change', 'input.obj-type-filter', function (ev) {
			var $checkbox = ev.findElement('input'),
				obj_types;

			if ($checkbox.hasClassName('toggle-all')) {
				$infobox_legend.select('input.obj-type-filter:not(.toggle-all)').invoke('setValue', ($checkbox.checked ? 1 : 0));
			}

			obj_types = $infobox_legend.select('input.obj-type-filter:checked:not(.toggle-all)').invoke('up', 'li').invoke('readAttribute', 'data-obj-type-id');

			idoit.callbackManager.triggerCallback('visualization-toggle-obj-types', obj_types);
		});
	}

	if ($service_filter) {
		$service_filter.on('change', function () {
			idoit.callbackManager.triggerCallback('visualization-init-explorer');
		});
	}

	if ($export_button) {
		// We re-enable the export button, once the graph is loaded.
		$export_button.disable();
	}

	if ($print_button) {
		// We re-enable the print button, once the graph is loaded.
		$print_button.disable().on('click', function () {
			if (window.print) {
				window.print();
			}
		});
	}

	if ($profile_selection) {
		$profile_selection.on('change', function () {
			// We need to reset the infos, because the profile might select different dynamic data.
			object_infos = {};

			// Handle the preselection.
			new Ajax.Request('[{$ajax_url_visualization}]&func=load-profile-config', {
				parameters: {
					'profile-id': $profile_selection.getValue()
				},
				onComplete: function (transport) {
					var json = transport.responseJSON, defaults, i, $checkbox;

					if (json.success) {
						try {
							defaults = (json.data.isys_visualization_profile__defaults || '{}').evalJSON();

							// Activate all checkboxes.
							$infobox_legend.select('input.obj-type-filter:not(.toggle-all)').invoke('setValue', 1);

							// Setting the default "object type view filter".
							if (Object.isArray(defaults['obj-type-filter']) && defaults['obj-type-filter'].length > 0) {
								for (i in defaults['obj-type-filter']) {
									if (defaults['obj-type-filter'].hasOwnProperty(i)) {
										$checkbox = $('obj-type-filter-' + defaults['obj-type-filter'][i]);

										if ($checkbox) {
											$checkbox.setValue(0);
										}
									}
								}
							}

							// Setting the default service filter.
							$service_filter.setValue(defaults['service-filter']);

							// Setting the default orientation.
							if (defaults['orientation'] != 'vertical') {
								$orientation.writeAttribute('data-orientation', 'horizontal')
									.down('img').writeAttribute('src', '[{$dir_images}]icons/silk/arrow_rotate_clockwise-half.png');
							} else {
								$orientation.writeAttribute('data-orientation', 'vertical')
									.down('img').writeAttribute('src', '[{$dir_images}]icons/silk/arrow_rotate_anticlockwise-half.png');
							}
						} catch (e) {
							idoit.Notify.error('[{isys type="lang" ident="LC__VISUALIZATION_PROFILES__ERROR_WHILE_PARSING_CONFIG"}]' + e, {sticky:true});
						}
					} else {
						// On failure:
						idoit.Notify.error(json.message, {sticky:true});
					}

					idoit.callbackManager.triggerCallback('visualization-init-explorer');
				}
			});
		});

		refresh_profile_dialog = function () {
			var selected_profile = $profile_selection.getValue();

			$profile_selection.disable();

			new Ajax.Request('[{$ajax_url_visualization}]&func=load-profiles-for-dialog', {
				parameters: {
					type: (document.location.href.split('?')[1].toQueryParams().type || '[{$smarty.const.C__CMDB__VISUALIZATION_TYPE__TREE}]')
				},
				onComplete: function (response) {
					var json = response.responseJSON, i, $optgroup = new Element('optgroup', {label:'[{isys type="lang" ident="LC__VISUALIZATION_PROFILES"}]'});

					if (!is_json_response(response, true)) {
						return;
					}

					if (json.success) {
						for (i in json.data) {
							if (json.data.hasOwnProperty(i)) {
								$optgroup.insert(new Element('option', {value:i}).update(json.data[i]));
							}
						}
					} else {
						idoit.Notify.error(json.message);
					}

					$profile_selection.update($optgroup).enable().setValue(selected_profile);
				}
			});
		};
	}

	if ($button_fullscreen) {
		$button_fullscreen.on('click', function () {
            var fullscreen = false;

			// Toggle the "fullscreen" mode.
			$('C_VISUALIZATION', 'C_VISUALIZATION_TOP', 'C_VISUALIZATION_LEFT_CONTENT').invoke('toggleClassName', 'fullscreen');
			$('downarrow', 'infoBox').invoke('toggle');

            fullscreen = $('C_VISUALIZATION').hasClassName('fullscreen');

			$button_fullscreen
				.writeAttribute('title', (fullscreen ? '[{isys type="lang" ident="LC__MODULE__CMDB__VISUALIZATION__EXIT_FULLSCREEN"}]' : '[{isys type="lang" ident="LC__MODULE__CMDB__VISUALIZATION__FULLSCREEN"}]'))
                .down('img')
				.writeAttribute('src', (fullscreen ? '[{$dir_images}]icons/silk/arrow_in_longer.png' : '[{$dir_images}]icons/silk/arrow_out.png'))
                .next('span')
				.update((fullscreen ? '[{isys type="lang" ident="LC__MODULE__CMDB__VISUALIZATION__EXIT_FULLSCREEN"}]' : '[{isys type="lang" ident="LC__MODULE__CMDB__VISUALIZATION__FULLSCREEN"}]'));

			$visualization.down('svg').setAttribute('width', $visualization.getWidth());
			$visualization.down('svg').setAttribute('height', $visualization.getHeight());
		});
	}

	if ($switch_vis_type) {
		$switch_vis_type.on('click', function () {
			var url = document.location.href.split('?')[1],
				url_params = url.toQueryParams();

			url_params.type = (url_params.type == '[{$smarty.const.C__CMDB__VISUALIZATION_TYPE__GRAPH}]' ? '[{$smarty.const.C__CMDB__VISUALIZATION_TYPE__TREE}]' : '[{$smarty.const.C__CMDB__VISUALIZATION_TYPE__GRAPH}]');

			// Push the URL state, to enable "page refresh" after changing some parameters.
			document.location.href = '?' + Object.toQueryString(url_params);
		});
	}

	if ($zoom_in) {
		$zoom_in.on('click', function () {
			idoit.callbackManager.triggerCallback('visualization-zoom', '+');
		});
	}

	if ($zoom) {
		$zoom.on('click', function () {
			idoit.callbackManager.triggerCallback('visualization-zoom', '=');
		});
	}

	if ($zoom_out) {
		$zoom_out.on('click', function () {
			idoit.callbackManager.triggerCallback('visualization-zoom', '-');
		});
	}

	if ($visualization_top_options_label) {
		$visualization_top_options_label.hide()
	}

	if ($orientation) {
		$orientation.on('click', function () {
			if ($orientation.readAttribute('data-orientation') == 'vertical') {
				$orientation.writeAttribute('data-orientation', 'horizontal')
					.down('img').writeAttribute('src', '[{$dir_images}]icons/silk/arrow_rotate_clockwise-half.png');
			} else {
				$orientation.writeAttribute('data-orientation', 'vertical')
					.down('img').writeAttribute('src', '[{$dir_images}]icons/silk/arrow_rotate_anticlockwise-half.png');
			}

			idoit.callbackManager.triggerCallback('visualization-toggle-orientation', $orientation.readAttribute('data-orientation'));
		});
	}

	idoit.callbackManager.registerCallback('visualization-open-infobox', load_infobox);

	idoit.callbackManager.registerCallback('refresh-profile-dialog', refresh_profile_dialog);
})();