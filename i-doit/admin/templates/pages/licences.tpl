<div class="gradient content-header">
	<img src="../images/icons/silk/key.png" class="vam mr5" /><span class="bold text-shadow headline vam">Licenses</span>
</div>

<div id="innercontent">

	[{if isset($errorcode) || isset($error)}]
	<h4 class="mb5 m0">License error:</h4>
	<div class="error mb10">
		<table cellspacing="4">
			<colgroup><col width="100" /></colgroup>
			<tr>
				<td class="bold">Error-Code:</td>
				<td>[{$errorcode}]</td>
			</tr>
			<tr>
				<td class="bold">Error:</td>
				<td>[{$error}]</td>
			</tr>
		</table>
	</div>
	[{/if}]

	[{if isset($note)}]
	<div class="note p5">
		<span>[{$note}]</span>
	</div>
	[{/if}]

	<div id="add-new" class="mt10" style="display:none;">
		<form id="form" action="?req=licences&action=add" method="post" enctype="multipart/form-data">
			<input type="hidden" name="action" value="add" />
			<fieldset>
				<legend class="bold text-shadow">Add a new licence</legend>

				<p>
					<strong>Licence Type:</strong><br /><br />
					&nbsp;&nbsp;<label><input type="radio" name="licence_type" checked="checked" value="subscription" onclick="$('mandator_licence').show();" /> Subscription</label>
					&nbsp;&nbsp;<label><input type="radio" name="licence_type" value="hosting" onclick="$('mandator_licence').hide();" /> Multi-tenant</label>
					&nbsp;&nbsp;<label><input type="radio" name="licence_type" value="buyers" onclick="$('mandator_licence').show();" /> Buyers licence</label>
					&nbsp;&nbsp;<label><input type="radio" name="licence_type" value="buyers-hosting" onclick="$('mandator_licence').hide();" /> Multi-tenant buyers licence</label>
				</p>

				<table class="contentTable" width="100%">
					<colgroup><col width="160" /></colgroup>
					<tr id="mandator_licence">
						<td class="bold">Install into tenant:</td>
						<td>[{html_options name="mandator" options=$mandators}]</td>
					</tr>
					<tr>
						<td class="bold">Licence file:</td>
						<td>
						<input type="file" name="licence_file" />
						</td>
					</tr>
				</table>

				<div>
					<button type="button" class="btn" onclick="$('add_loading').show(); $('form').submit();">
						<img src="../images/icons/silk/add.png" class="mr5" /><span>Add licence</span>
					</button>
					<button type="button" class="btn" onclick="new Effect.SlideUp('add-new', {duration:0.3});new Effect.Appear('licences',{duration:0.4});">
						<img src="../images/icons/silk/cross.png" class="mr5" /><span>Abort</span>
					</button>
					<img src="../images/ajax-loading.gif" style="display:none;" class="ml5 vam" id="add_loading" />
				</div>

			</fieldset>
		</form>
	</div>

	<div id="licences">
		[{include file="pages/licence_list.tpl"}]
	</div>
</div>