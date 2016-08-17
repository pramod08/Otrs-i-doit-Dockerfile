<div id="tpl_list">
	<p class="p10">
		<img style="vertical-align:middle;" id="tpl_loader" class="mr5" src="images/ajax-loading.gif" />
		[{isys type="lang" ident="LC__UNIVERSAL__LOADING"}]
	</p>
</div>
<input type="hidden" id="type" name="type" value="[{$rec_status}]">
<script type="text/javascript">
	function load_list() {
        var url_string = String(document.location) + '&call=template_table&ajax=1';

		new Ajax.Updater(
			'tpl_list',
            url_string,
			{
				method:'post',
				parameters:$('isys_form').serialize(true)
			}
		);
	}
	 
	load_list();
</script>