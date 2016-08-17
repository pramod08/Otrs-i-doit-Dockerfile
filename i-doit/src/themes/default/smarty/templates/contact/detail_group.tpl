<table class="contentTable">
	<tr>
	  <td class="key">[{isys type="lang" ident="LC__CONTACT__GROUP_TITLE"}]: </td>
	  <td class="value">[{isys type="f_text" name="C__CONTACT__GROUP_TITLE" id="C__CONTACT__GROUP_TITLE" tab="10"}]</td>
  </tr>
	<tr>
	  <td class="key">[{isys type="lang" ident="LC__CONTACT__GROUP_EMAIL_ADDRESS"}]: </td>
	  <td class="value">[{isys type="f_text" name="C__CONTACT__GROUP_EMAIL_ADDRESS" tab="20"}]</td>
  </tr>
 	<tr>
	  <td class="key">[{isys type="lang" ident="LC__CONTACT__GROUP_PHONE"}]: </td>
	  <td class="value">[{isys type="f_text" name="C__CONTACT__GROUP_PHONE" tab="30"}]</td>
  </tr>
  [{if $ldap}]
  <tr>
	  <td class="key">LDAP-[{isys type="lang" ident="LC__CMDB__OBJTYPE__GROUP"}] (Mapping): </td>
	  <td class="value">[{isys type="f_text" name="C__CONTACT__GROUP_LDAP" tab="30"}]</td>
  </tr>
  [{/if}]
</table>

<hr style="border:0;border-top:1px dashed #ccc" />

<div class="m15 p5 fr">
<a class="bold" href="javascript:" onclick="$('add_rights').appear();">[+] Rechte / Rollen für [{isys type="f_data" p_bInfoIconSpacer=0 name="C__CONTACT__GROUP_ROLE"}] hinzufügen</a>
</div>

<h4 class="p5">[{isys type="lang" ident="LC__UNIVERSAL__RIGHTS"}]:</h4>

<div id="add_rights" class="m10" [{if !$error}]style="display:none;"[{/if}]>
	<table width="40%" class="p5"">
		<tr>
			<td>[{isys type="lang" ident="LC__CMDB__CATS__WAN_ROLE"}]</td>
			<td>
				[{isys type="f_dialog" p_bInfoIconSpacer="0" status=0 p_bEditMode=1 p_strTable="isys_role" name="rights_role" p_bDbFieldNN=0 p_strSelectedID=$smarty.post.rights_role}]
			</td>
		</tr>
		<tr>
			<td>[{isys type="lang" ident="LC__UNIVERSAL__MODULE"}]</td>
			<td>
				[{isys type="f_dialog" p_bInfoIconSpacer="0" p_bEditMode=1 p_strTable="isys_module" name="rights_module" p_bDbFieldNN=0 p_strSelectedID=$smarty.post.rights_module}]
			</td>
		</tr>
		<tr>
			<td></td>
			<td>
				<input type="hidden" name="role_submit" id="role_submit" value="0" />
				<input type="submit" name="submit_group" onclick="$('role_submit').value='1';" value="[{isys type="lang" ident="LC__REPORT__FORM__UPDATE_REPORT"}]" />
			</td>
		</tr>
	</table>

	[{if $error}]
	<div class="exception p5">[{$error}]</div>
	[{/if}]
</div>

[{if is_array($rights)}]
<table class="contentInfoTable" cellspacing="0" width="100%" cellpadding="0">
	<thead>
		<tr>
			<td>[{isys type="lang" ident="LC__CMDB__CATS__WAN_ROLE"}]</td>
			<td>[{isys type="lang" ident="LC__UNIVERSAL__MODULE"}]</td>
			<td></td>
		</tr>
	</thead>
	<tbody>
		[{foreach from=$rights item=right}]
			<tr>
				<td>
					[{$right.isys_role__title}]
				</td>
				<td>
					<a href="?moduleID=[{$right.isys_module__id}]">
						[{isys type="lang" ident=$right.isys_module__title}]
					</a>
				</td>
				<td>
					<a href="[{$query_string}]&delete_role=1&role_id=[{$right.isys_role__id}]&mod_id=[{$right.isys_module__id}]">Löschen</a>
				</td>
			</tr>
		[{/foreach}]
	</tbody>
</table>
[{else}]
	<div class="p10">-</div>
[{/if}]

<script type="text/javascript">
if ($('C__CONTACT__GROUP_TITLE')) $('C__CONTACT__GROUP_TITLE').focus();
</script>