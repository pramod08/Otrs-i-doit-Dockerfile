<div id="C_VISUALIZATION_TOP" class="p5 bg-white border-bottom noprint">
	<div id="C_VISUALIZATION_TOP_OPTIONS">
		<div id="C_VISUALIZATION_TOP_OPTIONS_LABEL"><img src="[{$dir_images}]icons/silk/cog.png" class="mouse-pointer fr"></div>

		<button type="button" id="C_VISUALIZATION_FULLSCREEN" class="fr btn" title="[{isys type="lang" ident="LC__MODULE__CMDB__VISUALIZATION__FULLSCREEN"}]">
			<img src="[{$dir_images}]icons/silk/arrow_out.png" /><span class="ml5">[{isys type="lang" ident="LC__MODULE__CMDB__VISUALIZATION__FULLSCREEN"}]</span>
		</button>
		<button type="button" id="C_VISUALIZATION_REFRESH_BUTTON" class="fr btn mr5" title="[{isys type="lang" ident="LC__MODULE__CMDB__VISUALIZATION__REFRESH_VIEW"}]">
			<img src="[{$dir_images}]icons/silk/arrow_refresh.png" /><span class="ml5">[{isys type="lang" ident="LC__MODULE__CMDB__VISUALIZATION__REFRESH_VIEW"}]</span>
		</button>

		<button type="button" id="C_VISUALIZATION_PRINT_BUTTON" class="fr btn mr5" title="[{isys type="lang" ident="LC__MODULE__CMDB__VISUALIZATION__PRINT_BUTTON"}]">
			<img src="[{$dir_images}]icons/silk/printer.png" /><span class="ml5">[{isys type="lang" ident="LC__MODULE__CMDB__VISUALIZATION__PRINT_BUTTON"}]</span>
		</button>

		[{isys type="f_popup" p_strPopupType="visualization_export" name="C_VISUALIZATION_EXPORT_BUTTON"}]

		<button type="button" id="C_VISUALIZATION_ZOOM_IN_BUTTON" class="fr btn mr5" title="[{isys type="lang" ident="LC__MODULE__CMDB__VISUALIZATION__ZOOM_IN"}]">
			<img src="[{$dir_images}]icons/silk/magnifier_zoom_in.png" /><span class="ml5">[{isys type="lang" ident="LC__MODULE__CMDB__VISUALIZATION__ZOOM_IN"}]</span>
		</button>
		<button type="button" id="C_VISUALIZATION_ZOOM_BUTTON" class="fr btn mr5" title="[{isys type="lang" ident="LC__MODULE__CMDB__VISUALIZATION__ZOOM"}]">
			<img src="[{$dir_images}]icons/target.png" /><span class="ml5">[{isys type="lang" ident="LC__MODULE__CMDB__VISUALIZATION__ZOOM"}]</span>
		</button>
		<button type="button" id="C_VISUALIZATION_ZOOM_OUT_BUTTON" class="fr btn mr5" title="[{isys type="lang" ident="LC__MODULE__CMDB__VISUALIZATION__ZOOM_OUT"}]">
			<img src="[{$dir_images}]icons/silk/magnifier_zoom_out.png" /><span class="ml5">[{isys type="lang" ident="LC__MODULE__CMDB__VISUALIZATION__ZOOM_OUT"}]</span>
		</button>

		<!-- Spacing through margin -->

		<button type="button" id="C_VISUALIZATION_SWITCH_VIS_TYPE_BUTTON" class="fr btn mr20" title="[{isys type="lang" ident="LC__MODULE__CMDB__VISUALIZATION__SWITCH_VIS_TYPE_BUTTON"}]">
			<img src="[{$dir_images}]icons/silk/chart_organisation.png" /><span class="ml5">[{isys type="lang" ident="LC__MODULE__CMDB__VISUALIZATION__SWITCH_VIS_TYPE_BUTTON"}]</span>
		</button>
		<button type="button" id="C_VISUALIZATION_ORIENTATION_BUTTON" class="fr btn mr5" title="[{isys type="lang" ident="LC__MODULE__CMDB__VISUALIZATION__CHANGE_ORIENTATION"}]" data-orientation="vertical">
			<img src="[{$dir_images}]icons/silk/arrow_rotate_anticlockwise-half.png" /><span class="ml5">[{isys type="lang" ident="LC__MODULE__CMDB__VISUALIZATION__CHANGE_ORIENTATION"}]</span>
		</button>

		<span class="fr mr20">
			[{isys type="f_dialog" name="C_VISUALIZATION_SERVICE_FILTER"}]
			<a href="[{$service_filter_url}]" class="vam" title="[{isys type="lang" ident="LC__MODULE__CMDB__VISUALIZATION__SERVICES_FILTER_CONFIGURATION"}]"><img src="[{$dir_images}]icons/silk/link.png" class="ml5 vam" /></a>
		</span>
	</div>

	<span>
		[{isys type="f_popup" p_strPopupType="browser_object_ng" name="C_VISUALIZATION_OBJ_SELECTION"}]
		[{isys type="f_popup" p_strPopupType="visualization_itservice_selection" name="C_VISUALIZATION_IT_SERVICE_SELECTION"}]
	</span>

	<span class="ml20">[{isys type="f_popup" p_strPopupType="visualization_profile" name="C_VISUALIZATION_PROFILE"}]</span>
</div>

<script type="text/javascript">
	"use strict";

	(function () {
		SVGElement.prototype.exportToString = function (options) {
			var _svg = this;

			function exportSVG () {
				var svgData = XMLSerialize(_svg);

				if (options.decode) {
					if (window.btoa) {
						svgData = btoa(svgData);
					}
				}

				if (options.callback) options.callback(svgData);

				return svgData;
			}

			function XMLSerialize (svg) {

				// Needed for IE9
				// s: SVG dom, which is the <svg> element
				function XMLSerializerIE (s) {
					var out = "", n;

					out += "<" + s.nodeName;
					for (n = 0; n < s.attributes.length; n++) {
						out += " " + s.attributes[n].name + "=" + "'" + s.attributes[n].value + "'";
					}

					if (s.hasChildNodes()) {
						out += ">\n";

						for (n = 0; n < s.childNodes.length; n++) {
							out += XMLSerializerIE(s.childNodes[n]);
						}

						out += "</" + s.nodeName + ">" + "\n";

					}
					else out += " />\n";

					return out;
				}

				if (window.XMLSerializer) {
					return (new XMLSerializer()).serializeToString(svg);
				}
				else {
					return XMLSerializerIE(svg);
				}
			}

			return exportSVG();
		};
	}());
</script>