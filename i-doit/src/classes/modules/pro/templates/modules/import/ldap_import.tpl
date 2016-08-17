<div class="p10">
	<h3>[{isys type="lang" ident="LC__MODULE__IMPORT__LDAP__HEADLINE"}]</h3>
	<p>[{isys type="lang" ident="LC__MODULE__IMPORT__LDAP__DESCRIPTION"}]</p>
</div>
[{if $ldap_is_installed === true}]
	<fieldset class="m5" style="display:none">
		<legend>Information</legend>
		<div>
			[{$information_text}]
		</div>
	</fieldset>

	<fieldset class="overview">
		<legend><span>Server</span></legend>
		<table class="contentTable m10">
			<tr>
				<td class="key">[{isys type="f_label" name="C__LDAP_IMPORT__LDAP_SERVERS" ident="LDAP Server"}]</td>
				<td class="value">[{isys type="f_dialog" name="C__LDAP_IMPORT__LDAP_SERVERS" id="C__LDAP_IMPORT__LDAP_SERVERS"}]</td>
			</tr>
			<tr>
				<td class="key">[{isys type="f_label" name="C__LDAP_IMPORT__LDAP_DN" ident="LDAP - DN"}]</td>
				<td class="value">[{isys type="f_text" name="C__LDAP_IMPORT__LDAP_DN" id="C__LDAP_IMPORT__LDAP_DN"}]</td>
			</tr>
		</table>
		<div class="m10">
			[{isys type="f_button" name="filter_check" p_strValue="LC__UNIVERSAL__READ_DIRECTORY" icon="`$dir_images`icons/silk/arrow_down.png"}]
		</div>
	</fieldset>

	<fieldset class="overview" style="display:none;" id="result_fieldset">
		<legend><span>[{isys type="lang" ident="LC__MODULE__JDISC__IMPORT__RESULT"}]</span></legend>

		[{isys type="f_button" name="C__MODULE__LDAP__IMPORT__BUTTON" id="C__MODULE__LDAP__IMPORT__BUTTON" p_strValue="LC__MODULE__LDAP__START_IMPORT" p_strClass="import-button mt15 m10"}]
		<div class="mt10">
			<table class="mainTable" cellpadding="0" cellspacing="0">
				<thead>
				<tr>
					<th width="10%"><input type="checkbox" id="mark_all" /></th>
					<th>[{isys type="lang" ident="LC__UNIVERSAL__TITLE"}]</th>
				</tr>
				</thead>
				<tbody id="result">

				</tbody>
			</table>
		</div>
		[{isys type="f_button" name="C__MODULE__LDAP__IMPORT__BUTTON" id="C__MODULE__LDAP__IMPORT__BUTTON" p_strValue="LC__MODULE__LDAP__START_IMPORT" p_strClass="import-button m10"}]
	</fieldset>
[{/if}]

[{if $error_message}]
<div class="m5 p10 error">
	[{$error_message}]
</div>
[{/if}]

<fieldset class="m5" id="result_output" style="display:none;">
	<div style="height:20px;">
		<div id="loader" class="m5" style="display:none;"></div>
	</div>

	<pre></pre>
</fieldset>

<script type="text/javascript">
	(function () {
		'use strict';

		var $read_directory_button = $('filter_check'),
			$mark_all_checkbox = $('mark_all'),
			$result_fieldset = $('result_fieldset');

		$read_directory_button.on('click', function () {
			var $load_directory_button = $('filter_check');

			$result_fieldset.show();
			$mark_all_checkbox.setValue(1);

			$load_directory_button.disable()
				.down('img').writeAttribute('src', '[{$dir_images}]ajax-loading.gif')
				.next('span').update('[{isys type="lang" ident="LC__UNIVERSAL__LOADING"}]');

			new Ajax.Request('?call=ldap_import&ajax=1&func=filter',
				{
					parameters: {
						ldap_dn: $('C__LDAP_IMPORT__LDAP_DN').value,
						ldap_server: $('C__LDAP_IMPORT__LDAP_SERVERS').value
					},
					method: "post",
					onSuccess: function (transport) {
						var json = transport.responseText.evalJSON();
						var even_odd = 'CMDBListElementsEven';
						var tr_classname = '';
						$('result').childElements().each(function (ele) {
							ele.remove();
						});
						json.each(function (ele) {
							tr_classname = 'listRow ' + even_odd;
							$('result')
								.insert(new Element('tr', {className:tr_classname})
									.update(new Element('td', {className:'mt2'})
										.update(new Element('input', {type: 'checkbox', name: 'ldap_objects[]', className: 'checkbox', value: ele.id})
											.observe('click', function () {
												this.checked = (!this.checked) ? true : false;
											})))
									.insert(new Element('td', {className: 'mt2'}).update(ele.title))
									.observe('click', function () {
										this.down().down().checked = (! this.down().down().checked);
									})
							);
							even_odd = (even_odd == 'CMDBListElementsEven') ? 'CMDBListElementsOdd' : 'CMDBListElementsEven';
						});
					},
					onComplete: function () {
						$load_directory_button.enable()
							.down('img').writeAttribute('src', '[{$dir_images}]icons/silk/arrow_down.png')
							.next('span').update('[{isys type="lang" ident="LC__UNIVERSAL__READ_DIRECTORY"}]');
					}
				}
			);
		});

		$mark_all_checkbox.on('click', function () {
			var checker = this.checked;

			$result_fieldset.select('input.checkbox').each(function (ele) {
				ele.checked = checker;
			})
		});

		$result_fieldset.select('.import-button').invoke('on', 'click', function () {
			var ldap_ids = '[';
			var ldap_selected = false;

			$result_fieldset.select('input.checkbox').each(function (ele) {
				if (ele.checked === true) {
					ldap_selected = true;
					ldap_ids += '"' + ele.value + '",';
				}
			});

			if (ldap_selected) {
				ldap_ids = ldap_ids.substring(0, ldap_ids.length - 1) + ']';

				$('result_output').show();
				$('loader').show()
					.update(new Element('img', {src:'[{$dir_images}]ajax-loading.gif', className:'mr5 vam'}))
					.insert(new Element('span').update('[{isys type="lang" ident="LC__UNIVERSAL__LOADING"}]'));

				new Ajax.Request('?call=ldap_import&ajax=1&func=import',
					{
						parameters: {
							ids: ldap_ids,
							ldap_dn: $('C__LDAP_IMPORT__LDAP_DN').value,
							ldap_server: $('C__LDAP_IMPORT__LDAP_SERVERS').value
						},
						method: "post",
						onSuccess: function (transport) {
							$('loader').hide();
							$('result_output').show().down('pre').update(transport.responseText);
						}
					}
				);
			} else {
				alert('[{isys type="lang" ident="LC__BROWSER__NO_SELECTION"}]');
			}
		});
	})();
</script>
