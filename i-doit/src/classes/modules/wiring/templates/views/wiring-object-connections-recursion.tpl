<td>
	<table data-search="[{$c.search}]" data-object-id="[{$c.objID}]" class="innerWiring [{if $c.objID == 0}]hide[{/if}] [{if $c.objID === $current}]selected[{/if}]">
		<tr>
			<td class="port">
				[{if $c.port}]
					[{if $c.portType}]
						<div class="bullet bullet-right strcut center" data-tooltip="[{if $c.port}][{_L('LC__CATG__STORAGE_CONNECTION_TYPE')}]: [{$c.port}][{/if}]" style="background-color:[{$types[$c.portType]['color']}];">
							[{if $c.portID > 0}]<input type="checkbox" name="conn-[{$c.portID}]" />[{/if}]
							<span>[{$c.port}]</span>
						</div>
					[{else}]
						<div class="bullet bullet-right strcut center" data-tooltip="[{if $c.port}][{_L('LC__CATG__STORAGE_CONNECTION_TYPE')}]: [{$c.port}][{/if}]" style="background-color:darkred;">
							[{if $c.portID > 0}]<input type="checkbox" name="conn-[{$c.portID}]" />[{/if}]
							<span>[{$c.port}]</span>
						</div>
					[{/if}]
				[{/if}]
			</td>
			<td class="center bold text">
				<a class="strcut nowrap" data-tooltip="[{$c.locationPath}] ([{$c.objectType}])" href="[{$config.www_dir}]?objID=[{$c.objID}]">[{$c.object}]</a>
			</td>
			<td class="port">
				[{if $c.sibling}]
					[{if $c.siblingType}]
						<div class="bullet bullet-left strcut center" data-tooltip="[{if $c.sibling}][{_L('LC__CATG__STORAGE_CONNECTION_TYPE')}]: [{$c.sibling}][{/if}]" style="background-color:[{$types[$c.siblingType]['color']}];">
							[{if $c.siblingID > 0}]<input type="checkbox" name="conn-[{$c.siblingID}]" />[{/if}]
							<span>[{$c.sibling}]</span>
						</div>
					[{else}]
						<div class="bullet bullet-left strcut center" data-tooltip="[{if $c.sibling}][{_L('LC__CATG__STORAGE_CONNECTION_TYPE')}]: [{$c.sibling}][{/if}]" style="background-color:darkred;">
							[{if $c.siblingID > 0}]<input type="checkbox" name="conn-[{$c.siblingID}]" />[{/if}]
							<span>[{$c.sibling}]</span>
						</div>
					[{/if}]
				[{else}]
					<!-- Show empty bubble -->
					<div class="bullet bullet-left strcut center" style="background-color:#fff;opacity: .4;margin-left:-10px;">
						<span>n/a</span>
					</div>
				[{/if}]
			</td>
			[{if $c.sibling}]
				<td class="center cable strcut">
					<span><img src="[{$dir_images}]icons/silk/connect-rotated.png" alt="-" data-tooltip="[{if $c.cable}][{_L('LC__CMDB__OBJTYPE__CABLE')}]: [{$c.cable}][{/if}]" /></span>
					<span class="cable-label">[{$c.cable}]</span>
				</td>
			[{else}]
				<td class="center cable strcut">
					<span></span>
				</td>
			[{/if}]
		</tr>
	</table>
</td>