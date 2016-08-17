[{assign var="resultField" value=$smarty.get.resultField|default:""}]

<div id="popup-browser-location">
	<h3 class="popup-header">
		<img class="fr mouse-pointer popup-closer" alt="x" src="[{$dir_images}]prototip/styles/default/close.png">
		<span>[{isys type="lang" ident="LC__POPUP__BROWSER__LOCATION_TITLE"}]</span>
	</h3>

	<!-- If object_id is -1, an error has occured or no object has been selected! //-->
	<input type="hidden" id="selFull" name="selFull" value="[{$selFull}]" />
	<input type="hidden" id="selID" name="selID" value="[{$selID}]" />

	<div class="popup-content p5">
		<div style="height: 270px; overflow:auto">
			<div id="g_browser" class="dtree"></div>
		</div>

		<p>[{isys type="lang" ident="LC__POPUP__BROWSER__SELECTED_OBJECT"}]: <span id="object_sel" class="bold">[{$selFull|default:$selNoSelection}]</span></p>
	</div>

	<div class="popup-footer">
		<button type="button" class="btn mr5" onclick="window.moveToParent('[{$return_view}]', '[{$return_hidden}]');">
			<img src="[{$dir_images}]icons/silk/tick.png" class="mr5" /><span>[{isys type="lang" ident="LC__CMDB__OBJECT_BROWSER__BUTTON_SAVE"}]</span>
		</button>
		<button type="button" class="btn popup-closer">
			<img src="[{$dir_images}]icons/silk/cross.png" class="mr5" /><span>[{isys type="lang" ident="LC__UNIVERSAL__BUTTON_CANCEL"}]</span>
		</button>
	</div>
</div>

<script type="text/javascript" src="src/tools/js/etree.js"></script>
<script type="text/javascript">
	window.moveToParent = function (p_return_view, p_return_hidden) {
		if ($(p_return_view)) {
			$(p_return_view).setValue($('selFull').getValue());
		}

		if ($(p_return_hidden)) {
			$(p_return_hidden).setValue($('selID').getValue());
		}

		[{$callback_accept}]

		popup_close();
	};

	window.select = function (objId, objType, objName, objTypeName, e) {
		$$('a.nodeSel').each(function (iterator) {
			$(iterator).removeClassName('nodeSel').addClassName('node');
		});

		$(e).addClassName('nodeSel');
		$('object_sel').update(objName + ' (' + objTypeName + ')');
		$('selFull').setValue(objName + ' (' + objTypeName + ')');
		$('selID').setValue(objId);
	};

	[{$browser}]

	(function () {
		'use strict';

		var $popup = $('popup-browser-location');

		$popup.select('.popup-closer').invoke('on', 'click', function () {
			popup_close();
		});
	})();
</script>