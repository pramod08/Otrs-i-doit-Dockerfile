<style type="text/css">
	.rack-container {
		display: inline-block;
		text-align: center;
	}

	.rack-container select {
		margin: 5px auto;
		display: block;
		width: 150px;
	}

	.rack {
		width: 300px;
	}

	.rack .main-slots td {
		font-size: 8px;
	}

	.rack .main-slots td img {
		width: 11px;
		height: 11px;
	}

	.rack .main-slots td span {
		padding-top: 0;
	}

	.rack tr.row {
		height: 12px;
	}

	.rack td.slot div {
		padding: 0;
	}

	.rack .left-slots, .right-slots {
		width: 50px;
	}

	.rack .left-slots .slot, .right-slots .slot {
		height: 140px;
		padding: 5px 0;
		margin: 4px;
		width: 15px;
	}

	.rack .rotated {
		bottom: 68px;
		right: -67px;
		width: 150px;
        height: 15px;
	}

    .rotated span {
        font-size: 9px;
        width: 100px;
    }

    .rack .rotated img {
        height: 11px;
        width: 11px;
    }

    img.statistic-button {
        cursor: pointer;
    }

    #rack_stats em {
        color: #aaa;
    }

    /* IE8 hacks */
    @media \0screen {
        .rotated img.object-icon {
            left: 1px;
            top: -65px;
        }

        .rotated span {
            width: 15px;
            height: 130px;
            left: -15px;
            top: -50px;
        }

        .rotated {
            width: 25px;
            height: 150px;
        }

        .rack span.rotated {
            top: 0;
            height: 150px;
            width: 15px;
        }
    }

    *+html .rack .rotated {
        top: 0;
        right: 0;
        bottom: 0;
        left: 0;
        width: auto;
        height: auto;
        position: relative;
    }

    *+html .rack .rotated span {
        top: 0;
        right: 0;
        bottom: 0;
        left: 0;
        width: auto;
        height: auto;
        position: relative;
    }

    #clicker {
        cursor: pointer;
    }

	#position_racks.navbar_item,
	#recurse_racks.navbar_item,
	#clicker.navbar_item {
		display: block;
		float: none;
	}

	ul#rack_positions_list {
		margin: 5px;
    }

    ul#rack_positions_list li {
		list-style: none;
		margin: 0 0 5px;
		padding: 3px;
		background: #eee;
		border: 1px solid #B7B7B7;
    }

    ul#rack_positions_list li span.handle {
		background: transparent url('[{$dir_images}]icons/hatch.gif');
		display: block;
		float: left;
		margin-right: 5px;
		width: 10px;
		cursor: ns-resize;
    }
</style>

<div class="p10">
	[{if $has_edit_right}]
	<button type="button" id="position_racks" class="btn noprint">
		<img src="[{$dir_images}]icons/silk/arrow_switch.png" class="mr5" /><span>[{isys type="lang" ident="LC__CMDB__CATG__RACK_VIEW__SORT"}]</span>
	</button>
	[{/if}]
	<button type="button" id="recurse_racks" class="btn noprint">
		<img src="[{$dir_images}]icons/silk/information.png" class="mr5" /><span>[{isys type="lang" ident="LC__CMDB__CATG__RACK_VIEW__LOAD_ALL_RACKS"}]</span>
	</button>
	<button type="button" id="clicker" class="btn noprint">
		<img src="[{$dir_images}]icons/silk/bullet_arrow_down.png" class="mr5" /><span>[{isys type="lang" ident="LC__CMDB__CATS__RACK__STATS_FOR_ALL"}]</span>
	</button>

	<div id="rack_positions" class="box mt5" style="border-top: none; display: none;">
	    <h3 class="p5 gradient text-shadow" style="border-top:1px solid #B7B7B7; border-bottom:1px solid #B7B7B7;">[{isys type="lang" ident="LC__RACK__CHANGE_POSITIONING"}]</h3>
		<div class="error m5 p5" id="positioning_error" style="display: none;">[{isys type="lang" ident="LC__REPORT__EXCEPTION_TRIGGERED"}]</div>

		<ul id="rack_positions_list"></ul>

		<button type="button" class="btn m5" id="position_racks_save">
			<img src="[{$dir_images}]icons/silk/disk.png" class="mr5" /><span>[{isys type="lang" ident="LC__NAVIGATION__NAVBAR__SAVE"}]</span>
		</button>
		<button type="button" class="btn" onclick="$('rack_positions').hide();">
			<img src="[{$dir_images}]icons/silk/cross.png" class="mr5" /><span>[{isys type="lang" ident="LC__NAVIGATION__NAVBAR__CANCEL"}]</span>
		</button>
	</div>

	<div id="rack_stats" class="box mt5" style="border-top: none; display: none;">
	    <h3 class="p5 gradient text-shadow" style="border-top:1px solid #B7B7B7; border-bottom:1px solid #B7B7B7;">[{isys type="lang" ident="LC__UNIVERSAL__LOADING"}]</h3>
	    <div>...</div>
	</div>

	<div id="rackview" class="mt5" style="overflow-x: scroll; position:relative;">

		<div id="rackview_scroller" style="width:[{$object_cnt*320}]px;">
			[{foreach $racks as $rack}]
			<div class="rack-container mr10">
	            <div class="p5 gradient text-shadow" style="border:1px solid #B7B7B7;">
	                <strong id="obj-[{$rack.id}]-title">[{$rack.title}]</strong>&nbsp;
	                <img class="statistic-button vam noprint" data-object-id="[{$rack.id}]" src="[{$dir_images}]icons/silk/information.png" alt="Statistic" />
	                <a href="?[{$smarty.const.C__CMDB__GET__OBJECT}]=[{$rack.id}]&[{$smarty.const.C__CMDB__GET__CATS}]=[{$smarty.const.C__CATS__ENCLOSURE}]" title="[{isys type="lang" ident="LC__UNIVERSAL__TITLE_LINK"}]"><img class="vam noprint" src="[{$dir_images}]icons/silk/link.png" alt="[{isys type="lang" ident="LC__UNIVERSAL__TITLE_LINK"}]" /></a>
	                <select id="rack_switcher_[{$rack.id}]" class="inputDialog rack-switcher noprint">
	                    <option value="front">[{isys type="lang" ident="LC__CMDB__CATG__LOCATION_FRONT"}]</option>
	                    <option value="rear">[{isys type="lang" ident="LC__CMDB__CATG__LOCATION_BACK"}]</option>
	                </select>
	            </div>
				<div id="rack_front_[{$rack.id}]" class="rack front"></div>
				<div id="rack_rear_[{$rack.id}]" class="rack rear" style="display: none;"></div>
			</div>
			[{/foreach}]
		</div>


		<div id="child_racks" class="mt15" style="display: none;">
			<h3 class="p5">[{isys type="lang" ident="LC__CMDB__CATG__RACK_VIEW__ALL_RACKS"}]</h3>
		</div>
	</div>

	<script type="text/javascript">
		window.images_dir = '[{$dir_images}]';

		var objects,
			rack_positions = [];

		[{foreach $racks as $rack}]
		objects = '[{$rack.objects|escape:"quotes"}]'.evalJSON();

		new Rack('rack_front_[{$rack.id}]', {
			view: 'front',
			room_view: true,
			slots: parseInt('[{$rack.slots}]'),
			slot_sort: '[{$rack.sorting}]',
			object_reassign: false,
			object_remove: false,
			vertical_slots: parseInt('[{$rack.vslots_front}]'),
			'objects': objects});
		new Rack('rack_rear_[{$rack.id}]', {
			view: 'rear',
	        room_view: true,
			slots: parseInt('[{$rack.slots}]'),
			slot_sort: '[{$rack.sorting}]',
			object_reassign: false,
			object_remove: false,
			vertical_slots: parseInt('[{$rack.vslots_rear}]'),
			'objects': objects});

	    rack_positions.push({obj_id:'[{$rack.id}]', name:'[{$rack.title}]'});
		[{/foreach}]

		window.refresh_observer = function () {
			// First we stop all observation, so that click's wont be observed twice (and more).
			$$('.rack-switcher').invoke('stopObserving');
			$$('img.statistic-button').invoke('stopObserving');

			$$('.rack-switcher').invoke('on', 'change', function() {
				if (this.value == 'front') {
					this.up(1).down('.front').show();
					this.up(1).down('.rear').hide();
				} else {
					this.up(1).down('.front').hide();
					this.up(1).down('.rear').show();
				}
			});

			$$('img.statistic-button').invoke('on', 'click', function () {
				$('rack_stats').update();
				window.call_statistics(this.readAttribute('data-object-id'));
			});
		};


	    // Function for calling statistics of all racks.
	    $('clicker').on('click', function () {
		    var $rack_status = $('rack_stats');

		    if ($rack_status.visible()) {
			    $rack_status.hide();
			    $('clicker')
				    .down('img').writeAttribute('src', '[{$dir_images}]icons/silk/bullet_arrow_down.png')
				    .next('span').update('[{isys type="lang" ident="LC__CMDB__CATS__RACK__STATS_FOR_ALL"}]');

		    } else {
			    $rack_status.update();
			    $('clicker')
				    .down('img').writeAttribute('src', '[{$dir_images}]icons/silk/bullet_arrow_up.png')
				    .next('span').update('[{isys type="lang" ident="LC__CMDB__CATS__RACK__STATS_CLOSE"}]');

		        $$('.statistic-button').each(function (el) {
		            window.call_statistics(el.readAttribute('data-object-id'));
		        });
		    }
	    });

		// Function for calling the racks of all child locations
		$('recurse_racks').on('click', function () {
			var obj_id = [{$obj_id}],
				img = this.down('img'),
				src_information = img.readAttribute('src');

			// Change the "information" icon with the ajax-loading icon.
			img.writeAttribute('src', '[{$dir_images}]ajax-loading.gif');

			new Ajax.Request('?ajax=1&call=rack&func=get_racks_recursive',
				{
					parameters:{
						'obj_id': obj_id
					},
					method:"post",
					onSuccess:function (transport) {
						var racks = transport.responseJSON,
							length = racks.length,
							rack,
							element;

						for (i=0; i<length; i++) {

							rack = racks[i];

							element = new Element('div', {className: 'rack-container mr10'})
								.update(new Element('div', {className: 'p5 gradient text-shadow', style: 'border:1px solid #B7B7B7;'})
									.update(new Element('strong', {id: 'obj-' + rack.id + '-title'}).update(rack.title))
									.insert(new Element('img', {className: 'statistic-button vam', 'data-object-id': rack.id, 'src': '[{$dir_images}]icons/silk/information.png', alt: 'Statistic'}))
									.insert(new Element('a', {href: '?[{$smarty.const.C__CMDB__GET__OBJECT}]=' + rack.id + '&[{$smarty.const.C__CMDB__GET__CATS}]=[{$smarty.const.C__CATS__ENCLOSURE}]', title: '[{isys type="lang" ident="LC__UNIVERSAL__TITLE_LINK"}]'})
										.insert(new Element('img', {className: 'vam', src: '[{$dir_images}]icons/silk/link.png', alt: '[{isys type="lang" ident="LC__UNIVERSAL__TITLE_LINK"}]'})))
									.insert(new Element('select', {id: 'rack_switcher_' + rack.id, className: 'inputDialog rack-switcher'})
										.insert(new Element('option', {value: 'front'}).update('[{isys type="lang" ident="LC__CMDB__CATG__LOCATION_FRONT"}]'))
										.insert(new Element('option', {value: 'rear'}).update('[{isys type="lang" ident="LC__CMDB__CATG__LOCATION_BACK"}]'))))
								.insert(new Element('div', {id: 'rack_front_' + rack.id, className: 'rack front'}))
								.insert(new Element('div', {id: 'rack_rear_' + rack.id, className: 'rack rear', style: 'display: none;'}));

							$('child_racks').setStyle({width: (length * 315) + 'px'}).insert(element);

							// Actually create the racks.
							new Rack('rack_front_' + rack.id, {
								view: 'front',
								room_view: true,
								slots: rack.slots,
								slot_sort: rack.sorting,
								object_reassign: false,
								object_remove: false,
								vertical_slots: rack.vslots_front,
								objects: rack.objects.evalJSON()});
							new Rack('rack_rear_' + rack.id, {
								view: 'rear',
								room_view: true,
								slots: rack.slots,
								slot_sort: rack.sorting,
								object_reassign: false,
								object_remove: false,
								vertical_slots: rack.vslots_rear,
								objects: rack.objects.evalJSON()});
						}

						$('child_racks').appear();
						window.refresh_observer();

						// Restore the "information" icon.
						Effect.BlindUp(this, {duration: 0.5});
					}.bind(this)
				});
		});

	    window.call_statistics = function(obj_id) {
	        var obj_title = $('obj-' + obj_id + '-title').innerHTML,
	            stat_el = $('rack_stats').show(),
	            stat_container = new Element('div'),
	            stat_el_head = new Element('h3', {className: 'p5 gradient text-shadow', style: 'border-top:1px solid #B7B7B7; border-bottom:1px solid #B7B7B7;'}).update('[{isys type="lang" ident="LC__UNIVERSAL__LOADING"}]'),
	            stat_el_body = new Element('div').update('...')
	                .update(new Element('img', {className: 'vam p5', src: window.images_dir + 'ajax-loading.gif', alt: '[{isys type="lang" ident="LC__UNIVERSAL__LOADING"}]'}))
	                .insert('[{isys type="lang" ident="LC__UNIVERSAL__LOADING"}]');

	        stat_el.insert(stat_container.insert(stat_el_head).insert(stat_el_body));

	        new Ajax.Request('?ajax=1&call=statistic&func=get_rack_statistics',
	                {
	                    parameters:{
	                        'obj_id':obj_id,
	                        'as_json':false
	                    },
	                    method:"post",
	                    onSuccess:function (transport) {
	                        var obj_stat_title = ('[{isys type="lang" ident="LC__CMDB__CATS__RACK__STATS_FOR"}]').replace('%s', obj_title);

	                        stat_el_head.update(obj_stat_title);
	                        stat_el_body.update(transport.responseText);
	                    }.bind(this)
	                });
	    };

		/*[{if $has_edit_right}]*/
		$('position_racks').on('click', function () {
			var positioner = $('rack_positions'),
				list = $('rack_positions_list').update(),
				i;


			for (i in rack_positions) {
				if (rack_positions.hasOwnProperty(i)) {
	                list.insert(new Element('li', {'data-obj-id':rack_positions[i].obj_id}).update(new Element('span', {className: 'handle'}).update('&nbsp;')).insert(new Element('span').update(rack_positions[i].name)));
	            }
	        }

	        Sortable.create('rack_positions_list', {
				handle: 'handle'
			});

	        positioner.show();
	    });


		var $save_position_button = $('position_racks_save');

		$save_position_button.on('click', function () {
			var positions = [];

			$save_position_button
				.disable()
				.down('img').writeAttribute('src', '[{$dir_images}]ajax-loading.gif')
				.next('span').update('[{isys type="lang" ident="LC__UNIVERSAL__LOADING"}]');

	        $$('#rack_positions_list li').each(function (el, i) {
	            positions.push(parseInt(el.readAttribute('data-obj-id')));
	        });

	        new Ajax.Request('?ajax=1&call=rack&func=save_position_in_location', {
	                    parameters:{
	                        'positions':Object.toJSON(positions)
	                    },
	                    method:"post",
	                    onSuccess:function (transport) {
		                    $save_position_button
			                    .enable()
			                    .down('img').writeAttribute('src', '[{$dir_images}]icons/silk/disk.png')
			                    .next('span').update('[{isys type="lang" ident="LC__NAVIGATION__NAVBAR__SAVE"}]');

	                        if (transport.responseJSON.success) {
	                            // Redirect to this same site (for loading the newly positioned racks).
	                            document.location.href = '[{$this_page}]';
	                        } else {
		                        $('positioning_error').show()
	                        }
	                    }
	                });
		});
		/*[{/if}]*/

		window.refresh_observer();
	</script>
</div>