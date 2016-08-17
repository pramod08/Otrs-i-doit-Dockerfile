<script type="text/javascript">[{include file="modules/templates/templates.js"}]</script>

<h2 class="p5 gradient text-shadow border-bottom mb10">[{isys type="lang" ident="LC__TEMPLATES__NEW_OBJECT_FROM_TEMPLATE"}]</h2>

<div class="p10">

	<h3 class="mb5">1. [{isys type="lang" ident="LC__TEMPLATES__SELECT_TITLE"}]</h3>

	<table cellpadding="4" cellspacing="5">
		<tr>
			<td class="strong"><label for="object_title">[{isys type="lang" ident="LC__TASK__TITLE"}]:</label></td>
			<td>
				[{isys type="f_text" p_bInfoIconSpacer="1" p_bEditMode="1" name="object_title" id="object_title" p_strValue=""}]
			</td>
		</tr>
		<tr>
			<td class="strong"><label for="object_type">[{isys type="lang" ident="LC__CMDB__OBJTYPE"}]:</label></td>
			<td>
				[{assign var="objtype" value=$smarty.get.objTypeID}]
				[{isys type="f_dialog" p_bInfoIconSpacer="1" p_bDbFieldNN=0 status=0 exclude="C__OBJTYPE__CONTAINER;C__OBJTYPE__LOCATION_GENERIC;C__OBJTYPE__GENERIC_TEMPLATE;C__OBJTYPE__RELATION" p_strSelectedID=$objtype p_bEditMode=1 p_strTable="isys_obj_type" id="object_type" sort=true name="object_type"}]
			</td>
		</tr>
		[{isys type="f_title_suffix_counter" p_bEditMode="1" name="C__TEMPLATE__SUFFIX" title_identifier="object_title" label_counter="LC__CMDB__CATG__QUANTITY"}]
		<tr>
			<td class="key"><label for="purpose">[{isys type="lang" ident="LC__CMDB__CATG__GLOBAL_PURPOSE"}]:</label></td>
			<td class="value">
				[{isys type="f_popup" p_bInfoIconSpacer="1" p_bEditMode=1 p_strPopupType="dialog_plus" p_strTable="isys_purpose" name="purpose" id="purpose" tab="3"}]
			</td>
		</tr>
		<tr>
			<td class="key"><label for="category">[{isys type="lang" ident="LC__CMDB__CATG__GLOBAL_CATEGORY"}]:</label></td>
			<td class="value">
				[{isys type="f_popup" p_bInfoIconSpacer="1" p_bEditMode=1 p_strPopupType="dialog_plus" p_strTable="isys_catg_global_category" name="category" id="category" tab="4"}]
			</td>
		</tr>
	</table>

	<h3 class="mb5">2. [{isys type="lang" ident="LC__TEMPLATES__SELECT_TEMPLATES"}]</h3>

	[{isys type="f_dialog" name="templates_id" id=template_id p_bEditMode="1" p_arData=$templates p_strSelectedID=$smarty.post.template_id}]

	[{isys type="f_button" p_onClick="select_template($('template_id'));" p_strValue="LC__TEMPLATES__USE" p_bEditMode="1"}]

	<div class="container ml5 mt5" id="selected_templates">
		<h3 class="mb5">[{isys type="lang" ident="LC__UNIVERSAL__SELECTED"}] Templates (<span id="sel_count">0</span>)</h3>

		<div class="sortable p5">
			<ul id="template_list"></ul>
		</div>
	</div>

	<div class="cb mb5"></div>

	<div id="step2">
		<h3 class="mb5">3. [{isys type="lang" ident="LC__UNIVERSAL__CREATE_OBJECT"}]</h3>

		<button name="create_template" type="submit" id="create_template" class="btn disabled" style="margin-right:5px;" value="1" disabled>
			<span>[{isys type="lang" ident="LC__TEMPLATES__OBJECT_FROM_SELECTED_TEMPLATES"}]</span>
		</button>

		<img style="display:none;" id="tpl_loader" class="fl mr5" src="images/ajax-loading.gif" />

	</div>

	<iframe id="iframe" name="iframe" src="" class="mt10 border" style="width:50%;height:250px;display:none;"></iframe>
</div>

<script type="text/javascript">
	(function() {
		"use strict";

		var $obj_type = $('object_type'),
			$create_tpl = $('create_template');

		$('object_title').focus();

		$('isys_form').writeAttribute('target', 'iframe');

		if($create_tpl) {
			$create_tpl.on('click', function (ev) {
				$('tpl_loader').show();
				$('iframe').appear();
			});
		}

		if($obj_type) {
			$obj_type.on('change', function(){
				if(this.value != -1)
				{
					$create_tpl.removeClassName('disabled');
					$create_tpl.removeAttribute('disabled');
				}
				else
				{
					$create_tpl.addClassName('disabled');
					$create_tpl.writeAttribute('disabled');
				}
			});
			$obj_type.simulate('change');
		}
	})();
</script>