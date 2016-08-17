[{if (count($licences))}]

[{if $layout == 'vertical'}]

	[{foreach from=$licences item=licence}]
		<h3 class="p5 border-bottom border-ccc gradient text-shadow">
			[{if $remaining_days !== false}]<span class="fr">[{$remaining_days}]</span>[{/if}]
			[{isys type="lang" ident="LC__WIDGET__EVAL__LICENCE"}]: [{$licence.licencetype}]
		</h3>

		<div class="eval-widget p5">
			<p class="mt5">[{$licence.string_obj_limit}]</p>
			[{if $licence.unlimited == false}]
			<div class="progress">
				<div id="remaining_objects_percent_[{$uid}]" class="progress-bar" data-width-percent="[{$licence.remaining_objects_percent}]" style="width:0; background-color:transparent;"></div>
			</div>
			[{/if}]

			<p class="mt10">[{$licence.string_time_limit}]</p>
			<div class="progress">
				<div id="remaining_time_percent_[{$uid}]" class="progress-bar" data-width-percent="[{$licence.remaining_time_percent}]" style="width:0; background-color:transparent;"></div>
			</div>
		</div>
	[{/foreach}]

[{else}]

	[{foreach from=$licences item=licence}]
	<h3 class="border-bottom border-ccc p5 gradient text-shadow">
		[{if $remaining_days !== false}]<span class="fr">[{$remaining_days}]</span>[{/if}]
		[{isys type="lang" ident="LC__WIDGET__EVAL__LICENCE"}]: [{$licence.licencetype}]
	</h3>

	<div class="eval-widget p5">
		<table class="two-col mt5" style="width:100%;">
			<tr>
				<td class="vat pr5">
					<p>[{$licence.string_obj_limit}]</p>
				</td>
				<td class="vat">
					<p>[{$licence.string_time_limit}]</p>
				</td>
			</tr>
			<tr>
				<td class="pr5">
					[{if $licence.unlimited == false}]
					<div class="progress mt5">
						<div id="remaining_objects_percent_[{$uid}]" class="progress-bar" data-width-percent="[{$licence.remaining_objects_percent}]" style="width:0; background-color:transparent;"></div>
					</div>
					[{/if}]
				</td>
				<td>
					<div class="progress mt5">
						<div id="remaining_time_percent_[{$uid}]" class="progress-bar" data-width-percent="[{$licence.remaining_time_percent}]" style="width:0; background-color:transparent;"></div>
					</div>
				</td>
			</tr>
		</table>
	</div>
	[{/foreach}]

[{/if}]

<script type="text/javascript">
	(function () {
		"use strict";

		progressBarInit(true);
	}());
</script>
[{else}]
    <h3 class="border-bottom border-ccc p5 gradient text-shadow">
        [{isys type="lang" ident="LC__WIDGET__EVAL__LICENCE"}]
    </h3>

    <div class="eval-widget p5">
        <p class="mt5 error p5">[{isys type="lang" ident="LC__LICENCE__NO_LICENCE"}].</p>
    </div>
[{/if}]