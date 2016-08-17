<table class="contentTable">
	<tr>
		<td class="key">
			[{isys type="f_label" name="C__MODULE__CMDB__LOGBOOK_CONFIGURATION__MULTIVALUE_THRESHOLD" ident="LC__MODULE__CMDB__LOGBOOK_CONFIGURATION__MULTIVALUE_THRESHOLD"}]
		</td>
		<td class="value">
			[{isys type="f_text" name="C__MODULE__CMDB__LOGBOOK_CONFIGURATION__MULTIVALUE_THRESHOLD" p_strClass="input-small" p_strValue=$multivalue_threshold}]
		</td>
	</tr>
	<tr>
		<td class="key vat">
			[{isys type="f_label" name="C__MODULE__CMDB__LOGBOOK_CONFIGURATION__USER_IDENT" ident="LC__MODULE__CMDB__LOGBOOK_CONFIGURATION__USER_IDENTIFICATION"}]
		</td>
		<td class="value vat pl20">
			<label>
				<input type="radio" value="0" name="C__MODULE__CMDB__LOGBOOK_CONFIG__TYPE"[{if $disabled_on}] disabled="disabled"[{/if}][{if $default_checked}] checked="checked"[{/if}]/>
				[{isys type="lang" ident="LC__CMDB__CATG__INTERFACE_L__STANDARD"}]
			</label>
			<br/>
			<label>
				<input type="radio" value="1" name="C__MODULE__CMDB__LOGBOOK_CONFIG__TYPE"[{if $disabled_on}] disabled="disabled"[{/if}][{if $advanced_checked}] checked="checked"[{/if}]/>
				[{isys type="lang" ident="LC__EXTENDED"}]
			</label>
			<div class="mt10">
				[{isys type="lang" ident="LC__UNIVERSAL__FORMAT"}]
				[{isys type="f_text" id="C__MODULE__CMDB__LOGBOOK_CONFIG__PLACEHOLDER" name="C__MODULE__CMDB__LOGBOOK_CONFIG__PLACEHOLDER" p_strValue=$placeholder_string}]
			</div>
			[{if !$disabled_on}]
			<div class="mt10 p5 info">
				<img src="[{$dir_images}]icons/silk/information.png" class="vam mr5"/><span>[{isys type="lang" ident="LC__MODULE__CMDB__LOGBOOK_CONFIGURATION__PLACEHOLDER_INFO"}]</span>
			</div>
			[{/if}]
		</td>
	</tr>
</table>

<fieldset class="overview">
	<legend><span>Import</span></legend>

	<table class="contentTable mt10">
		<tr>
			<td class="key">
				[{isys type="f_label" name="C__MODULE__CMDB__LOGBOOK_CONFIGURATION__MULTIVALUE_THRESHOLD" ident="LC__MODULE__CMDB__LOGBOOK_CONFIGURATION__MULTIVALUE_THRESHOLD"}]
			</td>
			<td class="value pl20">
				[{isys type="f_text" p_bInfoIconSpacer=0 name="C__MODULE__CMDB__LOGBOOK_CONFIGURATION__MULTIVALUE_THRESHOLD" p_strClass="input-small" p_strValue=$multivalue_threshold}]

				<div class="mt10 p5 info">
					<img src="[{$dir_images}]icons/silk/information.png" class="vam"/> [{isys type="lang" ident="LC__MODULE__CMDB__LOGBOOK_CONFIGURATION__MULTIVALUE_THRESHOLD_DESCRIPTION"}]
				</div>
			</td>
		</tr>
	</table>
</fieldset>

<script type="text/javascript">
	(function () {
		var $formatInput = $('C__MODULE__CMDB__LOGBOOK_CONFIG__PLACEHOLDER');

		$('scroller').select('[type="radio"]').invoke('on', 'change', function (ev) {
			change_logbook_type(ev.findElement('input').getValue());
		});

		var change_logbook_type = function (val) {
			if ($formatInput) {
				if (val == 1) {
					$formatInput.enable();
				} else {
					$formatInput.disable().setValue('');
				}
			}
		};

		change_logbook_type('[{$logbook_type}]');
	})();
</script>