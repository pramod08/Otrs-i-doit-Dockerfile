<fieldset id="module-wiring" class="overview border-top-none">
	<legend><span>[{isys type="lang" ident="LC__MODULE__WIRING__OBJECT"}]</span></legend>

	<div id="module-wiring-legend" class="p5 muted text-shadow">
		<h3>[{isys type="lang" ident="LC__UNIVERSAL__LEGEND"}]</h3>
		<ul class="legend list-style-none m0 mt5 p0">
			[{foreach from=$types item="type"}]
				<li>
					<div class="bullet" style="background-color:[{$type.color}];"></div> <div class="text">[{$type.title}]</div>
				</li>
			[{/foreach}]
			<li>
				<div class="bullet" style="background-color:darkred;"></div> <div class="text">[{isys type="lang" ident="LC__MODULE__WIRING__UNKNOWN_CONNECTOR"}]</div>
			</li>
		</ul>
	</div>

	<table class="contentTable">
		<tr>
			<td class="key">[{isys type="f_label" name="cmdb_object" ident="LC__MODULE__WIRING__STARTING_OBJECT"}]</td>
			<td class="value">[{isys type="f_popup" name="cmdb_object" p_strPopupType="browser_object_ng" edit_mode="1" multiselection=false secondSelection=false}]</td>
		</tr>
		<tr>
			<td class="key"></td>
			<td>
				<button type="button" id="data-loader" class="btn ml20">
					<img src="[{$dir_images}]icons/silk/tick.png" class="mr5" />
					<span>[{isys type="lang" ident="LC__MODULE__WIRING__LOAD"}]</span>
				</button>
				<button type="button" id="csv-export" class="btn ml10" disabled="disabled">
					<img src="[{$dir_images}]icons/silk/table.png" class="mr5" />
					<span>[{isys type="lang" ident="LC__MODULE__WIRING__CSV_EXPORT"}]</span>
				</button>
				<label class="ml10">
					<input type="checkbox" name="alignOutput" id="alignOutput" value="1" checked="checked" /> [{isys type="lang" ident="LC__MODULE__WIRING__CENTER_SELECTED_OBJECT"}]
				</label>
			</td>
		</tr>
		<tr class="view-options" style="display:none;">
			<td colspan="2">
				<hr class="mt5 mb5" />
			</td>
		</tr>
		<tr class="view-options" style="display:none;">
			<td class="key vat">[{isys type="f_label" name="wiring_search" ident="LC__MODULE__WIRING__VIEW_OPTIONS"}]</td>
			<td>
				[{isys type="f_text" name="wiring_search" p_strClass="input-small mb5" p_strPlaceholder="LC__UNIVERSAL__SEARCH" p_bEditMode=1}]
				<label class="ml20 display-block">
					<input type="checkbox" id="wiring_view_port_names" />
					<span class="ml5">[{isys type="lang" ident="LC__MODULE__WIRING__DISPLAY_PORT_NAMES"}]</span>
				</label>
				<label class="ml20 display-block">
					<input type="checkbox" id="wiring_view_cable_names" />
					<span class="ml5">[{isys type="lang" ident="LC__MODULE__WIRING__DISPLAY_CABLE_NAMES"}]</span>
				</label>
				<label class="ml20">
					<input type="checkbox" id="wiring_view_port_checkboxes" />
					<span class="ml5">[{isys type="lang" ident="LC__MODULE__WIRING__DISPLAY_PORT_CHECKBOXES"}]</span>
				</label>

				[{isys type="f_dialog" name="wiring_connector_types"}]

				[{isys type="f_button" name="wiring_connector_save_button"}]
			</td>
		</tr>
	</table>

	<hr />

	<div class="p10" id="wiringResponse"></div>
</fieldset>

<script type="text/javascript">
(function () {
	"use strict";

	var $wiring_base = $('module-wiring'),
		$wiring_response = $('wiringResponse'),
		$wiring_data_loader = $('data-loader'),
		$wiring_legend = $('module-wiring-legend'),
		$wiring_viewoptions = $wiring_base.select('.view-options'),
		$wiring_searchfield = $('wiring_search'),
		$wiring_display_ports = $('wiring_view_port_names'),
		$wiring_display_cables = $('wiring_view_cable_names'),
		$wiring_display_checkboxes = $('wiring_view_port_checkboxes'),
		$wiring_connector_types = $('wiring_connector_types'),
		$wiring_connector_save_button = $('wiring_connector_save_button'),
		connectorTypes = '[{$types|json_encode}]'.evalJSON();

	// Translations
	idoit.Translate.set('LC__CMDB__BROWSER_OBJECT__PLEASE_CHOOSE', '[{isys type="lang" ident="LC__CMDB__BROWSER_OBJECT__PLEASE_CHOOSE"}]');

	// Functions
	if (!idoit.Module) idoit.Module = {};

	idoit.Module.Wiring = Class.create({
		/**
		 * DOM Element where the processed html gets inserted into.
		 * @var Element
		 */
		$container: null,

		/**
		 * URL for all server requests.
		 * @var object
		 */
		options: {},

		/**
		 * Initialize wiring instance.
		 *
		 * @param {Element} $el
		 * @param {object} options
		 *
		 * @constructor
		 */
		initialize: function($el, options) {
			if (Object.isString($el)) {
				$el = $($el);
			}


			this.$container = $el;
			this.options = Object.extend({
				baseURL: '',
				tooltips: false,
				beforeAjaxRequest: Prototype.emptyFunction,
				afterAjaxRequest: Prototype.emptyFunction,
				afterRender: Prototype.emptyFunction
			}, options || {});
		},

		/**
		 * Initiate a cable run request
		 *
		 * @param  {Number}  objID
		 * @param  {Boolean} alignOutput
		 */
		cableRunSingle: function(objID, alignOutput) {
			if (objID > 0) {
				$(this.$container)
					.update()
					.up()
					.setOpacity(.4);

				if (alignOutput == null) {
					alignOutput = true;
				}

				try {
					this.options.beforeAjaxRequest();
				} catch (e) {
					idoit.Notify.error(e, {sticky:true});
				}

				new Ajax.Request(this.options.baseURL + 'wiring/object', {
					parameters: {
						'objID': objID,
						'alignOutput': alignOutput
					},
					onComplete: function (r) {
						try {
							this.options.afterAjaxRequest();
						} catch (e) {
							idoit.Notify.error(e, {sticky:true});
						}

						this.$container
							.update(r.responseText)
							.up()
							.setOpacity(1);

						$wiring_viewoptions.invoke('show');

						if (this.options.tooltips) {
							delay(function () {
								$wiring_response.select('[data-tooltip]').each(function ($el) {
									var tooltip = $el.getAttribute('data-tooltip');

									if (!tooltip.blank()) {
										new Tip($el, tooltip, {
											style: 'darkgrey',
											offset: {x: 0, y: 0},
											hook: {target: 'bottomMiddle', tip: 'topMiddle'}
										});
									}
								});
							}, 500);
						}

						try {
							$('csv-export').disabled='';
							this.options.afterRender();
						} catch (e) {
							idoit.Notify.error(e, {sticky:true});
						}
					}.bind(this)
				});
			} else {
				idoit.Notify.warning(idoit.Translate.get('LC__CMDB__BROWSER_OBJECT__PLEASE_CHOOSE'));
			}
		}
	});

	// Action controller
	$('csv-export').on('click', function () {
		$('isys_form')
			.writeAttribute('action', window.www_dir + 'wiring/csv-export')
			.submit();
	});

	if ($wiring_data_loader) {
		$wiring_data_loader.on('click', function () {
			var wiring = new idoit.Module.Wiring($wiring_response, {
				baseURL: '[{$config.www_dir}]',
				tooltips: true,
				beforeAjaxRequest: function () {
					$wiring_data_loader
						.disable()
						.update(new Element('img', {src:'[{$dir_images}]ajax-loading.gif', className:'mr5'}))
						.insert(new Element('span').update('[{isys type="lang" ident="LC__UNIVERSAL__LOADING"}]'));
				},
				afterAjaxRequest: function () {
					$wiring_data_loader
						.enable()
						.update(new Element('img', {src:'[{$dir_images}]icons/silk/tick.png', className:'mr5'}))
						.insert(new Element('span').update('[{isys type="lang" ident="LC__MODULE__WIRING__LOAD"}]'));
				},
				afterRender: function () {
					window_resize();
					$wiring_display_cables.simulate('change');
					$wiring_display_ports.simulate('change');
					$wiring_display_checkboxes.simulate('change');
					filter_wiring();
				}
			});

			// Grab the cable run
			wiring.cableRunSingle($('cmdb_object__HIDDEN').value, $('alignOutput').checked);
		});
	}

	if ($wiring_searchfield) {
		$wiring_searchfield.on('keyup', function () {
			// Delay for half a second to prevent JS from blowing up.
			delay(filter_wiring, 500);
		});
	}

	if ($wiring_display_ports) {
		$wiring_display_ports.on('change', function () {
			if ($wiring_display_ports.checked) {
				$wiring_response.addClassName('expanded-bullets');
			} else {
				$wiring_response.removeClassName('expanded-bullets');
			}
		});
	}

	if ($wiring_display_cables) {
		$wiring_display_cables.on('change', function () {
			if ($wiring_display_cables.checked) {
				$wiring_response.addClassName('expanded-cables');
			} else {
				$wiring_response.removeClassName('expanded-cables');
			}
		});
	}

	if ($wiring_display_checkboxes) {
		$wiring_display_checkboxes.on('change', function () {
			if ($wiring_display_checkboxes.checked) {
				// The ".disabled" CSS class gets assigned by the smarty plugin "f_button".
				$wiring_connector_save_button.enable().removeClassName('disabled');
				$wiring_connector_types.enable();

				$wiring_response.addClassName('expanded-port-checkboxes');
			} else {
				$wiring_connector_save_button.disable();
				$wiring_connector_types.disable();

				$wiring_response.removeClassName('expanded-port-checkboxes');
			}
		});
	}

	if ($wiring_connector_save_button) {
		$wiring_connector_save_button.on('click', function () {
			var $checkboxes = $wiring_response.select('input:checked'),
				connectorType = $wiring_connector_types.getValue();

			if ($checkboxes.length) {
				if (confirm(('[{isys type="lang" ident="LC__MODULE__WIRING__UPDATE_CONNECTORS_CONFIRMATION" p_bHtmlEncode=false}]'.replace('%d', $checkboxes.length)))) {
					$wiring_connector_save_button
						.disable()
						.update(new Element('img', {src:'[{$dir_images}]ajax-loading.gif', className:'mr5'}))
						.insert(new Element('span').update('[{isys type="lang" ident="LC__UNIVERSAL__LOADING"}]'));

					new Ajax.Request('[{$config.www_dir}]wiring/object', {
						parameters: {
							'connectors': Object.toJSON($checkboxes.invoke('readAttribute', 'name').invoke('substr', 5).map(function (id) {return parseInt(id)})),
							'connectorType': connectorType
						},
						onComplete: function (r) {
							var json = r.responseJSON;

							$wiring_connector_save_button
								.enable()
								.update(new Element('img', {src:'[{$dir_images}]icons/silk/disk.png', className:'mr5'}))
								.insert(new Element('span').update('[{isys type="lang" ident="LC__MODULE__WIRING__UPDATE_CONNECTORS"}]'));

							if (json.success) {
								idoit.Notify.success('[{isys type="lang" ident="LC__MODULE__WIRING__UPDATE_CONNECTORS_SUCCESS"}]');

								$checkboxes.invoke('setValue', '').each(function ($checkbox) {
									$checkbox.up('.bullet').setStyle({
										backgroundColor:connectorTypes[connectorType].color
									});
								});
							} else {
								idoit.Notify.error(json.message, {sticky: true});
							}
						}
					});
				}
			} else {
				idoit.Notify.warning('[{isys type="lang" ident="LC__MODULE__WIRING__NO_CONNECTORS_SELECTED"}]', {life:5});
			}
		});
	}

	function filter_wiring () {
		var search = $wiring_searchfield.getValue().toLowerCase(),
			$rows = $wiring_response.select('.wiring');

		if (search.blank() || search.length < 3) {
			$rows.invoke('show');
			return;
		}

		$rows.each(function ($table) {
			if (!$table.down('[data-search*="' + search.escapeHTML() + '"]')) {
				$table.hide();
			} else {
				$table.show();
			}
		});
	}

	// This little snippet will move the legend vertically, while scrolling.
	$('contentWrapper').on('scroll', function() {
		var top = this.scrollTop,
			scroll_at = 20;

		if (top > scroll_at) {
			$wiring_legend.setStyle({top: 40 + (top - scroll_at) + 'px'});
		} else {
			$wiring_legend.setStyle({top: 40 + 'px'});
		}
	});

	// Change the URL when a new selection is done.
	$('cmdb_object__VIEW').on('updated:selection', function () {
		var value = $F('cmdb_object__HIDDEN');

		if (value.blank()) {
			window.pushState({}, '', window.www_dir + 'wiring');
		} else {
			window.pushState({}, '', window.www_dir + 'wiring/' + value);
		}
	});

	if (! $F('cmdb_object__HIDDEN').blank()) {
		$wiring_data_loader.simulate('click');
	}

	Event.observe(window, 'resize', window_resize);

	function window_resize () {
		$wiring_response.setStyle({width:($('contentWrapper').getWidth() - 25) + 'px'});
	}
}());
</script>

<style type="text/css">
	#module-wiring .bullet {
		width: 8px;
		height: 8px;
	}

	#wiringResponse .cable-label {
		display: none;
		background: #fff;
		width: 100px;
		height: 12px;
		line-height: 12px;
		margin: 0 12px;
		border: 1px solid #aaa;
		border-radius: 10px;
	}

	#wiringResponse.expanded-bullets table.wiring td,
	#wiringResponse.expanded-cables table.wiring td {
		width: 200px;
	}

	#wiringResponse.expanded-bullets.expanded-cables table.wiring td {
		width: 350px;
	}

	#wiringResponse.expanded-bullets.expanded-cables .cable {
		padding-left: 50px;
		width: 125px;
	}

	#wiringResponse.expanded-cables table.wiring table.innerWiring tr td.cable {
		width: 120px;
		padding-left: 0;
		padding-top: 2px;
	}

	#wiringResponse.expanded-bullets.expanded-cables table.wiring table.innerWiring tr td.cable {
		padding-left: 50px;
	}

	#wiringResponse.expanded-bullets .bullet {
		width: 55px;
		height: 10px;
		border-radius: 4px;
		margin-top: 0;
		line-height: 10px;
		font-size: 9px;
		color:#000;
		padding:0 2px;
		text-shadow: none;
	}

	#wiringResponse.expanded-bullets .bullet span {
		display: inline;
	}

	#wiringResponse.expanded-cables .cable-label {
		display: block;
	}

	#module-wiring table.wiring {
		/*opacity: .6;
		-ms-filter: "progid:DXImageTransform.Microsoft.Alpha(Opacity=60)";
		filter: alpha(opacity=60);*/

		border-spacing: 0;
		border-collapse: collapse;
		font-size: 10px;

		background: #eaebec;
		border: #ccc 1px solid;

		/*
		-moz-box-shadow: 0 1px 2px #d1d1d1;
		-webkit-box-shadow: 0 1px 2px #d1d1d1;
		box-shadow: 0 1px 2px #d1d1d1;
		*/

		margin-bottom: 5px;
		table-layout: fixed;
	}

	#module-wiring table.wiring table.innerWiring tr td.text a:active,
	#module-wiring table.wiring table.innerWiring tr td.text a:hover,
	#module-wiring table.wiring table.innerWiring tr td.text.selected a:hover,
	#module-wiring table.wiring table.innerWiring tr td.text.selected a:active {
		color: #bd5a35;
		text-decoration: underline;
	}

	/*
	#module-wiring table.wiring:hover {
		opacity: 1;
		-ms-filter: "progid:DXImageTransform.Microsoft.Alpha(Opacity=100)";
		filter: alpha(opacity=100);
	}
	*/

	#module-wiring table.wiring td {
		width: 160px;
		line-height: 19px;
		vertical-align: top;
	}

	#module-wiring table.wiring tr:hover td:not(.port) table:not(.sub-wiring) {
		background: #fff;
	}

	#module-wiring table.wiring div.bullet-right {
		position: absolute;
		right: 0;
		top: 3px;
		margin-right: -4px;
	}

	#module-wiring table.wiring div.bullet-left {
		position: absolute;
		left: 5px;
		top: 3px;
		margin-left: -9px;
	}

	#wiringResponse .bullet {
		margin-top: 2px;
	}

	#wiringResponse.expanded-port-checkboxes table.wiring .bullet {
		width: 14px;
		height: 14px;
		padding: 2px;

		/*
		<!--MAC FIX-->
		width: 12px;
		height: 13px;
		padding: 3px 2px 2px 2px;
		*/

		border-radius: 2px;
		top: -2px;
	}

	#wiringResponse.expanded-port-checkboxes table.wiring .bullet input {
		display: block;
		float:left;
	}

	#wiringResponse.expanded-bullets table.wiring tr td:first-child table.innerWiring tr td.text
	{
		text-align:left;
	}

	#wiringResponse.expanded-bullets table.wiring .bullet-right {
		margin-right: -40px;
	}

	#wiringResponse.expanded-bullets table.wiring .bullet-left {
		margin-left: -40px;
	}

	#wiringResponse.expanded-port-checkboxes.expanded-bullets table.wiring .bullet {
		width: 60px;
		top: 0;
	}

	#wiringResponse.expanded-port-checkboxes.expanded-bullets table.wiring .bullet span {
		display: block;
		height: 14px;
		line-height: 13px;
		overflow: hidden;
	}

	#wiringResponse .bullet span,
	#wiringResponse .bullet input {
		display:none;
	}

	#wiringResponse table.wiring table.innerWiring tr td.cable {
		width: 35px;
		padding-left: 3px;
	}

	#wiringResponse table.wiring table.innerWiring tr td.port {
		width: 5px;
		position: relative;
		padding: 0;
	}

	#module-wiring table.wiring table.innerWiring.selected tr td.text {
		background: #ccc !important;
		width:160px;
	}

	#wiringResponse table.wiring table.innerWiring tr td.text {
		border-left: 1px solid #aaa;
		border-right: 1px solid #aaa;

		background-color: white !important;
		width: 110px;
		padding: 0 2px 0 3px;
	}

	#module-wiring table.wiring tr td:first-child table.innerWiring tr td.text {
		border-left: none !important;
		width:160px;

	}

	#wiringResponse td.cable img {
		vertical-align: middle;
		display: none;
	}

	#wiringResponse:not(.expanded-cables) table.wiring:hover td.cable img {
		display: inline;
	}

	#module-wiring table.wiring table.innerWiring {
		table-layout: fixed;
		border-spacing: 0;
		border-collapse: collapse;
	}

	#module-wiring table.wiring tr {
		background: url(data:image/gif;base64,R0lGODlhAQABAIAAAAUEBAAAACwAAAAAAQABAAACAkQBADs=) repeat-x 0 10px;
	}

	#module-wiring table.wiring tr td:last-child table.innerWiring tr td.port:last-child {
		width: 0 !important;
	}

	#module-wiring table.wiring tr td:first-child table.innerWiring td.cable:first-child,
	#module-wiring table.wiring tr td:last-child table.innerWiring td.cable:last-child {
		background-color: transparent !important;
	}

	#module-wiring table.wiring tr td:first-child table.innerWiring td:first-child,
	#module-wiring table.wiring tr td:last-child table.innerWiring td:last-child {
		background-color: white !important;
	}

	#wiringResponse {
		min-height: 150px;
		overflow-x: auto;
	}

	#module-wiring-legend {
		position:absolute;
		top: 40px;
		right: 35px;
		z-index: 100;
	}

	#module-wiring-legend .bullet {
		margin-top: 3px;
		float: left;
	}

	#module-wiring-legend .text {
		padding-left: 15px;
	}
</style>