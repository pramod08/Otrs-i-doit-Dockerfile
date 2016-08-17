[{isys_group name="tom"}]
[{isys_group name="details"}]
	<div id="object_details">	
		[{if !$smarty.get.multiSelection}]
			<table cellpadding="3" cellspacing="0" class="details">
				<tr>
					<td>[{isys type="lang" ident="LC__CMDB__CATG__SYSID" p_bInfoIconSpacer="0"}]:</td>
					<td>&nbsp;</td>
					<td><b>[{isys type="f_data" name="C__CATG__SYSID" p_bInfoIconSpacer="0"}]</b></td>
					<td></td>
					<td></td>
					<td>[{isys type="lang" ident="LC__CMDB__CATG__LOCATION"}]: </td>
					<td>&nbsp;</td>
					<td><b>[{isys type="f_data" name="C__CATG__LOCATION" p_bInfoIconSpacer="0"}]</b></td>
		    	</tr>
		    	<tr>
					<td>[{isys type="lang" ident="LC__CMDB__CATG__PURPOSE"}]: </td>
					<td>&nbsp;</td>
					<td><b>[{isys type="f_data" name="C__CATG__PURPOSE" p_bInfoIconSpacer="0"}]</b></td>
					<td></td>
					<td></td>
					<td>[{isys type="lang" ident="LC__CMDB__CATG__CONTACT"}]: </td>
					<td>&nbsp;</td>
					<td><b>[{isys type="f_data" name="C__CATG__CONTACT" p_bInfoIconSpacer="0"}]</b></td>
				</tr>
				<tr>
					<td>[{isys type="lang" ident="LC__CMDB__CATG__CATEGORY"}]: </td>
					<td>&nbsp;</td>
					<td><b>[{isys type="f_data" name="C__CATG__CATEGORY" p_bInfoIconSpacer="0"}]</b></td>
					<td></td>
					<td></td>
					<td>[{isys type="lang" ident="LC__OBJECTDETAIL__ACCESS"}]: </td>
					<td>&nbsp;</td>
					<td><b>[{isys type="f_data" name="C__CATG__ACCESS" p_bInfoIconSpacer="0"}]</b></td>
				</tr>
			</table>
			
			[{if $treeObject}]
			<div class="p10 bold">
				[{isys type="lang" ident="LC__CMDB__BROWSER_OBJECT__PLEASE_CHOOSE"}]
			</div>
			[{/if}]
			
		[{else}]
			<div class="p10 gradient text-shadow">
				<strong>[{isys type="lang" ident="LC__UNIVERSAL__SELECTED"}] [{isys type="lang" ident="LC__CMDB__CATG__OBJECT"}]</strong>: 
				<span id="selected_objects">[{$selFull}]</span>
			</div>
		[{/if}]
		
		<div style="text-align:center;clear: both">
			 <br />
			 [{if $objID}]
			 	[{assign var="buttonDisabled" value="0"}]
			 [{else}]
			 	[{assign var="buttonDisabled" value="1"}]
			 [{/if}]
			 
       		 [{isys
 	 			p_bDisabled="$buttonDisabled"
				p_strAccessKey="s"
 	 			type="f_button"
 				type="f_button"
 				id="BUTTON_SAVE"
 				p_onClick="move_selection_to_parent('selFull', 'selID'); close_window();"
				p_strValue="LC__CMDB__BROWSER_OBJECT__BUTTON_SAVE"}]
	 		 [{isys
	 		 	type="f_button"
	 			type="f_button"
	 			p_bDisabled="0"
	 			p_onClick="close_window();"
	 			p_strValue="LC__CMDB__BROWSER_OBJECT__BUTTON_CANCEL"}]
		</div>
		
		
	</div>
[{/isys_group}]
[{/isys_group}]