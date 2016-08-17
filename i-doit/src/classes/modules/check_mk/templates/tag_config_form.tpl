<table class="contentTable">
	<tr>
		<td class="key vat">[{isys type="f_label" name="C__CHECK_MK__TAGS__UNIQUE_NAME" ident="LC__MODULE__CHECK_MK__STATIC_TAGS__UNIQUE_NAME"}]</td>
		<td class="value">[{isys type="f_text" name="C__CHECK_MK__TAGS__UNIQUE_NAME"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" name="C__CHECK_MK__TAGS__DISPLAY_NAME" ident="LC__MODULE__CHECK_MK__STATIC_TAGS__DISPLAY_NAME"}]</td>
		<td class="value">[{isys type="f_text" name="C__CHECK_MK__TAGS__DISPLAY_NAME"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" name="C__CHECK_MK__TAGS__TAG_GROUP" ident="LC__MODULE__CHECK_MK__STATIC_TAGS__TAG_GROUP"}]</td>
		<td class="value">[{isys type="f_popup" p_strPopupType="dialog_plus" name="C__CHECK_MK__TAGS__TAG_GROUP"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" name="C__CHECK_MK__TAGS__EXPORTABLE" ident="LC__MODULE__CHECK_MK__STATIC_TAGS__EXPORTABLE"}]</td>
		<td class="value">[{isys type="f_dialog" name="C__CHECK_MK__TAGS__EXPORTABLE" p_bDbFieldNN=true}]</td>
	</tr>
	<tr>
		<td colspan="2"><hr /></td>
	</tr>
	<tr>
		<td class="key vat">[{isys type="f_label" name="C__CHECK_MK__TAGS__DESCRIPTION" ident="LC__UNIVERSAL__DESCRIPTION"}]</td>
		<td>[{isys type="f_wysiwyg" name="C__CHECK_MK__TAGS__DESCRIPTION"}]</td>
	</tr>
</table>

<script type="text/javascript">
	(function () {
		'use strict';

		var $idField = $('isys_form').down('[name="id"]'),
				id = parseInt('[{$id}]');

		if ($idField && id > 0) {
			$idField.setValue(id);
		}
	})();
</script>