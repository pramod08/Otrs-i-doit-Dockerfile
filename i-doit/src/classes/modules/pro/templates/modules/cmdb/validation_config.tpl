<h2 class="p5 gradient border-bottom">[{isys type="lang" ident="LC__CMDB__TREE__SYSTEM__TOOLS__VALIDATION"}]</h2>

<div class="hide">
	[{isys type="f_dialog" name="cmdb-validation-rules-template"}]
</div>

<div id="cmdb-validation" class="m10">
	<div class="border fl mr10 mb5" style="width:300px;">
		<h3 class="gradient p5 mouse-pointer">
			<img src="[{$dir_images}]icons/silk/bullet_arrow_right.png" class="mr5" /><span>[{isys type="lang" ident="LC__SETTINGS__CMDB__VALIDATION__ADD_CATEGORY_VALIDATION"}]</span>
		</h3>
		<div class="p5 border-top" style="display:none;">
			[{isys type="f_dialog" name="cmdb-validation-category-selector" chosen=true p_bInfoIconSpacer=0}]

			<button type="button" id="cmdb-validation-category-adder" class="mt5 btn btn-block">
				<img src="[{$dir_images}]icons/silk/add.png" class="mr5" /><span>[{isys type="lang" ident="LC__UNIVERSAL__BUTTON_ADD"}]</span>
			</button>
		</div>
	</div>

	<div class="border fl mr10 mb5" style="width:300px;">
		<h3 class="gradient p5 mouse-pointer">
			<img src="[{$dir_images}]icons/silk/bullet_arrow_right.png" class="mr5" /><span>[{isys type="lang" ident="LC__SETTINGS__CMDB__VALIDATION__CACHE_REFRESH"}]</span>
		</h3>

		<div class="p5 border-top" style="display:none;">
			<p>[{isys type="lang" ident="LC__SETTINGS__CMDB__VALIDATION__CACHE_REFRESH_DESCRIPTION"}]</p>
			<button type="button" id="cmdb-validation-cache-button" class="mt5 btn btn-block">
				<img src="[{$dir_images}]icons/silk/arrow_refresh.png" class="mr5" /><span>[{isys type="lang" ident="LC__SETTINGS__CMDB__VALIDATION__CACHE_BUTTON"}]</span>
			</button>
			<p class="p5 mt5" style="display:none;"></p>
		</div>
	</div>

	<div class="border fl" style="width:300px;">
		<h3 class="gradient p5 mouse-pointer">
			<img src="[{$dir_images}]icons/silk/bullet_arrow_right.png" class="mr5" /><span>[{isys type="lang" ident="LC__SETTINGS__CMDB__VALIDATION__REGEX"}]</span>
		</h3>

		<div class="p5 border-top" style="display:none;">
			<p>[{isys type="lang" ident="LC__SETTINGS__CMDB__VALIDATION__REGEX_DESCRIPTION" p_bHtmlEncode=false}]</p>

			[{isys type="f_text" name="cmdb-validation-regex-pattern" p_strClass="input input-block mb5" p_strValue="^(.*)\$" p_strPlaceholder="LC__SETTINGS__CMDB__VALIDATION__REGEX_PATTERN" p_bInfoIconSpacer=0}]
			[{isys type="f_text" name="cmdb-validation-regex-search" p_strClass="input input-block mb5" p_strPlaceholder="LC__SETTINGS__CMDB__VALIDATION__REGEX_SEARCH" p_bInfoIconSpacer=0}]

			<p id="cmdb-validation-regex-result" class="p5 border">[{isys type="lang" ident="LC__UNIVERSAL__WAITING"}]</p>

			<button type="button" id="cmdb-validation-regex-button" class="mt5 btn btn-block">
				<img src="[{$dir_images}]icons/silk/tick.png" class="mr5" /><span>[{isys type="lang" ident="LC__SETTINGS__CMDB__VALIDATION__REGEX_CHECK"}]</span>
			</button>
		</div>
	</div>

	<br class="cb" />

	<p class="mt5 p5 info"><img src="[{$dir_images}]icons/silk/information.png" class="vam mr5" /><span class="vam">[{isys type="lang" ident="LC__SETTINGS__CMDB__VALIDATION__REGEX_INFORMATION"}]</span></p>

	<div id="cmdb-validation-list"></div>
</div>

<style type="text/css">

	#cmdb-validation h3 span,
	#cmdb-validation h3 img,
	#cmdb-validation h4 span,
	#cmdb-validation h4 img {
		vertical-align: middle;
	}

	#cmdb-validation div.chosen-container {
		width:100%;
	}

	#cmdb-validation div.category-container {
		background: #eee;
	}

	#cmdb-validation div.property-label {
		width: 250px;
		height: 50px;
		display: block;
		float: left;
		position: relative;
	}

	#cmdb-validation div.property-label img {
		position: absolute;
		right: 10px;
		top: -1px;
	}

	#cmdb-validation-regex-pattern,
	#cmdb-validation-regex-search {
		box-sizing: border-box;
	}

	#cmdb-validation input.regex-field,
	#cmdb-validation-regex-pattern {
		font-family: Courier New,Lucida Console,Monospace,Monaco,sans-serif,serif;;
	}
</style>

<script type="text/javascript">
	(function () {
		"use strict";

		var $category_select = $('cmdb-validation-category-selector'),
			$category_add = $('cmdb-validation-category-adder'),
			$configuration_list = $('cmdb-validation-list'),
			$cache_button = $('cmdb-validation-cache-button'),
			$rule_template = $('cmdb-validation-rules-template'),
			$save_button = $('navbar_item_C__NAVMODE__SAVE').writeAttribute('onclick', false),
			category_configuration = {},
			ajax_counter = 0;

		var display_rules = function (category) {
			var $category = $(category),
				$content = $category.down('div').update(),
				cat_data = category_configuration[category],
				unique_obj_disabled = ! cat_data.multivalue,
				property,
				locked,
				$rule_dialog;

			for (property in cat_data.properties) {
				if (cat_data.properties.hasOwnProperty(property)) {
					// We don't want to display "HTML" or "HR" properties of custom categories:
					if (category.substr(0, 1) === 'c' && (property.substr(0, 5) === 'hr_c_' || property.substr(0, 7) === 'html_c_')) {
						continue;
					}

					locked = cat_data.locked_properties.in_array(property);
					$rule_dialog = $rule_template.clone(true)
						.writeAttribute('disabled', locked)
						.writeAttribute('id', category + '-' + property + '-rule');

					$content
						.insert(new Element('div', {'data-property':property, style:'min-height:50px;'})
							.insert(new Element('div', {className:'property-label'})
								.update(new Element('strong').update(cat_data.properties[property]))
								.insert(new Element('img', {src:'[{$dir_images}]icons/silk/information.png', title:'[{isys type="lang" ident="LC__SETTINGS__CMDB__VALIDATION__LOCKED_PROPERTY" p_bHtmlEncode=false}]', className:'vam mouse-help ' + (locked ? '' : 'hide')})))
							.insert(new Element('div', {className:'fl'})
								.insert(new Element('button', {type:'button', className:'btn btn-small mandatory'}).update(new Element('span').update('[{isys type="lang" ident="LC__SETTINGS__CMDB__VALIDATION__BUTTON__MANDATORY"}]')))
								.insert(new Element('button', {type:'button', className:'btn btn-small unique-object ml5'}).update(new Element('span').update('[{isys type="lang" ident="LC__SETTINGS__CMDB__VALIDATION__BUTTON__UNIQUE_OBJ"}]')))
								.insert(new Element('button', {type:'button', className:'btn btn-small unique-object-type ml5'}).update(new Element('span').update('[{isys type="lang" ident="LC__SETTINGS__CMDB__VALIDATION__BUTTON__UNIQUE_OBJTYPE"}]')))
								.insert(new Element('button', {type:'button', className:'btn btn-small unique-global ml5'}).update(new Element('span').update('[{isys type="lang" ident="LC__SETTINGS__CMDB__VALIDATION__BUTTON__UNIQUE_GLOBAL"}]')))
								.insert(new Element('br'))
								.insert(new Element('label', {className:'display-block' + (locked?' opacity-30':'')}).update('[{isys type="lang" ident="LC__SETTINGS__CMDB__VALIDATION__ATTRIBUTE_NEEDS_TO"}]').insert($rule_dialog))
								.insert(new Element('label', {className:'display-block mt10' + (locked?' opacity-30':'')}).update('[{isys type="lang" ident="LC__SETTINGS__CMDB__VALIDATION__REGEX_LABEL"}]').insert(new Element('input', {id:category + '-' + property + '-parameter', className:'inputDialog normal regex-field ml5', type:'text', disabled:locked}).setValue('(.*)')).hide())
								.insert(new Element('br'))
								.insert(new Element('div', {className:'textarea-selection'})
									.update(new Element('label', {className:'display-block'})
										.update(new Element('input', {type:'checkbox', className:'checkbox mr5', name:category + '-' + property + '-as-select'}))
										.insert('[{isys type="lang" ident="LC__SETTINGS__CMDB__VALIDATION__TEXTAREA_AS_DROPDOWN"}]'))
									.insert(new Element('textarea', {id:category + '-' + property + '-values', className:'input value-field mb5', style:'height:100px; resize:vertical;', disabled:locked})).hide()))
							.insert(new Element('br', {className:'cb'})))
						.insert(new Element('hr', {className:'cb', style:'margin:10px 0;'}));
				}
			}

			// Go ahead and hide all "unique per object" buttons, because they're all disabled and only confuse the user. @see  ID-1237
			if (unique_obj_disabled) {
				$content.select('button.unique-object').invoke('hide');
			}

			process_preselection(category);

			if (ajax_counter < 0) {
				$category.down('img.toggle').writeAttribute('src', '[{$dir_images}]icons/silk/bullet_arrow_down.png');
				$content.show();
			}

			$content.down('hr:last-child').remove();
		};

		var update_property_rules = function (category, property) {
			var $property_div = $(category).down('div[data-property="' + property + '"]'),
				$buttons = $property_div.select('button.btn-green'),
				rule = $property_div.down('.rule-selector').getValue(),
				regex = $property_div.down('.regex-field').getValue(),
				i;

			if (! category_configuration[category].rules.hasOwnProperty('config')) {
				category_configuration[category].rules.config = {};
			}

			if (Object.isArray(category_configuration[category].rules.config)) {
				category_configuration[category].rules.config = {};
			}

			if (! category_configuration[category].rules.config.hasOwnProperty(property)) {
				category_configuration[category].rules.config[property] = {check:null};
			}

			category_configuration[category].rules.config[property].check = {mandatory:false, unique_obj:false, unique_objtype:false, unique_global:false, validation:[]};

			for (i in $buttons) {
				if ($buttons.hasOwnProperty(i)) {
					if ($buttons[i].hasClassName('mandatory')) {
						category_configuration[category].rules.config[property].check.mandatory = true;
					} else if ($buttons[i].hasClassName('unique-object')) {
						category_configuration[category].rules.config[property].check.unique_obj = true;
					} else if ($buttons[i].hasClassName('unique-object-type')) {
						category_configuration[category].rules.config[property].check.unique_objtype = true;
					} else if ($buttons[i].hasClassName('unique-global')) {
						category_configuration[category].rules.config[property].check.unique_global = true;
					}
				}
			}

			if (rule != '-1') {
				category_configuration[category].rules.config[property].check.validation[0] = rule;
				category_configuration[category].rules.config[property].check.validation[1] = [];

				if (rule === 'FILTER_VALIDATE_REGEXP') {
					category_configuration[category].rules.config[property].check.validation[1] = {options: {regexp: regex}};
				} else if (rule === 'VALIDATE_BY_TEXTFIELD') {
					category_configuration[category].rules.config[property].check.validation[1] = {
						value: $property_div.down('textarea').getValue(),
						'as-select': $property_div.down('input.checkbox').checked
					};
				}
			}
		};

		var process_preselection = function (category) {
			var $category = $(category),
				$properties = $category.select('div[data-property]'),
				cat_data = category_configuration[category],
				checks,
				i,
				property;

			for (i in $properties) {
				if ($properties.hasOwnProperty(i)) {
					property = $properties[i].readAttribute('data-property');

					if (cat_data.rules.config.hasOwnProperty(property)) {
						if (cat_data.rules.config[property].hasOwnProperty('check')) {
							checks = cat_data.rules.config[property].check;

							if (checks.hasOwnProperty('mandatory') && checks.mandatory) {
								$properties[i].down('button.mandatory').addClassName('btn-green');
							}

							if (checks.hasOwnProperty('unique_obj') && checks.unique_obj) {
								$properties[i].down('button.unique-object').addClassName('btn-green');
							}

							if (checks.hasOwnProperty('unique_objtype') && checks.unique_objtype) {
								$properties[i].down('button.unique-object-type').addClassName('btn-green');
							}

							if (checks.hasOwnProperty('unique_global') && checks.unique_global) {
								$properties[i].down('button.unique-global').addClassName('btn-green');
							}

							if (checks.hasOwnProperty('validation') && Object.isArray(checks.validation) && checks.validation.length == 2) {
								$(category + '-' + property + '-rule').setValue(checks.validation[0]);

								if (checks.validation[0] == 'FILTER_VALIDATE_REGEXP') {
									$(category + '-' + property + '-parameter').setValue(checks.validation[1].options.regexp).up('label').show();
								} else if (checks.validation[0] == 'VALIDATE_BY_TEXTFIELD') {
									$(category + '-' + property + '-values').setValue(checks.validation[1]['value']).up('.textarea-selection').show();

									if (checks.validation[1]['as-select']) {
										$(category + '-' + property + '-values').previous('label.display-block').down('input').setValue('1');
									}
								}
							}
						}
					}
				}
			}
		};

		// Set the global observers.
		$category_add.on('click', function () {
			var value = $category_select.getValue(),
				title = $category_select.down('option:selected').innerHTML,
				cat_type,
				cat_id = value.substr(1);

			switch (value.substr(0, 1)) {
				case 'g': cat_type = '[{$smarty.const.C__CMDB__CATEGORY__TYPE_GLOBAL}]'; break;
				case 's': cat_type = '[{$smarty.const.C__CMDB__CATEGORY__TYPE_SPECIFIC}]'; break;
				case 'c': cat_type = '[{$smarty.const.C__CMDB__CATEGORY__TYPE_CUSTOM}]'; break;
			}

			if ($configuration_list.down('div#' + value)) {
				$configuration_list.down('div#' + value).highlight();
				idoit.Notify.info('[{isys type="lang" ident="LC__SETTINGS__CMDB__VALIDATION__CATEGORY_HAS_ALREADY_BEEN_SELECTED"}]', {life:5});
			} else {
				// Load the properties (and possible configuration) via Ajax.
				new Ajax.Request('?call=validate_field&ajax=1&func=get_validation_by_category', {
					method:'post',
					parameters: {
						cat_type:cat_type,
						cat_id:cat_id
					},
					onSuccess:function (transport) {
						ajax_counter --;
						var json = transport.responseJSON;

						if (! is_json_response(transport, true)) {
							return;
						}

						category_configuration[value] = json.data;

						$configuration_list
							.insert(new Element('div', {id:value, className:'border mt10 category-container'})
								.update(new Element('h4', {className:'gradient p5 mouse-pointer'})
									.update(new Element('button', {type:'button', className:'btn btn-small close fr', style:'margin-top:-2px'}).update(new Element('img', {src:'[{$dir_images}]icons/silk/cross.png', alt:'x'})))
									.insert(new Element('img', {src:'[{$dir_images}]icons/silk/bullet_arrow_right.png', alt:'v', className:'mr5 toggle'}))
									.insert(new Element('span').update(title)))
								.insert(new Element('div', {className:'border-top p5', style:'display:none;'})
									.update(new Element('img', {src:'[{$dir_images}]ajax-loading.gif', className:'vam mr5'}))
									.insert(new Element('span', {className:'vam'}).update('[{isys type="lang" ident="LC__UNIVERSAL__LOADING"}]'))));

						display_rules(value);
					}.bind({value:value})
				});
			}
		});

		// Remove a category configuration.
		$configuration_list.on('click', 'button.close', function (ev) {
			var category = ev.findElement('button').up('.category-container').remove().id;

			delete category_configuration[category];
		});

		// Toggle the unique and mandatory buttons.
		$configuration_list.on('click', '.category-container button:not(.close)', function (ev) {
			var $property_div = ev.findElement('button').toggleClassName('btn-green').up('div[data-property]'),
				category = $property_div.up('.category-container').readAttribute('id'),
				property = $property_div.readAttribute('data-property');

			update_property_rules(category, property);
		});

		// Toggle the "regular expression" input.
		$configuration_list.on('change', 'select.rule-selector', function (ev) {
			var $select = ev.findElement('select'),
				value = $select.getValue(),
				category = $select.up('.category-container').readAttribute('id'),
				property = $select.up('div[data-property]').readAttribute('data-property');

			if (value === 'FILTER_VALIDATE_REGEXP') {
				$select.up('label').next('label').show();
			} else {
				$select.up('label').next('label').hide();
			}

			if (value === 'VALIDATE_BY_TEXTFIELD') {
				$select.up('label').next('div.textarea-selection').show();
			} else {
				$select.up('label').next('div.textarea-selection').hide();
			}

			update_property_rules(category, property);
		});

		// Toggle the unique and mandatory buttons.
		$configuration_list.on('change', 'textarea.value-field,input.checkbox', function (ev) {
			var $property_div = ev.findElement('div[data-property]'),
				category = $property_div.up('.category-container').readAttribute('id'),
				property = $property_div.readAttribute('data-property');

			update_property_rules(category, property);
		});

		// If the regular expression is changed, we update call the "update_property_rules" function.
		$configuration_list.on('change', 'input.regex-field', function (ev) {
			var $input = ev.findElement('input').removeClassName('error'),
				value = $input.getValue(),
				$property_div = $input.up('div[data-property]'),
				property = $property_div.readAttribute('data-property'),
				category = $property_div.up('.category-container').readAttribute('id');

			try {
				new RegExp(value).test('test');
				update_property_rules(category, property);
			} catch (e) {
				$input.addClassName('error');
				idoit.Notify.error('[{isys type="lang" ident="LC__SETTINGS__CMDB__VALIDATION__REGEX_SYNTAX_ERROR"}]');
			}
		});

		// Show and hide the category configurations.
		$configuration_list.on('click', 'h4', function (ev) {
			var $headline = ev.findElement('h4'),
				$container = $headline.next('div').toggle();

			if ($container.visible()) {
				$headline.down('img.toggle').writeAttribute('src', '[{$dir_images}]icons/silk/bullet_arrow_down.png');
			} else {
				$headline.down('img.toggle').writeAttribute('src', '[{$dir_images}]icons/silk/bullet_arrow_right.png');
			}
		});

		// Refresh the validation cache.
		$cache_button.on('click', function () {
			$cache_button.down('img').writeAttribute('src', '[{$dir_images}]ajax-loading.gif');

			new Ajax.Request('?call=validate_field&ajax=1&func=reset_validation_cache', {
				method: 'post',
				onSuccess: function (transport) {
					var json = transport.responseJSON,
						$message = $cache_button.next('p').removeClassName('error').removeClassName('note');

					$cache_button.down('img').writeAttribute('src', '[{$dir_images}]icons/silk/arrow_refresh.png');

					if (json.success) {
						$message.addClassName('note')
							.update(new Element('span', {className: 'vam'}).update(json.data))
							.insert({top: new Element('img', {src: '[{$dir_images}]icons/silk/tick.png', className: 'mr5 vam'})});
					} else {
						$message.addClassName('error')
							.update(new Element('span', {className: 'vam'}).update(json.message))
							.insert({top: new Element('img', {src: '[{$dir_images}]icons/silk/cross.png', className: 'mr5 vam'})});
					}

					$message.show();
				}});
		});

		// The save-logic
		$save_button.on('click', function () {
			new Ajax.Request('?call=validate_field&ajax=1&func=save_validation_configuration', {
				method: 'post',
				parameters: {
					configuration:Object.toJSON(category_configuration)
				},
				onSuccess: function (transport) {
					var json = transport.responseJSON;

					if (! is_json_response(transport, true)) {
						return;
					}

					if (json.success) {
						idoit.Notify.success('[{isys type="lang" ident="LC__INFOBOX__DATA_WAS_SAVED"}]');
					} else {
						idoit.Notify.error(json.message, {sticky:true});
					}
				}
			});
		});

		var check_regex = function () {
			var $pattern = $('cmdb-validation-regex-pattern').removeClassName('error'),
				$search = $('cmdb-validation-regex-search'),
				$result = $('cmdb-validation-regex-result').removeClassName('error').removeClassName('success'),
				regex = $pattern.getValue().strip(),
				regex_result;

			if (regex.substr(0, 1) !== '^') {
				regex = '^' + regex;
			}

			if (regex.substr(-1, 1) !== '$') {
				regex += '$';
			}

			try {
				regex_result = new RegExp(regex).exec($search.getValue().strip());

				if (regex_result) {
					$result.addClassName('success').update('[{isys type="lang" ident="LC__SETTINGS__CMDB__VALIDATION__REGEX_CHECK_SUCCESS"}]');
				} else {
					$result.addClassName('error').update('[{isys type="lang" ident="LC__SETTINGS__CMDB__VALIDATION__REGEX_CHECK_FAILURE"}]');
				}
			} catch (e) {
				$pattern.addClassName('error');
				$result.addClassName('error').update(e);
				idoit.Notify.error('[{isys type="lang" ident="LC__SETTINGS__CMDB__VALIDATION__REGEX_SYNTAX_ERROR"}]');
			}
		};

		$('cmdb-validation-regex-pattern', 'cmdb-validation-regex-search')
			.invoke('observe', 'blur', check_regex)
			.invoke('observe', 'keydown',function (ev) {
				if (ev.keyCode == Event.KEY_RETURN) {
					ev.preventDefault();

					check_regex();
				}
			});

		$('cmdb-validation-regex-button').observe('click', check_regex);

		$('cmdb-validation').select('h3').invoke('on', 'click', function (ev) {
			var $headline = ev.findElement('h3'),
				$container = $headline.next('div');

			if ($container.visible()) {
				$headline.down('img').writeAttribute('src', '[{$dir_images}]icons/silk/bullet_arrow_right.png');
				$container.hide();
			} else {
				$headline.down('img').writeAttribute('src', '[{$dir_images}]icons/silk/bullet_arrow_down.png');
				$container.show();
			}
		});

		// Finally we load the configured validation rules.
		[{foreach $configured_categories as $l_category}]
		ajax_counter ++;
		$category_select.setValue('[{$l_category}]');
		$category_add.simulate('click');
		[{/foreach}]

		$category_select.setValue('g1');
	})();
</script>