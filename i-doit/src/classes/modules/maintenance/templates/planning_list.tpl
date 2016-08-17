[{$list}]

<script type="text/javascript">
	(function () {
		'use strict';

		var $table = $('mainTable'),
			$send_email_button = $('navbar_item_maintenance_send_mail');

		if ($send_email_button) {
			$send_email_button.on('click', function () {
				var $plannings = $table.select('.listRow input:checked'),
					already_dispatched = [],
					confirm_message = '[{isys type="lang" ident="LC__MAINTENANCE__SEND_MAIL_CONFIRM" p_bHtmlEncode=false}]',
					ids = [],
					last_errors = [];

				if ($plannings.length === 0) {
					idoit.Notify.warning('[{isys type="lang" ident="LC__MAINTENANCE__SEND_MAIL_NO_PLANNING_SELECTED" p_bHtmlEncode=false}]', {life:10});
					return;
				}

				$plannings.each(function ($el) {
					var $tr = $el.up('tr'),
						id = parseInt($tr.down('td', 1).innerHTML);

					if ($tr.down('span[data-mail-dispatched="1"]')) {
						already_dispatched.push($tr.down('td', 2).innerHTML + ' (#' + id + ')');
					}

					ids.push(id);
				});

				if (already_dispatched.length > 0) {
					confirm_message = '[{isys type="lang" ident="LC__MAINTENANCE__SEND_MAIL_AGAIN_CONFIRM" p_bHtmlEncode=false}]'.replace('%s', already_dispatched.join(', '));
				}

				if (confirm(confirm_message)) {
					$send_email_button
						.down('img').writeAttribute('src', '[{$dir_images}]ajax-loading.gif')
						.next('span').update('[{isys type="lang" ident="LC__UNIVERSAL__LOADING"}]');

					new Ajax.Request('[{$ajax_url}]&func=send-planning-email', {
						parameters: {
							ids: Object.toJSON(ids)
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
	})();
</script>