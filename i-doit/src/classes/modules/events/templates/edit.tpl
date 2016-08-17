<fieldset class="overview">
	<legend><span style="border-top:0;">[{isys type="lang" ident="LC__MODULE__EVENTS__SELECT_CORRESPONDING_EVENT"}]</span></legend>
	<table class="contentTable">
		<tr>
			<td class="key">[{isys type='f_label' name='event_id' ident='LC__MODULE__EVENTS__EVENT'}]</td>
			<td class="value">
	            [{isys type='f_dialog' name='event_id' id="event_id" p_bDbFieldNN=1 tab='1' p_bSort=1}]
	        </td>
		</tr>
		<tr>
			<td class="key grey">[{isys type='f_label' name='event_id' ident='LC__CMDB__CATG__DESCRIPTION'}]</td>
			<td class="pl20 grey" id="event_description"></td>
		</tr>
		<tr>
			<td class="key">[{isys type='f_label' name='title' ident='LC__MODULE__EVENTS__DESCRIPTION'}]</td>
			<td class="value">
	            [{isys type='f_text' name='title' p_strPlaceholder="" tab='1'}]
	        </td>
		</tr>
	</table>
</fieldset>

<fieldset class="overview">
	<legend><span>[{isys type="lang" ident="LC__MODULE__EVENTS__CONFIGURE_CALL"}]</span></legend>

	<table class="contentTable">
		<tr>
			<td class="key">[{isys type='f_label' name='type' ident='LC__MODULE__EVENTS__TYPE'}]</td>
			<td class="value">
	            [{isys type='f_dialog' name='type' p_bDbFieldNN=1 tab='1'}]
	        </td>
		</tr>
		<tr>
			<td class="key">[{isys type='f_label' name='command' ident='LC__MODULE__EVENTS__COMMAND'}]</td>
			<td class="value">
	            [{isys type='f_text' name='command' p_strPlaceholder='/path/to/bash/script.sh' tab='1'}]
	        </td>
		</tr>
		<tr>
			<td class="key">[{isys type='f_label' name='parameters' ident='LC__MODULE__EVENTS__ADDITIONAL_PARAMETERS'}]</td>
			<td class="value">
	            [{isys type='f_text' name='parameters' p_strPlaceholder='' tab='1'}]
	        </td>
		</tr>
		<tr>
			<td class="key">[{isys type='f_label' name='mode' ident='LC__MODULE__EVENTS__MODE'}]</td>
			<td class="value">
	            [{isys type='f_dialog' name='mode' p_bDbFieldNN=1 tab='1'}]
	        </td>
		</tr>
	</table>
</fieldset>

<input type="hidden" name="eventSubscriptionID" value="[{$eventSubscriptionID}]" />

<script type="text/javascript">
	"use strict";

	(function () {
		if ($('event_id'))
		{
			$('event_id').on('change', function (ev)
			{
				var mapping = [{$descriptionMapping}];

				if (this.value in mapping)
				{
					$('event_description').update(mapping[this.value]);
				}
				else $('event_description').update('');

			});

			$('event_id').simulate('change');
		}
	}());
</script>