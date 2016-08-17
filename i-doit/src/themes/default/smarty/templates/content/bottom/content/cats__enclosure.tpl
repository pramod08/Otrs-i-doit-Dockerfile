<div class="p10">
	<div id="rack_stats" class="box mb5">
	    <h3 class="p5 gradient text-shadow" style="border-bottom:1px solid #B7B7B7; font-weight:normal; cursor:pointer;"><img class="vam" src="[{$dir_images}]icons/silk/bullet_arrow_down.png" alt="Icon" /> [{isys type="lang" ident="LC__CMDB__CATS__RACK__STATS"}]</h3>
		<div class="m5">
	         <!-- To be filled by AJAX -->
	    </div>
	</div>

	<div id="rackview">
		<div class="fl">
			<h3 class="p5 gradient text-shadow" style="border:1px solid #B7B7B7">[{isys type="lang" ident="LC__CMDB__CATG__LOCATION_FRONT"}]:</h3>
			<div id="rack_front" class="contentTable"></div>
		</div>

		<div class="fl ml5 mr10">
			<h3 class="p5 ml5 gradient text-shadow" style="border:1px solid #B7B7B7">[{isys type="lang" ident="LC__CMDB__CATG__LOCATION_BACK"}]:</h3>
			<div id="rack_rear" class="contentTable ml5"></div>
		</div>

		<div id="side_box" class="fl">
			<div class="mb15 box">
				<h3 class="p5 gradient text-shadow" style="border-bottom:1px solid #B7B7B7;font-weight:normal;">[{isys type="lang" ident="LC__CMDB__CATS__RACK__ATTRIBUTES"}]:</h3>

				<div class="m10">
					<table>
						<tr>
							<td class="key">
								[{isys type="f_label" ident="LC__CMDB__CATS__ENCLOSURE__SLOT_SORTING" name="C__CATS__ENCLOSURE__UNIT_SORTING"}]
							</td>
							<td class="value">
								[{isys type="f_dialog" name="C__CATS__ENCLOSURE__UNIT_SORTING" p_onChange="window.update_slot_sorting();" p_bDbFieldNN=true}]
							</td>
						</tr>
						<tr>
							<td class="key">
								[{isys type="f_label" ident="LC__CMDB__CATS__ENCLOSURE__VERTICAL_SLOTS" name="C__CATS__ENCLOSURE__VERTICAL_SLOTS_FRONT"}] ([{isys type="lang" ident="LC__UNIVERSAL__FRONT"}])
							</td>
							<td class="value">
								[{isys type="f_dialog" name="C__CATS__ENCLOSURE__VERTICAL_SLOTS_FRONT" p_onChange="window.update_vertical_slots();"}]
							</td>
						</tr>
						<tr>
							<td class="key">
								[{isys type="f_label" ident="LC__CMDB__CATS__ENCLOSURE__VERTICAL_SLOTS" name="C__CATS__ENCLOSURE__VERTICAL_SLOTS_REAR"}] ([{isys type="lang" ident="LC__UNIVERSAL__REAR"}])
							</td>
							<td class="value">
								[{isys type="f_dialog" name="C__CATS__ENCLOSURE__VERTICAL_SLOTS_REAR" p_onChange="window.update_vertical_slots();"}]
							</td>
						</tr>
					</table>
				</div>
			</div>

			<div id="unassigned_objects" class="mb15 box">
				<h3 class="p5 gradient text-shadow" style="border-bottom:1px solid #B7B7B7;font-weight:normal;">[{isys type="lang" ident="LC__CMDB__CATG__LOCATION_UNPOSITIONED"}]:</h3>

				<div>
					<div class="list">
					[{foreach from=$objects key=obj_id item=object}]
						[{if $object.insertion === null || $object.pos == 0}]
							<div style="background:[{$object.color}];" data-background="[{$object.color}]">
								<span id="object-[{$obj_id}]" class="unassigned_object fl" data-object-id="[{$obj_id}]" data-object-height="[{$object.height}]">
									<b>[{$object.height}] [{isys type="lang" ident="LC__CMDB__CATG__RACKUNITS_ABBR"}]</b> [{$object.title}] ([{$object.type}], FF: [{$object.formfactor}])
								</span>

								[{if $has_edit_right}]
								<span class="location-remove" data-object-id="[{$obj_id}]" title="[{isys type="lang" ident="LC_UNIVERSAL__REMOVE_LOCATION"}]">
									<img src="[{$dir_images}]icons/silk/cross.png" alt="[{isys type="lang" ident="LC_UNIVERSAL__REMOVE_LOCATION"}]" />
								</span>
								<span class="he-edit" data-object-id="[{$obj_id}]" data-object-height="[{$object.height}]" data-object-title="[{$object.title}]" title="[{isys type="lang" ident="LC__CMDB__CATS__RACK__CHANGE_HEIGHT_UNITS"}]">
									<img src="[{$dir_images}]icons/silk/cog.png" alt="[{isys type="lang" ident="LC__CMDB__CATS__RACK__CHANGE_HEIGHT_UNITS"}]" />
								</span>
								[{/if}]
								<br class="clear" />
							</div>
						[{/if}]
					[{/foreach}]
					</div>

					<a id="new" class="navbar_item" href="[{$category_link}]">
						[{isys type="lang" ident="LC__CMDB__CATS__RACK__MANAGE_OBJECTS"}]
					</a><br class="clear" />
				</div>
			</div>

			<div id="edit_height_unit" class="box">
				<h3 class="p5 gradient text-shadow" style="border-bottom:1px solid #B7B7B7;font-weight:normal;">[{isys type="lang" ident="LC__CMDB__CATS__RACK__CHANGE_HEIGHT_UNITS"}]:</h3>

				<div class="m10">
					<p id="edit_height_unit_description" class="mb5">[{isys type="lang" ident="LC__UNIVERSAL__LOAD"}]</p>

					<select id="object_he_units" class="inputDialog" disabled="disabled"></select>

					<input type="button" id="object_he_units_submit" disabled="disabled" value="[{isys type="lang" ident="LC__NAVIGATION__NAVBAR__SAVE"}]" />
				</div>
			</div>

			<div id="assign_object" class="box">
				<h3 class="p5 gradient text-shadow" id="object-positioning" style="border-bottom:1px solid #B7B7B7;font-weight:normal;">[{isys type="lang" ident="LC__CMDB__CATG__LOCATION_POSITIONING"}]:</h3>


				<div class="m10">
					<select id="rack_option" class="input input-block mb10" disabled="disabled"></select>

					<select id="rack_insertion" class="input input-block mb10" disabled="disabled"></select>

					<select id="rack_position" class="input input-block mb10" disabled="disabled"></select>

					<button type="button" class="btn" id="rack_submit" disabled="disabled">[{isys type="lang" ident="LC__CMDB__CATG__LOCATION_POSITIONING"}]</button>
				</div>
			</div>
		</div>

		<br class="clear" />
	</div>

	<script type="text/javascript">
		(function(){
			'use strict';

			var $contentWrapper = $('contentWrapper'),
				$rackView = $('rackview'),
				$rackStats = $('rack_stats'),
				$sideBox = $('side_box');

			[{if $smarty.get.catsID == $smarty.const.C__CATS__ENCLOSURE}]
			$contentWrapper.on('scroll', function () {
				var top = $contentWrapper.cumulativeScrollOffset().top,
					limit = 140;

				if (infiniteScroll()) {
					$sideBox
						.addClassName('mt5')
						.setStyle({top: 'auto'});
				} else {
					if ($rackStats.down('div').visible()) {
						limit += $rackStats.down('div').getHeight() + 10;
					}

					$sideBox
						.removeClassName('mt5')
						.setStyle({top: (top > limit) ? (top - limit) + 'px' : 0});
				}
			});

			function infiniteScroll () {
				if ($rackView.getWidth() < 1081) {
					return true;
				}

				if ($contentWrapper.getHeight() <= $sideBox.getHeight()) {
					return true;
				}
			}
			[{/if}]
		})();


		window.images_dir = '[{$dir_images}]';
		idoit.Translate.set('LC__CMDB__CATS__RACK__REASSIGN_OBJECT', '[{isys type="lang" ident="LC__CMDB__CATS__RACK__REASSIGN_OBJECT"}]');
		idoit.Translate.set('LC__CMDB__CATS__RACK__REMOVE_OBJECT', '[{isys type="lang" ident="LC__CMDB__CATS__RACK__REMOVE_OBJECT"}]');
		idoit.Translate.set('LC__UNIVERSAL__TITLE_LINK', '[{isys type="lang" ident="LC__UNIVERSAL__TITLE_LINK"}]');

		window.update_vertical_slots = function () {
			rack_front.update_vertical_slots($('C__CATS__ENCLOSURE__VERTICAL_SLOTS_FRONT').value);
			rack_rear.update_vertical_slots($('C__CATS__ENCLOSURE__VERTICAL_SLOTS_REAR').value);
		};

		window.update_slot_sorting = function () {
			var sorting = $('C__CATS__ENCLOSURE__UNIT_SORTING').value;

			rack_front.set_slot_sorting(sorting).create_slots();
			rack_rear.set_slot_sorting(sorting).create_slots();
		};

		// Functions and variables for the object-assignment.
		var object = 0,
			objects = '[{$objects_json|escape:"javascript"}]'.evalJSON(),
			object_height = 0,
			rack_slots = parseInt('[{$rack_slots}]'),
			option = 0,
			option_preselection = 0,
			insertion = 0,
			insertion_preselection = 0,
			position = 0,
			position_preselection = 0,
			he_change_text = '[{isys type="lang" ident="LC__CMDB__CATS__RACK__CHANGE_HEIGHT_UNITS__DESCRIPTION"}]',
			statistics_loaded = false;

		// Function to reset and hide the assignment-elements.
		window.reset_object_assignment = function () {
			object = 0;
			object_height = 0;
			option = 0;
			option_preselection = 0;
			insertion = 0;
			insertion_preselection = 0;
			position = 0;
			position_preselection = 0;

			$('object-positioning').update();

			$$('#rackview .active').invoke('removeClassName', 'active');

			$('edit_height_unit').hide();
			$('assign_object').hide();
			$('rack_option').disable().hide();
			$('rack_insertion').disable().hide();
			$('rack_position').disable().hide();
		};

		// Function that gets called, when an object from the "not-assigned" area is clicked.
		window.select_object_for_assignment = function () {
			var object_title;

			window.reset_object_assignment();

			object = this.readAttribute('data-object-id');
			object_height = this.readAttribute('data-object-height');

			// We try to preselect the vertical items, if possible.
			option_preselection = '[{$smarty.const.C__RACK_INSERTION__HORIZONTAL}]';
			if (this.up().hasClassName('rotated')) {
				option_preselection = '[{$smarty.const.C__RACK_INSERTION__VERTICAL}]';
			}

			// Here we try to select the front, back or both sides as preselection.
			if ($('rack_front').down('#object-' + object) && $('rack_rear').down('#object-' + object)) {
				insertion_preselection = '[{$smarty.const.C__RACK_INSERTION__BOTH}]';
			} else if ($('rack_rear').down('#object-' + object)) {
				insertion_preselection = '[{$smarty.const.C__RACK_INSERTION__BACK}]';
			} else {
				insertion_preselection = '[{$smarty.const.C__RACK_INSERTION__FRONT}]';
			}

			// And finally we try to receive the position of the clicked object.
			if (this.up('tr.row')) {
				position_preselection = parseInt(this.up('tr.row').down('td').innerHTML);
			} else if (this.up('div.slot')) {
				position_preselection = this.up('div.slot').readAttribute('data-slot');
			}

			if (this.hasClassName('unassigned_object')) {
				this.addClassName('active');
				object_title = this.innerHTML.stripTags();
			} else {
				object_title = this.up().down('li').innerHTML;
			}

	        // If this action is called from the option-drop down, we remove it now.
	        if ($('contextWrapper')) {
	            $('contextWrapper').remove();
	        }

			$('object-positioning').update(object_title);
			$('assign_object').show();
			$('rack_option').enable().setValue(option_preselection).show().simulate('change');

	        new Effect.Highlight('assign_object', {startcolor:'#ddffdd', restorecolor: '#fff'});
		};

		// This function gets called, when we change the option (horizontal / vertical).
		window.assign_option_changed = function () {
			option = $('rack_option').getValue();

			new Ajax.Request('?ajax=1&call=rack&func=get_rack_insertions',
				{
					parameters:{
						'obj_id': parseInt([{$object_id}]),
						'option': option
					},
					method:"post",
					onSuccess:function (transport) {
						var insertion = $('rack_insertion').update();

						transport.responseJSON.each(function (option) {
							insertion.insert(new Element('option', {value:option.id}).update(option.title));
						}.bind(this));

						insertion.enable().setValue(insertion_preselection).show().simulate('change');
					}
				});
		};

		// This function gets called, when we change the insertion (front, back, both).
		window.assign_insertion_changed = function () {
			insertion = $('rack_insertion').value;

			window.display_position_selection();
		};

		window.display_position_selection = function () {
			// At first we load the free slots, so we don't have to mess around with various calculation later on.
			new Ajax.Request('?ajax=1&call=rack&func=get_free_slots',
				{
					parameters:{
						'rack_obj_id': parseInt([{$object_id}]),
						'assign_obj_id': object,
						'option': option,
						'insertion': insertion
					},
					method:"post",
					onSuccess:function (transport) {
						var slots = transport.responseJSON,
							slot_hash,
							position_field = $('rack_position').update();

						if (transport.responseText != '[]') {
							slot_hash = $H(slots);

							slot_hash.each(function(pair) {
								var fromto = pair.key.split(';');

								position_field.insert(new Element('option', {value: fromto[0], 'data-from': fromto[0], 'data-to': fromto[1]}).update(pair.value));
							}.bind(this));

							position_field.enable().setValue(position_preselection).show().simulate('change');
							$('rack_submit').enable();
						} else {
							position_field.disable();
							$('rack_submit').disable();
						}
					}
				});
		};

		// This function is called, when we selected a position to assign our object to.
		window.assign_position_changed = function () {
			var index = this.selectedIndex,
				pos_option = this.down('option', index),
				assign_from = parseInt(pos_option.readAttribute('data-from')),
				assign_to = parseInt(pos_option.readAttribute('data-to')),
				i;

			position = pos_option.value;

			$$('.row, .slot').invoke('removeClassName', 'selected');

			// This is used to highlight the assignment area.
			if (option != '[{$smarty.const.C__RACK_INSERTION__VERTICAL}]') {
				for ([{if $rack_slot_sorting == 'asc'}]i = assign_from; i <= assign_to; i++[{else}]i = assign_from; i >= assign_to; i--[{/if}]) {
					if (insertion == '[{$smarty.const.C__RACK_INSERTION__BOTH}]') {
						$$('.slot-' + i).invoke('addClassName', 'selected');
					} else if (insertion == '[{$smarty.const.C__RACK_INSERTION__FRONT}]') {
						$$('#rack_front .slot-' + i)[0].addClassName('selected');
					} else if (insertion == '[{$smarty.const.C__RACK_INSERTION__BACK}]') {
						$$('#rack_rear .slot-' + i)[0].addClassName('selected');
					}
				}
			} else {
				if (insertion == '[{$smarty.const.C__RACK_INSERTION__FRONT}]') {
					$$('#rack_front .left-slots .slot, #rack_front .right-slots .slot').each(function(el) {
						if (el.readAttribute('data-slot') == assign_from) {
							el.addClassName('selected');
						}
					});
				} else if (insertion == '[{$smarty.const.C__RACK_INSERTION__BACK}]') {
					$$('#rack_rear .left-slots .slot, #rack_rear .right-slots .slot').each(function(el) {
						if (el.readAttribute('data-slot') == assign_from) {
							el.addClassName('selected');
						}
					});
				}
			}

			$('rack_submit').enable().show();
		};

		// Function for finally assigning an object to the rack and removing it from the "unassigned" field.
		window.assign_object = function() {
			var relative_position;

			if (option == '[{$smarty.const.C__RACK_INSERTION__HORIZONTAL}]') {
				relative_position = $$('.selected')[0].readAttribute('data-slotnum');
			} else {
				relative_position = $$('.selected')[0].readAttribute('data-slot');
			}

			new Ajax.Request('?ajax=1&call=rack&func=assign_object_to_rack',
				{
					parameters:{
						'rack_obj_id':parseInt('[{$object_id}]'),
						'obj_id':object,
						'pos':relative_position,
						'option':option,
						'insertion':insertion
					},
					method:"post",
					onSuccess:function (transport) {
						if ($('object-' + object)) {
							$('object-' + object).up('div').remove();
						}

						rack_front.set_objects(transport.responseJSON).create_rack();
						rack_rear.set_objects(transport.responseJSON).create_rack();

						window.reset_object_assignment();
						window.create_tooltips();
					}.bind(this)
				});
		};

		// Function for removing an object from the rack and making it selectable in the "unassigned" field.
		window.remove_object_assignment = function() {
			var removed_object = this.readAttribute('data-object-id');

	        // If this action is called from the option-drop down, we remove it now.
	        if ($('contextWrapper')) {
	            $('contextWrapper').remove();
	        }

			new Ajax.Request('?ajax=1&call=rack&func=remove_object_assignment',
				{
					parameters:{
						'rack_obj_id':parseInt('[{$object_id}]'),
						'obj_id':removed_object
					},
					method:"post",
					onSuccess:function (transport) {
						var i;

						for (i in objects) {
	                        // Fix for iterating through member methods.
	                        if (objects.hasOwnProperty(i)) {
	                            if (objects[i].id == removed_object) {
	                                object = objects[i];
	                            }
	                        }
						}

						var unassigned = new Element('span', {
								id: 'object-' + object.id,
								className: 'unassigned_object fl',
								'data-object-height': object.height,
								'data-object-id': object.id})
							.update('<b>' + object.height + ' [{isys type="lang" ident="LC__CMDB__CATG__RACKUNITS_ABBR"}]</b> ' + object.title + ' (' + object.type + ', FF: ' + object.formfactor + ')');

						var ru_edit_button = new Element('span', {
								className: 'he-edit',
								title: '[{isys type="lang" ident="LC__CMDB__CATS__RACK__CHANGE_HEIGHT_UNITS"}]',
								'data-object-title': object.title,
								'data-object-height': object.height,
								'data-object-id': object.id})
							.update(new Element('img', {src: window.images_dir + 'icons/silk/cog.png'}));

						var location_remove_button = new Element('span', {
							className: 'location-remove',
							title: '[{isys type="lang" ident="LC_UNIVERSAL__REMOVE_LOCATION" p_bHtmlEncode=0}]',
							'data-object-id': object.id})
							.update(new Element('img', {src: window.images_dir + 'icons/silk/cross.png'}));

						$('unassigned_objects').down('div.list').insert(
							new Element('div')
								.setStyle({background: object.color})
								.writeAttribute('data-background', object.color)
								.update(unassigned)
								.insert(location_remove_button)
								.insert(ru_edit_button)
								.insert(new Element('br', {className: 'clear'})));

						$$('.unassigned_object').invoke('on', 'click', window.select_object_for_assignment);
						$$('.he-edit').invoke('on', 'click', window.edit_height_unit);
						$$('.location-remove').invoke('on', 'click', window.remove_location);

						rack_front.set_objects(transport.responseJSON).create_rack();
						rack_rear.set_objects(transport.responseJSON).create_rack();

						window.reset_object_assignment();
						window.create_tooltips();
					}.bind(this)
				});
		};

		// Function for displaying the edit-dialog for an objects RU's.
		window.edit_height_unit = function() {
			window.reset_object_assignment();

			$('edit_height_unit').show();
			$('edit_height_unit_description').update(he_change_text.replace('%s', '<strong>' + this.readAttribute('data-object-title') + '</strong>'));

			$('object_he_units_submit').enable();
			$('object_he_units').enable().update();

			object = this.readAttribute('data-object-id');

			$R(1, rack_slots).each(function(val) {
				$('object_he_units').insert(new Element('option', {value: val}).update(val + ' [{isys type="lang" ident="LC__CMDB__CATG__RACKUNITS_ABBR"}]'));
			});

			$('object_he_units').setValue(this.readAttribute('data-object-height'));
		};

		window.remove_location = function() {
			var remove = confirm('[{isys type="lang" ident="LC__CMDB__CATS__RACK__REMOVE_OBJECT_LOCATION"}]'),
				obj_id = this.readAttribute('data-object-id'),
				obj_el = this.up('div');

			if (remove) {
				new Ajax.Request('?ajax=1&call=rack&func=detach_object_from_rack',
					{
						parameters:{
							'obj_id':obj_id
						},
						method:"post",
						onSuccess:function (transport) {
							var json = transport.responseJSON;

							if (json.success) {
								obj_el.remove();
							} else {
								new Effect.Highlight(obj_el, {startcolor: '#ffB7B7', endcolor: obj_el.readAttribute('data-background')});
							}
						}.bind(this)
					});
			}
		};

		// Function for saving the new RU.
		window.save_height_unit = function() {
			new Ajax.Request('?ajax=1&call=rack&func=save_object_ru',
				{
					parameters:{
						'obj_id':object,
						'height':$('object_he_units').value
					},
					method:"post",
					onSuccess:function (transport) {
						var json = transport.responseJSON,
							obj = $('object-' + object),
							obj_div = obj.up();

						if (json.success) {
							obj.writeAttribute('data-object-height', json.new_height).down('b').update(json.new_height + ' [{isys type="lang" ident="LC__CMDB__CATG__RACKUNITS_ABBR"}]');

							new Effect.Highlight(obj_div, {startcolor: '#88ff88', endcolor: obj_div.readAttribute('data-background')});
							window.reset_object_assignment();
						} else {
							new Effect.Highlight(obj_div, {startcolor: '#ffB7B7', endcolor: obj_div.readAttribute('data-background')});
							window.reset_object_assignment();
						}
					}
				});
		};

	    // Function for loading and displaying the statistics
		window.toggle_statistics = function() {
			var el = $('rack_stats').down('div').toggle();

	        if (el.getStyle('display') == 'block') {
	            $('rack_stats').down('h3 img').writeAttribute('src', '[{$dir_images}]icons/silk/bullet_arrow_up.png');
	        } else {
	            $('rack_stats').down('h3 img').writeAttribute('src', '[{$dir_images}]icons/silk/bullet_arrow_down.png');
	        }

	        if (!statistics_loaded) {
	            statistics_loaded = true;
	            new Ajax.Request('?ajax=1&call=statistic&func=get_rack_statistics',
	                    {
	                        parameters:{
	                            'obj_id': parseInt([{$object_id}]),
	                            'as_json': false
	                        },
	                        method:"post",
	                        onSuccess:function (transport) {
	                            el.update(transport.responseText);
	                        }.bind(this)
	                    });
	        }
		};

		// Hide the assignment form-elements.
		window.reset_object_assignment();

		[{if $has_edit_right}]
		// We observe severel objects.
		$$('.unassigned_object').invoke('on', 'click', window.select_object_for_assignment);
		$$('.he-edit').invoke('on', 'click', window.edit_height_unit);
		$$('.location-remove').invoke('on', 'click', window.remove_location);
		[{/if}]
		$('rack_stats').down('h3').on('click', window.toggle_statistics);
		$('rack_stats').down('div').hide();

		[{if $new_entry}]
		if ($$('input[name="SM2__C__CATS__ENCLOSURE__UNIT_SORTING[p_strSelectedID]"]')[0]) $$('input[name="SM2__C__CATS__ENCLOSURE__UNIT_SORTING[p_strSelectedID]"]')[0].value = '';
		if ($$('input[name="SM2__C__CATS__ENCLOSURE__VERTICAL_SLOTS_FRONT[p_strSelectedID]"]')[0]) $$('input[name="SM2__C__CATS__ENCLOSURE__VERTICAL_SLOTS_FRONT[p_strSelectedID]"]')[0].value = '-1';
		if ($$('input[name="SM2__C__CATS__ENCLOSURE__VERTICAL_SLOTS_REAR[p_strSelectedID]"]')[0]) $$('input[name="SM2__C__CATS__ENCLOSURE__VERTICAL_SLOTS_REAR[p_strSelectedID]"]')[0].value = '-1';
		[{/if}]

		// Now we set the observer for our form-fields.
		$('rack_option').on('change', window.assign_option_changed);
		$('rack_insertion').on('change', window.assign_insertion_changed);
		$('rack_position').on('change', window.assign_position_changed);
		$('rack_submit').on('click', window.assign_object);
		$('object_he_units_submit').on('click', window.save_height_unit);

		// Prepare the assignment options.
		new Ajax.Request('?ajax=1&call=rack&func=get_rack_options',
			{
				parameters:{
					'obj_id':parseInt([{$object_id}])
				},
				method:"post",
				onSuccess:function (transport) {
					transport.responseJSON.each(function (option) {
						$('rack_option').insert(new Element('option', {value:option.id}).update(option.title));
					}.bind(this));
				}
			});

		/*
		 * We call this logic here, because the functions above are used for observer actions
		 * inside the class (only if "object_reassign : true").
		 */
		var rack_front = new Rack('rack_front', {
				edit_right: [{if $has_edit_right}]true[{else}]false[{/if}],
				view: 'front',
				slots: rack_slots,
				slot_sort: '[{$rack_slot_sorting}]',
	            object_link: true,
				'objects': objects,
				object_reassign: true,
				object_reassign_callback: window.select_object_for_assignment,
				object_remove: true,
				object_remove_callback: window.remove_object_assignment,
				vertical_slots: parseInt('[{$vertical_slots_front}]')}),
			rack_rear = new Rack('rack_rear', {
				edit_right: [{if $has_edit_right}]true[{else}]false[{/if}],
				view: 'rear',
				slots: rack_slots,
				slot_sort: '[{$rack_slot_sorting}]',
	            object_link: true,
				'objects': objects,
				object_reassign: true,
				object_reassign_callback: window.select_object_for_assignment,
				object_remove: true,
				object_remove_callback: window.remove_object_assignment,
				vertical_slots: parseInt('[{$vertical_slots_rear}]')});

		// Preparing the tooltips.
		window.create_tooltips = function() {
			[{$quickinfo}]
		};

		window.create_tooltips();
	</script>
</div>