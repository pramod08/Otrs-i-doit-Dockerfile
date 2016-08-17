<h2 class="p5 gradient border-bottom">[{isys type="lang" ident="LC__UNIVERSAL__GENERAL_CONFIGURATION"}]</h2>

<table class="contentTable" >
    [{foreach from=$registry_keys item="registry_data" key="registry_key"}]
    <tr>
        <td class="key">[{isys type="f_label" ident=$registry_data.title name=$registry_data.post}]</td>
        <td class="value">[{isys type=$registry_data.type name=$registry_data.post}]</td>
    </tr>
    [{/foreach}]
    <tr>
        <td class="key">[{isys type="f_label" ident="LC__CATG__OVERVIEW__MONETARY_FORMAT" name="C__CATG__OVERVIEW__MONETARY_FORMAT"}]</td>
        <td class="value">[{isys type="f_popup" p_strPopupType="dialog_plus" name="C__CATG__OVERVIEW__MONETARY_FORMAT" p_strFootnote="**" p_bDbFieldNN="1" p_strTable="isys_currency"}]</td>
    </tr>
	<tr>
		<td colspan="2">
            <hr class="mt5 mb5" />
		</td>
	</tr>
	<tr>
		<td class="grey bold" colspan="2">
            <span style="margin-left: 10px;padding-right:25px;"><span style="position:absolute;">*</span></span> [{isys type="lang" ident="LC__CMDB__SYSTEM_SETTING__SANITIZE_INPUT_DATA__DESCRIPTION"}]
		</td>
	</tr>
	<tr>
		<td class="grey bold" colspan="2">
			<span style="margin-left: 10px;padding-right:25px;"><span style="position:absolute;">**</span></span> [{isys type="lang" ident="LC__CATG__OVERVIEW__MONETARY_FORMAT_DESCRIPTION"}]
		</td>
	</tr>
</table>

<h3 class="p5 gradient border-top border-bottom">[{isys type="lang" ident="LC__UNIVERSAL__CMDB"}]</h3>

<table class="contentTable m0 p0">
	<tr>
        <td class="key">[{isys type="f_label" ident='LC__MANDATOR_SETTING__IP_HANDLING' name='C__MANDATOR_SETTINGS__IP_HANDLING'}]</td>
        <td class="value">[{isys type="f_dialog" name="C__MANDATOR_SETTINGS__IP_HANDLING"}]</td>
	</tr>
</table>

[{include file="modules/templates/settings__templates.tpl"}]

<h3 class="p5 gradient border-top">[{isys type="lang" ident="LC__LOCKED__OBJECTS"}]</h3>

<table class="contentTable m0 p0">
	<tr>
		<td style="margin-top:0; padding-top:0;">
			<fieldset class="overview">
			    <legend><span>[{isys type="lang" ident="LC__CONFIGURATION"}]</span></legend>
			<br />
			    <label>
			        [{isys type="checkbox" name="lock" p_strValue="1" p_bChecked=$C__LOCK__DATASETS|default:0}]
			        [{isys type="lang" ident="LC__LOCKING__ACTIVATE"}]
			    </label>
			    <br>
			    <strong class="m5">
			        Timeout: [{isys type="f_count" name="lock_timeout" p_strValue=$C__LOCK__TIMEOUT}]
			    </strong>

			    <p class="m5 info p5"><img src="[{$dir_images}]icons/silk/information.png" class="vam mr5" />[{isys type="lang" ident="LC__LOCKING__DESCRIPTION" p_bHtmlEncode=false}]</p>
			</fieldset>

			<fieldset class="overview" style="margin-top:5px;">
			    <legend><span>[{isys type="lang" ident="LC__LOCKED__OBJECTS_CURRENTLY"}]</span></legend>
			</fieldset>

			<div class="pt10">
		    [{if $g_list}]
			    [{if $smarty.post.navMode == 2}]
			    <div class="m5">
			        <button type="button" class="btn" onclick="$('isys_form').action = $('isys_form').action +'&delete_locks'; $('isys_form').submit()">
				        <img src="[{$dir_images}]icons/silk/page_delete.png" class="mr5" alt=""><span>[{isys type="lang" ident="LC_UNIVERSAL__DELETE"}]</span>
			        </button>
			    </div>
			    [{/if}]

		        [{$g_list}]
		    [{else}]
	            <strong>[{isys type="lang" ident="LC_UNIVERSAL__NONE"}]</strong>
		    [{/if}]
			</div>
		</td>
	</tr>
</table>
