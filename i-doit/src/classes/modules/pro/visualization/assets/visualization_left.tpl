<!-- We need #tree_content so that clicking on object type groups will still work! -->
<div id="tree_content" style="height:100%;">
	<div id="C_VISUALIZATION_LEFT">
		<div id="C_VISUALIZATION_LEFT_HEADER">
			<h3 class="text-shadow ml5">
				<img src="[{$dir_images}]icons/silk/chart_organisation.png" class="vam mr5" />
				<span>[{isys type="lang" ident="LC__MODULE__CMDB__VISUALIZATION__OBJECT_INFORMATION"}]</span>
			</h3>
		</div>
		<div id="C_VISUALIZATION_LEFT_CONTENT">

		</div>

		<div id="C_VISUALIZATION_LEFT_FUNCTIONS">
			<h3 class="gradient p5 border-top border-bottom">[{isys type="lang" ident="LC__MODULE__CMDB__VISUALIZATION__FUNCTIONS"}]</h3>
			<div class="m5"><!-- Will be filled when clicking on a object --></div>
		</div>

		<div id="C_VISUALIZATION_LEFT_LEGEND">
			<h3 class="gradient p5 border-top border-bottom">
				<input type="checkbox" class="obj-type-filter toggle-all fr ml5" style="margin-right:18px;" checked="checked" />
				<img src="[{$dir_images}]icons/eye.png" class="fr" title="[{isys type="lang" ident="LC__MODULE__CMDB__VISUALIZATION__FILTER_ALL"}]" />
				<span>[{isys type="lang" ident="LC__MODULE__CMDB__VISUALIZATION__LEGEND"}]</span>
			</h3>
			<ul>
			[{foreach $object_types as $id => $object_type}]
				<li data-obj-type-id="[{$id}]">
					<input type="checkbox" class="obj-type-filter fr" id="obj-type-filter-[{$id}]" [{if !$object_type.filtered}]checked="checked"[{/if}] />

					<div>
						<div class="cmdb-marker mr5" style="background:[{$object_type.color}];"></div>
						<img src="[{$object_type.icon}]" class="vam mr5" />
						<label for="obj-type-filter-[{$id}]">[{$object_type.title}]</label>
					</div>
				</li>
			[{/foreach}]
			</ul>
		</div>
	</div>
</div>