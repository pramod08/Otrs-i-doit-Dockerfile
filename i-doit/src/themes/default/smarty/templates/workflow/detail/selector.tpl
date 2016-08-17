<script type="text/javascript">
	function load_workflow_data(p_workflow_type, p_disable_field) {
		var l_url_ajax_request = "[{$g_url}]&[{$smarty.const.C__GET__AJAX_REQUEST}]=1&[{$smarty.const.C__WF__GET__TYPE}]="+p_workflow_type+"&[{$smarty.const.C__CMDB__GET__EDITMODE}]=1";

		$('workflow_loading').show();
		$('workflow_content').update();

		new Ajax.Updater(
			'workflow_content',
			l_url_ajax_request,
			{
				onSuccess:function() {
					var e = $('workflow_content');
					if (e) {
						$('workflow_loading').hide();
						if (p_disable_field) {
							$('workflow_type').disabled = 'disabled';
						}
					}
				},
				evalScripts:true,
				evalJS:true,
				method:'get'
			});

	}

	function exception_switch(p_action) {
		(p_action==1)?l_disable='true':l_disable='';
		for(i=0;i<=6;i++) {
			if (document.getElementsByName('C__TASK__DETAIL_WORKORDER__EXCEPTION['+i+']')[0]) {
				document.getElementsByName('C__TASK__DETAIL_WORKORDER__EXCEPTION['+i+']')[0].disabled=l_disable;
			}
		}
	}

	function submit_workflow(oInput) {

		var l_form  = $('isys_form');
		var l_error = 0;
		var m		= 0;

		l_form.getElements().each(function(o) {
			if(o.name) {
                var check = o.getAttribute("check") || o.check;
                var ele = o;

                if(ele.hasAttribute("data-hidden-field"))
                {
                    ele = $(ele.getAttribute("data-hidden-field"));
                }

                if (check == "1" && (ele.value == "" || ele.value == "-1")) {
                    if (m++ < 1) {
                        o.focus();
                    }
                    new Effect.Morph(o, {style:'background-color:#ff9999;',duration:0.4});

                    l_error = 1;
                } else {
                    o.style.backgroundColor = "";
                }
			}
		});

		return l_error <= 0;
	}
</script>

<div id="workflow">

	<h2 class="border-bottom gradient p5 text-shadow">[{isys type="lang" ident="LC_WORKFLOW__METADATA"}]:</h2>

	<input type="hidden" name="C__ACTION_TYPE__ID" value="[{$g_action_type|default:"1"}]" />

	<div id="workflow_meta">
		<table cellspacing="0" cellpadding="0" class="contentTable" style="border-top: none;">
			<tr>
				<td class="key"><label for="C__WF__TITLE">[{isys type="lang" ident="LC__TASK__DETAIL__WORKORDER__TITLE"}]</label></td>
				<td class="value">[{isys type="f_text" name="C__WF__TITLE" p_strValue="" p_additional="check=1"}]</td>
			</tr>
			<tr>
				<td class="key"><label for="C__WF__CATEGORY">[{isys type="lang" ident="LC__TASK__DETAIL__WORKORDER__CATEGORY"}]</label></td>
				<td class="value">[{isys type="f_popup" p_strPopupType="dialog_plus" name="C__WF__CATEGORY" p_strTable="isys_workflow_category" p_bDbFieldNN="0" tab=""}]</td>
			</tr>
			<tr>
				<td class="key"><label for="C__WF__AUTHOR">[{isys type="lang" ident="LC__TASK__DETAIL__WORKORDER__INITIATOR"}]</label></td>
				<td class="value">
					[{isys type="f_text" p_bDisabled="1" p_strName="C__WF__AUTHOR__NAME" p_strValue=$g_user_name}]
					<input type="hidden" name="C__WF__AUTHOR" value="[{$g_current_user__id}]" />
				</td>
			</tr>
			<tr>
				<td class="key"><label for="C__WF__PARENT_WORKFLOW">[{isys type="lang" ident="LC__TASK__DETAIL__WORKORDER__PARENT_WORKFLOW"}]</label></td>
				<td class="value">[{isys type="f_dialog" p_arData=$workflow_list name="C__WF__PARENT_WORKFLOW"}]</td>
			</tr>
			<tr>
				<td class="key"><label for="contact_to">[{isys type="lang" ident="LC__CMDB__CATG__CONTACT"}]<bold class="red">*</bold></label></td>
				<td class="value">
					[{isys
						title="LC__BROWSER__TITLE__CONTACT"
						name="contact_to"
						type="f_popup"
						p_strPopupType="browser_object_ng"
						catFilter='C__CATS__PERSON;C__CATS__PERSON_GROUP;C__CATS__ORGANIZATION'
						multiselection="true"
						p_additional="check=1"
						p_strValue=$smarty.get.contact_preselection
					}]
				</td>
			</tr>
			<tr>
				<td class="key"><label for="f_object">[{isys type="lang" ident="LC__TASK__DETAIL__WORKORDER__OBJECT"}]<bold class="red">*</bold></label></td>
				<td class="value">
					[{isys type="f_popup" p_strPopupType="browser_object_ng" name="f_object" multiselection="true" p_additional="check=1" p_strValue=$smarty.get.object_preselection}]
				</td>
			</tr>
		</table>
	</div>

	<h2 class="border-top border-bottom gradient p5 text-shadow mt10">[{isys type="lang" ident="LC__WORKFLOW__SELECT_TYPE"}]:</strong></h2>
	<div id="selection">
		<table cellspacing="0" cellpadding="0" class="contentTable" style="border-top: none;">
			<tr>
				<td class="key">
					<input type="hidden" name="C__WF__ACTION" value="[{$smarty.const.C__WORKFLOW__ACTION__TYPE__NEW}]" />
					<label for="workflow_type">[{isys type="lang" ident="LC__CATG__RACK_TYPE"}]</label>
				</td>
				<td class="value">
					[{isys type="f_dialog" p_onChange="load_workflow_data(this.value, false);" p_additional="check=1" p_arData=$workflow_types name="workflow_type" p_strSelectedID=$g_workflow_type}]
				</td>
			</tr>
		</table>
	</div>

	<h2 class="border-top border-bottom gradient p5 text-shadow mt10">[{isys type="lang" ident="LC_WORKFLOW__PARAMETER"}]:</h2>

	<div id="workflow_loading" style="border:1px solid #B7B7B7; border-top:none; display:none; background:#eee;" class="p5">
		<img src="[{$dir_images}]/ajax-loading.gif" class="vam mr5" /> <span class="vam">[{isys type="lang" ident="LC__UNIVERSAL__LOADING"}]</span>
	</div>

	<div id="workflow_content"></div>
</div>

[{if !empty($g_workflow_type)}]
<script language="JavaScript" type="text/javascript">
	load_workflow_data([{$g_workflow_type}], true);
</script>
[{/if}]