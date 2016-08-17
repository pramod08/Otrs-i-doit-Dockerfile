[{* Smarty template for module 'Notifications'
    @ author: Benjamin Heisig <bheisig@i-doit.org>
    @ copyright: synetics GmbH
    @ license: <http://www.i-doit.com/license>
*}]

[{if $g_list}]
    [{$g_list}]
[{else}]

<div>

<h2 class="border-bottom gradient p5">[{isys type='lang' ident='LC__NOTIFICATIONS__MANAGE_NOTIFICATIONS'}]</h2>

<div class="p10">
	<h3 class="mb5">[{$type_title}]</h3>

	<p>[{$type_description}]</p>

	<p>&nbsp;</p>

	<p><a href="[{$type_templates}]">[{isys type='lang' ident='LC__NOTIFICATIONS__MANAGE_TEMPLATES'}]</a></p>

	[{isys type='f_text' name='C__NOTIFICATIONS__NOTIFICATION_ID'}]
	[{isys type='f_text' name='C__NOTIFICATIONS__NOTIFICATION_TYPE_ID'}]
</div>

<fieldset class="overview">

	<legend><span>[{isys type='lang' ident='LC__NOTIFICATIONS__COMMON_SETTINGS'}]</span></legend>

	<table class="contentTable">
		<tr>
	        <td class="key">[{isys type='f_label' name='C__NOTIFICATIONS__NOTIFICATION_TITLE' ident='LC__NOTIFICATIONS__NOTIFICATION_TITLE'}]</td>
	        <td class="value">[{isys type='f_text' name='C__NOTIFICATIONS__NOTIFICATION_TITLE'}]</td>
	    </tr>
		<tr>
	        <td class="key">[{isys type='f_label' name='C__NOTIFICATIONS__NOTIFICATION_STATUS' ident='LC__NOTIFICATIONS__NOTIFICATION_STATUS'}]</td>
	        <td class="value">[{isys type='checkbox' name='C__NOTIFICATIONS__NOTIFICATION_STATUS'}][{if $current_notification_status}] ([{$current_notification_status}])[{/if}]</td>
	    </tr>
		<tr>
	        <td class="key">[{isys type='f_label' name='C__NOTIFICATIONS__NOTIFICATION_THRESHOLD' ident='LC__NOTIFICATIONS__NOTIFICATION_THRESHOLD'}]</td>
	        <td class="value">
			[{if (!$is_report_based)}]
		        [{isys type='f_text' name='C__NOTIFICATIONS__NOTIFICATION_THRESHOLD' p_strClass="[{if $show_unit}]input-dual-large[{/if}]"}]
		        [{if $show_unit}][{isys type='f_dialog' name='C__NOTIFICATIONS__NOTIFICATION_THRESHOLD_UNIT' p_strClass="input-dual-small" p_bInfoIconSpacer=0}][{/if}]
			[{else}]
				[{isys type='f_text' name='C__NOTIFICATIONS__NOTIFICATION_THRESHOLD' p_strClass="input input-large"}]

				<div class="m5 info p5 ml20 input" style="height: auto;">
					[{isys type="lang" ident="LC__NOTIFICATIONS__NOTIFICATION_TEMPLATE_REPORT__THRESHOLD_INFO"}]
				</div>
			[{/if}]
	        </td>
	    </tr>
		<tr>
	        <td class="key">[{isys type='f_label' name='C__NOTIFICATIONS__NOTIFICATION_LIMIT' ident='LC__NOTIFICATIONS__NOTIFICATION_LIMIT'}]</td>
	        <td class="value">[{isys type='f_text' name='C__NOTIFICATIONS__NOTIFICATION_LIMIT'}]</td>
	    </tr>
		<tr>
	        <td class="key">[{isys type='f_label' name='C__NOTIFICATIONS__NOTIFICATION_COUNT' ident='LC__NOTIFICATIONS__NOTIFICATION_COUNT'}]</td>
	        <td class="value">[{isys type='f_text' name='C__NOTIFICATIONS__NOTIFICATION_COUNT'}]</td>
	    </tr>
		<tr>
	        <td class="key">[{isys type='f_label' name='C__NOTIFICATIONS__NOTIFICATION_LAST_RUN' ident='LC__NOTIFICATIONS__NOTIFICATION_LAST_RUN'}]</td>
	        <td class="value">[{isys type='f_popup' name='C__NOTIFICATIONS__NOTIFICATION_LAST_RUN' p_strClass="input-small"}]</td>
	    </tr>
		<tr>
	        <td class="key">[{isys type='f_label' name='C__NOTIFICATIONS__NOTIFICATION_DESCRIPTION' ident='LC__NOTIFICATIONS__NOTIFICATION_DESCRIPTION'}]</td>
	        <td class="value">[{isys type='f_textarea' name='C__NOTIFICATIONS__NOTIFICATION_DESCRIPTION'}]</td>
	    </tr>
	</table>

</fieldset>

[{if $domain}]

<fieldset class="overview">
	<legend><span>[{isys type='lang' ident='LC__NOTIFICATIONS__NOTIFICATION_DOMAINS'}]</span></legend>

	<table class="contentTable">

	[{if $objects_domain}]
		<tr>
	        <td class="key">[{isys type='f_label' name='C__NOTIFICATIONS__DOMAIN_OBJECTS' ident='LC__NOTIFICATIONS__DOMAIN_OBJECTS'}]</td>
	        <td class="value">[{isys type='f_popup' name='C__NOTIFICATIONS__DOMAIN_OBJECTS'}]</td>
	    </tr>
	[{/if}]

	[{if $object_types_domain}]
		<tr>
	        <td class="key">[{isys type='f_label' name='C__NOTIFICATIONS__DOMAIN_OBJECT_TYPES' ident='LC__NOTIFICATIONS__DOMAIN_OBJECT_TYPES'}]</td>
	        <td class="value">[{isys type='f_dialog_list' name='C__NOTIFICATIONS__DOMAIN_OBJECT_TYPES'}]</td>
	    </tr>
	[{/if}]

	[{if $reports_domain}]
		<tr>
	        <td class="key">[{isys type='f_label' name='C__NOTIFICATIONS__DOMAIN_REPORTS' ident='LC__NOTIFICATIONS__DOMAIN_REPORTS'}]</td>
	        <td class="value">[{isys type='f_dialog_list'
				p_strClass="input input-large"
				name='C__NOTIFICATIONS__DOMAIN_REPORTS'
				emptyMessage="LC__NOTIFICATIONS__NOTIFICATION_TEMPLATE_REPORT__NO_REPORTS"}]</td>
	    </tr>
	[{/if}]

	</table>

</fieldset>

[{/if}]

<fieldset class="overview">
	<legend><span>[{isys type='lang' ident='LC__NOTIFICATIONS__RECEIVERS'}]</span></legend>

	<table class="contentTable">
		<tr>
	        <td class="key">[{isys type='f_label' name='C__NOTIFICATIONS__ASSIGNED_ROLES' ident='LC__NOTIFICATIONS__ASSIGNED_ROLES'}]</td>
	        <td class="value">[{isys type='f_dialog_list' name='C__NOTIFICATIONS__ASSIGNED_ROLES' p_strClass="input input-large"}]
	        </td>
	    </tr>
		<tr>
	        <td class="key">[{isys type='f_label' name='C__NOTIFICATIONS__CONTACTS' ident='LC__NOTIFICATIONS__CONTACTS'}]</td>
	        <td class="value">[{isys type='f_popup' name='C__NOTIFICATIONS__CONTACTS'}]</td>
	    </tr>
	</table>

</fieldset>

</div>

[{/if}]
