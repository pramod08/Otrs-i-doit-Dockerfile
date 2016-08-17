[{isys_group name="tom.popup.maintenance"}]
<div id="maintenance-popup-finish-maintenance">
	<h3 class="popup-header">
		<img class="fr mouse-pointer popup-closer" alt="x" src="[{$dir_images}]prototip/styles/default/close.png">

		<span>[{isys type="lang" ident="LC__MAINTENANCE__POPUP__FINISH_MAINTENANCE"}]</span>
	</h3>
	<div class="popup-content" style="height:250px;">
		<table class="contentTable" cellspacing="0" cellpadding="0">
			<tr>
				<td class="key vat">[{isys type="lang" ident="LC__MAINTENANCE__POPUP__MAINTENANCES_TO_FINISH"}]</td>
				<td class="value"><ul id="maintenance-popup-unfinished" class="m0 ml20 list-style-none"></ul></td>
			</tr>
			<tr>
				<td class="key vat">[{isys type="f_label" name="C__MAINTENANCE__POPUP__FINISH_COMMENT" ident="LC__MAINTENANCE__POPUP__FINISH_COMMENT"}]</td>
				<td class="value">[{isys type="f_textarea" name="C__MAINTENANCE__POPUP__FINISH_COMMENT"}]</td>
			</tr>
		</table>
	</div>
	<div class="popup-footer">
		<button id="maintenance-popup-button-finish-maintenance" type="button" class="btn">
			<img src="[{$dir_images}]icons/silk/tick.png" class="mr5"><span>[{isys type="lang" ident="LC__MAINTENANCE__POPUP__FINISH_MAINTENANCE"}]</span>
		</button>
		<button type="button" class="btn popup-closer">
			<img src="[{$dir_images}]icons/silk/cross.png" class="mr5"><span>[{isys type="lang" ident="LC_UNIVERSAL__ABORT"}]</span>
		</button>
	</div>
</div>

<script type="text/javascript">
	(function () {
		'use strict';

		var $popup = $('maintenance-popup-finish-maintenance'),
			$popup_content = $popup.down('.popup-content'),
			$comment = $('C__MAINTENANCE__POPUP__FINISH_COMMENT'),
			$finish_button = $('maintenance-popup-button-finish-maintenance'),
			$maintenance_id = $('C__MAINTENANCE__PLANNING__ID'),
			$maintenance_list = $('mainTable'),
			$selected_maintenances,
			$unfinished = $('maintenance-popup-unfinished'),
			ids = [],
			id;

		$popup.on('click', '.popup-closer', function () {
			popup_close();
		});

		$finish_button.on('click', function () {
			var last_error = false;

			new Ajax.Request('[{$ajax_url}]&func=finish-maintenances', {
				parameters: {
					ids: Object.toJSON(ids),
					comment: $comment.getValue()
				},
				onSuccess: function (response) {
					var json = response.responseJSON, i;

					if (json.success) {
						for (i in json.data) {
							if (json.data.hasOwnProperty(i) && json.data[i] !== true) {
								$unfinished.down('li[data-maintenance-id="' + i + '"]')
									.insert(new Element('img', {className:'ml20 vam mr5', src:'[{$dir_images}]icons/silk/error.png'}))
									.insert(new Element('span', {className:'red'}).update(json.data[i]));

								last_error = json.data[i];
							} else {
								$unfinished.down('li[data-maintenance-id="' + i + '"]')
									.insert(new Element('img', {className:'ml20 vam', src:'[{$dir_images}]icons/silk/tick.png'}));
							}
						}
					} else {
						last_error = json.message || response.responseText;
					}
				},
				onFailure: function (response) {
					last_error = response.responseText;
				},
				onComplete: function () {
					if (last_error) {
						idoit.Notify.error(last_error, {sticky:true});
					} else {
						idoit.Notify.success('[{isys type="lang" ident="LC__MAINTENANCE__NOTIFY__COMPLETED_SUCCESSFULLY"}]', {sticky:true});

						document.location.reload(true);
					}
				}
			});
		});

		if ($maintenance_id) {
			id = parseInt($maintenance_id.getValue());

			ids.push(id);

			$unfinished.insert(new Element('li', {'data-maintenance-id': id}).update($('C__MAINTENANCE__PLANNING__TYPE').down(':selected').innerHTML + ' (#' + id + ')'));
		} else {
			if ($maintenance_list) {
				$selected_maintenances = $maintenance_list.select('.listRow input:checked')
			}

			$selected_maintenances.invoke('up', 'tr').each(function ($el) {
				if ($el.down('span[data-finished="0"]')) {
					id = parseInt($el.down('td', 1).innerHTML);

					$unfinished.insert(new Element('li', {'data-maintenance-id': id}).update($el.down('td', 2).innerHTML + ' (#' + id + ')'));
					ids.push(id);
				}
			});
		}

		if (ids.length === 0) {
			$finish_button.disable();

			$popup_content.update(new Element('p', {className:'p5 m5 warning'})
				.update(new Element('img', {src:'[{$dir_images}]icons/silk/error.png', className:'vam mr5'}))
				.insert(new Element('span').update('[{isys type="lang" ident="LC__MAINTENANCE__POPUP__NO_MAINTENANCES_SELECTED"}]')))
		}
	})();
</script>
[{/isys_group}]