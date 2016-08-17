Position.includeScrollOffsets = true;

var g_templates = [],
	g_sortable;

function select_template(p_template) {
	var len = g_templates.length,
		selection = $F(p_template),
		item,
		find_template_in_list = function (f) {
			return (typeof f == 'object' && f.template_id == selection)
		};

	if (!g_templates.find(find_template_in_list) && selection > 0) {
		item = new Element('li', {className: 'p5 tpl_li', id: 'tpl_li_' + len})
			.update(new Element('div', {className: 'fr'})
				.update(new Element('button', {type: 'button', title: 'Remove template from list', className: 'btn btn-small', onClick: 'delete_template("' + len + '");'})
					.update(new Element('img', {src: window.dir_images + 'icons/silk/cross.png'}))))
			.insert(new Element('div', {id: 'xtmp_val_' + p_template.selectedIndex})
				.update(new Element('img', {className: 'fl mr5', src: window.dir_images + 'ajax-loading.gif'}))
				.insert(new Element('strong').update(p_template.options[p_template.selectedIndex].text)));

		g_templates[len] = {
			el: item,
			template_id: selection,
			index: p_template.selectedIndex,
			input: new Element('input', {'name': 'templates[]', 'type': 'hidden', 'value': selection})
		};

		print_list();

		if ($F('object_title').blank()) {
			$('object_title').setValue(p_template.options[p_template.selectedIndex].text);
		}

		p_template.options[p_template.selectedIndex].disabled = true;
	}
}

function select_single_template(p_template) {
	var len = g_templates.length,
		selection = $F(p_template),
		tpl_list = $('template_list').update(),
		item,
		active_templates = 0;

	if (p_template.selectedIndex != 0) {

		item = new Element('li', {className: 'p5 tpl_li', id: 'tpl_li_' + len})
			.update(new Element('div', {className: 'fr'})
				.update(new Element('button', {type: 'button', title: 'Remove template from list', className: 'btn btn-small', onClick: 'delete_template("' + len + '");'})
					.update(new Element('img', {src: window.dir_images + 'icons/silk/cross.png'}))))
			.insert(new Element('div', {id: 'xtmp_val_' + p_template.selectedIndex})
				.update(new Element('img', {className: 'fl mr5', src: window.dir_images + 'ajax-loading.gif'}))
				.insert(new Element('strong').update(p_template.options[p_template.selectedIndex].text)));

		g_templates[0] = {
			el: item,
			template_id: selection,
			index: p_template.selectedIndex,
			input: new Element('input', {'name': 'templates[]', 'type': 'hidden', 'value': selection})
		};

		g_templates.each(function (i) {
			if (i != undefined && typeof i == 'object' && i.index != -1) {
				active_templates++;

				// The "clone" is necessary for IE browsers to display the selected templates correctly.
				tpl_list.insert(i.el.clone(true)).insert(i.input);

				new Ajax.Updater('xtmp_val_' + i.index, '?ajax=1&call=template_content&template_id=' + i.template_id);
			}
		});

		g_sortable = Sortable.create('template_list', {
			scroll: (Prototype.Browser.Gecko) ? 'contentArea' : window,
			onChange: function (el) {

				var tpl_new = [];
				$$('.tpl_li').each(function (li) {
					var tpl_id = li.id.split('_')[2];

					tpl_new[tpl_new.length] = g_templates[tpl_id];
				});

				g_templates = tpl_new;
			}
		});
	} else {
		document.getElementById('template_list').innerHTML = "";
	}
}

function delete_template(p_index) {

	if (typeof g_templates[p_index] == 'object' && 'index' in g_templates[p_index]) {
		$('template_id').options[g_templates[p_index].index].disabled = '';
		g_templates[p_index].index = -1;
	}
	delete g_templates[p_index];

	print_list();
}

function print_list() {
	var active_templates = 0,
		tpl_list = $('template_list').update();

	g_templates.each(function (i) {
		if (i != undefined && typeof i == 'object' && i.index != -1) {
			active_templates++;

			// The "clone" is necessary for IE browsers to display the selected templates correctly.
			tpl_list.insert(i.el.clone(true)).insert(i.input);

			new Ajax.Updater(
				'xtmp_val_' + i.index,
				document.location.href + '&call=template_content&ajax=1&template_id=' + i.template_id
			);
		}
	});

	$('sel_count').update(active_templates);

	g_sortable = Sortable.create('template_list', {
		scroll: (Prototype.Browser.Gecko) ? 'contentArea' : window,
		onChange: function (el) {

			var tpl_new = [];
			$$('.tpl_li').each(function (li) {
				var tpl_id = li.id.split('_')[2];

				tpl_new[tpl_new.length] = g_templates[tpl_id];
			});

			g_templates = tpl_new;
		}
	});

	$('create_template').disabled = !($('object_type').value != -1 && active_templates > 0);
}

function loader_hide() {
	$('loader').hide();
	document.isys_form.target = '';
}

function tpl_loader_hide() {
	$('tpl_loader').hide();
}