[{assign var="resultField" value=$smarty.get.resultField|default:""}]
<body>
	<script language="JavaScript" type="text/javascript">
		
		function select_lun(p_id, p_title) {
			
			aj_submit('[{$statusURL}]&[{$smarty.const.C__CMDB__GET__LDEVSERVER}]='+p_id+'&show=details', 'get', 'object_details');
			
			$('selID').value = p_id;
			$('selFull').value = p_title;
			
			if ($('BUTTON_SAVE')) {
				$('BUTTON_SAVE').disabled = '';
			}
			
			$('object_name').update('(' + p_title + ')');
		}
		
		function move_selection_to_parent(p_eText, p_eHidden)
		{
			var valText		= $(p_eText).value;
			var valHidden	= $(p_eHidden).value;
			 
			var peText		= parent.opener.document.getElementsByName("[{$resultField}]__VIEW")[0];
			var peHidden	= parent.opener.document.getElementsByName("[{$resultField}]__HIDDEN")[0];
			
			if(peText && peHidden) {
				peText.value	= valText;
				peHidden.value	= valHidden;
			}
			
			window.opener.isys_popup_receiver(valHidden,[{$g_form_submit|default:0}]);
			
			[{if $js_callback}]
				if (window.opener.[{$js_callback}]) {
					window.opener.[{$js_callback}];
				}
			[{/if}]
			
		}
		
		var tree_active;
		
		function switch_view(p_toenable, p_todisable, p_todisable_2, p_tree)
		{
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
		
		function close_window()
		{
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
		<h1>[{isys type="lang" ident="LC__CMDB__CATG__LDEV_SERVER" p_bInfoIconSpacer="0"}]-Browser</h1>
		
		<form name="browser" onsubmit="return false;" id="browser">
	    	<input type="hidden" id="selFull" name="selFull" value="[{$selFull}]" />
	    	<input type="hidden" id="selID" name="selID" value="[{$ldevserverID}]" />
			
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
				 [{*
				 <th class="cell" id="treeFilter_BUTTON" onclick="switch_view('treeFilter', 'treeObject', 'treeLocation', g_browser_loc);">
				 	<span>[{isys type="lang" ident="LC__UNIVERSAL__SEARCH"}]</span>
				 </th>
				 *}]
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
				
				[{*
				<div id="treeFilter" style="display: none;">
				 	<div style="padding-left:5px;" class="p10">
						
					 	[{isys type="f_text" id="filter" p_bInfoIconSpacer="0" p_onKeyPress="if (this.value.length>2)reload();" name="filter" p_bEditMode="1"}]
					 	[{isys type="f_button" p_onClick="reload();" p_strValue="Filter" p_bDisabled="0" name="submit" p_bEditMode="1"}]
					 	
					 	<div style="display:none;" class="fr p5" id="loader"><img src="images/ajax-loading.gif" /></div>
					 	<div id="object_filter" style="overflow:auto;" class="tree p10"></div>
						
					</div>
				</div>
				*}]
			</div>
		
		<div class="detailheader">
			<h3 class="m5">[{isys type="lang" ident="LC__POPUP__BROWSER__UI_SELECTED"}] <span id="object_name"></span></h3>
		</div>

			[{include file="popup/object_lun_detail.tpl"}]
			
		</form>
	</div>
	
	<script language="JavaScript" type="text/javascript">
		
		if (!activate_filter) {
			switch_view('treeObject', 'treeLocation', 'treeFilter', g_browser_obj);
		} else {
			switch_view('treeFilter', 'treeObject', 'treeLocation', null);
			$('filter').focus();
		}
		
		[{if $smarty.get.ldevserverID}]
			select_lun('[{$smarty.get.ldevserverID}]', '[{$selFull}]');
		[{/if}]
			
		function tree_collapse()
		{
			tree_active.closeAll();
		}

		function tree_expand()
		{
			tree_active.openAll();
		}
		
	</script>
</body>
</html>