<input name="notFirstTime" type="hidden" value="1" />

[{if $ldap_dn}]

[{if empty($ldap_server)}]
<script type="text/javascript">show_content_overlay();</script>
[{/if}]

<table class="contentTable mb5">
	<tr>
		<td class="key bold">LDAP-Info:</td>
		<td class="value"></td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" ident="Server" name="C__CONTACT__PERSON_LDAP_SERVER"}]</td>
		<td class="value">[{isys type="f_text" p_bDisabled=1 p_strValue=$ldap_server name="C__CONTACT__PERSON_LDAP_SERVER"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" ident="DN" name="C__CONTACT__PERSON_LDAP_DN"}]</td>
		<td class="value">[{isys type="f_text" p_bDisabled=1  p_strValue=$ldap_dn name="C__CONTACT__PERSON_LDAP_DN"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" ident="CN" name="C__CONTACT__PERSON_LDAP_CN"}]</td>
		<td class="value">[{isys type="f_text" p_bDisabled=1  p_strValue=$ldap_cn name="C__CONTACT__PERSON_LDAP_CN"}]</td>
	</tr>
</table>
[{/if}]

<table class="contentTable">	
	<tr>
	  <td class="key">[{isys type="lang" ident="LC__CONTACT__PERSON_FIRST_NAME"}]: </td>
	  <td class="value">[{isys type="f_text" name="C__CONTACT__PERSON_FIRST_NAME" id="C__CONTACT__PERSON_FIRST_NAME" tab="1"}]</td>
  </tr>
	<tr>
      <td class="key">[{isys type="lang" ident="LC__CONTACT__PERSON_LAST_NAME"}]: </td>
      <td class="value">[{isys type="f_text" name="C__CONTACT__PERSON_LAST_NAME" tab="1"}]</td>
  </tr>
	<tr>
	  <td class="key">&nbsp;</td>
	  <td class="value">&nbsp;</td>
  </tr>
	<tr>
	  <td class="key">[{isys type="lang" ident="LC__CONTACT__PERSON_MAIL_ADDRESS"}]: </td>
	  <td class="value">[{isys type="f_text" name="C__CONTACT__PERSON_MAIL_ADDRESS" tab="2"}]</td>
  </tr>
	<tr>
	  <td class="key">[{isys type="lang" ident="LC__CONTACT__PERSON_TELEPHONE_COMPANY"}]: </td>
	  <td class="value">[{isys type="f_text" name="C__CONTACT__PERSON_PHONE_COMPANY" tab="3"}]</td>
  </tr>
	<tr>
      <td class="key">[{isys type="lang" ident="LC__CONTACT__PERSON_TELEPHONE_HOME"}]: </td>
      <td class="value">[{isys type="f_text" name="C__CONTACT__PERSON_PHONE_HOME" tab="4"}]</td>
  </tr>
	<tr>
      <td class="key">[{isys type="lang" ident="LC__CONTACT__PERSON_TELEPHONE_MOBILE"}]: </td>
      <td class="value">[{isys type="f_text" name="C__CONTACT__PERSON_PHONE_MOBILE" tab="5"}]</td>
  </tr>
	<tr>
	  <td class="key">[{isys type="lang" ident="LC__CONTACT__PERSON_FAX"}]: </td>
	  <td class="value">[{isys type="f_text" name="C__CONTACT__PERSON_FAX" tab="6"}]</td>
  </tr>
	<tr>
	  <td class="key">&nbsp;</td>
	  <td class="value">&nbsp;</td>
  </tr>
	<tr>
	  <td class="key">[{isys type="lang" ident="LC__CONTACT__PERSON_DEPARTMENT"}]: </td>
	  <td class="value">[{isys type="f_text" name="C__CONTACT__PERSON_DEPARTMENT" tab="7"}]</td>
  </tr>
  <tr>
	  <td class="key">[{isys type="lang" ident="LC__CONTACT__PERSON_ASSIGNED_ORGANISATION"}]: </td>
	  <td class="value">
	  	[{isys
	  		type="f_dialog"
	  		name="C__CONTACT__PERSON_ASSIGNED_ORGANISATION"
	  		p_strTable="isys_organisation_intern_iop"
	  		p_bDisabled="0"
	  		tab="8"}]
	  </td>
  </tr>

  [{if $username_edit}]
	<tr>
	  <td class="key">&nbsp;</td>
	  <td class="value">&nbsp;</td>
  </tr>
	<tr>
	  <td class="key">[{isys type="lang" ident="LC__CONTACT__PERSON_USER_NAME"}]: </td>
	  <td class="value">[{isys type="f_text" name="C__CONTACT__PERSON_USER_NAME" tab="9"}]</td>
  </tr>
	<tr>
	  <td class="key">[{isys type="lang" ident="LC__CONTACT__PERSON_PASSWORD"}] <sup>*</sup>: </td>
	  <td class="value">[{isys type="f_text" p_bPassword="1" name="C__CONTACT__PERSON_PASSWORD" tab="10"}]</td>
  </tr>
  <tr>
	  <td class="key">[{isys type="lang" ident="LC__CONTACT__PERSON_PASSWORD"}] <sup>*</sup>: </td>
	  <td class="value">[{isys type="f_text" p_bPassword="1" name="C__CONTACT__PERSON_PASSWORD_SECOND" tab="10"}]</td>
  </tr>
  <tr>
  <td colspan="2">
  	<div class="sup_infotext"><sup>*</sup>[{isys type="lang" ident="LC__CONTACT__PERSON_PASSWORD_INFO"}]</div>
  </td>
  </tr>
  [{/if}]
</table>

<script type="text/javascript">
if ($('C__CONTACT__PERSON_FIRST_NAME')) $('C__CONTACT__PERSON_FIRST_NAME').focus();
</script> 