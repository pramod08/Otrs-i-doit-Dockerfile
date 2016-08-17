<div class="m5">
	<p class="mb5">[{isys type="lang" ident="LC__MODULE__CHECK_MK__TAG_GUI__GENERIC_DESCRIPTION" p_bHtmlEncode=false}]</p>
	<p class="mb15">[{isys type="lang" ident="LC__MODULE__CHECK_MK__TAG_GUI__GENERIC_EXAMPLE_1" p_bHtmlEncode=false}]</p>

	<p class="mb5">[{isys type="lang" ident="LC__MODULE__CHECK_MK__TAG_GUI__GENERIC_DESCRIPTION_2" p_bHtmlEncode=false}]</p>
	<p class="mb15">[{isys type="lang" ident="LC__MODULE__CHECK_MK__TAG_GUI__GENERIC_EXAMPLE_2" p_bHtmlEncode=false}]</p>
</div>

<fieldset class="overview mb20">
	<legend><span>[{isys type="lang" ident="LC__MODULE__CHECK_MK__TAG_GUI__GLOBAL_DEFINITION"}]</span></legend>

	<div class="p10">
		<table class="contentTable">
			<tr>
				<td class="key">[{isys type="f_label" ident="LC__MODULE__CHECK_MK__TAG_GUI__GENERIC_LOCATION_EXPORT" name="generic_location"}]</td>
				<td class="value">[{isys type="f_dialog" name="generic_location" p_strClass="input-mini export-generic-location" p_bDbFieldNN=true}]</td>
			</tr>
			<tr>
				<td class="key">[{isys type="f_label" ident="LC__MODULE__CHECK_MK__TAG_GUI__GENERIC_LOCATION_OBJ_TYPE" name="generic_location_obj_type"}]</td>
				<td class="value">[{isys type="f_dialog" name="generic_location_obj_type" p_strClass="input-small generic-location-object-type"}]</td>
			</tr>
		</table>

		[{isys type="f_property_selector" name="generic_tag_properties" preselection=$preselection provide=$smarty.const.C__PROPERTY__PROVIDES__REPORT p_bInfoIconSpacer=0 allowed_property_types='["dialog","dialog_plus"]'}]
	</div>
</fieldset>

<fieldset class="overview">
	<legend><span>[{isys type="lang" ident="LC__MODULE__CHECK_MK__TAG_GUI__CATEGORY_DEFINITION"}]</span></legend>

	<div class="p10">
	[{foreach $check_mk_obj_types as $obj_type_const => $obj_type}]
		<div class="gradient border p5 bold obj-type-opener mouse-pointer">
			<img src="[{$dir_images}]icons/silk/bullet_arrow_right.png" class="vam" />
			<span class="vam">[{$obj_type.title}]</span>
		</div>
		<div class="border p5 hide obj-type-config" style="border-top: none;" data-obj-type-const="[{$obj_type_const}]">
			<table class="contentTable mb5">
				<tr>
					<td class="key">[{isys type="f_label" ident="LC__MODULE__CHECK_MK__TAG_GUI__OVERWRITE_GLOBAL_DEFINITION" name="overwrite_global_$obj_type_const"}]</td>
					<td class="value">[{isys type="f_dialog" name="overwrite_global_$obj_type_const" p_strClass="input-mini" p_bDbFieldNN=true}]</td>
				</tr>
				<tr>
					<td class="key">[{isys type="f_label" ident="LC__MODULE__CHECK_MK__TAG_GUI__GENERIC_LOCATION_EXPORT" name="generic_location_$obj_type_const"}]</td>
					<td class="value">[{isys type="f_dialog" name="generic_location_$obj_type_const" p_strClass="input-mini export-generic-location" p_bDbFieldNN=true}]</td>
				</tr>
				<tr>
					<td class="key">[{isys type="f_label" ident="LC__MODULE__CHECK_MK__TAG_GUI__GENERIC_LOCATION_OBJ_TYPE" name="generic_location_obj_type_$obj_type_const"}]</td>
					<td class="value">[{isys type="f_dialog" name="generic_location_obj_type_$obj_type_const" p_strClass="input-small generic-location-object-type"}]</td>
				</tr>
			</table>

			[{isys type="f_property_selector" name="generic_tag_properties_$obj_type_const" obj_type_id=$obj_type.id preselection=$obj_type.preselection provide=$smarty.const.C__PROPERTY__PROVIDES__REPORT p_bInfoIconSpacer=0 allowed_property_types='["dialog","dialog_plus"]'}]
		</div>
	[{/foreach}]
	</div>
</fieldset>

<script type="text/javascript">
	(function () {
		"use strict";

		var $cmdbTagTab = $('check_mk-cmdb-tags'),
			$objTypeOpener = $cmdbTagTab.down('div.obj-type-opener');

		$cmdbTagTab.select('div.obj-type-opener').invoke('on', 'click', function () {
			var hidden = this.next().toggleClassName('hide').hasClassName('hide');

			this.down('img').writeAttribute('src', '[{$dir_images}]icons/silk/bullet_arrow_' + (hidden ? 'right' : 'down') + '.png')
		});

		$cmdbTagTab.select('.export-generic-location').invoke('on', 'change', function (ev) {
			var $select = ev.findElement('select'),
				$objTypeSelection = $select.up('table').down('.generic-location-object-type');

			if ($select.getValue() == '0') {
				$objTypeSelection.disable();
			} else {
				$objTypeSelection.enable();
			}
		});

		$cmdbTagTab.select('.export-generic-location').invoke('simulate', 'change');

		if ($objTypeOpener) {
			$objTypeOpener.setStyle({borderTopWidth: '1px'});
		}
	}());
</script>

<style type="text/css">
	div.obj-type-opener {
		border-top-width: 0;
	}
</style>