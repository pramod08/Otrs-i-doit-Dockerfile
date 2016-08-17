<div id="contextWrapper"></div>
<div id="infoWidgetWrapper" class="m0" style="padding:10px;height:100%;">
	<select name="layer3net" id="layer3net" onchange="$('isys_form').submit();">
	[{foreach from=$layer3networks item=net}]
		<option value="[{$net.isys_obj__id}]"[{if $net.isys_obj__id eq $smarty.post.layer3net}] selected="selected"[{/if}]>
			[{$net.isys_obj__title}] ([{$net.isys_cats_net_list__address}])
		</option>
	[{/foreach}]
	</select>
</div>
<div id="networkPlan" class="explorer text-shadow" style="position:absolute;margin:0;border:0;border-top:1px solid #888;font-weight:bold;top:64px;left:0;right:0;bottom:0;"></div>
<div id="infoWidget" class="info p10 bottom-inset-shadow" style="display:none;position:absolute;outline:1px solid #888;border:1px solid #fff;z-index:1000;right:10px;top:80px;width:250px;"></div>

<script type="text/javascript" language="javascript">
	[{include file="./view_network_plan.js"}]
</script>

<script type="text/javascript">
	$('contentArea').style.overflow	= 'hidden';

    var json = [{$data|default:"{id: '0', name: '', data: {objectType: 'Network Plan'}, children: []}"}];

	//load JSON data
	rgraph.loadJSON(json);
	rgraph.compute('end');
	rgraph.fx.animate({
	  modes:['polar'],
	  duration: 800
	});

	/*
	//load JSON data.
	ht.loadJSON(json);
	//compute positions and plot.
	ht.refresh();
	//end
	ht.controller.onComplete();
	*/
</script>