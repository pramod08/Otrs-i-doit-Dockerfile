<script type="text/javascript">
	function synchronizeCustomFields() {	
		new Ajax.Updater('synchro_complete', '?call=rt_synchronize_custom_fields&ajax=1',
			{
				method:'post',	
				onLoading:function() {
					$('synchro_complete').show();
				},
				onComplete:function() { 
					 
				}
			});
	}
</script>

<table class="contentTable">
	<tr>
		<td class="value"><input value="[{isys type="lang" ident="LC__REQUEST_TRACKER__SYNCHRONIZE_CUSTOM_FIELDS"}]" type="button" onclick="synchronizeCustomFields();" /></td>
	</tr>	
</table>
<div style="display:none;" id="synchro_complete">
	<img src="images/ajax-loading.gif" class="m5" style="vertical-align:middle;" /> <span>[{isys type="lang" ident="LC__REQUEST_TRACKER__SYNCHRONIZING__LOADING"}]</span>
</div>