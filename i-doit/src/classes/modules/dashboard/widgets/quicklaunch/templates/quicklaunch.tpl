<h3 class="gradient p5 text-shadow border-bottom border-ccc">Quicklaunch</h3>

<div class="p5">
	[{if count($function_list) > 0}]
	<div class="fl">
		<h4 class="m5">[{isys type="lang" ident="LC__WIDGET__QUICKLAUNCH_FUNCTIONS"}]</h4>
		<ul class="menu">
			[{foreach from=$function_list key="url" item="name"}]
			<li><a href="[{$url}]">[{$name}]</a></li>
			[{/foreach}]
		</ul>
	</div>
	[{/if}]

	[{if count($configuration_list) > 0}]
	<div class="fl">
		<h4 class="m5">[{isys type="lang" ident="LC__WIDGET__QUICKLAUNCH_CONFIGURATION"}]</h4>
		<ul class="menu">
			[{foreach from=$configuration_list key="url" item="name"}]
			<li><a href="[{$url}]">[{$name}]</a></li>
			[{/foreach}]
		</ul>
	</div>
	[{/if}]

	[{if $allow_update}]
	<div class="fl">
		<h4 class="m5">[{isys type="lang" ident="LC__WIDGET__QUICKLAUNCH_IDOIT_UPDATE"}]</h4>
		<ul class="menu">
			<li><a href="./updates">i-doit Update</a></li>
		</ul>
	</div>
	[{/if}]
	<br class="cb" />
</div>