var $canvas = $('C_VISUALIZATION_CANVAS'),
	$overlay = $('C_VISUALIZATION_OVERLAY').hide(),
	$overlayAddition = $overlay.down('.addition'),
	$refresh_button = $('C_VISUALIZATION_REFRESH_BUTTON'),
	object_types = '[{$object_types|json_encode|escape:"javascript"}]'.evalJSON(),
	$export_button = $('C_VISUALIZATION_EXPORT_BUTTON'),
	$print_button = $('C_VISUALIZATION_PRINT_BUTTON'),

	explorer,
	explorer_name = 'visualization_graph_main',
	profile = {},

	box_height = 20,
	profile_rows = 0;

$('C_VISUALIZATION_ORIENTATION_BUTTON').hide();

var initialize_explorer = function () {
	var object = $F('C_VISUALIZATION_OBJ_SELECTION__HIDDEN');

	if (object.blank() || !(object > 0)) {
		idoit.Notify.info('[{isys type="lang" ident="LC__MODULE__CMDB__VISUALIZATION__EMPTY_SELECTION"}]', {life: 7.5});
		return;
	}

	// This should be done to end the lifecycle of older instances.
	if (explorer && explorer.hasOwnProperty('stop')) {
		explorer.stop();
	}

	$refresh_button.down('img').writeAttribute('src', '[{$dir_images}]ajax-loading.gif');
	$overlay.show();

	new Ajax.Request('[{$ajax_url}]&func=load-graph-data', {
		parameters: {
			object: object,
			filter: $F('C_VISUALIZATION_SERVICE_FILTER'),
			profile: $F('C_VISUALIZATION_PROFILE')
		},
		onComplete: function (response) {
			var json = response.responseJSON, node_height, node_width, obj_types,
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

				$overlayAddition
					.update('[{isys type="lang" ident="LC__MODULE__CMDB__VISUALIZATION__LOADING_OBJECTS"}]'.replace('%s', json.data.nodes.length))
					.removeClassName('hide');

				node_height = ((profile_rows * box_height) + 10);
				node_width = (parseInt(profile.width) + 10);

				obj_types = $('C_VISUALIZATION_LEFT_LEGEND').select('input.obj-type-filter:checked:not(.toggle-all)').invoke('up', 'li').invoke('readAttribute', 'data-obj-type-id');

				explorer = new CMDB_Explorer_Graph($canvas.update(), json.data.nodes, {
					name: explorer_name,
					distance: (profile.mikro ? 200 : 100),
					gravity: 0.5,
					charge: -5000,
					node_width: (profile.mikro ? 30 : node_width),
					node_height: (profile.mikro ? 30 : node_height),
					node_row_height: box_height,
					obj_type_filter: obj_types,
					tooltips: profile.tooltip,
					click: function (d) {
						explorer.show_root_path(d);

						idoit.callbackManager.triggerCallback('visualization-open-infobox', d.data, d.id);
					}
				}, profile, object_types);

				explorer.process(true);

				$overlay.hide();
				$overlayAddition.addClassName('hide');

				// Now we enable the export and print button.
				$export_button.enable();
				$print_button.enable();

				// And finally we load the root-object data.
				idoit.callbackManager.triggerCallback('visualization-open-infobox', {obj_id:object});
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