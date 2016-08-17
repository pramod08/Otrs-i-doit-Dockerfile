<div class="browser-content listing" id="browser-content"">
	<ul id="cablingTabs" class="m0 browser-tabs gradient">
		<li>
			<a href="#tab1">[{isys type="lang" ident="LC__CATG__CONNECTOR__CABLERUN"}]: [{isys type="lang" ident="LC__UNIVERSAL__LIST"}]</a>
		</li>
		[{if count($connections) > 0}]
		<li>
			<a href="#tab2">[{isys type="lang" ident="LC__CATG__CONNECTOR__CABLERUN"}]: [{isys type="lang" ident="LC__UNIVERSAL__GRAPHICAL_VIEW"}]</a>
		</li>
		[{/if}]
		<li>
			<a href="#tab3">[{isys type="lang" ident="LC__CATG__CONNECTOR__CABLERUN"}]: [{isys type="lang" ident="LC__UNIVERSAL__TREEVIEW"}] ([{isys type="lang" ident="LC__UNIVERSAL__NESTING"}])</a>
		</li>
	</ul>

	<div id="tab1" class="p10" style="overflow-x:auto">

		<table class="listing" cellpadding="0" cellspacing="0">
			<colgroup>
					<col width="10%" />
			</colgroup>
			<thead>
				<tr>
					<th>[{isys type="lang" ident="LC__CATG__STORAGE_CONNECTION_TYPE"}]</th>
					<th>[{isys type="lang" ident="LC__CATG__CONNECTOR__CABLERUN"}]</th>
				</tr>
			</thead>
			<tbody>
			[{foreach from=$connections item=con}]
			[{assign var=cable_run value=$con.cable_run}]
				<tr class="[{cycle values="CMDBListElementsOdd,CMDBListElementsEven"}]">
					<td>
						[{$con.isys_catg_connector_list__title}]
					</td>
					<td>
						[{if !$con.cable_left && !$con.cable_right}]
							[{isys type="lang" ident="LC__CATG__CONNECTOR__NO_CABLERUN"}]
						[{else}]
							[{$con.cable_left}]
							[{$con.cable_right}]
						[{/if}]
					</td>
				</tr>
			[{/foreach}]
			</tbody>
		</table>
	</div>

	<div id="tab2" class="" style="overflow-x: auto;">
		[{if count($connections) > 0}]
			[{foreach from=$connections item=con}]
			[{assign var=cable_run value=$con.cable_run}]

				[{if !(!$con.cable_left && !$con.cable_right)}]
					[{counter assign=cnt print=false}]

					<h3 class="m0 p10">[{$con.isys_catg_connector_list__title}]</h3>

					<div id="horizontal_carousel_[{$cnt}]" class="horizontal_carousel">
						<div class="previous_button"></div>
							<div class="container">
							<ul>
								[{foreach from=$con.carousel item="cl" key="key"}]

									<li>

										<p class="m5">
											<a href="[{$cl.LINK}]">
												[{isys type="object_image" objType=$cl.OBJECT_TYPE objID=$cl.OBJECT_ID width="50" height="50" align="center" class="object"}]
											</a>
										</p>

										<span class="text-shadow-black[{if $con.isys_catg_connector_list__id eq $cl.CONNECTOR_ID}] bold[{/if}]">[{$cl.OBJECT_TITLE}] > [{$cl.CONNECTOR_TITLE}]</span>

									</li>
									<li style="height:100px;width:60px;">

										<p style="margin-top:25px;">
											[{if $key < count($con.carousel)-1}]
												[{isys type="cmdb_link" quickinfo="1" objID=$cl.CABLE_ID p_strValue="&rarr;" style="font-size:160%;color:#eee;"}]
											[{/if}]
										</p>
									</li>
								[{/foreach}]
							</ul>
							</div>
						<div class="next_button"></div>
					</div>
					<div class="cb"></div>
					<script type="text/javascript">
						new UI.Carousel($("horizontal_carousel_[{$cnt}]"));
					</script>
				[{/if}]
			[{/foreach}]
		[{else}]
			<br />
		[{/if}]
	</div>

	<div id="tab3" class="p10">
		<div class="toolbar">
			<a href="javascript:" onclick="cable_run.openAll();" class="fr m10 bold">+ Expand all</a>
		</div>

		<div id="cable_run" class="text-shadow p5" style="min-height:35px;overflow-x: auto;">-</div>
		[{$tree}]
	</div>
</div>

<script type="text/javascript">
	new Tabs('cablingTabs', {
		wrapperClass: 'browser-tabs',
		contentClass: 'browser-tab-content',
		tabClass: 'text-shadow'
	});

	$('cable_run').next().remove();
</script>