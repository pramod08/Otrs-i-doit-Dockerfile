<div class="p10">

<h2>[{isys type="lang" ident="LC__TEMPLATES__NEW_TEMPLATE"}]</h2>

<div>

 	<table class="p5" cellspacing="4">
		<tr>
			<td>[{isys type="lang" ident="LC__CMDB__OBJTYPE"}] wählen:</td>
			<td>
				[{assign var="objtype" value=$smarty.get.objTypeID}]
				[{isys type="f_dialog" p_bInfoIconSpacer="0" p_bDbFieldNN=0 status=0 exclude="C__OBJTYPE__CONTAINER;C__OBJTYPE__LOCATION_GENERIC;C__OBJTYPE__RELATION" p_strSelectedID=$objtype p_bEditMode=1 p_strTable="isys_obj_type" id="object_type" sort=true name="object_type"}]
			</td>
		</tr>
		<tr>
			<td colspan="2"></td>
		</tr>
		<tr>
			<td colspan="2">
				[{isys type="f_button" id="create_template" p_onClick="obj_create($('object_type').value);" p_strClass="disabled" p_strValue="LC__TEMPLATE__CREATE_NEW_TEMPLATE" p_bEditMode="1"}]
			</td>
		</tr>
	</table>

</div>

</div>

<script type="text/javascript">

    $('object_type').on('change', function(){
        if(this.value != -1)
        {
            $('create_template').removeClassName('disabled');
        }
        else
        {
            $('create_template').addClassName('disabled');
        }
    });

	function obj_create(p_obj_id) {

        if(p_obj_id == -1) return false;

		document.isys_form.template.value='1';
		document.isys_form.navMode.value='[{$smarty.const.C__NAVMODE__NEW}]';
		$('isys_form').action = '?' + C__CMDB__GET__VIEWMODE + '=1001&' + C__CMDB__GET__OBJECTTYPE + '=' + p_obj_id;
		$('isys_form').submit();
	}
</script>