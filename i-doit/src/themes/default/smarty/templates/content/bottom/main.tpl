[{isys_group name="content" id="contentbottomcontent"}]

<div id="contentBottom">
	<div id="content_overlay" style="display:none;"></div>
	<div id="contentBottomContent">
		<div id="scroller">
			[{if isset($index_includes.contentbottomcontentadditionbefore)}]
				[{include file=$index_includes.contentbottomcontentadditionbefore}]
			[{/if}]

			[{include file=$index_includes.contentbottomcontent|default:"content/bottom/content/main.tpl"}]

			[{if isset($index_includes.contentbottomcontentaddition)}]
				[{if is_array($index_includes.contentbottomcontentaddition)}]
					[{foreach from=$index_includes.contentbottomcontentaddition item=addition_tpl}]
						[{include file=$addition_tpl}]
					[{/foreach}]
				[{else}]
					[{include file=$index_includes.contentbottomcontentaddition}]
				[{/if}]
			[{/if}]

			[{if $bShowCommentary == "1"}]
			<table class="contentTable commentaryTable" style="border-top:none;">
	            <tr>
	                <td class="key" style="vertical-align: top;">[{isys type="f_label" name="C__CMDB__CAT__COMMENTARY_$commentarySuffix" ident="LC__CMDB__CAT__COMMENTARY"}]</td>
					<td>[{isys type="f_wysiwyg" name="C__CMDB__CAT__COMMENTARY_$commentarySuffix"}]</td>
				</tr>
			</table>
			[{/if}]

			<input id="LogbookCommentary" name="LogbookCommentary" type="hidden" value="" />

			[{if isset($index_includes.contentbottomcontentadditionafter)}]
				[{include file=$index_includes.contentbottomcontentadditionafter}]
			[{/if}]
		</div>
	</div>
	[{/isys_group}]

	<script type="text/javascript">
		var $contentbottomcontent = $('contentBottomContent');

		// Hide all open tooltips.
		Tips.hideAll();

		[{if isys_glob_is_edit_mode()}]
		var elements = $contentbottomcontent.select('.validate-me').invoke('readAttribute', 'data-identifier'),

			mandatory_fields = $contentbottomcontent.select('.validate-mandatory');

		if (mandatory_fields.length > 0) {
			var i, $el, $label, tmp;

			for (i in mandatory_fields) {
				if (mandatory_fields.hasOwnProperty(i)) {
					$el = mandatory_fields[i];

					if ($label = $contentbottomcontent.down('label[for=' + $el.id + ']')) {
						// If we have a "label", we can be much more precise.
						$label.insert(new Element('span', {className: 'red bold vam'}).update('*'));
					} else {
						// This is the fallback, if no label could be found.
						if (tmp = $el.up('td')) {
							if (tmp = tmp.previous('td')) {
								tmp.insert(new Element('span', {className: 'red bold vam'}).update('*'));
							}
						}
					}
				}
			}
		}

		// On every change we want to check for validation-issues.
		$contentbottomcontent.on('change', '.validate-rule', function (ev) {
			var $element = ev.findElement().removeClassName('error'),
				identifier = $element.readAttribute('data-identifier'),
				category = '',
				validation = new Validation($element, {images_path:'[{$dir_images}]'});

			if ($element.retrieve('validating', false)) {
				return;
			}

			$element.store('validating', true);

			// This is a kind of fallback for the overview-page.
			if (identifier == '' || identifier == null) {
				identifier = $element.name;
				if ($element.up('fieldset')) {
					category = $element.up('fieldset').id
				}
			}

			new Ajax.Request('?call=validate_field&ajax=1&func=validate', {
				method: 'post',
				parameters: {
					identifier: identifier,
					element_value: $element.getValue(),
					category: category,
					obj_id: ('[{$smarty.get.objID}]' || '[{$object_id}]'),
					obj_type_id: ('[{$smarty.get.objTypeID}]' || '[{$object_type_id}]'),
					category_entry_id: ('[{$smarty.get.cateID}]' || '[{$category_entry_id}]')
				},
				onComplete: function (result) {
					var json = result.responseJSON;

					$element.store('validating', false);

					if (json.success) {
						validation.success();
					} else {
						validation.fail(json.message);
					}

					// Freeing memory.
					validation = null;
				}
			});
		});
		[{/if}]

		// Enable chosen.
		$contentbottomcontent.select('select.chosen-select').each(function ($element) {
			new Chosen($element, {
				default_multiple_text:     '[{isys type="lang" ident="LC__UNIVERSAL__CHOOSEN_PLACEHOLDER" p_bHtmlEncode=false}]',
				placeholder_text_multiple: '[{isys type="lang" ident="LC__UNIVERSAL__CHOOSEN_PLACEHOLDER" p_bHtmlEncode=false}]',
				no_results_text:           '[{isys type="lang" ident="LC__UNIVERSAL__CHOOSEN_EMPTY" p_bHtmlEncode=false}]',
				disable_search_threshold:  10,
				width:                     '',
				search_contains:           true
			});
		});
	</script>
</div>