<h2 class="p5 gradient text-shadow border-bottom">
	<a class="fr" href="?moduleID=[{$smarty.get.moduleID}]&handle=licence_overview">[{isys type="lang" ident="LC__UNIVERSAL__LICENE_OVERVIEW"}]</a>
	[{isys type="lang" ident="LC__UNIVERSAL__LICENE_INSTALLATION"}]
</h2>

[{if isset($errorcode) || isset($error)}]
	<div class="error p5 m10">
		<h4 class="mb10">[{isys type="lang" ident="LC__LICENCE__INSTALL_ERROR"}]</h4>

		<p><b>[{isys type="lang" ident="LC__UNIVERSAL__ERRORCODE"}]</b> : [{$errorcode}]</p>
		<p><b>[{isys type="lang" ident="LC__CMDB__LOGBOOK__DESCRIPTION"}]</b> : [{$error}]</p>
	</div>
[{/if}]

[{if isset($note)}]
	<div class="note p5 m10">[{$note}]</div>
[{/if}]


<p class="m10 p10 muted">
	[{isys type="lang" ident="LC__UNIVERSAL__LICENE_INSTALLATION_HELP_TEXT_1" values=array($tenant_database)}]
	<br />
	[{isys type="lang" ident="LC__UNIVERSAL__LICENE_INSTALLATION_HELP_TEXT_2" p_bHtmlEncode=0 values=array($config.www_dir, 'admin')}]
</p>

<table class="contentTable">
	<colgroup>
		<col width="100" />
	</colgroup>
	<tr>
		<td class="key"><label for="licence_file">[{isys type="lang" ident="LC__LICENCE__FILE"}]</label></td>
		<td class="value"><input type="file" name="licence_file" class="ml20" /></td>
	</tr>
</table>

<p class="m5 mt10">[{isys type="f_submit" name="licence_submit" p_strValue="LC__LICENCE__INSTALL" p_bEditMode=1}]</p>