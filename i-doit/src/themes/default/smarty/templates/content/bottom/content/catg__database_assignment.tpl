<table class="contentTable">
	<tr>
		<td class="key">[{isys type='f_label' name='C__CATG__DATABASE_ASSIGNMENT__RELATION_OBJECT__VIEW' ident="LC__CMDB__CATG__DATABASE_ASSIGNMENT__INSTALLED_ON"}]</td>
		<td class="value">
			[{isys
				title="LC__POPUP__BROWSER__RELATION_BROWSER"
				name="C__CATG__DATABASE_ASSIGNMENT__RELATION_OBJECT"
				type="f_popup"
				p_strPopupType="browser_object_ng"
				categoryFilter="isys_cmdb_dao_category_g_database_assignment::object_browser"
				p_bDisableDetach=true
				p_bReadonly=true}]
		</td>
	</tr>
	<tr>
		<td class="key">[{isys type='f_label' name='C__CATG__DATABASE_ASSIGNMENT__TARGET_SCHEMA__VIEW' ident="LC__CMDB__CATS__DATABASE_GATEWAY__TARGET_SCHEMA"}]</td>
		<td class="value">
			[{isys
				title="LC__BROWSER__TITLE__DATABASE_SCHEMATA"
				name="C__CATG__DATABASE_ASSIGNMENT__TARGET_SCHEMA"
				type="f_popup"
				p_strPopupType="browser_object_ng"
				typeFilter="C__OBJTYPE__DATABASE_SCHEMA"
				p_bDisableDetach=true}]
		</td>
	</tr>
</table>