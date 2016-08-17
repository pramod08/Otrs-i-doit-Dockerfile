<style type="text/css">
	.slaac {
		background: #FFFFFF !important;
	}

	.slaac-dhcp {
		background: #E2F4FF url('[{$image_path}]list/ip_list_slaac_dhcp.png') !important;
	}

	.slaac-dhcp.even {
		background: #E2F4FF url('[{$image_path}]list/ip_list_slaac_dhcp.png') 46px 0 !important;
	}
</style>

<table class="contentInfoTable mainTable m10 border" cellspacing="0" cellpadding="0" id="ip-table">
	<thead>
		<tr>
			<th style="width:280px">IP</th>
			<th>[{isys type="lang" ident="LC__CATP__IP__HOSTNAME"}]</th>
			<th>[{isys type="lang" ident="LC__CMDB__CATS__NET_IP_ADDRESSES__OBJECT"}]</th>
			<th class="action_column">[{isys type="lang" ident="LC__CMDB__CATS__NET_IP_ADDRESSES__ACTION"}]</th>
		</tr>
	</thead>
	<tbody id="ip-table-body">
	</tbody>
</table>

<div id="table-scroller" class="p10">
	<div class="note p10 mb10">[{isys type="lang" ident="LC__CMDB__CATS__NET_IP_ADDRESSES__NET_SIZE_NOTICE"}]</div>
	<table class="contentInfoTable border border-ccc" cellspacing="0" cellpadding="0" id="info-table">
		<thead>
			<tr>
				<th>&nbsp;[{isys type="lang" ident="LC__CMDB__CATS__NET_IP_ADDRESSES__LEGEND"}]</th>
			</tr>
		</thead>
		<tbody>
			<tr class="static-address">
				<td>[{isys type="lang" ident="LC__CMDB__CATG__IP__STATIC"}]</td>
			</tr>
			<tr class="slaac">
				<td>[{isys type="lang" ident="LC__CMDB__CATG__IP__SLAAC"}]</td>
			</tr>
			<tr class="slaac-dhcp">
				<td>[{isys type="lang" ident="LC__CMDB__CATG__IP__SLAAC_AND_DHCPV6"}]</td>
			</tr>
			<tr class="dhcp-address">
				<td>[{isys type="lang" ident="LC__CMDB__CATG__IP__DHCPV6"}]</td>
			</tr>
			<tr class="dhcp-reserved-address">
				<td>[{isys type="lang" ident="LC__CMDB__CATG__IP__DHCPV6_RESERVED"}]</td>
			</tr>
		</tbody>
	</table>

	<br />

	<table class="contentInfoTable border border-ccc" cellspacing="0" cellpadding="0" id="statistic-table">
		<thead>
			<tr>
				<th>&nbsp;[{isys type="lang" ident="LC__CMDB__CATS__NET_IP_ADDRESSES__STATISTIC"}]</th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td><span id="statistic-used-addresses"></span> [{isys type="lang" ident="LC__CMDB__CATS__NET_IP_ADDRESSES__USED_ADDRESSES"}]</td>
			</tr>
			<tr>
				<td>[{isys type="lang" ident="LC__CMDB__CATS__NET_IP_ADDRESSES__NETADDRESS"}] <span id="statistic-net-address">[{$net_address}]</span></td>
			</tr>
			<tr>
				<td>[{isys type="lang" ident="LC__CMDB__CATS__NET_IP_ADDRESSES__PREFIXLENGTH"}] <span id="statistic-net-subnetmask">[{$net_subnet_mask}] (/[{$net_cidr_suffix}])</span></td>
			</tr>
			<tr>
				<td>[{isys type="lang" ident="LC__CMDB__CATS__NET__ADDRESS_RANGE"}] <span id="statistic-net-ip_range">[{$address_range_from}] - [{$address_range_to}]</span></td>
			</tr>
		</tbody>
	</table>

	[{if $address_conflict && !$is_global_net}]
	<div class="error p10 m10">
		[{isys type="lang" ident="LC__CMDB__CATS__NET__ADDRESS_CONFLICT"}]

		[{isys type="lang" ident="LC__REPORT__VIEW__LAYER3_NETS__IP_ADDRESSES"}]: [{implode(', ', $address_conflict_ips)}]
	</div>
	[{/if}]
</div>

<br style="clear: both" />

<script type="text/javascript">
	(function () {
		"use strict";

		var ip_table_body = $('ip-table-body'),
			hosts = $H([{$hosts}]),
			non_addressed_hosts = $H([{$non_addressed_hosts}]),
			class_name = null;

		// Here we render the IP list.
		var render_list = function () {
			var cnt = 0, action, tr, i;
			ip_table_body.update();

			hosts.each(function (e) {
				for (i = 0; i < e.value.size(); i++) {
					// Prepare the row css-classes.
					switch (e.value[i].assignment__id) {
						case '[{$smarty.const.C__CMDB__CATG__IP__DHCPV6}]':
							class_name = 'dhcp-address';
							break;

						case '[{$smarty.const.C__CMDB__CATG__IP__SLAAC_AND_DHCPV6}]':
							class_name = 'slaac-dhcp';
							break;

						case '[{$smarty.const.C__CMDB__CATG__IP__SLAAC}]':
							class_name = 'slaac';
							break;

						case '[{$smarty.const.C__CMDB__CATG__IP__DHCPV6_RESERVED}]':
							class_name = 'dhcp-reserved-address';
							break;

						default:
						case '[{$smarty.const.C__CMDB__CATG__IP__STATIC}]':
							class_name = 'static-address';
							break;
					}

					action = new Element('a', {href: '#', onClick: 'idoit.callbackManager.triggerCallback(\'iplist_disconnect\', ' + e.value[i].list_id + ')'})
						.update('[{isys type="lang" ident="LC__CMDB__CATS__NET_IP_ADDRESSES__DISCONNECT"}]');

					// Start preparing the single table-rows.
					tr = new Element('tr', {className: class_name + ((cnt%2) ? ' even' : ' odd')}).insert(
							new Element('td').update(e.key)
						).insert(
							new Element('td').update((e.value[i].hostname || '') + (e.value[i].domain ? ' (' + e.value[i].domain + ')' : ''))
						).insert(
							new Element('td').update(new Element('a', {href: '?[{$smarty.const.C__CMDB__GET__OBJECT}]=' + e.value[i].isys_obj__id}).update(e.value[i].isys_obj__title))
						).insert(
							new Element('td', {className:'action_column'}).update(action)
						);

					cnt++;

					ip_table_body.insert(tr);
				}
			}.bind(this));

			ip_table_body.insert(new Element('tr', {id: 'separator-line', className: 'used'}).insert(new Element('td', {colspan: 4})));

			// Next we will render the small IP-list of hosts with no addresses.
			non_addressed_hosts.each(function (e) {
				switch (e.value.assignment__id) {
					case '[{$smarty.const.C__CMDB__CATG__IP__DHCPV6}]':
						class_name = 'dhcp-address';
						break;

					case '[{$smarty.const.C__CMDB__CATG__IP__SLAAC_AND_DHCPV6}]':
						class_name = 'slaac-dhcp';
						break;

					case '[{$smarty.const.C__CMDB__CATG__IP__SLAAC}]':
						class_name = 'slaac';
						break;

					case '[{$smarty.const.C__CMDB__CATG__IP__DHCPV6_RESERVED}]':
						class_name = 'dhcp-reserved-address';
						break;

					case '[{$smarty.const.C__CMDB__CATG__IP__STATIC}]':
					default:
						class_name = 'static-address';
						break;
				}

				action = new Element('a', {href: '#', onClick: 'idoit.callbackManager.triggerCallback(\'iplist_disconnect\', ' + e.value.list_id + ')'})
					.update('[{isys type="lang" ident="LC__CMDB__CATS__NET_IP_ADDRESSES__DISCONNECT"}]');

				tr = new Element('tr', {className: class_name + ' used ' + ((i%2) ? 'even' : 'odd')}).insert(
						new Element('td').update('-')
					).insert(
						new Element('td').update((e.value.hostname || '') + (e.value.domain ? ' (' + e.value.domain + ')' : ''))
					).insert(
						new Element('td').update(new Element('a', {href: '?[{$smarty.const.C__CMDB__GET__OBJECT}]=' + e.value.isys_obj__id}).update(e.value.isys_obj__title))
					).insert(
						new Element('td', {className:'action_column'}).update(action)
					);

				i++;

				ip_table_body.insert(tr);
			});

			[{if !$has_edit_right}]
			$$('.action_column').each(function (ele){ele.remove();});
			[{/if}]
		};

		render_list();

		$('statistic-used-addresses').update(hosts.size());

		var legend_scroll_at = '[{$legend_scroller}]';

		// This little snippet will move the to right boxes, while scrolling.
		$('contentWrapper').on('scroll', function () {
			var top = this.scrollTop,
				scroll_at;

			if (legend_scroll_at != '') {
				scroll_at = parseInt(legend_scroll_at);
			} else {
				scroll_at = 114;
			}
			if (top > scroll_at) {
				$('table-scroller').setStyle({top: (top - scroll_at ) + 'px'});
			} else {
				$('table-scroller').setStyle({top: 0});
			}
		});

		// Method for disconnecting an host object.
		var disconnect = function (obj) {
			[{if $is_global_net}]
			alert('[{isys type="lang" ident="LC__CMDB__CATS__NET_IP_ADDRESSES__DISCONNECT_GLOBAL_NET" p_bHtmlEncode=0}]');
			[{else}]

			if (confirm('[{isys type="lang" ident="LC__CMDB__CATS__NET_IP_ADDRESSES__DISCONNECT_CONFIRMATION" p_bHtmlEncode=0}]')) {
				new Ajax.Call('?call=ip_addresses&method=dv6&ajax=1',
					{
						requestHeaders: {Accept: 'application/json'},
						method: 'post',
						parameters: {'[{$smarty.const.C__CMDB__GET__OBJECT}]': obj, '[{$smarty.const.C__CMDB__GET__OBJECT}]2': '[{$obj_id}]'},
						onSuccess: function (transport) {
							var json = transport.responseText.evalJSON();

							// We got our response - Now we display the new range!
							if (json.result == 'success') {
								// We fill our host-hash.
								hosts = $H(json.hosts);
								non_addressed_hosts = $H(json.not_addressed_hosts);
							}

							// And render the list again.
							render_list();
						}.bind(this)
					});
			}
			[{/if}]
		};

		// The IE7 has some problems with the table-width, so we fix that issue.
		if (Prototype.Browser.IE7) {
			$('ip-table').setStyle({width: '60%'});
		}

		// Adding the global callbacks.
		idoit.callbackManager.registerCallback('iplist_disconnect', disconnect);
	}());
</script>