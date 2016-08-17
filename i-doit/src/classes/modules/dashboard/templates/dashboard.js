window.dashboard = {
	/**
	 * Method for (re)loading a certain widget.
	 * @param  el
	 * @param  options
	 */
	reload_widget: function (el, options) {
		var opts = {
			identifier:el.readAttribute('data-identifier'),
			unique_id:el.id,
			config: el.readAttribute('data-config')
		};

		options = Object.extend(opts, options);

		new Ajax.Request('[{$widget_ajax_url}]&func=load_widget',
			{
				parameters: options,
				method: 'get',
				onSuccess: function (response) {
					var json = response.responseJSON;

					if (json) {
						if (json.success) {
							el.update(json.data);
						} else {
							el.update(new Element('p', {className: 'p5 m10 exception'}).update(json.message));
						}

						this.add_title_bar(el.id);
					}
					else {
						el.update(response.responseText);
					}
				}.bind(this)
			});
	},

	/**
	 * Method for creating the title-bar (including "edit" and "delete"-buttons).
	 * @param  unique_id
	 */
	add_title_bar: function (unique_id) {
		var el = $(unique_id),
			block = new Element('div', {className: 'widget-title'})
				.update(new Element('h4', {className: 'border gradient text-shadow'})
					.update(el.readAttribute('data-title')));

		// Only display the "edit" and "delete" buttons, if we are not displaying the default dashboard.
		if (window.default_dashboard == 0) {
			// Only display the "edit" button, if the widget is configurable and we are not in "default" mode.
			if (el.readAttribute('data-configurable') == 1 && window.is_allowed_to_configure_widgets == 1) {
				block.insert(
					new Element('h4', {className: 'border gradient text-shadow mouse-pointer edit', title: '[{isys type="lang" ident="LC__WIDGET__EDIT"}]'})
					.update(new Element('img', {className: 'vam', src: '[{$dir_images}]icons/silk/pencil.png'})));
			}

			if (el.readAttribute('data-removable') == 1 && window.is_allowed_to_configure_dashboard == 1) {
				block.insert(
					new Element('h4', {className: 'border gradient text-shadow mouse-pointer delete', title: '[{isys type="lang" ident="LC__WIDGET__REMOVE"}]'})
					.update(new Element('img', {className: 'vam', src: '[{$dir_images}]icons/silk/cross.png'})));
			}
		}

		$(unique_id).insert(block);
	},

	save_config_and_reload_widget: function (ajax_url, options) {
		// options needs "id", "unique_id" and "config"
		new Ajax.Request(ajax_url,
			{
				parameters: options,
				method: 'post',
				onSuccess: function (response) {
					var json = response.responseJSON,
						second_overlay = $('widget-popup-overlay');

                    if (second_overlay) {
	                    second_overlay.remove();
                    }

					if (json.success) {
						$(options.unique_id).update(json.data).writeAttribute('data-config', options.config);
						window.dashboard.add_title_bar(options.unique_id);

						popup_close($('widget-container-popup'));
					} else {
						alert(json.message);
						popup_close($('widget-container-popup'));
					}
				}
			});
	}
};