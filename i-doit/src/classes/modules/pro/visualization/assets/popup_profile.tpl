[{isys_group name="tom.popup.visualization"}]
<div id="visualization-popup">
	<h3 class="popup-header">
		<img class="fr mouse-pointer popup-closer" alt="x" src="[{$dir_images}]prototip/styles/default/close.png">
		<span>[{isys type="lang" ident="LC__VISUALIZATION_PROFILES_DESCRIPTION"}]</span>
	</h3>

	<div class="popup-content">
		<div class="p5">
			<button type="button" id="visualization-popup-new-profile" class="btn" [{if !$edit_right}]disabled="disabled"[{/if}]>
				<img src="[{$dir_images}]icons/silk/add.png" class="mr5" /><span>[{isys type="lang" ident="LC__VISUALIZATION_PROFILES__NEW_PROFILE"}]</span>
			</button>
		</div>

		<ul id="visualization-popup-profile-list" class="border-top">
			[{foreach $profiles as $profile}]
			<li>
				<button type="button" class="btn mr5 default" data-profile-id="[{$profile.isys_visualization_profile__id}]" title="[{isys type="lang" ident="LC__VISUALIZATION_PROFILES__SET_DEFAULT_PROFILE"}]">
					[{if $profile.isys_visualization_profile__id == $default_profile}]
					<img src="[{$dir_images}]icons/silk/bullet_green.png" />
					[{else}]
					<img src="[{$dir_images}]icons/silk/bullet_red.png" />
					[{/if}]
				</button><button type="button" class="btn mr5 edit" data-profile-id="[{$profile.isys_visualization_profile__id}]" data-const="[{$profile.isys_visualization_profile__const}]" title="[{isys type="lang" ident="LC__VISUALIZATION_PROFILES__EDIT_PROFILE"}]" [{if !$edit_right}]disabled="disabled"[{/if}]>
					<img src="[{$dir_images}]icons/silk/pencil.png" />
				</button><button type="button" class="btn mr5 duplicate" data-profile-id="[{$profile.isys_visualization_profile__id}]" data-const="[{$profile.isys_visualization_profile__const}]" title="[{isys type="lang" ident="LC__VISUALIZATION_PROFILES__DUPLICATE_PROFILE"}]" [{if !$edit_right}]disabled="disabled"[{/if}]>
					<img src="[{$dir_images}]icons/silk/page_white_copy.png" />
				</button>
				<strong>[{$profile.isys_visualization_profile__title}]</strong>
			</li>
			[{/foreach}]
		</ul>

		<div id="visualization-popup-form">
			<table class="contentTable pr5">
				<tr>
					<td class="key">[{isys type="f_label" name="C__VISUALIZATION_PROFILES__TITLE" ident="LC__VISUALIZATION_PROFILES__FORM__TITLE"}]</td>
					<td class="value vat">[{isys type="f_text" name="C__VISUALIZATION_PROFILES__TITLE"}]</td>
					<td rowspan="3" style="width:450px; position:relative; background:#eee;" class="vat border border-ccc">
						<span class="preview grey">[{isys type="lang" ident="LC__VISUALIZATION_PROFILES__PREVIEW"}]</span>

						<div id="visualization-popup-preview" class="border"></div>
					</td>
				</tr>
				<tr>
					<td class="key">[{isys type="f_label" name="C__VISUALIZATION_PROFILES__SHOW_PATH" ident="LC__VISUALIZATION_PROFILES__FORM__SHOW_PATH"}]</td>
					<td class="value vat">[{isys type="f_dialog" name="C__VISUALIZATION_PROFILES__SHOW_PATH"}]</td>
				</tr>
				<tr>
					<td class="key">[{isys type="f_label" name="C__VISUALIZATION_PROFILES__SHOW_TOOLTIP" ident="LC__VISUALIZATION_PROFILES__FORM__SHOW_TOOLTIP"}]</td>
					<td class="value vat">[{isys type="f_dialog" name="C__VISUALIZATION_PROFILES__SHOW_TOOLTIP"}]</td>
				</tr>
				<tr>
					<td class="key">[{isys type="f_label" name="C__VISUALIZATION_PROFILES__MASTER_TOP" ident="LC__VISUALIZATION_PROFILES__FORM__MASTER"}]</td>
					<td class="value vat">[{isys type="f_dialog" name="C__VISUALIZATION_PROFILES__MASTER_TOP"}]</td>
				</tr>
			</table>

			<div id="visualization-popup-tab-container">
				<ul id="visualization-popup-tabs" class="browser-tabs m0 gradient pt5 border-top">
					<li><a href="#visualization-popup-tab-a">[{isys type="lang" ident="LC__VISUALIZATION_PROFILES__TAB__NODE_CONFIGURATION"}]</a></li>
					<li><a href="#visualization-popup-tab-b">[{isys type="lang" ident="LC__VISUALIZATION_PROFILES__TAB__DEFAULT_CONFIGURATION"}]</a></li>
					<li><a href="#visualization-popup-tab-c">[{isys type="lang" ident="LC__VISUALIZATION_PROFILES__TAB__OBJECT_INFO_CONFIGURATION"}]</a></li>
				</ul>
				<div id="visualization-popup-tab-a">

					<!-- Visualisierungsoptionen -->

					<table class="contentTable border-bottom pt10 pb0">
						<tr>
							<td class="key">[{isys type="f_label" name="C__VISUALIZATION_PROFILES__WIDTH" ident="LC__VISUALIZATION_PROFILES__FORM__WIDTH"}]</td>
							<td class="value">[{isys type="f_count" name="C__VISUALIZATION_PROFILES__WIDTH"}]</td>
						</tr>
						<tr>
							<td class="key pb10">[{isys type="f_label" name="C__VISUALIZATION_PROFILES__HIGHLIGHT_COLOR" ident="LC__VISUALIZATION_PROFILES__FORM__HIGHLIGHT_COLOR"}]</td>
							<td class="value pb10">[{isys type="f_text" name="C__VISUALIZATION_PROFILES__HIGHLIGHT_COLOR"}]</td>
						</tr>
						[{for $i=1 to 8}]
						<tr data-row="[{$i}]">
							<td class="key gradient border-top">[{isys type="checkbox" name="C__VISUALIZATION_PROFILES__R`$i`__ROW"}]</td>
							<td class="gradient border-top"></td>
						</tr>
						<tr class="row-[{$i}]">
							<td class="key border-top">[{isys type="f_label" name="C__VISUALIZATION_PROFILES__R`$i`__OPTION" ident="LC__VISUALIZATION_PROFILES__FORM__CONTENT"}]</td>
							<td class="value border-top">[{isys type="f_dialog" name="C__VISUALIZATION_PROFILES__R`$i`__OPTION"}]</td>
						</tr>
						<tr class="row-[{$i}]">
							<td class="key">[{isys type="f_label" name="C__VISUALIZATION_PROFILES__R`$i`__FILLCOLOR" ident="LC__VISUALIZATION_PROFILES__FORM__BACKGROUND"}]</td>
							<td>
								[{isys type="f_text" name="C__VISUALIZATION_PROFILES__R`$i`__FILLCOLOR"}]
								[{isys type="f_button" name="C__VISUALIZATION_PROFILES__R`$i`__FILLCOLOR_OBJ_TYPE"}]
							</td>
						</tr>
						<tr class="row-[{$i}]">
							<td class="key">[{isys type="f_label" name="C__VISUALIZATION_PROFILES__R`$i`__FONTCOLOR" ident="LC__VISUALIZATION_PROFILES__FORM__FONT"}]</td>
							<td class="value">
								[{isys type="f_text" name="C__VISUALIZATION_PROFILES__R`$i`__FONTCOLOR"}]
								[{isys type="f_button" name="C__VISUALIZATION_PROFILES__R`$i`__FONT_BOLD"}]
								[{isys type="f_button" name="C__VISUALIZATION_PROFILES__R`$i`__FONT_ITALIC"}]
								[{isys type="f_button" name="C__VISUALIZATION_PROFILES__R`$i`__FONT_UNDERLINE"}]
								[{isys type="f_button" name="C__VISUALIZATION_PROFILES__R`$i`__FONT_ALIGN_MIDDLE"}]
								[{isys type="f_button" name="C__VISUALIZATION_PROFILES__R`$i`__FONT_ALIGN_RIGHT"}]
							</td>
						</tr>
						[{/for}]
					</table>
				</div>
				<div id="visualization-popup-tab-b">

					<!-- Standardwerte -->

					<table class="contentTable pt10">
						<tr>
							<td class="key">[{isys type="f_label" name="C__VISUALIZATION_PROFILES__DEFAULT_ORIENTATION" ident="LC__VISUALIZATION_PROFILES__FORM__DEFAULT_ORIENTATION"}]</td>
							<td class="value">[{isys type="f_dialog" name="C__VISUALIZATION_PROFILES__DEFAULT_ORIENTATION"}]</td>
						</tr>
						<tr>
							<td class="key">[{isys type="f_label" name="C__VISUALIZATION_PROFILES__DEFAULT_SERVICE_FILTER" ident="LC__VISUALIZATION_PROFILES__FORM__DEFAULT_SERVICE_FILTER"}]</td>
							<td class="value">[{isys type="f_dialog" name="C__VISUALIZATION_PROFILES__DEFAULT_SERVICE_FILTER"}]</td>
						</tr>
						<tr>
							<td class="key">[{isys type="f_label" name="C__VISUALIZATION_PROFILES__DEFAULT_OBJECT_TYPE_FILTER" ident="LC__VISUALIZATION_PROFILES__FORM__DEFAULT_OBJECT_TYPE_FILTER"}]</td>
							<td class="value">[{isys type="f_dialog_list" name="C__VISUALIZATION_PROFILES__DEFAULT_OBJECT_TYPE_FILTER"}]</td>
						</tr>
					</table>
				</div>
				<div id="visualization-popup-tab-c">

					<!-- Objektinformationen -->

					<div id="visualization-popup-form-property-selector" class="ml5 pt10"></div>
				</div>
			</div>
		</div>
	</div>

	<div class="popup-footer">
		<button type="button" class="btn mr5" id="visualization-popup-save" disabled="disabled">
			<img src="[{$dir_images}]icons/silk/disk.png" class="mr5" />
			<span>[{isys type="lang" ident="LC__VISUALIZATION_PROFILES__SAVE_PROFILE"}]</span>
		</button>

		<button type="button" class="btn" id="visualization-popup-cancel">
			<img src="[{$dir_images}]icons/silk/cross.png" class="mr5" />
			<span>[{isys type="lang" ident="LC__VISUALIZATION_PROFILES__CLOSE_POPUP"}]</span>
		</button>
	</div>
</div>

<script type="text/javascript">
	(function () {
		'use strict';

		var $popup = $('visualization-popup'),
			$content = $popup.down('.popup-content'),
			$new_profile_button = $('visualization-popup-new-profile'),
			$preview = $('visualization-popup-preview'),
			$profile_list = $('visualization-popup-profile-list'),
			$accept_button = $('visualization-popup-save'),
			$cancel_button = $('visualization-popup-cancel'),
			$popup_form = $('visualization-popup-form').hide(),
			colorpicker = {},
			current_profile = null,
			preview_styles = {
				sysid: 'SYSID_00000' + ('00000' + parseInt(Math.random() * 99999)).substr(-5),
				objid: '#' + parseInt(Math.random() * 9999),
				color: null,
				ip: '127.0.0.1',
				cmdbstatus: new Element('div', {className:'cmdb-marker', style:'background:#33C20A; height:9px;'}),
				icon: new Element('img', {src:'[{$dir_images}]icons/silk/drive.png', className:'vam mr5', style:'width:14px; height:14px;'}),
				defaults: ''
			};

		new Tabs('visualization-popup-tabs', {
			wrapperClass: 'browser-tabs',
			contentClass: 'result-tab-content',
			tabClass: 'text-shadow'
		});

		$$('input.js-color').each(function($el) {
			colorpicker[$el.id] = new jscolor.color($el, {hash:true});
		});

		$$('button.toggle-button').invoke('on', 'click', function (ev) {
			var $button = ev.findElement('button').toggleClassName('btn-green');

			if ($button.hasClassName('text-align') && $button.hasClassName('btn-green')) {
				// In the case of the text-alignment we need to handle the buttons like radio buttons.
				$button.up('td').select('button.text-align').invoke('removeClassName', 'btn-green');
				// This is a bit stupid, but it's necessary.
				$button.addClassName('btn-green');
			}

			if ($button.id.substr(-20) == '__FILLCOLOR_OBJ_TYPE') {
				if ($button.hasClassName('btn-green')) {
					$button.previous('.js-color').disable()
				} else {
					$button.previous('.js-color').enable()
				}
			}
		});

		$popup_form.select('input.row-toggle').invoke('on', 'click', function (ev) {
			var $checkbox = ev.findElement('input');

			$popup_form.select('tr.row-' + $checkbox.up('tr').readAttribute('data-row')).invoke($checkbox.checked ? 'show' : 'hide');
		});

		$popup.select('.popup-closer').invoke('on', 'click', function () {
			idoit.callbackManager.triggerCallback('refresh-profile-dialog');

			popup_close();
		});

		// "Create new profile" button.
		$new_profile_button.on('click', function () {
			current_profile = null;

			reset_form();

			$profile_list.hide();

			if ($popup_form.visible()) {
				$popup.highlight({
					startcolor:'#d4ffde',
					endcolor:'#ffffff',
					restorecolor:'#ffffff'
				});
			} else {
				$popup_form.show();
			}

			update_preview();

			$accept_button.enable();
		});

		// We need this snippet to size the content area correctly, so we don't scroll the header and footer as well. Also the "undeletable" profiles get disabled.
		$content
			.setStyle({height: ($popup.getHeight() - ($popup.down('.popup-header').getHeight() + $popup.down('.popup-footer').getHeight())) + 'px'})
			.on('click', 'button.edit[data-const=""]', function (ev) {
				[{if !$edit_right}]return null;[{/if}]


				var $button = ev.findElement('button'),
					$img = $button.down('img').writeAttribute('src', '[{$dir_images}]ajax-loading.gif');

				current_profile = $button.readAttribute('data-profile-id');
				$cancel_button.down('span').update('[{isys type="lang" ident="LC__CMDB__BROWSER_OBJECT__BUTTON_CANCEL"}]');

				// Load the profiles configuration.
				new Ajax.Request('[{$ajax_url}]&func=load-profile-config', {
					parameters: {
						'profile-id': $button.readAttribute('data-profile-id')
					},
					onComplete: function (transport) {
						var json = transport.responseJSON,
							config, defaults, i, index, $checkbox, row;

						$img.writeAttribute('src', '[{$dir_images}]icons/silk/pencil.png');

						if (json.success) {
							try {
								config = (json.data.isys_visualization_profile__config || '{}').evalJSON();
								defaults = (json.data.isys_visualization_profile__defaults || '{}').evalJSON();

								reset_form(false);

								load_property_selector(json.data.isys_visualization_profile__obj_info_config || '{}');

								// Fill all the form fields, before displaying them.
								$('C__VISUALIZATION_PROFILES__TITLE').setValue(json.data.isys_visualization_profile__title);
								$('C__VISUALIZATION_PROFILES__WIDTH').setValue(config.width);
								colorpicker['C__VISUALIZATION_PROFILES__HIGHLIGHT_COLOR'].fromString(config['highlight-color'] || '538cdd');
								$('C__VISUALIZATION_PROFILES__SHOW_PATH').setValue(config['show-cmdb-path']);
								$('C__VISUALIZATION_PROFILES__SHOW_TOOLTIP').setValue(config['tooltip']);
								$('C__VISUALIZATION_PROFILES__MASTER_TOP').setValue(config.master_top);

								// Default values.
								$('C__VISUALIZATION_PROFILES__DEFAULT_ORIENTATION').setValue(defaults.orientation);
								$('C__VISUALIZATION_PROFILES__DEFAULT_SERVICE_FILTER').setValue(defaults['service-filter']);
								Event.fire($("C__VISUALIZATION_PROFILES__DEFAULT_OBJECT_TYPE_FILTER__selected_box").setValue(defaults['obj-type-filter'] || []), "chosen:updated");

								for (i = 1; i <= 8; i ++) {
									index = (i - 1);

									if (! Object.isUndefined(config.rows[index])) {
										row = config.rows[index];

										$checkbox = $('C__VISUALIZATION_PROFILES__R' + i + '__ROW').setValue(1);
										colorpicker['C__VISUALIZATION_PROFILES__R' + i + '__FILLCOLOR'].fromString(row.fillcolor || 'ffffff');
										colorpicker['C__VISUALIZATION_PROFILES__R' + i + '__FONTCOLOR'].fromString(row.fontcolor || '000000');
										$('C__VISUALIZATION_PROFILES__R' + i + '__OPTION').setValue(row.option || '[{$smarty.const.C__VISUALIZATION_PROFILE__OBJ_TITLE}]');

										if (row['fillcolor_obj_type'] || Object.isUndefined(row.fillcolor)) {
											$('C__VISUALIZATION_PROFILES__R' + i + '__FILLCOLOR_OBJ_TYPE').addClassName('btn-green');
											$('C__VISUALIZATION_PROFILES__R' + i + '__FILLCOLOR').disable();
										}

										// Font styles.
										if (row['font-bold']) {
											$('C__VISUALIZATION_PROFILES__R' + i + '__FONT_BOLD').addClassName('btn-green');
										}

										if (row['font-italic']) {
											$('C__VISUALIZATION_PROFILES__R' + i + '__FONT_ITALIC').addClassName('btn-green');
										}

										if (row['font-underline']) {
											$('C__VISUALIZATION_PROFILES__R' + i + '__FONT_UNDERLINE').addClassName('btn-green');
										}

										// Text alignment.
										if (row['font-align-middle']) {
											$('C__VISUALIZATION_PROFILES__R' + i + '__FONT_ALIGN_MIDDLE').addClassName('btn-green');
										}

										if (row['font-align-right']) {
											$('C__VISUALIZATION_PROFILES__R' + i + '__FONT_ALIGN_RIGHT').addClassName('btn-green');
										}

										if ($checkbox.checked) {
											$popup_form.select('tr.row-' + i).invoke('show');
										}
									}
								}

								update_preview();
							} catch (e) {
								idoit.Notify.error('[{isys type="lang" ident="LC__VISUALIZATION_PROFILES__ERROR_WHILE_PARSING_CONFIG"}]' + e, {sticky:true});
							}

							$profile_list.hide();

							if ($popup_form.visible()) {
								$popup.highlight({
									startcolor:'#d4ffde',
									endcolor:'#ffffff',
									restorecolor:'#ffffff'
								});
							} else {
								$popup_form.show();
							}

							$accept_button.enable();
						} else {
							// On failure:
							idoit.Notify.error(json.message, {sticky:true});
						}
					}
				});
			});

		$content.on('click', 'button.default', function (ev) {
			var $button = ev.findElement('button');

			$button.down('img').writeAttribute('src', '[{$dir_images}]ajax-loading.gif');

			new Ajax.Request('[{$ajax_url}]&func=set-profile-as-default', {
				parameters: {
					'profile-id':$button.readAttribute('data-profile-id')
				},
				onComplete: function (transport) {
					$content.select('button.default img').invoke('writeAttribute', 'src', '[{$dir_images}]icons/silk/bullet_red.png');
					$button.down('img').writeAttribute('src', '[{$dir_images}]icons/silk/bullet_green.png');
				}
			});
		});

		$content.on('click', 'button.duplicate', function (ev) {
			var $button = ev.findElement('button'),
				$strong = $button.next('strong');

			if (confirm(('[{isys type="lang" ident="LC__VISUALIZATION_PROFILES__DUPLICATE_CONFIRM" p_bHtmlEncode=false}]'.replace('%s', $strong.innerHTML)))) {
				$button.down('img').writeAttribute('src', '[{$dir_images}]ajax-loading.gif');

				new Ajax.Request('[{$ajax_url}]&func=duplicate-profile', {
					parameters: {
						'profile-id': $button.readAttribute('data-profile-id')
					},
					onComplete: function (transport) {
						var json = transport.responseJSON, $profile;

						$button.down('img').writeAttribute('src', '[{$dir_images}]icons/silk/page_white_copy.png');

						is_json_response(transport, true);

						if (json.success) {
							// Do the magic

							$profile = new Element('li')
								.update(
									new Element('button', {className:'btn mr5 default', type:'button', 'data-profile-id':json.data.isys_visualization_profile__id, title:'[{isys type="lang" ident="LC__VISUALIZATION_PROFILES__SET_DEFAULT_PROFILE"}]'[{if !$edit_right}],disabled:'disabled'[{/if}]}).update(
										new Element('img', {src:'[{$dir_images}]icons/silk/bullet_red.png'})
									)
								).insert(
									new Element('button', {className:'btn fr delete-profile', type:'button', 'data-profile-id':json.data.isys_visualization_profile__id, title:'[{isys type="lang" ident="LC__VISUALIZATION_PROFILES__DELETE_PROFILE"}]'[{if !$delete_right}],disabled:'disabled'[{/if}]}).update(
										new Element('img', {src:'[{$dir_images}]icons/silk/cross.png'})
									)
								).insert(
									new Element('button', {className:'btn mr5 edit', type:'button', 'data-profile-id':json.data.isys_visualization_profile__id, 'data-const':'', title:'[{isys type="lang" ident="LC__VISUALIZATION_PROFILES__EDIT_PROFILE"}]'[{if !$edit_right}],disabled:'disabled'[{/if}]}).update(
										new Element('img', {src:'[{$dir_images}]icons/silk/pencil.png'})
									)
								).insert(
									new Element('button', {className:'btn mr5 duplicate', type:'button', 'data-profile-id':json.data.isys_visualization_profile__id, 'data-const':'', title:'[{isys type="lang" ident="LC__VISUALIZATION_PROFILES__DUPLICATE_PROFILE"}]'[{if !$edit_right}],disabled:'disabled'[{/if}]}).update(
										new Element('img', {src:'[{$dir_images}]icons/silk/page_white_copy.png'})
									)
								).insert(
									new Element('strong').update(json.data.isys_visualization_profile__title)
								);

							$content.down('ul').insert($profile).down('li:last-child').highlight();
						} else {
							idoit.Notify.error(json.message, {sticky:true});
						}
					}
				});
			}
		});

		[{if $edit_right}]
		// Handle the "undeletable" profiles (these are defined by a constant)
		$content.select('button.edit[data-const!=""]').each(function ($el) {
			$el.disable().writeAttribute('title', '[{isys type="lang" ident="LC__VISUALIZATION_PROFILES__PROFILE_NOT_EDITABLE"}]')
				.down('img').writeAttribute('src', '[{$dir_images}]icons/silk/lock.png');
		});
		[{/if}]

		[{if $delete_right}]
		// Handle the user-specified profiles.
		$content.select('button.edit[data-const=""]').each(function ($el) {
			var delete_button = new Element('button', {
				type: 'button',
				className: 'btn fr delete-profile',
				'data-profile-id': $el.readAttribute('data-profile-id'),
				title: '[{isys type="lang" ident="LC__VISUALIZATION_PROFILES__DELETE_PROFILE"}]'
			}).update(new Element('img', {src: '[{$dir_images}]icons/silk/cross.png'}));

			$el.insert({before: delete_button});
		});

		// Handler for clicking the "delete" button.
		$content.on('click', 'button.delete-profile', function (ev) {
			var $button = ev.findElement('button'),
				$row = $button.up('li');


			if (confirm('[{isys type="lang" ident="LC__VISUALIZATION_PROFILES__DELETE_PROFILE_CONFIRMATION" p_bHtmlEncode=false}]')) {
				$button.down('img').writeAttribute('src', '[{$dir_images}]ajax-loading.gif');

				// Load the profiles configuration.
				new Ajax.Request('[{$ajax_url}]&func=delete-profile', {
					parameters: {
						'profile-id': $button.readAttribute('data-profile-id')
					},
					onComplete: function (transport) {
						var json = transport.responseJSON;

						is_json_response(transport, true);

						if (json.success) {
							// Simply remove the profile from the GUI.
							$row.remove();
						} else {
							idoit.Notify.error(json.message, {sticky:true});
						}
					}
				});
			}
		});
		[{/if}]

		$cancel_button.on('click', function () {
			if ($popup_form.visible()) {
				current_profile = null;
				$profile_list.show();
				$popup_form.hide();
				$accept_button.disable();
				$cancel_button.down('span').update('[{isys type="lang" ident="LC__VISUALIZATION_PROFILES__CLOSE_POPUP"}]');
			} else {
				$popup.down('.popup-closer').simulate('click');
			}
		});

		$accept_button.on('click', function () {
			var form_data = {},
				property_selector_data = $F('C__VISUALIZATION_PROFILES__OBJ_INFO_CONFIG__COMPLETE').evalJSON(),
				$property_selector_levels = $popup_form.select('[name^="lvls_raw"]');

			$popup_form.select('input[id],textarea[id],button[id].btn-green,select[id]').each(function ($el) {
				form_data[$el.id] = false;

				if ($el.tagName == 'BUTTON') {
					form_data[$el.id] = true;
				} else if ($el.tagName == 'INPUT' && $el.readAttribute('type') == 'checkbox' && $el.checked) {
					form_data[$el.id] = true;
				} else {
					form_data[$el.id] = $el.getValue();
				}
			});

			// Remove some of the property selector rules.
			delete form_data['C__VISUALIZATION_PROFILES__OBJ_INFO_CONFIG__HIDDEN'];
			delete form_data['C__VISUALIZATION_PROFILES__OBJ_INFO_CONFIG__HIDDEN_IDS'];

			// Here we collect the necessary property selector data.
			form_data['C__VISUALIZATION_PROFILES__OBJ_INFO_CONFIG'] = {
				'main_obj': property_selector_data.root,
				'lvls':{}
			};

			$property_selector_levels.each(function ($el) {
				var matches = $el.readAttribute('name').match(/^lvls_raw\[(\d+)\]\[(.*?)\]$/);

				if (matches == null) {
					return;
				}

				if (! form_data['C__VISUALIZATION_PROFILES__OBJ_INFO_CONFIG'].lvls[matches[1]]) {
					form_data['C__VISUALIZATION_PROFILES__OBJ_INFO_CONFIG'].lvls[matches[1]] = {};
				}

				form_data['C__VISUALIZATION_PROFILES__OBJ_INFO_CONFIG'].lvls[matches[1]][matches[2]] = $el.getValue().evalJSON();
			});

			$accept_button
				.disable()
				.down('img').writeAttribute('src', '[{$dir_images}]ajax-loading.gif')
				.next('span').update('[{isys type="lang" ident="LC__UNIVERSAL__LOADING"}]');

			new Ajax.Request('[{$ajax_url}]&func=save-profile-config', {
				parameters: {
					'profile-id': current_profile,
					'profile-config': Object.toJSON(form_data)
				},
				onComplete: function (transport) {
					var json = transport.responseJSON, $profile;

					is_json_response(transport, true);

					if (json.success) {
						$accept_button
							.down('img').writeAttribute('src', '[{$dir_images}]icons/silk/disk.png')
							.next('span').update('[{isys type="lang" ident="LC__VISUALIZATION_PROFILES__SAVE_PROFILE"}]');

						$cancel_button.down('span').update('[{isys type="lang" ident="LC__VISUALIZATION_PROFILES__CLOSE_POPUP"}]');

						if (json.data > 0) {
							// And add the new profile to the list.
							$profile = new Element('li').update(
								new Element('button', {className:'btn mr5 default', type:'button', 'data-profile-id':json.data, title:'[{isys type="lang" ident="LC__VISUALIZATION_PROFILES__SET_DEFAULT_PROFILE"}]'}).update(
									new Element('img', {src:'[{$dir_images}]icons/silk/bullet_red.png'})
								)
							).insert(
								new Element('button', {className:'btn fr delete-profile', type:'button', 'data-profile-id':json.data, title:'[{isys type="lang" ident="LC__VISUALIZATION_PROFILES__DELETE_PROFILE"}]'}).update(
									new Element('img', {src:'[{$dir_images}]icons/silk/cross.png'})
								)
							[{if $edit_right}]
							).insert(
								new Element('button', {className:'btn mr5 edit', type:'button', 'data-profile-id':json.data, 'data-const':'', title:'[{isys type="lang" ident="LC__VISUALIZATION_PROFILES__EDIT_PROFILE"}]'}).update(
									new Element('img', {src:'[{$dir_images}]icons/silk/pencil.png'})
								)
							[{/if}]
							).insert(
								new Element('button', {className:'btn mr5 duplicate', type:'button', 'data-profile-id':json.data, 'data-const':'', title:'[{isys type="lang" ident="LC__VISUALIZATION_PROFILES__DUPLICATE_PROFILE"}]'}).update(
									new Element('img', {src:'[{$dir_images}]icons/silk/page_white_copy.png'})
								)
							).insert(
								new Element('strong').update(form_data['C__VISUALIZATION_PROFILES__TITLE'])
							);

							$content.down('ul').insert($profile).down('li:last-child').highlight();
						} else {
							// Update the existing profile title
							$profile = $content.down('button[data-profile-id="' + current_profile + '"]');

							if ($profile) {
								$profile.up('li').highlight().down('strong').update(form_data['C__VISUALIZATION_PROFILES__TITLE']);
							}
						}

						// Reset the GUI.
						current_profile = null;
						$popup_form.hide();
						$profile_list.show();
					} else {
						$accept_button
							.enable()
							.down('img').writeAttribute('src', '[{$dir_images}]icons/silk/disk.png')
							.next('span').update('[{isys type="lang" ident="LC__VISUALIZATION_PROFILES__TRY_AGAIN"}]');

						idoit.Notify.error(json.message, {sticky:true});
					}
				}
			});
		});

		var reset_form = function (with_property_selector) {
			var i;

			if (Object.isUndefined(with_property_selector)) {
				with_property_selector = true;
			}

			if (with_property_selector) {
				// To "reset" the property selector, we load it with no data.
				load_property_selector('');
			}

			$('C__VISUALIZATION_PROFILES__TITLE').setValue('');
			$('C__VISUALIZATION_PROFILES__WIDTH').setValue('120');
			colorpicker['C__VISUALIZATION_PROFILES__HIGHLIGHT_COLOR'].fromString('538cdd');
			$('C__VISUALIZATION_PROFILES__SHOW_PATH').setValue('1');
			$('C__VISUALIZATION_PROFILES__SHOW_TOOLTIP').setValue('1');
			$('C__VISUALIZATION_PROFILES__MASTER_TOP').setValue('1');
			$popup_form.select('.toggle-button').invoke('removeClassName', 'btn-green');

			// Default values.
			$('C__VISUALIZATION_PROFILES__DEFAULT_ORIENTATION').setValue('horizontal');
			$('C__VISUALIZATION_PROFILES__DEFAULT_SERVICE_FILTER').setValue(-1);
			Event.fire($("C__VISUALIZATION_PROFILES__DEFAULT_OBJECT_TYPE_FILTER__selected_box").setValue([]), "chosen:updated");

			for (i = 1; i <= 8; i ++) {
				$popup_form.select('tr.row-' + i).invoke('hide');
				$('C__VISUALIZATION_PROFILES__R' + i + '__ROW').setValue('');
				$('C__VISUALIZATION_PROFILES__R' + i + '__FILLCOLOR').enable();

				colorpicker['C__VISUALIZATION_PROFILES__R' + i + '__FILLCOLOR'].fromString('ffffff');
				colorpicker['C__VISUALIZATION_PROFILES__R' + i + '__FONTCOLOR'].fromString('000000');
				$('C__VISUALIZATION_PROFILES__R' + i + '__OPTION').setValue('[{$smarty.const.C__VISUALIZATION_PROFILE__OBJ_TITLE}]');
			}
		};

		var load_property_selector = function (json_string) {
			var json = {main_obj:{},lvls:{}};

			if (json_string.isJSON()) {
				json = json_string.evalJSON()
			}

			var properties = {
				name: "C__VISUALIZATION_PROFILES__OBJ_INFO_CONFIG",
				preselection: Object.toJSON(json.main_obj),
				preselection_lvls: Object.toJSON(json.lvls),
				grouping: false,
				sortable: true,
				p_bInfoIconSpacer: 0,
				p_bEditMode: true,
				p_bInfoIcon: false,
				provide: '[{$smarty.const.C__PROPERTY__PROVIDES__REPORT}]',
				p_consider_rights: true,
				custom_fields: false,
				report: true,
				selector_size: "small",
				dialog_width: "280px"
			};

			new Ajax.Request('[{$ajax_property_url}]&mode=edit', {
				parameters: {
					plugin_name: 'f_property_selector',
					parameters: Object.toJSON(properties)
				},
				onComplete: function (transport) {
					var json = transport.responseJSON;

					is_json_response(transport, true);

					if (json.success) {
						$('visualization-popup-form-property-selector').update(json.data).select('select.chosen-select').each(function ($select) {
							new Chosen($select, {
								disable_search_threshold: 10,
								width:                    "280px",
								height:                   '20px',
								search_contains:          true
							});
						});
					}
				}
			});
		};

		var update_preview = function () {
			var width = $F('C__VISUALIZATION_PROFILES__WIDTH');

			$preview.update().setStyle({marginLeft: parseInt(225 - (width/2)) + 'px', width: (width > 400 ? 400 : width) + 'px'});

			$popup_form.select('.row-toggle:checked').each(function ($checkbox) {
				var row = $checkbox.up('tr').readAttribute('data-row'),
					$row = new Element('div', {className:'row'}),
					style = {},
					content = 'text';

				if ($('C__VISUALIZATION_PROFILES__R' + row + '__FILLCOLOR_OBJ_TYPE').hasClassName('btn-green')) {
					style.background = 'transparent';
				} else {
					style.background = $F('C__VISUALIZATION_PROFILES__R' + row + '__FILLCOLOR');
				}

				style.color = $F('C__VISUALIZATION_PROFILES__R' + row + '__FONTCOLOR');

				if ($('C__VISUALIZATION_PROFILES__R' + row + '__FONT_BOLD').hasClassName('btn-green')) {
					style.fontWeight = 'bold';
				}

				if ($('C__VISUALIZATION_PROFILES__R' + row + '__FONT_ITALIC').hasClassName('btn-green')) {
					style.fontStyle = 'italic';
				}

				if ($('C__VISUALIZATION_PROFILES__R' + row + '__FONT_UNDERLINE').hasClassName('btn-green')) {
					style.textDecoration = 'underline';
				}

				if ($('C__VISUALIZATION_PROFILES__R' + row + '__FONT_ALIGN_MIDDLE').hasClassName('btn-green')) {
					style.textAlign = 'center';
				} else if ($('C__VISUALIZATION_PROFILES__R' + row + '__FONT_ALIGN_RIGHT').hasClassName('btn-green')) {
					style.textAlign = 'right';
				}

				preview_styles.defaults = $('C__VISUALIZATION_PROFILES__R' + row + '__OPTION').down(':selected').innerHTML;

				switch ($F('C__VISUALIZATION_PROFILES__R' + row + '__OPTION')) {
					default:
						content = preview_styles.defaults;
						break;

					case 'obj-id':
						content = preview_styles.objid;
						break;

					case 'obj-sys-id':
						content = preview_styles.sysid;
						break;

					case 'obj-type-title':
						content = new Element('span').update('[{isys type="lang" ident="LC__CMDB__OBJTYPE"}]');
						break;

					case 'cmdb-status':
						content = preview_styles.cmdbstatus.clone(true).addClassName('mr5').outerHTML +
							'[{isys type="lang" ident="LC__CMDB_STATUS__IN_OPERATION"}]';
						break;

					case 'obj-title-cmdb-status':
						content = preview_styles.cmdbstatus.clone(true).setStyle({float:'right'}).outerHTML +
							new Element('span').update('[{isys type="lang" ident="LC__VISUALIZATION_PROFILES_OPTION__OBJECT_TITLE"}]').outerHTML;
						break;

					case 'primary-ip':
						content = preview_styles.ip;
						break;

					case 'obj-type-title-icon':
						content = preview_styles.icon.outerHTML + new Element('span').update('[{isys type="lang" ident="LC__CMDB__OBJTYPE"}]').outerHTML;
						break;

					case 'obj-title-type-title-icon-cmdb-status':
						content =  preview_styles.cmdbstatus.clone(true).setStyle({float:'right'}).outerHTML +
							preview_styles.icon.outerHTML + '[{isys type="lang" ident="LC__CMDB__OBJTYPE"}]';
						break;
				}

				$preview.insert($row.setStyle(style).update(content));
			});
		};

		var morph_color = function () {
			setTimeout(function () {
				$preview.morph('background:' + Color.random_rgb() + ';', 1000);

				morph_color();
			}, 1500);
		};

		morph_color();
		$popup_form.on('change', 'input,select', update_preview);
		$popup_form.on('click', 'button', update_preview);
	})();
</script>

<style>
	#visualization-popup {
		box-sizing: border-box;
		position: relative;
		height: 100%;
	}

	#visualization-popup ul#visualization-popup-profile-list,
	#visualization-popup ul#visualization-popup-profile-list li {
		list-style: none;
		margin: 0;
		padding: 0;
	}

	#visualization-popup ul#visualization-popup-profile-list li {
		background: #eee;
		border-bottom: 1px solid #888;
		padding:5px;
		vertical-align: middle;
	}

	#visualization-popup ul#visualization-popup-profile-list li strong,
	#visualization-popup ul#visualization-popup-profile-list li span {
		vertical-align: middle;
	}

	#visualization-popup .preview {
		position: absolute;
		bottom: 5px;
		right: 5px;
	}

	#visualization-popup-preview {
		margin-left: 45px;
		max-width: 400px;
		text-align: left;
		overflow: hidden;
		box-sizing: border-box;
	}

	#visualization-popup-preview div.row {
		height: 15px;
		line-height: 15px;
		padding: 0 5px;
		white-space: nowrap;
	}
</style>
[{/isys_group}]