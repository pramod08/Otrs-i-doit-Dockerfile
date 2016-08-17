<div>
	<button type="button" class="btn bold" onclick="$('mandators').fade({duration:0.3});new Effect.SlideDown('add-new',{duration:0.4});"><img src="../images/icons/silk/add.png" class="mr5" /><span>Add new tenant</span></button>
	<button type="button" class="btn bold ml15" onclick="edit_mandator();"><img src="../images/icons/silk/pencil.png" class="mr5" /><span>Edit</span></button>
	<button type="button" class="btn bold" onclick="submit_mandators('activate');"><img src="../images/icons/silk/bullet_green.png" class="mr5" /><span>Activate</span></button>
	<button type="button" class="btn bold" onclick="submit_mandators('deactivate');"><img src="../images/icons/silk/bullet_red.png" class="mr5" /><span>Deactivate</span></button>
	<button type="button" class="btn bold ml15" onclick="delete_mandators();"><img src="../images/icons/silk/delete.png" class="mr5" /><span>Remove</span></button>

	<img src="../images/ajax-loading.gif" style="margin-top:1px;margin-left:5px;display:none;" id="toolbar_loading" />
</div>

<hr class="separator" />

<table cellpadding="2" cellspacing="0" width="100%" class="sortable mt10" id="list">
	<colgroup>
		<col width="30" />
		<col width="30" />
		<col width="100" />
		<col width="400" />
		<col width="100" />
	</colgroup>
	<thead>
		<tr>
			<th>&nbsp;[ ]</th>
			<th>ID</th>
			<th>Tenant Name</th>
			<th>Database Name</th>
			<th>Database Host</th>
			<th>Active</th>
		</tr>
	</thead>
	<tbody>
	[{while $row = $mandators->get_row()}]
		<tr class="[{cycle values="even,odd"}]">
			<td><input type="checkbox" name="id[]" value="[{$row.isys_mandator__id}]" /></td>
			<td>[{$row.isys_mandator__id}]</td>
			<td>[{$row.isys_mandator__title}]</td>
			<td class="bold">[{$row.isys_mandator__db_name}]</td>
			<td>[{$row.isys_mandator__db_host}]</td>
			<td>[{if $row.isys_mandator__active}]<span class="green">Yes[{else}]<span class="red">No[{/if}]</span></td>
		</tr>
	[{/while}]
	</tbody>
</table>