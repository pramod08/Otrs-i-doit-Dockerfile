[{if $ipv4 && !$ipv6}]
	[{include file="content/bottom/content/cats__net_ipv4_addresses.tpl"}]
[{elseif !$ipv4 && $ipv6}]
	[{include file="content/bottom/content/cats__net_ipv6_addresses.tpl"}]
[{else}]
	<p class="p5 error red">
		[{isys type="lang" ident="LC__CMDB__CATS__NET_IP_ADDRESSES__NO_NETWORK_DEFINED"}]
	</p>
[{/if}]