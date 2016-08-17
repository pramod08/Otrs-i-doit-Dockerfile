<h3 class="border-bottom border-ccc p5 gradient text-shadow">[{$title}]</h3>


<div style="overflow-x: scroll;" class="p5">
	<canvas id="canvas-[{$unique_id}]">
		<!-- Render graph here -->
	</canvas>
</div>

<script type="text/javascript">
	(function () {
		"use strict";

		var data = '[{$data|escape:"javascript"}]'.evalJSON(),
			object_count = parseInt('[{$object_count}]'),
			cmdb_status = '[{$cmdb_status|escape:"javascript"}]'.evalJSON(),
			delta = parseInt('[{$delta}]') + 5,
			chart = new Bluff.Line('canvas-[{$unique_id}]', (delta * 30) + 'x600'),
			i;

		chart.theme_pastel();

		chart.hide_title = true;
		chart.legend_font_size = 320 / delta;
		chart.marker_font_size = 320 / delta;
		chart.legend_box_size  = 320 / delta;
		chart.legend_margin    = 320 / delta;
		chart.line_width       = 60 / delta;
		chart.dot_radius       = 120 / delta;

		chart.minimum_value = parseInt('[{$cmdb_status_lowest}]');
		chart.maximum_value = parseInt('[{$cmdb_status_highest}]');
		chart.marker_count = parseInt('[{$cmdb_status_highest}]') - parseInt('[{$cmdb_status_lowest}]');

		for (i in data.changes) {
			if (data.changes.hasOwnProperty(i)) {
				chart.data(data.objects[i].obj_title, data.changes[i]);
			}
		}

		chart.labels = data.label;

		chart.draw();

		$('[{$unique_id}]')
			.down('.bluff-wrapper').setStyle({marginLeft:'100px'})
			.select('.bluff-text').each(function($el, i) {
				if (i >= object_count && i <= object_count + chart.marker_count) {
					$el.update(cmdb_status[$el.innerHTML]).setStyle({width:'135px', left: '-100px', overflow:'visible', textAlign:'right', whiteSpace:'nowrap'});
				}
			});

	})();
</script>