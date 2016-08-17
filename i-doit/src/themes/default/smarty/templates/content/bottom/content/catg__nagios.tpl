<style type="text/css">
	.prototip .commentary {
		margin: 0;
	}

	.command-comment {
		padding: 6px 3px;
		background: #fff;
	}
</style>

<h3 class="p5 gradient border-bottom">[{isys type="lang" ident="LC__CMDB__CATG__NAGIOS_EXPORT"}]</h3>
<div id="nagiosParametersExport">
	<table class="contentTable">
        <tr>
            <td class="key">[{isys type='f_label' name='C__CATG__NAGIOS_IS_EXPORTABLE' ident="LC__CATG__NAGIOS_CONFIG_EXPORT"}]</td>
            <td class="value">[{isys type="f_dialog" name="C__CATG__NAGIOS_IS_EXPORTABLE" p_bDbFieldNN=1 p_strClass="input input-mini"}]</td>
        </tr>
		<tr>
			<td class="key">[{isys type='f_label' name='C__CATG__NAGIOS_EXPORT_HOST' ident="LC__MONITORING__EXPORT__CONFIGURATION"}]</td>
			<td class="value">[{isys type="f_dialog" name="C__CATG__NAGIOS_EXPORT_HOST" p_strClass="input input-mini"}]</td>
		</tr>
		<tr>
			<td colspan="2">
				<hr class="mt5 mb5" />
			</td>
		</tr>
		<tr>
			<td class="key">[{isys type="lang" ident="LC__CMDB__TREE__SYSTEM__SETTINGS_SYSTEM__TEMPLATES"}]</td>
			<td class="value">[{isys name="C__CATG__NAGIOS_TEMPLATES"  id="C__CATG__NAGIOS_TEMPLATES" type="f_popup" p_strPopupType="browser_object_ng" multiselection=true sortSelection=true}]</td>
		</tr>
		<tr>
			<td colspan="2">
				<hr class="mt5 mb5" />
			</td>
		</tr>
        <tr>
            <td class="key">[{isys type='f_label' name='C__CATG__NAGIOS_HOST_NAME' ident="host_name"}]</td>
            <td class="value pl20">
                [{if isys_glob_is_edit_mode()}]
                <label><input type="radio" name="C__CATG__NAGIOS_HOST_NAME_SELECTION" value="[{$smarty.const.C__CATG_NAGIOS__NAME_SELECTION__OBJ_ID}]" [{if $host_name_selection == $smarty.const.C__CATG_NAGIOS__NAME_SELECTION__OBJ_ID}]checked="checked"[{/if}] /> [{isys type="lang" ident="LC__UNIVERSAL__OBJECT_TITLE"}] ("<span class="grey">[{$hostname_obj_title}]</span>")</label><br />
	            <label><input type="radio" name="C__CATG__NAGIOS_HOST_NAME_SELECTION" value="[{$smarty.const.C__CATG_NAGIOS__NAME_SELECTION__HOSTNAME_FQDN}]" [{if $host_name_selection == $smarty.const.C__CATG_NAGIOS__NAME_SELECTION__HOSTNAME_FQDN}]checked="checked"[{/if}] /> [{isys type="lang" ident="LC__CATP__IP__HOSTNAME_FQDN"}] ("<span class="grey">[{$hostname_hostname_fqdn}]</span>")</label><br />
                <label><input type="radio" name="C__CATG__NAGIOS_HOST_NAME_SELECTION" value="[{$smarty.const.C__CATG_NAGIOS__NAME_SELECTION__HOSTNAME}]" [{if $host_name_selection == $smarty.const.C__CATG_NAGIOS__NAME_SELECTION__HOSTNAME}]checked="checked"[{/if}] /> [{isys type="lang" ident="LC__CATP__IP__HOSTNAME"}] ("<span class="grey">[{$hostname_hostname}]</span>")</label><br />
                <input type="radio" name="C__CATG__NAGIOS_HOST_NAME_SELECTION" value="[{$smarty.const.C__CATG_NAGIOS__NAME_SELECTION__INPUT}]" [{if $host_name_selection == $smarty.const.C__CATG_NAGIOS__NAME_SELECTION__INPUT}]checked="checked"[{/if}] /> [{isys type="f_text" name="C__CATG__NAGIOS_HOST_NAME"}]
                [{else}]
                [{$host_name_view}]
                [{/if}]
            </td>
        </tr>
		<tr>
			<td class="key">[{isys type='f_label' name='C__CATG__NAGIOS_ALIAS' ident="Alias"}]</td>
			<td class="value">[{isys type="f_text" name="C__CATG__NAGIOS_ALIAS"}]</td>
		</tr>
		<tr>
			<td class="key vat">[{isys type="f_label" name="C__CATG__NAGIOS_IP" ident="LC__CATG__NAGIOS__ADDRESS"}]</td>
			<td class="value">
				[{if isys_glob_is_edit_mode()}]
					[{isys type="f_dialog" name="C__CATG__NAGIOS_IP" p_bDbFieldNN=1 p_bSort=false p_strClass="input"}]
					<br />
					<label class="ml20 mt5"><input name="C__CATG__NAGIOS_IP_SELECTION" type="radio" class="vam mr5" [{if $address_selection == $smarty.const.C__CATG_NAGIOS__NAME_SELECTION__IP}]checked="checked"[{/if}] value="[{$smarty.const.C__CATG_NAGIOS__NAME_SELECTION__IP}]" /><span class="vam">[{isys type="lang" ident="LC__CATG__NAGIOS__USE_IP"}]</span></label>
					<label class="ml20 mt5"><input name="C__CATG__NAGIOS_IP_SELECTION" type="radio" class="vam mr5" [{if $address_selection == $smarty.const.C__CATG_NAGIOS__NAME_SELECTION__HOSTNAME}]checked="checked"[{/if}] value="[{$smarty.const.C__CATG_NAGIOS__NAME_SELECTION__HOSTNAME}]" /><span class="vam">[{isys type="lang" ident="LC__CATG__NAGIOS__USE_HOSTNAME"}]</span></label>
					<label class="ml20 mt5"><input name="C__CATG__NAGIOS_IP_SELECTION" type="radio" class="vam mr5" [{if $address_selection == $smarty.const.C__CATG_NAGIOS__NAME_SELECTION__HOSTNAME_FQDN}]checked="checked"[{/if}] value="[{$smarty.const.C__CATG_NAGIOS__NAME_SELECTION__HOSTNAME_FQDN}]" /><span class="vam">[{isys type="lang" ident="LC__CATG__NAGIOS__USE_HOSTNAME_FQDN"}]</span></label>
				[{else}]
					<span class="ml20">[{$address_view}]</span>
				[{/if}]
			</td>
		</tr>
        <tr>
            <td class="key">[{isys type='f_label' name='C__CATG__NAGIOS__CONTACTS' ident="Contacts"}]</td>
            <td class="value">[{isys name="C__CATG__NAGIOS_CONTACTS" id="C__CATG__NAGIOS_CONTACTS" multiselection=true type="f_popup" p_strPopupType="browser_object_ng" edit_mode="0"}]</td>
        </tr>
        <tr>
            <td class="key">[{isys type='f_label' name='C__CATG__NAGIOS_PARENTS' ident="LC__CATG__NAGIOS_PARENTS"}]</td>
            <td class="value">[{isys name="C__CATG__NAGIOS_PARENTS" id="C__CATG__NAGIOS_PARENTS" type="f_popup" p_strPopupType="browser_object_ng" multiselection=true}]</td>
        </tr>
        <tr>
            <td class="key">[{isys type='f_label' name='C__CATG__NAGIOS_IS_PARENT' ident="LC__CATG__NAGIOS_IS_PARENT"}]</td>
            <td class="value">
	            [{if count($parents)}]
                <ul class="inputLinkList" style="margin:10px 20px;">
                    [{foreach $parents as $parent}]
                    <li><a target="_blank" href="?[{$smarty.const.C__CMDB__GET__OBJECT}]=[{$parent.id}]">[{$parent.type}] >> [{$parent.title}] ("[{$parent.rendered_host_name}]")</a></li>
                    [{/foreach}]
                </ul>
	            [{/if}]
                [{isys type="f_dialog" name="C__CATG__NAGIOS_IS_PARENT" p_bDbFieldNN="1" p_strClass="input input-mini"}]
            </td>
        </tr>
    </table>

	<h3 id="advanced_link" class="gradient border-bottom border-top mouse-pointer p5 mt10">
		<img src="[{$dir_images}]icons/silk/bullet_arrow_down.png" class="vam" /> <span class="vam">[{isys type="lang" ident="LC__EXTENDED"}]</span>
	</h3>
	<table class="contentTable" id="advanced_nagios_options">
		<tr>
			<td class="key">[{isys type='f_label' name='C__CATG__NAGIOS_CHECK_COMMAND' ident="check_command"}]</td>
			<td class="value pl20">
				[{if isys_glob_is_edit_mode()}]<input type="radio" name="C__CHECK_COMMAND_SELECTION" disabled="disabled" />[{/if}]
				[{isys type="f_dialog" name="C__CATG__NAGIOS_CHECK_COMMAND" p_onChange="idoit.callbackManager.triggerCallback('nagios__check_command_description', this.id);"}]&nbsp;
				<img class="vam mouse-help" data-input-el="C__CATG__NAGIOS_CHECK_COMMAND" src="[{$dir_images}]icons/silk/information.png" /><br />

				[{if isys_glob_is_edit_mode()}]<input type="radio" name="C__CHECK_COMMAND_SELECTION" disabled="disabled" />[{/if}]
				[{isys type="f_popup" p_strPopupType="dialog_plus" name="C__CATG__NAGIOS_CHECK_COMMAND_PLUS"}]
			</td>
		</tr>
		<tr>
			<td class="key">[{isys type='f_label' name='C__CATG__NAGIOS_CHECK_COMMAND_PARAMETERS' ident="check_command parameter"}]</td>
			<td class="value">[{isys type="f_text" name="C__CATG__NAGIOS_CHECK_COMMAND_PARAMETERS"}]</td>
		</tr>
		<tr>
			<td class="key">[{isys type='f_label' name='C__CATG__NAGIOS_CHECK_INTERVAL' ident="check_interval"}]</td>
			<td class="value">[{isys type="f_text" name="C__CATG__NAGIOS_CHECK_INTERVAL"}]</td>
		</tr>
		<tr>
			<td class="key">[{isys type='f_label' name='C__CATG__NAGIOS_RETRY_INTERVAL' ident="retry_interval"}]</td>
			<td class="value">[{isys type="f_text" name="C__CATG__NAGIOS_RETRY_INTERVAL"}]</td>
		</tr>
        <tr>
            <td class="key">[{isys type='f_label' name='C__CATG__NAGIOS_MAX_CHECK_ATTEMPTS' ident="max_check_attempts"}]</td>
            <td class="value">[{isys type="f_text" name="C__CATG__NAGIOS_MAX_CHECK_ATTEMPTS"}]</td>
        </tr>
        <tr>
            <td class="key">[{isys type='f_label' name='C__CATG__NAGIOS_CHECK_PERIOD' ident="check_period"}]</td>
            <td class="value pl20">
                [{if isys_glob_is_edit_mode()}]<input type="radio" name="C__CHECK_PERIOD_SELECTION" disabled="disabled" />[{/if}]
                [{isys type="f_dialog" name="C__CATG__NAGIOS_CHECK_PERIOD" }]<br />

                [{if isys_glob_is_edit_mode()}]<input type="radio" name="C__CHECK_PERIOD_SELECTION" disabled="disabled" />[{/if}]
                [{isys type="f_popup" p_strPopupType="dialog_plus" name="C__CATG__NAGIOS_CHECK_PERIOD_PLUS" p_strTable="isys_nagios_timeperiods_plus"}]
            </td>
        </tr>
		<tr>
			<td class="key">[{isys type='f_label' name='C__CATG__NAGIOS_ACTIVE_CHECKS_ENABLED' ident="active_checks_enabled"}]</td>
			<td class="value">[{isys type="f_dialog" name="C__CATG__NAGIOS_ACTIVE_CHECKS_ENABLED" p_strClass="input input-mini"}]</td>
		</tr>
		<tr>
			<td class="key">[{isys type='f_label' name='C__CATG__NAGIOS_PASSIVE_CHECKS_ENABLED' ident="passive_checks_enabled"}]</td>
			<td class="value">[{isys type="f_dialog" name="C__CATG__NAGIOS_PASSIVE_CHECKS_ENABLED" p_strClass="input input-mini"}]</td>
		</tr>
		<tr>
			<td class="key">[{isys type='f_label' name='C__CATG__NAGIOS_NOTIFICATIONS_ENABLED' ident="notifications_enabled"}]</td>
			<td class="value">[{isys type="f_dialog" name="C__CATG__NAGIOS_NOTIFICATIONS_ENABLED" p_strClass="input input-mini"}]</td>
		</tr>
		<tr>
			<td class="key">[{isys type='f_label' name='C__CATG__NAGIOS_NOTIFICATION_OPTIONS__available_box' ident="notification_options"}]</td>
			<td class="value">[{isys type="f_dialog_list" name="C__CATG__NAGIOS_NOTIFICATION_OPTIONS" p_bDialogMode=true p_strClass="input"}]</td>
		</tr>
        <tr>
            <td class="key">[{isys type='f_label' name='C__CATG__NAGIOS_NOTIFICATION_INTERVAL' ident="notification_interval"}]</td>
            <td class="value">[{isys type="f_text" name="C__CATG__NAGIOS_NOTIFICATION_INTERVAL"}]</td>
        </tr>
        <tr>
            <td class="key">[{isys type='f_label' name='C__CATG__NAGIOS_NOTIFICATION_PERIOD' ident="notification_period"}]</td>
            <td class="value pl20">
                [{if isys_glob_is_edit_mode()}]<input type="radio" name="C__NOTIFICATION_PERIOD_SELECTION" disabled="disabled" />[{/if}]
                [{isys type="f_dialog" name="C__CATG__NAGIOS_NOTIFICATION_PERIOD"}]<br />

                [{if isys_glob_is_edit_mode()}]<input type="radio" name="C__NOTIFICATION_PERIOD_SELECTION" disabled="disabled" />[{/if}]
                [{isys type="f_popup" p_strPopupType="dialog_plus" name="C__CATG__NAGIOS_NOTIFICATION_PERIOD_PLUS" p_strTable="isys_nagios_timeperiods_plus"}]
            </td>
        </tr>
		<tr>
			<td class="key">[{isys type='f_label' name='C__CATG__NAGIOS_INITIAL_STATE' ident="initial_state"}]</td>
			<td class="value">[{isys type="f_dialog" name="C__CATG__NAGIOS_INITIAL_STATE" p_strClass="input input-mini"}]</td>
		</tr>
		<tr>
			<td class="key">[{isys type='f_label' name='C__CATG__NAGIOS_OBSESS_OVER_HOST' ident="obsess_over_host"}]</td>
			<td class="value">[{isys type="f_dialog" name="C__CATG__NAGIOS_OBSESS_OVER_HOST" p_strClass="input input-mini"}]</td>
		</tr>
		<tr>
			<td class="key">[{isys type='f_label' name='C__CATG__NAGIOS_CHECK_FRESHNESS' ident="check_freshness"}]</td>
			<td class="value">[{isys type="f_dialog" name="C__CATG__NAGIOS_CHECK_FRESHNESS" p_strClass="input input-mini"}]</td>
		</tr>
		<tr>
			<td class="key">[{isys type='f_label' name='C__CATG__NAGIOS_FRESHNESS_THRESHOLD' ident="freshness_threshold"}]</td>
			<td class="value">[{isys type="f_text" name="C__CATG__NAGIOS_FRESHNESS_THRESHOLD"}]</td>
		</tr>
		<tr>
			<td class="key">[{isys type='f_label' name='C__CATG__NAGIOS_FLAP_DETECTION_ENABLED' ident="flap_detection_enabled"}]</td>
			<td class="value">[{isys type="f_dialog" name="C__CATG__NAGIOS_FLAP_DETECTION_ENABLED" p_strClass="input input-mini"}]</td>
		</tr>
		<tr>
			<td class="key">[{isys type='f_label' name='C__CATG__NAGIOS_FLAP_DETECTION_OPTIONS__available_box' ident="flap_detection_options"}]</td>
			<td class="value">[{isys type="f_dialog_list" name="C__CATG__NAGIOS_FLAP_DETECTION_OPTIONS" p_bDialogMode=true p_strClass="input"}]</td>
		</tr>
		<tr>
			<td class="key">[{isys type='f_label' name='C__CATG__NAGIOS_LOW_FLAP_THRESHOLD' ident="low_flap_threshold"}]</td>
			<td class="value">[{isys type="f_text" name="C__CATG__NAGIOS_LOW_FLAP_THRESHOLD"}]</td>
		</tr>
		<tr>
			<td class="key">[{isys type='f_label' name='C__CATG__NAGIOS_HIGH_FLAP_THRESHOLD' ident="high_flap_threshold"}]</td>
			<td class="value">[{isys type="f_text" name="C__CATG__NAGIOS_HIGH_FLAP_THRESHOLD"}]</td>
		</tr>
		<tr>
			<td class="key">[{isys type='f_label' name='C__CATG__NAGIOS_EVENT_HANDLER_ENABLED' ident="event_handler_enabled"}]</td>
			<td class="value">[{isys type="f_dialog" name="C__CATG__NAGIOS_EVENT_HANDLER_ENABLED" p_strClass="input input-mini"}]</td>
		</tr>
        <tr>
            <td class="key">[{isys type='f_label' name='C__CATG__NAGIOS_EVENT_HANDLER' ident="event_handler"}]</td>
            <td class="value pl20">
                [{if isys_glob_is_edit_mode()}]<input type="radio" name="C__EVENT_HANDLER_SELECTION" disabled="disabled" />[{/if}]
                [{isys type="f_dialog" name="C__CATG__NAGIOS_EVENT_HANDLER" p_onChange="idoit.callbackManager.triggerCallback('nagios__check_command_description', this.id);"}]&nbsp;
	            <img class="vam mouse-help" data-input-el="C__CATG__NAGIOS_EVENT_HANDLER" src="[{$dir_images}]icons/silk/information.png" /><br />

                [{if isys_glob_is_edit_mode()}]<input type="radio" name="C__EVENT_HANDLER_SELECTION" disabled="disabled" />[{/if}]
                [{isys type="f_popup" p_strPopupType="dialog_plus" name="C__CATG__NAGIOS_EVENT_HANDLER_PLUS" p_strTable="isys_nagios_commands_plus"}]
            </td>
        </tr>
        <tr>
            <td class="key">[{isys type='f_label' name='C__CATG__NAGIOS_EVENT_HANDLER_PARAMETERS' ident="event_handler parameter"}]</td>
            <td class="value">[{isys type="f_text" name="C__CATG__NAGIOS_EVENT_HANDLER_PARAMETERS"}]</td>
        </tr>
		<tr>
			<td class="key">[{isys type='f_label' name='C__CATG__NAGIOS_PROCESS_PERF_DATA' ident="process_perf_data"}]</td>
			<td class="value">[{isys type="f_dialog" name="C__CATG__NAGIOS_PROCESS_PERF_DATA" p_strClass="input input-mini"}]</td>
		</tr>
		<tr>
			<td class="key">[{isys type='f_label' name='C__CATG__NAGIOS_RETAIN_STATUS_INFORMATION' ident="retain_status_information"}]</td>
			<td class="value">[{isys type="f_dialog" name="C__CATG__NAGIOS_RETAIN_STATUS_INFORMATION" p_strClass="input input-mini"}]</td>
		</tr>
		<tr>
			<td class="key">[{isys type='f_label' name='C__CATG__NAGIOS_RETAIN_NONSTATUS_INFORMATION' ident="retain_nonstatus_information"}]</td>
			<td class="value">[{isys type="f_dialog" name="C__CATG__NAGIOS_RETAIN_NONSTATUS_INFORMATION" p_strClass="input input-mini"}]</td>
		</tr>
		<tr>
			<td class="key">[{isys type='f_label' name='C__CATG__NAGIOS_FIRST_NOTIFICATION_DELAY' ident="first_notification_delay"}]</td>
			<td class="value">[{isys type="f_text" name="C__CATG__NAGIOS_FIRST_NOTIFICATION_DELAY"}]</td>
		</tr>
		<tr>
			<td class="key">[{isys type='f_label' name='C__CATG__NAGIOS_STALKING_OPTIONS__available_box' ident="stalking_options"}]</td>
			<td class="value">[{isys type="f_dialog_list" name="C__CATG__NAGIOS_STALKING_OPTIONS" p_bDialogMode=true p_strClass="input"}]</td>
		</tr>
		<tr>
			<td class="key">[{isys type='f_label' name='C__CATG__NAGIOS_ESCALATIONS__available_box' ident='escalations'}]</td>
			<td class="value">[{isys type="f_dialog_list" name="C__CATG__NAGIOS_ESCALATIONS" p_bDialogMode=true p_strClass="input"}]</td>
		</tr>
		<tr>
			<td class="key">[{isys type='f_label' name='C__CATG__NAGIOS_ACTION_URL' ident="action_url"}]</td>
			<td class="value">[{isys type="f_text" name="C__CATG__NAGIOS_ACTION_URL"}]</td>
		</tr>
		<tr>
			<td class="key">[{isys type='f_label' name='C__CATG__NAGIOS_ICON_IMAGE' ident="icon_image"}]</td>
			<td class="value">[{isys type="f_text" name="C__CATG__NAGIOS_ICON_IMAGE"}]</td>
		</tr>
		<tr>
			<td class="key">[{isys type='f_label' name='C__CATG__NAGIOS_ICON_IMAGE_ALT' ident="icon_image_alt"}]</td>
			<td class="value">[{isys type="f_text" name="C__CATG__NAGIOS_ICON_IMAGE_ALT"}]</td>
		</tr>
		<tr>
			<td class="key">[{isys type='f_label' name='C__CATG__NAGIOS_VRML_IMAGE' ident="vrml_image"}]</td>
			<td class="value">[{isys type="f_text" name="C__CATG__NAGIOS_VRML_IMAGE"}]</td>
		</tr>
		<tr>
			<td class="key">[{isys type='f_label' name='C__CATG__NAGIOS_STATUSMAP_IMAGE' ident="statusmap_image"}]</td>
			<td class="value">[{isys type="f_text" name="C__CATG__NAGIOS_STATUSMAP_IMAGE"}]</td>
		</tr>
		<tr>
			<td class="key">[{isys type='f_label' name='C__CATG__NAGIOS_2D_COORDS_X' ident="2d_coords"}]</td>
			<td class="value">
				[{isys type="f_text" name="C__CATG__NAGIOS_2D_COORDS" p_bInvisible=true p_bInfoIconSpacer=0}]
				[{isys type="f_text" name="C__CATG__NAGIOS_2D_COORDS_X" p_strClass="input input-mini 2d_coords" p_strPlaceholder="X"}] &times;
				[{isys type="f_text" name="C__CATG__NAGIOS_2D_COORDS_Y" p_strClass="input input-mini 2d_coords" p_strPlaceholder="Y" p_bInfoIconSpacer=0}]
			</td>
		</tr>
		<tr>
			<td class="key">[{isys type='f_label' name='C__CATG__NAGIOS_3D_COORDS_X' ident="3d_coords"}]</td>
			<td class="value">
				[{isys type="f_text" name="C__CATG__NAGIOS_3D_COORDS" p_bInvisible=true p_bInfoIconSpacer=0}]
				[{isys type="f_text" name="C__CATG__NAGIOS_3D_COORDS_X" p_strClass="input input-mini 3d_coords" p_strPlaceholder="X"}] &times;
				[{isys type="f_text" name="C__CATG__NAGIOS_3D_COORDS_Y" p_strClass="input input-mini 3d_coords" p_strPlaceholder="Y" p_bInfoIconSpacer=0}] &times;
				[{isys type="f_text" name="C__CATG__NAGIOS_3D_COORDS_Z" p_strClass="input input-mini 3d_coords" p_strPlaceholder="Z" p_bInfoIconSpacer=0}]
			</td>
		</tr>
		<tr>
			<td class="key">[{isys type='f_label' name='C__CATG__NAGIOS_NOTES' ident="notes"}]</td>
			<td class="value">[{isys type="f_text" name="C__CATG__NAGIOS_NOTES"}]</td>
		</tr>
		<tr>
			<td class="key">[{isys type='f_label' name='C__CATG__NAGIOS_NOTES_URL' ident="notes_url"}]</td>
			<td class="value">[{isys type="f_text" name="C__CATG__NAGIOS_NOTES_URL"}]</td>
		</tr>
		<tr>
			<td class="key">[{isys type='f_label' name='C__CATG__NAGIOS_DISPLAY_NAME' ident="display_name"}]</td>
			<td class="value">
				[{if isys_glob_is_edit_mode()}]
				<label><input type="radio" name="C__CATG__NAGIOS_DISPLAY_NAME_SELECTION" value="[{$smarty.const.C__CATG_NAGIOS__NAME_SELECTION__OBJ_ID}]" style="margin-left: 20px;" [{if $display_name_selection == $smarty.const.C__CATG_NAGIOS__NAME_SELECTION__OBJ_ID}]checked="checked"[{/if}] /> [{isys type="lang" ident="LC__UNIVERSAL__OBJECT_TITLE"}]</label><br />
				<label><input type="radio" name="C__CATG__NAGIOS_DISPLAY_NAME_SELECTION" value="[{$smarty.const.C__CATG_NAGIOS__NAME_SELECTION__HOSTNAME}]" style="margin-left: 20px;" [{if $display_name_selection == $smarty.const.C__CATG_NAGIOS__NAME_SELECTION__HOSTNAME}]checked="checked"[{/if}] /> [{isys type="lang" ident="LC__CATP__IP__HOSTNAME"}]</label><br />
				<input type="radio" name="C__CATG__NAGIOS_DISPLAY_NAME_SELECTION" value="[{$smarty.const.C__CATG_NAGIOS__NAME_SELECTION__INPUT}]" style="margin-left: 20px;" [{if $display_name_selection == $smarty.const.C__CATG_NAGIOS__NAME_SELECTION__INPUT}]checked="checked"[{/if}] /> [{isys type="f_text" name="C__CATG__NAGIOS_DISPLAY_NAME"}]
				[{else}]
				<img src="[{$dir_images}]empty.gif" width="20" height="1" />[{$display_name_view}]
				[{/if}]
			</td>
		</tr>
		<tr>
			<td class="key">[{isys type="f_label" name="C__CATG__NAGIOS_CUSTOM_OBJ_VARS" ident="custom_object_vars"}]</td>
			<td class="value">[{isys type="f_textarea" name="C__CATG__NAGIOS_CUSTOM_OBJ_VARS" p_strClass="input"}]</td>
		</tr>
	</table>
</div>

<script>
	(function () {
		"use strict";

		var advanced_link = $('advanced_link');

		if (advanced_link) {
			advanced_link.on('click', function () {
				var table = this.next();

				if (table.hasClassName('hide')) {
					this.down('img').setAttribute('src', '[{$dir_images}]icons/silk/bullet_arrow_up.png');
					table.removeClassName('hide');
				} else {
					this.down('img').setAttribute('src', '[{$dir_images}]icons/silk/bullet_arrow_down.png');
					table.addClassName('hide');
				}
			});
		}

		$('advanced_nagios_options').addClassName('hide');

		var check_command = $('C__CATG__NAGIOS_CHECK_COMMAND'),
			check_command_plus = $('C__CATG__NAGIOS_CHECK_COMMAND_PLUS'),
			event_handler = $('C__CATG__NAGIOS_EVENT_HANDLER'),
			event_handler_plus = $('C__CATG__NAGIOS_EVENT_HANDLER_PLUS'),
			check_period = $('C__CATG__NAGIOS_CHECK_PERIOD'),
			check_period_plus = $('C__CATG__NAGIOS_CHECK_PERIOD_PLUS'),
			notification_period = $('C__CATG__NAGIOS_NOTIFICATION_PERIOD'),
			notification_period_plus = $('C__CATG__NAGIOS_NOTIFICATION_PERIOD_PLUS'),
			display_name = $('C__CATG__NAGIOS_DISPLAY_NAME'),
			display_name_radios = $$('input[name="C__CATG__NAGIOS_DISPLAY_NAME_SELECTION"]'),
			host_name = $('C__CATG__NAGIOS_HOST_NAME'),
			host_name_radios = $$('input[name="C__CATG__NAGIOS_HOST_NAME_SELECTION"]'),
			coords_2d = $$('.2d_coords'),
			coords_3d = $$('.3d_coords');

		if (check_command && check_command_plus) {
			check_command.on('change', function () {
				$$('input[name="C__CHECK_COMMAND_SELECTION"]')[0].checked = true;
				$$('input[name="C__CHECK_COMMAND_SELECTION"]')[1].checked = false;
				check_command_plus.selectedIndex = 0;
			});

			check_command_plus.on('change', function () {
				$$('input[name="C__CHECK_COMMAND_SELECTION"]')[0].checked = false;
				$$('input[name="C__CHECK_COMMAND_SELECTION"]')[1].checked = true;
				check_command.selectedIndex = 0;
			});

			// Visual selection (has no effect on any logic, is just an indicator for the user).
			if ($F(check_command) > 0) {
				check_command.previous('input[type="radio"]').checked = true;
			}

			if ($F(check_command_plus) > 0) {
				check_command_plus.previous('input[type="radio"]').checked = true;
			}
		}

		if (event_handler && event_handler_plus) {
			event_handler.on('change', function () {
				$$('input[name="C__EVENT_HANDLER_SELECTION"]')[0].checked = true;
				$$('input[name="C__EVENT_HANDLER_SELECTION"]')[1].checked = false;
				event_handler_plus.selectedIndex = 0;
			});

			event_handler_plus.on('change', function () {
				$$('input[name="C__EVENT_HANDLER_SELECTION"]')[0].checked = false;
				$$('input[name="C__EVENT_HANDLER_SELECTION"]')[1].checked = true;
				event_handler.selectedIndex = 0;
			});

			// Visual selection (has no effect on any logic, is just an indicator for the user).
			if ($F(event_handler) > 0) {
				event_handler.previous('input[type="radio"]').checked = true;
			}

			if ($F(event_handler_plus) > 0) {
				event_handler_plus.previous('input[type="radio"]').checked = true;
			}
		}

		if (check_period && check_period_plus) {
			check_period.on('change', function () {
				$$('input[name="C__CHECK_PERIOD_SELECTION"]')[0].checked = true;
				$$('input[name="C__CHECK_PERIOD_SELECTION"]')[1].checked = false;
				check_period_plus.selectedIndex = 0;
			});

			check_period_plus.on('change', function () {
				$$('input[name="C__CHECK_PERIOD_SELECTION"]')[0].checked = false;
				$$('input[name="C__CHECK_PERIOD_SELECTION"]')[1].checked = true;
				check_period.selectedIndex = 0;
			});

			// Visual selection (has no effect on any logic, is just an indicator for the user).
			if ($F(check_period) > 0) {
				check_period.previous('input[type="radio"]').checked = true;
			}

			if ($F(check_period_plus) > 0) {
				check_period_plus.previous('input[type="radio"]').checked = true;
			}
		}

		if (notification_period && notification_period_plus) {
			notification_period.on('change', function () {
				$$('input[name="C__NOTIFICATION_PERIOD_SELECTION"]')[0].checked = true;
				$$('input[name="C__NOTIFICATION_PERIOD_SELECTION"]')[1].checked = false;
				notification_period_plus.selectedIndex = 0;
			});

			notification_period_plus.on('change', function () {
				$$('input[name="C__NOTIFICATION_PERIOD_SELECTION"]')[0].checked = false;
				$$('input[name="C__NOTIFICATION_PERIOD_SELECTION"]')[1].checked = true;
				notification_period.selectedIndex = 0;
			});

			// Visual selection (has no effect on any logic, is just an indicator for the user).
			if ($F(notification_period) > 0) {
				notification_period.previous('input[type="radio"]').checked = true;
			}

			if ($F(notification_period_plus) > 0) {
				notification_period_plus.previous('input[type="radio"]').checked = true;
			}
		}

		if (display_name && display_name_radios.length == 3) {
			display_name.on('focus', function () {
				display_name_radios[0].checked = false;
				display_name_radios[1].checked = false;
				display_name_radios[2].checked = true;
			});
		}

		if (host_name && host_name_radios.length == 3) {
			host_name.on('focus', function () {
				host_name_radios[0].checked = false;
				host_name_radios[1].checked = false;
				host_name_radios[2].checked = true;
			});
		}

		if (coords_2d && coords_2d.length == 2) {
			coords_2d.invoke('on', 'change', function (ev) {
				var value = ev.findElement('input').getValue();

				if (value.blank()) {
					return;
				} else {
					value = parseInt(value);
				}

				this.setValue(value);

				$('C__CATG__NAGIOS_2D_COORDS').setValue(coords_2d.invoke('getValue').join(','));
			});
		}

		if (coords_3d && coords_3d.length == 3) {
			coords_3d.invoke('on', 'change', function (ev) {
				var value = ev.findElement('input').getValue();

				if (value.blank()) {
					return;
				} else {
					value = parseFloat(value);
				}

				this.setValue(value);

				$('C__CATG__NAGIOS_3D_COORDS').setValue(coords_3d.invoke('getValue').join(','));
			});
		}

		idoit.callbackManager
			.registerCallback('nagios__check_command_description', function (el, value) {
				var $el = $(el);

				if (! $el) {
					return;
				}

				if (Object.isUndefined(value)) {
					value = $el.getValue();
				}

				new Ajax.Request('?ajax=1&call=nagios&func=load_command_comment', {
					parameters: {
						command_id: value
					},
					method: "post",
					onComplete: function (response) {
						new Tip($$('img[data-input-el="' + this + '"]')[0], response.responseJSON.data, {delay:0, className:'command-comment border'});
					}.bind(el)
				});
			})
			.triggerCallback('nagios__check_command_description', 'C__CATG__NAGIOS_CHECK_COMMAND', '[{$check_command_value}]')
			.triggerCallback('nagios__check_command_description', 'C__CATG__NAGIOS_EVENT_HANDLER', '[{$event_handler_value}]');
	}());
</script>