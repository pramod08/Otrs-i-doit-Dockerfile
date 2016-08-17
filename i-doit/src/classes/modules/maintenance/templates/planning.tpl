<h2 class="gradient p5 border-bottom text-shadow">[{isys type="lang" ident="LC__MAINTENANCE__PLANNING"}]</h2>

[{isys type="f_text" name="C__MAINTENANCE__PLANNING__ID"}]

<table class="contentTable">
	<tr>
		<td class="key">[{isys type="lang" ident="LC__MAINTENANCE__PLANNING__FINISHED"}]</td>
		<td class="value">
			[{if $finished}]
			<img src="[{$dir_images}]icons/silk/tick.png" class="ml20 mr5 vam" /><span class="green">[{isys type="lang" ident="LC__UNIVERSAL__YES"}]</span> ([{$finished}])
			[{else}]
			<img src="[{$dir_images}]icons/silk/cross.png" class="ml20 mr5 vam" /><span class="red">[{isys type="lang" ident="LC__UNIVERSAL__NO"}]</span>
			[{/if}]
		</td>
	</tr>
	<tr>
		<td class="key">[{isys type="lang" ident="LC__MAINTENANCE__PLANNING__MAIL_DISPATCHED"}]</td>
		<td class="value">
			[{if $mail_dispatched}]
			<img src="[{$dir_images}]icons/silk/tick.png" class="ml20 mr5 vam" /><span class="green" data-mail-dispatched="1">[{isys type="lang" ident="LC__UNIVERSAL__YES"}]</span> ([{$mail_dispatched}])
			[{else}]
			<img src="[{$dir_images}]icons/silk/cross.png" class="ml20 mr5 vam" /><span class="red" data-mail-dispatched="0">[{isys type="lang" ident="LC__UNIVERSAL__NO"}]</span>
			[{/if}]
		</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" name="C__MAINTENANCE__PLANNING__OBJECT_SELECTION" ident="LC__MAINTENANCE__PLANNING__OBJECT_SELECTION"}]</td>
		<td class="value">[{isys type="f_popup" p_strPopupType="browser_object_ng" name="C__MAINTENANCE__PLANNING__OBJECT_SELECTION" id="C__MAINTENANCE__PLANNING__OBJECT_SELECTION"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" name="C__MAINTENANCE__PLANNING__TYPE" ident="LC__MAINTENANCE__PLANNING__TYPE"}]</td>
		<td class="value">[{isys type="f_popup" name="C__MAINTENANCE__PLANNING__TYPE"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" name="C__MAINTENANCE__PLANNING__DATE_FROM" ident="LC__MAINTENANCE__PLANNING__DATE_FROM"}]</td>
		<td class="value">
			[{isys type="f_popup" name="C__MAINTENANCE__PLANNING__DATE_FROM"}]
			<span class="ml5 mr5">[{isys type="lang" ident="LC__UNIVERSAL__TO"}]</span>
			[{isys type="f_popup" name="C__MAINTENANCE__PLANNING__DATE_TO"}]
		</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" name="C__MAINTENANCE__PLANNING__COMMENT" ident="LC__MAINTENANCE__PLANNING__COMMENT"}]</td>
		<td class="value">[{isys type="f_textarea" name="C__MAINTENANCE__PLANNING__COMMENT"}]</td>
	</tr>
	<tr><td colspan="2"><hr class="mt5 mb5" /></td></tr>
	<tr>
		<td class="key">[{isys type="f_label" name="C__MAINTENANCE__PLANNING__CONTACTS" ident="LC__MAINTENANCE__PLANNING__CONTACTS"}]</td>
		<td class="value">[{isys type="f_popup" p_strPopupType="browser_object_ng" name="C__MAINTENANCE__PLANNING__CONTACTS"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" name="C__MAINTENANCE__PLANNING__CONTACT_ROLES" ident="LC__MAINTENANCE__PLANNING__CONTACT_ROLES" description="LC__MAINTENANCE__PLANNING__CONTACT_ROLES_INFO"}]</td>
		<td class="value">[{isys type="f_dialog" name="C__MAINTENANCE__PLANNING__CONTACT_ROLES"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" name="C__MAINTENANCE__PLANNING__MAILTEMPLATE" ident="LC__MAINTENANCE__PLANNING__MAILTEMPLATE"}]</td>
		<td class="value">[{isys type="f_dialog" name="C__MAINTENANCE__PLANNING__MAILTEMPLATE"}]</td>
	</tr>
</table>

<script>
	(function () {
		'use strict';

		var id = $F('C__MAINTENANCE__PLANNING__ID'),
			location = document.location.href.toQueryParams(),
			$send_email_button = $('navbar_item_maintenance_send_mail');

		if ($send_email_button) {
			$send_email_button.on('click', function () {
				var confirm_message = '[{isys type="lang" ident="LC__MAINTENANCE__SEND_MAIL_CONFIRM" p_bHtmlEncode=false}]',
					last_errors = [];

				if ($$('span[data-mail-dispatched="1"]').length > 0) {
					confirm_message = '[{isys type="lang" ident="LC__MAINTENANCE__SEND_MAIL_AGAIN_CONFIRM" p_bHtmlEncode=false}]'.replace('%s', '[{$mail_dispatched}]');
				}

				if (confirm(confirm_message)) {
					$send_email_button
						.down('img').writeAttribute('src', '[{$dir_images}]ajax-loading.gif')
						.next('span').update('[{isys type="lang" ident="LC__UNIVERSAL__LOADING"}]');

					new Ajax.Request('[{$ajax_url}]&func=send-planning-email', {
						parameters: {
							ids: '[' + id + ']'
						},
						onSuccess: function (response) {
							var json = response.responseJSON, i;

							if (json.success) {
								for (i in json.data) {
									if (json.data.hasOwnProperty(i) && json.data[i] !== true) {
										last_errors.push(json.data[i]);
									}
								}
							} else {
								last_errors.push(json.message || response.responseText);
							}
						},
						onFailure: function (response) {
							last_errors.push(response.responseText);
						},
						onComplete: function () {
							$send_email_button
								.down('img').writeAttribute('src', '[{$dir_images}]icons/silk/email.png')
								.next('span').update('[{isys type="lang" ident="LC__MAINTENANCE__SEND_MAIL"}]');

							if (last_errors.length > 0) {
								idoit.Notify.error('[{isys type="lang" ident="LC__MAINTENANCE__SEND_MAIL_FAILURE"}]' + last_errors.join('<br />'), {sticky:true});
							} else {
								idoit.Notify.success('[{isys type="lang" ident="LC__MAINTENANCE__SEND_MAIL_SUCCESS"}]', {sticky:true});

								document.location.reload(true);
							}
						}
					});
				}
			});
		}

		// This comes in handy to set the "id" parameter, even if the user got here using the checkboxes.
		if (id > 0 && Object.isFunction(window.pushState) && !location.hasOwnProperty('[{$smarty.const.C__GET__ID}]')) {
			location['[{$smarty.const.C__GET__ID}]'] = id;

			setTimeout(function () {
				window.pushState({}, document.title, '?' + Hash.toQueryString(location));
			}, 100);
		}
	})();
</script>