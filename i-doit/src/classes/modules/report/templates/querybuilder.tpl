[{include file="./style.css"}]

<div style="background: #fff;">
    <input type="hidden" name="report_id" id="report_id" value="[{$report_id}]">
	<div class="p10">
        <input type="hidden" name="queryBuilder" value="1">

		<div style="width:822px; border:1px solid #888;">
			<div class="m0 p10 gradient browser-tabs" style="height:15px; border-bottom:1px solid #888;">
				<span class="text-shadow"><b style="color:#333333; font-size:10px;">[{isys type="lang" ident="LC__REPORT__INFO__NAME_AND_DESCRIPTION"}]</b></span>
			</div>

			<div class="m10">
				<table>
					<tr>
						<td style="text-align: right">
                            [{isys type="f_label" name="title" ident="LC__REPORT__FORM__TITLE"}]
						</td>
						<td class="value">
                            [{isys type="f_text" name="title" id="title" p_strClass="reportInput" p_bEditMode=1 p_strStyle="width:400px;" p_strValue=$report_title}]
						</td>
					</tr>
                    <tr>
                        <td style="text-align: right">
                            [{isys type="f_label" name="report_category" ident="LC_UNIVERSAL__CATEGORY"}]
                        </td>
                        <td class="value">
                            [{isys type="f_dialog" p_bDbFieldNN=1 p_arData=$category_data p_strSelectedID=$category_selected name="report_category" id="report_category" p_strClass="reportInput" p_bEditMode=1 p_strStyle="width:400px;"}]
                        </td>
                    </tr>
					<tr>
						<td style="text-align: right">
                            [{isys type="f_label" name="description" ident="LC__REPORT__FORM__DESCRIPTION"}]
						</td>
						<td class="value">
                            [{isys type="f_textarea" name="description" p_nRows="5" p_strStyle="width:400px;" p_bEditMode=1 p_strValue=$report_description}]
						</td>
					</tr>
				</table>
			</div>
		</div>
	</div>

	<fieldset class="overview">
		<legend><span>[{isys type="lang" ident="LC__REPORT__FORM__OUTPUT"}]: <button type="button" class="btn btn-small checkup">[{isys type="lang" ident="LC__REPORT__FORM__CHECK"}]</button></span></legend>
		<div class="p10">
			<p class="mt10 mb10">[{isys type="lang" ident="LC__REPORT__INFO__ATTRIBUTE_CHOOSER_TEXT"}]</p>

			<table class="mt5 mb10">
				<tr>
					<td><label for="empty_values">[{isys type="lang" ident="LC__REPORT__FORM__SHOW_EMPTY_VALUES"}]</label></td>
					<td>[{isys type="f_dialog" p_bDbFieldNN=1 p_arData=$yes_or_no p_strSelectedID=$empty_values_selected p_bSort=false name="empty_values" p_strClass="input-mini" p_bEditMode=1}]</td>
				</tr>
				<tr>
					<td><label for="display_relations">[{isys type="lang" ident="LC__REPORT__FORM__DISPLAY_RELATION_OBJECTS"}]</label></td>
					<td>[{isys type="f_dialog" p_bDbFieldNN=1 p_arData=$yes_or_no p_strSelectedID=$display_relations_selected p_bSort=false name="display_relations" p_strClass="input-mini" p_bEditMode=1}]</td>
				</tr>
			</table>

			[{isys type="f_property_selector"
	            grouping=false
	            sortable=true
	            p_bInfoIconSpacer=0
	            p_bEditMode=true
	            name="report"
	            p_bInfoIcon=false
	            provide=$smarty.const.C__PROPERTY__PROVIDES__REPORT
	            p_consider_rights=true
	            custom_fields=true
	            report=true
	            preselection=$preselection_data
	            preselection_lvls=$preselection_lvls
	            replace_dynamic_properties=true}]

			<hr style="margin:20px 0;" />
		</div>
	</fieldset>


	<fieldset class="overview">
        <legend><span>[{isys type="lang" ident="LC__REPORT__FORM__CONDITIONS"}]: <button type="button" class="btn btn-small checkup">[{isys type="lang" ident="LC__REPORT__FORM__CHECK"}]</button></span></legend>

        <div id="condition_overlay" style="position:absolute; display:[{if $report_id == '' || $querybuilder_conditions == ''}]none[{/if}]; top:0; left:0; height:100%; width: 100%;">
            <div style="position:absolute; z-index: 1001; opacity: 0.4; background: #FFF; height:100%; width: 100%;">
            </div>
            <div class="mt10" style="position:absolute; left:50%; margin-left: -108px; text-align: center; z-index: 1100;">
                <span class="vam"><b>[{isys type="lang" ident="LC__REPORT__FORM__LOADING_CONDITIONS"}] </b><img src="[{$dir_images}]ajax-loading.gif" class="vam"/></span>
            </div>
        </div>

        <div id="ReportCondtionBlockTemplate" class="constraintDiv p10" style="display:none;min-width:805px;max-width: 58%">
            <span class="remove"></span>
            <span class="add"></span>
            <table id="ReportConditionTemplate" class="reportCondtionsTable">
                <tr>
                    <td>
                        <label class="reportLabel">[{isys type="lang" ident="LC__REPORT__FORM__CATEGORY"}]</label>
                    </td>
                    <td>
                        <select id="querycondition_#{queryConditionBlock}_#{queryConditionLvl}_category" class="reportDialog conditionCategory" name="querycondition[#{queryConditionBlock}][#{queryConditionLvl}][category]" size="1" style="width:275px;">
                        </select>
                    </td>
                </tr>
                <tr>
                    <td>
                        <label class="reportLabel">[{isys type="lang" ident="LC__REPORT__FORM__PROPERTY"}]</label>
                    </td>
                    <td>
                        <select id="querycondition_#{queryConditionBlock}_#{queryConditionLvl}_property" class="reportDialog2 conditionProperty" name="querycondition[#{queryConditionBlock}][#{queryConditionLvl}][property]" size="1" style="width:275px;">
                        </select>
                        <span id="ReportConditionTemplateValue">
                            <select id="querycondition_#{queryConditionBlock}_#{queryConditionLvl}_comparison" name="querycondition[#{queryConditionBlock}][#{queryConditionLvl}][comparison]" class="reportDialog2 conditionComparison" style="width:80px;">
                                <option value="=">=</option>
                                <option value="&lt;">&lt;</option>
                                <option value="&gt;">&gt;</option>
                                <option value="!=">!=</option>
                                <option value="&lt;=">&lt;=</option>
                                <option value="&gt;=">&gt;=</option>
                                <option value="LIKE">LIKE</option>
                                <option value="LIKE %...%">LIKE %...%</option>
                                <option value="NOT LIKE">NOT LIKE</option>
                                <option value="NOT LIKE %...%">NOT LIKE %...%</option>
                            </select>
                            <input id="querycondition_#{queryConditionBlock}_#{queryConditionLvl}_value" name="querycondition[#{queryConditionBlock}][#{queryConditionLvl}][value]" class="reportInput conditionValue" style="width:140px;">
                            <select id="querycondition_#{queryConditionBlock}_#{queryConditionLvl}_unit" name="querycondition[#{queryConditionBlock}][#{queryConditionLvl}][unit]" class="reportDialog2 conditionUnit" style="width:60px;display:none;" disabled></select>
                            <select id="ReportConditionTemplateOperator" name="querycondition[#{queryConditionBlock}][#{queryConditionLvl}][operator]" class="reportDialog2 conditionOperator" style="width:60px;display:none;">
                                <option value="AND">[{isys type="lang" ident="LC__UNIVERSAL__AND" p_func="strtoupper"}]</option>
                                <option value="OR">[{isys type="lang" ident="LC_UNIVERSAL__OR" p_func="strtoupper"}]</option>
                            </select>
                        </span>
                    </td>
                </tr>
                <tr style="display: none;" id="ReportConditionSubTemplateBlock" class="reportConditionsSubBlock">
                    <td colspan="2">
                        <div class="constraintSubDiv">
                            <span class="subremove"></span>
                            <span class="subadd"></span>
                            <table id="ReportConditionSubTemplate" class="reportCondtionsSubTable ml10 p10">
                                <tr>
                                    <td>
                                        <label class="reportLabel">[{isys type="lang" ident="LC__REPORT__FORM__CATEGORY"}]</label>
                                    </td>
                                    <td>
                                        <select id="querycondition_#{queryConditionBlock}_#{queryConditionLvl}_#{queryConditionSubLvl}_#{queryConditionSubLvlProp}_category" class="reportDialog conditionSubCategory" name="querycondition[#{queryConditionBlock}][#{queryConditionLvl}][subcnd][#{queryConditionSubLvlProp}][#{queryConditionSubLvl}][category]" size="1" style="width:275px;">
                                        </select>
                                    </td>
                                </tr>
                               <tr>
                                    <td>
                                        <label class="reportLabel">[{isys type="lang" ident="LC__REPORT__FORM__PROPERTY"}]</label>
                                    </td>
                                    <td>
                                        <select id="querycondition_#{queryConditionBlock}_#{queryConditionLvl}_#{queryConditionSubLvl}_#{queryConditionSubLvlProp}_property" class="reportDialog2 conditionSubProperty" name="querycondition[#{queryConditionBlock}][#{queryConditionLvl}][subcnd][#{queryConditionSubLvlProp}][#{queryConditionSubLvl}][property]" size="1" style="width:275px;">
                                        </select>
                                        <span id="ReportConditionSubTemplateValue">
                                            <select id="querycondition_#{queryConditionBlock}_#{queryConditionLvl}_#{queryConditionSubLvl}_#{queryConditionSubLvlProp}_comparison" name="querycondition[#{queryConditionBlock}][#{queryConditionLvl}][subcnd][#{queryConditionSubLvlProp}][#{queryConditionSubLvl}][comparison]" class="reportDialog2 conditionSubComparison" style="width:80px;">
                                                <option value="=">=</option>
                                                <option value="&lt;">&lt;</option>
                                                <option value="&gt;">&gt;</option>
                                                <option value="!=">!=</option>
                                                <option value="&lt;=">&lt;=</option>
                                                <option value="&gt;=">&gt;=</option>
                                                <option value="LIKE">LIKE</option>
                                                <option value="LIKE %...%">LIKE %...%</option>
                                                <option value="NOT LIKE">NOT LIKE</option>
                                                <option value="NOT LIKE %...%">NOT LIKE %...%</option>
                                            </select>
                                            <input id="querycondition_#{queryConditionBlock}_#{queryConditionLvl}_#{queryConditionSubLvl}_#{queryConditionSubLvlProp}_value" name="querycondition[#{queryConditionBlock}][#{queryConditionLvl}][subcnd][#{queryConditionSubLvlProp}][#{queryConditionSubLvl}][value]" class="reportInput conditionSubValue" style="width:140px;">
                                            <select id="querycondition_#{queryConditionBlock}_#{queryConditionLvl}_#{queryConditionSubLvl}_#{queryConditionSubLvlProp}_unit" name="querycondition[#{queryConditionBlock}][#{queryConditionLvl}][subcnd][#{queryConditionSubLvlProp}][#{queryConditionSubLvl}][unit]" class="reportDialog2 conditionSubUnit" style="width:60px;display:none;" disabled></select>
                                            <select id="ReportConditionSubTemplateOperator" name="querycondition[#{queryConditionBlock}][#{queryConditionLvl}][subcnd][#{queryConditionSubLvlProp}][#{queryConditionSubLvl}][operator]" class="reportDialog2 conditionSubOperator" style="width:60px;display:none;">
                                                <option value="AND">[{isys type="lang" ident="LC__UNIVERSAL__AND" p_func="strtoupper"}]</option>
                                                <option value="OR">[{isys type="lang" ident="LC_UNIVERSAL__OR" p_func="strtoupper"}]</option>
                                            </select>
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </td>
                </tr>
            </table>
        </div>

		<div class="p10 mt10">
			<p>[{isys type="lang" ident="LC__REPORT__INFO__DIVISION_TEXT"}]</p>
			<ul style="margin-top: 5px;">
				<li>[{isys type="lang" ident="LC__REPORT__INFO__DIVISION_POINT1"}]</li>
				<li>[{isys type="lang" ident="LC__REPORT__INFO__DIVISION_POINT2"}]</li>
			</ul>

			<div id="dyn" style="min-height:50px">
				<div id="dvsn-remover" class="constraintDiv p10" style="width: 805px;">[{isys type="lang" ident="LC__REPORT__FORM__NO_CONSTRAINTS_ADDED"}]</div>
			</div>

            <button type="button" class="btn btn-small"><img src="[{$dir_images}]icons/silk/add.png" class="mr5" /><span>[{isys type="lang" ident="LC__REPORT__FORM__BUTTON__ADD_CONDITION_BLOCK"}]</span></button>

			<div id="errors"></div>
		</div>
	</fieldset>

<script type="text/javascript">
idoit.Translate.set('LC__REPORT__NO_ATTRIBUTES_FOUND', '[{isys type="lang" ident="LC__REPORT__NO_ATTRIBUTES_FOUND"}]');
idoit.Translate.set('LC__REPORT__EMPTY_RESULT', '[{isys type="lang" ident="LC__REPORT__EMPTY_RESULT"}]');
[{include file="./report.js"}]
[{include file="./report_condition.js"}]

$$('.checkup').invoke('on', 'click', function(e){
    var lvls_content = new Hash;
    var lvl = 1;

    $$('tr.selector-spacer').each(function(ele){
        var child_elements = ele.getElementsByTagName('input');
        var lvls = new Hash;
        for(a in child_elements)
        {
            if(child_elements.hasOwnProperty(a) && !isNaN(a))
            {
                if((a % 2) != 0){
                    var property_arr = child_elements[a].id.split('_'+lvl);
                    property_id = property_arr[0];
                    lvls.set(property_id, child_elements[a].value);
                }
            }
        }
        lvls_content.set(lvl, lvls);

        lvl++;
    }.bind(lvl));


    var l_parameters = {
                    'report__HIDDEN_IDS': $F('report__HIDDEN_IDS'),
                    'lvls': Object.toJSON(lvls_content),
                    'func':'report_preview'
                };
    get_popup('report', '', 800, 508, l_parameters);
});


var report_condition = new ReportCondition();

$('dyn').up().down('button').on('click', function(){
    report_condition.addConditionBlock();
}.bind(report_condition));

[{if $querybuilder_conditions}]
    report_condition.handle_preselection([{$querybuilder_conditions}]);
[{/if}]

/**
 * Retrieve report id after saving to prevent saving duplicate reports
 */
document.on('form:saved', function(ev) {
	if ($('report_id')) {
		if (ev.memo.response.responseJSON) {
			var response = ev.memo.response.responseJSON;

			if (response.id) {
				$('report_id').value = response.id;
			}
		}
	}
});

</script>