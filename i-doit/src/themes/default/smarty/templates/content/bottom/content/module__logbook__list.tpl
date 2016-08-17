[{assign var="mod" value=$smarty.const.C__GET__MODULE_ID}]

<div class="pt10">
	[{if $LogbookList}]
		<script type="text/javascript">
	        window.resetFilter = function () {
	            $('filter_from__VIEW').value = null;
	            $('filter_from__HIDDEN').value = null;
	            $('filter_to__VIEW').value = null;
	            $('filter_to__HIDDEN').value = null;
	            $('filter_text').value = null;
	            $('filter_source').selectedIndex = null;
	            $('filter_type').selectedIndex = null;
	            $('filter_alert').selectedIndex = null;
	            $('changes_only').checked = false;
				[{if $archiveBrowser == 1}]$('filter_archive').selectedIndex = 0;[{/if}]
	            $('navPageStart').value = '0';
	        };

			var e = document.getElementsByName('cRecStatus')[0];

			if(e) {
				if(e.options.length == 0) {
					var f = $('cSpanRecFilter');
					if(!f) {
						f.show();
					}
				}
			}

			window.expandAllLogbookChanges = function() {
	            $$('table.mainTable .logexpand').each(function($td) {
		            var id = $td.readAttribute('id').replace('ec', 'tr'),
			            $destination = $(id);

		            if ($destination && ! $destination.visible()) {
		                $td.simulate('click');
		            }
	            });
			};
		</script>

		<style type="text/css">
			.tdNav {
				padding:0 10px;
				vertical-align: top;
			}
		</style>

		<table cellpadding="0" cellspacing="0">
			<tr>
				<td class="tdNav">
					[{if ($archiveBrowser == "1")}]
						Status<br />
						[{isys type="f_dialog" name="filter_archive" p_strClass="input-mini" p_bDbFieldNN="1" p_bEditMode="1" p_bInfoIconSpacer="0"}]
						<br />
					[{/if}]

					[{isys type="f_label" name="C__CMDB__LOGBOOK__SOURCE__USER" ident="LC__CMDB__LOGBOOK__SOURCE__USER"}]<br />
					[{isys
						title="LC__CMDB__LOGBOOK__SOURCE__USER"
						name="filter_user"
						type="f_popup"
						p_strPopupType="browser_object_ng"
						typeFilter="C__OBJTYPE__PERSON"
						edit_mode="1"
						p_strClass="input-mini"
						p_bDisableDetach="1"
						multiselection="1"
						p_bInfoIconSpacer="0"}]
				</td>
				<td class="tdNav">
					[{isys type="f_label" ident="LC_UNIVERSAL__FROM" name="filter_from__VIEW"}]<br />
					[{isys type="f_popup" p_bInfoIconSpacer="0" p_strClass="small" p_bEditMode="1" name="filter_from" p_strPopupType="calendar" p_calSelDate="" p_bTime="1"}]
					<br />
					[{isys type="f_label" ident="LC__UNIVERSAL__TO" name="filter_to__VIEW"}]<br />
					[{isys type="f_popup" p_bInfoIconSpacer="0" p_strClass="small" p_bEditMode="1" name="filter_to" p_strPopupType="calendar" p_calSelDate="" p_bTime="1"}]
				</td>
				<td class="tdNav">
					[{isys type="f_label" ident="LC__CMDB__LOGBOOK__SOURCE" name="filter_source"}]<br />
					[{isys type="f_dialog" name="filter_source" p_strClass="input-mini" p_bEditMode="1" p_bInfoIconSpacer="0"}]
					<br />
					[{isys type="f_label" ident="LC__CMDB__CATG__TYPE" name="filter_type"}]<br />
					[{isys type="f_dialog" name="filter_type" p_strClass="input-mini" p_bEditMode="1" p_bInfoIconSpacer="0"}]
				</td>
				<td class="tdNav">
					[{isys type="f_label" ident="LC__CMDB__LOGBOOK__LEVEL" name="filter_alert"}]<br />
					[{isys type="f_dialog" name="filter_alert" p_strClass="input-mini" p_bEditMode="1" p_bInfoIconSpacer="0"}]
					<br />
					<label style="line-height: 18px;">
	                    [{isys type="lang" ident="LC__LOGBOOK__ONLY_CMDB_CHANGES"}]<br />
						<input type="checkbox" [{if $smarty.post.changes_only eq 1}]checked="checked"[{/if}] name="changes_only" value="1" />
					</label>
				</td>
				[{if $groups == "1"}]
				<td class="tdNav">
					[{isys type="f_label" ident="LC__LOGBOOK_FILTER__GROUP_BY" name="filter_group"}]<br />
					[{isys type="f_dialog" p_onChange="document.isys_form.navPageStart.value='0'; document.isys_form.submit();" name="filter_group" p_strClass="input-mini" p_bEditMode="1" p_bInfoIconSpacer="0"}]
				</td>
				[{/if}]

				<td class="p10 vam">
					<button type="button" class="btn ml10" onclick="$('navPageStart').setValue(0); form_submit(null, null, null, null, '$(\'logbookListContent\').show()');">
						<img src="[{$dir_images}]icons/silk/tick.png" class="mr5" /><span>[{isys type='lang' ident='LC_UNIVERSAL__FILTER'}]</span>
					</button>
				</td>
			</tr>
		</table>

		<div id="logbookListContent" class="border-top p0 mt10">
		[{$LogbookList}]
		</div>
	[{else}]

		<script type="text/javascript">
			function chg_level(sel) {
				var img;

				if (sel.selectedIndex == 1) img = "green";
				else if (sel.selectedIndex == 2) img = "yellow";
				else if (sel.selectedIndex == 3) img = "red";
				else img = "blue";

				$('alert_img').src = 'images/icons/infobox/' + img + '.png';
			}
		</script>

		<table class="contentTable">
			<tr>
				<td class="key">Alert Level</td>
				<td class="value">
					[{isys type="f_dialog" name="C__CATG__LOGBOOK__ALERTLEVEL" p_bDbFieldNN=1 p_onChange="chg_level(this);"}] <img src="images/icons/infobox/blue.png" class="vam" id="alert_img" />
				</td>
			</tr>
			<tr>
				<td class="key">[{isys type="lang" ident="LC__UNIVERSAL__MESSAGE" name="C__CATG__LOGBOOK__MESSAGE"}]</td>
				<td class="value">[{isys type="f_text" name="C__CATG__LOGBOOK__MESSAGE"}]</td>
			</tr>
			<tr>
				<td class="key" valign="top">[{isys type="f_label" ident="LC__LANGUAGEEDIT__TABLEHEADER_DESCRIPTION" name="C__CATG__LOGBOOK__DESCRIPTION"}]</td>
				<td class="value">[{isys type="f_textarea" name="C__CATG__LOGBOOK__DESCRIPTION" p_nCols="55"}]</td>
			</tr>

		</table>

	[{/if}]
</div>