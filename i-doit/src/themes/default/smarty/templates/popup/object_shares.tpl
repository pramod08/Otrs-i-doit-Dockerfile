[{assign var="resultField" value=$smarty.get.resultField|default:""}]

<body>
	<script language="JavaScript" type="text/javascript">
		var g_selected_devices = '';
	
		function move_selection_to_parent() {
			var peText		= parent.opener.document.getElementsByName("[{$resultField}]__VIEW")[0];
			var peHidden	= parent.opener.document.getElementsByName("[{$resultField}]__HIDDEN")[0];
			
			if(peText && peHidden) {
				peText.value	= document.getElementById('selectedFullText').innerHTML;
				peHidden.value	= g_selected_devices;		
			}
		}
		

		function close_window() {
			window.close();
		}
		

		// FOLLOWING THE DEVICE LIST AS ARRAY
		var g_shares = [];
		[{foreach from=$sharesList item=share_name key=share_id}]
			g_shares["[{$share_id}]"] = "[{$share_name|escape}]";
		[{/foreach}]
		
		 

		function refresh_selected() {
			var l_elements = document.getElementsByName('devicesInPool[]');
			var l_text = '';
			var l_devices = 0;
			
			g_selected_devices = '';

			for(var l_i = 0; l_i < l_elements.length; l_i++) {
				if(l_elements[l_i].checked) {
					l_text += g_shares[l_elements[l_i].value];
					l_text += ', ';
					g_selected_devices += l_elements[l_i].value;
					g_selected_devices += ',';
					l_devices++;
					
				}
			}
			
			if(l_devices == 0) {
				l_text = '[{isys type="lang" ident="LC_UNIVERSAL__NONE_SELECTED"}]';
			} else {
				l_text = l_text.substring(0, l_text.length-2);
			}
			
			$('selectedFullText').update(l_text);
			
		}
	</script>
	<form name="browser">
	<input type="hidden" id="selFull" name="selFull" value="[{$selFull}]" />
	<input type="hidden" id="selID" name="selID" value="" />
	<div style="margin:15px;">
		  <h1 style="border-bottom:1px solid #ccc;margin:0;">[{isys type="lang" ident="LC__CMDB__SHARES_POPUP__SHARES"}]-Browser</h1>
		  <h3 style="color:#ccc;margin:0 0 10px 0;"></h3>
		<div>
		 <div style="border:1px solid #31ACC2; width: 100%; background-color:#E7F5F8">
			 <div style="border:1px solid #31ACC2; margin:5px; height: 250px; overflow:auto">
			  [{$browser}]
			 </div>
			 
			 <div style="text-align: center;">
			  [{if $currentObjFull != ""}]
			  	[{$currentObjFull}].<br /><br />
			  [{/if}]
				<table style="width:100%">
					<tr style="text-align:left">
						<td>
							[{isys type="lang" ident="LC_SHARE_POPUP__CHOSEN_SHARE"}]:
						</td>
						<td>
							<span ID="selectedFullText" style="font-weight: bold;"></span>
						</td>
					</tr>
					<tr style="text-align:left">
						
					</tr>
				</table>
			</div>
		 </div>
		</div>
		<div style="text-align:center;clear: both">
		 <br />
         [{isys	type="f_button"	p_bDisabled="0" id="BUTTON_SAVE" p_strAccessKey="s" p_onClick="move_selection_to_parent(); close_window();" p_strValue="LC__UNIVERSAL__BUTTON_SAVE"}]
	 	 [{isys	type="f_button"	p_bDisabled="0"	p_onClick="close_window();"	p_strValue="LC__UNIVERSAL__BUTTON_CANCEL"}]
		</div>
	 </div>
	 
	
	 
	<script language="JavaScript" type="text/javascript">
		refresh_selected();
	</script>
	</form>
</body>