<div class="gradient content-header">
	<img src="../images/icons/silk/bricks.png" class="vam mr5" /><span class="bold text-shadow headline vam">Modules</span>
</div>

<div id="innercontent">

	[{if $message}]<p class="note mt0 p10 mb10">[{$message}]</p>[{/if}]
	[{if $error}]<p class="error mt0 p10 mb10">[{$error}]</p>[{/if}]

	<div id="add-new" class="mt10" style="display:none;">
		<form id="form" action="?req=modules&action=add" method="post" enctype="multipart/form-data">
			<input type="hidden" name="action" value="add" />
			<fieldset>
				<legend class="bold text-shadow">Install/update module</legend>

				<table class="contentTable" width="100%">
					<colgroup><col width="160" /></colgroup>
					<tr>
						<td class="bold"><label for="mandator">Tenant</label></td>
						<td>
							<select name="mandator" id="mandator">
								<option value="0">All tenants</option>
								<optgroup label="specific tenant">
								[{foreach from=$mandators item=mandator}]
									<option value="[{$mandator.isys_mandator__id}]" label="[{$mandator.isys_mandator__title}]">[{$mandator.isys_mandator__title}]</option>
								[{/foreach}]
								</optgroup>
							</select>
						</td>
					</tr>
					<tr>
						<td class="bold"><label for="module_file">ZIP File</label></td>
						<td><input type="file" name="module_file" id="module_file" /></td>
					</tr>
				</table>

				<div>
					<button class="btn" type="button" onclick="$('add_loading').show(); $('form').submit();">
						<img src="../images/icons/silk/brick_add.png" class="mr5" /><span>Upload and install</span>
					</button>
					<button class="btn" type="button" onclick="$('module_toolbar').show();new Effect.SlideUp('add-new', {duration:0.3});new Effect.Appear('modules',{duration:0.4});">
						<img src="../images/icons/silk/cross.png" class="mr5" /><span>Abort</span>
					</button>
					<img src="../images/ajax-loading.gif" style="display:none;" class="ml5 vam" id="add_loading" />
				</div>
			</fieldset>
		</form>
	</div>

	<div id="module_toolbar">
		<button type="button" class="btn bold" onclick="$('module_toolbar').hide();$('modules').fade({duration:0.3});new Effect.SlideDown('add-new',{duration:0.4});"><img src="../images/icons/silk/brick_add.png" class="mr5" /><span>Install/update module</span></button>
		<button type="button" class="btn red" onclick="if (confirm('Are you sure you want to uninstall selected module(s)?\n\nAll data will be lost!')) { $('action').value='uninstall';$('modules_form').submit(); }"><img src="../images/icons/silk/brick_delete.png" class="mr5" /><span>Uninstall selected module</span></button>

		<button type="button" class="btn ml15" onclick="$('action').value='activate';$('modules_form').submit();"><img src="../images/icons/silk/bullet_green.png" class="mr5" /><span>Activate selected module</span></button>
		<button type="button" class="btn" onclick="if (confirm('Are you sure you want to deactivate selected module(s)?')) { $('action').value='deactivate';$('modules_form').submit(); }"><img src="../images/icons/silk/bullet_red.png" class="mr5" /><span>Deactivate selected module</span></button>

		<img src="../images/ajax-loading.gif" style="margin-top:1px;margin-left:5px;display:none;" id="toolbar_loading" />
	</div>

	<div id="modules">
		[{include file="pages/modules_list.tpl"}]
	</div>
</div>