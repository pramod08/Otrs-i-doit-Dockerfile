<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title>i-doit barcode</title>
	<meta name="author" content="synetics gmbh" />
	<meta name="description" content="i-doit" />

	<script type="text/javascript" language="JavaScript" src="[{$dir_tools}]js/prototype/prototype.js"></script>
	<script type="text/javascript" language="JavaScript" src="[{$dir_tools}]js/scriptaculous/src/scriptaculous.js?load=effects"></script>
</head>
<body>

<style>
	body {
		font-family: "Lucida Grande", Tahoma, Arial, Helvetica, sans-serif;
		color: #000;
		font-size: 14pt;
		margin: 0;
	}

	table td {
		text-align: center;
	}

	p {
		margin: 0;
	}

	.red {
		color: #a00;
	}

	table.qrcode_table {
		width: 100%;
	}

	table.qrcode_table td {
		border: none;
	}

	#layout {
		display: none;
	}
</style>

<div id="resultfield"></div>

<div id="layout">[{$layout}]</div>

<script type="text/javascript" language="JavaScript">
	new Ajax.Request('[{$ajax_url}]',
		{
			parameters:{
				obj_ids: '[{$obj_ids}]'
			},
			method:"post",
			onSuccess:function (transport) {
				var json = transport.responseJSON,
					i,
					data = json.data,
					result_container = $('resultfield').update(),
					qr_data,
					table = new Element('table', {style:'width:100%;'}),
					tr = new Element('tr'),
					added = false,
					columns = '[{$columns}]',
					qrcode_table_tpl = $('layout').down(),
					qrcode_table,
					qrcode_table_img,
					qrcode_table_description,
					qrcode_table_logo,
					height = 40 + (parseInt('[{$qr_code_size}]') * 10);

				for (i in data) {
					if (data.hasOwnProperty(i)) {
						qr_data = data[i];
						added = false;

						qrcode_table = qrcode_table_tpl.clone(true);
						qrcode_table_img = qrcode_table.down('img.qr-code-img');
						qrcode_table_description = qrcode_table.down('.description').setStyle({textAlign: '[{$text_alignment}]'});
						qrcode_table_logo = qrcode_table.down('.qr-code-logo');

						if (qrcode_table_img) {
							qrcode_table_img.writeAttribute({src: qr_data.url + '&s=10&e=[{$qr_code_error_correction}]', height: height + 'px'});
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
								qrcode_table_img.writeAttribute({src: qr_data.url, height: null});
							}

							if (qrcode_table_description) {
								qrcode_table_description.addClassName('red');
							}
						}

						tr.insert(new Element('td').update(qrcode_table));

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

				if(window.print) {
					window.setTimeout(function () {
						window.print();
					}, 1000);
				}
			}
		});
</script>

</body>
</html>
