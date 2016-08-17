<div id="C_VISUALIZATION">
	<div id="C_VISUALIZATION_OVERLAY">
		<div>
			<p class="addition fr hide"></p>
			<img src="[{$dir_images}]ajax-loading.gif" class="vam mr5" /><span>[{isys type="lang" ident="LC__UNIVERSAL__LOADING"}]</span>
		</div>
	</div>
	<div id="C_VISUALIZATION_CANVAS"></div>
</div>

<br class="cb" />

<style type="text/css">
	[{include file="`$visualization_dir`/assets/visualization.css"}]
</style>

<script src="[{$dirs.tools}]js/d3/d3-v3.5.5-min.js"></script>
<script src="[{$dirs.tools}]js/d3/cola-v3.1.0-min.js"></script>
<script>
	(function () {
		'use strict';

		[{include file="`$visualization_dir`/assets/visualization.js"}]

		// This fixes some problems in IE and FF.
		setTimeout(function() {
			[{include file="`$visualization_dir`/graph/assets/graph.js"}]
		}, 100);
	})();
</script>