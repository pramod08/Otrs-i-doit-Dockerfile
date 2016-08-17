<table class="contentTable">
	<tr>
		<td class="key">
			[{isys name="C__CATG__CMK_SERVICE__SOFTWARE_ASSIGNMENT" type="f_label" ident="LC__CATG__CMK_SERVICE__SOFTWARE_ASSIGNMENT"}]
		</td>
		<td class="value">
			[{isys name="C__CATG__CMK_SERVICE__SOFTWARE_ASSIGNMENT" type="f_dialog"}]
		</td>
	</tr>
	<tr>
		<td class="key">
			[{isys name="C__CATG__CMK_SERVICE__CHECK_MK_SERVICES" type="f_label" ident="LC__CATG__CMK_SERVICE__CHECK_MK_SERVICES"}]
		</td>
		<td class="value">
			[{isys name="C__CATG__CMK_SERVICE__CHECK_MK_SERVICES" type="f_dialog" p_bDbFieldNN=true}]
		</td>
	</tr>
	<tr>
		<td colspan="2">
			<hr class="mb5 mt5" />
		</td>
	</tr>
	[{foreach $services as $service}]
	<tr>
		<td class="key">
			[{$service[0]}]
		</td>
		<td class="value">
			<span class="ml20 [{$states[$service[1]].color}]">[{$service[3]}]</span>
		</td>
	</tr>
	[{foreachelse}]
	<tr>
		<td colspan="2">
			<div class="ml5 mr5 p5 info">
				<img src="[{$dir_images}]icons/silk/information.png" class="vam mr5" /><span class="vam">[{isys type="lang" ident="LC__CATG__CMK_SERVICE__NO_SERVICES"}]</span>
			</div>
		</td>
	</tr>
	[{/foreach}]
</table>