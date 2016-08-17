<style type="text/css">
	/* We include the styling directly for smarty variables inside the styling and no cache problems. */
	[{include file=$css_path}]

	#scroller {
		padding:0;
	}
</style>

<script type="text/javascript">
	window.default_dashboard = '[{$default_dashboard}]';
	window.is_allowed_to_configure_dashboard = '[{$is_allowed_to_configure_dashboard|intval}]';
	window.is_allowed_to_configure_widgets = '[{$is_allowed_to_configure_widgets|intval}]';

	[{include file=$dashboard_js}]
</script>

<h2 class="p10 gradient border-bottom text-shadow">
	[{if $is_allowed_to_configure_dashboard}]
		<img id="widget-config-edit" src="[{$dir_images}]icons/silk/pencil.png" title="[{isys type="lang" ident="LC__MODULE__DASHBOARD__CONFIGURE_WIDGETS"}]" class="mouse-pointer fr" />
	[{/if}]
	[{isys type="lang" ident="LC__MODULE__DASHBOARD"}] [{if $default_dashboard}]<em class="grey">[{isys type="lang" ident="LC__MODULE__DASHBOARD__DEFAULT"}]</em>[{/if}]
</h2>

<div id="module-dashboard" class="p10">
    <div id="widget-container-popup" class="popup" style="display:none; background: #fff"></div>

	<div id="widget-container">
		[{if count($widgets) > 0}]
			<div id="result-left" class="fl">
				[{foreach from=$widgets.left item=widget}]
				<div class="widget" id="[{$widget.unique_id}]" data-id="[{$widget.id}]" data-base64="[{$widget.base64}]" data-identifier="[{$widget.identifier}]" data-config="[{$widget.config}]" data-title="[{$widget.title}]" data-configurable="[{$widget.configurable}]" data-removable="[{$widget.removable|default:0}]">
					<!-- Will be loaded via AJAX -->
					<div class="loader">
						<span class="vam">[{isys type="lang" ident="LC__UNIVERSAL__LOADING"}]</span>
					</div>
				</div>
				[{/foreach}]
			</div>

			<div id="result-right" class="fr">
				[{foreach from=$widgets.right item=widget}]
				<div class="widget" id="[{$widget.unique_id}]" data-id="[{$widget.id}]" data-base64="[{$widget.base64}]" data-identifier="[{$widget.identifier}]" data-config="[{$widget.config}]" data-title="[{$widget.title}]" data-configurable="[{$widget.configurable}]" data-removable="[{$widget.removable|default:0}]">
					<div class="loader">
						<!-- Will be loaded via AJAX -->
						<span class="vam">[{isys type="lang" ident="LC__UNIVERSAL__LOADING"}]</span>
					</div>
				</div>
				[{/foreach}]
			</div>

			<br class="cb" />
		[{else}]
			<p class="p5 info">[{isys type="lang" ident="LC__MODULE__DASHBOARD__NO_WIDGETS"}]</p>
		[{/if}]


	</div>
</div>

<script type="text/javascript">
	$$('#module-dashboard .widget').each(function (el) {
		window.dashboard.reload_widget(el);
	});

	if ($('widget-config-edit')) {
		$('widget-config-edit').on('click', function () {
			get_popup('widget-config', 'ajax=1&call=dashboard_popup&func=load_widget_config', '640', '500', '', 'widget-container-popup');
		});
	}

	$('module-dashboard').on('click', 'div.widget-title h4.edit', function (ev) {
		var el = ev.findElement().up('div.widget'),
			identifier = el.readAttribute('data-identifier'),
			unique_id = el.id,
			id = el.readAttribute('data-id'),
			base64 = el.readAttribute('data-base64');

		// We need to create the base64 string in PHP thanks to IE ...
		get_popup(identifier + '-' + id, 'ajax=1&call=dashboard_popup&func=load_config_popup&unique_id=' + unique_id, '720', '480', {params:base64}, 'widget-container-popup');
	});

	$('module-dashboard').on('click', 'div.widget-title h4.delete', function (ev) {
		var el = ev.findElement().up('div.widget'),
			identifier = el.readAttribute('data-identifier'),
			unique_id = el.id,
			id = el.readAttribute('data-id');

		if (confirm('[{isys type="lang" ident="LC__WIDGET__REMOVE_CONFIRMATION" p_bHtmlEncode=false}]')) {
			new Ajax.Request('[{$widget_ajax_url}]&func=remove_widget', {
				parameters: {
					data_id: el.readAttribute('data-id')
				},
				method: 'post',
				onSuccess: function (response) {
					var json = response.responseJSON;

					if (json.success) {
						el.remove();
					} else {
						alert(json.message);
					}
				}
			});
		}
	});
</script>