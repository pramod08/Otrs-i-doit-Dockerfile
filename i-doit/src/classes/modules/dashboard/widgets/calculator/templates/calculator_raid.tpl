<h3>[{isys type="lang" ident="LC__CMDB__CATG__RAID"}]</h3>
<table>
	<tr>
		<td>Raid-Level</td>
		<td>[{isys type="f_dialog" name="[{$unique_id}]_calculator-raid-raidlevel" p_bInfoIconSpacer="0" p_bDbFieldNN="0" p_arData=$rules.raid_lvls p_strClass="input-mini"}]</td>
	</tr>
	<tr>
		<td>Speicher Einheit</td>
		<td>[{isys type="f_dialog" name="[{$unique_id}]_calculator-raid-memory_unit" p_bInfoIconSpacer="0" p_bDbFieldNN="1" p_arData=$rules.memory_unit p_strClass="input-mini" p_strSelectedID=$rules.memory_selected}]</td>
	</tr>
</table>
<div id="[{$unique_id}]_calculator-raid-content">
	<button id="[{$unique_id}]_calculator-add-raid" type="button" class="btn mt5">
		<img src="[{$dir_images}]icons/silk/add.png" class="mr5" /><span>[{isys type="lang" ident="LC__UNIVERSAL__NEW_VALUE"}]</span>
	</button>

	<table id="[{$unique_id}]_calculator-raid-content-fields" class="m5">
		<tr>
			<td colspan="3" class="pb5" id="[{$unique_id}]_calculator-raid-content-result-content" style="display:none;">
				<button id="[{$unique_id}]_calculator-raid-content-button" type="button" class="btn">
					<img src="[{$dir_images}]icons/silk/table_edit.png" class="mr5" /><span>[{isys type="lang" ident="Calculate"}]</span>
				</button>
				[{isys type="f_text" p_strStyle="text-align:right"  name="[{$unique_id}]_calculator-raid-content-result" p_bReadonly="1" p_bEditMode="1" p_strClass="ml10 input input-mini" p_bInfoIconSpacer="0" p_strPlaceholder="0 GB"}]
			</td>
		</tr>
	</table>
</div>

<script type="text/javascript">
	(function () {
		"use strict";

		var $content_container = $('[{$unique_id}]_calculator-raid-content'),
			$raid_level_select = $('[{$unique_id}]_calculator-raid-raidlevel'),
			$result = $('[{$unique_id}]_calculator-raid-content-result'),
			$memory_unit = $('[{$unique_id}]_calculator-raid-memory_unit'),
			raid_min_disks = {
				"C__STOR_RAID_LEVEL__0": 2,
				"C__STOR_RAID_LEVEL__1": 2,
				"C__STOR_RAID_LEVEL__5": 3,
				"C__STOR_RAID_LEVEL__6": 4,
				"C__STOR_RAID_LEVEL__10": 4,
				"C__STOR_RAID_LEVEL__JBOD": 2
			};

		$('[{$unique_id}]_calculator-raid-content-button').on('click', function () {
			var smallest_hd = 0,
				l_val = 0,
				raid_capacity = 0,
				disk_amount = 0,
				min_disks;

			if ($raid_level_select.getValue() == -1) {
				$raid_level_select.addClassName('error');
			} else if ($raid_level_select.getValue() == "C__STOR_RAID_LEVEL__JBOD") {
				$content_container.select('.raidfield input').each(function (ele) {
					raid_capacity += parseInt(ele.value);
				});

				$result.setValue(raid_capacity + ' ' + $memory_unit.down('option:selected').innerHTML);
			} else {
				$raid_level_select.removeClassName('error');

				$content_container.select('.raidfield input').each(function ($el) {
					l_val = parseInt($el.getValue());

					if (smallest_hd == 0 || smallest_hd > l_val) {
						smallest_hd = l_val;
					}
				});

				switch ($memory_unit.getValue()) {
					case 'C__MEMORY_UNIT__MB':
						smallest_hd = parseFloat(smallest_hd / 1000);
						break;
					case 'C__MEMORY_UNIT__TB':
						smallest_hd = parseFloat(smallest_hd * 1000);
						break;
					case 'C__MEMORY_UNIT__B':
						smallest_hd = parseFloat(smallest_hd / Math.pow(1000, 3));
						break;
					case 'C__MEMORY_UNIT__KB':
						smallest_hd = parseFloat(smallest_hd / Math.pow(1000, 2));
						break;
					default:
						break;
				}

				min_disks = raid_min_disks[$raid_level_select.getValue()];

				if ($content_container.select('.raidfield input').length > 0) {
					$content_container.select('.raidfield input').each(function ($el) {
						if ($el.getValue() > 0) {
							disk_amount++;
						}
					});
				}

				if (disk_amount < min_disks) {
					$('[{$unique_id}]_calculator-messages').update('[{isys type="lang" ident="LC__WIDGET__CALCULATOR__RAID_CAPACITY_CALCULATOR__MINIMUM_HARDDISKS"}] ' + min_disks);

					Effect.Appear('[{$unique_id}]_calculator-messages', {
						duraction: 1.5,
						afterFinish: function () {
							Effect.Fade('[{$unique_id}]_calculator-messages', {duration: 2.5});
						}
					});
					$result.setValue('');
				}
				else {
					raid_capacity = raidcalc(disk_amount, smallest_hd, $raid_level_select.down('option:selected').text, '');

					$result.setValue(raid_capacity + ' GB');
				}
			}
		});

		$('[{$unique_id}]_calculator-add-raid').on('click', function () {
			var $table = $('[{$unique_id}]_calculator-raid-content-fields'),
				counter = $content_container.select('.raidfield a').length + 1;

			$('[{$unique_id}]_calculator-raid-content-result-content').show();

			var input_field = new Element('tr', {'class': 'raidfield'})
				.insert(new Element('td').update(new Element('a', {className: 'btn btn-small'}).insert(new Element('img', {src: '[{$dir_images}]icons/silk/cross.png'}))))
				.insert(new Element('td').update(new Element('span', {className: 'vam ml5 mr5'}).update('[{isys type="lang" ident="LC__STORAGE_TYPE__HARD_DISK"}] ' + counter)))
				.insert(new Element('td').update(new Element('input', {type: 'text', className: 'input input-mini', style: 'text-align:right', placeholder: '0'})))
				.insert(new Element('td').update(new Element('span', {className: 'vam ml5'}).update($memory_unit.down('option:selected').innerHTML)));

			$table.insert(input_field);

			$content_container.select('.raidfield a')
				.invoke('stopObserving')
				.invoke('on', 'click', function (ev) {
					ev.findElement('a').up('tr').remove();

					if ($content_container.select('.raidfield a').length == 0) {
						$('[{$unique_id}]_calculator-raid-content-result').setValue('');
						$('[{$unique_id}]_calculator-raid-content-result-content').hide();
					}
				});
		});
	})();
</script>