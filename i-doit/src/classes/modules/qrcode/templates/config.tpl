<h2 class="p5 gradient border-bottom">QR-Code</h2>

<h3 class="p5 gradient border-bottom">[{isys type="lang" ident="LC__MODULE__QRCODE__CONFIGURATION"}]</h3>

<div class="contentTable" id="qrcode_config">
	<div class="m5">
		<p class="info p5 mb5"><img src="[{$dir_images}]icons/silk/information.png" class="mr5 vam" />[{isys type="lang" ident="LC__MODULE__QRCODE__CONFIGURATION__GLOBAL_ENABLE"}]</p>
		<p>[{isys type="lang" ident="LC__MODULE__QRCODE__CONFIGURATION__GLOBAL_CONFIGURATION"}]</p>

		[{if !$auth_edit_global}]
			[{* If the "edit" right is missing, we insert a hidden element as a radio-button fallback *}]
			[{isys type="f_text" name="C__MODULE__QRCODE__GLOBAL_TYPE" p_strValue=$global_type p_bInvisible=true p_bInfoIconSpacer=0}]
			[{isys type="f_text" name="C__MODULE__QRCODE__GLOBAL_LINK_TYPE" p_strValue=$link_type p_bInvisible=true p_bInfoIconSpacer=0}]
		[{/if}]

		<table class="mt10">
			<tr>
				<td class="key vat pt10">[{isys type="lang" ident="LC__MODULE__QRCODE__CONFIGURATION__QR_METHOD"}]</td>
				<td>
					<table>
						<tr>
							<td style="width: 150px;">
								<label>
									<input type="radio" value="[{$smarty.const.C__QRCODE__TYPE__SELFDEFINED}]" class="radio ml20" name="C__MODULE__QRCODE__GLOBAL_TYPE" />
									[{isys type="lang" ident="LC__MODULE__QRCODE__CONFIGURATION__GLOBAL_DEFINITION"}]<strong class="blue">*</strong>
								</label>
							</td>
							<td>
								<div class="ml20">
									[{isys type="f_text" name="C__MODULE__QRCODE__GLOBAL_URL" p_bInfoIconSpacer=0 p_bReadonly=!$auth_edit_global}]
								</div>
							</td>
						</tr>
						<tr>
							<td style="width: 150px;">
								<label>
									<input type="radio" value="[{$smarty.const.C__QRCODE__TYPE__ACCESS_URL}]" class="radio ml20" name="C__MODULE__QRCODE__GLOBAL_TYPE" />
									[{isys type="lang" ident="LC__MODULE__QRCODE__CONFIGURATION__PRIMARY_URL"}]
								</label>
							</td>
							<td>
								<div class="ml20">
									[{isys type="lang" ident="LC__MODULE__QRCODE__CONFIGURATION__PRIMARY_URL_DESCRIPTION"}]
								</div>
							</td>
						</tr>
					</table>
				</td>
			</tr>
			<tr>
				<td class="key vat pt5">[{isys type="lang" ident="LC__MODULE__QRCODE__CONFIGURATION__QR_LINK"}]</td>
				<td class="value">
					<label class="display-block ml20">
						<input type="radio" value="[{$smarty.const.C__QRCODE__LINK__IQR}]" class="radio" name="C__MODULE__QRCODE__GLOBAL_LINK_TYPE" />
						[{isys type="lang" ident="LC__MODULE__QRCODE__CONFIGURATION__LINK_IQR"}]
					</label>
					<label class="display-block ml20">
						<input type="radio" value="[{$smarty.const.C__QRCODE__LINK__PRINT}]" class="radio" name="C__MODULE__QRCODE__GLOBAL_LINK_TYPE" />
						[{isys type="lang" ident="LC__MODULE__QRCODE__CONFIGURATION__LINK_PRINT"}]
					</label>
				</td>
			</tr>
			<tr>
				<td class="key vat">[{isys type="f_label" name="C__MODULE__QRCODE__CONFIGURATION__GLOBAL_WYSIWYG" ident="LC__MODULE__QRCODE__CONFIGURATION__DESCRIPTION"}]<strong class="blue">*</strong></td>
				<td>[{isys type="f_wysiwyg" name="C__MODULE__QRCODE__CONFIGURATION__GLOBAL_WYSIWYG" p_bReadonly=!$auth_edit_global}]</td>
			</tr>
			<tr>
				<td class="key">[{isys type="f_label" name="C__MODULE__QRCODE__CONFIGURATION__GLOBAL_LOGO" ident="LC__MODULE__QRCODE__CONFIGURATION__LOGO"}]</td>
				<td>
					[{isys type="f_popup" p_strPopupType="browser_file" name="C__MODULE__QRCODE__LOGO_OBJ" p_bReadonly=!$auth_edit_global}]
				</td>
			</tr>
		</table>

		[{if isys_glob_is_edit_mode()}]
		<p class="mt10 info p5">
			<strong>*</strong> [{$variable_description}]
		</p>
		[{/if}]
	</div>
</div>

<h3 class="p5 gradient border-top border-bottom">[{isys type="lang" ident="LC__MODULE__QRCODE__CONFIGURATION__BY_OBJ_TYPE"}]</h3>

<div class="contentTable" id="qrcode_obj_type_config">
	<div class="m5">
		[{if isys_glob_is_edit_mode() && $auth_edit_objtype}]
		[{isys type="f_dialog" name="C__MODULE__QRCODE__OBJ_TYPES" p_strClass="input input-small ml5 mr5" p_bInfoIconSpacer=0 p_bDbFieldNN=1}]

		<button type="button" id="qrcode_obj_type_add" class="btn">
			<img src="[{$dir_images}]icons/silk/add.png" class="mr5" /><span>[{isys type="lang" ident="LC__MODULE__QRCODE__CONFIGURATION__NEW_CONFIG"}]</span>
		</button>
		[{/if}]

		<div id="qrcode_obj_type_config_items">
			[{foreach $obj_type_config as $obj_type => $config}]

				[{if !$auth_edit_objtype}]
					[{* If the "edit" right is missing, we insert a hidden element as a radio-button fallback *}]
					[{isys type="f_text" name="C__MODULE__QRCODE__[{$obj_type}]_TYPE" p_strValue=$config.type_selection p_bInvisible=true p_bInfoIconSpacer=0}]
				[{/if}]

			<div class="item border mt5" data-obj-type="[{$obj_type}]">
				<h3 class="gradient p5 text-shadow toggle mouse-pointer border-bottom">
					<img src="[{$dir_images}]icons/silk/bullet_arrow_down.png" class="vam" />
					<span class="vam">[{$config.obj_type_name}]</span>
					[{if isys_glob_is_edit_mode() && $auth_delete_objtype}]<img src="[{$dir_images}]icons/silk/cross.png" class="fr remove mouse-pointer" />[{/if}]
				</h3>
				<table class="ml5">
					<tr>
						<td style="width:200px;">[{isys type="f_label" name="C__MODULE__QRCODE__ENABLE__`$obj_type`" ident="LC__MODULE__QRCODE__CONFIGURATION__ENABLE"}]</td>
						<td>[{isys type="f_dialog" name="C__MODULE__QRCODE__ENABLE__`$obj_type`" p_bDbFieldNN=1 p_arData=$smarty_yes_no p_strClass="input-mini qr-code-disabler" p_strSelectedID=$config.enabled}]</td>
					</tr>
					<tr [{if $config.enabled == 0}]style="display:none;"[{/if}]>
						<td>[{isys type="lang" ident="LC__MODULE__QRCODE__CONFIGURATION__QR_METHOD"}]</td>
						<td>
							<table>
								<tr>
									<td style="width: 150px;">
										<label>
											<input type="radio" value="[{$smarty.const.C__QRCODE__TYPE__SELFDEFINED}]" class="radio ml20" name="C__MODULE__QRCODE__[{$obj_type}]_TYPE" [{$config.type_selfdefined}] />
											[{isys type="lang" ident="LC__MODULE__QRCODE__CONFIGURATION__GLOBAL_DEFINITION"}] <strong>*</strong>
										</label>
									</td>
									<td>
										<div class="ml20">
											[{isys type="f_text" name=$config.url p_bInfoIconSpacer=0}]
										</div>
									</td>
								</tr>
								<tr>
									<td style="width: 150px;">
										<label>
											<input type="radio" value="[{$smarty.const.C__QRCODE__TYPE__ACCESS_URL}]" class="radio ml20" name="C__MODULE__QRCODE__[{$obj_type}]_TYPE" [{$config.type_accessurl}] />
											[{isys type="lang" ident="LC__MODULE__QRCODE__CONFIGURATION__PRIMARY_URL"}]
										</label>
									</td>
									<td>
										<div class="ml20">
											[{isys type="lang" ident="LC__MODULE__QRCODE__CONFIGURATION__PRIMARY_URL_DESCRIPTION"}]
										</div>
									</td>
								</tr>
							</table>
						</td>
					</tr>
					<tr [{if $config.enabled == 0}]style="display:none;"[{/if}]>
						<td class="vat">
							[{isys type="f_label" name=$config.description ident="LC__MODULE__QRCODE__CONFIGURATION__DESCRIPTION"}]<strong>*</strong>
						</td>
						<td class="wysiwyg">
							[{isys type="f_wysiwyg" name=$config.description p_bReadonly=!$auth_edit_objtype}]
						</td>
					</tr>
				</table>
			</div>
			[{foreachelse}]
			<p class="no-config-message">[{isys type="lang" ident="LC__MODULE__QRCODE__CONFIGURATION__NO_OBJTYPE_CONFIG"}]</p>
			[{/foreach}]
		</div>
	</div>
</div>

<div id="qrcode_obj_type_config_template" class="hide">

	<!-- This will serve as our template, when creating a new object-type specific configuration -->
	<div class="item border mt5" data-obj-type="%s">
		<h3 class="p5 gradient border-bottom toggle mouse-pointer border-bottom">
			<img src="[{$dir_images}]icons/silk/bullet_arrow_down.png" class="vam" />
			<span class="vam">%type</span>
			<img src="[{$dir_images}]icons/silk/cross.png" class="fr remove mouse-pointer" />
		</h3>
		<table class="ml5">
			<tr>
				<td style="width: 200px;">[{isys type="f_label" name="C__MODULE__QRCODE__ENABLE__%s" ident="LC__MODULE__QRCODE__CONFIGURATION__ENABLE"}]</td>
				<td>[{isys type="f_dialog" name="C__MODULE__QRCODE__ENABLE__%s" p_bDbFieldNN=1 p_arData=$smarty_yes_no p_strClass="input-mini qr-code-disabler"}]</td>
			</tr>
			<tr>
				<td>[{isys type="lang" ident="LC__MODULE__QRCODE__CONFIGURATION__QR_METHOD"}]</td>
				<td>
					<table>
						<tr>
							<td style="width: 150px;">
								<label>
									<input type="radio" value="[{$smarty.const.C__QRCODE__TYPE__SELFDEFINED}]" class="radio ml20" name="C__MODULE__QRCODE__%s_TYPE" />
									[{isys type="lang" ident="LC__MODULE__QRCODE__CONFIGURATION__GLOBAL_DEFINITION"}] <strong>*</strong>
								</label>
							</td>
							<td>
								<div class="ml20">
									[{isys type="f_text" name="C__MODULE__QRCODE__%s_URL" p_bInfoIconSpacer=0 p_strValue="%idoit_host%/?objID=%objid%"}]
								</div>
							</td>
						</tr>
						<tr>
							<td style="width: 150px;">
								<label>
									<input type="radio" value="[{$smarty.const.C__QRCODE__TYPE__ACCESS_URL}]" class="radio ml20" name="C__MODULE__QRCODE__%s_TYPE" checked="checked" />
									[{isys type="lang" ident="LC__MODULE__QRCODE__CONFIGURATION__PRIMARY_URL"}]
								</label>
							</td>
							<td>
								<div class="ml20">
									[{isys type="lang" ident="LC__MODULE__QRCODE__CONFIGURATION__PRIMARY_URL_DESCRIPTION"}]
								</div>
							</td>
						</tr>
					</table>
				</td>
			</tr>
			<tr>
				<td class="vat">
					[{isys type="f_label" name="C__MODULE__QRCODE__WYSIWYG__%s" ident="LC__MODULE__QRCODE__CONFIGURATION__DESCRIPTION"}]<strong>*</strong>
				</td>
				<td class="wysiwyg">

				</td>
			</tr>
		</table>
	</div>

</div>

<script type="text/javascript">
	var $gui = $('qrcode_config'),
		$obj_type_config = $('qrcode_obj_type_config'),
		$obj_type_config_items = $('qrcode_obj_type_config_items'),
		$obj_types = $('C__MODULE__QRCODE__OBJ_TYPES');


	$obj_type_config_items.on('click', 'h3.toggle', function(ev) {
		var $div = ev.findElement('h3').up('div.item');

		if ($div.down('table').toggle().visible()) {
			$div.down('h3').addClassName('border-bottom')
				.down('img').writeAttribute('src', '[{$dir_images}]icons/silk/bullet_arrow_down.png');
		} else {
			$div.down('h3').removeClassName('border-bottom')
				.down('img').writeAttribute('src', '[{$dir_images}]icons/silk/bullet_arrow_right.png');
		}
	});

	[{if isys_glob_is_edit_mode()}]
		var change_qrcode_type = function () {
			var default_url = $('C__MODULE__QRCODE__GLOBAL_URL');

			if (default_url) {
				if (this.getValue() == '[{$smarty.const.C__QRCODE__TYPE__SELFDEFINED}]') {
					default_url.enable().setStyle({background: '#fff'});
				} else {
					default_url.disable().setStyle({background: '#eee'});
				}
			}
		};

		$gui.select('.radio').invoke('observe', 'change', change_qrcode_type);

		$obj_type_config.on('change', '.qr-code-disabler', function (ev) {
			var $select = ev.findElement('select');

			if ($select.getValue() == 0) {
				$select.up('tr')
					.next('tr').hide()
					.next('tr').hide();
			} else {
				$select.up('tr')
					.next('tr').show()
					.next('tr').show();
			}
		});

		if ($('qrcode_obj_type_add')) {
			$('qrcode_obj_type_add').on('click', function () {
				var obj_type = $obj_types.getValue(),
					obj_type_name = $obj_types.down('option:selected').innerHTML,
					obj_type_config = $obj_type_config_items.select('.item[data-obj-type="' + obj_type + '"]'),
					config,
					item;

				if (obj_type_config.length == 0) {
					new Ajax.Request('?ajax=1&call=smartyplugin&mode=edit', {
						method: 'post',
						parameters:{
							plugin_name:'f_wysiwyg',
							parameters:Object.toJSON({name:'C__MODULE__QRCODE__WYSIWYG__' + obj_type})
						},
						onSuccess: function (response) {
							var json = response.responseJSON;
							if (json.success) {
								// Some sort of templating, here...
								item = $('qrcode_obj_type_config_template').clone(true);
								item.down('td.wysiwyg').update(json.data);
								item = item.innerHTML.replace(new RegExp('%s', 'g'), obj_type).replace(new RegExp('%type', 'g'), obj_type_name);

								$obj_type_config_items.insert(item).down('p.no-config-message').addClassName('hide');
							}
						}
					});
				} else {
					obj_type_config[0].highlight();
				}
			});
		}

		$obj_type_config_items.on('click', 'img.remove', function(ev) {
			ev.findElement().up('div.item').remove();
		});

		[{if !$auth_edit_global}]
		$gui.select('input.radio').invoke('disable');
		[{/if}]

		[{if !$auth_edit_objtype}]
		$obj_type_config.select('input.radio').invoke('disable');
		$obj_type_config.select('input.input').invoke('writeAttribute', 'readonly', 'readonly');
		[{/if}]

	[{else}]
		$gui.select('input.radio').invoke('disable');
		$obj_type_config.select('h3.toggle').invoke('simulate', 'click');
	[{/if}]

	$gui.down('input.radio[value=[{$global_type}]]')
		.writeAttribute('checked', 'checked')
		.simulate('change');

	$gui.down('input.radio[value=[{$link_type}]]')
	    .writeAttribute('checked', 'checked')
	    .simulate('change');
</script>

<script type="text/javascript" src="[{$dir_tools}]js/ajax_upload/fileuploader.js"></script>