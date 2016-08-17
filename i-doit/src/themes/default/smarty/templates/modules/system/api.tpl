<h2 class="p5 gradient border-bottom">[{isys type="lang" ident="LC__AUTH_GUI__JSONRPCAPI_CONDITION"}]</h2>

<fieldset class="overview border-top-none">
	<legend><span>[{isys type="lang" ident="LC__MODULE__JDISC__CONFIGURATION__COMMON_SETTINGS"}]</span></legend>

	<table class="contentTable">
		<tr>
			<td class="key"></td>
			<td class="value">
				<label class="ml20">
					<input type="checkbox" name="C__SYSTEM_SETTINGS__API_STATUS" value="1" [{if !$isEditMode}]disabled[{/if}] [{if $status}]checked="checked" [{/if}]/>
					[{isys type="lang" ident="LC__SYSTEM_SETTINGS__API__ACTIVATE"}]
				</label>
			</td>
		</tr>
		<tr>
			<td class="key"></td>
			<td class="value">
				<label class="ml20">
					<input type="checkbox" name="C__SYSTEM_SETTINGS__API__AUTHENTICATED_USERS_ONLY" value="0" [{if !$isEditMode}]disabled[{/if}] [{if $force_user_login}]checked="checked" [{/if}]/>
					[{isys type="lang" ident="LC__SYSTEM_SETTINGS__API__AUTHENTICATED_USERS_ONLY"}]
				</label>
			</td>
		</tr>
		<tr>
			<td class="key"></td>
			<td class="value">
				<label class="ml20">
					<input type="checkbox" name="C__SYSTEM_SETTINGS__LOGGING_ENABLED" value="1" [{if !$isEditMode}]disabled[{/if}] [{if $logging}]checked="checked" [{/if}]/>
					[{isys type="lang" ident="LC__SYSTEM_SETTINGS__API__LOGGING_ENABLED"}]
				</label>
			</td>
		</tr>
		<tr>
			<td class="key">[{isys type='f_label' name='C__SYSTEM_SETTINGS__APIKEY' ident='API-Key'}]</td>
			<td class="value">
				[{isys type='f_text' name='C__SYSTEM_SETTINGS__APIKEY' style="width:100px;" p_strValue=$apikey}]

				[{if isys_glob_is_edit_mode()}]
				<button type="button" id="btn_new_key" class="btn">
					<img src="[{$dir_images}]icons/silk/arrow_refresh.png" class="mr5"/><span>[{isys type="lang" ident="LC__SYSTEM_SETTINGS__API__CREATE_NEW_KEY"}]</span>
				</button>
				[{/if}]
			</td>
		</tr>
	</table>
</fieldset>

<script type="text/javascript">
	(function () {
		'use strict';

		var $api_key_input = $('C__SYSTEM_SETTINGS__APIKEY'),
			$api_key_button = $('btn_new_key');

		if ($api_key_input && $api_key_button) {
			$api_key_button.on('click', function () {
				new Ajax.Request('?call=apikey&ajax=1', {
					method: 'post',
					onSuccess: function (transport) {
						var json = transport.responseJSON;

						if (json.key) {
							$api_key_input.setValue(json.key);
						}
					}
				});
			});
		}
	})();
</script>