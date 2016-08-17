<h2 class="p5 gradient border-bottom">[{isys type="lang" ident="LC__MODULE__QCW"}]</h2>

<div id="qcw" class="p10">
    <table id="main">
        <tr>
            <td>
                <div class="box">
                    <h3 class="gradient p5">[{isys type="lang" ident="LC__MODULE__QCW__OBJTYPEGROUPS"}]
                        <img id="objtypegroup_loading" src="[{$dir_images}]ajax-loading.gif" alt="[{isys type="lang" ident="LC__UNIVERSAL__LOADING"}]" style="vertical-align:middle; display:none;"/>
                    </h3>

                    <p class="m5">[{isys type="lang" ident="LC__MODULE__QCW__OBJTYPEGROUPS_DESCRIPTION"}]</p>
                    <ul>
                        <li class="p5 heading"><strong>[{isys type="lang" ident="LC__MODULE__QCW__NAME"}]</strong><span class="icon eye" title="[{isys type="lang" ident="LC__MODULE__QCW__VISIBILITY"}]"></span></li>
                    </ul>
                    <ul id="obj_type_group_list">
                        [{foreach from=$obj_type_groups key=const item=row}]
                        <li class="p5 [{if $row.selfdefined}]selfdefined[{/if}]" id="objtypegroup_[{$const}]" data-const="[{$const}]">
                            <span class="handle"></span>
                            <span class="title">[{$row.name}]</span>
                            <input class="objtypegroup_active" type="checkbox" [{if $row.active}]checked="checked"[{/if}] /> [{if $row.selfdefined}]<span class="icon edit"></span>[{/if}]
                        </li>
                        [{/foreach}]
                    </ul>

                    <div id="objtypegroup_new_edit" class="m5" style="display:none;">
                        <div>
                            [{isys type="f_text" name="C__MODULE__QCW__OBJTYPEGROUP_NAME" p_strClass="fl" editmode=true nowiki=true p_bInfoIconSpacer=0}]
                            <button id="C__MODULE__QCW__OBJTYPEGROUP_BUTTON_DELETE" class="btn fr ml5" title="[{isys type="lang" ident="LC__MODULE__QCW__DELETE"}]"><img src="[{$dir_images}]icons/silk/cross.png" alt="x" /></button>
                            <button id="C__MODULE__QCW__OBJTYPEGROUP_BUTTON" class="btn fr" title="[{isys type="lang" ident="LC__UNIVERSAL__BUTTON_SAVE"}]"><img src="[{$dir_images}]icons/silk/disk.png" alt="save" /></button>
                            <br class="cb" />
                        </div>
                    </div>

					<div class="p5 mt5">
						[{isys type="f_text" name="C__MODULE__QCW__OBJTYPEGROUP_NEW" p_strClass="fl" editmode=true nowiki=true p_bInfoIconSpacer=0 p_strPlaceholder="LC__MODULE__QCW__BUTTON_ADD_NEW_GROUP"}]
						<button id="C__MODULE__QCW__OBJTYPEGROUP_NEW_BUTTON" class="btn fr" title="[{isys type="lang" ident="LC__UNIVERSAL__BUTTON_ADD"}]"><img src="[{$dir_images}]icons/silk/add.png" alt="+"/></button>
						<br class="cb" />
					</div>
                </div>
            </td>

            <td class="rsaquo"><img src="[{$dir_images}]rsaquo.png" alt="&rsaquo;"/></td>

            <td>
	            <div id="object-type-hider" class="opacity-70"></div>

                <div class="box">
                    <h3 class="gradient p5">[{isys type="lang" ident="LC_UNIVERSAL__OBJECT_TYPES"}] in "<span id="objtypegroup_name">...</span>"<img id="objtype_loading" src="[{$dir_images}]ajax-loading.gif" alt="[{isys type="lang" ident="LC__UNIVERSAL__LOADING"}]" style="vertical-align:middle; display:none;"/></h3>

                    <p class="m5">[{isys type="lang" ident="LC__MODULE__QCW__OBJTYPE_DESCRIPTION"}]</p>
                    <ul>
                        <li class="p5 heading">
	                        <strong>[{isys type="lang" ident="LC__MODULE__QCW__NAME"}]</strong><span class="icon attach" title="[{isys type="lang" ident="LC__MODULE__QCW__ATTACHED"}]"></span>
                        </li>
                    </ul>
                    <div id="obj_type_list_div">
                        <ul id="obj_type_list">
                            [{foreach from=$obj_types item=row}]
                            <li class="p5 [{if $row.selfdefined}]selfdefined[{/if}] disabled" id="objtype_[{$row.const}]" data-const="[{$row.const}]" data-insertion="[{$row.insertion}]" data-container="[{$row.container}]">
                                <span class="handle"></span>
	                            <span class="obj-type">
	                                <span class="title">[{$row.title}]</span>
	                                <span class="used_in">[{if $row.used_in}]([{$row.used_in}])[{/if}]</span>
		                        </span>
                                <input class="objtype_active" type="checkbox" [{if $row.active}]checked="checked"[{/if}] disabled="disabled" /> [{if $row.selfdefined}]<span class="icon edit"></span>[{/if}]
                            </li>
                            [{/foreach}]
                        </ul>
                    </div>

                    <div id="objtype_new_edit" class="m5" style="display:none;">
                        <div>
                            [{isys type="f_text" name="C__MODULE__QCW__OBJTYPE_NAME" p_strClass="fl" editmode=true nowiki=true p_bInfoIconSpacer=0}]
                            <button id="C__MODULE__QCW__OBJTYPE_BUTTON_DELETE" class="btn fr ml5" title="[{isys type="lang" ident="LC__MODULE__QCW__DELETE"}]"><img src="[{$dir_images}]icons/silk/cross.png" alt="x" /></button>
                            <button id="C__MODULE__QCW__OBJTYPE_BUTTON" class="btn fr" title="[{isys type="lang" ident="LC__UNIVERSAL__BUTTON_SAVE"}]"><img src="[{$dir_images}]icons/silk/disk.png" alt="save" /></button>
                            <br class="cb" />
                            [{isys type="checkbox" p_bInfoIconSpacer="0" name="C__MODULE__QCW__CONTAINER_OBJECT_EDIT"}] [{isys type="f_label" ident="LC__MODULE__QCW__LOCATION_OBJECT" name="C__MODULE__QCW__CONTAINER_OBJECT_EDIT" p_strStyle="margin-right:15px;"}]
                            [{isys type="checkbox" p_bInfoIconSpacer="0" name="C__MODULE__QCW__INSERTION_OBJECT_EDIT"}] [{isys type="f_label" ident="LC__MODULE__QCW__POSITIONABLE_IN_RACK" name="C__MODULE__QCW__INSERTION_OBJECT_EDIT"}]
                        </div>
                    </div>

                    <div class="p5">
                        [{isys type="f_text" name="C__MODULE__QCW__OBJTYPE_NEW" p_strClass="fl" editmode=true nowiki=true p_bInfoIconSpacer=0 p_strPlaceholder="LC__MODULE__QCW__BUTTON_ADD_NEW_OBJTYPE"}]
                        <button id="C__MODULE__QCW__OBJTYPE_NEW_BUTTON" class="btn fr" type="[{isys type="lang" ident="LC__UNIVERSAL__BUTTON_ADD"}]"><img src="[{$dir_images}]icons/silk/add.png" alt="+" /></button>
                        <br class="cb" />
                        [{isys type="checkbox" p_bInfoIconSpacer="0" name="C__MODULE__QCW__CONTAINER_OBJECT"}] [{isys type="f_label" ident="LC__MODULE__QCW__LOCATION_OBJECT" name="C__MODULE__QCW__CONTAINER_OBJECT" p_strStyle="margin-right:15px;"}]
                        [{isys type="checkbox" p_bInfoIconSpacer="0" name="C__MODULE__QCW__INSERTION_OBJECT"}] [{isys type="f_label" ident="LC__MODULE__QCW__POSITIONABLE_IN_RACK" name="C__MODULE__QCW__INSERTION_OBJECT"}]
                    </div>
                </div>
            </td>

            <td class="rsaquo"><img src="[{$dir_images}]rsaquo.png" alt="&rsaquo;"/></td>

            <td>
                <div id="category-hider" class="opacity-70"></div>

                <div class="box">
                    <h3 class="gradient p5">[{isys type="lang" ident="LC__MODULE__QCW__CATEGORIES"}] in "<span id="objtype_name">...</span>"<img id="category_loading" src="[{$dir_images}]ajax-loading.gif" alt="[{isys type="lang" ident="LC__UNIVERSAL__LOADING"}]" style="vertical-align:middle; display:none;"/></h3>

                    <p class="m5">[{isys type="lang" ident="LC__MODULE__QCW__CATEGORY_DESCRIPTION"}]</p>
                    <ul>
                        <li class="p5 heading">
                            <strong>[{isys type="lang" ident="LC__MODULE__QCW__NAME"}]</strong><span class="icon attach" title="[{isys type="lang" ident="LC__MODULE__QCW__ATTACHED"}]"></span>
                        </li>
                    </ul>
                    <div>
                        <ul id="category_list">
                            [{foreach from=$categories item=row}]
                            <li class="p5 [{if $row.selfdefined}]selfdefined[{/if}] disabled" id="category_[{$row.const}]" data-const="[{$row.const}]">
                                <span class="title">[{$row.title}]</span>
                                <input class="category_active" type="checkbox" disabled="disabled" />
                            </li>
                            [{/foreach}]
                        </ul>
                    </div>

                    <div class="p5">
                        <button id="C__MODULE__QCW__CATEGORY_NEW_BUTTON" class="btn btn-block"><img src="[{$dir_images}]icons/silk/add.png" alt="+" class="mr5" /><span>[{isys type="lang" ident="LC__MODULE__QCW__CREATE_CUSTOM_CATEGORY"}]</span></button>
                    </div>
                </div>
            </td>
        </tr>
    </table>

    <table id="configuration">
        <tr>
            <td>
	            <div class="box">
	                <h3 class="gradient p5">[{isys type="lang" ident="LC__MODULE__QCW__SAVE_CURRENT_PROFILE"}]</h3>
		            <div class="p5">
			            <p>[{isys type="lang" ident="LC__MODULE__QCW__EXPORT_DESCRIPTION"}]</p>

						[{isys type="f_text" name="C__MODULE__QCW__CONFIG_TITLE" p_strClass="mt15 fl" nowiki=true p_bInfoIconSpacer="0" p_strPlaceholder="LC__MODULE__QCW__NAME"}]
			            <button id="C__MODULE__QCW__SAVE_CONFIG" class="btn ml5 mt15"><img src="[{$dir_images}]icons/silk/disk.png" class="mr5" /><span>[{isys type="lang" ident="LC__UNIVERSAL__BUTTON_SAVE"}]</span></button>
			            <br class="cb" />
                    </div>
                </div>

                <div class="box mt15">
                    <h3 class="gradient p5">[{isys type="lang" ident="LC__MODULE__QCW__RESET"}]</h3>

                    <div class="m5">
                        <p>[{isys type="lang" ident="LC__MODULE__QCW__RESET_DESCRIPTION"}]</p>
	                    <p id="reset_message" class="note m5 p5"><img src="[{$dir_images}]ajax-loading.gif" class="vam" /> [{isys type="lang" ident="LC__UNIVERSAL__LOADING"}]</p>
                        <button class="btn btn-block mt15" id="C__MODULE__QCW__RESET_CONFIG"><span>[{isys type="lang" ident="LC__MODULE__QCW__BUTTON_RESET"}]</span></button>
                    </div>
                </div>
            </td>
            <td class="spacer">
            </td>
            <td>
                <div class="box">
	                <h3 class="gradient p5">[{isys type="lang" ident="LC__MODULE__QCW__ADD_PROFILES"}]</h3>

	                <div class="p5">
			            <p>[{isys type="lang" ident="LC__MODULE__QCW__IMPORT_DESCRIPTION"}]</p>

                        <input type="file" class="mt15" name="import_file" onChange="document.forms[0].submit()"/>

		                [{if $upload_error}]<p class="error">[{$upload_error}]</p>[{/if}]

		                [{if count($config_files) > 0}]
		                <table class="mainTable mt15 border">
			                <thead>
			                <tr>
				                <th>&nbsp;</th>
				                <th>[{isys type="lang" ident="LC__MODULE__QCW__FILE"}]</th>
				                <th>[{isys type="lang" ident="LC__MODULE__QCW__ACTIONS"}]</th>
			                </tr>
			                </thead>
			                <tbody>
			                [{foreach from=$config_files item=file_item key=file_key}]
				                <tr id="row_[{$file_key}]" class="[{cycle values="CMDBListElementsEven,CMDBListElementsOdd"}]">
					                <td class="small">[{isys type="checkbox" p_bInfoIconSpacer="0" name="C__MODULE__QCW__FILE_ITEM[]" p_strClass="qcw_profile_files" p_strValue=$file_item}]</td>
					                <td class="large">[{$file_item}]</td>
					                <td class="small">
						                <a type="application/octet-stream" href="[{$download_link|cat:$file_item}]" class="btn btn-small"><img src="[{$dir_images}]icons/silk/disk.png" title="[{isys type="lang" ident="LC__MODULE__QCW__DOWNLOAD"}]"></a>
						                <img src="[{$dir_images}]icons/silk/cross.png" title="[{isys type="lang" ident="LC__MODULE__QCW__DELETE"}]" onclick="QCW.delete_config_file('[{$file_item}]', 'row_[{$file_key}]');" class="btn btn-small">
					                </td>
				                </tr>
			                [{/foreach}]
			                </tbody>
		                </table>
		                [{/if}]

                        <button id="C__MODULE__QCW__LOAD_CONFIG" class="btn btn-block mt15"><span>[{isys type="lang" ident="LC__MODULE__QCW__LOAD"}]</span></button>
                    </div>
                </div>
            </td>
        </tr>
    </table>
</div>

<script type="text/javascript" language="JavaScript" src="[{$dir_tools}]js/qcw/quick_configuration_wizard.js"></script>

<script type="text/javascript">
	// Create a new QCW instance.
	var QCW = new QuickConfWizard({
			ajax_url: '?ajax=1&call=quick_configuration_wizard',
			confirm_delete_file: '[{isys type="lang" ident="LC__MODULE__QCW__DELETE_FILE" p_bHtmlEncode=false}]',
			confirm_objtypegroup_delete: '[{isys type="lang" ident="LC__MODULE__QCW__DELETE_OBJTYPEGROUPS" p_bHtmlEncode=false}]',
			confirm_objtype_delete: '[{isys type="lang" ident="LC__MODULE__QCW__DELETE_OBJTYPE" p_bHtmlEncode=false}]',
			message_obj_type_sorting_notice: '[{isys type="lang" ident="LC__MODULE__QCW__OBJ_TYPE_SORTING_MODE_IS_ALPHABETICALLY" p_bHtmlEncode=false}]',
			object_type_sort: '[{if $obj_type_sorting_alphabetical}]alphabetically[{else}]manual[{/if}]',
			$objTypeHider:$('object-type-hider'),
			$categoryHider:$('category-hider')
		}),
		description_height = 0;

	// On this page, the form shall not be submitted accidentally!
	Event.observe('isys_form', 'submit', function (ev) {
		Event.stop(ev);
	});

	// Only submit the form, by click on this buttons.
	$('C__MODULE__QCW__SAVE_CONFIG').on('click', function () {
		$('isys_form').submit();
	});

	// Import profile to the database
	$('C__MODULE__QCW__LOAD_CONFIG').on('click', function () {
		var profiles = [];
		$$('.qcw_profile_files').each(function (ele) {
			if (ele.checked) {
				profiles.push(ele.value);
			}
		});

		if (profiles.length > 0) {
			new Ajax.Request('?ajax=1&call=quick_configuration_wizard', {
				parameters: {
					func: 'load_profiles',
					profile_files: Object.toJSON(profiles)
				},
				method: 'post',
				onSuccess: function () {
					/* This triggers a get request */
					window.location.href = window.location.href;
				}
			});
		}
	});

	// A semi-link to the custom-category module.
	$('C__MODULE__QCW__CATEGORY_NEW_BUTTON').on('click', function () {
		location.href = '[{$user_category_module}]';
	});

	// Trigger an ajax call for the "reset" button.
	$('C__MODULE__QCW__RESET_CONFIG').on('click', function () {
		if (confirm('[{isys type="lang" ident="LC__MODULE__QCW__RESET_CONFIRM" p_bHtmlEncode=0}]')) {
			$('reset_message').show();

			new Ajax.Request('?ajax=1&call=quick_configuration_wizard', {
				parameters: {
					func: 'reset'
				},
				method: 'post',
				onSuccess: function (result) {
					var json = result.responseJSON;
					$('objtype_loading').hide();

					if (json.success) {
						// If everything went fine, reload the page.
						$('reset_message').update('[{isys type="lang" ident="LC__MODULE__QCW__RESET_SUCCESS"}]');

						/* This triggers a get request */
						window.location.href = window.location.href;
					} else {
						// Do some error messaging.
						$('reset_message')
							.removeClassName('note')
							.addClassName('error')
							.update(new Element('img', {src: '[{$dir_images}]icons/silk/cross.png', className: 'vam'}))
							.insert(json.message);
					}
				}
			});
		}
	});

	$('reset_message').hide();

	// Every box introduction shall have the same height (looks WAY better).
	$$('#qcw div.box p').each(function (el) {
			if (el.getHeight() > description_height) {
				description_height = el.getHeight();
			}
		}).each(function (el, i) {
			if (i < 3) {
				el.setStyle({height: description_height + 'px'});
			}
		});
</script>