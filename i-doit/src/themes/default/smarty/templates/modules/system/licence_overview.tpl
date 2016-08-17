<div id="system-licence-overview">
	<h2 class="p10 header gradient text-shadow">[{isys type="lang" ident="LC__UNIVERSAL__LICENE_OVERVIEW"}]</h2>

	<div class="p5 fl">
		<table class="mainTable border">
			<colgroup>
				<col width="160" />
				<col width="100" />
			</colgroup>
			<thead>
				<tr>
					<th>[{isys type="lang" ident="LC__UNIVERTSAL__QUERY"}]</th>
					<th>[{isys type="lang" ident="LC__LICENCE_OVERVIEW__CURRENT_VALUE"}]</th>
					<th>[{isys type="lang" ident="LC__LICENCE_OVERVIEW__LICENCE_EXCEEDING"}]</th>
				</tr>
			</thead>
			<tbody>
				<tr class="CMDBListElementsOdd">
					<td><strong>[{isys type="lang" ident="LC__LICENCE__DOCUMENTED_OBJECTS"}]</strong></td>
					<td>[{$stat_counts.objects}]</td>
					<td><span class="[{$exceeding.objects_class|default:"green"}]">[{$exceeding.objects}]</span></td>
				</tr>
				<tr class="CMDBListElementsEven">
					<td><strong>[{isys type="lang" ident="LC__LICENCE__FREE_OBJECTS"}]</strong></td>
					<td[{if $stat_counts.free_objects eq 0}] class="red bold"[{/if}]>[{$stat_counts.free_objects}]</td>
					<td><span class="[{$exceeding.objects_class|default:"green"}]">[{$exceeding.objects}]</span></td>
				</tr>
			</tbody>
		</table>

		[{if isset($note)}]
		<div class="note p5 mt10">
			<span>[{$note}]</span>
		</div>
		[{/if}]

		[{if isset($error)}]
		<div class="exception p5 mt10" style="width:380px;">
			<span class="red bold">[{$error}]</span>
		</div>
		[{/if}]

		<p class="mt10" id="licence_toolbar">
			<button type="button" class="btn bold mr5" onclick="new Effect.SlideDown('object_count_detail',{duration:0.5});this.up().hide();">
				<span>[{isys type="lang" ident="LC__LICENCE_OVERVIEW__OBJECTCOUNT_BY_TYPE"}]</span>
			</button>
			<a class="btn bold" href="?moduleID=[{$smarty.get.moduleID}]&handle=licence_installation">
				<span>[{isys type="lang" ident="LC__LICENCE__INSTALL"}]</span>
			</a>
		</p>

		<div class="mt10 border" style="display:none" id="object_count_detail">
			<img src="[{$dir_images}]prototip/styles/default/close.png" class="fr mouse-pointer m5" onclick="new Effect.SlideUp('object_count_detail',{duration:0.3,afterFinish:function(){$('licence_toolbar').show();}});" />

			<h3 class="p5 gradient border-bottom text-shadow">[{isys type="lang" ident="LC__LICENCE__OBJECT_COUNTER"}]</h3>
			<table class="mainTable">
			<thead>
				<tr>
					<th>[{isys type="lang" ident="LC__REPORT__FORM__OBJECT_TYPE"}]</th>
					<th>[{isys type="lang" ident="LC__CMDB__CATG__QUANTITY"}]</th>
				</tr>
			</thead>
			[{foreach from=$stat_counts.objects_by_type item="ocount"}]
				<tr class="[{cycle values="CMDBListElementsOdd,CMDBListElementsEven"}]">
					<td><strong>[{$ocount.type}]</strong>:</td>
					<td>[{$ocount.count}]</td>
				</tr>
			[{/foreach}]
			</table>
		</div>
	</div>

	<div class="p5 fl">
		<table class="mainTable border">
			<colgroup>
				<col width="180" />
				<col width="200" />
			</colgroup>
			<thead>
			<tr style="border-bottom:1px solid #888;">
				<th>[{isys type="lang" ident="LC__LICENCE_OVERVIEW__STATISTIC"}]</th>
				<th>[{isys type="lang" ident="LC__LICENCE_OVERVIEW__CURRENT_VALUE"}]</th>
			</tr>
			</thead>
			<tbody></tbody>
			<tr class="CMDBListElementsOdd">
				<td><strong>[{isys type="lang" ident="LC__LICENCE_OVERVIEW__CMDB_REFERENCES"}]</strong></td>
				<td>[{$stat_counts.cmdb_references}]</td>
			</tr>
			<tr class="CMDBListElementsEven">
				<td><strong>[{isys type="lang" ident="LC__DASHBOAD__LAST_IDOIT_UPDATE"}]</strong></td>
				<td>[{$stat_stats.last_idoit_update}]</td>
			</tr>
			<tr class="CMDBListElementsOdd">
				<td><strong>Version</strong></td>
				<td>[{$gProductInfo.version}]</td>
			</tr>
			</tbody>
		</table>
	</div>

	<div class="cb"></div>

	<h2 class="mt5 p10 border-top header gradient text-shadow">[{isys type="lang" ident="LC__LICENCE__INSTALLED_LICENCES"}]</h2>

	<table class="mainTable">
		<thead>
			<tr>
				<th>[{isys type="lang" ident="LC__CATG__CONTACT_COMPANY"}]</th>
				<th>[{isys type="lang" ident="LC__CATG__CONTACT_EMAIL"}]</th>
				<th>[{isys type="lang" ident="LC__LICENCE__LICENCED_DATABASE"}]</th>
				<th>[{isys type="lang" ident="LC__LICENCE__LICENCE_TYPE"}]</th>
				<th>[{isys type="lang" ident="LC__LICENCE__MAX_AMOUNT_OF_OBJECTS"}]</th>
				<th>[{isys type="lang" ident="LC__LICENCE__REGISTRATION_DATE"}]</th>
				<th>[{isys type="lang" ident="LC__CMDB__CATS__LICENCE_EXPIRE"}]</th>
				<th>[{isys type="lang" ident="LC__SETTINGS__SYSTEM__OPTIONS"}]</th>
			</tr>
		</thead>
		<tbody>
			[{if count($licences) <= 0}]
				<tr>
					<td>
						<span>[{isys type="lang" ident="LC__LICENCE__CURRENTLY_NO_LICENCES"}]</span>
					</td>
				</tr>
			[{else}]
				[{foreach from=$licences item="licence"}]
				<tr class="[{cycle values="CMDBListElementsOdd,CMDBListElementsEven"}]">
					<td class="bold">[{$licence.organisation}]</td>
					<td>[{$licence.email}]</td>
					<td>[{$licence.database}]</td>
					<td class="underline">[{$licence.licencetype|ucfirst}]</td>
					<td class="green bold">[{if $licence.objcount eq 0}]Unlimitiert[{else}][{$licence.objcount}][{/if}]</td>
					<td>[{$licence.reg_date|date_format:"%Y-%m-%d"}]</td>
					<td [{if $licence.expires < $smarty.now}]class="red bold underline"[{/if}]>[{$licence.expires|date_format:"%Y-%m-%d"}]</td>
					<td>
						[{if $licence.type != $smarty.const.C__LICENCE_TYPE__HOSTING_SINGLE}]
						<button class="btn red bold" onclick="if (confirm('[{isys type="lang" ident="LC__LICENCE__REMOVE_CONFIRMATION"}]'))document.location='?moduleID=[{$smarty.get.moduleID}]&handle=licence_overview&id=[{$licence.id}]&delete';">
							<img src="[{$dir_images}]icons/silk/page_delete.png" class="mr5" /><span>[{isys type="lang" ident="LC__LICENCE__REMOVE"}]</span>
						</button>
						[{/if}]
					</td>
				</tr>
				[{/foreach}]
			[{/if}]
		</tbody>
	</table>
</div>

<style type="text/css">
	#system-licence-overview .mainTable td {
		padding: 5px;
	}
</style>