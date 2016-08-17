<div id="custom_fields_configuration">
	[{if isset($g_list)}]
		[{$g_list}]
	[{else}]
	<table class="contentTable">
		<tr>
			<td class="key">[{isys type="f_label" name="category_title" ident="LC__UNIVERSAL__CATEGORY_TITLE"}]</td>
			<td class="value">[{isys type="f_text" id="category_title" name="category_title" p_bNoTranslation=true}]</td>
		</tr>
		<tr>
			<td class="key" style="vertical-align:top;">[{isys type="f_label" name="object_types" ident="LC_UNIVERSAL__OBJECT_TYPES"}]</td>
			<td class="value">[{isys type="f_dialog_list" name="object_types" p_bSort=false}]</td>
		</tr>
        <tr>
            <td class="key" style="vertical-align:top;">[{isys type="f_label" name="object_types" ident="LC__CMDB__CUSTOM_CATEGORIES__LIST_CATEGORY"}]</td>
            <td class="value">[{isys type="f_dialog" name="multivalued"}]</td>
        </tr>
        [{* Constant should not be editable for non existing entries. *}]
        <tr>
            <td class="key" style="vertical-align:top;">[{isys type="f_label" name="object_types" ident="LC__CMDB__CUSTOM_CATEGORIES__CONSTANT"}]</td>
            <td class="value">[{isys type="f_text" name="category_constant"}]</td>
        </tr>
	</table>


	<div class="p10">

		<p class="mb10"><img src="[{$dir_images}]icons/silk/information.png" class="vam" /> <span class="vam">[{isys type="lang" ident="LC__SYSTEM__CUSTOM_CATEGORIES__DESCRIPTION"}]</span></p>

		<ul id="custom_fields_configuration_list_header" class="border" style="border-bottom: none;">
			<li class="p5">
				<div class="label-a bold ml15">[{isys type="lang" ident="LC__SYSTEM__CUSTOM_CATEGORIES__FIELD_TITLE"}]</div>
				<div class="label-b bold">[{isys type="lang" ident="LC__SYSTEM__CUSTOM_CATEGORIES__FIELD_TYPE"}]</div>
				<div class="label-c bold">[{isys type="lang" ident="LC__SYSTEM__CUSTOM_CATEGORIES__FIELD_ADDITIONAL"}]</div>
				<div class="label-d bold showInList" title="[{isys type="lang" ident="LC__SYSTEM__CUSTOM_CATEGORIES__SHOW_IN_LIST"}]"><img src="[{$dir_images}]icons/silk/table.png" /></div>
				<div class="label-e bold">[{isys type="lang" ident="LC__SYSTEM__CUSTOM_CATEGORIES__FIELD_ACTION"}]</div>
				<br class="cb" />
			</li>
		</ul>

		<ul id="custom_fields_configuration_list" class="border" style="border-top: none;"></ul>

		<a href="javascript:window.add_custom_field('', '', '', '', '', 1, false, 0);" class="btn mt5" id="add_field_button"><img class="mr5" src="[{$dir_images}]icons/plus-green.gif" /><span>[{isys type="lang" ident="LC__SYSTEM__CUSTOM_CATEGORIES__ADD_NEW_FIELD"}]</span></a>

		[{if count($category_config) && $valueCount > 0}]
			<p class="warning p10 mt10"><img src="[{$dir_images}]icons/silk/error.png" class="vam" /> [{isys type="lang" ident="LC__CMDB__CUSTOM_CATEGORIES__CHANGE_NOTE"}]</p>
		[{/if}]

		[{if $entryCount}]
			<p class="mt10">
				<h3 class="border-bottom">[{isys type="lang" ident="LC__LICENCE_OVERVIEW__STATISTIC"}]</h3>
			</p>
			<p>
				[{isys type="lang" ident="LC__CMDB__CUSTOM_CATEGORIES__ENTRY_COUNT"}]: <strong>[{$entryCount}]</strong>
			</p>
			<p>
				[{isys type="lang" ident="LC__CMDB__CUSTOM_CATEGORIES__VALUE_COUNT"}]: <strong>[{$valueCount}]</strong>
			</p>
		[{/if}]

		[{if $apiExample}]
			<p class="mt20">
				<a class="btn mt5 show-api-info">
					<img class="mr5" src="[{$dir_images}]icons/silk/brick.png" /> <span>[{isys type="lang" ident="LC__SYSTEM__CUSTOM_CATEGORIES__SHOW_TECHNICAL_INFO"}]</span>
				</a>

				<div class="api-info" style="display:none;">
					<div>
						<div class="fl">
							<h3 class="mt10">Configuration:</h3>
							<pre class="mt10 p10 muted">[{json_encode($category_config, JSON_PRETTY_PRINT)}]</pre>
						</div>

						<div class="ml10 fl">
							<h3 class="mt10">Structure Info:</h3>
							<pre class="muted mt10 p10">{
    "Field Key / Identifier": {
        "type": "Field Type",
        "popup": "Popup Type",
        "title": "Title",
        "identifier": "Group ID",
        "show_in_list": 0/1
    }
}</pre>
						</div>
					</div>

				<br class="cb"/>

				<h3 class="mt10">API-Examples:</h3>
				<pre class="muted mt10 p10">[{json_encode($apiExample.read, JSON_PRETTY_PRINT)}]</pre>
				<pre class="muted mt10 p10">[{json_encode($apiExample.create, JSON_PRETTY_PRINT)}]</pre>
				</div>
			</p>
		[{/if}]
	</div>
</div>

<style type="text/css">
	#custom_fields_configuration .label-a {
		width:295px;
		float:left;
	}

	#custom_fields_configuration .label-b {
		width:158px;
		float:left;
	}

	#custom_fields_configuration .label-c {
		width:152px;
		float:left;
	}

	#custom_fields_configuration .label-d {
		width:32px;
		float:left;
	}

	#custom_fields_configuration .label-e {
		width:70px;
		float:left;
	}

	#custom_fields_configuration_list,
	#custom_fields_configuration_list_header {
		margin: 0;
		padding: 0;
		list-style: none;
		width: 750px;
	}

	#custom_fields_configuration_list_header li {
		background: url("[{$dir_images}]gradient.png") repeat-x scroll 0 0 #DDDDDD;
	}

	#custom_fields_configuration_list li {
		position: relative;
		background: #eee;
		border-top:1px solid #888888;
	}

	#custom_fields_configuration_list li div.handle {
		width: 10px;
		height: 21px;
		background:url('[{$dir_images}]icons/hatch.gif');
		cursor: ns-resize;
		position:absolute;
	}
</style>

<script type="text/javascript" language="javascript">
	"use strict";

	var url = document.location.href.parseQuery(),
		$id_field = $N('id');

	if ($id_field.length > 0 && $id_field[0].getValue().blank()) {
		$id_field[0].setValue('[{$id}]');
	}

	url['[{$smarty.const.C__GET__ID}]'] = '[{$id}]';
	url['[{$smarty.const.C__GET__SETTINGS_PAGE}]'] = '[{$smarty.const.C__CUSTOM_FIELDS__CONFIG}]';
	url['[{$smarty.const.C__CMDB__GET__EDITMODE}]'] = 1;

	window.pushState({}, '', '?' + Object.toQueryString(url));

	if ($('category_title') && $('category_constant')) {
		$('category_title').on('change', function(ev, el) {
			$('category_constant').setValue('C__CATG__CUSTOM_FIELDS_' + el.value.toUpperCase().replace(/[\s]/g, '_').replace(/[^a-z0-9]/gi, ''));
		});
	}

	var custom_field_container = $('custom_fields_configuration_list'),
		$custom_field_header = $('custom_fields_configuration_list_header'),
		field_options = [
			{title: '[{isys type="lang" ident="LC__SYSTEM__CUSTOM_CATEGORIES__FIELD_TYPE__TEXT"}]', value:'f_text'},
			{title: '[{isys type="lang" ident="LC__SYSTEM__CUSTOM_CATEGORIES__FIELD_TYPE__TEXTAREA"}]', value:'f_textarea'},
			{title: '[{isys type="lang" ident="LC__SYSTEM__CUSTOM_CATEGORIES__FIELD_TYPE__HTML_EDITOR"}]', value:'f_wysiwyg'},
			{title: 'Dialog+', value:'f_popup', popup:'dialog_plus'},
			{title: '[{isys type="lang" ident="LC__MODULE__CUSTOM_FIELDS__YES_NO_FIELD"}]', value:'f_dialog', extra:'yes-no'},
			{title: '[{isys type="lang" ident="LC__MODULE__CUSTOM_FIELDS__OBJECT_BROWSER"}]', value:'f_popup', popup:'browser_object', relation:0},
            {title: '[{isys type="lang" ident="LC__MODULE__CUSTOM_FIELDS__OBJECT_RELATIONSHIP"}]', value:'f_popup', popup:'browser_object', relation:1},
			{title: '[{isys type="lang" ident="LC__MODULE__CUSTOM_FIELDS__OBJECT_BROWSER_MULTISELECTION"}]', value:'f_popup', popup:'browser_object', relation:0, multiselection:1},
			{title: '[{isys type="lang" ident="LC__MODULE__CUSTOM_FIELDS__OBJECT_RELATIONSHIP_MULTISELECTION"}]', value:'f_popup', popup:'browser_object', relation:1, multiselection:1},
			{title: '[{isys type="lang" ident="LC__MODULE__CUSTOM_FIELDS__HORIZONTAL_LINE"}]', value:'hr'},
			{title: 'HTML', value:'html'},
			{title: 'Javascript', value:'script'},
			{title: 'Link', value:'f_link'},
			{title: '[{isys type="lang" ident="LC_UNIVERSAL__DATE"}]', value:'f_popup', popup:'calendar'},
			{title: '[{isys type="lang" ident="LC__MODULE__CUSTOM_FIELDS__DIALOG_PLUS_MULTI"}]', value:'f_popup', popup:'dialog_plus', multiselection:1},
        ],
        relation_types = JSON.parse('[{$relation_types}]');

	Position.includeScrollOffsets = true;

    var custom_optional_relation = new Element('select', {style:'display:none;', className:'ml15 input input-mini'});

    for(var a in relation_types) {
        if(relation_types.hasOwnProperty(a)){
            custom_optional_relation.insert(
		        new Element('option', {value: relation_types[a].id}).update(relation_types[a].title_lang)
            );
        }
    }

	/**
	 * Function for en- or disabling the additional input-field.
	 */
	window.change_field_type = function (ev) {
		var $el = ev.findElement();
		var $elVal = $el.getValue().split(',');

		if($elVal[0] == 'f_popup')
		{
			if($elVal[1] == 'dialog_plus')
			{
				$el.next().show().enable().writeAttribute('readonly', null).writeAttribute('placeholder', '[{isys type="lang" ident="LC__SYSTEM__CUSTOM_CATEGORIES__FIELD_IDENTIFIER"}]')
						.next().disable().hide();
				return true;
			}
			else if($elVal[1] == 'browser_object')
			{
				if(($elVal.length == 3 || $elVal.length == 4) && parseInt($elVal[2]) == 1)
				{
					$el.next().hide().writeAttribute('readonly', 'readonly').writeAttribute('placeholder', null)
							.next().enable().show();
					return true;
				}
			}
		}

		$el.next().show().disable().writeAttribute('readonly', 'readonly').writeAttribute('placeholder', null)
				.next().disable().hide();
		return true;
	};

	/**
	 * Function for adding a new field to the custom category.
	 *
	 * @param  p_key
	 * @param  p_title
	 * @param  p_type
	 * @param  p_popup
	 * @param  p_identifier
	 * @param  p_show_in_list
	 */
	window.add_custom_field = function (p_key, p_title, p_type, p_popup, p_identifier, p_show_in_list, disable, p_multiselection) {
		if (typeof p_title == 'undefined') p_title = '';

		// The "custom" prefix is necessary because the integers alone can cause problems when serializing on 32/64 bit machines.
		var i = (p_key) ? p_key : (new Date()).getTime() + Math.floor((10 - 3) * Math.random()) + 5,
			i2,
			custom_title,
			option,
			custom_select = new Element('select', {name:'field_type[' + i + ']', id:i, className:'input input-mini fieldType'}),
			custom_optional = new Element('input', {type:'text', id:'identifier_' + i, name:'field_dialog_identifier[' + i + ']', value:p_identifier, className:'input input-mini ml15', disabled:'disabled', readonly:'readonly'}),
			custom_show_in_list = new Element('label', {id:'field_show_in_list_' + i, className:'ml15 showInList'}),
            custom_optional_relation_ele = custom_optional_relation.clone();

		if (p_type == 'html')
		{
			custom_title = (new Element('textarea', {cols:'10', rows:'10', name:'field_title[' + i + ']', placeholder:'HTML Content..', id:'input_' + i, className:'input input-small ml15 mr15'})).update(p_title);

		}
		else if (p_type == 'script')
		{
			custom_title = (new Element('textarea', {cols:'10', rows:'10', name:'field_title[' + i + ']', placeholder:'Javascript..', id:'input_' + i, className:'input input-small ml15 mr15'})).update(p_title);
		}
		else
		{
			custom_title = new Element('input', {type:'text', name:'field_title[' + i + ']', id:'input_' + i, value:p_title, className:'input input-small ml15 mr15'});
		}

        custom_optional_relation_ele.writeAttribute('name', 'field_relation[' + i + ']');
        custom_optional_relation_ele.writeAttribute('id', 'field_relation_id_' + i);
        custom_optional_relation_ele.insert(custom_optional_relation.innerHTML);

		for (i2 in field_options) {
			if (field_options.hasOwnProperty(i2)) {
				option = new Element('option', {value: field_options[i2].value}).update(field_options[i2].title);

				if (p_type == field_options[i2].value && (field_options[i2].popup == undefined || field_options[i2].popup == p_popup)) {
                    if(p_popup == 'browser_object')
                    {
                        if(p_identifier != '' && field_options[i2].relation == 1)
                        {
							if(p_multiselection > 0)
							{
								if(field_options[i2].multiselection != undefined)
								{
									option.writeAttribute('selected', 'selected'); // does not work in all other browsers except for FF
								}
							}
							else
							{
								if(field_options[i2].multiselection == undefined)
								{
									option.writeAttribute('selected', 'selected'); // does not work in all other browsers except for FF
								}
							}

                            for(var l = 0; custom_optional_relation_ele.options.length; l++)
                            {
                                if(custom_optional_relation_ele.options[l] == undefined)
                                {
                                    custom_optional.value = null;
                                    break;
                                }
                                else if(custom_optional_relation_ele.options[l].value == p_identifier)
                                {
                                    custom_optional_relation_ele.options[l].selected = "selected";
                                    break;
                                }
                            }

                        }
                        else if(p_identifier != '' && field_options[i2].relation == 0)
                        {
                            /* do nothing */
                        }
                        else if(p_identifier == '' && field_options[i2].relation == 0)
                        {
							if(p_multiselection > 0)
							{
								if(field_options[i2].multiselection != undefined)
								{
									option.writeAttribute('selected', 'selected'); // does not work in all other browsers except for FF
								}
							}
							else
							{
								if(field_options[i2].multiselection == undefined)
								{
									option.writeAttribute('selected', 'selected'); // does not work in all other browsers except for FF
								}
							}
                            p_identifier = '';
                        }
                    }
                    else
                    {
						if(p_multiselection > 0)
						{
							if(field_options[i2].multiselection != undefined)
							{
								option.writeAttribute('selected', 'selected');
							}
						}
						else if(field_options[i2].multiselection == undefined)
						{
							option.writeAttribute('selected', 'selected');
						}

                    }
				}

				if (field_options[i2].hasOwnProperty('extra')) {
					option.value += ',' + field_options[i2].extra;
				}

				if (field_options[i2].popup != undefined && field_options[i2].relation == undefined) {
					option.value += ',' + field_options[i2].popup;
				}

				if (field_options[i2].relation != undefined) {
					option.value += ',' + field_options[i2].popup + ',' + field_options[i2].relation;
				}

				if (field_options[i2].multiselection != undefined) {
					option.value += ',' + field_options[i2].multiselection;
				}

				custom_select.insert(option);
			}
		}

		var show_in_list_attributes = {type:'checkbox', value:'1', name:'field_show_in_list[' + i + ']'};
		if (p_show_in_list && p_show_in_list == 1)
		{
			show_in_list_attributes.checked = 'checked';
		}

		custom_show_in_list.insert(new Element('input', show_in_list_attributes));

		if ($('multivalued').getValue() == 0) {
			custom_show_in_list.hide();
		}

		custom_select.setValue(p_type);     // does not work in FF but in all other Browsers
		custom_select.on('change', window.change_field_type);

		var $row = new Element('li', {id: 'custom_' + i, className:'p5 border-top'})
			.insert(new Element('div', {className:'handle'}))
			.insert(custom_title)
			.insert(custom_select)
			.insert(custom_optional)
            .insert(custom_optional_relation_ele)
            .insert(custom_show_in_list)
			.insert(new Element('button', {className:'btn remove ml15', type:'button'})
				.update(new Element('img', {className:'vam mr5', src:'[{$dir_images}]icons/silk/cross.png'}))
				.insert(new Element('span', {className:'vam red'}).update('[{isys type="lang" ident="LC__SYSTEM__CUSTOM_CATEGORIES__FIELD_REMOVE"}]'))
			);

		custom_field_container.insert($row);

		custom_select.simulate('change');

		if (disable && disable === true)
		{
			// disable all options
			$A(custom_select.options).each(function(el){
				el.disabled = 'disabled';
			});
			custom_select.addClassName('disabled');
			custom_select.title = '[{isys type="lang" ident="LC__CMDB__CUSTOM_CATEGORIES__CHANGE_FIELD_TYPE_MESSAGE"}]';
		}

		window.set_observer();

		$('custom_fields_configuration_list').fire('configurationList:updated');

		// Add focus to the last field.
		try {
			custom_field_container.down('input[name^="field_title"]:last').focus();
		} catch (e) {
			// Do nothing...
		}
	};

	/**
	 * Function for (re-)setting the necessary observers.
	 */
	window.set_observer = function () {
		Sortable.destroy(custom_field_container);

		Sortable.create(custom_field_container, {
			tag:'li',
			handle:'handle'
		});
	};

	[{foreach from=$category_config item="config" key="key"}]
	window.add_custom_field('[{$key}]', '[{$config.title}]', '[{$config.type|default:'f_text'}]', '[{$config.popup|default:''}]', '[{$config.identifier|default:''}]', [{$config.show_in_list|default:1}], true, [{$config.multiselection|default:0}]);
	[{/foreach}]

	(function (){
		'use strict';

		var $multivalue = $('multivalued'),
			$custom_fields_list = $('custom_fields_configuration_list');

		$custom_fields_list.select('select').invoke('simulate', 'change');

		$custom_fields_list.on('change', '.fieldType', function(ev, $el) {
			var textarea = false,
				input = false;

			if (['script', 'html', 'hr'].indexOf($el.getValue()) >= 0) {
				$el.next('label.showInList').down('input')
					.writeAttribute('disabled', 'disabled')
					.removeAttribute('checked');
			} else {
				$el.next('label.showInList').down('input')
					.removeAttribute('disabled');
			}

			// Switching textareas and input fields for HTML and SCRIPT elements.
			if (['html', 'script'].indexOf($el.getValue()) >= 0) {
				input = $el.previous('input');

				if (input) {
					textarea = new Element('textarea', {rows:'10', name:input.getAttribute('name'), id:input.getAttribute('id'), className:'input input-small ml15 mr15'}).update(input.value);
					input.insert({after:textarea}).remove();
				} else {
					textarea = $el.previous('textarea');
				}

				textarea.setAttribute('placeholder', $el.down('option:selected').innerHTML + '..');
			} else {
				textarea = $el.previous('textarea');

				if (textarea) {
					textarea.insert({after:new Element('input', {type:'text', name:textarea.getAttribute('name'), id:textarea.getAttribute('id'), value:textarea.innerHTML.unescapeHTML(), className:'input input-small ml15 mr15'})}).remove();
				}
			}
		});

		// Handle multivalue changes
		$multivalue.on('change', function() {
			$custom_field_header.down('.showInList')[($multivalue.getValue() == 0) ? 'hide' : 'show']();
			$custom_fields_list.select('.showInList').invoke(($multivalue.getValue() == 0) ? 'hide' : 'show');
		});

		$custom_fields_list.on('click', 'button.remove', function (ev, $el) {
			$el.up('li').remove();

			$custom_fields_list.fire('configurationList:updated');
		});

		$custom_fields_list.on('configurationList:updated', function () {
			if ($custom_fields_list.select('li').length > 0) {
				isys_glob_enable_save();
			} else {
				isys_glob_disable_save();
			}
		});

		$multivalue.simulate('change');

		$custom_fields_list.fire('configurationList:updated');

		$$('a.show-api-info').invoke('on', 'click', function() {
			$$('div.api-info').invoke('toggle');
		});
	})();
</script>
[{/if}]