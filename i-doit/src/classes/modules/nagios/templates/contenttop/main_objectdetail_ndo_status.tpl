<script type="text/javascript">
	$('contentTopTitle').insert(new Element('span', {id: 'monitoring_ndo_head', className:'hide pl15'}));

	new Ajax.Request('?ajax=1&call=monitoring_ndo&func=load_ndo_state', {
		parameters: {'[{$smarty.const.C__CMDB__GET__OBJECT}]': '[{$smarty.get.objID}]'},
		method: "post",
		onSuccess: function (transport) {
			var json = transport.responseJSON,
				el = $('monitoring_ndo_head').removeClassName('hide'),
				img = new Element('img', {className: 'ml5 vam'});

			if (! is_json_response(transport)) {
				idoit.Notify.error('The ajax request did not answer with JSON! Message: ' + transport.responseText)
			}

			if (json.success) {
				display_monitoring_state(json.data, $('monitoring_ndo_head'));
			} else {
				el.addClassName('red').update(json.message);
			}
		}});
</script>