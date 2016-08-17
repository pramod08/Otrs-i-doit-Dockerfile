<table class="contentTable">
	<tr>
		<td class="key">[{isys type='f_label' name='C__CATG__LOCATION_PARENT' ident="LC__CMDB__CATG__LOCATION_PARENT"}]</td>
		<td class="value">
			[{isys
				name="C__CATG__LOCATION_PARENT"
				id="C__CATG__LOCATION_PARENT"
				type="f_popup"
				p_strPopupType="browser_location"
				callback_accept="idoit.callbackManager.triggerCallback('location__parent_location_change');"
				p_onChange="idoit.callbackManager.triggerCallback('location__parent_location_change');"
				containers_only=true}]
		</td>
	</tr>
	<tr>
		<td class="key">[{isys type='f_label' name='LC__CMDB__CATG__LOCATION_LATITUDE' ident="LC__CMDB__CATG__LOCATION_LATITUDE"}]</td>
		<td class="value">
			[{isys type="f_text" name="C__CATG__LOCATION_LATITUDE"}]
		</td>
	</tr>
	<tr>
		<td class="key">[{isys type='f_label' name='LC__CMDB__CATG__LOCATION_LONGITUDE' ident="LC__CMDB__CATG__LOCATION_LONGITUDE"}]</td>
		<td class="value">
			[{isys type="f_text" name="C__CATG__LOCATION_LONGITUDE"}]
		</td>
	</tr>
	[{if $lat && $lng}]
	<tr>
		<td class="key">[{isys type="lang" ident="LC__CMDB__CATG__LOCATION_OPEN_MAP"}]</td>
		<td>
			<!-- Google Maps -->
			<a class="btn ml20" target="_new" href="https://www.google.de/maps/@[{$lat}],[{$lng}],17z">
				<img src="[{$dir_images}]/icons/googlemaps.png" class="mr5" /><span>Google Maps</span>
			</a>

			[{* @todo  Check if we can simply use the bing icon and name!
			<!-- Bing Maps -->
			<a class="btn ml5" target="_new" href="https://www.bing.com/maps?cp=[{$lat}]~[{$lng}]&lvl=17">
				<img src="[{$dir_images}]/icons/bing.png" class="mr5" /><span>Bing Karten</span>
			</a>
			*}]

			<!-- OpenStreetMap -->
			<a class="btn ml5" target="_new" href="https://www.openstreetmap.org/#map=17/[{$lat}]/[{$lng}]">
				<img src="[{$dir_images}]/icons/openstreetmap.png" class="mr5" /><span>OpenStreetMap</span>
			</a>
		</td>
	</tr>
	[{/if}]
	<tr class="rack_dummy_class [{if !$show_in_rack}]hide[{/if}]" style="display:none;">
		<td class="key">[{isys type='f_label' name='C__CATG__LOCATION_OPTION' ident="LC__CMDB__CATG__LOCATION_OPTION"}]</td>
		<td class="value">[{isys name="C__CATG__LOCATION_OPTION" type="f_dialog"}]</td>
	</tr>
	<tr class="rack_dummy_class [{if !$show_in_rack}]hide[{/if}]" style="display:none;">
		<td class="key">[{isys type='f_label' name='C__CATG__LOCATION_INSERTION' ident="LC__CMDB__CATG__LOCATION_FRONTSIDE"}]</td>
		<td class="value">[{isys name="C__CATG__LOCATION_INSERTION" type="f_dialog"}]</td>
	</tr>
	<tr class="rack_dummy_class [{if !$show_in_rack}]hide[{/if}]" style="display:none;">
		<td class="key">[{isys type='f_label' name='C__CATG__LOCATION_POS' ident="LC__CMDB__CATG__LOCATION_POS"}]</td>
		<td class="value">[{isys name="C__CATG__LOCATION_POS" id="C__CATG__LOCATION_POS" type="f_dialog" p_bSort=false}]</td>
	</tr>
</table>

<script type="text/javascript">
	(function () {
		"use strict";

		var $location_pos = $('C__CATG__LOCATION_POS'),
			$location_parent_hidden = $('C__CATG__LOCATION_PARENT__HIDDEN'),
			$location_option = $('C__CATG__LOCATION_OPTION'),
			$location_insertion = $('C__CATG__LOCATION_INSERTION'),

			option = '[{$option}]',
			obj_id = '[{$smarty.get.objID}]',
			location_obj_id = '[{$rack_object}]',

			parent_location_change = function () {
				location_obj_id = $location_parent_hidden.getValue();

				$location_option.update().disable();
				$location_insertion.update().disable();
				$location_pos.update().disable();

				new Ajax.Request('?ajax=1&call=rack&func=get_rack_options',
					{
						parameters:{
							'obj_id': location_obj_id
						},
						method:"post",
						onSuccess:function (transport) {
							var	json = transport.responseJSON;

							if(json == false){
								$$('.rack_dummy_class').invoke('hide');
							} else{
								$$('.rack_dummy_class').invoke('show');

								for (var i in json) {
									if (json[i].id > 0) $location_option.insert(new Element('option', {value: json[i].id}).update(json[i].title));
								}

								$location_option.enable();

								// The ".simulate('change')" method does not seem to work here.
								window.option_change();
							}
						}
					});
			};

		window.option_change = function () {
			$location_insertion.update().disable();
			$location_pos.update().disable();

			if ($location_option.getValue() < 0) {
				return;
			}

			new Ajax.Request('?ajax=1&call=rack&func=get_rack_insertions',
				{
					parameters:{
						'obj_id': obj_id,
						'option': $location_option.getValue()
					},
					method:"post",
					onSuccess:function (transport) {
						transport.responseJSON.each(function (option) {
							$location_insertion.insert(new Element('option', {value:option.id}).update(option.title))
						});

						$location_insertion.enable();

						// The ".simulate('change')" method does not seem to work here.
						window.insertion_change();
					}
				});
		};

		window.insertion_change = function () {
			$location_pos.update().disable();

			new Ajax.Request('?ajax=1&call=rack&func=get_free_slots_for_location',
				{
					parameters:{
						'rack_obj_id': location_obj_id,
						'assign_obj_id': obj_id,
						'option': $location_option.getValue(),
						'insertion': $location_insertion.getValue()
					},
					method:"post",
					onSuccess:function (transport) {
						var slots = transport.responseJSON,
							slot_hash;

						if (transport.responseText != '[]') {
							slot_hash = $H(slots);

							slot_hash.each(function(pair) {
								var fromto = pair.key.split(';');

								$location_pos.insert(new Element('option', {value: fromto[0], 'data-from': fromto[0], 'data-to': fromto[1]}).update(pair.value))
							}.bind(this));

							$location_pos.enable();
						}
					}
				});
		};

		if ($location_parent_hidden) {
			$location_parent_hidden.on('change', parent_location_change);
		}

		if ($location_option) {
			$location_option.on('change', window.option_change);
		}

		if ($location_insertion) {
			$location_insertion.on('change', window.insertion_change);
		}

		if ($location_pos) {
			$location_pos.select('option').each(function(el, i) {
				if (i > 0) el.update(el.innerHTML.replace('-', ' &rarr; '));
			});
		}

		[{if $rack_object}]$$('.rack_dummy_class').invoke('show');[{/if}]

		idoit.callbackManager.registerCallback('location__parent_location_change', parent_location_change);
	}());
</script>