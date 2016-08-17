<h3 class="gradient p5 text-shadow border-bottom border-ccc">[{isys type="lang" ident="LC__WIDGET__IT_SERVICE_CONSISTENCY"}]</h3>

<div class="m5">
	<div id="[{$unique_id}]_list">
		[{if $selected_services !== false}]
			[{if count($services) > 0}]
			<ul class="service-list">
				[{foreach from=$services item=service}]
				<li class="service">
					<h4 class="p5 border gradient text-shadow"><span class="fr">[{$service.link}]</span>[{$service.name}]</h4>
					<ul class="object-list">
					[{if count($service.inconsistencies) > 0}]
						<li class="error p5"><img src="[{$dir_images}]icons/silk/cross.png" class="vat" /> <span class="vat ml5">[{isys type="lang" ident="LC__WIDGET__IT_SERVICE_CONSISTENCY_INCONSISTENCE"}]</span></li>
						[{foreach from=$service.inconsistencies item=object}]
							<li><div class="cmdb-marker" style="background-color:#[{$object.status.isys_cmdb_status__color}]; cursor:help;" title="[{isys type="lang" ident=$object.status.isys_cmdb_status__title}]"></div>[{$object.name}]</li>
						[{/foreach}]
					[{else}]
						<li class="note p5"><img src="[{$dir_images}]icons/silk/tick.png" class="vat" /> <span class="vat ml5">[{isys type="lang" ident="LC__WIDGET__IT_SERVICE_CONSISTENCY_ALL_OKAY"}]</span></li>
					[{/if}]
					</ul>
				</li>
				[{/foreach}]
			</ul>
			[{else}]
			<div class="note p5"><img src="[{$dir_images}]icons/silk/tick.png" class="vat" /> <span class="vat ml5">[{isys type="lang" ident="LC__WIDGET__IT_SERVICE_CONSISTENCY_ALL_OKAY"}]</span></div>
			[{/if}]
		[{else}]
		<div class="info p5"><img src="[{$dir_images}]icons/silk/information.png" class="vat" /> <span class="vat ml5">[{isys type="lang" ident="LC__WIDGET__IT_SERVICE_CONSISTENCY_NO_SELECTION"}]</span></div>
		[{/if}]
	</div>

	<div class="cb"></div>
</div>

<style type="text/css">
	#[{$unique_id}]_list {
		overflow: hidden;
		overflow-x: auto;
	}

	ul.service-list {
		list-style: none;
		margin: 0;
	}

	ul.service-list li.service {
		float: left;
		width: 260px;
		margin-right: 5px;
		padding-bottom: 15px;
	}

	ul.service-list li.service.last {
		margin-right: 0;
	}

	ul.object-list {
		list-style: none;
		margin: 0;
	}

	ul.object-list li {
		margin-top: 5px;
	}
</style>

[{if count($services) > 0}]
<script type="text/javascript">
	var service_list = $('[{$unique_id}]_list').down('ul.service-list'),
		service_list_length = service_list.select('li.service').length;

	// Add the "last" CSS-class on the last service to remove the right margin.
	service_list.select('li.service').last().addClassName('last');

	service_list.setStyle({width:((service_list_length * 265) - 5) + 'px'})
</script>
[{/if}]