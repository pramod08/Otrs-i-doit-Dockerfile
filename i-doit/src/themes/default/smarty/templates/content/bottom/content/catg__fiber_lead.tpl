[{*
Smarty template for global category for fiber/lead
@ author: Benjamin Heisig <bheisig@synetics.de>
@ copyright: synetics GmbH
@ license: <http://www.i-doit.com/license>
*}]

<table class="contentTable">
	<tr>
		<td class="key">[{isys type="f_label" name="C__CATG__FIBER_LEAD__LABEL" ident="LC__CATG__FIBER_LEAD__LABEL"}]</td>
		<td class="value">[{isys type="f_text" name="C__CATG__FIBER_LEAD__LABEL"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" name="C__CATG__FIBER_LEAD__CATEGORY" ident="LC__CATG__FIBER_LEAD__CATEGORY"}]</td>
		<td class="value">[{isys type="f_popup" p_strPopupType="dialog_plus" name="C__CATG__FIBER_LEAD__CATEGORY"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" name="C__CATG__FIBER_LEAD__COLOR" ident="LC__CATG__FIBER_LEAD__COLOR"}]</td>
		<td class="value">[{isys type="f_popup" p_strPopupType="dialog_plus" name="C__CATG__FIBER_LEAD__COLOR"}]</td>
	</tr>
</table>