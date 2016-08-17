<table class="contentTable">
	<tr>
		<td class="key">[{isys type="lang" ident="LC__LOGBOOK__ENTRIES_OLDER_THAN"}]</td>
		<td class="value">[{isys type="f_text" name="archiveInterval"}] [{isys type="lang" ident="LC__CMDB__UNIT_OF_TIME__DAY"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type="lang" ident="LC__UNIVERSAL__DESTINATION"}]</td>
		<td class="value">[{isys type="f_dialog" name="archiveDest" p_bDbFieldNN="1"}]</td>
	</tr>
	<tr class="remoteDestination">
		<td colspan="2">
			<hr class="mt5 mb5" />
		</td>
	</tr>
	<tr class="remoteDestination">
		<td class="key">IP</td>
		<td class="value">[{isys type="f_text" name="archiveHost"}]</td>
	</tr>
	<tr class="remoteDestination">
		<td class="key">Port</td>
		<td class="value">[{isys type="f_text" name="archivePort"}]</td>
	</tr>
	<tr class="remoteDestination">
		<td class="key">[{isys type="lang" ident="LC__MODULE__NAGIOS__NDODB_SCHEMA"}]</td>
		<td class="value">[{isys type="f_text" name="archiveDB"}]</td>
	</tr>
	<tr class="remoteDestination">
		<td class="key">[{isys type="lang" ident="LC__LOGIN__USERNAME"}]</td>
		<td class="value">[{isys type="f_text" name="archiveUser"}]</td>
	</tr>
	<tr class="remoteDestination">
		<td class="key">[{isys type="lang" ident="LC__LOGIN__PASSWORD"}]</td>
		<td class="value">[{isys type="f_text" p_bPassword="true" name="archivePass"}]</td>
	</tr>
	[{if $archiveError}]
	<tr>
		<td class="key"></td>
		<td class="value"><p class="error p5 ml20 mr20">[{$archiveError}]</p></td>
	</tr>
	[{/if}]
</table>

<script type="text/javascript">
	(function(){
		var $archiveDest = $('archiveDest'),
			$rows = $('scroller').select('.remoteDestination');

		if ($archiveDest) {
			$archiveDest.on('change', function () {
				toggle_remote($archiveDest.getValue() == '1');
			});

			$archiveDest.simulate('change');
		} else {
			[{if $archiveDest == '0'}]
			toggle_remote(false);
			[{/if}]
		}

		function toggle_remote(show) {
			$rows.invoke(show ? 'show' : 'hide');
		}
	})();
</script>