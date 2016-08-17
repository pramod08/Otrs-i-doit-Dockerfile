[{if $inherited_services}]
<table cellspacing="0" class="mainTable mt10 border-top" id="mainTableAddition">
	<colgroup>
		<col />
		<col />
		<col />
		<col />
	</colgroup>
	<tbody>
	<tr>
		<th colspan="4">[{isys type="lang" ident="LC__CATG__CMK_SERVICE__INHERITED_SERVICES"}]</th>
	</tr>
	[{foreach $inherited_services as $inheritance}]
	<tr class="listRow">
		<td><input type="checkbox" disabled="disabled" class="checkbox"></td>
		<td>[{isys_tenantsettings::get('gui.empty_value', '-')}]</td>
		<td>[{$inheritance.application}]</td>
		<td>[{$inheritance.service}]</td>
	</tr>
	[{/foreach}]
	</tbody>
</table>

<script type="text/javascript">
	var table = $('mainTable').addClassName('border-bottom'),
		table_header = table.select('th'),
		sub_table = $('mainTableAddition'),
		sub_table_cols = sub_table.select('col'),
		sub_table_rows = sub_table.select('tr.listRow'),
		sub_table_color = (table.select('tr.listRow').length % 2),
		i;

	// This little script will set the "mainTableAddition" columns to the same width as the ones in "mainTable".
	for (i in table_header) {
		if (table_header.hasOwnProperty(i)) {
			sub_table_cols[i].setStyle({width:table_header[i].getWidth() + 'px'});
		}
	}

	// This little script will continue the "even/odd" colors for the sub-table.
	for (i in sub_table_rows) {
		if (sub_table_rows.hasOwnProperty(i)) {
			sub_table_rows[i].addClassName(((parseInt(i) + sub_table_color) % 2) ? 'CMDBListElementsEven' : 'CMDBListElementsOdd')
		}
	}
</script>
[{/if}]