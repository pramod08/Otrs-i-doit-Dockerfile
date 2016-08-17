<div id="popup-commentary">
	<h3 class="popup-header">
		<img class="fr mouse-pointer popup-closer" alt="x" src="[{$dir_images}]prototip/styles/default/close.png">
	    <span>[{isys type="lang" ident="LC__UNIVERSAL__TITLE_LOGBOOK"}] ([{isys type="lang" ident="LC__POPUP__COMMENTARY__TITLE"}])</span>
	</h3>

	<div class="popup-content p5">
	[{isys_group name="tom"}]
		[{isys_group name="content"}]
			[{if $error_msg != ""}]
				<p class="red">[{$error_msg}]</p>
			[{/if}]

			[{isys_group name="middle"}]
				<input name="object_id" type="hidden" value="[{$smarty.get.object_id}]" />
				<input name="objTypeID" type="hidden" value="[{$smarty.get.objTypeID}]" />

	            [{isys type="lang" ident="LC__POPUP__COMMENTARY__REASON"}]:
				[{isys type="f_popup" name="LogbookReason" p_strPopupType="dialog_plus" p_strTable="isys_logbook_reason" p_strStyle="width:425px;"}]

				[{isys type="f_textarea" name="commentary" id="f_commentary" p_onChange="$('LogbookCommentary').value = $('f_commentary').value;" p_nRows="5" p_strStyle="width:445px; resize:none; margin-top:10px;" p_bInfoIconSpacer=0}]
			[{/isys_group}]

			[{isys_group name="bottom"}]

			[{assign var="object_id" value=$smarty.get.object_id|default:"0"}]
			[{assign var="navmode_save" value=$smarty.const.C__NAVMODE__SAVE}]

			[{/isys_group}]
		[{/isys_group}]
	[{/isys_group}]
	</div>

	<div class="popup-footer">
		[{isys
			name="save"
			type="f_button"
			id="save_button"
			p_strAccessKey="s"
			icon="`$dir_images`icons/silk/tick.png"
			p_strValue="LC__UNIVERSAL__BUTTON_SAVE"}]
		[{isys
			name="C__UNIVERSAL__BUTTON_CANCEL"
			type="f_button"
			icon="`$dir_images`icons/silk/cross.png"
			p_strClass="popup-closer"
			p_strValue="LC__UNIVERSAL__BUTTON_CANCEL"}]
	</div>
</div>

<script type="text/javascript">
	(function () {
		'use strict';

		var $popup = $('popup-commentary'),
			$save_button = $('save_button');

		$popup.on('click', '.popup-closer', function () {
			popup_close($('popup_commentary'));
		});

		$save_button.on('click', function () {
			popup_close($('popup_commentary'));
			$('navMode').setValue('[{$navmode_save}]');
			save_via_ajax('ajaxReturnNote');
		});

		$save_button.focus();
	})();
</script>