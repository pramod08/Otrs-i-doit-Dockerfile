<input type="hidden" name="id" value="[{$nID}]" />
 
<table class="contentTable">
	<tr>
		<td class="key">[{isys type="lang" ident="LC_WORKFLOW__ACTIVE"}]: </td>
		<td class="value">[{isys type="f_dialog" name="C__MODULE__REQUEST_TRACKER_CONFIG__DB_ACTIVE" p_bDbFieldNN="1" tab="10"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type="lang" ident="IP"}]:</td>
		<td class="value">[{isys type="f_text" name="C__MODULE__REQUEST_TRACKER_CONFIG__IP" tab="20"}]</td>
	</tr>
	
	<tr>
		<td class="key">[{isys type="lang" ident="Port"}]:</td>
		<td class="value">[{isys type="f_text" name="C__MODULE__REQUEST_TRACKER_CONFIG__PORT" tab="30"}]</td>
	</tr>
	
	<tr>
		<td class="key">[{isys type="lang" ident="LC__MODULE__REQUEST_TRACKER_CONFIG__SCHEMA"}]:</td>
		<td class="value">[{isys type="f_text" name="C__MODULE__REQUEST_TRACKER_CONFIG__SCHEMA" tab="40"}]</td>
	</tr>
[{*<tr>
		<td class="key">DB Prefix:</td>
		<td class="value">[{isys type="f_text" name="C__MODULE__REQUEST_TRACKER_CONFIG__PREFIX" tab="40"}]</td>
	</tr>*}]
	
	<tr>
		<td class="key">[{isys type="lang" ident="LC__LOGIN__USERNAME"}]:</td>
		<td class="value">[{isys type="f_text" name="C__MODULE__REQUEST_TRACKER_CONFIG__USER" tab="50"}]</td>
	</tr>
	
	<tr>
		<td class="key">[{isys type="lang" ident="LC__LOGIN__PASSWORD"}]:</td>
		<td class="value">[{isys type="f_text" p_bPassword="true" name="C__MODULE__REQUEST_TRACKER_CONFIG__PASS" tab="60"}]</td>
	</tr>	
	
	<tr>
		<td colspan="2">
			<hr class="partingLine mt5 mb5" />
		</td>
	</tr>
	
	<tr>
		<td class="key">RT [{isys type="lang" ident="LC__MODULE__REQUEST_TRACKER_CONFIG__LINK"}]:</td>
		<td class="value">[{isys type="f_text" name="C__MODULE__REQUEST_TRACKER_CONFIG__LINK" tab="50"}]</td>
	</tr>
</table>
<h3>[{isys type="f_data" name="C__MODULE__REQUEST_TRACKER_CONFIG__ERROR"}]</h3>