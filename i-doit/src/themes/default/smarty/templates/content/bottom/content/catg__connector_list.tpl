<table width="100%" class="gradient text-shadow border-bottom">
	<colgroup>
		<col width="51%" />
	</colgroup>
	<tr>
		<td><h3 class="m5">[{isys type="lang" ident="LC__CMDB__CATG__CONNECTOR__FRONT"}]</h3></td>
		<td><h3 class="m5">[{isys type="lang" ident="LC__CMDB__CATG__CONNECTOR__BACK"}]</h3></td>
	</tr>
</table>

[{if $inputs->num_rows() > 0}]

	[{while $l_row = $inputs->get_row()}]
		[{assign var="row" value=$list_dao->modify_row($l_row)}]
		[{assign var="l_sibling_id" value=$l_row.isys_catg_connector_list__id}]
		[{assign var="siblings" value=$dao_connector->get_data_by_sibling($l_sibling_id, $smarty.session.cRecStatusListView, isys_glob_get_param("sort"), isys_glob_get_param("dir"))}]

		[{cycle values="CMDBListElementsEven,CMDBListElementsOdd" print=false clear=true}]

		<div class="connector p10">
			<table id="inputs" cellpadding="0" cellspacing="0" width="100%">
				<colgroup>
					<col width="48%" />
					<col width="3%" />

				</colgroup>
				<tr>

				<td class="border" style="vertical-align: top;">

					<table cellspacing="0" cellpadding="0" class="mainTable">
						<colgroup>
						</colgroup>
						<thead>
							<tr>
								[{counter assign="counter"}]
								<th><input class="check_input" type="checkbox" onClick="CheckAllBoxes(this, 'check_input');" value="X" /></th>

								[{foreach from=$list_dao->get_fields() item="header" key="header_key"}]
								<th title="[{isys type="lang" ident="LC__UNIVERSAL__SORT"}]">
									<a href="javascript:" onclick="document.isys_form.dir.value='[{$list_dao->get_order()}]'; document.isys_form.sort.value='[{$header_key}]'; form_submit();">[{$header}]</a>
								</th>
								[{/foreach}]
							</tr>
						</thead>
						<tbody>


						<tr class="[{cycle}]">
							<td><input type="checkbox" class="checkbox check_input" name="id[]" value="[{$row.isys_catg_connector_list__id}]" /></td>

							[{foreach from=$list_dao->get_fields() item="header" key="header_key"}]
							<td onclick="document.location='[{$conn_link}]&cateID=[{$row.isys_catg_connector_list__id}]'">[{$row.$header_key}]</td>
							[{/foreach}]

						</tr>

						</tbody>
					</table>

				</td>
				<td>

					<div class="dash"></div>

				</td>

				<td class="border" style="background-color: #ccc; vertical-align: top;">

					[{if $siblings->num_rows() > 0}]

						<table class="mainTable" cellpadding="0" cellspacing="0">
								<thead>
								<tr>
									[{counter assign="counter"}]
									<th><input class="check_output" type="checkbox" onClick="CheckAllBoxes(this, 'check_output');" value="X" /></th>

									[{foreach from=$list_dao->get_fields() item="header" key="header_key"}]
									<th title="[{isys type="lang" ident="LC__UNIVERSAL__SORT"}]">
										<a href="javascript:"
										onclick="	document.isys_form.dir.value='[{$list_dao->get_order()}]';
											        document.isys_form.sort.value='[{$header_key}]';
													form_submit();">[{$header}]</a>
									</th>
									[{/foreach}]

								</tr>
							</thead>
							<tbody>

							[{cycle values="CMDBListElementsOdd,CMDBListElementsEven" print=false clear=true}]

							[{while $row = $siblings->get_row()}]
								[{assign var="row" value=$list_dao->modify_row($row)}]

								<tr class="[{cycle}]">
									<td><input type="checkbox" class="checkbox check_output" name="id[]" value="[{$row.isys_catg_connector_list__id}]" /></td>

									[{foreach from=$list_dao->get_fields() item="header" key="header_key"}]
									<td onclick="document.location='[{$conn_link}]&cateID=[{$row.isys_catg_connector_list__id}]'">[{$row.$header_key}]</td>
									[{/foreach}]

								</tr>

							[{/while}]
						</tbody>
						</table>
					[{else}]

					<h3 class="p10 text-shadow">[{isys type="lang" ident="LC__UNIVERSAL__UNASSIGNED"}]</h3>

					[{/if}]
				</td>
				</tr>
			</table>
		</div>
		[{/while}]
[{/if}]

[{if $outputs->num_rows() > 0}]
	<div class="connector m10">
		<table id="outputs" cellpadding="0" cellspacing="0" width="100%">
			<colgroup>
				<col width="48%" />
				<col width="3%" />
			</colgroup>
			</tr>
			<tr>
			<td class="border" style="background-color: #ccc; vertical-align: top;">
				<h3 class="p10 text-shadow">[{isys type="lang" ident="LC__UNIVERSAL__UNASSIGNED"}]</h3>
			</td>
			<td>
				<div class="dash"></div>
			</td>
			<td class="border" style="vertical-align: top;">

				<table class="mainTable" cellpadding="0" cellspacing="0">
					<thead>
						<tr>
							[{counter assign="counter"}]
							<th><input class="check_output" type="checkbox" onClick="CheckAllBoxes(this, 'check_output');" value="X" /></th>

							[{foreach from=$list_dao->get_fields() item="header" key="header_key"}]
							<th title="[{isys type="lang" ident="LC__UNIVERSAL__SORT"}]">
								<a href="javascript:"
								onclick="	document.isys_form.dir.value='[{$list_dao->get_order()}]';
									 		document.isys_form.sort.value='[{$header_key}]';
											form_submit();">[{$header}]

								[{if $smarty.post.sort eq $header_key}]
								<img src="images/[{$smarty.post.dir|lower|default:"desc"}].png" height="10" border="0" />
								[{/if}]
								</a>
							</th>
							[{/foreach}]

						</tr>
					</thead>
					<tbody>
						[{cycle values="CMDBListElementsEven,CMDBListElementsOdd" print=false clear=true}]
						[{while $row = $outputs->get_row()}]
						[{assign var="row" value=$list_dao->modify_row($row)}]

						<tr class="[{cycle}]">

							<td><input type="checkbox" class="checkbox check_output" name="id[]" value="[{$row.isys_catg_connector_list__id}]" /></td>

							[{foreach from=$list_dao->get_fields() item="header" key="header_key"}]
							<td onclick="document.location='[{$conn_link}]&cateID=[{$row.isys_catg_connector_list__id}]'">[{$row.$header_key}]</td>
							[{/foreach}]

						</tr>
						[{/while}]
					</tbody>
				</table>
			</td>
			</tr>
		</table>
	</div>
[{/if}]


[{if $inputs->num_rows() <=0 && $outputs->num_rows() <= 0}]

<h3 class="p10">[{isys type="lang" ident="LC__CATG__CONNECTOR__NO_CONNECTORS"}]</h3>

[{/if}]