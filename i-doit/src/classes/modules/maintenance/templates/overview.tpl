<table cellspacing="0" cellpadding="0" id="maintenance-overview-filter">
	<tr>
		<td>
			[{isys type="f_label" name="C__MAINTENANCE__OVERVIEW__FILTER__DATE_FROM" ident="LC__MAINTENANCE__OVERVIEW__FILTER__DATE_FROM"}]<br />
			[{isys type="f_popup" name="C__MAINTENANCE__OVERVIEW__FILTER__DATE_FROM"}]
		</td>
		<td>
			[{isys type="f_label" name="C__MAINTENANCE__OVERVIEW__FILTER__DATE_TO" ident="LC__MAINTENANCE__OVERVIEW__FILTER__DATE_TO"}]<br />
			[{isys type="f_popup" name="C__MAINTENANCE__OVERVIEW__FILTER__DATE_TO"}]
		</td>
		<td>
			<button id="C__MAINTENANCE__OVERVIEW__RESET_BUTTON" type="button" class="btn">
				<img src="[{$dir_images}]icons/silk/cross.png" class="mr5" />
				<span>[{isys type="lang" ident="LC__MAINTENANCE__OVERVIEW__FILTER_RESET_BUTTON"}]</span>
			</button>

			<button id="C__MAINTENANCE__OVERVIEW__FILTER_BUTTON" type="button" class="btn ml5">
				<img src="[{$dir_images}]icons/silk/tick.png" class="mr5" />
				<span>[{isys type="lang" ident="LC__MAINTENANCE__OVERVIEW__FILTER_BUTTON"}]</span>
			</button>
		</td>
	</tr>
</table>

<div id="maintenance-overview-list" class="mt5">
	<div id="maintenance-overview-list-loader"></div>

	<h3 class="p5 gradient border-top border-bottom cb">[{isys type="lang" ident="LC__MAINTENANCE__OVERVIEW__LIST_PAST"}]</h3>
	<div id="maintenance-overview-list-past" class="p5 timeperiod"></div>

	<h3 class="p5 gradient border-top border-bottom cb">[{isys type="lang" ident="LC__MAINTENANCE__OVERVIEW__LIST_THIS_WEEK"}]</h3>
	<div id="maintenance-overview-list-this-week" class="p5 timeperiod"></div>

	<h3 class="p5 gradient border-top border-bottom cb">[{isys type="lang" ident="LC__MAINTENANCE__OVERVIEW__LIST_THIS_MONTH"}]</h3>
	<div id="maintenance-overview-list-this-month" class="p5 timeperiod"></div>

	<h3 class="p5 gradient border-top border-bottom cb">[{isys type="lang" ident="LC__MAINTENANCE__OVERVIEW__LIST_NEXT_WEEK"}]</h3>
	<div id="maintenance-overview-list-next-week" class="p5 timeperiod"></div>

	<h3 class="p5 gradient border-top border-bottom cb">[{isys type="lang" ident="LC__MAINTENANCE__OVERVIEW__LIST_NEXT_MONTH"}]</h3>
	<div id="maintenance-overview-list-next-month" class="p5 timeperiod"></div>

	<h3 class="p5 gradient border-top border-bottom cb">[{isys type="lang" ident="LC__MAINTENANCE__OVERVIEW__LIST_FUTURE"}]</h3>
	<div id="maintenance-overview-list-future" class="p5 timeperiod"></div>

	<br class="cb" />
</div>

<style type="text/css">
	#maintenance-overview-filter {
		padding: 10px 0;
	}

	#maintenance-overview-filter td {
		padding: 0 10px;
		vertical-align: top;
	}

	#maintenance-overview-filter td:last-child {
		vertical-align: bottom;
	}

	#maintenance-overview-list .maintenance-object {
		width: 250px;
		min-height: 100px;
		margin: 0 5px 5px 0;
		background: #fff;
		position: relative;
		box-sizing: border-box;
	}

	#maintenance-overview-list ul {
		margin: 0;
		list-style: outside none none;
	}

	#maintenance-overview-list li {
		margin: 2px 5px;
	}

	#maintenance-overview-list .maintenance-object img.obj-image {
		width: 150px;
		margin-left: 50px;
	}
</style>
<script type="text/javascript">
	(function () {
		'use strict';

		var objects,
			maintenances,
			last_error = '',
			$from = $('C__MAINTENANCE__OVERVIEW__FILTER__DATE_FROM__HIDDEN'),
			$to = $('C__MAINTENANCE__OVERVIEW__FILTER__DATE_TO__HIDDEN'),
			$filter_button = $('C__MAINTENANCE__OVERVIEW__FILTER_BUTTON'),
			$reset_button = $('C__MAINTENANCE__OVERVIEW__RESET_BUTTON'),
			$list = $('maintenance-overview-list'),
			$list_loader = $('maintenance-overview-list-loader').hide(),
			$list_past = $('maintenance-overview-list-past'),
			$list_this_week = $('maintenance-overview-list-this-week'),
			$list_this_month = $('maintenance-overview-list-this-month'),
			$list_next_week = $('maintenance-overview-list-next-week'),
			$list_next_month = $('maintenance-overview-list-next-month'),
			$list_future = $('maintenance-overview-list-future'),
			object_elements = {},
			this_week = parseInt('[{$this_week}]'),
			this_month = parseInt('[{$this_month}]'),
			next_week = parseInt('[{$next_week}]'),
			next_month = parseInt('[{$next_month}]'),
			next_next_month = parseInt('[{$next_next_month}]');

		$reset_button.on('click', function () {
			$('C__MAINTENANCE__OVERVIEW__FILTER__DATE_FROM__VIEW', 'C__MAINTENANCE__OVERVIEW__FILTER__DATE_FROM__HIDDEN', 'C__MAINTENANCE__OVERVIEW__FILTER__DATE_TO__VIEW', 'C__MAINTENANCE__OVERVIEW__FILTER__DATE_TO__HIDDEN').invoke('setValue', '');
		});

		$filter_button.on('click', function () {
			$list_loader.show();

			$filter_button
				.down('img').writeAttribute('src', '[{$dir_images}]ajax-loading.gif')
				.next('span').update('[{isys type="lang" ident="LC__UNIVERSAL__LOADING"}]');

			new Ajax.Request('[{$ajax_url}]&func=get-filtered-planning-list', {
					parameters: {
						from: $from.getValue(),
						to: $to.getValue()
					},
					onSuccess: function (response) {
						var json = response.responseJSON;

						if (json.success) {
							objects = json.data.objects;
							maintenances = json.data.maintenances;
						} else {
							objects = false;
							maintenances = false;
							last_error = json.message || response.responseText;
						}
					},
					onFailure: function (response) {
						objects = false;
						last_error = response.responseText;
					},
					onComplete: function () {
						$list_loader.hide();
						$filter_button
							.down('img').writeAttribute('src', '[{$dir_images}]icons/silk/tick.png')
							.next('span').update('[{isys type="lang" ident="LC__MAINTENANCE__OVERVIEW__FILTER_BUTTON"}]');

						render_list();
					}
				});
		});

		function render_list () {
			var date, obj, item, role_people, $usage, $object, $object_ul, i, list_width = ($list.getWidth() - 15);

			// First we flush the contents.
			$list_past.update().store('count', 0);
			$list_this_month.update().store('count', 0);
			$list_this_week.update().store('count', 0);
			$list_next_week.update().store('count', 0);
			$list_next_month.update().store('count', 0);
			$list_future.update().store('count', 0);

			// And then we hide all containers.
			$list.select('.timeperiod').invoke('hide')
				.invoke('previous').invoke('hide');

			if (!objects || !maintenances) {
				$list.update(new Element('p', {className:'error m5 p5'})
					.update(new Element('img', {src:'[{$dir_images}]icons/silk/error.png', className:'mr5'}))
					.insert(new Element('span').update(last_error)));

				return;
			}

			for (obj in objects) {
				if (objects.hasOwnProperty(obj)) {
					item = objects[obj];

					object_elements[obj] = new Element('div', {className:'fl border maintenance-object', 'data-object-id': item.isys_obj__id})
						.update(new Element('strong', {className:'p5 display-block'})
							.update(new Element('img', {className:'mr5 vam', src:item.isys_obj_type__icon, width:'16px', height:'16px'}))
							.insert(new Element('span').update(item.isys_obj_type__title + ' &raquo; ' + item.isys_obj__title)))
						.insert(new Element('img', {className:'obj-image', src:item.image}))
						.insert(new Element('ul'));
				}
			}

			for (date in maintenances) {
				if (maintenances.hasOwnProperty(date)) {
					item = maintenances[date];

					if (item.from < this_month) {
						$usage = $list_past;
					} else if (item.from >= this_month && item.from < this_week) {
						$usage = $list_this_month;
					} else if (item.from >= this_week && item.from < next_week) {
						$usage = $list_this_week;
					} else if (item.from >= next_week && item.from < next_month) {
						$usage = $list_next_week;
					} else if (item.from >= next_month && item.from < next_next_month) {
						$usage = $list_next_month;
					} else {
						$usage = $list_future;
					}

					for (obj in item.objects) {
						if (item.objects.hasOwnProperty(obj)) {
							$object = $usage.down('[data-object-id="' + item.objects[obj] + '"]');

							if (! $object) {
								$usage.store('count', $usage.retrieve('count') + 1);

								if (($usage.retrieve('count') * 255) > list_width) {
									$usage.store('count', 1).insert(new Element('div', {className:'cb'}));
								}

								$object = object_elements[item.objects[obj]].clone(true);
								$usage.insert($object);
							}

							$object_ul = $object.down('ul');

							if (item.isys_maintenance__finished != null) {
								$object_ul.insert(
									new Element('li', {className:'mt10', title:'[{isys type="lang" ident="LC__MAINTENANCE__OVERVIEW__FINISHED"}]'})
										.update(new Element('img', {className:'vam mr5', src:'[{$dir_images}]icons/silk/tick.png'}))
										.insert(new Element('a', {href:'[{$planning_url}]&[{$smarty.const.C__GET__ID}]=' + item.isys_maintenance__id, className:'green'}).update(item.from_formatted + ' - ' + item.to_formatted)));
							} else {
								$object_ul.insert(
									new Element('li', {className:'mt10'})
										.update(new Element('img', {className:'vam mr5', src:'[{$dir_images}]icons/silk/wrench.png'}))
										.insert(new Element('a', {href:'[{$planning_url}]&[{$smarty.const.C__GET__ID}]=' + item.isys_maintenance__id}).update(item.from_formatted + ' - ' + item.to_formatted)));
							}

							// Now add each person, persongroup, organisation and role.
							for (i in item.contacts) {
								if (item.contacts.hasOwnProperty(i)) {
									$object_ul.insert(
										new Element('li')
											.update(new Element('img', {className:'vam mr5', src:item.contacts[i].isys_obj_type__icon}))
											.insert(new Element('a', {href:'?[{$smarty.const.C__CMDB__GET__OBJECT}]=' + item.contacts[i].isys_obj__id}).update(item.contacts[i].isys_obj_type__title + ' &raquo; ' + item.contacts[i].isys_obj__title)));
								}
							}

							role_people = '';

							if (objects[item.objects[obj]]['roles'].hasOwnProperty(item.isys_maintenance__isys_contact_tag__id)) {
								role_people = objects[item.objects[obj]]['roles'][item.isys_maintenance__isys_contact_tag__id];
							}

							$object_ul.insert(new Element('li')
								.update(new Element('strong', {className:'mr10'}).update('[{isys type="lang" ident="LC__CMDB__CONTACT_ROLE"}]'))
								.insert(new Element('span', {className:(! role_people.empty() ? 'mouse-help underline' : ''), title: role_people}).update(item.isys_contact_tag__title || '[{isys_tenantsettings::get('gui.empty_value', '-')}]')));
						}
					}
				}
			}

			// Hide the empty timeperiods (+ headlines).
			$list.select('.timeperiod:not(:empty)').invoke('show')
				.invoke('previous').invoke('show');
		}

		$list.select('.timeperiod:empty').invoke('hide')
			.invoke('previous').invoke('hide');
	})();
</script>