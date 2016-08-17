<script type="text/javascript">
[{include file="modules/templates/templates.js"}]
</script>

<div class="p10">
	<h2 class="mb5"><h2>[{isys type="lang" ident="LC__MASS_CHANGE"}]</h2>

	<hr />

	<h3 class="mb5" style="margin-top: 1em; margin-bottom: 0.5em;">1. [{isys type="lang" ident="LC__MASS_CHANGE__CHOOSE_OBJECTS_TO_BE_CHANGED"}]</h3>

    [{isys
        name="selected_objects"
        type="f_popup"
        edit_mode="1"
        id="object_browser"
        readOnly=true
        multiselection=true
        p_strPopupType="browser_object_ng"
        p_strStyle="margin-left:-20px;"
        callback_accept="idoit.callbackManager.triggerCallback('activate-mass-change-btn');"
        callback_detach="idoit.callbackManager.triggerCallback('activate-mass-change-btn');"}]

	<h3 class="mb5" style="margin-top: 1em; margin-bottom: 0.5em;">2. [{isys type="lang" ident="LC__MASS_CHANGE__SELECT_TEMPLATE_FOR_MASS_CHANGES"}]</h3>


	[{isys type="lang" ident="LC__MASS_CHANGE__MASS_CHANGES_DESCRIPTION_CONTENT"}]
	<br><br>


	<label>[{isys type="lang" ident="LC__MASS_CHANGE__AVAILABLE_TEMPLATES"}]
		[{isys type="f_dialog" name="templates" p_bEditMode="1" p_onChange="select_single_template(this);"}]
	</label>

	[{if !empty($field_disabled)}]
	<span id="C__CATG__IP__MESSAGES" class="input error">[{isys type="lang" ident="LC__MASS_CHANGE__NO_TEMPLATES_AVAILABLE"}]</span>
	[{/if}]

	<div class="container ml5 mt5" id="selected_templates">
		<div class="sortable p5">
			<ul id="template_list">
			</ul>
		</div>
	</div>
	<div class="cb mb5"></div>

    <h3 class="mb5" style="margin-top: 1em; margin-bottom: 0.5em;">3. [{isys type='lang' ident='LC__MASS_CHANGE__OPTIONS'}]</h3>

    <h4 style="margin-top: 1em; margin-bottom: 0.5em;">3.1 [{isys type='lang' ident='LC__MASS_CHANGE__HANDLING_EMPTY_FIELDS'}]</h4>

    <label><input type="radio" value="[{$keep}]" name="empty_fields" checked="checked" [{$field_disabled}]/> [{isys type='lang' ident='LC__MASS_CHANGE__IGNORE_EMPTY_FIELDS'}]</label><br />
    <label><input type="radio" value="[{$clear}]" name="empty_fields" [{$field_disabled}]/> [{isys type='lang' ident='LC__MASS_CHANGE__CLEAR_FIELDS'}]</label>

    <h4 style="margin-top: 1em; margin-bottom: 0.5em;">3.2 [{isys type='lang' ident='LC__MASS_CHANGE__HANDLING_MULTI-VALUED_CATEGORIES'}]</h4>

    <label><input type="radio" value="[{$untouched}]" name="multivalue_categories" checked="checked" [{$field_disabled}]/> [{isys type='lang' ident='LC__MASS_CHANGE__KEEP_CATEGORY_ENTRIES_UNTOUCHED'}]</label><br />
    <label><input type="radio" value="[{$add}]" name="multivalue_categories" [{$field_disabled}]/> [{isys type='lang' ident='LC__MASS_CHANGE__ADD_CATEGORY_ENTRIES'}]</label><br />
    <label><input type="radio" value="[{$delete_add}]" name="multivalue_categories" [{$field_disabled}]/> [{isys type='lang' ident='LC__MASS_CHANGE__DELETE_BEFORE_ADD_CATEGORY_ENTRIES'}]</label><br />

    <h3 class="mb5" style="margin-top: 1em; margin-bottom: 0.5em;">4. [{isys type="lang" ident="LC__MASS_CHANGE__APPLY_MASS_CHANGE"}]</h3>

    [{isys type="f_submit" id="apply_mass_change" name="apply_mass_change" p_bDisabled=1 p_strValue="LC__MASS_CHANGE__APPLY_MASS_CHANGE" p_bEditMode="1"}]

    <img style="display:none;" id="loader" class="vam mr5" src="images/ajax-loading.gif" />

	<br />

	<iframe id="iframe" name="iframe" src="" class="mt10 border" style="width:50%;height:250px;display:none;"></iframe>
</div>

<script type="text/javascript">
    (function() {

        var activate_mass_change_btn = function()
        {
            if($('templates').value != -1 && $('selected_objects__HIDDEN').value != '')
            {
                $('apply_mass_change').removeClassName('disabled');
                $('apply_mass_change').removeAttribute('disabled');
            }
            else
            {
                $('apply_mass_change').addClassName('disabled');
                $('apply_mass_change').writeAttribute('disabled');
            }
        };

        new $('apply_mass_change').on('click', function() {
            $('isys_form').target = 'iframe';
            $('loader').show();
            $('iframe').appear();

            delay(function() {$('loader').hide();}, 3500)
        });

        idoit.callbackManager.registerCallback('activate-mass-change-btn', activate_mass_change_btn);

        $('templates').on('change', activate_mass_change_btn);
    })();
</script>