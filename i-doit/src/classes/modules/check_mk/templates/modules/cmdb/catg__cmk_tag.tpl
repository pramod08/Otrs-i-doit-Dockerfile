<table class="contentTable" id="C__CATG__CMK_TAG">
	<tr>
		<td class="key" style="vertical-align: top;">[{isys type='f_label' name='C__CATG__CMK_TAG__TAGS' ident='LC__CATG__CMK_TAG__TAGS'}]</td>
		<td class="value">[{isys type="f_dialog_list" name="C__CATG__CMK_TAG__TAGS" p_bDialogMode=false p_strClass="input" emptyMessage="LC__MODULE__CHECK_MK__TAGS__NO_TAGS"}]</td>
	</tr>
	<tr>
		<td class="key" style="vertical-align: top;">[{isys type='lang' ident='LC__CATG__CMK_TAG__CMDB_TAGS'}]</td>
		<td class="value">
			[{if $cmdb_tags}]
				[{$cmdb_tags}]
			[{else}]
				<p class="ml20">[{isys type="lang" ident="LC__CATG__CMK_TAG__NO_CMDB_TAGS"}]</p>
			[{/if}]
		</td>
	</tr>
	<tr>
		<td class="key" style="vertical-align: top;">[{isys type='lang' ident='LC__CATG__CMK_TAG__DYNAMIC_TAGS'}]</td>
		<td class="value">
			[{if $dynamic_tags}]
				[{$dynamic_tags}]
			[{else}]
				<p class="ml20">[{isys type="lang" ident="LC__CATG__CMK_TAG__NO_DYNAMIC_TAGS"}]</p>
			[{/if}]
		</td>
	</tr>
</table>

<style type="text/css">
	#C__CATG__CMK_TAG ul {
		padding: 0;
		list-style: none;
	}

	.chosen-container-multi .chosen-choices li {
		float: none;
	}

	.chosen-container-multi .chosen-choices li.search-choice {
		margin-right: 5px;
	}
</style>