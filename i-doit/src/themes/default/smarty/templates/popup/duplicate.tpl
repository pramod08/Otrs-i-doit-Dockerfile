<div id="popup-duplicate">
	<h3 class="popup-header">
		<img src="[{$dir_images}]prototip/styles/default/close.png" class="fr popup-closer mouse-pointer" alt="x"/>
		<span>[{isys type="lang" ident="LC__CATG__ODEP_OBJ"}] [{isys type="lang" ident="LC__NAVIGATION__NAVBAR__DUPLICATE"}]</span>
	</h3>

	<div class="popup-content">
		[{if !$customName}]
		<div class="m5 p5 note">
			<img src="[{$dir_images}]icons/infoicon/info.png" class="vam mr5"/>[{isys type="lang" ident="LC__POPUP__DUPLICATE__POST NEW_NAME"}]
		</div>
		[{/if}]
		<table class="contentTable">
			[{if $customName}]
			<tr>
				<td class="key">[{isys type="f_label" name="object_title" ident="LC__CMDB__DUPLICATE__NEW_NAME"}]</td>
				<td class="value">[{isys type="f_text" name="object_title" id="object_title" p_strClass="input-small" p_strValue=$object_title}]</td>
			</tr>
			[{/if}]
			<tr>
				<td class="key">
					[{isys type='f_label' name='update_globals' ident='LC__MODULE__JDISC__IMPORT__MODE_UPDATE'}]
					<img title="[{isys type="lang" ident="LC__CMDB__DUPLICATE__UPDATE_GLOBALS"}]" alt="help" src="[{$dir_images}]icons/infoicon/help.png" class="vam ml5"/>
				</td>
				<td class="value"><input type="checkbox" checked="checked" name="update_globals" id="update_globals" value="1" class="ml20" /></td>
			</tr>
			<tr>
				<td class="key">[{isys type="f_label" name="export_specific_catg" ident="LC__CMDB__DUPLICATE__SPECIFIC_CATEGORY"}]</td>
				<td class="value"><input type="checkbox" checked="checked" name="export_specific_catg" id="export_specific_catg" class="ml20" /></td>
			</tr>
			<tr>
				<td class="key vat">[{isys type="lang" ident="LC__CMDB__DUPLICATE__CATEGORIES"}]</td>
				<td class="value pl20">
					<label class="display-block bold">
						<input type="checkbox" checked="checked" id="select_all" name="select_all" class="mr5" value="-1"/>
						[{isys type="lang" ident="LC__UNIVERSAL__SELECT_ALL"}]
					</label>

					<div style="[{if !$customName}]height:225px[{else}]height:235px[{/if}] ;overflow:auto;" id="category-checkbox-list">
						[{foreach from=$categories key=index item=cat}]
							<label>
								<input type="checkbox" class="categories mr5" checked="checked" name="category[]" value="[{$cat.id}]"/>[{isys type="lang" ident=$cat.title}]
							</label>
						[{/foreach}]

						[{if is_array($custom_categories) && count($custom_categories)}]
						<br class="cb" />
						<h3 class="mt20">[{isys type="lang" ident="LC__CMDB__CUSTOM_CATEGORIES"}]</h3>
						[{foreach from=$custom_categories key=i item=cat}]
							<label>
								<input type="checkbox" class="categories mr5" checked="checked" name="custom_category[]" value="[{$cat.isysgui_catg_custom__id}]"/>[{isys type="lang" ident=$cat.isysgui_catg_custom__title}]
							</label>
						[{/foreach}]
						[{/if}]
					</div>
				</td>
			</tr>
			<tr>
				<td class="key">[{isys type="f_label" name="duplicate_options" ident="LC__MASS_CHANGE__OPTIONS"}]</td>
				<td class="value">
					<select name="duplicate_options" id="duplicate_options" class="input input-small ml20">
						<option value="0">-</option>
						<optgroup label="Virtuelle Maschine">
							<option value="virtualize">[{isys type="lang" ident="LC__CMDB__DUPLICATE__VIRTUALIZE"}]</option>
							<option value="devirtualize">[{isys type="lang" ident="LC__CMDB__DUPLICATE__DEVIRTUALIZE"}]</option>
						</optgroup>
					</select>
				</td>
			</tr>
			<tr>
				<td class="key">[{isys type="f_label" name="open_new_created_object" ident="LC__MASS_CHANGE__OPEN_NEWLY_CREATED_OBJECT"}]</td>
				<td class="value">
					[{isys type="checkbox" name="open_new_created_object"}]
				</td>
			</tr>
		</table>
	</div>

	<div class="popup-footer">
		[{isys name="save" type="f_button" id="popup-duplicate-save-button" icon="`$dir_images`icons/silk/page_copy.png" p_strValue="LC__NAVIGATION__NAVBAR__DUPLICATE"}]
		[{isys name="C__UNIVERSAL__BUTTON_CANCEL" type="f_button" icon="`$dir_images`icons/silk/cross.png" p_strValue="LC__UNIVERSAL__BUTTON_CANCEL" p_strClass="popup-closer"}]
	</div>

	<input type="hidden" name="objects" id="objects" value=""/>
	<input type="hidden" name="duplicate" id="duplicate" value="1"/>
</div>

<style type="text/css">
	#category-checkbox-list label {
		float: left;
		width: 155px;
		height: auto;
	}
</style>

<script language="JavaScript" type="text/javascript">
	(function () {
		'use strict';

		var $popup = $('popup-duplicate'),
				$duplicateButton = $('popup-duplicate-save-button'),
				$cancelButton = $('C__UNIVERSAL__BUTTON_CANCEL');

		$popup.select('.popup-closer').invoke('on', 'click', function () {
			$('duplicate').setValue('0');
			popup_close();
		});

		$duplicateButton.on('click', function () {
			$cancelButton.disable();
			$duplicateButton
					.disable()
					.down('img').writeAttribute('src', '[{$dir_images}]ajax-loading.gif')
					.next('span').update('[{isys type="lang" ident="LC__UNIVERSAL__LOADING"}]');

			$('navMode').setValue('[{$smarty.const.C__NAVMODE__SAVE}]');

			replace_listSelection();

			new Ajax.Request(window.location.href, {
				method:     'post',
				parameters: $('isys_form').serialize(true),
				onComplete: function (r) {
					var json = r.responseJSON;

					if (json.success)
					{
						// Set the Notify messages "sticky" because we'll redirect anyhow...
						idoit.Notify.success('[{isys type="lang" ident="LC__INFOBOX__OBJECT_WAS_DUPLICATED"}]', {sticky: true});

						if (json.data.redirect)
						{
							// Simply replace the "?objID=123&..." part.
							window.location.search = json.data.redirect;

							// One day, we'll use this pretty URL :)
							//window.location.href = json.data.url;
						}
						else
						{
							window.location.reload(true);
						}
					}
					else
					{
						idoit.Notify.error(json.message, {sticky: true});
					}
				}
			});
		});

		$('select_all').on('change', function () {
			var select_all = this.checked;

			$('popup-duplicate').select('.categories').each(function ($el){
				$el.checked = select_all;
			});
		});

		[{if $smarty.get.objID}]
		$('objects').setValue('[{$smarty.get.objID}]');
		$('object_title').setValue('[{$smarty.get.objTitle}]');
		[{/if}]
	})();
</script>
