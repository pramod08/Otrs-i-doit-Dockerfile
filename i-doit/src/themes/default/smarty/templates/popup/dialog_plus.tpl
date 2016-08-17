<div id="popup-dialog-plus">
	<h3 class="popup-header">
		<img class="fr mouse-pointer popup-closer" alt="x" src="[{$dir_images}]prototip/styles/default/close.png">
		<span>[{isys type="lang" ident="LC__POPUP__DIALOG_PLUS__TITLE"}]</span>
	</h3>

	<div class="popup-content">
		<div id="items"></div>

		<div class="p5 border-top">
			[{isys type="f_text" name="popup-dialog-plus-new-value" p_strClass="input-small" p_strPlaceholder="LC__NAVIGATION__NAVBAR__NEW_TOOLTIP" p_bInfoIconSpacer=0}]

			<button type="button" id="popup-dialog-plus-add-new-value" class="btn">
				<img src="[{$dir_images}]icons/silk/add.png" class="mr5"/>
				<span>[{isys type="lang" ident="LC__UNIVERSAL__BUTTON_ADD"}]</span>
			</button>
		</div>
	</div>

	<div class="popup-footer">
		<button type="button" id="popup-dialog-plus-save" class="btn mr5">
			<img src="[{$dir_images}]icons/silk/tick.png" class="mr5"/><span>[{isys type="lang" ident="LC__UNIVERSAL__BUTTON_SAVE"}]</span>
		</button>
		<button type="button" class="btn popup-closer">
			<img src="[{$dir_images}]icons/silk/cross.png" class="mr5"/><span>[{isys type="lang" ident="LC__UNIVERSAL__BUTTON_CANCEL"}]</span>
		</button>
	</div>
</div>

<script type="text/javascript">
	// @todo  Refactor all of the JS into an own small class.

	(function () {
		"use strict";

		var $popup = $('popup-dialog-plus'),
			$input_new_value = $('popup-dialog-plus-new-value'),
			$button_new_value = $('popup-dialog-plus-add-new-value'),
			$button_save = $('popup-dialog-plus-save'),
			$items = $popup.down('#items'),
			objID = parseInt('[{$cat_table_object|default:0}]');

		$input_new_value.on('keydown', function (ev) {
			if (ev.keyCode == Event.KEY_RETURN) {
				Event.stop(ev);
				idoit.callbackManager.triggerCallback('dialogplus_add_new_value');
			}
		});

		$button_new_value.on('click', function() {
			idoit.callbackManager.triggerCallback('dialogplus_add_new_value');
		});

		$button_save.on('click', function () {
			idoit.callbackManager.triggerCallback('dialogplus_save_and_close');
		});

		// Close the popup, when clicking ".popup-closer" elements.
		$popup.select('.popup-closer').invoke('on', 'click', function() {
			popup_close();
		});

		// We need this snippet to size the content area correctly, so we don't scroll the header and footer as well. Also the "undeletable" profiles get disabled.
		$popup.down('.popup-content').setStyle({height: ($popup.getHeight() - ($popup.down('.popup-header').getHeight() + $popup.down('.popup-footer').getHeight())) + 'px'});

		var item_div = $('items'),
			selected = $F('[{$self}]'),
			parent_id = 0;

		if ($('[{$parent}]')) {
			parent_id = $F('[{$parent}]');
		}

		var edit_dialog = function edit_dialog (ev) {
			var $el = ev.findElement(),
				div = $el.up('div'),
				edit_icon = div.down('span.edit'),
				title_span = div.down('span.value');

			if (! $el.up('div[data-id]')) {
				return;
			}

			if ($el.up('div[data-id]').readAttribute('data-id') == '-') {
				return;
			}

			if (! div.readAttribute('data-constant').blank()) {
				// This field holds a constant and may not be edited!
				return;
			}

			div
				.insert(new Element('input', {type:'text', className: 'input input-small', value: (title_span.textContent || title_span.innerHTML), onKeyDown: 'if (event.keyCode == Event.KEY_RETURN) {Event.stop(event);}'}))
				.down('input.input')
				.focus();

			// Remove the title span and the edit-icon.
			title_span.remove();
			edit_icon.remove();

			// Restore the view.
			div
				.insert(new Element('span', {className: 'save'}))
				.down('.save')
				.on('click', update_field);
		};

		var update_field = function update_field () {
			var div = this.up('div'),
				title = div.down('input.input').getValue();

			new Ajax.Request('?call=combobox&func=save_field&ajax=1',
				{
					parameters:{
						'table':'[{$table}]',
						'id': div.readAttribute('data-id'),
						'title': title
					},
					method:'post',
					onSuccess:function (transport) {
						var json = transport.responseJSON;

						if (json.success)
						{
							div.down('input.input').remove();
							div.down('span.save').remove();

							div.down('label').insert(new Element('span', {className: 'value'}).update(title));
							div.insert(new Element('span', {className: 'edit'}));

							new Effect.Highlight(div, {startcolor: '#ddffdd', endcolor: '#eeeeee'});
						}
						else
						{
							new Effect.Highlight(div, {startcolor: '#ffdddd', endcolor: '#eeeeee'});
						}
					}.bind(this)
				});
		};

		var add_new_value = function add_new_value () {
			var value = $input_new_value.getValue().strip(),
				radiobutton = $$('#items input[type="radio"]');

			if (! value.blank()) {
				// This is necessary for the IE. Yeah, I know...
				if (radiobutton && radiobutton.length > 0) {
					radiobutton.each(function (el) {
						if (el.hasAttribute('checked')) {
							el.removeAttribute('checked');
						}
						el.checked = false;
						el.simulate('blur');
					});
				}

				item_div.insert(new Element('div', {'data-id':'-', 'data-constant': ''}).update('<label><input type="radio" name="selection" class="vam" checked="checked" /> <span class="vam value">' + value + '</span></label>'));

				$input_new_value.setValue('');

				[{if $multiselect}]
				$popup.select('input[type="radio"]').invoke('hide').invoke('setValue', '');
				[{/if}]
			}

			$input_new_value.focus();
		};

		var save_and_close = function save_and_close () {
			var items = [],
				classIterator,
				url = '?call=combobox&func=save&ajax=1',
				parameters;

			// Save all changes if the save for each field has not been triggered
			item_div.select('div').each(function ($div) {
				if ($div.down('input.input')) {
					$div.down('span.save').simulate('click');
				}

				// At first we gather all the elements (including their sorting).
				items.push({
					'id': $div.readAttribute('data-id'),
					'name': $div.down('span').textContent || $div.down('span').innerHTML,
					'checked': $div.down('input').checked
				});
			}.bind(this));

			classIterator = $('[{$self}]').name.replace(/\[.*/, '');

			parameters = {
				'parent': '{"selected_id":' + parent_id + ',"table":"[{$parent_table}]"}',
				'data': Object.toJSON(items),
				'table': '[{$table}]',
				'condition': "[{$condition}]"
			};

			// This will be used when filling CMDB categories by dialog+.
			if (objID > 0) {
				url = '?call=combobox&func=save_cat_data&ajax=1';
				parameters.cat_table_object = objID;
			}

			// And now we save the data.
			new Ajax.Request(url,
				{
					parameters:parameters,
					method:'post',
					onSuccess:function (transport) {
						var callback_func = "[{$callback_accept}]",
							selected_id = transport.responseText,
							self = $('[{$self}]'),
							current_id = $('[{$self}]').getValue();

						new Ajax.Request('?call=combobox&func=load&ajax=1',
							{
								parameters:{
									'table':'[{$table}]',
									'parent_table':'[{$parent_table}]',
									'parent_table_id':parent_id,
									'condition': "[{$condition}]"
								},
								method:'post',
								onSuccess:function (transport) {
									var json = [],
										option_ids = [],
										index = 0;

									// Empty content for sbox
									self.update('');

									// Transform to json
									if (transport.responseText != '[]') {
										json = $H(transport.responseJSON);
									}

									// Add null parameter
									[{if ! $notnull_parameter}]
									self.insert(new Element('option', {value: '-1'}).update('-'));
									index ++;
									[{/if}]

									// Add options to sbox
									json.each(function(item) {
										var itemkey = item.key.replace(/^\s+|\s+$/g, '');

										option_ids.push(itemkey);

										self.insert(new Element('option', {value: itemkey}).update(item.value));

										if (selected_id == itemkey) {
											// Set value
											self.setValue(itemkey);

											/*
											 * Let us check for changed selection before triggering change event to fill the child.
											 * Otherwise we would lose selection in child field
											 */
											[{if ('[{$child}]' != '' && '[{$child_table}]')}]
											if (selected_id != current_id) {
												self.simulate('change');
											}
											[{/if}]
										}
										index++;
									});

									// ID-2822 Bugfix
									self.fire('chosen:updated');

									// Fire Custom Dialog Plus After Save Event
									self.fire('dialog-plus:afterSave', {
										'classIterator': classIterator,
										'selectBox': self,
										'options': json,
										'parent': [{if !$parent}]0[{else}]1[{/if}]
									});

									[{if $onComplete|default:FALSE}][{$onComplete}][{/if}]
								}
							});

						try {
							if (callback_func != '') {
								eval(callback_func);
							}
						} catch (e) {
							idoit.Notify.error(e);
						}

						popup_close();
					}.bind(this)
				});
		}.bind(this);

		// Load the items.
		new Ajax.Request('?call=combobox&func=load_extended&ajax=1',
			{
				parameters:{
					'table':'[{$table}]',
					'parent_table':'[{$parent_table}]',
					'parent_table_id':parent_id,
					'condition': "[{$condition}]"
				},
				method:'post',
				onSuccess:function (transport) {
					var json = transport.responseJSON,
						selection,
						index,
						item,
						edit;

					// When we get no data, we should not run the rest of the code.
					if (transport.responseText == '[]') {
						return null;
					}

					for (index in json) {
						if (json.hasOwnProperty(index)) {
							item = json[index];
							index = index.replace(/\s+$/,'');
							selection = '';
							edit = '';

							if (index == selected) {
								selection = 'checked="checked"';
							}

							if (item.constant == '') {
								edit = '<span class="edit vam"></span>';
							}

							item_div.insert(new Element('div', {'data-id':index, 'data-constant':item.constant}).update('<label><input type="radio" name="selection" ' + selection + ' class="vam" /> <span class="value vam">' + item.title + '</span></label>' + edit));
						}
					}

					[{if $multiselect}]
					$popup.select('input[type="radio"]').invoke('hide').invoke('setValue', '');
					[{/if}]
				}
			});

		// Focus the input-field for direct input.
		$input_new_value.focus();

		$items.on('click', 'span.edit', edit_dialog);
		$items.on('dblclick', 'span.value', edit_dialog);

		idoit.callbackManager
			.registerCallback('dialogplus_add_new_value', add_new_value)
			.registerCallback('dialogplus_save_and_close', save_and_close);
	}());
</script>
<style type="text/css">
	#popup-dialog-plus {
		height: 100%;
	}

	div#items {
		overflow: auto;
		height: 495px;
		position: relative;
	}

	div#items div {
		background: #eee;
		margin: 5px;
		padding: 5px;
		position: relative;
	}

	div#items div span.edit,
	div#items div span.save {
		background: url('[{$dir_images}]icons/silk/table_edit.png');
		cursor: pointer;
		display: inline-block;
		height: 16px;
		width: 16px;
		top: 8px;
		right: 5px;
		position: absolute;
		vertical-align: middle;
	}

	div#items div span.save {
		background: url('[{$dir_images}]icons/silk/table_save.png');
		margin-top: 1px;
	}
</style>