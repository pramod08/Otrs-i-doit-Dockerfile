<div>

	<h2 class="p10 header gradient text-shadow">
		Verinice Mapper
	</h2>

	<div class="p10">
		<h3>[{isys type="lang" ident="LC__UNIVERSAL__OBJECTTYPES_TO_EXPORT"}]:</h3>

		<table class="mainTable mt10" style="font-size:11px;" width="100%">
			<colgroup><col width="100" /></colgroup>
			<tr>
				<th>i-doit</th>
				<th>Verinice</th>
			</tr>
			[{foreach from=$types item="type"}]
			<tr class="[{cycle values="CMDBListElementsEven,CMDBListElementsOdd"}]">
				<td class="bold">[{isys type="lang" ident=$type.isys_obj_type__title}]</td>
				<td>[{isys type="lang" ident=$type.isys_verinice_types__title}] ([{$type.isys_verinice_types__const}])</td>
			</tr>
			[{/foreach}]
		</table>

		<p class="mt10">
			[{isys type="f_button" p_onClick="document.location = document.location + '&export'" name="v_export" id="v_export" p_strValue="Download Mapping" p_bEditMode=1}]

			[{isys type="lang" ident="LC__UNIVERSAL__EXTERNAL_DIRECTLINK"}]:
			<a id="v_link"></a>
		</p>
	</div>

</div>

<script type="text/javascript">
var link = (document.location + '&export').replace('index.php', '');
$('v_link').update(link).setAttribute('href', link);
</script>