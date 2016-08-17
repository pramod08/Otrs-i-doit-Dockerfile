<h2 class="p5 gradient border-bottom">[{isys type="lang" ident="LC__MODULE__CHECK_MK__EXPORT"}]</h2>

<table class="contentTable" style="border-top:none;">
	<tr>
		<td class="key">[{isys type="f_label" name="C__MODULE__CHECK_MK__EXPORT_LANGUAGE" ident="LC__MODULE__CHECK_MK__EXPORT_LANGUAGE"}]</td>
		<td class="value">[{isys type="f_dialog" name="C__MODULE__CHECK_MK__EXPORT_LANGUAGE"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" name="C__MODULE__CHECK_MK__EXPORT_STRUCTURE" ident="LC__MODULE__CHECK_MK__EXPORT_STRUCTURE"}]</td>
		<td class="value">[{isys type="f_dialog" name="C__MODULE__CHECK_MK__EXPORT_STRUCTURE"}]<br /><strong class="ml20 red">* [{$export_warning}]</strong></td>
	</tr>
</table>

<pre id="export_result" class="p5 mt10 border" style="border-left: none; border-right: none;">[{isys type="lang" ident="LC__MODULE__CHECK_MK__WAITING"}]</pre>

<button class="btn btn-large m5" type="button" id="start_export_button">[{isys type="lang" ident="LC__MODULE__CHECK_MK__START_EXPORT"}]</button>
<button class="btn btn-large m5" type="button" id="start_shellscript_button">[{isys type="lang" ident="LC__MODULE__CHECK_MK__START_SHELLSCRIPT"}]</button>

<div class="m5"><img src="[{$dir_images}]icons/silk/information.png" class="vam mr5" /><span class="vam">[{isys type="lang" ident="LC__MODULE__CHECK_MK__START_SHELLSCRIPT_DESCRIPTION"}]</span></div>

<script type="text/javascript">
	(function () {
		"use strict";

		$('start_export_button').on('click', function () {
			var result_box = $('export_result')
				.removeClassName('exception')
				.removeClassName('note')
				.update(new Element('img', {src:'[{$dir_images}]ajax-loading.gif', className:'vam mr5'}))
				.insert(new Element('span', {className:'vam'}).update('[{isys type="lang" ident="LC__UNIVERSAL__LOADING"}]'));

			new Ajax.Request('[{$ajax_url_export}]', {
				method: 'post',
				parameters: {
					export_structure:$F('C__MODULE__CHECK_MK__EXPORT_STRUCTURE'),
					export_language:$F('C__MODULE__CHECK_MK__EXPORT_LANGUAGE')
				},
				onSuccess: function (response) {
					var json = response.responseJSON,
						log_list = [],
						file_list = [],
						icon,
						file,
						log,
						i;

					if (Object.isUndefined(json)) {
						result_box.addClassName('exception').update(response.responseText);
						return;
					}

					if (json.success) {
						for (i in json.data.log) {
							if (json.data.log.hasOwnProperty(i)) {
								log = json.data.log[i];
								icon = json.data.log_icons[log.level];

								log_list.push(new Element('img', {src:icon, className:'vam mr5'}).outerHTML + new Element('span', {className:'vam'}).update(log.message).outerHTML);
							}
						}

						result_box.addClassName('note').update(log_list.join("\n") + "\n\n[{isys type="lang" ident="LC__MODULE__CHECK_MK__EXPORTED_FILES"}]\n");

						for (i in json.data.files) {
							if (json.data.files.hasOwnProperty(i)) {
								file = json.data.files[i];

								file_list.push('&raquo; ' + file);
							}
						}

						result_box.insert(file_list.join("\n"));
					} else {
						result_box.addClassName('exception').update(json.message);
					}
				}.bind(this)
			});
		});

		$('start_shellscript_button').on('click', function () {
			var result_box = $('export_result')
				.removeClassName('exception')
				.removeClassName('note')
				.update(new Element('img', {src:'[{$dir_images}]ajax-loading.gif', className:'vam mr5'}))
				.insert(new Element('span', {className:'vam'}).update('[{isys type="lang" ident="LC__UNIVERSAL__LOADING"}]'));

			new Ajax.Request('[{$ajax_url_shellscript}]', {
				method: 'post',
				onSuccess: function (response) {
					var json = response.responseJSON;

					is_json_response(response, true);

					if (json.success) {
						result_box.addClassName('note').update(json.data);
					} else {
						result_box.addClassName('exception').update(json.message);
					}
				}.bind(this)
			});
		});
	}());
</script>
