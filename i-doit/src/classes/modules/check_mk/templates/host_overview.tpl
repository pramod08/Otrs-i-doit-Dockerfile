<h3 class="p5 mb10 gradient border text-shadow">Host overview</h3>

<ul id="check_mk_host_list">
	[{while $row = $hosts->get_row()}]
	<li data-obj-id="[{$row.isys_obj__id}]" class="border mr5">
		<h4 class="gradient p5 border-bottom">
			<div class="cmdb-marker mouse-help" style="background-color: #[{$cmdb_status[$row.isys_obj__isys_cmdb_status__id].isys_cmdb_status__color}];" title="[{isys type="lang" ident=$cmdb_status[$row.isys_obj__isys_cmdb_status__id].isys_cmdb_status__title}]"></div>
			[{$row.isys_obj__title}]
			<a href="?[{$smarty.const.C__CMDB__GET__OBJECT}]=[{$row.isys_obj__id}]" class="fr"><img src="[{$dir_images}]icons/silk/link.png" /></a>
		</h4>

		<div class="m5">
			<div class="border-transparent p5" style="background-color: #[{$row.isys_obj_type__color}];">Objekttyp [{isys type="lang" ident=$row.isys_obj_type__title}]</div>

			<div class="mt5 mb10">
				<span class="vam mr5">Status</span><span class="state-container"><img src="[{$dir_images}]ajax-loading.gif" class="vam" /></span>
			</div>

			<div class="fr toggle-services mouse-pointer"><img src="[{$dir_images}]icons/silk/bullet_toggle_plus.png" /><span class="vat">Show all</span></div>
			<strong>Services</strong>
			<ul class="services"></ul>
		</div>
	</li>
	[{/while}]
</ul>

<br class="cb" />

<style type="text/css">
	#check_mk_host_list {
		list-style: none;
		margin: 10px 0 0;
		padding: 0;
	}

	#check_mk_host_list li {
		float: left;
		width: 300px;
	}

	#check_mk_host_list ul {
		margin: 0;
		padding: 0;
		list-style: none;
	}

	#check_mk_host_list ul li {
		margin-bottom: 5px;
	}
</style>

<script type="text/javascript">
	(function () {
		"use strict";

		var host_list = $('check_mk_host_list'),
			obj_ids = host_list.select('li').invoke('readAttribute', 'data-obj-id').compact(),
			states = '[{$states}]'.evalJSON();

		new Ajax.Request('[{$ajax_url}]', {
			method: "post",
			parameters: {
				obj_ids:Object.toJSON(obj_ids)
			},
			onSuccess: function(transport) {
				var json = transport.responseJSON,
					item, index, index2, el, state, services;

				if (json.success) {
					for (index in json.data) {
						if (json.data.hasOwnProperty(index)) {
							item = json.data[index];

							el = host_list.down('li[data-obj-id="' + item.obj_id + '"]');


							if (item.success) {
								state = item.data[1][item.data[0].indexOf('state')];
								services = item.data[1][item.data[0].indexOf('services_with_info')];

								el.down('.state-container')
									.addClassName(states[state].color)
									.update(new Element('img', {className:'vam mr5', src:'[{$dir_images}]' + states[state].icon}))
									.insert(new Element('span', {className:'vam'}).update(states[state].state));

								el = el.down('ul.services');

								for (index2 in services) {
									if (services.hasOwnProperty(index2)) {
										el.insert(new Element('li', {className:states[services[index2][1]].color}).update(
												new Element('img', {className:'vam mr5 mouse-help', src:'[{$dir_images}]icons/silk/information.png', title:services[index2][3]})
											).insert(
												new Element('span', {className:'vam'}).update(services[index2][0])
											));
									}
								}
							} else {
								el.down('.state-container')
									.addClassName('red')
									.update(new Element('img', {className:'vam mr5', src:'[{$dir_images}]icons/silk/delete.png'}))
									.insert(new Element('span', {className:'vam'}).update(item.message));

								el.down('ul.services').update(new Element('li').update('[{isys type="lang" ident="LC__CATG__CMK_SERVICE__NO_SERVICES"}]'));
							}
						}
					}

					$$('.services li.green').invoke('hide');
				} else {
					host_list.insert({before:new Element('p', {className:'mt5 p5 error'}).update(json.message)});
				}
			}.bind(this)
		});

		$$('.toggle-services').invoke('on', 'click', function () {
			var list = this.next('.services').toggleClassName('show-all');

			list.select('.green').invoke('toggle');

			this.down('img').writeAttribute('src', (list.hasClassName('show-all') ? '[{$dir_images}]icons/silk/bullet_toggle_minus.png' : '[{$dir_images}]icons/silk/bullet_toggle_plus.png'))
		});
	}());
</script>