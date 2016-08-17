[{assign var="resultField" value=$smarty.get.resultField|default:""}]
<body>
	<script language="JavaScript" type="text/javascript">
		var g_selected_clients = '';
		
		var g_clients = [];
		[{foreach from=$clientList item=client_name key=client_id}]
			g_clients["[{$client_id}]"] = "[{$client_name|escape}]";
		[{/foreach}]
		
		
		function refresh_selected(from) {
			if (from == 'obj')
				var l_elements = document.getElementsByName('clientsSelected[]');
			else if (from == 'loc')
				var l_elements = document.getElementsByName('clientsSelectedLocation[]');
				
			var l_text = '';
			var l_devices = 0;

			g_selected_clients = '';

			for(var l_i = 0; l_i < l_elements.length; l_i++) {
				if(l_elements[l_i].checked) {
					l_text += g_clients[l_elements[l_i].value];
					l_text += ', ';
					g_selected_clients += l_elements[l_i].value;
					g_selected_clients += ',';
					l_devices++;
				}
			}
 
			if(l_devices == 0) {
				l_text = '[{isys type="lang" ident="LC_UNIVERSAL__NONE_SELECTED"}]';
			} else {
				l_text = l_text.substring(0, l_text.length-2);
			}

			document.getElementById('sel_clients').innerHTML = l_text;
		}
		
	
		function move_selection_to_parent(p_eText, p_eHidden) {
			var valText		= $(p_eText).value;
			var valHidden	= $(p_eHidden).value;
			
			var peText		= parent.opener.document.getElementsByName("[{$resultField}]__VIEW")[0];
			var peHidden	= parent.opener.document.getElementsByName("[{$resultField}]__HIDDEN")[0];
			
			if(peText && peHidden) {
				peText.value	= $('sel_clients').innerHTML;
				peHidden.value	= g_selected_clients;
			}
		}
		
		var tree_active;
		
		function switch_view(p_toenable, p_todisable, p_todisable_2, p_tree) {
			var e;
			
			e = $(p_toenable);
			if(e && e.style) e.style.display = '';
			e = $(p_toenable + "_BUTTON");
			if(e) e.className = 'cell_selected';
			e = $(p_toenable + "_EXTRA");
			if(e) e.style.display = '';
			
			e = $(p_todisable);
			if(e && e.style) e.style.display = 'none';
			e = $(p_todisable + "_BUTTON");
			if(e) e.className = 'cell';
			e = $(p_todisable + "_EXTRA");
			if(e) e.style.display = 'none';
			
			e = $(p_todisable_2);
			if(e && e.style) e.style.display = 'none';
			e = $(p_todisable_2 + "_BUTTON");
			if(e) e.className = 'cell';
			e = $(p_todisable_2 + "_EXTRA");
			if(e) e.style.display = 'none';
			
			if (p_tree) {
				tree_active = p_tree;
			}
		}
		
		
		function close_window() {
			window.close();
		}
		
		
		function reload() {
			$('loader').appear();
			new Ajax.Submit(String(document.location),
						'object_filter',
						'browser',
						{
							method:'post',
							history:false,
							onComplete:function(){$('loader').hide();}
						});
			
		}
		
		var g_browser_obj;
		var g_browser_loc;
		var activate_filter = 0;
		
	</script>
	
	<div class="browserObject">
		<h1>LDEV Client-Browser</h1>
		
		<form name="browser" onsubmit="return false;" id="browser">
	    	<input type="hidden" id="selFull" name="selFull" value="[{$selFull}]" />
	    	<input type="hidden" id="selID" name="selID" value="[{$objID}]" />
			
			<table class="main" cellpadding="0" width="100%" cellspacing="0">
				<tr class="row">
				 <th class="cell_button" onclick="tree_expand();">
				  <img src="[{$dir_images}]expand.gif" alt="+" />
				 </th>
				 <th class="cell_button" onclick="tree_collapse();">
				  <img src="[{$dir_images}]collapse.gif" alt="-" />
				 </th>
				 <th class="cell" id="treeObject_BUTTON" onclick="switch_view('treeObject', 'treeLocation', 'treeFilter', g_browser_obj);">
				 	<span>[{isys type="lang" ident="LC__CMDB__BROWSER_OBJECT__OBJECT_VIEW"}]</span>
				 </th>
				 <th class="cell" id="treeLocation_BUTTON" onclick="switch_view('treeLocation', 'treeObject', 'treeFilter', g_browser_loc);">
				 	<span>[{isys type="lang" ident="LC__CMDB__BROWSER_OBJECT__LOCATION_VIEW"}]</span>
				 </th>
				 <th class="status">
		
				 	<div id="treeObject_EXTRA">
					[{isys_group name="status"}]
						[{isys
							type="f_dialog"
							name="cRecStatus"
							p_bEditMode="1"
							p_strStyle="width: 100px"
							p_onClick=""
							p_bDbFieldNN="1"
							p_onChange="window.location.href='$statusURL&status=' + this.options[this.options.selectedIndex].value;"}]
					[{/isys_group}]
					</div>
		
					<div id="treeLocation_EXTRA">
						<!-- Dummy DIV //-->
					</div>
					
					<div id="treeFilter_EXTRA">
						<!-- Dummy DIV //-->
					</div>
					
					
				 </th>
				</tr>
				</table>
				
			<div class="content">
				<div id="treeObject" style="display: none;">
				 
				 	<div class="tree">
						[{$treeObject}]
					</div>
				 
				</div>
				
				<div id="treeLocation" style="display: none;">
				 	<div class="tree">
						[{$treeLocation}]
					</div>
				</div>
				
				<div id="treeFilter" style="display: none;">
				 	<div style="padding-left:5px;" class="p10">
						
					 	[{isys type="f_text" id="filter" p_bInfoIconSpacer="0" p_onKeyPress="if (this.value.length>2)reload();" name="filter" p_bEditMode="1"}]
					 	[{isys type="f_button" p_onClick="reload();" p_strValue="Filter" p_bDisabled="0" name="submit" p_bEditMode="1"}]
					 	
					 	<div style="display:none;" class="fr p5" id="loader"><img src="images/ajax-loading.gif" /></div>
					 	<div id="object_filter" style="overflow:auto;" class="tree p10"></div>
						
					</div>
				</div>
				
			</div>
		
		<div class="detailheaderClient">
			<h3 class="m5">Gewählte Clients:</h3>
			<span class="m10" id="sel_clients">
			
			</span>
		</div>
		
		<div style="text-align:center;clear: both">
			 <br />
			 [{isys	p_bDisabled="0" p_strAccessKey="s" type="f_button" id="BUTTON_SAVE" p_onClick="move_selection_to_parent('selFull', 'selID'); close_window();" p_strValue="LC__CMDB__BROWSER_OBJECT__BUTTON_SAVE"}]
	 		 [{isys p_bDisabled="0" type="f_button" p_onClick="close_window();" p_strValue="LC__CMDB__BROWSER_OBJECT__BUTTON_CANCEL"}]
		</div>
			
		</form>
	</div>
	
	<script language="JavaScript" type="text/javascript">
		switch_view('treeObject', 'treeLocation', 'treeFilter', g_browser_obj);
		refresh_selected('obj');
		
		[{if $smarty.get.objID}]
			select_object('[{$smarty.get.objID}]', '[{$selFull}]');
		[{/if}]
			
		
		function tree_collapse() {
			tree_active.closeAll();
		}
		

		function tree_expand() {
			tree_active.openAll();
		}
		
	</script>
</body>
</html>