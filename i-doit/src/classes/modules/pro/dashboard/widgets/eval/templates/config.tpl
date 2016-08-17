<div id="eval-config">
	<table class="contentTable">
		<tr>
			<td class="key">
				[{isys type="f_label" name="widget-popup-config-animate" ident="LC__WIDGET__EVAL__CONFIG__LAYOUT"}]
			</td>
			<td class="value">
				[{isys type="f_dialog" name="layout" p_arData=$layout_options p_strClass="normal" p_bDbFieldNN=true p_strSelectedID=$rules.layout}]
			</td>
		</tr>
		<tr>
			<td class="key" style="vertical-align:top;">
				[{isys type="f_label" name="widget-popup-config-salutation" ident="LC__WIDGET__EVAL__CONFIG__SHORTFORM"}]
			</td>
			<td class="value">
				[{isys type="f_dialog" name="short_form" p_arData=$short_form_options p_strClass="normal" p_bDbFieldNN=true p_strSelectedID=$rules.short_form}]
			</td>
		</tr>
	</table>
</div>

<script type="text/javascript">
	var on_value_change = function () {
			var value = {
				layout:$F('layout'),
				short_form:$F('short_form')
			};

			$('widget-popup-config-hidden').setValue(Object.toJSON(value));
		};

	on_value_change();
	$('widget-popup-config-changed').setValue('1');
	$('layout', 'short_form').invoke('on', 'change', on_value_change);
</script>