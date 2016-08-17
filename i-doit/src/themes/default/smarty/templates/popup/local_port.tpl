[{assign var="resultField" value=$smarty.get.resultField|default:""}]

<body>
	<script language="JavaScript" type="text/javascript">
	
		function move_selection_to_parent() {
			var peText		= parent.opener.document.getElementsByName("[{$resultField}]__VIEW")[0];
			var peHidden	= parent.opener.$("[{$resultField}]__HIDDEN");
			
			peHidden.value = format_selection();
			peText.value = '';
			
			$$('#port_list li').each(function(e){
				if (e.down(0).down(0).value > 0 && e.down(0).down(0).checked)
					peText.value += e.down(0).down(1).title + ', ';
			});
			peText.value = peText.value.substr(0, peText.value.length - 2);
		}
		 
		function format_selection() {
			$$('.ports').each(function(e){
				if (e.checked) $('selIDs').value += e.value + ',';
			});
			$('selIDs').value = $('selIDs').value.substr(0, $('selIDs').value.length - 1);
			
			return $('selIDs').value;
		}

		function close_window() {
			window.close();
		}
		
	</script>
	
	<style type="text/css">
		
		#port_list {
			list-style:none;
			margin:0;;
			padding:0;
		}
		
		#port_list li {
			padding:5px;
		}
		#port_list li:hover {
			background:#eee;
		}
		
	</style>
	<form name="browser">
	
	<div class="m10">
		 <h2 class="mb5">
		 	[{isys type="lang" ident="LC__POPUP__BROWSER__PORT_TITLE"}]
		 </h2>
		<div>
			<div style="border:1px solid #31ACC2; width: 100%; background-color:#E7F5F8">
			
			<p class="p10" style="background:#fff;">
				[{isys type="lang" ident="LC__POPUP__BROWSER__SELECTED_OBJECT"}]: <strong>[{$obj_title}]</strong>
			</p>
			
			<div>
				[{if is_object($ports) && $ports->num_rows() > 0}]
					
					<ul style="list-style:none;" id="port_list">
						[{while $addr = $ports->get_row()}]
							
							<li>
								<label>
									
									<input type="[{if !$smarty.get.singleSelection}]checkbox[{else}]radio[{/if}]" name="port[]" class="ports" value="[{$addr.isys_catg_port_list__id}]" [{if $preselection[$addr.isys_catg_port_list__title]}]checked="checked"[{/if}] />
									
									<span title="[{$addr.isys_catg_port_list__title}]">
										[{$addr.isys_catg_port_list__title}]
										
										[{if $addr.isys_catg_netp_list__title}]
											([{$addr.isys_catg_netp_list__title}])
										[{/if}]
										
										[{if $addr.isys_cats_net_ip_addresses_list__title}]
											([{$addr.isys_cats_net_ip_addresses_list__title}])
										[{/if}]
									</span>
								</label>
							</li>

						[{/while}]
					</ul>
					
				[{else}]
					<p class="p10">
						[{isys type="lang" ident="LC__POPUP__BROWSER__NO_PORTS"}].
					</p>
				[{/if}]
			</div>
			
			<input type="hidden" id="selIDs" name="selIDs" value="" />
		</div>
		<div style="text-align:center;" class="p10">
         [{isys	type="f_button"	p_bDisabled="0" p_strAccessKey="s" p_onClick="move_selection_to_parent(); close_window();" p_strValue="LC__UNIVERSAL__BUTTON_SAVE"}]
	 	 [{isys	type="f_button"	p_bDisabled="0"	p_onClick="close_window();"	p_strValue="LC__UNIVERSAL__BUTTON_CANCEL"}]
		</div>
	 </div>
	 
	</form>
</body>