<div>
	[{if $has_execute_right}]
	<div class="p10">
		<button id="ping-all" type="button" class="btn mr5" title="[{isys type="lang" ident="LC__CMDB__CATS__NET_IP_ADDRESSES__PING_USES_`$ping_method`"}]">
			<img src="[{$dir_images}]icons/silk/drive_network.png" class="mr5">
			<span>[{isys type="lang" ident="LC__CMDB__CATS__NET_IP_ADDRESSES__PING"}]</span>
		</button>
		<button id="nslookup-all" type="button" class="btn mr5">
			<img src="[{$dir_images}]icons/silk/zoom.png" class="mr5">
			<span>[{isys type="lang" ident="LC__CMDB__CATS__NET_IP_ADDRESSES__NSLOOKUP"}]</span>
		</button>
		<button id="r-nslookup-all" type="button" class="btn">
			<img src="[{$dir_images}]icons/silk/zoom_reverse.png" class="mr5">
			<span>[{isys type="lang" ident="LC__CMDB__CATS__NET_IP_ADDRESSES__REVERSE_NSLOOKUP"}]</span>
		</button>
	</div>
	[{/if}]

	<table class="contentInfoTable mainTable border ml10" cellspacing="0" cellpadding="0" id="ip-table">
		<thead>
			<tr>
				<th style="width:18px;padding-left:5px;"><img src="[{$dir_images}]icons/silk/chart_organisation.png" class="vam" /></th>
				<th style="width:170px;">[{isys type="lang" ident="LC__CMDB__CATS__NET_IP_ADDRESSES__TH__IP"}]</th>
				<th>[{isys type="lang" ident="LC__CMDB__CATS__NET_IP_ADDRESSES__TH__HOSTNAME"}]</th>
				<th>[{isys type="lang" ident="LC__CMDB__CATS__NET_IP_ADDRESSES__TH__OBJECT"}]</th>
				<th>[{isys type="lang" ident="LC__CMDB__CATS__NET_IP_ADDRESSES__TH__PING"}]</th>
				<th>[{isys type="lang" ident="LC__CMDB__CATS__NET_IP_ADDRESSES__TH__NSLOOKUP"}]</th>
				<th class="action_column">[{isys type="lang" ident="LC__CMDB__CATS__NET_IP_ADDRESSES__TH__ACTION"}]</th>
			</tr>
		</thead>
		<tbody id="ip-table-body">
			<tr id="ip-table-obj-adder">
				<td></td>
				<td colspan="2">[{isys type="f_text" name="C__CATS__IP_ADDRESSES__GLOBAL_IP" p_strClass="input input-mini" p_bInfoIconSpacer=0}]</td>
				<td>
					[{isys
						type="f_popup"
						p_strPopupType="browser_object_ng"
						name="C__CATS__IP_ADDRESSES__GLOBAL_OBJ"
						id="C__CATS__IP_ADDRESSES__GLOBAL_OBJ"
						p_strClass="input-small"
						p_bInfoIconSpacer=0}]
				</td>
				<td></td>
				<td></td>
				<td>
					<button id="C__CATS__IP_ADDRESSES__GLOBAL_BUTTON" type="button" class="btn">
						<img src="[{$dir_images}]icons/silk/link.png" class="mr5" /><span>[{isys type="lang" ident="LC__CMDB__CATS__NET_IP_ADDRESSES__CONNECT"}]</span>
					</button>
				</td>
			</tr>
		</tbody>
	</table>

	<div id="table-scroller" class="mr10">
		<table class="contentInfoTable border" cellspacing="0" cellpadding="0" id="statistic-table">
			<thead>
			<tr>
				<th><h3 class="p5 gradient text-shadow"><img src="[{$dir_images}]icons/silk/chart_line.png" class="mr5 vam"><span>[{isys type="lang" ident="LC__CMDB__CATS__NET_IP_ADDRESSES__STATISTIC"}]</span></h3></th>
			</tr>
			</thead>
			<tbody>
			<tr>
				<td><span id="statistic-used-addresses"></span> [{isys type="lang" ident="LC__CMDB__CATS__NET_IP_ADDRESSES__USED_ADDRESSES"}]</td>
			</tr>
			<tr>
				<td><span id="statistic-free-addresses"></span> [{isys type="lang" ident="LC__CMDB__CATS__NET_IP_ADDRESSES__FREE_ADDRESSES"}]</td>
			</tr>
			<tr>
				<td>
					[{isys type="lang" ident="LC__CMDB__CATS__NET_IP_ADDRESSES__NETADDRESS"}]
					<span id="statistic-net-address" class="ml5 grey">[{$net_address}]</span>
				</td>
			</tr>
			<tr>
				<td>
					[{isys type="lang" ident="LC__CATP__IP__SUBNETMASK"}]
					<span id="statistic-net-subnetmask" class="ml5 grey">[{$net_subnet_mask}] (/[{$net_cidr_suffix}])</span>
				</td>
			</tr>
			<tr>
				<td>
					[{isys type="lang" ident="LC__CATP__IP__DEFAULTGATEWAY"}]
					<span id="statistic-net-default_gateway" class="ml5 grey">[{$address_default_gateway}]</span>
				</td>
			</tr>
			<tr>
				<td>
					[{isys type="lang" ident="LC__CMDB__CATS__NET__ADDRESS_RANGE"}]
					<span id="statistic-net-ip_range" class="ml5 grey">[{$address_range_from}] - [{$address_range_to}]</span>
				</td>
			</tr>
			<tr>
				<td>
					[{isys type="lang" ident="LC__CMDB__CATG__SUPERNET"}]
					<span class="ml5">[{$supernet}]</span>
				</td>
			</tr>
			<tr>
				<td>
					[{isys type="lang" ident="LC__REPORT__VIEW__LAYER2_NETS__TITLE"}]
					<span class="ml5">[{if count($layer2_net)}][{$layer2_net|implode:", "}][{else}]-[{/if}]</span>
				</td>
			</tr>
			</tbody>
		</table>

		<br class="m0" />

		<table class="contentInfoTable border" cellspacing="0" cellpadding="0" id="info-table">
			<thead>
				<tr>
					<th><h3 class="p5 gradient text-shadow"><img src="[{$dir_images}]icons/silk/chart_pie.png" class="mr5 vam"><span>[{isys type="lang" ident="LC__CMDB__CATS__NET_IP_ADDRESSES__LEGEND"}]</span></h3></th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td><button id="hide-show-unused" class="btn" type="button"><img src="[{$dir_images}]icons/eye.png" class="mr5" /><span>[{isys type='lang' ident='LC__CMDB__CATS__NET_IP_ADDRESSES__TOGGLE_VIEW'}]</span></button></td>
				</tr>
				<tr class="reserved">
					<td>[{isys type="lang" ident="LC__CMDB__CATS__NET_IP_ADDRESSES__NETADDRESS"}] / Broadcast</td>
				</tr>
				<tr class="default-gateway">
					<td>Default Gateway</td>
				</tr>
				<tr class="unnumbered">
					<td>[{isys type="lang" ident="LC__CATP__IP__ASSIGN__UNNUMBERED"}]</td>
				</tr>
				<tr class="static-address">
					<td>
						[{isys type="lang" ident="LC__CMDB__CATS__NET_IP_ADDRESSES__STATIC"}]
						[{if $has_edit_right}]
						<button id="new-static-area" class="btn btn-small" type="button"><img src="[{$dir_images}]icons/silk/add.png" class="mr5" /><span>[{isys type='lang' ident='LC__CMDB__CATS__NET_IP_ADDRESSES__NEW_AREA'}]</span></button>
						[{/if}]
					</td>
				</tr>
				<tr class="dhcp-reserved-address">
					<td>
						[{isys type="lang" ident="LC__CATP__IP__ASSIGN__DHCP_RESERVED"}]
						[{if $has_edit_right}]
						<button id="new-reserved-dhcp-area" class="btn btn-small" type="button"><img src="[{$dir_images}]icons/silk/add.png" class="mr5" /><span>[{isys type='lang' ident='LC__CMDB__CATS__NET_IP_ADDRESSES__NEW_AREA'}]</span></button>
						[{/if}]
					</td>
				</tr>
				<tr class="dhcp-address">
					<td>
						DHCP
						[{if $has_edit_right}]
						<button id="new-dhcp-area" class="btn btn-small" type="button"><img src="[{$dir_images}]icons/silk/add.png" class="mr5" /><span>[{isys type='lang' ident='LC__CMDB__CATS__NET_IP_ADDRESSES__NEW_AREA'}]</span></button>
						[{/if}]
					</td>
				</tr>
			</tbody>
		</table>

		<br class="m0" />

		[{if $address_conflict && !$is_global_net}]
		<div class="error p10">
			[{isys type="lang" ident="LC__CMDB__CATS__NET__ADDRESS_CONFLICT"}]

			[{isys type="lang" ident="LC__REPORT__VIEW__LAYER3_NETS__IP_ADDRESSES"}]: [{implode(', ', $address_conflict_ips)}]
		</div>

		<br class="m0" />
		[{/if}]
	</div>
</div>

<br style="clear: both" />

<span style="display:none;" id="object-browser">
	[{isys
		type="f_popup"
		p_strPopupType="browser_object_ng"
		name="C__CATS__IP_ADDRESSES"
		id="C__CATS__IP_ADDRESSES"
		callback_accept="idoit.callbackManager.triggerCallback('iplist_connect_success');"
		callback_abort="idoit.callbackManager.triggerCallback('iplist_connect_abort');"
		edit_mode="1"}]
</span>

<script type="text/javascript">
(function () {
	"use strict";

	// Our address-range FROM.
	var address_range_from = '[{$address_range_from}]',
		// The CIDR Suffix
		address_cidr_suffix = '[{$net_cidr_suffix}]',
		address_range_from_long = ((address_cidr_suffix == 32 || address_cidr_suffix == 31)? IPv4.ip2long(address_range_from): (IPv4.ip2long(address_range_from) - 1)),
		// Our address-range TO.
		address_range_to = '[{$address_range_to}]',
		address_range_to_long = ((address_cidr_suffix == 32 || address_cidr_suffix == 32)? IPv4.ip2long(address_range_to): (IPv4.ip2long(address_range_to) + 1)),
		// Our default-gateway.
		address_default_gateway = '[{$address_default_gateway}]',
		address_default_gateway_long = IPv4.ip2long(address_default_gateway),
		// Our array with dhcp (FROM, TO) ranges.
		dhcp_ranges = [{$dhcp_ranges}],
		net_object_id = [{$obj_id}],
		global_button = $('C__CATS__IP_ADDRESSES__GLOBAL_BUTTON'),

		$ip_table_body = $('ip-table-body'),
		$global_ip = $('C__CATS__IP_ADDRESSES__GLOBAL_IP'),
		$tableScroller = $('table-scroller'),
		$legend = $('info-table'),
		$reverse_nslookup_all = $('r-nslookup-all'),
		$nslookup_all = $('nslookup-all'),
		$ping_all = $('ping-all');

	// We calculate the IP's to integers.
	dhcp_ranges.each(function(e) {
		e.from = IPv4.ip2long(e.from);
		e.to = IPv4.ip2long(e.to);
	}.bind(this));

	// How many addresses have we got in the range?
	var diff = address_range_to_long - address_range_from_long;

	if (address_cidr_suffix == 32) {
		diff = 0;
	} else if (address_cidr_suffix == 31) {
		diff = 1;
	}

	// We save our hosts inside an Hash object.
	var hosts = $H([{$hosts}]),
		non_addressed_hosts = $H([{$non_addressed_hosts}]),
		cache_data = '[{$cache_data}]'.evalJSON();

	// And prepare a few other variables.
	var host = null,
		action = null,
		object = null,
		class_name = null,
		act_ip = null,
		display_unused = true,
		checkbox = null,
		checkbox_range = null,
		connect_new_ip = null;

	var address_list_notice = new Element('div', {className: 'note p10', id:'ip_address_list_notice', style:'display:none;'}).update('[{isys type="lang" ident="LC__CMDB__CATS__NET_IP_ADDRESSES__NET_SIZE_NOTICE"}]');
	$tableScroller.insert({bottom: address_list_notice});

	if ((address_cidr_suffix < 22 || diff > 1024) || (Prototype.Browser.IE7 && (address_cidr_suffix < 23 || diff > 512))) {
		display_unused = false;
		$legend.down('tr', 1).hide();
		$legend.select('button').invoke('hide');
		address_list_notice.show();
	}

	var render_ping = function (ip_address) {
		if (cache_data.hasOwnProperty('pings') && cache_data.pings.hasOwnProperty(ip_address)) {
			if (cache_data.pings[ip_address] === true) {
				return new Element('td', {style:'text-align:center;',className:'bg-green'}).update(new Element('img', {src:'[{$dir_images}]icons/silk/tick.png'}));
			} else if (cache_data.pings[ip_address] === false) {
				return new Element('td', {style:'text-align:center;',className:'bg-red'}).update(new Element('img', {src:'[{$dir_images}]icons/silk/cross.png'}));
			}
		}

		return new Element('td', {style:'text-align:center;'}).update('[{isys_tenantsettings::get('gui.empty_value', '-')}]');
	};

	var render_nslookup = function (ip_address, hostname, domains) {
		var $ip = null,
			$hostname = null,
			ip_address_long = 0,
			match, i;

		if (hostname && cache_data.hasOwnProperty('nslookup_ips') && cache_data.nslookup_ips.hasOwnProperty(ip_address)) {
			if (cache_data.nslookup_ips[ip_address] === false) {
				$ip = new Element('img', {src:'[{$dir_images}]icons/silk/help.png', className:'mr5 vam mouse-help', title: '[{isys type="lang" ident="LC__CMDB__CATS__NET_IP_ADDRESSES__NSLOOKUP_NO_HOST_FOUND"}]'});
			} else {
				if (cache_data.nslookup_ips[ip_address] == ip_address) {
					$ip = new Element('span', {className:'green mr5'}).update(ip_address);
				} else {
					ip_address_long = IPv4.ip2long(cache_data.nslookup_ips[ip_address]);

					if (address_range_from_long < ip_address_long && ip_address_long < address_range_to_long) {
						$ip = new Element('button', {type:'button',className:'btn btn-mini mr5 update-host-ip', title:'[{isys type="lang" ident="LC__CMDB__CATS__NET_IP_ADDRESSES__NSLOOKUP_OVERWRITE_IP" p_bHtmlEncode=false}]'})
							.update(new Element('img', {src:'[{$dir_images}]icons/silk/disk.png', className:'mr5'}))
							.insert(new Element('span').update(cache_data.nslookup_ips[ip_address]));
					} else if (cache_data.nslookup_ips[ip_address] !== null) {
						// The IP address lies within another net.
						$ip = new Element('button', {type:'button',className:'btn btn-mini mr5 update-host-notice', title:'[{isys type="lang" ident="LC__CMDB__CATS__NET_IP_ADDRESSES__NSLOOKUP_IP_OUTSIDE_OF_ADDRESS_RANGE" p_bHtmlEncode=false}]'})
							.update(new Element('img', {src:'[{$dir_images}]icons/silk/error.png', className:'mr5'}))
							.insert(new Element('span').update(cache_data.nslookup_ips[ip_address]));
					}

					[{if !$has_edit_right}]
					if ($ip) {
						$ip.disable();
					}
					[{/if}]
				}
			}
		}

		if (cache_data.hasOwnProperty('nslookup_hostnames') && cache_data.nslookup_hostnames.hasOwnProperty(ip_address)) {
			if (cache_data.nslookup_hostnames[ip_address] === false) {
				$hostname = new Element('img', {src:'[{$dir_images}]icons/silk/help.png', className:'mr5 vam mouse-help', title: '[{isys type="lang" ident="LC__CMDB__CATS__NET_IP_ADDRESSES__REVERSE_NSLOOKUP_NO_HOST_FOUND"}]'});
			} else {
				match = false;

				if (Object.isArray(domains) && domains.length > 0) {
					for (i in domains) {
						if (domains.hasOwnProperty(i)) {
							// Check the FQDN.
							if (hostname + '.' + domains[i] == cache_data.nslookup_hostnames[ip_address]) {
								match = true;
							}
						}
					}
				} else {
					match = (cache_data.nslookup_hostnames[ip_address] == hostname);
				}

				if (match) {
					$hostname = new Element('span', {className:'green'}).update(cache_data.nslookup_hostnames[ip_address]);
				} else if (cache_data.nslookup_hostnames[ip_address] !== null) {
					$hostname = new Element('button', {type:'button',className:'btn btn-mini update-host-hostname', title:'[{isys type="lang" ident="LC__CMDB__CATS__NET_IP_ADDRESSES__REVERSE_NSLOOKUP_OVERWRITE_HOSTNAME" p_bHtmlEncode=false}]: ' + cache_data.nslookup_hostnames[ip_address]})
						.update(new Element('img', {src:'[{$dir_images}]icons/silk/disk.png', className:'mr5'}))
						.insert(new Element('span').update(cache_data.nslookup_hostnames[ip_address]));

					[{if !$has_edit_right}]
					if ($hostname) {
					$hostname.disable();
					}
					[{/if}]
				}
			}
		}

		return new Element('td').update($ip).insert($hostname);
	};

	global_button.on('click', function(){
		assign_object_to_hostaddress();
	});

	// Here we render the IP list.
	var render_list = function() {
		var tr, i, cnt, $ping = null, $nslookup = null, $r_nslookup = null;

		// We update the "ip-table-body" with the "ip-table-obj-adder" to empty all the other rows.
		// $ip_table_body.update($('ip-table-obj-adder'));

		// We remove all rows but "#ip-table-obj-adder" because that would remove the Suggestion.
		$ip_table_body.select('tr:not(#ip-table-obj-adder)').invoke('remove');

		if (display_unused) {
			// We will only run through this, if we have a CIDR-suffix from 22 and above.
			for (i = 0; i <= diff; i ++) {
				checkbox = '';
				class_name = '';
				act_ip = address_range_from_long + i;

				// Check if we are displaying an reserved IP-address or the default-gateway.
				if ((i == 0 || act_ip == address_range_to_long) && diff > 1) {
					action = '-';
					class_name = 'used reserved';
				} else if ((act_ip == address_default_gateway_long) && diff > 1) {
					action = '-';
					class_name = 'used default-gateway';
				} else {
					// We only render checkboxes if we are not displaying any reserved addresses and we are allowed to edit (auth-system).
					/*[{if $has_edit_right}]*/
					checkbox = new Element('input', {type: 'checkbox', name: 'ip_list[]'}).observe('click', function(event) {
						mark_selected_area(Event.element(event));
					}.bind(this));
					/*[{/if}]*/
				}

				// Check if we are displaying items inside a DHCP range.
				if (class_name != 'used default-gateway' && class_name != 'used reserved') {
					dhcp_ranges.each(function(e) {
						if (act_ip >= e.from && act_ip <= e.to) {
							if (e.type == 1) {
								class_name = 'dhcp-address';
							} else {
								class_name = 'dhcp-reserved-address';
							}
						}
					}.bind(this))
				}

				// Check if we got some data in our IP-Hash, so we can set some content for the table.
				if (typeof hosts.get(IPv4.long2ip(act_ip)) != 'undefined') {
					host = hosts.get(IPv4.long2ip(act_ip));

					// This is used for the matter that more than one host-address is assigned to a IP address.
					for (cnt in host) {
						if (! host.hasOwnProperty(cnt)) {
							continue;
						}

						$r_nslookup = $nslookup = $ping = null;

						object = new Element('a', {href: '?[{$smarty.const.C__CMDB__GET__OBJECT}]=' + host[cnt].isys_obj__id, className:'ip-list-obj', 'data-obj-id':host[cnt].isys_obj__id}).update(host[cnt].isys_obj__title);
						action = new Element('button', {type: 'button', className:'btn btn-mini object-btn', onClick: 'idoit.callbackManager.triggerCallback(\'iplist_disconnect\', ' + host[cnt].list_id + ')'})
							.update(new Element('img', {src:'[{$dir_images}]icons/silk/detach.png', className:'mr5'}))
							.insert(new Element('span').update('[{isys type="lang" ident="LC__CMDB__CATS__NET_IP_ADDRESSES__DISCONNECT"}]'));

						/* [{if $has_execute_right}] */
						$ping = new Element('button', {type: 'button', className:'btn btn-mini ping-btn ml5', onClick: 'idoit.callbackManager.triggerCallback(\'iplist_ping\', \'' + IPv4.long2ip(act_ip) + '\', this);', title:'[{isys type="lang" ident="LC__CMDB__CATS__NET_IP_ADDRESSES__PING"}]'})
							.update(new Element('img', {src:'[{$dir_images}]icons/silk/drive_network.png'}));

						/* [{if !$nslookup_available}] */
						$nslookup = new Element('button', {type: 'button', className:'btn btn-mini nslookup-btn ml5', title:'[{isys type="lang" ident="LC__CMDB__CATS__NET_IP_ADDRESSES__NOTIFY__NSLOOKUP_NOT_FOUND" p_bHtmlEncode=false}]', disabled:true})
							.update(new Element('img', {src:'[{$dir_images}]icons/silk/zoom.png'}))
							.disable();

						$r_nslookup = new Element('button', {type: 'button', className:'btn btn-mini r_nslookup-btn ml5', title:'[{isys type="lang" ident="LC__CMDB__CATS__NET_IP_ADDRESSES__NOTIFY__NSLOOKUP_NOT_FOUND" p_bHtmlEncode=false}]', disabled:true})
							.update(new Element('img', {src:'[{$dir_images}]icons/silk/zoom_reverse.png'}));
						/* [{else}] */
						$nslookup = new Element('button', {type: 'button', className:'btn btn-mini nslookup-btn ml5', onClick: 'idoit.callbackManager.triggerCallback(\'iplist_nslookup\', ' + parseInt(host[cnt].catg_ip_id) + ', this);', title:'[{isys type="lang" ident="LC__CMDB__CATS__NET_IP_ADDRESSES__NSLOOKUP" p_bHtmlEncode=false}]'})
							.update(new Element('img', {src:'[{$dir_images}]icons/silk/zoom.png'}))
							.disable();

						$r_nslookup = new Element('button', {type: 'button', className:'btn btn-mini r_nslookup-btn ml5', onClick: 'idoit.callbackManager.triggerCallback(\'iplist_r_nslookup\', ' + parseInt(host[cnt].catg_ip_id) + ', this);', title:'[{isys type="lang" ident="LC__CMDB__CATS__NET_IP_ADDRESSES__REVERSE_NSLOOKUP" p_bHtmlEncode=false}]'})
							.update(new Element('img', {src:'[{$dir_images}]icons/silk/zoom_reverse.png'}));

						if (host[cnt].hostname && ! host[cnt].hostname.empty()) {
							$nslookup.addClassName('enabled').enable();
						}
						/* [{/if}] */
						/* [{/if}] */

						// If we got an empty class-name, it's a static address.
						class_name = 'used';

						switch (host[cnt].assignment__id) {
							default:
							case '[{$smarty.const.C__CATP__IP__ASSIGN__STATIC}]': class_name += ' static-address'; break;
							case '[{$smarty.const.C__CATP__IP__ASSIGN__DHCP}]': class_name += ' dhcp-address'; break;
							case '[{$smarty.const.C__CATP__IP__ASSIGN__UNNUMBERED}]': class_name += ' unnumbered'; break;
							case '[{$smarty.const.C__CATP__IP__ASSIGN__DHCP_RESERVED}]': class_name += ' dhcp-reserved-address'; break;
						}

						if (act_ip == address_default_gateway_long) {
							class_name = 'used default-gateway';
						}

						// Start preparing the single table-rows.
						tr = new Element('tr', {id: 'ip-' + (act_ip), className: class_name, 'data-obj-id':host[cnt].isys_obj__id, 'data-obj-title':host[cnt].isys_obj__title, 'data-id': host[cnt].catg_ip_id, 'data-ip':IPv4.long2ip(act_ip)})
							.insert(new Element('td').update(checkbox).addClassName('check'))
							.insert(new Element('td').update(IPv4.long2ip(act_ip)))
							.insert(new Element('td', {'data-hostname':host[cnt].hostname, 'data-domains':(Object.isArray(host[cnt].domains) ? host[cnt].domains.join(';') : '')}).update((host[cnt].hostname || '') + (host[cnt].domain ? ' (' + host[cnt].domain + ')' : '')))
							.insert(new Element('td').update(object))
							.insert(render_ping(IPv4.long2ip(act_ip)))
							.insert(render_nslookup(IPv4.long2ip(act_ip), (host[cnt].hostname || ''), (host[cnt].domains || null)))
							.insert(new Element('td', {className:'action_column'}).update(action).insert($ping).insert($nslookup).insert($r_nslookup))
							.addClassName((i%2) ? 'even' : 'odd');

						$ip_table_body.insert(tr);
					}
				} else {
					$ping = null;
					action = '-';

					// Check if we are displaying items inside a DHCP range.
					if (class_name != 'used default-gateway' && class_name != 'used reserved') {
						/*[{if $has_edit_right}]*/
						action = new Element('button', {type: 'button', className:'btn btn-mini object-btn', onClick: 'idoit.callbackManager.triggerCallback(\'iplist_connect\', \'' + IPv4.long2ip(act_ip) + '\')'})
							.update(new Element('img', {src:'[{$dir_images}]icons/silk/attach.png', className:'mr5'}))
							.insert(new Element('span').update('[{isys type="lang" ident="LC__CMDB__CATS__NET_IP_ADDRESSES__CONNECT"}]'));
						/*[{/if}]*/

						/* [{if $has_execute_right}] */
						$ping = new Element('button', {type: 'button', className:'btn btn-mini ping-btn ml5', onClick: 'idoit.callbackManager.triggerCallback(\'iplist_ping\', \'' + IPv4.long2ip(act_ip) + '\', this);', title:'[{isys type="lang" ident="LC__CMDB__CATS__NET_IP_ADDRESSES__PING"}]'})
							.update(new Element('img', {src:'[{$dir_images}]icons/silk/drive_network.png'}));


						/* [{if !$nslookup_available}] */
						$nslookup = new Element('button', {type: 'button', className:'btn btn-mini nslookup-btn ml5', title:'[{isys type="lang" ident="LC__CMDB__CATS__NET_IP_ADDRESSES__NOTIFY__NSLOOKUP_NOT_FOUND" p_bHtmlEncode=false}]', disabled:true})
							.update(new Element('img', {src:'[{$dir_images}]icons/silk/zoom.png'}))
							.disable();

						$r_nslookup = new Element('button', {type: 'button', className:'btn btn-mini r_nslookup-btn ml5', title:'[{isys type="lang" ident="LC__CMDB__CATS__NET_IP_ADDRESSES__NOTIFY__NSLOOKUP_NOT_FOUND" p_bHtmlEncode=false}]', disabled:true})
							.update(new Element('img', {src:'[{$dir_images}]icons/silk/zoom_reverse.png'}));
						/* [{else}] */
						$nslookup = new Element('button', {type: 'button', className:'btn btn-mini nslookup-btn ml5', title:'[{isys type="lang" ident="LC__CMDB__CATS__NET_IP_ADDRESSES__NSLOOKUP"}]'})
							.update(new Element('img', {src:'[{$dir_images}]icons/silk/zoom.png'}))
							.disable();

						$r_nslookup = new Element('button', {type: 'button', className:'btn btn-mini r_nslookup-btn ml5', onClick: 'idoit.callbackManager.triggerCallback(\'iplist_r_nslookup\', \'' + IPv4.long2ip(act_ip) + '\', this);', title:'[{isys type="lang" ident="LC__CMDB__CATS__NET_IP_ADDRESSES__REVERSE_NSLOOKUP"}]'})
							.update(new Element('img', {src:'[{$dir_images}]icons/silk/zoom_reverse.png'}));
						/* [{/if}] */
						/* [{/if}] */
					}

					// If we got an empty class-name, it's a static address.
					if (class_name == '') {
						class_name = 'static-address';
					}

					// Start preparing the single table-rows.
					tr = new Element('tr', {id: 'ip-' + (act_ip), className: class_name, 'data-ip':IPv4.long2ip(act_ip)})
						.insert(new Element('td').update(checkbox).addClassName('check'))
						.insert(new Element('td').update(IPv4.long2ip(act_ip)))
						.insert(new Element('td', {'data-hostname':'', 'data-domains':''}).update('-'))
						.insert(new Element('td').update('-'))
						.insert(render_ping(IPv4.long2ip(act_ip)))
						.insert(render_nslookup(IPv4.long2ip(act_ip), null))
						.insert(new Element('td', {className:'action_column'}).update(action).insert($ping).insert($nslookup).insert($r_nslookup))
						.addClassName((i%2) ? 'even' : 'odd');

					$ip_table_body.insert(tr);
				}
			}
		} else {
			var rowcol = 0;
			// Here's a special solution for the huge nets.
			hosts.each(function(e) {
				// Because we can have more than one object assigned to a IP we have to iterate.
				for(i in e.value) {
					if (! e.value.hasOwnProperty(i)) {
						continue;
					}

					host = e.value[i];

					var class_name = 'used static-address';
					$r_nslookup = $nslookup = $ping = null;

					dhcp_ranges.each(function(e2) {
						if (IPv4.ip2long(e[0]) >= e2.from && IPv4.ip2long(e[0]) <= e2.to) {
							if (e2.type == '[{$smarty.const.C__NET__DHCP_DYNAMIC}]') {
								class_name = 'dhcp-address';
							} else {
								class_name = 'dhcp-reserved-address';
							}
						}
					});

					if (IPv4.ip2long(e[0]) == address_default_gateway_long) {
						class_name = 'used default-gateway';
					}
					action = new Element('button', {type: 'button', className:'btn btn-mini object-btn', onClick: 'idoit.callbackManager.triggerCallback(\'iplist_disconnect\', ' + host.list_id + ')'})
						.update(new Element('img', {src:'[{$dir_images}]icons/silk/detach.png', className:'mr5'}))
						.insert(new Element('span').update('[{isys type="lang" ident="LC__CMDB__CATS__NET_IP_ADDRESSES__DISCONNECT"}]'));

					/* [{if $has_execute_right}] */
					$ping = new Element('button', {type: 'button', className:'btn btn-mini ping-btn ml5', onClick: 'idoit.callbackManager.triggerCallback(\'iplist_ping\', \'' + e[0] + '\', this);', title:'[{isys type="lang" ident="LC__CMDB__CATS__NET_IP_ADDRESSES__PING"}]'})
						.update(new Element('img', {src:'[{$dir_images}]icons/silk/drive_network.png'}));

					/* [{if !$nslookup_available}] */
					$nslookup = new Element('button', {type: 'button', className:'btn btn-mini nslookup-btn ml5', title:'[{isys type="lang" ident="LC__CMDB__CATS__NET_IP_ADDRESSES__NOTIFY__NSLOOKUP_NOT_FOUND" p_bHtmlEncode=false}]', disabled:true})
						.update(new Element('img', {src:'[{$dir_images}]icons/silk/zoom.png'}))
						.disable();

					$r_nslookup = new Element('button', {type: 'button', className:'btn btn-mini r_nslookup-btn ml5', title:'[{isys type="lang" ident="LC__CMDB__CATS__NET_IP_ADDRESSES__NOTIFY__NSLOOKUP_NOT_FOUND" p_bHtmlEncode=false}]', disabled:true})
						.update(new Element('img', {src:'[{$dir_images}]icons/silk/zoom_reverse.png'}));
					/* [{else}] */
					$nslookup = new Element('button', {type: 'button', className:'btn btn-mini nslookup-btn ml5', onClick: 'idoit.callbackManager.triggerCallback(\'iplist_nslookup\', ' + parseInt(host.catg_ip_id) + ', this);', title:'[{isys type="lang" ident="LC__CMDB__CATS__NET_IP_ADDRESSES__NSLOOKUP"}]'})
						.update(new Element('img', {src:'[{$dir_images}]icons/silk/zoom.png'}))
						.disable();

					$r_nslookup = new Element('button', {type: 'button', className:'btn btn-mini r_nslookup-btn ml5', onClick: 'idoit.callbackManager.triggerCallback(\'iplist_r_nslookup\', ' + parseInt(host.catg_ip_id) + ', this);', title:'[{isys type="lang" ident="LC__CMDB__CATS__NET_IP_ADDRESSES__REVERSE_NSLOOKUP"}]'})
						.update(new Element('img', {src:'[{$dir_images}]icons/silk/zoom_reverse.png'}));

					if(host.hostname && ! host.hostname.empty()) {
						$nslookup.addClassName('enabled').enable();
					}
					/* [{/if}] */
					/* [{/if}] */

					// Start preparing the single table-rows.
					tr = new Element('tr', {id: 'ip-' + IPv4.ip2long(e[0]), className: class_name, 'data-obj-id':host.isys_obj__id, 'data-obj-title':host.isys_obj__title, 'data-id': host.catg_ip_id, 'data-ip':e[0]})
						.insert(new Element('td').addClassName('check'))
						.insert(new Element('td').update(e[0]))
						.insert(new Element('td', {'data-hostname':(host.hostname || ''), 'data-domains':(Object.isArray(host.domains) ? host.domains.join(';') : '')}).update((host.hostname || '') + (host.domain ? ' (' + host.domain + ')' : '')))
						.insert(new Element('td').update(new Element('a', {href: '?[{$smarty.const.C__CMDB__GET__OBJECT}]=' + host.isys_obj__id, className:'ip-list-obj', 'data-obj-id':host.isys_obj__id}).update(host.isys_obj__title)))
						.insert(render_ping(e[0]))
						.insert(render_nslookup(e[0], host.hostname, host.domains))
						.insert(new Element('td', {className:'action_column'}).update(action).insert($ping).insert($nslookup).insert($r_nslookup))
						.addClassName((rowcol%2) ? 'even' : 'odd');
					$ip_table_body.insert(tr);
					rowcol ++;
				}
			});
		}

		$ip_table_body.insert(new Element('tr', {id: 'separator-line', className: 'used'}).insert(new Element('td', {colspan: 7})));

		// Next we will render the small IP-list of hosts with no addresses.
		non_addressed_hosts.each(function(e) {
			var action = new Element('button', {type: 'button', className:'btn btn-mini object-btn', onClick: 'idoit.callbackManager.triggerCallback(\'iplist_disconnect\', ' + e.value.list_id + ')'})
				.update(new Element('img', {src:'[{$dir_images}]icons/silk/detach.png', className:'mr5'}))
				.insert(new Element('span').update('[{isys type="lang" ident="LC__CMDB__CATS__NET_IP_ADDRESSES__DISCONNECT"}]'));

			class_name = 'unnumbered';
			if (e.value.assignment__id == '[{$smarty.const.C__CATP__IP__ASSIGN__DHCP}]') {
				class_name = 'dhcp-address';
			} else if (e.value.assignment__id == '[{$smarty.const.C__CATP__IP__ASSIGN__DHCP_RESERVED}]') {
				class_name = 'dhcp-reserved-address';
			} else if (e.value.assignment__id == '[{$smarty.const.C__CATP__IP__ASSIGN__STATIC}]') {
				class_name = 'static-address';
			}

			tr = new Element('tr', {className: class_name + ' used ' + ((i%2) ? 'even' : 'odd')})
				.insert(new Element('td').addClassName('check'))
				.insert(new Element('td').update('-'))
				.insert(new Element('td', {'data-hostname': (e.value.hostname || ''), 'data-domains': (Object.isArray(e.value.domains) ? e.value.domains.join(';') : '')}).update((e.value.hostname || '') + (e.value.domain ? ' (' + e.value.domain + ')' : '')))
				.insert(new Element('td').update(new Element('a', {href: '?[{$smarty.const.C__CMDB__GET__OBJECT}]=' + e.value.isys_obj__id, className:'ip-list-obj', 'data-obj-id':e.value.isys_obj__id}).update(e.value.isys_obj__title)))
				.insert(render_ping(null))
				.insert(render_nslookup(null, (e.value.hostname || '')))
				.insert(new Element('td', {className:'action_column'}).update(action));
			i++;
			$ip_table_body.insert(tr);
		});

		[{if $has_edit_right}]
		action = new Element('button', {type: 'button', className:'btn btn-mini object-btn', onClick: 'idoit.callbackManager.triggerCallback(\'iplist_connect\');'})
			.update(new Element('img', {src:'[{$dir_images}]icons/silk/attach.png', className:'mr5'}))
			.insert(new Element('span').update('[{isys type="lang" ident="LC__CMDB__CATS__NET_IP_ADDRESSES__CONNECT"}]'));

		tr = new Element('tr', {className: (i%2) ? 'even' : 'odd'}).insert(new Element('td', {colspan: 7, align: 'right', className:'action_column'}).update(action));
		$ip_table_body.insert(tr);
		[{else}]
		if ($('ip-table-obj-adder')) {
			$('ip-table-obj-adder').hide();
		}

		// We only hide the button for assigning objects.
		$ip_table_body.select('.object-btn').invoke('hide');
		[{/if}]

		if ($('hide-show-unused').hasClassName('active')) {
			$('hide-show-unused').removeClassName('active').simulate('click');
		}

		$ip_table_body.select('a.ip-list-obj').each(function($link) {
			new Tip($link, '', {ajax: {url: window.www_dir + '?ajax=1&call=quick_info&objID=' + $link.readAttribute('data-obj-id')}, delay: '0', stem: 'topLeft', style: 'default', className: 'objectinfo'});
		});
	};

	var set_area = function(from, to, type) {
		// Create a new DHCP range, using ajax!
		new Ajax.Call('?call=dhcp&ajax=1&[{$smarty.const.C__CMDB__GET__OBJECT}]=[{$obj_id}]',
			{
				requestHeaders: {Accept: 'application/json'},
				method: 'post',
				parameters: {from: IPv4.long2ip(from.substr(3)), to: IPv4.long2ip(to.substr(3)), type: type},
				onSuccess: function(transport) {
					var json = transport.responseText.evalJSON();

					// We got our response - Now we display the new range!
					if (json.result == 'success' || json.result == 'merged') {

						if (typeof json.result_data != 'undefined') {
							// We push our new data to the data-array.
							dhcp_ranges = json.result_data;

							// We calculate the IP's to integers.
							dhcp_ranges.each(function(e) {
								e.from = IPv4.ip2long(e.from);
								e.to = IPv4.ip2long(e.to);
							}.bind(this));
						} else {
							dhcp_ranges = [];
						}
					}

					// And render the list again.
					render_list();
					update_statistics();
				}.bind(this)
			});
	};

	var mark_selected_area = function(e) {
		var ip = e.up('tr').readAttribute('id').substr(3);
		checkbox_range = $H();

		// At first, we remove all the colored TD's
		$$('#ip-table-body tr td.check').each(function(e) {
			e.removeClassName('sel');

			if (e.down('input')) {
				e.down('input').enable();
			}
		});

		$$('input[name="ip_list[]"]:checked').each(function(e) {
			checkbox_range.set(e.up('tr').readAttribute('id').substr(3), true);
		});
		if (checkbox_range.size() == 1) {
			$('ip-' + checkbox_range.keys()[0]).down('td').addClassName('sel');
		} else if (checkbox_range.size() == 2) {

			$$('#ip-table-body tr td input:not(:checked)').invoke('disable');

			$$('#ip-table-body tr').each(function(el) {

				var keys = checkbox_range.keys().sort(function(a,b){return a-b});
				var ip;

				if (el.hasAttribute('id')) {
					ip = el.readAttribute('id').substr(3);
					ip = parseInt(ip); //parseInt added to fix compare string with int
				}

				if (ip >= keys[0] && ip <= keys[1]) {
					el.down('td').addClassName('sel');
				}
			});
		}
	};

	/*[{if $has_edit_right}]*/
	// We observe the clicks on the "+ new area" buttons.
	$('new-dhcp-area', 'new-reserved-dhcp-area', 'new-static-area').invoke('on', 'click', function(e) {
		var checked_addresses = $$('[name="ip_list[]"]:checked'),
			from,
			to;

		// Look if we got two checkboxes selected (range "from" and "to").
		if (checked_addresses.length == 0 || checked_addresses.length > 2) {
			idoit.Notify.warning('[{isys type="lang" ident="LC__CMDB__CATS__NET_IP_ADDRESSES__MAX_TWO_ADDRESSES_SELECTABLE" p_bHtmlEncode=false}]', {life:5});
			return;
		}

		from = checked_addresses[0].up('tr').readAttribute('id');

		if (checked_addresses.length == 1) {
			to = from;
		} else {
			to = checked_addresses[1].up('tr').readAttribute('id')
		}

		// Please note: The given ID (third parameter) decides in the ajax handler class what to do!
		set_area(from, to, e.findElement('button').id);
	}.bind(this));
	/*[{/if}]*/

	$('hide-show-unused').on('click', function() {
		this.toggleClassName('active');

		$$('#ip-table-body tr').each(function(e, i) {
			if (! e.hasClassName('used')) {
				e.toggle();
			}

			// Remove all "even" and "odd" class names.
			e.removeClassName('even').removeClassName('odd');

			// Add the "even" and "odd" class names for the new visible items.
			if (e.getStyle('display') != 'none') {
				e.addClassName((i%2) ? 'even' : 'odd');
			}
		});
	});

	// Method for calling the object browser inside our IP-list.
	var connect = function(ip) {
		// Openes the object-browser.
		$('object-browser').down('a:not(.wiki-link)').click();

		// We set a new entry with the
		connect_new_ip = ip;
	};

	var ping = function (ip, $button, callback) {
		if (Object.isElement($button)) {
			$button.disable().down('img').writeAttribute('src', '[{$dir_images}]ajax-loading.gif');
		}

		new Ajax.Request('?call=ip_addresses&method=ping&ajax=1', {
			method: 'post',
			parameters: {
				ip: Object.toJSON(ip),
				net_obj:net_object_id
			},
			onSuccess: function (transport) {
				var json = transport.responseJSON, ip;

				if (! cache_data.hasOwnProperty('pings')) {
					cache_data.pings = {};
				}

				try {
					if (json && json.success) {
						for (ip in json.data) {
							if (json.data.hasOwnProperty(ip)) {
								cache_data.pings[ip] = json.data[ip];

								$('ip-' + IPv4.ip2long(ip)).highlight().down('td', 4).replace(render_ping(ip))
							}
						}
					} else {
						throw (json.message || '[{isys type="lang" ident="LC__CMDB__CATS__NET_IP_ADDRESSES__PING_EMPTY_RESULT"}]');
					}
				} catch (e) {
					idoit.Notify.error(e, {sticky:true});
				}
			},
			onFailure: function (transport) {
				idoit.Notify.error(transport.responseText, {life: 10});
			},
			onComplete: function (transport) {
				if (Object.isElement($button)) {
					$button.enable().down('img').writeAttribute('src', '[{$dir_images}]icons/silk/drive_network.png');
				}

				if (Object.isFunction(callback)) {
					callback(transport);
				}
			}
		});
	};

	var r_ns_lookup = function (catg_ip_id, $button, callback) {
		if (catg_ip_id == null) {
			return;
		}

		if (Object.isElement($button)) {
			$button.disable().down('img').writeAttribute('src', '[{$dir_images}]ajax-loading.gif');
		}

		new Ajax.Request('?call=ip_addresses&method=r_nslookup&ajax=1', {
			method: 'post',
			parameters: {
				catg_ip_id: Object.toJSON(catg_ip_id),
				net_obj: net_object_id
			},
			onSuccess: function (transport) {
				var json = transport.responseJSON,
					ip, item, $row, $td, domains, messages = [], old_hostname;

				if (! cache_data.hasOwnProperty('nslookup_hostnames')) {
					cache_data.nslookup_hostnames = {};
				}

				try {
					if (json && json.success) {
						for (ip in json.data) {
							if (json.data.hasOwnProperty(ip)) {
								item = json.data[ip];
								$row = $('ip-' + IPv4.ip2long(ip));
								old_hostname = $row.down('td', 2).readAttribute('data-hostname');
								domains = $row.down('td', 2).readAttribute('data-domains').split(';');
								$td = $row.down('td', 5);

								if (item.success) {
									cache_data.nslookup_hostnames[ip] = item.data;

									$row.highlight();

									$td.replace(render_nslookup(ip, old_hostname, domains))
								} else {
									messages.push('<strong>' + ip + '</strong> [{isys type="lang" ident="LC__CMDB__CATS__NET_IP_ADDRESSES__REVERSE_NSLOOKUP_NO_HOST_FOUND"}]');

									$row.highlight({startcolor:'#ff4343'});
								}
							}
						}

						if (messages.length) {
							idoit.Notify.info(messages.join('<br />'), {sticky: true});
						}
					} else {
						throw (json.message || '[{isys type="lang" ident="LC__CMDB__CATS__NET_IP_ADDRESSES__PING_EMPTY_RESULT"}]');
					}
				} catch (e) {
					idoit.Notify.error(e, {sticky:true});
				}
			},
			onFailure: function (transport) {
				idoit.Notify.error(transport.responseText, {life: 10});
			},
			onComplete: function (transport) {
				if (Object.isElement($button)) {
					$button.enable().down('img').writeAttribute('src', '[{$dir_images}]icons/silk/zoom_reverse.png');
				}

				if (Object.isFunction(callback)) {
					callback(transport);
				}
			}
		});
	};

	var ns_lookup = function (catg_ip_id, $button, callback) {
		if (catg_ip_id == null) {
			return;
		}

		if (Object.isElement($button)) {
			$button.disable().removeClassName('btn-red').removeClassName('btn-green')
				.down('img').writeAttribute('src', '[{$dir_images}]ajax-loading.gif');
		}

		new Ajax.Request('?call=ip_addresses&method=nslookup&ajax=1', {
			method: 'post',
			parameters: {
				catg_ip_id: Object.toJSON(catg_ip_id),
				net_obj: net_object_id
			},
			onSuccess: function (transport) {
				var json = transport.responseJSON,
					ip, old_ip, item, $row, $td, messages = [];

				if (! cache_data.hasOwnProperty('nslookup_ips')) {
					cache_data.nslookup_ips = {};
				}

				try {
					if (json && json.success) {
						for (ip in json.data) {
							if (json.data.hasOwnProperty(ip)) {
								item = json.data[ip];
								$row = $('ip-' + IPv4.ip2long(ip));
								old_ip = $row.readAttribute('data-ip');
								$td = $row.down('td', 5);

								if (item.success) {
									cache_data.nslookup_ips[ip] = item.data;

									$td.replace(render_nslookup(old_ip, $row.down('td', 2).readAttribute('data-hostname'), $row.down('td', 2).readAttribute('data-domains').split(';')));
								} else {
									messages.push('<strong>' + ip + '</strong> [{isys type="lang" ident="LC__CMDB__CATS__NET_IP_ADDRESSES__NSLOOKUP_NO_HOST_FOUND"}]');

									$row.highlight({startcolor:'#ff4343'});
								}
							}
						}

						if (messages.length) {
							idoit.Notify.info(messages.join('<br />'), {sticky: true});
						}
					} else {
						throw (json.message || '[{isys type="lang" ident="LC__CMDB__CATS__NET_IP_ADDRESSES__PING_EMPTY_RESULT"}]');
					}
				} catch (e) {
					idoit.Notify.error(e, {stickz: true});
				}
			},
			onFailure: function (transport) {
				idoit.Notify.error(transport.responseText, {life: 10});
			},
			onComplete: function (transport) {
				if (Object.isElement($button)) {
					$button.enable().down('img').writeAttribute('src', '[{$dir_images}]icons/silk/zoom.png');
				}

				if (Object.isFunction(callback)) {
					callback(transport);
				}
			}
		});
	};

	$ip_table_body.on('click', 'button.update-host-hostname', function (ev) {
		var $button = ev.findElement('button'),
			$tr = $button.up('tr'),
			catg_ip_id = $tr.readAttribute('data-id'),
			new_hostname = cache_data.nslookup_hostnames[$tr.readAttribute('data-ip')];

		if (confirm('[{isys type="lang" ident="LC__CMDB__CATS__NET_IP_ADDRESSES__REVERSE_NSLOOKUP_OVERWRITE_HOSTNAME_CONFIRM" p_bHtmlEncode=false}]')) {
			$button.disable().down('img').writeAttribute('src', '[{$dir_images}]ajax-loading.gif');

			new Ajax.Request('?call=ip_addresses&method=update-hostname&ajax=1', {
				method: 'post',
				parameters: {
					catg_ip_id: catg_ip_id,
					new_hostname: new_hostname,
					net_obj_id: net_object_id
				},
				onSuccess: function (transport) {
					var json = transport.responseJSON;

					if (json && json.success) {
						// We got our response - Now we display the new range!
						if (json.data.hasOwnProperty('hosts')) {
							hosts = $H(json.data.hosts);
						}

						// And render the list again.
						render_list();
						update_statistics();
					} else {
						idoit.Notify.error(json.message, {life: 10});
					}
				},
				onFailure: function (transport) {
					idoit.Notify.error(transport.responseText, {life: 10});
				}
			});
		}
	});

	$ip_table_body.on('click', 'button.update-host-ip', function (ev) {
		var $button = ev.findElement('button'),
			$tr = $button.up('tr'),
			catg_ip_id = $tr.readAttribute('data-id'),
			new_ip = $button.down('span').innerHTML,
			old_ip = $tr.readAttribute('data-ip');

		if (confirm('[{isys type="lang" ident="LC__CMDB__CATS__NET_IP_ADDRESSES__NSLOOKUP_OVERWRITE_IP_CONFIRM" p_bHtmlEncode=false}]')) {
			$button.disable().down('img').writeAttribute('src', '[{$dir_images}]ajax-loading.gif');

			new Ajax.Request('?call=ip_addresses&method=update-ip-address&ajax=1', {
				method: 'post',
				parameters: {
					catg_ip_id: catg_ip_id,
					old_ip: old_ip,
					new_ip: new_ip,
					net_obj_id: net_object_id
				},
				onSuccess: function (transport) {
					var json = transport.responseJSON;

					if (json && json.success) {
						// We got our response - Now we display the new range!
						if (json.data.hasOwnProperty('hosts')) {
							hosts = $H(json.data.hosts);
						}

						// And render the list again.
						render_list();
						update_statistics();
					} else {
						idoit.Notify.error(json.message, {life: 10});
					}
				},
				onFailure: function (transport) {
					idoit.Notify.error(transport.responseText, {life: 10});
				}
			});
		}
	});

	$ip_table_body.on('click', 'button.update-host-notice', function (ev) {
		alert(ev.findElement('button').readAttribute('title'));
	});


	// When the user clicks the "accept"-button in the object browser.
	var connect_success = function (conn_obj) {
		var obj;

		if (Object.isUndefined(conn_obj)) {
			obj = $('C__CATS__IP_ADDRESSES__HIDDEN').getValue();
		} else {
			obj = conn_obj;
		}

		global_button
			.disable()
			.down('img').writeAttribute('src', '[{$dir_images}]ajax-loading.gif')
			.next('span').update('[{isys type="lang" ident="LC__UNIVERSAL__LOADING"}]');

		new Ajax.Call('?call=ip_addresses&method=c&ajax=1',
			{
				requestHeaders: {Accept: 'application/json'},
				method: 'post',
				parameters: {
					'[{$smarty.const.C__CMDB__GET__OBJECT}]': '[{$obj_id}]',
					'[{$smarty.const.C__CMDB__GET__OBJECT}]2': obj,
					'ip': connect_new_ip
				},
				onSuccess: function (transport) {
					var json = transport.responseText.evalJSON();

					// We got our response - Now we display the new range!
					if (json.result == 'success') {
						// We fill our host-hash.
						hosts = $H(json.hosts);
						non_addressed_hosts = $H(json.not_addressed_hosts);
						idoit.Notify.success('[{isys type="lang" ident="LC__INFOBOX__DATA_WAS_SAVED"}]');
					}
				},
				onComplete: function () {
					global_button
						.enable()
						.down('img').writeAttribute('src', '[{$dir_images}]icons/silk/link.png')
						.next('span').update('[{isys type="lang" ident="LC__CMDB__CATS__NET_IP_ADDRESSES__CONNECT"}]');

					// And render the list again.
					render_list();
					update_statistics();
				}
			});
	};

	// Method for disconnecting an host object.
	var disconnect = function(obj) {
		[{if $is_global_net}]
		idoit.Notify.error('[{isys type="lang" ident="LC__CMDB__CATS__NET_IP_ADDRESSES__DISCONNECT_GLOBAL_NET" p_bHtmlEncode=0}]', {sticky:true});
		[{else}]

		if (confirm('[{isys type="lang" ident="LC__CMDB__CATS__NET_IP_ADDRESSES__DISCONNECT_CONFIRMATION" p_bHtmlEncode=0}]')) {
			new Ajax.Call('?call=ip_addresses&method=d&ajax=1',
				{
					requestHeaders: {Accept: 'application/json'},
					method: 'post',
					parameters: {'[{$smarty.const.C__CMDB__GET__OBJECT}]': obj, '[{$smarty.const.C__CMDB__GET__OBJECT}]2': '[{$obj_id}]'},
					onSuccess: function(transport) {
						var json = transport.responseText.evalJSON();

						// We got our response - Now we display the new range!
						if (json.result == 'success') {
							// We fill our host-hash.
							hosts = $H(json.hosts);
							non_addressed_hosts = $H(json.not_addressed_hosts);
						}

						// And render the list again.
						render_list();
						update_statistics();
					}.bind(this)
				});
		}
		[{/if}]
	};

	var update_statistics = function() {
		$('statistic-used-addresses').update(hosts.size());
		$('statistic-free-addresses').update(diff - hosts.size() - 1);
	};

	render_list();
	update_statistics();

	var legend_scroll_at = '[{$legend_scroller}]';

	// This little snippet will move the to right boxes, while scrolling.
	$('contentWrapper').on('scroll', function() {
		var top = this.scrollTop,
			scroll_at;

		if(legend_scroll_at != ''){
			scroll_at = parseInt(legend_scroll_at);
		} else{
			scroll_at = 130;
		}
		if (top > scroll_at) {
			$tableScroller.setStyle({top: 145 + (top - scroll_at ) + 'px'});
		} else {
			$tableScroller.setStyle({top: null});
		}
	});

	// The IE7 has some problems with the table-width, so we fix that issue.
	if (Prototype.Browser.IE7) {
		$('ip-table').setStyle({width: '60%'});
	}

	// New functions for the "global" object adder.
	var validate_global_ip = function () {
		// Check, if the given IP address lies inside this net and is not taken.
		var $ip_address = $global_ip.removeClassName('error').writeAttribute('title', ''),
			ip_address_value = $ip_address.getValue(),
			ip_long,
			used_addresses;

		// We check, if all fields are empty (unnumbered).
		if (ip_address_value.blank()) {
			// Attention! This returns true, because a unnumbered IP is okay.
			$ip_address.highlight({startcolor:'#C3F4C3', restorecolor:'#FBFBFB'});
			return true;
		}

		// We check, if the given IP address is valid.
		if (! IPv4.valid_ip(ip_address_value)) {
			$ip_address
				.addClassName('error')
				.writeAttribute('title', '[{isys type="lang" ident="LC__CMDB__OBJECT_BROWSER__SCRIPT_JS__ERROR_URL"}]');
			return false;
		}

		ip_long = IPv4.ip2long(ip_address_value);

		// We check, if the given IP address is inside this net.
		if (! (address_range_from_long < ip_long && ip_long < address_range_to_long)) {
			$ip_address
				.addClassName('error')
				.writeAttribute('title', '[{isys type="lang" ident="LC__CMDB__IP__NOT_INSIDE_NET"}]');
			return false;
		}

		// We check, if the given IP address was already assigned.
		used_addresses = $ip_table_body.select('tr.used:not(.unnumbered)').invoke('readAttribute', 'id').invoke('substring', 3);

		if (used_addresses.in_array(ip_long + '')) {
			var row = $('ip-' + ip_long);

			$ip_address
				.addClassName('error')
				.writeAttribute('title', '[{isys type="lang" ident="LC__CATG__IP__UNIQUE_IP_WARNING" p_bHtmlEncode=false}]'.replace('%s', row.readAttribute('data-obj-title')).replace('%d', row.readAttribute('data-obj-id')));
			return false;
		}

		$ip_address.highlight({startcolor:'#C3F4C3', restorecolor:'#FBFBFB'});
		return true;
	};

	if ($global_ip) {
		$global_ip.on('change', validate_global_ip);
	}

	var assign_object_to_hostaddress = function() {
		if ($('C__CATS__IP_ADDRESSES__GLOBAL_OBJ__HIDDEN')) {
			var obj = $F('C__CATS__IP_ADDRESSES__GLOBAL_OBJ__HIDDEN'),
					obj_browser_field = $('C__CATS__IP_ADDRESSES__GLOBAL_OBJ__VIEW').removeClassName('error');

			if (validate_global_ip()) {
				if (obj > 0) {
					connect_new_ip = $global_ip.getValue();

					if (connect_new_ip == '...') {
						connect_new_ip = '';
					}

					connect_success(obj);
				}
				else {
					obj_browser_field.addClassName('error');
					idoit.Notify.warning('[{isys type="lang" ident="LC__CMDB__CATS__NET_IP_ADDRESSES__PLEASE_SELECT_AN_OBJECT" p_bHtmlEncode=false}]',
							{life: 5});
				}
			}
			else {
				idoit.Notify.warning('[{isys type="lang" ident="LC__CMDB__CATS__NET_IP_ADDRESSES__IP_INVALID" p_bHtmlEncode=false}]',
						{life: 5});
			}
		}
	};

	if ($reverse_nslookup_all) {
		$reverse_nslookup_all.on('click', function (ev) {
			var $button = ev.findElement('button'),
				$r_nslookup_buttons,
				ip_addresses;

			if ($ip_table_body.down('td.sel')) {
				$r_nslookup_buttons = $ip_table_body.select('td.sel').invoke('up', 'tr').invoke('down', '.r_nslookup-btn').filter(function($el) { return Object.isElement($el); });
			} else {
				$r_nslookup_buttons = $ip_table_body.select('.r_nslookup-btn');
			}

			if (confirm('[{isys type="lang" ident="LC__CMDB__CATS__NET_IP_ADDRESSES__REVERSE_NSLOOKUP_ALL_CONFIRM" p_bHtmlEncode=false}]'.replace(/%d/, $r_nslookup_buttons.length))) {
				ip_addresses = $r_nslookup_buttons.invoke('disable').invoke('up', 'tr').invoke('readAttribute', 'data-ip');

				if (ip_addresses.length) {
					$button.disable().down('img').writeAttribute('src', '[{$dir_images}]ajax-loading.gif');

					r_ns_lookup(ip_addresses, null, function () {
						$r_nslookup_buttons.invoke('enable');
						$button.enable().down('img').writeAttribute('src', '[{$dir_images}]icons/silk/zoom_reverse.png');
					});
				}
			}
		});
	}

	if ($nslookup_all) {
		$nslookup_all.on('click', function (ev) {
			var $button = ev.findElement('button'),
				$nslookup_buttons,
				catg_ip_ids;

			if ($ip_table_body.down('td.sel')) {
				$nslookup_buttons = $ip_table_body.select('td.sel').invoke('up', 'tr').invoke('down', '.nslookup-btn.enabled').filter(function($el) { return Object.isElement($el); });
			} else {
				$nslookup_buttons = $ip_table_body.select('.nslookup-btn.enabled');
			}

			if (confirm('[{isys type="lang" ident="LC__CMDB__CATS__NET_IP_ADDRESSES__NSLOOKUP_ALL_CONFIRM" p_bHtmlEncode=false}]'.replace(/%d/, $nslookup_buttons.length))) {
				catg_ip_ids = $nslookup_buttons.invoke('up', 'tr').invoke('readAttribute', 'data-id');

				if (catg_ip_ids.length) {
					$nslookup_buttons.invoke('disable');
					$button.disable().down('img').writeAttribute('src', '[{$dir_images}]ajax-loading.gif');

					ns_lookup(catg_ip_ids, null, function () {
						$nslookup_buttons.invoke('enable');
						$button.enable().down('img').writeAttribute('src', '[{$dir_images}]icons/silk/zoom.png');
					});
				}
			}
		});
	}

	if ($ping_all) {
		$ping_all.on('click', function (ev) {
			var $button = ev.findElement('button'),
				$ping_buttons,
				ips,
				cnt = 0;

			if ($ip_table_body.down('td.sel')) {
				$ping_buttons = $ip_table_body.select('td.sel:first,td.sel:last').invoke('up', 'tr').invoke('down', '.ping-btn').filter(function($el) { return Object.isElement($el); });
				cnt = $ip_table_body.select('td.sel').length;
			} else {
				$ping_buttons = $ip_table_body.select('.ping-btn');
				cnt = $ping_buttons.length;
			}

			if (confirm('[{isys type="lang" ident="LC__CMDB__CATS__NET_IP_ADDRESSES__PING_ALL_CONFIRM" p_bHtmlEncode=false}]'.replace(/%d/, cnt))) {
				ips = $ping_buttons.invoke('up', 'tr').invoke('readAttribute', 'data-ip');

				if (ips.length) {
					$ping_buttons.invoke('disable');
					$button.disable().down('img').writeAttribute('src', '[{$dir_images}]ajax-loading.gif');

					ping(ips, null, function () {
						$ping_buttons.invoke('enable');
						$button.enable().down('img').writeAttribute('src', '[{$dir_images}]icons/silk/drive_network.png');
					});
				}
			}
		});
	}

	// Adding the global callbacks.
	idoit.callbackManager
		.registerCallback('iplist_connect_success', connect_success)
		.registerCallback('iplist_disconnect', disconnect)
		.registerCallback('iplist_connect', connect)
		.registerCallback('iplist_ping', ping)
		.registerCallback('iplist_nslookup', ns_lookup)
		.registerCallback('iplist_r_nslookup', r_ns_lookup)
		.registerCallback('iplist_connect_abort', function() { connect_new_ip = null; });

	[{if !$ping_available}]
	if ($ping_all) {
		$ping_all
			.disable().addClassName('mouse-info').writeAttribute('title', '[{isys type="lang" ident="LC__CMDB__CATS__NET_IP_ADDRESSES__NOTIFY__`$ping_method`_NOT_FOUND" p_bHtmlEncode=false}]')
			.down('img').writeAttribute('src', '[{$dir_images}]icons/silk/cross.png');
	}
	[{/if}]

	[{if !$nslookup_available}]
	if ($nslookup_all) {
		$nslookup_all
			.disable().addClassName('mouse-info').writeAttribute('title', '[{isys type="lang" ident="LC__CMDB__CATS__NET_IP_ADDRESSES__NOTIFY__NSLOOKUP_NOT_FOUND" p_bHtmlEncode=false}]')
			.down('img').writeAttribute('src', '[{$dir_images}]icons/silk/cross.png');
	}

	if ($reverse_nslookup_all) {
		$reverse_nslookup_all
			.disable().addClassName('mouse-info').writeAttribute('title', '[{isys type="lang" ident="LC__CMDB__CATS__NET_IP_ADDRESSES__NOTIFY__NSLOOKUP_NOT_FOUND" p_bHtmlEncode=false}]')
			.down('img').writeAttribute('src', '[{$dir_images}]icons/silk/cross.png');
	}
	[{/if}]

	function responsiveFunction () {
		var scrollerWidth = $('scroller').getWidth();

		if (scrollerWidth < 1140) {
			$tableScroller.addClassName('condensed');
		} else if (scrollerWidth < 1350) {
			$tableScroller
				.removeClassName('condensed')
				.addClassName('condensed-half');
		} else {
			$tableScroller.removeClassName('condensed-half');
		}
	}

	Event.observe(window, 'resize', responsiveFunction);

	idoit.callbackManager.registerCallback('idoit-dragbar-update', responsiveFunction);

	responsiveFunction();
}());
</script>