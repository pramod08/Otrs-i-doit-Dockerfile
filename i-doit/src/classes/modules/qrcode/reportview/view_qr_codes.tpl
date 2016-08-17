<style>
	table.qrcode_table {
		width: 100%;
	}

	#report_view__qr_codes #qr_codes td {
		border: 1px solid #ddd
	}

	#report_view__qr_codes #qr_codes {
		overflow: auto;
	}

	#report_view__qr_codes #qr_codes table.qrcode_table td {
		border:none;
	}

	#layout_preview .layout {
		border: 1px dashed #888;
		padding: 5px;
		width: 150px;
		height: 50px;
		margin: 0 5px 5px 0;
		float: left;
	}

	#layout_preview .layout table {
		height: 100%;
	}

	#layout_preview .layout.active {
		border: 2px solid #E64117;
		padding: 4px;
	}
</style>

<div id="report_view__qr_codes">
	<table class="contentTable">
		<tr>
			<td>
				<div class="ml10">
					<p class="bold mb5">[{isys type="lang" ident="LC__REPORT__VIEW__QR_CODES_SELECT_OBJECTS"}]</p>

					[{isys type="f_popup" name="C__QR_CODE_OBJ_SELECTION" p_strPopupType="browser_object_ng" multiselection=true secondSelection=false p_bInfoIconSpacer=0}]

					<table class="mt10 mb10">
						<tr>
							<td class="vat" style="width:240px; padding-right: 20px;">

								<table style="width:100%;">
									<tr>
										<td>[{isys type="f_label" name="C__QR_CODE_SIZE" ident="LC__REPORT__VIEW__QR_CODES_SIZE"}]</td>
										<td>[{isys type="f_count" p_bNeg="0" p_strClass="verysmall" name="C__QR_CODE_SIZE" p_strValue="3" p_onChange="window.qr_code_size_change();"}]</td>
									</tr>
									<tr>
										<td>[{isys type="f_label" name="C__QR_CODE_COLUMNS" ident="LC__REPORT__VIEW__QR_CODES_COLUMNS"}]</td>
										<td>[{isys type="f_count" p_bNeg="0" p_strClass="verysmall" name="C__QR_CODE_COLUMNS" p_strValue="3" p_onChange="window.draw_cells();"}]</td>
									</tr>
									<tr>
										<td>[{isys type="f_label" name="C__QR_CODE_ERROR_CORRECTION" ident="LC__REPORT__VIEW__QR_CODES__CORRECTION"}]</td>
										<td>[{isys type="f_dialog" p_strClass="small" name="C__QR_CODE_ERROR_CORRECTION" p_bSort=false p_bDbFieldNN=true p_onChange="window.qr_code_size_change();"}]</td>
									</tr>
									<tr>
										<td>[{isys type="f_label" name="C__QR_CODE_DEFAULT_TEXT_ALIGNMENT" ident="LC__QR_CODE_DEFAULT_TEXT_ALIGNMENT"}]</td>
										<td>[{isys type="f_dialog" p_strClass="small" name="C__QR_CODE_DEFAULT_TEXT_ALIGNMENT" p_bSort=false p_bDbFieldNN=true p_onChange="window.qr_code_alignment_change();"}]</td>
									</tr>
								</table>

							</td>
							<td class="border-left vat" style="padding-left: 20px;">
								<h3 class="mt5 mb5">[{isys type="lang" ident="LC__REPORT__VIEW__QR_CODES__LAYOUT_SELECTION"}]</h3>

								<div id="layout_preview">
								[{foreach $layouts as $key => $layout}]
									<div data-layout="[{$key}]" class="layout mouse-pointer">
										[{$layout}]
									</div>
								[{/foreach}]
								</div>
							</td>
						</tr>
					</table>

					<button type="button" id="data-loader" class="btn mr5">
						<img src="[{$dir_images}]icons/silk/bullet_arrow_down.png" class="mr5" /><span>[{isys type="lang" ident="LC__UNIVERSAL__LOAD"}]</span>
					</button>
					<button type="button" id="qr-code-popup-loader" class="btn mr15">
						<img src="[{$dir_images}]icons/silk/application_double.png" class="mr5" /><span>[{isys type="lang" ident="LC__REPORT__VIEW__QR_CODES_POPUP"}]</span>
					</button>
					<a href="[{$configuration_url}]" class="btn">
						<img src="[{$dir_images}]icons/silk/wrench.png" class="mr5" /><span>[{isys type="lang" ident="LC__REPORT__VIEW__QR_CODES__CONFIGURATION_LINK"}]</span>
					</a>
				</div>
			</td>
		</tr>
	</table>

	<fieldset class="overview">
		<legend><span>[{isys type="lang" ident="LC__UNIVERSAL__RESULT"}]</span></legend>
		<div id="qr_codes" class="p10"></div>
	</fieldset>

</div>

<script type="text/javascript">
	var obj_ids,
		data,
		layout = 0;

	// Openes a new window with the results.
	$('qr-code-popup-loader').on('click', function () {
		obj_ids = $F('C__QR_CODE_OBJ_SELECTION__HIDDEN');

		if (! obj_ids.blank()) {
			var newwindow = window.open(
				'[{$ajax_url}]&objects=' + obj_ids + '&layout=' + layout + '&size=' + parseInt($F('C__QR_CODE_SIZE')) + '&cols=' + parseInt($F('C__QR_CODE_COLUMNS')) + '&error_correction=' + parseInt($F('C__QR_CODE_ERROR_CORRECTION')) + '&printview=1&text_alignment=' + $F('C__QR_CODE_DEFAULT_TEXT_ALIGNMENT'),
				'QR Code popup',
				'height=640,width=480,scrollbars=yes,menubar=yes');

			newwindow.focus();
		}
	});

	// Loads the results of the selected objects.
	$('data-loader').on('click', function () {
		obj_ids = $F('C__QR_CODE_OBJ_SELECTION__HIDDEN');

		if (! obj_ids.blank()) {

			new Ajax.Request('[{$ajax_url}]', {
				parameters:{
					obj_ids: obj_ids
				},
				method:"post",
				onSuccess:function (transport) {
					var json = transport.responseJSON;

					if (json.success) {
						data = json.data;
						window.draw_cells();
					} else {
						$('qr_codes').insert(new Element('p', {className:'error p5'}).update(json.message));
					}
				}
			});
		}
	});

	$('layout_preview').on('click', '.layout', function (ev) {
		$$('#layout_preview div.layout.active').invoke('removeClassName', 'active');

		layout = ev.findElement('div.layout').addClassName('active').readAttribute('data-layout');

		$('data-loader').simulate('click');
	});

	// Changes the QR code size "on the fly".
	window.qr_code_size_change = function () {
		// We need this to set the logo image to the same size as the qr-code.
		var height = 40 + (parseInt($F('C__QR_CODE_SIZE')) * 10);

		// Now we try to set the logo to the same size as the QR Code.
		$('qr_codes').select('table.qrcode_table img.qr-code-img:not(.icon), table.qrcode_table img.qr-code-logo').invoke('writeAttribute', 'height', height + 'px');
	};

	window.qr_code_alignment_change = function () {
		var alignment = $F('C__QR_CODE_DEFAULT_TEXT_ALIGNMENT');

		$('qr_codes').select('.description')
			.invoke('removeClassName', 'left')
			.invoke('removeClassName', 'center')
			.invoke('removeClassName', 'right')
			.invoke('removeClassName', 'justify')
			.invoke('addClassName', alignment);
	};

	// Renders the QR code table.
	window.draw_cells = function () {
		var i,
			result_container = $('qr_codes').update(),
			qr_data,
			table = new Element('table', {style:'width:100%;'}),
			tr = new Element('tr'),
			added = false,
			qrcode_table_tpl = $$('#layout_preview div.layout[data-layout="' + layout + '"]')[0],
			qrcode_table,
			qrcode_table_img,
			qrcode_table_description,
			qrcode_table_logo,
			columns = $F('C__QR_CODE_COLUMNS'),
			error_correction = $F('C__QR_CODE_ERROR_CORRECTION');

		for (i in data) {
			if (data.hasOwnProperty(i)) {
				qr_data = data[i];
				added = false;
				qrcode_table = qrcode_table_tpl.clone(true);
				qrcode_table_img = qrcode_table.down('img.qr-code-img');
				qrcode_table_description = qrcode_table.down('.description').addClassName($F('C__QR_CODE_DEFAULT_TEXT_ALIGNMENT'));
				qrcode_table_logo = qrcode_table.down('.qr-code-logo');

				if (qrcode_table_img) {
					qrcode_table_img.writeAttribute('src', qr_data.url + '&s=10&e=' + error_correction);
				}

				if (qrcode_table_description) {
					qrcode_table_description.update(qr_data.description);
				}

				if (qrcode_table_logo) {
					if (qr_data.logo) {
						qrcode_table_logo.writeAttribute('src', qr_data.logo);
					} else {
						qrcode_table_logo.writeAttribute('src', '[{$dir_images}]logo.png');
					}
				}

				if (! qr_data.success) {
					if (qrcode_table_img) {
						qrcode_table_img.writeAttribute('src', qr_data.url).addClassName('icon');
					}

					if (qrcode_table_description) {
						qrcode_table_description.addClassName('red');
					}
				}

				tr.insert(new Element('td', {className:'center'}).update(qrcode_table));

				if (((parseInt(i)+1) % columns) == 0) {
					added = true;
					table.insert(tr);
					tr = new Element('tr');
				}
			}
		}

		if (added === false) {
			table.insert(tr);
		}

		result_container.insert(table);

		window.qr_code_size_change();
	};

	// By default, we select the first "layout".
	$$('#layout_preview div.layout')[0].simulate('click');
</script>