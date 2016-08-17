<div class="pt5 border-top w100">
	<div id="list"></div>
</div>

<script type="text/javascript">
	(function () {
		window.build_table('list', "[{$result|escape:"javascript"}]".evalJSON(), [{if $ajax_pager}]true[{else}]false[{/if}], '[{$ajax_url}]', '[{$preload_pages}]', '[{$max_pages}]');

		$('list')
				.up()
				.setStyle({
					'overflow': 'auto',
					'height': ($('contentWrapper').getHeight() - 35) + 'px',
					'border-bottom': '0px'
				});
	})();
</script>