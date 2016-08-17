<div class="whitebg border-bottom">
	<h2 class="p5 gradient border-bottom">[{isys type="lang" ident="LC__CMDB__TREE__SYSTEM__SETTINGS__USER__PRESENTATION"}]</h2>

	<table class="contentTable">
		<tr>
			<td class="key">[{isys type="f_label" ident="LC__NOTIFICATIONS__NOTIFICATION_TEMPLATE_LOCALE" name="C__CATG__OVERVIEW__LANGUAGE"}]</td>
			<td class="value">[{isys type="f_dialog" name="C__CATG__OVERVIEW__LANGUAGE" p_bDbFieldNN="1"}]</td>
		</tr>
		<tr>
			<td class="key">[{isys type="f_label" ident="LC__CATG__OVERVIEW__DATE_FORMAT" name="C__CATG__OVERVIEW__DATE_FORMAT"}]</td>
			<td class="value">[{isys type="f_dialog" name="C__CATG__OVERVIEW__DATE_FORMAT" p_bDbFieldNN="1"}]</td>
		</tr>
		<tr>
			<td class="key">[{isys type="f_label" ident="LC__CATG__OVERVIEW__NUMERIC_FORMAT" name="C__CATG__OVERVIEW__NUMERIC_FORMAT"}]</td>
			<td class="value">[{isys type="f_dialog" name="C__CATG__OVERVIEW__NUMERIC_FORMAT" p_bDbFieldNN="1"}]</td>
		</tr>
		<tr>
			<td class="key">[{isys type="f_label" ident="LC__CMDB__SETTINGS__USER__DEFAULT_TREEVIEW" name="C__CATG__OVERVIEW__DEFAULT_TREEVIEW"}]</td>
			<td class="value">[{isys type="f_dialog" name="C__CATG__OVERVIEW__DEFAULT_TREEVIEW" p_bDbFieldNN="1"}]</td>
		</tr>
		<tr>
			<td class="key">[{isys type="f_label" ident="LC__CMDB__SETTINGS__USER__DEFAULT_TREETYPE" name="C__CATG__OVERVIEW__DEFAULT_TREETYPE"}]</td>
			<td class="value">[{isys type="f_dialog" name="C__CATG__OVERVIEW__DEFAULT_TREETYPE" p_bDbFieldNN="1"}]</td>
		</tr>
	</table>

	[{* @see  ID-1220
	[{if isset($g_current_theme)}]

		<h3 class="p5 gradient border-top border-bottom">[{isys type="lang" ident="LC__SETTINGS__THEME__TITLE"}]</h3>

		[{include file="content/bottom/content/module__settings__theme.tpl"}]

	[{/if}]
	*}]
</div>