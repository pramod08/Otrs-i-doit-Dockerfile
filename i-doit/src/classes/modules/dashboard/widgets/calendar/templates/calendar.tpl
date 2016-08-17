<style type="text/css">
	table.calendar {
		width: 100%;
		border-collapse: separate;
		border-spacing: 2px 2px;
	}

	table.calendar td {
		text-align: center;
		border: 1px solid #ddd;
		width: 14.28%;
	}

	table.calendar td.weekend {
		background: #fdd;
		color: #800;
	}

	table.calendar td.grey {
		background: #ddd;
		color: #888;
	}

	table.calendar td.today {
		background: #F3ECB1;
		border-color: #C7BE76;
		color: #1C1B0D;
	}

	table.calendar td span.marker {
		border-width: 3px;
		padding: 1px 3px;
		margin: 0 -8px;
	}

	#[{$unique_id}]_popup {
		position: absolute;
		background: #fff;
	}

	#[{$unique_id}]_popup_content {
		overflow: hidden;
		overflow-y: auto;
	}
</style>

[{if $title}]
<h3 class="gradient p5 text-shadow border-bottom border-ccc">[{$title}]</h3>
[{else}]
<h3 class="gradient p5 text-shadow border-bottom border-ccc">[{isys type="lang" ident="LC__WIDGET__CALENDAR"}]</h3>
[{/if}]

<table width="100%">
	<tr>
		<td class="vat">

			<table class="calendar" data-year="[{$data_prev.year}]" data-month="[{$data_prev.month_num}]">
				<thead>
				<tr>
					<th colspan="7" class="p5 gradient border border-ccc text-shadow">
						[{$data_prev.month}] - [{$data_prev.year}]
					</th>
				</tr>
				</thead>
				<tbody>
					[{foreach from=$data_prev.data item=week}]
					<tr>
						[{foreach from=$week item=day}]
						<td class="[{$day.css_class}][{if $day.events}] bold mouse-pointer[{/if}]" data-date="[{$day.date}]"[{if $day.events}] data-events="[{$day.events_json|escape:"html"}]"[{/if}]>
							[{$day.date}]
						</td>
						[{/foreach}]
					</tr>
					[{/foreach}]
				</tbody>
			</table>

		</td>
		<td class="vat">

			<table class="calendar" data-year="[{$data.year}]" data-month="[{$data.month_num}]">
				<thead>
				<tr>
					<th colspan="7" class="p5 gradient border border-ccc text-shadow">
						[{$data.month}] - [{$data.year}]
					</th>
				</tr>
				</thead>
				<tbody>
					[{foreach from=$data.data item=week}]
					<tr>
						[{foreach from=$week item=day}]
						<td class="[{$day.css_class}][{if $day.events}] bold mouse-pointer[{/if}]" data-date="[{$day.date}]"[{if $day.events}] data-events="[{$day.events_json|escape:"html"}]"[{/if}]>
							[{$day.date}]
						</td>
						[{/foreach}]
					</tr>
					[{/foreach}]
				</tbody>
			</table>

		</td>
		<td class="vat">

			<table class="calendar" data-year="[{$data_next.year}]" data-month="[{$data_next.month_num}]">
				<thead>
				<tr>
					<th colspan="7" class="p5 gradient border border-ccc text-shadow">
						[{$data_next.month}] - [{$data_next.year}]
					</th>
				</tr>
				</thead>
				<tbody>
					[{foreach from=$data_next.data item=week}]
					<tr>
						[{foreach from=$week item=day}]
						<td class="[{$day.css_class}][{if $day.events}] bold mouse-pointer[{/if}]" data-date="[{$day.date}]"[{if $day.events}] data-events="[{$day.events_json|escape:"html"}]"[{/if}]>
							[{$day.date}]
						</td>
						[{/foreach}]
					</tr>
					[{/foreach}]
				</tbody>
			</table>

		</td>
	</tr>
</table>

<div id="[{$unique_id}]_popup" class="border blurred-shadow" style="display: none;">
	<h4 class="gradient p5" style="border-bottom:1px solid #888;">
		<span class="close fr mr5 mouse-pointer">&times;</span>
		[{isys type="lang" ident="LC__WIDGET__CALENDAR__EVENT_TITLE"}]<span class="date"></span>
	</h4>
	<div id="[{$unique_id}]_popup_content"></div>
</div>

<script type="text/javascript">
	(function () {
		'use strict';

		var $widget = $('[{$unique_id}]'),
			$widget_popup = $('[{$unique_id}]_popup'),
			$widget_popup_content = $('[{$unique_id}]_popup_content'),
			$today_event = $widget.down('td.today.event');

		$widget.on('click', 'td.event', function (ev) {
			var $td = ev.findElement('td'),
				date = $td.readAttribute('data-date'),
				month = $td.up('table').readAttribute('data-month'),
				year = $td.up('table').readAttribute('data-year');

			$widget_popup.down('span.date').update(date + '.' + month + '.' + year);
			$widget_popup.setStyle({top:'12.5%', left:'25%', width:(this.getWidth() / 2) + 'px', height:(this.getHeight() / 1.5) + 'px'}).appear({duration:0.5});


			new Ajax.Request('[{$ajax_url}]', {
				parameters: {
					events: $td.readAttribute('data-events'),
					year: year,
					month: month,
					day: date
				},
				method: 'post',
				onSuccess: function (response) {
					var json = response.responseJSON,
						events = [],
						i;

					if (json.success) {
						for (i in json.data) {
							if (json.data.hasOwnProperty(i)) {
								if (json.data[i].hasOwnProperty('data')) {
									events.push(json.data[i].data);
								} else {
									events.push(new Element('p', {className:'p5'}).update(json.data[i]).outerHTML);
								}
							}
						}

						$widget_popup_content.update(events.join('<hr />'));
					} else {
						$widget_popup_content.update(new Element('p', {className:'p5 error'}).update(json.message));
					}
				}
			});
		});

		$widget_popup.on('click', '.close', function () {
			$widget_popup.fade({
				duration:0.5,
				afterFinish:function () {
					$widget_popup_content.update();
				}
			});
		});

		if ($today_event) {
			$today_event.simulate('click');
		}
	})();
</script>