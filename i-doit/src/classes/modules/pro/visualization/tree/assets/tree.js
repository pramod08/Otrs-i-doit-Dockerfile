var $canvas = $('C_VISUALIZATION_CANVAS'),
	$overlay = $('C_VISUALIZATION_OVERLAY').hide(),
	$overlayAddition = $overlay.down('.addition'),
	$refresh_button = $('C_VISUALIZATION_REFRESH_BUTTON'),
	$orientation = $('C_VISUALIZATION_ORIENTATION_BUTTON'),
	object_types = '[{$object_types|json_encode|escape:"javascript"}]'.evalJSON(),
	$export_button = $('C_VISUALIZATION_EXPORT_BUTTON'),
	$print_button = $('C_VISUALIZATION_PRINT_BUTTON'),

	explorer_master,
	explorer_slave,
	top_tree_name = 'visualization_tree_top',
	bottom_tree_name = 'visualization_tree_bottom',
	profile = {},
	last_inserted_master_id = 0,
	last_inserted_slave_id = 0,

	box_height = 20,
	profile_rows = 0;

var toggle_orientation_function = function (orientation) {
	profile = profile || {};

	var vertical = (orientation == 'vertical'),
		node_height = ((profile_rows * box_height) + 10),
		node_width = (parseInt(profile.width) + 10);

	explorer_master
		.set_option('vertical', vertical)
		.set_option('level_distance', (vertical ? (node_height + 50) : (node_width + 50)))
		.process();

	explorer_slave
		.set_option('vertical', vertical)
		.set_option('level_distance', (vertical ? (node_height + 50) : (node_width + 50)))
		.process();

	// Trigger the "zoom to 100%" callback, to center the view.
	idoit.callbackManager.triggerCallback('visualization-zoom', '=');
};

var toggle_obj_type_transparency = function (object_types) {
	explorer_master.toggle_obj_type_transparency.call(explorer_master, object_types);
	explorer_slave.toggle_obj_type_transparency.call(explorer_slave, object_types);
};

// We need to implement this here (instead of the base class), because the tree displays two graphs.
var toggle_node = function (node_id, explorer) {
	var $node = explorer.vis.select('[data-id="' + node_id + '"]').node(),
		data;

	data = $node.__data__;

	if (data.children) {
		data._children = data.children;
		data.children = null;
	} else {
		data.children = data._children;
		data._children = null
	}

	$node.__data__ = data;

	explorer.process();
};

var load_node_children = function (node_data, explorer, by_master) {
	// Make an Ajax request to get the children (only 1 level) and add them:
	new Ajax.Request('[{$ajax_url}]&func=load-tree-level', {
		parameters: {
			object: node_data.data.obj_id,
			profile: $F('C_VISUALIZATION_PROFILE'),
			filter: $F('C_VISUALIZATION_SERVICE_FILTER'),
			last_id: (by_master ? last_inserted_master_id : last_inserted_slave_id),
			by_master: (by_master ? 1 : 0),
			only_one_row: (!node_data.data.doubling ? 1 : 0)
		},
		onComplete: function (response) {
			var json = response.responseJSON;

			$refresh_button.down('img').writeAttribute('src', '[{$dir_images}]icons/silk/arrow_refresh.png');

			if (!is_json_response(response, true)) {
				return;
			}

			if (json.success) {
				if (json.data.nodes.children.length > 0) {
					if (by_master) {
						last_inserted_master_id = json.data.count;

					} else {
						last_inserted_slave_id = json.data.count;
					}

					node_data.children = json.data.nodes.children;

					explorer.process();
				} else {
					idoit.Notify.info('[{isys type="lang" ident="LC__MODULE__CMDB__VISUALIZATION__NO_CHILDREN"}]');
				}
			}
		}
	});
};

var initialize_explorer = function () {
	var object = $F('C_VISUALIZATION_OBJ_SELECTION__HIDDEN');

	if (object.blank() || !(object > 0)) {
		idoit.Notify.info('[{isys type="lang" ident="LC__MODULE__CMDB__VISUALIZATION__EMPTY_SELECTION"}]', {life: 7.5});
		return;
	}

	// This should be done to end the lifecycle of older instances.
	if (explorer_master && explorer_master.hasOwnProperty('stop')) {
		explorer_master.stop();
	}

	if (explorer_slave && explorer_slave.hasOwnProperty('stop')) {
		explorer_slave.stop();
	}

	$refresh_button.down('img').writeAttribute('src', '[{$dir_images}]ajax-loading.gif');
	$overlay.show();

	new Ajax.Request('[{$ajax_url}]&func=load-tree-data', {
		parameters: {
			object: object,
			filter: $F('C_VISUALIZATION_SERVICE_FILTER'),
			profile: $F('C_VISUALIZATION_PROFILE')
		},
		onComplete: function (response) {
			var json = response.responseJSON, node_height, node_width, vertical, top_data, bottom_data, obj_types,
				url = document.location.href.split('?')[1],
				url_params = url.toQueryParams();

			$refresh_button.down('img').writeAttribute('src', '[{$dir_images}]icons/silk/arrow_refresh.png');

			if (!is_json_response(response, true)) {
				$overlay.hide();
				return;
			}

			if (json.success) {
				// Rewrite the URL.
				url_params.objID = object;
				url_params.service = $F('C_VISUALIZATION_SERVICE_FILTER');
				url_params.profile = $F('C_VISUALIZATION_PROFILE');

				// Push the URL state, to enable "page refresh" after changing some parameters.
				window.pushState({url: url_params, content: 'main_content'}, document.title, '?' + Object.toQueryString(url_params));

				profile = json.data.profile;

				profile_rows = profile.rows.length;

				last_inserted_master_id = parseInt(json.data.explorer_a_count);
				last_inserted_slave_id = parseInt(json.data.explorer_b_count);

				$overlayAddition
					.update('[{isys type="lang" ident="LC__MODULE__CMDB__VISUALIZATION__LOADING_OBJECTS"}]'.replace('%s', (last_inserted_master_id + last_inserted_slave_id)))
					.removeClassName('hide');

				if (profile.master_top) {
					top_data = json.data.explorer_a;
					bottom_data = json.data.explorer_b;
				} else {
					top_data = json.data.explorer_b;
					bottom_data = json.data.explorer_a;

				}

				node_height = ((profile_rows * box_height) + 10);
				node_width = (parseInt(profile.width) + 10);
				vertical = ($orientation.readAttribute('data-orientation') == 'vertical');
				obj_types = $('C_VISUALIZATION_LEFT_LEGEND').select('input.obj-type-filter:checked:not(.toggle-all)').invoke('up', 'li').invoke('readAttribute', 'data-obj-type-id');

				explorer_slave = new CMDB_Explorer_Tree($canvas.update(), top_data, {
					name:bottom_tree_name,
					top_tree:top_tree_name,
					bottom_tree:bottom_tree_name,
					node_width: node_width,
					node_height: node_height,
					node_row_height: box_height,
					vertical: vertical,
					level_distance: (vertical ? (node_height + 50) : (node_width + 50)),
					obj_type_filter: obj_types,
					tooltips: profile.tooltip,
					click: function (d) {
						if (profile.hasOwnProperty('show-cmdb-path') && profile['show-cmdb-path']) {
							explorer_slave.show_root_path(d);
						}

						idoit.callbackManager.triggerCallback('visualization-open-infobox', d.data, d.id);

						// First we remove the "flashing" effect from all nodes.
						explorer_slave.vis.selectAll('.flash').classed('flash', false);
						explorer_master.vis.selectAll('.flash').classed('flash', false);

						// If this node has the class "doubling" select the origin object and highlight it (somehow). Maybe Scriptacolous' "highlight" works?
						if (d.data.doubling) {
							explorer_slave.vis.select('[data-object-id="' + d.data.obj_id + '"]:not(.doubling)').classed('flash', true);
						}
					},
					dblclick: function (d) {
						if (d.children || d._children) {
							toggle_node(d.id, explorer_slave);
						} else {
							load_node_children(d, explorer_slave, profile.master_top);
						}
					}
				}, profile, object_types);

				explorer_slave.process(true);

				// This second tree is "mirrored" and will display the tree upside down.
				explorer_master = new CMDB_Explorer_Tree($canvas, bottom_data, {
					name:top_tree_name,
					top_tree:top_tree_name,
					bottom_tree:bottom_tree_name,
					node_width: node_width,
					node_height: node_height,
					mirrored: true,
					vertical: vertical,
					level_distance: (vertical ? (node_height + 50) : (node_width + 50)),
					obj_type_filter: obj_types,
					tooltips: profile.tooltip,
					click: function (d) {
						if (profile.hasOwnProperty('show-cmdb-path') && profile['show-cmdb-path']) {
							explorer_master.show_root_path(d);
						}

						idoit.callbackManager.triggerCallback('visualization-open-infobox', d.data, d.id);

						// First we remove the "flashing" effect from all nodes.
						explorer_slave.vis.selectAll('.flash').classed('flash', false);
						explorer_master.vis.selectAll('.flash').classed('flash', false);

						// If this node has the class "doubling" select the origin object and highlight it (somehow). Maybe Scriptacolous' "highlight" works?
						if (d.data.doubling) {
							explorer_master.vis.select('[data-object-id="' + d.data.obj_id + '"]:not(.doubling)').classed('flash', true);
						}
					},
					dblclick: function (d) {
						if (! d.data['root-object']) {
							if (d.children || d._children) {
								toggle_node(d.id, explorer_master);
							} else {
								load_node_children(d, explorer_master, !profile.master_top);
							}
						}
					}
				}, profile, object_types);

				explorer_master.set_svg(explorer_slave.get_svg());
				explorer_master.process(true);

				// Overwrite the CMDB_Explorer_Tree "toggle_orientation" method.
				idoit.callbackManager.registerCallback('visualization-toggle-orientation', toggle_orientation_function);

				// Overwrite the CMDB_Explorer_Tree "toggle_obj_type_transparency" method.
				idoit.callbackManager.registerCallback('visualization-toggle-obj-types', toggle_obj_type_transparency);

				// Trigger the "zoom to 100%" callback, to center the view.
				idoit.callbackManager.triggerCallback('visualization-zoom', '=');

				$overlay.hide();
				$overlayAddition.addClassName('hide');

				// Now we enable the export and print button.
				$export_button.enable();
				$print_button.enable();

				// And finally we load the root-object data.
				idoit.callbackManager.triggerCallback('visualization-open-infobox', {obj_id: object});
			} else {
				idoit.Notify.error(json.message);
			}
		}
	});
};

$refresh_button.on('click', function () {
	idoit.callbackManager.triggerCallback('visualization-init-explorer');
});

// Register a callback to initialize the CMDB-Explorer and trigger it on startup.
idoit.callbackManager.registerCallback('visualization-init-explorer', initialize_explorer).triggerCallback('visualization-init-explorer');