<h3 class="gradient p5 text-shadow border-bottom border-ccc">[{isys type="lang" ident="LC__WIDGET__STATS"}]</h3>

<canvas id="chart_[{$unique_id}]"></canvas>

<script language="javascript" type="text/javascript">
	if (!document.createElement('canvas').getContext) {
		G_vmlCanvasManager.initElement($('chart_[{$unique_id}]'));
	}

	function object_chart()
	{
		if ($('[{$unique_id}]'))
		{
			// "-2" because of the borders.
			var x = ($('[{$unique_id}]').getWidth() - 2),
				y = 425,
				g = new Bluff.[{$chart_type|default:"Pie"}]('chart_[{$unique_id}]', x + 'x' + y);

			// Set theme and options.
			g.theme_keynote();
			g.title = '[{isys type="lang" ident=$title p_bHtmlEncode=false}]';

			if (g.title.blank()) {
				g.hide_title = true;
			}

			g.set_theme({
				background_colors: ['#ffffff', '#eeeeee'],
				marker_color: '#ddd',
				font_color: '#000'
			});

			[{if $legend}]
			g.hide_legend = true;
			g.hide_mini_legend = true;
			[{/if}]

			g.hide_labels_less_than = 2.5;
			g.bottom_margin = -10;

			// Only change the font-size, if we are not displaying a "mini" chart.
			if ('[{$chart_type|default:"Pie"}]'.indexOf('Mini') == -1) {
				// All these options are relative to the chart-width... So we need to check this.
				if (x < 500) {
					g.legend_font_size = 17;
					g.legend_box_size = 15;
					g.legend_margin = 15;
				} else {
					g.legend_font_size = 13;
					g.legend_box_size = 10;
					g.legend_margin = 10;
				}
			}

			[{foreach from=$object_types item=type}]
			g.data('[{$type.title}]', [ [{$type.obj_cnt}] ], '#[{$type.color}]');
			[{/foreach}]

			g.draw();
		}
	}

	// We use this to get the element width, after it has been rendered (prevents awkward sizing).
	object_chart();

	//Event.observe(window, 'resize', object_chart);
</script>
