<div id="service-error" class="error p5 mb10">[{if $error}][{$error}][{else}][{$default_error}][{/if}]</div>

<table class="contentTable">
	<tr>
		<td class="key">
			[{isys name="C__CATG__CMK_SERVICE__HOST" type="f_label" ident="LC__MODULE__CHECK_MK__HOST"}]
		</td>
		<td class="value">
			[{isys name="C__CATG__CMK_SERVICE__HOST" type="f_dialog"}]
		</td>
	</tr>
	<tr>
		<td class="key">
			[{isys name="C__CATG__CMK_SERVICE__CHECK_MK_SERVICES" type="f_label" ident="LC__CATG__CMK_SERVICE__CHECK_MK_SERVICES"}]
		</td>
		<td class="value">
			[{isys name="C__CATG__CMK_SERVICE__CHECK_MK_SERVICES" type="f_dialog" p_bDbFieldNN=true}]
		</td>
	</tr>
</table>

<script type="text/javascript">
	var host_selection = $('C__CATG__CMK_SERVICE__HOST'),
		service_error_box = $('service-error');

	[{if !$error}]service_error_box.hide();[{/if}]

	if (host_selection) {
		host_selection.on('change', function () {
			if (this.getValue() > 0) {
				service_error_box.hide();

				// Retrieve the services from the selected host.
				call_services();
			} else {
				service_error_box.show();

				// Empty the dialog field.
				$('C__CATG__CMK_SERVICE__CHECK_MK_SERVICES').update(new Element('option', {value:-1}).update('[{isys_tenantsettings::get('gui.empty_value', '-')}]'));
			}
		});
	}

	function call_services () {
		new Ajax.Request('[{$ajax_url}]', {
			method: "post",
			parameters: {
				host_id:host_selection.getValue(),
				query:'["GET services","Columns: description"]'
			},
			onSuccess: function(transport) {
				var json = transport.responseJSON,
					service_dialog = $('C__CATG__CMK_SERVICE__CHECK_MK_SERVICES').update(new Element('option', {value:-1}).update('[{isys_tenantsettings::get('gui.empty_value', '-')}]')),
					services = [],
					i;

				if (json.success) {
					services = json.data.flatten().uniq();

					for (i in services) {
						if (services.hasOwnProperty(i)) {
							service_dialog.insert(new Element('option', {value:services[i]}).update(services[i]));
						}
					}
				} else {
					service_error_box.update(json.message).show();
				}
			}
		});
	}
</script>