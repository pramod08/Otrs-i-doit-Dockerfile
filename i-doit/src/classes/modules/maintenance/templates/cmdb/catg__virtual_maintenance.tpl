<div id="catg__virtual_maintenance">
	[{if $maintenances}]

	<div class="p5">
		<a href="[{$filter_url}]" class="btn mr10">
			[{if $filter_asc}]
			<img src="[{$dir_images}]icons/silk/bullet_arrow_up.png" class="mr5" /><span>[{isys type="lang" ident="LC__CATG__VIRTUAL_MAINTENANCE__FILTER_NEWEST_FIRST"}]</span>
			[{else}]
			<img src="[{$dir_images}]icons/silk/bullet_arrow_down.png" class="mr5" /><span>[{isys type="lang" ident="LC__CATG__VIRTUAL_MAINTENANCE__FILTER_OLDEST_FIRST"}]</span>
			[{/if}]
		</a>
		[{isys type="lang" ident="LC__CATG__VIRTUAL_MAINTENANCE__FILTER"}]
	</div>

	<table class="mainTable border-top">
		<thead>
		<tr>
			<th>[{isys type="lang" ident="LC__CATG__VIRTUAL_MAINTENANCE__MAINTENANCE"}]</th>
			<th><img src="[{$dir_images}]icons/silk/calendar.png" class="vam mr5" />[{isys type="lang" ident="LC__CATG__VIRTUAL_MAINTENANCE__MAINTENANCE_DATE"}]</th>
			<th><img src="[{$dir_images}]icons/silk/wrench.png" class="vam mr5" />[{isys type="lang" ident="LC__CATG__VIRTUAL_MAINTENANCE__FINISHED"}]</th>
			<th><img src="[{$dir_images}]icons/silk/email.png" class="vam mr5" />[{isys type="lang" ident="LC__CATG__VIRTUAL_MAINTENANCE__MAIL_SENT"}]</th>
		</tr>
		</thead>
		<tbody>
		[{foreach $maintenances as $maintenance}]
		<tr>
			<td><a href="[{$maintenance.url}]"><img src="[{$dir_images}]icons/silk/link.png" class="vam mr5" /><span>[{isys type="lang" ident="LC__CATG__VIRTUAL_MAINTENANCE__LINK"}]</span></a></td>
			<td>[{$maintenance.from}] - [{$maintenance.to}]</td>
			<td>
				[{if $maintenance.finished}]
				<img src="[{$dir_images}]icons/silk/tick.png" class="vam mr5"><span class="green">[{$maintenance.finished_datetime}]</span>
				[{else}]
				<img src="[{$dir_images}]icons/silk/cross.png" class="vam mr5"><span class="red">[{isys type="lang" ident="LC__UNIVERSAL__NO"}]</span>
				[{/if}]
			</td>
			<td>
				[{if $maintenance.mail_sent}]
				<img src="[{$dir_images}]icons/silk/tick.png" class="vam mr5"><span class="green">[{$maintenance.mail_sent_datetime}]</span>
				[{else}]
				<img src="[{$dir_images}]icons/silk/cross.png" class="vam mr5"><span class="red">[{isys type="lang" ident="LC__UNIVERSAL__NO"}]</span>
				[{/if}]
			</td>
		</tr>
		[{/foreach}]
		</tbody>
	</table>
	[{else}]
	<div class="p10 blue">
		<img src="[{$dir_images}]icons/silk/information.png" class="vam mr5" /><span>[{isys type="lang" ident="LC__CATG__VIRTUAL_MAINTENANCE__NO_PLANNED_MAINTENANCES"}]</span>
	</div>
	[{/if}]
</div>

<style type="text/css">
	#catg__virtual_maintenance table {
		width:100%;
	}
</style>