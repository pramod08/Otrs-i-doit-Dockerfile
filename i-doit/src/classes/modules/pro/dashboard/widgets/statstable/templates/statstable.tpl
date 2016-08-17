<h3 class="gradient p5 text-shadow border-bottom border-ccc">[{isys type="lang" ident="LC__WIDGET__STATSTABLE"}]</h3>

<div class="m5">
	<table width="100%" class="stats-table border border-ccc" cellspacing="0" cellpadding="0">
		<colgroup>
			<col width="200" />
		</colgroup>
		[{foreach from=$stats key=label item=row}]
		<tr class="[{cycle values="CMDBListElementsOdd,CMDBListElementsEven"}]">
			<td><span class="bold mr10">[{isys type="lang" ident=$label}]</span></td>
			<td>[{$row}]</td>
		</tr>
		[{/foreach}]
	</table>
</div>