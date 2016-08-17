<style type="text/css">
	ul#category_list_overview,
	ul#category_list_active {
		margin: 0 !important;
	}

	ul#category_list_overview li:first-child,
	ul#category_list_active li:first-child {
		border: none !important;
	}
</style>
<table class="contentTable">
	<tr>
		<td class="key">[{isys type="lang" ident="LC__CMDB__OBJTYPE__ID"}]</td>
		<td class="value">[{isys type="f_data" name="C__OBJTYPE__ID"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type="lang" ident="LC__CMDB__OBJTYPE__NAME"}]</td>
		<td class="value">[{isys type="f_data" name="C__OBJTYPE__TRANSLATED_TITLE"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" name="C__OBJTYPE__TITLE" ident="LC__CMDB__OBJTYPE__CONST_NAME"}]</td>
		<td class="value">[{isys type="f_text" name="C__OBJTYPE__TITLE" p_bNoTranslation="1"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" name="C__OBJTYPE__SYSID_PREFIX" ident="LC__CMDB__OBJTYPE__SYSID_PREFIX"}]</td>
		<td class="value">[{isys type="f_text" name="C__OBJTYPE__SYSID_PREFIX" p_bNoTranslation="1"}]</td>
	</tr>
    <tr>
        <td class="key">
            [{isys type="f_label" name="C__OBJTYPE__AUTOMATED_INVENTORY_NO" ident="LC__CMDB__OBJTYPE__AUTOMATIC_INVENTORY_NUMBER"}]
        </td>
        <td class="value">
            [{isys type="f_text" name="C__OBJTYPE__AUTOMATED_INVENTORY_NO" p_bNoTranslation="1"}]
            [{if $placeholders}]
            <img src="images/icons/silk/help.png" class="vam" onclick="if($('placeholderHelper').visible() === false){$('placeholderHelper').slideDown({duration:0.2});}else{$('placeholderHelper').slideUp({duration:0.2});}" />

            <div class="box ml20 mt5 mb5 overflow-auto text-shadow" style="display:none;height:200px;" id="placeholderHelper">
                <table class="border-none m0 w100 listing hover" style="border:0;">
                    [{foreach from=$placeholders item="plholder" key="plkey"}]
                    <tr>
                        <td class="mouse-pointer">
                            <span><code>[{$plkey}]</code> = [{$plholder}]</span>
                        </td>
                    </tr>
                    [{/foreach}]
                </table>
            </div>
            [{/if}]
        </td>
    </tr>
	<tr>
		<td class="key">[{isys type="f_label" name="C__OBJTYPE__POSITION_IN_TREE" ident="LC__CMDB__OBJTYPE__POSITION_IN_TREE"}]</td>
		<td class="value">[{isys type="f_text" name="C__OBJTYPE__POSITION_IN_TREE" p_bNoTranslation="1"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" name="C__OBJTYPE__COLOR" ident="LC__CMDB__OBJTYPE__COLOR"}]</td>
		<td class="value">[{isys type="f_text" id="C__OBJTYPE__COLOR" name="C__OBJTYPE__COLOR" p_bNoTranslation="1"}]</td>
	</tr>

	<tr>
		<td class="key">[{isys type="f_label" name="C__OBJTYPE__GROUP_ID" ident="LC__CMDB__OBJTYPE__GROUP"}]</td>
		<td class="value">[{isys type="f_dialog" name="C__OBJTYPE__GROUP_ID" p_strTable="isys_obj_type_group" p_bDbFieldNN="1" tab="3"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" name="C__OBJTYPE__CATS_ID" ident="LC__CMDB__OBJTYPE__CATS"}]</td>
		<td class="value">[{isys type="f_dialog" name="C__OBJTYPE__CATS_ID" tab="3"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" name="C__OBJTYPE__SELF_DEFINED" ident="LC__CMDB__OBJTYPE__SELFDEFINED"}]</td>
		<td class="value">[{isys type="f_dialog"  name="C__OBJTYPE__SELF_DEFINED" p_bDisabled=true p_bDbFieldNN="1" tab="4"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" name="C__OBJTYPE__IS_CONTAINER" ident="LC__CMDB__OBJTYPE__LOCATION"}]</td>
		<td class="value">[{isys type="f_dialog" name="C__OBJTYPE__IS_CONTAINER" p_bDbFieldNN="1"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" name="C__OBJTYPE__RELATION_MASTER" ident="LC__CMDB__OBJTYPE__MASTER_RELATION"}]</td>
		<td class="value">[{isys type="f_dialog" name="C__OBJTYPE__RELATION_MASTER" p_bDbFieldNN="1"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" name="C__OBJTYPE__INSERTION_OBJECT" ident="LC__CMDB__OBJTYPE__INSERTION_OBJECT"}]</td>
		<td class="value">[{isys type="f_dialog" name="C__OBJTYPE__INSERTION_OBJECT" p_bDbFieldNN="1"}]</td>
	</tr>
	<tr>
		  <td class="key">[{isys type="f_label" name="C__OBJTYPE__SHOW_IN_TREE" ident="LC__CMDB__OBJTYPE__SHOW_IN_TREE"}]</td>
	  	  <td class="value">[{isys type="f_dialog" name="C__OBJTYPE__SHOW_IN_TREE"  p_bDbFieldNN="1"}]</td>
	</tr>
	<tr>
		<td class="key vat">[{isys type="f_label" name="C__OBJTYPE__IMG_NAME" ident="LC__CMDB__OBJTYPE__IMG_NAME"}]</td>
		<td class="value">
			[{isys type="f_dialog" name="C__OBJTYPE__IMG_NAME" id="C__OBJTYPE__IMG_NAME" p_bDbFieldNN=1}]
            [{if $objTypeImages}]
            <img src="images/icons/silk/help.png" class="vam" onclick="if($('objTypeImagesHelp').visible() === false){$('objTypeImagesHelp').slideDown({duration:0.2});}else{$('objTypeImagesHelp').slideUp({duration:0.2});}" />

			<div class="box ml20 mt5 mb5 overflow-auto text-shadow" style="display:none;height:200px;" id="objTypeImagesHelp">
				<table class="border-none m0 w100 listing hover" style="border:0;">
					[{foreach from=$objTypeImages item="image"}]
						<tr>
							<td class="mouse-pointer" onclick="if ($('C__OBJTYPE__IMG_NAME')) $('C__OBJTYPE__IMG_NAME').value = '[{$image}]';">
								<span><img src="images/objecttypes/[{$image}]" class="image-shadow vam mr5" style="width:25px;height:25px;" /> [{$image}]</span>
							</td>
						</tr>
					[{/foreach}]
				</table>
			</div>
            [{/if}]
		</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" name="C__OBJTYPE__ICON" ident="LC__UNIVERSAL__ICON"}]</td>
		<td class="value">
			[{isys type="f_text" name="C__OBJTYPE__ICON"}]
		</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" name="C__OBJTYPE__CONST" ident="LC__CMDB__OBJTYPE__CONST"}]</td>
		<td class="value">[{isys type="f_text" name="C__OBJTYPE__CONST"}]</td>
	</tr>
	<tr>
		<td class="key">Default Template</td>
		<td class="value">[{isys type="f_dialog" name="C__CMDB__OBJTYPE__DEFAULT_TEMPLATE" p_arData=$templates p_bDbFieldNN="0"}]</td>
	</tr>
	<tr>
		<td colspan="2">&nbsp;</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" name="C__CMDB__OVERVIEW__ENTRY_POINT" ident="LC__CMDB__OVERVIEW__ENTRY_POINT"}]</td>
	  	<td class="value">[{isys type="f_dialog" name="C__CMDB__OVERVIEW__ENTRY_POINT" p_bDbFieldNN="1"}]</td>
    </tr>
	<tr>
		<td class="key">[{isys type="lang" ident="LC__MODULE__SEARCH__CATG"}]</td>
		<td class="value">
			<img width="15px" height="15px" style="float:left;margin-right:5px;" title="" alt="" src="[{$dir_images}]empty.gif" class="infoIcon vam">
			<div id="qcw" class="ml20">
				<table id="main" >
					<tr>
						<td>
							<div class="box" style="min-height:250px;margin-right: 5px;">
								<h3 class="gradient p5">[{isys type="lang" ident="LC__CMDB__OBJTYPE__ASSIGNED_CATG"}]
									<span class="icon" style="margin-right:17px;">
										<label title="[{isys type="lang" ident="LC__UNIVERSAL__MARK_ALL"}]">
											<input type="checkbox" id="checkall" class="checkall" onclick="$$('.category_active').each(function(chk) {chk.checked = this.checked}.bind(this)); window.CategoryHandling.handle_objtype_overview(this.checked)" [{if $editmode != 1 || $row.sticky == 1}]disabled="disabled"[{else}][{/if}] />
										</label>
									</span>
								</h3>
								<div id="category_list_active_div">
									<ul class="qcw_category_list" id="category_list_active">
										[{foreach from=$arDialogList item=row}]
										<li class="p5 [{if $row.sel != 1}]disabled[{/if}]" id="category_[{$row.id}]">
											<span class="title" [{if $row.sel == 1}]style="color:#444444"[{/if}]>[{$row.val}]</span>
											<input class="category_active" type="checkbox" name="assigned_categories[]" value="[{$row.id}]" data-overview="[{$row.overview}]" data-directories="[{$row.directory_categories}]" [{if $editmode != 1 || $row.sticky == 1}]disabled="disabled"[{else}][{/if}] [{if $row.sel == 1}]checked="checked"[{/if}] />
										</li>
										[{/foreach}]
									</ul>
								</div>
							</div>
						</td>
						<td style="vertical-align: middle;">
							<img alt=">" src="[{$dir_images}]rsaquo.png" >
						</td>
						<td>
							<div class="box" style="min-height:250px;margin-right:350px">
								<h3 class="gradient p5">
									[{isys type="lang" ident="LC__CMDB__OBJTYPE__CATEGORIES_ON_THE_OVERVIEW"}]

									<span class="icon" style="margin-right:17px;">
										<label title="[{isys type="lang" ident="LC__UNIVERSAL__MARK_ALL"}]">
											<input type="checkbox" id="checkall" class="checkall" onclick="$$('.category_overview_active').each(function(chk) {chk.checked = this.checked}.bind(this));" [{if $editmode != 1 || $row.sticky == 1}]disabled="disabled"[{else}][{/if}] />
										</label>
									</span>
								</h3>
								<div id="category_list_overview_div">
									<ul class="qcw_category_list" id="category_list_overview">
										[{foreach from=$arDialogList2 item=row}]
										<li class="p5 [{if $row.sel != 1}]disabled[{/if}]" id="category_ov_[{$row.id}]">
											<span class="handle"></span>
											<span class="title" [{if $row.sel == 1}]style="color:#444444"[{/if}]>[{$row.val}]</span>
											<input class="category_overview_active" type="checkbox" name="assigned_cat_overview[]" value="[{$row.id}]" [{if $editmode != 1 || $category_overview_is_active == 0 || $row.sticky == 1}]disabled="disabled"[{else}][{/if}] [{if $row.sticky == 1}]data-sticky="true"[{/if}] [{if $row.sel == 1}]checked="checked"[{/if}] />
										</li>
										[{/foreach}]
									</ul>
								</div>
							</div>
						</td>
					</tr>
				</table>
			</div>
		</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" name="C__OBJTYPE__DESCRIPTION" ident="LC__CMDB__OBJTYPE__DESCRIPTION"}]</td>
		<td class="value">
			<span class="value" style="font-weight:normal; font-family:Fixedsys,Courier New,Sans-Serif,Serif,Monospace;">
			[{isys type="f_textarea" name="C__OBJTYPE__DESCRIPTION" p_nRows="6" p_bInfoIconDisabled="1" p_strStyle="font-weight:normal; font-family:Fixedsys,Courier,Sans-Serif,Serif,Monospace;"}]
			</span>
		</td>
	</tr>
</table>

[{if isys_glob_is_edit_mode()}]
<script type="text/javascript">
	(function () {
		"use strict";

		window.entry_point       = $('C__CMDB__OVERVIEW__ENTRY_POINT');
		window.objtype_color     = $('C__OBJTYPE__COLOR');
		window.ObjtypeCategories = Class.create({
				initialize: function () {
					$$('#category_list_active input.category_active').invoke('on', 'click', this.handle_objtype_category.bindAsEventListener(this));
					this.reset_observer_overview();
				},

				reset_observer_overview: function () {
					Sortable.destroy('category_list_overview');
					$$('#category_list_overview input.category_overview_active').invoke('stopObserving');

					Position.includeScrollOffsets = true;

					Sortable.create('category_list_overview', {tag:'li', handle:'handle'});
					$$('#category_list_overview input.category_overview_active').invoke('on', 'click', this.handle_objtype_overview_category.bindAsEventListener(this));
				},

				handle_objtype_overview_category: function (ev) {
					var el = ev.findElement();

					if (el.checked) {
						el.up('li').removeClassName('disabled');
						el.previous('span').setStyle({color:'#444444'});
					} else {
						el.up('li').addClassName('disabled');
						el.previous('span').setStyle({color:'#888888'});
					}
				},

				handle_objtype_overview: function (check) {
					if (check == 1) {
						$$('input.category_overview_active').each(function (ele) {
							if (ele.readAttribute('data-sticky') == null) {
								ele.disabled = false;
							}
						})
					} else {
						$$('input.category_overview_active').each(function (ele) {
							ele.disabled = true;
						})
					}
				},

				handle_objtype_category: function (p_ev) {
					var el = p_ev.findElement(),
						overview_category = el.readAttribute('data-overview'),
						directory_categories = el.readAttribute('data-directories'),
						ele_checked = el.checked,
						l_newElements = [];

					if (directory_categories) {
						l_newElements = directory_categories.evalJSON();
					}

					if (ele_checked) {
						el.up('li').removeClassName('disabled');
						el.previous('span').setStyle({color:'#444444'});
					} else {
						el.up('li').addClassName('disabled');
						el.previous('span').setStyle({color:'#888888'});
					}

					if (overview_category == 1) {
						l_newElements.push({id:  el.value, title: el.previous('span').innerHTML});
					}

					if (ele_checked) {
						l_newElements.each(function (elem) {
							var ele_id = elem.id,
								ele_title = elem.title,
								$li = new Element('li', {className:'p5 disabled', id:'category_ov_' + ele_id, 'data-const': 'const_' + ele_id}),
								$list_overview = $('category_list_overview'),
								$overview_box = $list_overview.up('div');

							$list_overview
								.insert(
									$li
										.insert(new Element('span', {className: 'handle'}))
										.insert(new Element('span', {className: 'title'}).update(ele_title))
										.insert(new Element('input', {type: 'checkbox', className: 'category_overview_active', disabled: (entry_point.getValue() == 0), name: 'assigned_cat_overview[]', value: ele_id})))
								.down('li:last input').on('click', this.handle_objtype_overview_category.bindAsEventListener(this));

							new Effect.Highlight($overview_box, {startcolor:'#88ff88', afterFinish:function() {$overview_box.setStyle({'backgroundColor':''})}});
						}.bind(this));
					} else {
						// Remove from overview list.
						l_newElements.each(function (elem) {
							$('category_ov_' + elem.id).remove();
						});
					}

					this.reset_observer_overview();
				}
			});
			window.CategoryHandling = new ObjtypeCategories();

		if (objtype_color) {
			new jscolor.color(objtype_color);
		}

		if (entry_point) {
			entry_point.on('change', function () {
				window.CategoryHandling.handle_objtype_overview(this.value);
			});
		}
	}());
</script>
[{/if}]
