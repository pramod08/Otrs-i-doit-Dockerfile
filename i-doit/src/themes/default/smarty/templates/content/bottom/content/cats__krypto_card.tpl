<table class="contentTable">
	<tr>
		<td class="key">[{isys type="f_label" ident="LC__CMDB__CATS__KRYPTO_CARD__CERTIFICATE_NUMBER" name="C__CATS__KRYPTO_CARD__CERTIFICATE_NUMBER"}]</td>
	  	<td class="value">[{isys type="f_text" p_strStyle="width:90px;" name="C__CATS__KRYPTO_CARD__CERTIFICATE_NUMBER"}]</td>
  	</tr>
	<tr>
		<td class="key">[{isys type="f_label" ident="LC__CMDB__CATS__KRYPTO_CARD__CERTGATE_CARD_NUMBER" name="C__CATS__KRYPTO_CARD__CERTGATE_CARD_NUMBER"}]</td>
	  	<td class="value">[{isys type="f_text" p_strStyle="width:90px;" name="C__CATS__KRYPTO_CARD__CERTGATE_CARD_NUMBER"}]</td>
  	</tr>
	<tr>
		<td class="key">[{isys type="f_label" ident="LC__CMDB__CATS__KRYPTO_CARD__CERTIFICATE_TITLE" name="C__CATS__KRYPTO_CARD__CERTIFICATE_TITLE"}]</td>
	  	<td class="value">[{isys type="f_text" p_strStyle="width:90px;" name="C__CATS__KRYPTO_CARD__CERTIFICATE_TITLE"}]</td>
  	</tr>
  	<tr>
		<td class="key">[{isys type="f_label" ident="LC__CMDB__CATS__KRYPTO_CARD__CERTIFICATE_PASSWORD" name="C__CATS__KRYPTO_CARD__CERTIFICATE_PASSWORD"}]</td>
	  	<td class="value">[{isys type="f_text" p_strStyle="width:90px;" name="C__CATS__KRYPTO_CARD__CERTIFICATE_PASSWORD"}]</td>
  	</tr>

  	<!-- DATES START -->
  	<tr>
		<td class="key">[{isys type="f_label" ident="LC__CMDB__CATS__KRYPTO_CARD__CERTIFICATE_PROCEDURE" name="C__CATS__KRYPTO_CARD__CERTIFICATE_PROCEDURE__VIEW"}]</td>
	  	<td class="value">
		  	[{isys type="f_popup" name="C__CATS__KRYPTO_CARD__CERTIFICATE_PROCEDURE" p_strPopupType="calendar"  p_calSelDate="" p_bTime="0"}]
	  	</td>
  	</tr>
  	<tr>
		<td class="key">[{isys type="f_label" ident="LC__CMDB__CATS__KRYPTO_CARD__DATE_OF_ISSUE" name="C__CATS__KRYPTO_CARD__DATE_OF_ISSUE__VIEW"}]</td>
	  	<td class="value">
		  	[{isys type="f_popup" name="C__CATS__KRYPTO_CARD__DATE_OF_ISSUE" p_strPopupType="calendar"  p_calSelDate="" p_bTime="0"}]
	  	</td>
  	</tr>
  	<!-- DATES END -->

  	<tr>
		<td class="key">[{isys type="f_label" ident="LC__CMDB__CATS__KRYPTO_CARD__IMEI_NUMBER" name="C__CATS__KRYPTO_CARD__IMEI_NUMBER"}]</td>
	  	<td class="value">[{isys type="f_text" p_strStyle="width:90px;" name="C__CATS__KRYPTO_CARD__IMEI_NUMBER"}]</td>
  	</tr>

	<tr>
		<td class="key">[{isys type="f_label" ident="LC__CMDB__CATS__KRYPTO_CARD__ASSIGNED_MOBILE_PHONE" name="C__CATS__KRYPTO_CARD__ASSIGNED_MOBILE_PHONE__VIEW"}]</td>
		<td class="value">
			[{isys
				name="C__CATS__KRYPTO_CARD__ASSIGNED_MOBILE_PHONE"
				type="f_popup"
				p_strPopupType="browser_object_ng"
				typeFilter="C__OBJTYPE__CELL_PHONE_CONTRACT"
				p_bReadonly="1"
				p_image="true"
				p_strFormSubmit="0"
				p_iSelectedTab="1"
				p_iEnabledPreselection="1"}]
		</td>
	</tr>

</table>