[{if !$error}]
	<div id="[{$uniqueid}]_CONTAINER" style="height:320px;">
		<h3 class="gradient p5 text-shadow border-bottom border-ccc">CMDB-Explorer: [{$objtitle|default:""}]</h3>

		<div id="[{$uniqueid}]_OVERLAY">
			<div><img src="[{$dir_images}]ajax-loading.gif" class="vam mr5" /><span>[{isys type="lang" ident="LC__UNIVERSAL__LOADING"}]</span></div>
		</div>

		<div id="[{$uniqueid}]_CANVAS"></div>
	</div>
	<br class="cb" />

	<script type="text/javascript" language="javascript">
	/* Include D3 JS*/
	[{$d3_js}]
	/* End of D3 JS */

	(function() {
		'use strict';

		var $canvas = $('[{$uniqueid}]_CANVAS'),
			$overlay = $('[{$uniqueid}]_OVERLAY').hide(),
			object_types = '[{$object_types|escape:"javascript"}]'.evalJSON(),
			explorer_master,
			explorer_slave,
			top_tree_name = '[{$uniqueid}]_tree_top',
			bottom_tree_name = '[{$uniqueid}]_tree_bottom',
			profile = {},
			box_height = 20,
			profile_rows = 0;

		var initialize_explorer = function () {
			// This should be done to end the lifecycle of older instances.
			if (explorer_master && explorer_master.hasOwnProperty('stop')) {
				explorer_master.stop();
			}

			if (explorer_slave && explorer_slave.hasOwnProperty('stop')) {
				explorer_slave.stop();
			}

			if ('[{$objid|default:"0"}]' == 0 || '[{$profile|default:"0"}]' == 0) {
				idoit.Notify.warning('[{isys type="lang" ident=""}]', {life:10});
			}

			$overlay.show();

			new Ajax.Request('[{$ajax_url}]&func=load-tree-data', {
				parameters: {
					object: '[{$objid|default:"0"}]',
					filter: '[{$filter|default:"0"}]',
					profile: '[{$profile|default:"0"}]'
				},
				onComplete: function (response) {
					var json = response.responseJSON, node_height, node_width, vertical;

					if (!is_json_response(response, true)) {
						return;
					}

					if (json.success) {
						profile = json.data.profile;
						profile_rows = profile.rows.length;

						node_height = ((profile_rows * box_height) + 10);
						node_width = (parseInt(profile.width) + 10);
						vertical = ('[{$orientation}]' == 'vertical');

						explorer_slave = new CMDB_Explorer_Tree($canvas.update(), json.data.explorer_a, {
							name:bottom_tree_name,
							top_tree:top_tree_name,
							bottom_tree:bottom_tree_name,
							node_width: node_width,
							node_height: node_height,
							node_row_height: box_height,
							vertical: vertical,
							level_distance: (vertical ? (node_height + 50) : (node_width + 50)),
							click: function (d) {
								d3.select('.node.active').classed('active', false);
								d3.select(this).classed('active', true);

								if (profile.hasOwnProperty('show-cmdb-path') && profile['show-cmdb-path']) {
									explorer_slave.show_root_path(d.id);
								}
							}
						}, profile, object_types);

						explorer_slave.process(true);

						// This second tree is "mirrored" and will display the tree upside down.
						explorer_master = new CMDB_Explorer_Tree($canvas, json.data.explorer_b, {
							name:top_tree_name,
							top_tree:top_tree_name,
							bottom_tree:bottom_tree_name,
							node_width: node_width,
							node_height: node_height,
							node_row_height: box_height,
							mirrored: true,
							vertical: vertical,
							level_distance: (vertical ? (node_height + 50) : (node_width + 50)),
							click: function (d) {
								d3.select('.node.active').classed('active', false);
								d3.select(this).classed('active', true);

								if (profile.hasOwnProperty('show-cmdb-path') && profile['show-cmdb-path']) {
									explorer_master.show_root_path(d.id);
								}
							}
						}, profile, object_types);

						explorer_master.set_svg(explorer_slave.get_svg());
						explorer_master.process(true);

						// Trigger the "zoom to 100%" callback, to center the view.
						idoit.callbackManager.triggerCallback('visualization-zoom', '=');

						$overlay.hide();
					} else {
						idoit.Notify.error(json.message);
					}
				}
			});
		};

		// Init the explorer!
		initialize_explorer();
	})();
	</script>

	<style type="text/css">
		#[{$uniqueid}]_CANVAS,
		#[{$uniqueid}]_CANVAS svg {
			height:306px;
			overflow: hidden;
			-moz-user-select: none;
		}

		#[{$uniqueid}]_CANVAS {
			background: #eee;
		}

		#[{$uniqueid}]_CANVAS svg .link {
			fill: none;
			stroke: #aaa;
			stroke-width: 1px;
		}

		#[{$uniqueid}]_CANVAS svg .node {
			z-index: 1002;
		}

		#[{$uniqueid}]_CANVAS svg .node rect:first-child {
			fill: transparent;
			stroke: #000;
			stroke-opacity: 0.5;
		}

		#[{$uniqueid}]_CANVAS svg .node.active rect:first-child {
			stroke-width: 4px;
			stroke-opacity: 1;
		}

		#[{$uniqueid}]_CANVAS svg text {
			/*font: 10px sans-serif;*/
			pointer-events: none;
		}

		#[{$uniqueid}]_CANVAS svg .overlay {
			fill: none;
			pointer-events: all;
		}

		/* Styles for the CMDB-Explorer overlay */
		#[{$uniqueid}]_OVERLAY {
			position: absolute;
			top: 0;
			right: 0;
			bottom: 0;
			left: 0;
			background: rgba(255, 255, 255, .5);
			z-index: 1003;
		}

		#[{$uniqueid}]_OVERLAY div {
			position: absolute;
			top: 50%;
			left: 50%;
			background: #fff;
			width: 300px;
			margin-left: -150px;
			margin-top: -15px;
			box-sizing: border-box;
			padding: 5px;
			border: 1px solid #aaa;
		}
	</style>
[{else}]
	<h3 class="gradient p5 text-shadow border-bottom border-ccc">CMDB-Explorer</h3>
	<p class="note p5 m5">[{$error}]</p>
[{/if}]