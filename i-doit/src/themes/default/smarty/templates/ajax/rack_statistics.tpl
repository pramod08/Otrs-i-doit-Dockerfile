<table class="contentTable fl m5">
    <tr>
        <td class="key"></td>
        <td>[{isys type="lang" ident="LC__CMDB__CATG__LOCATION_FRONT"}]</td>
        <td>[{isys type="lang" ident="LC__CMDB__CATG__LOCATION_BACK"}]</td>
        <td>[{isys type="lang" ident="LC__CMDB__CATG__LOCATION_COMBINED"}]</td>
    </tr>
    <tr>
        <td class="key"><span class="mr10">[{isys type="lang" ident="LC__CMDB__CATS__RACK__STATS__FREE_SLOTS"}]</span></td>
        <td class="value">[{$stats.free_h_slots_f}]</td>
        <td class="value">[{$stats.free_h_slots_r}]</td>
        <td class="value">
            [{$stats.free_h_slots_comb}] ([{$stats.free_h_slots_percent}] %)
            <div class="bar" title="[{$stats.free_h_slots_percent}] %">
                <div style="width:[{$stats.free_h_slots_percent}]%; background:[{$stats.free_h_slots_percent_color}];"></div>
            </div>
        </td>
    </tr>
    <tr>
        <td class="key"><span class="mr10">[{isys type="lang" ident="LC__CMDB__CATS__RACK__STATS__USED_SLOTS"}]</span></td>
        <td class="value">[{$stats.used_h_slots_f}]</td>
        <td class="value">[{$stats.used_h_slots_r}]</td>
        <td class="value">[{$stats.used_h_slots_comb}]</td>
    </tr>
    <tr>
        <td class="key"><span class="mr10">[{isys type="lang" ident="LC__CMDB__CATS__RACK__STATS__FREE_V_SLOTS"}]</span></td>
        <td class="value">[{$stats.free_v_slots_f}]</td>
        <td class="value">[{$stats.free_v_slots_r}]</td>
        <td class="value">
            [{$stats.free_v_slots_comb}] ([{$stats.free_v_slots_percent}] %)
            <div class="bar" title="[{$stats.free_v_slots_percent}] %">
                <div style="width:[{$stats.free_v_slots_percent}]%; background:[{$stats.free_v_slots_percent_color}];"></div>
            </div>
        </td>
    </tr>
    <tr>
        <td class="key"><span class="mr10">[{isys type="lang" ident="LC__CMDB__CATS__RACK__STATS__USED_V_SLOTS"}]</span></td>
        <td class="value">[{$stats.used_v_slots_f}]</td>
        <td class="value">[{$stats.used_v_slots_r}]</td>
        <td class="value">[{$stats.used_v_slots_comb}]</td>
    </tr>
</table>

<table class="contentTable fl m5">
    <tr>
        <td class="key"></td>
        <td class="pl15">[{isys type="lang" ident="LC__CATG__CONNECTOR__CONNECTION_TYPE"}]</td>
        <td>[{isys type="lang" ident="LC__UNIVERSAL__FREE"}]</td>
        <td>[{isys type="lang" ident="LC__UNIVERSAL__USED"}]</td>
    </tr>
    [{if count($stats.pdu_connectors.in) > 0}]
    <tr>
        <td class="key"><span class="mr10">[{isys type="lang" ident="LC__CMDB__CATS__RACK__STATS__PDU_CONNECTOR"}] ([{isys type="lang" ident="LC__CATG__CONNECTOR__INPUT"}])</span></td>
        <td class="value">[{foreach from=$stats.pdu_connectors.in key=type item=usage}][{$type}]<br />[{/foreach}]</td>
        <td class="value">[{foreach from=$stats.pdu_connectors.in key=type item=usage}][{$usage.free}]<br />[{/foreach}]</td>
        <td class="value">[{foreach from=$stats.pdu_connectors.in key=type item=usage}][{$usage.used}]<br />[{/foreach}]</td>
    </tr>
    [{/if}]
    [{if count($stats.pdu_connectors.out) > 0}]
    <tr>
        <td class="key"><span class="mr10">[{isys type="lang" ident="LC__CMDB__CATS__RACK__STATS__PDU_CONNECTOR"}] ([{isys type="lang" ident="LC__CATG__CONNECTOR__OUTPUT"}])</span></td>
        <td class="value">[{foreach from=$stats.pdu_connectors.out key=type item=usage}][{$type}]<br />[{/foreach}]</td>
        <td class="value">[{foreach from=$stats.pdu_connectors.out key=type item=usage}][{$usage.free}]<br />[{/foreach}]</td>
        <td class="value">[{foreach from=$stats.pdu_connectors.out key=type item=usage}][{$usage.used}]<br />[{/foreach}]</td>
    </tr>
    [{/if}]
    [{if count($stats.switch_ports) > 0}]
    <tr>
        <td class="key"><span class="mr10">[{isys type="lang" ident="LC__CMDB__CATS__RACK__STATS__SWITCH_PORTS"}]</span></td>
        <td class="value">[{foreach from=$stats.switch_ports key=type item=usage}][{$type}]<br />[{/foreach}]</td>
        <td class="value">[{foreach from=$stats.switch_ports key=type item=usage}][{$usage.free}]<br />[{/foreach}]</td>
        <td class="value">[{foreach from=$stats.switch_ports key=type item=usage}][{$usage.used}]<br />[{/foreach}]</td>
    </tr>
    [{/if}]
    [{if count($stats.fc_switch_ports) > 0}]
    <tr>
        <td class="key"><span class="mr10">[{isys type="lang" ident="LC__CMDB__CATS__RACK__STATS__FC_SWITCH_PORTS"}]</span></td>
        <td class="value">[{foreach from=$stats.fc_switch_ports key=type item=usage}][{$type}]<br />[{/foreach}]</td>
        <td class="value">[{foreach from=$stats.fc_switch_ports key=type item=usage}][{$usage.free}]<br />[{/foreach}]</td>
        <td class="value">[{foreach from=$stats.fc_switch_ports key=type item=usage}][{$usage.used}]<br />[{/foreach}]</td>
    </tr>
    [{/if}]
    [{if count($stats.patch_connectors.in) > 0}]
    <tr>
        <td class="key"><span class="mr10">[{isys type="lang" ident="LC__CMDB__CATS__RACK__STATS__PATCH_PORTS"}] ([{isys type="lang" ident="LC__CMDB__CATG__CONNECTOR__FRONT"}])</span></td>
        <td class="value">[{foreach from=$stats.patch_connectors.in key=type item=usage}][{$type}]<br />[{/foreach}]</td>
        <td class="value">[{foreach from=$stats.patch_connectors.in key=type item=usage}][{$usage.free}]<br />[{/foreach}]</td>
        <td class="value">[{foreach from=$stats.patch_connectors.in key=type item=usage}][{$usage.used}]<br />[{/foreach}]</td>
    </tr>
    [{/if}]
    [{if count($stats.patch_connectors.out) > 0}]
    <tr>
        <td class="key"><span class="mr10">[{isys type="lang" ident="LC__CMDB__CATS__RACK__STATS__PATCH_PORTS"}] ([{isys type="lang" ident="LC__CMDB__CATG__CONNECTOR__BACK"}])</span></td>
        <td class="value">[{foreach from=$stats.patch_connectors.out key=type item=usage}][{$type}]<br />[{/foreach}]</td>
        <td class="value">[{foreach from=$stats.patch_connectors.out key=type item=usage}][{$usage.free}]<br />[{/foreach}]</td>
        <td class="value">[{foreach from=$stats.patch_connectors.out key=type item=usage}][{$usage.used}]<br />[{/foreach}]</td>
    </tr>
    [{/if}]
</table>

<table class="contentTable fl m5">
	<tr>
		<td class="key"><span class="mr10">[{isys type="lang" ident="LC__CMDB__CATS__RACK__STATS__CONSUMPTION_OF_WATT"}]</span></td>
		<td class="value">[{$stats.consumption_of_watt}]</td>
	</tr>
	<tr>
		<td class="key"><span class="mr10">[{isys type="lang" ident="LC__CMDB__CATS__RACK__STATS__CONSUMPTION_OF_BTU"}]</span></td>
		<td class="value">[{$stats.consumption_of_btu}]</td>
	</tr>
</table>

<br class="clear" />