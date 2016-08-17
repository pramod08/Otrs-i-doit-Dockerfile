[{isys_group name="tom.popup.visualization"}]
<div id="visualization-popup">
	<h3 class="p10 border-bottom gradient">
		<img class="fr mouse-pointer popup-closer" alt="x" src="[{$dir_images}]prototip/styles/default/close.png">

		<span>[{isys type="lang" ident="LC__VISUALIZATION_EXPORT__POPUP_TITLE"}]</span>
	</h3>

	<div id="visualization-popup-content" class="p5">
		<h4 class="border gradient p5">[{isys type="lang" ident="LC__VISUALIZATION_EXPORT__TYPE"}]</h4>

		<label class="mt5 ml5">
			<input type="radio" name="visualization-popup-export-type" class="mr5" value="svg" checked="checked" />
			<span>[{isys type="lang" ident="LC__VISUALIZATION_EXPORT__TYPE__SVG"}]</span>
		</label>

		<label class="mb5 ml5">
			<input type="radio" name="visualization-popup-export-type" class="mr5" value="graphml" />
			<span>[{isys type="lang" ident="LC__VISUALIZATION_EXPORT__TYPE__GRAPHML"}]</span>
		</label>

		<!-- For later use :)
		<h4 class="border gradient p5">[{isys type="lang" ident="LC__VISUALIZATION_EXPORT__OPTIONS"}]</h4>

		<table class="contentTable">
			<tr>
				<td class="key">key</td>
				<td class="value">value</td>
			</tr>
		</table>
		-->
	</div>

	<div id="visualization-popup-footer" class="border-top">
		<button type="button" class="btn m5" id="visualization-popup-save">
			<img src="[{$dir_images}]icons/silk/disk.png" class="mr5" />
			<span>[{isys type="lang" ident="LC__VISUALIZATION_EXPORT"}]</span>
		</button>

		<button type="button" class="btn m5 popup-closer" id="visualization-popup-cancel">
			<img src="[{$dir_images}]icons/silk/cross.png" class="mr5" />
			<span>[{isys type="lang" ident="LC__UNIVERSAL__BUTTON_CANCEL_CLOSE"}]</span>
		</button>
	</div>
</div>

<script type="text/javascript">
	(function () {
		'use strict';

		var $popup = $('visualization-popup'),
			$content = $('visualization-popup-content'),
			$footer = $('visualization-popup-footer'),
			$accept_button = $('visualization-popup-save');

		$popup.select('.popup-closer').invoke('on', 'click', function () {
			popup_close();
		});

		// We need this snippet to size the content area correctly, so we don't scroll the header and footer as well. Also the "undeletable" profiles get disabled.
		$content.setStyle({height: ($popup.getHeight() - ($popup.down('h3').getHeight() + $footer.getHeight())) + 'px'});

		// Unsupported browsers (=IE) shall not be able to export as SVG. The user agent match is necessary for IE11.
		if (Prototype.Browser.IE || !!navigator.userAgent.match(/Trident.*rv[ :]*11\./)) {
			$popup.down('input[value="svg"]').disable();
			$popup.down('input[value="graphml"]').setValue(1);
		}

		$accept_button.on('click', function () {
			var export_type = $content.down('[name="visualization-popup-export-type"]:checked').getValue();

			switch (export_type) {
				case 'svg':

					var $svg = $('C_VISUALIZATION_CANVAS').down('svg'),
						content;

					if ($svg) {
						$svg.exportToString({callback: function (data) {
							if (data) {
								content = 'data:image/svg+xml,' + encodeURIComponent('<?xml version="1.0" encoding="UTF-8" standalone="no"?><!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.1//EN" "http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd">' + data);

								window.open(content, '_blank');
							} else {
								idoit.Notify.error('Error while exporting data. Your browser may be incompatible.');
							}
						}});
					} else {
						idoit.Notify.error('Could not save: Visualization not loaded.');
					}

					break;
				case 'graphml':
					// Trigger the download.
					document.location.href = '[{$export_url}]' +
						'&object=' + $F('C_VISUALIZATION_OBJ_SELECTION__HIDDEN') +
						'&profile=' + $F('C_VISUALIZATION_PROFILE') +
						'&service-filter=' + $F('C_VISUALIZATION_SERVICE_FILTER');
					break;
			}
		});
	})();
</script>
<style>
	#visualization-popup {
		box-sizing: border-box;
		position: relative;
		height: 100%;
	}

	#visualization-popup #visualization-popup-content {
		overflow-y: auto;
	}

	#visualization-popup #visualization-popup-content label {
		display: block;
	}

	#visualization-popup #visualization-popup-content table td.key {
		width: 150px;
	}

	#visualization-popup #visualization-popup-footer {
		position: absolute;
		bottom: 0;
		width: 100%;
		background: #eee;
	}
</style>
[{/isys_group}]