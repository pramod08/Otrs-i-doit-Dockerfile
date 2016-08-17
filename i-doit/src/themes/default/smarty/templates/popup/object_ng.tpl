[{if $js_init}]
<script type="text/javascript">
	// Add some translations.
	idoit.Translate.set('C__CMDB__GET__OBJECT', '[{$smarty.const.C__CMDB__GET__OBJECT}]');

	idoit.Translate.set('LC__CMDB__OBJECT_BROWSER__SCRIPT__ADD', '[{isys type="lang" ident="LC__CMDB__OBJECT_BROWSER__SCRIPT__ADD"}]');
	idoit.Translate.set('LC__CMDB__OBJECT_BROWSER__SCRIPT__NO_PRESELECTION', '[{isys type="lang" ident="LC__CMDB__OBJECT_BROWSER__SCRIPT__NO_PRESELECTION"}]');
	idoit.Translate.set('LC__CMDB__OBJECT_BROWSER__SCRIPT__REMOVE', '[{isys type="lang" ident="LC__CMDB__OBJECT_BROWSER__SCRIPT__REMOVE"}]');
	idoit.Translate.set('LC__CMDB__OBJECT_BROWSER__SCRIPT__0_FROM_TYPE_1_HAS_BEEN_ADDED', '[{isys type="lang" ident="LC__CMDB__OBJECT_BROWSER__SCRIPT__0_FROM_TYPE_1_HAS_BEEN_ADDED"}]');
	idoit.Translate.set('LC__CMDB__OBJECT_BROWSER__SCRIPT__0_FROM_TYPE_1_HAS_BEEN_REMOVED', '[{isys type="lang" ident="LC__CMDB__OBJECT_BROWSER__SCRIPT__0_FROM_TYPE_1_HAS_BEEN_REMOVED"}]');
	idoit.Translate.set('LC__CMDB__OBJECT_BROWSER__SCRIPT__0_FROM_TYPE_1_SELECTED', '[{isys type="lang" ident="LC__CMDB__OBJECT_BROWSER__SCRIPT__0_FROM_TYPE_1_SELECTED"}]');
	idoit.Translate.set('LC__UNIVERSAL__ALL', ('[{isys type="lang" ident="LC__UNIVERSAL__ALL"}]'));
	idoit.Translate.set('LC__UNIVERSAL__PAGE', ('[{isys type="lang" ident="LC__UNIVERSAL__PAGE"}]'));
	idoit.Translate.set('LC__UNIVERSAL__OBJECT_TITLE', '[{isys type="lang" ident="LC__UNIVERSAL__OBJECT_TITLE"}]');
	idoit.Translate.set('LC__UNIVERSAL__OBJECT_TYPE', '[{isys type="lang" ident="LC__UNIVERSAL__OBJECT_TYPE"}]');
	idoit.Translate.set('LC__CMDB__CATG__RELATION', ('[{isys type="lang" ident="LC__CMDB__CATG__RELATION"}]').slice(0, 3) + '.');
	idoit.Translate.set('LC__CMDB__OBJECT_BROWSER__ADD_ALL_ON_PAGE', ('[{isys type="lang" ident="LC__CMDB__OBJECT_BROWSER__ADD_ALL_ON_PAGE"}]'));
	idoit.Translate.set('LC__CMDB__OBJECT_BROWSER__ADD_ALL_BY_FILTER', ('[{isys type="lang" ident="LC__CMDB__OBJECT_BROWSER__ADD_ALL_BY_FILTER"}]'));

	idoit.Translate.set('LC__CMDB__OBJECT_BROWSER__SCRIPT_JS__EMPTY_RESULTS', '[{isys type="lang" ident="LC__CMDB__OBJECT_BROWSER__SCRIPT_JS__EMPTY_RESULTS"}]');
	idoit.Translate.set('LC__CMDB__OBJECT_BROWSER__SCRIPT_JS__ERROR_DATA', '[{isys type="lang" ident="LC__CMDB__OBJECT_BROWSER__SCRIPT_JS__ERROR_DATA"}]');
	idoit.Translate.set('LC__CMDB__OBJECT_BROWSER__SCRIPT_JS__ERROR_URL', '[{isys type="lang" ident="LC__CMDB__OBJECT_BROWSER__SCRIPT_JS__ERROR_URL"}]');
	idoit.Translate.set('LC__CMDB__OBJECT_BROWSER__SCRIPT_JS__FILTER_LABEL', '[{isys type="lang" ident="LC__CMDB__OBJECT_BROWSER__SCRIPT_JS__FILTER_LABEL"}]');
	idoit.Translate.set('LC__CMDB__OBJECT_BROWSER__SCRIPT_JS__LOADING', '[{isys type="lang" ident="LC__CMDB__OBJECT_BROWSER__SCRIPT_JS__LOADING"}]');
	idoit.Translate.set('LC__CMDB__OBJECT_BROWSER__SCRIPT_JS__SEARCH_LABEL', '[{isys type="lang" ident="LC__CMDB__OBJECT_BROWSER__SCRIPT_JS__SEARCH_LABEL"}]');
	idoit.Translate.set('LC__CMDB__OBJECT_BROWSER__SCRIPT_JS__PAGINATEN_OF', '[{isys type="lang" ident="LC__CMDB__OBJECT_BROWSER__SCRIPT_JS__PAGINATEN_OF"}]');
	idoit.Translate.set('LC__CMDB__OBJECT_BROWSER__SCRIPT_JS__PAGINATEN_PAGES', '[{isys type="lang" ident="LC__CMDB__OBJECT_BROWSER__SCRIPT_JS__PAGINATEN_PAGES"}]');

	// Initialize list compnents.
	window.browserList = new Browser.objectList('objectList', {
		jsonClient: idoitJSON,
		listOptions: {
		    colgroup: '<colgroup><col style="width:80px;" /><col /><col style="width:130px;" /><col style="width:130px;" /></colgroup>',
			multiselection: [{if $multiselection}]true[{else}]false[{/if}],
			firstSelection: true,
			secondSelection: false,
			secondSelectionExists: [{$secondSelection|default:"false"}],
			objectSelectionCallback: '[{if $secondSelection}]browserPreselection.secondSelectionCall[{else}]browserPreselection.add[{/if}]',
			instanceName: 'browserList',
			useAuth:!!parseInt('[{$useAuth}]}')
		},
		cmdb_filter:'[{$cmdb_filter}]'
	});

	window.browserSearch = new Browser.objectList('objectSearch', {
		jsonClient: idoitJSON,
		listOptions: {
			colgroup: '<colgroup><col style="width:20px" /><col /><col style="width:130px" /><col style="width:130px" /></colgroup>',
			multiselection: [{if $multiselection}]true[{else}]false[{/if}],
			objectSelectionCallback: 'browserPreselection.add',
			firstSelection: true,
			secondSelection: false,
			secondSelectionExists: false,
			instanceName: 'browserSearch',
			useAuth:!!parseInt('[{$useAuth}]}')
		},
		cmdb_filter:'[{$cmdb_filter}]'
	});

	window.browserReport = new Browser.objectList('reportList', {
		jsonClient: idoitJSON,
		listOptions: {
			colgroup: '<colgroup><col style="width:80px;" /><col /><col style="width:130px;" /><col style="width:130px;" /></colgroup>',
			multiselection: [{if $multiselection}]true[{else}]false[{/if}],
			firstSelection: true,
			secondSelection: false,
			secondSelectionExists: false,
			objectSelectionCallback: 'browserPreselection.add',
			instanceName: 'browserReport',
			useAuth:!!parseInt('[{$useAuth}]}')
		},
		cmdb_filter:'[{$cmdb_filter}]'
	});

	[{include file=$js_init}]

	Ajax.Responders.register({
	    onCreate: function (oXHR, oJson) {
	        $('ajaxLoader').show();
	    },
	    onComplete: function (oXHR, oJson) {
	        $('ajaxLoader').hide();
	    }
	});

	window.objectBrowserTabs = new Tabs('browser_tabs', {
		wrapperClass: 'browser-tabs',
		contentClass: 'browser-tab-content',
		tabClass: 'text-shadow'
	});

	/**
	  * Show and close the object creation wizard
	  */
	window.showWindow = function (p_window, p_focus) {
		var right_div = $('rightsError');
		$(p_window).style.left = (($('browser-content').getWidth() / 2) - ($(p_window).getWidth() / 2)) + 'px';

		if (right_div) {
			if (right_div.visible) {
				right_div.hide();
			}
		}
		new Effect.BlindDown(p_window, {
			duration: 0.2,
			afterFinish: function () {
				if (p_focus && $(p_focus) && $(p_focus).focus)
					$(p_focus).focus();
			}
		});
		$('browser-content').setOpacity(0.6);
	};

	window.closeWindow = function (p_window) {
		var right_div = $('rightsError');
		if (right_div) {
			if (right_div.visible) {
				right_div.hide();
			}
		}
		$('browser-content').setOpacity(1);
		new Effect.BlindUp(p_window, {duration:0.2});
	};

	/**
	  * Place the call and really create the object group
	  */
	window.createObjectGroup = function(objectGroupTitle, forceOverwrite) {
		$('ajaxLoader').show();

        // Check if object type group should be created by a report
        if($('filterview').visible())
        {
            if(browserPreselection.options.preselection.length > 0)
            {
                var objects_selected = browserPreselection.getData();
            }
            else
            {
                var objects_selected = [];
                window.browserReport.cache.each(function(ele){
                    objects_selected.push(parseInt(ele['__checkbox__']));
                });
                objects_selected = Object.toJSON(objects_selected);
            }
        }
        else
        {
            var objects_selected = browserPreselection.getData();
        }

		/* lets call the object group creation*/
		idoitJSON.createObjectGroup(objectGroupTitle, objects_selected, forceOverwrite, function(transport) {
			if (transport.responseJSON) {
				if (!transport.responseJSON.exists) {

					closeWindow('new-objectgroup');
					$('ajaxLoader').hide();

					if (transport.responseText) {

						/* Add new group to group selection */
						$('latestLog').update('Group added (ID: '+transport.responseText+').');
						$('object_group').options[$('object_group').options.length] = new Option(objectGroupTitle, transport.responseText, false, true);
						$('object_group').onchange(window.event);

						/* Show groups */
						$('navGroupGroups').click();

						/* Activate tab one */
						window.objectBrowserTabs.activate(0);

					} else {
						$('latestLog').update('Unknown error while adding group.');
					}

				} else {

					if (confirm('Objectgruppe {0} existiert bereits.\nSoll die Gruppe trotzdem erzeugt werden?'.replace('{0}', objectGroupTitle))) {
						idoitJSON.createObjectGroup(objectGroupTitle, browserPreselection.getData(), true, function(transport) {

							closeWindow('new-objectgroup');
							$('ajaxLoader').hide();

						});
					}
				}
			}
		});
	};

	/**
	  * Place the call and really create the object
	  */
	window.createObject = function(objectTitle, objectType) {
		$('ajaxLoader').show();

		// Lets call the object creation.
		idoitJSON.createObject(objectTitle, objectType, function (transport) {
			if (transport.responseText > 0) {
				// Close dialog and hide ajax loader.
				closeWindow('new-object');

				$('ajaxLoader').hide();

				// Show latest objects, where the new one should be on first place.
				$('groupLatest').down('select').setValue('latest-created').simulate('change');
				$('navGroupLatest').click();

				// Navigate to the first page.
				window.browserList.tools.page = 1;
				window.browserList.update();
			} else {
				idoit.Notify.info('Error while creating the object. Please try again. Error: ' + transport.responseText + ', HTTP-Status: ' + transport.statusText, {life:5});
			}
		});
	};

    /**
     * Checks right of the selected objecttype
     * @param p_objtype
     */
    window.checkEditRightByObjType = function (p_objtype) {
	    var rightToCheck = '[{$checkRight}]';

	    idoitJSON.getRightsByObjectTypeId(p_objtype, rightToCheck, function (transport) {
		    var json = transport.responseJSON,
			    $right_div = $('rightsError');

		    if ($right_div.visible()) {
			    $right_div.hide();
		    }

		    if (json.success) {
			    $('btn_createObject')
				    .enable()
				    .writeAttribute('onclick', "createObject($('createObjectTitle').value, $('createObjectType').value);");
		    } else {
			    $('btn_createObject')
				    .disable()
				    .writeAttribute('onclick', "checkEditRightByObjType($('createObjectType').value)");

			    $right_div.down('p').update(json.message);

			    new Effect.Appear($right_div, {duration: 0.5});
		    }
	    });
    };


	/**
	 * Checks right of the selected objecttype, for the "create object group" button.
	 */
	window.checkEditRightByObjTypeForObjGroup = function () {
		var rightToCheck = '[{$checkRight}]';

		idoitJSON.getRightsByObjectTypeId('[{$smarty.const.C__OBJECT_TYPE__GROUP}]', rightToCheck, function (transport) {
			var json = transport.responseJSON,
				right_div = $('rightsError');

			if (right_div.visible) {
				right_div.hide();
			}

			if (json.success) {
				$('btn_createObjectGroup')
					.enable()
					.writeAttribute('onclick', "createObjectGroup($F('createObjectGroupTitle'));");
			} else {
				$('btn_createObjectGroup')
					.disable()
					.writeAttribute('onclick', "checkEditRightByObjTypeForObjGroup();showWindow('new-objectgroup');$('selectedObjectsForGroup').update(browserPreselection.options.preselection.length);");

				right_div.down('p').update(json.message);

				new Effect.Appear(right_div, {
					duration: 0.5
				});
			}
		});
	};


	$('report_dropdown').observe('change', function() {
		idoitJSON.getObjectsByReport(this.value, function(transport) {
			browserReport.initList(transport);
		},
        {
            select:browserReport.defaultSelection
        });

	});

	if (browserPreselection) {
		$('objectPreselection').update(browserPreselection.compute());
		if (browserPreselection.count() > 0) browserPreselection.log('[{isys type="lang" ident="LC__CMDB__OBJECT_BROWSER__SCRIPT__PRESELECTED_OBJECTS"}]'.replace('{0}', browserPreselection.count()));
		browserPreselection.updateSelectionCount();
	}

	if (browserReport) {
		browserReport.initList([]);
	}

    /**
     * Register escape key for closing the popup
     */
	document.on('keydown', function(ev) {
		ev = ev || window.event;
	    if (ev.keyCode == Event.KEY_ESC) {
		    document.stopObserving('keydown');
		    [{if $callback_abort}][{$callback_abort}][{/if}]
	        popup_close();
	    }
		else if (ev.keyCode == Event.KEY_RETURN) {
		    document.stopObserving('keydown');
			moveToParent('[{$return_element}]', '[{$return_view}]');
			[{if $callback_accept}][{$callback_accept}][{/if}]
			[{if $formsubmit}]
			    document.getElementsByName(C__GET__NAVMODE)[0].value = C__NAVMODE__JS_ACTION; $('isys_form').submit(); $('preselectionLoader').show();$('browser-content').hide(); $('bottombar').hide();
			[{else}]
			    popup_close();
		    [{/if}]
	    }
	});

</script>
[{else}]

	[{assign var=error value='js_init not set.'}]

[{/if}]

<div id="popup-object-ng">
	<h3 class="popup-header">
		<img class="fr mouse-pointer popup-closer" alt="x" src="[{$dir_images}]prototip/styles/default/close.png">
		<span>[{$browser_title|default:"Browser"}]</span>
	</h3>

	[{if !$error}]
	<div class="m10" id="preselectionLoader"><img src="images/ajax-loading-big.gif" class="vam" /> [{isys type="lang" ident="LC__CMDB__OBJECT_BROWSER__SCRIPT_JS__LOADING"}]</div>

	<div id="new-object" style="display:none;">
		<h3 class="popup-header">[{isys type="lang" ident="LC__CMDB__OBJECT_BROWSER__CREATE_NEW_OBJECT"}]</h3>

		<table class="m10 padding spacing vat">
			<colgroup>
				<col width="30%"/>
			</colgroup>
			<tr>
				<td>[{isys type="f_label" name="createObjectTitle" ident="LC__UNIVERSAL__OBJECT_TITLE"}]</td>
				<td><input type="text" class="input" id="createObjectTitle" name="createObjectTitle" onkeypress="if(event.keyCode == Event.KEY_RETURN) { event.preventDefault(); return false; }"/></td>
			</tr>
			<tr>
				<td>[{isys type="f_label" ident="LC__UNIVERSAL__OBJECT_TYPE" name="createObjectType"}]</td>
				<td>[{isys type="f_dialog" p_bInfoIconSpacer="0" p_bDbFieldNN=1 status=0 exclude="C__OBJTYPE__CONTAINER;C__OBJTYPE__LOCATION_GENERIC" p_bEditMode=1 p_bEnableMetaMap=0 p_arData=$arAllObjectTypes p_onChange="checkEditRightByObjType(this.value);" id="createObjectType" sort=true name="createObjectType"}]</td>
			</tr>
		</table>

		<div class="popup-footer" style="position: relative">
			<button type="button" id="btn_createObject" class="btn" onclick="createObject($F('createObjectTitle'), $F('createObjectType'));">
				<img src="[{$dir_images}]icons/silk/tick.png" class="mr5"/><span>[{isys type="lang" ident="LC__UNIVERSAL__CREATE_OBJECT"}]</span>
			</button>
			<button type="button" class="btn" onclick="closeWindow('new-object');">
				<img src="[{$dir_images}]icons/silk/cross.png" class="mr5"/><span>[{isys type="lang" ident="LC__UNIVERSAL__BUTTON_CANCEL"}]</span>
			</button>
		</div>
	</div>

	<div id="new-objectgroup" style="display:none;">
		<h3 class="popup-header">[{isys type="lang" ident="LC__CMDB__OBJECT_BROWSER__CREATE_NEW_OBJECT_GROUP_FROM_SELECTION"}]</h3>

		<table class="m10 padding spacing vat">
			<colgroup>
				<col width="50%"/>
			</colgroup>
			<tr>
				<td>[{isys type="lang" ident="LC__CMDB__OBJECT_BROWSER__SELECTED_ELEMENTS"}]</td>
				<td id="selectedObjectsForGroup"></td>
			</tr>
			<tr>
				<td>[{isys type="f_label" ident="LC__CMDB__OBJECT_BROWSER__NAME_OF_THE_GROUP" name="createObjectGroupTitle"}]</td>
				<td><input type="text" class="input input-small" id="createObjectGroupTitle" name="createObjectGroupTitle"/></td>
			</tr>
		</table>

		<div class="popup-footer" style="position: relative">
			<button type="button" id="btn_createObjectGroup" class="btn" onclick="createObjectGroup($F('createObjectGroupTitle'));">
				<img src="[{$dir_images}]icons/silk/tick.png" class="mr5"/><span>[{isys type="lang" ident="LC__UNIVERSAL__CREATE_OBJECT"}]</span>
			</button>
			<button type="button" class="btn" onclick="closeWindow('new-objectgroup');">
				<img src="[{$dir_images}]icons/silk/cross.png" class="mr5"/><span>[{isys type="lang" ident="LC__UNIVERSAL__BUTTON_CANCEL"}]</span>
			</button>
		</div>
	</div>

	<div id="rightsError" class="exception" style="display:none;">
		<h3 class="p5 gradient">[{isys type="lang" ident="LC__AUTH__EXCEPTION"}] <a href="javascript:" onclick="$('rightsError').fade({duration:0.5});" style="color:#a61616;" class="bold fr">&times;</a></h3>
		<p class="p5"></p>
	</div>

	<div class="browser-content" id="browser-content" style="display:none;">

		<ul id="browser_tabs" class="m0 gradient">
			<li><a href="#objectview">[{isys type="lang" ident="LC__CMDB__OBJECT_BROWSER__OBJECT_VIEW"}]</a></li>
			<li[{if $tabs.location.disabled}] class="disabled"[{/if}]><a href="#locationview">[{isys type="lang" ident="LC__CMDB__OBJECT_BROWSER__LOCATION_VIEW"}]</a></li>
			<li[{if $tabs.search.disabled}] class="disabled"[{/if}]><a href="#searchview">[{isys type="lang" ident="LC__UNIVERSAL__SEARCH"}]</a></li>
			<li[{if $tabs.report.disabled}] class="disabled"[{/if}]><a href="#filterview">Reports</a></li>
			<li style=""><a href="#selectionview">[{isys type="lang" ident="LC__CMDB__OBJECT_BROWSER__SELECTED_OBJECTS"}] (<span id="numObjects">0</span>)</a></li>
			<li id="ajaxLoader"><img src="[{$dir_images}]ajax-loading.gif" /></li>
			<li[{if $tabs.log.disabled}] class="disabled"[{/if}] class="logview"><a href="#logview">Log</a></li>
		</ul>

		<div id="objectview">

			<div id="leftPane" class="fl browserContent">

				<img id="collapser" height="20px" width="5px" src="[{$dir_images}]icons/vdrawer_close.png" alt="" title="">

				<h3 class="p10">
					<img src="[{$dir_images}]icons/tree_icon_down.png" class="vam" />
					<img src="[{$dir_images}]icons/directory.png" class="vam" />
					Filter
				</h3>

				<ul>
					[{if !$categoryFilter}]
					[{if is_array($arObjectTypes)}]<li class="selected" group="groupObjectType">[{isys type="lang" ident="LC__CMDB__OBJECT_BROWSER__BY_OBJECT_TYPE"}]</li>[{/if}]
					[{if is_array($arGroups)}]<li group="groupGroups" id="navGroupGroups">[{isys type="lang" ident="LC__CMDB__OBJECT_BROWSER__BY_GROUPS"}]</li>[{/if}]
					[{if is_array($arPersonGroups)}]<li group="groupPersons">[{isys type="lang" ident="LC__CMDB__OBJECT_BROWSER__BY_PERSON_GROUPS"}]</li>[{/if}]
					[{if is_array($arRelationTypes)}]<li group="groupRelationTypes">[{isys type="lang" ident="LC__CMDB__OBJECT_BROWSER__BY_RELATIONS"}]</li>[{/if}]

					<li group="groupLatest" id="navGroupLatest">[{isys type="lang" ident="LC__CMDB__OBJECT_BROWSER__BY_DATE"}]</li>
					[{else}]
					[{if is_array($arCategoryFilter)}]<li class="selected" group="groupCategoryData">[{isys type="lang" ident="LC__CMDB__OBJECT_BROWSER__BY_SPECIFIC"}]</li>[{/if}]
					[{/if}]
				</ul>
			</div>

			<script type="text/javascript">
				$('collapser').observe('click', function(e) {
					var img = Event.findElement(e),
						el = img.up();

					if (el.hasClassName('collapsed')) {
						img.writeAttribute('src', window.dir_images + 'icons/vdrawer_close.png');
						el.removeClassName('collapsed');
						new Effect.Morph(el, {style: 'width:185px', duration: 0.5});
					} else {
						img.writeAttribute('src', window.dir_images + 'icons/vdrawer_open.png');
						el.addClassName('collapsed');
						new Effect.Morph(el, {style: 'width:5px', duration: 0.5});
					}
				});

				$$('#leftPane ul li').each(function(li){
					li.observe('click', function(e) {
						if (!Prototype.Browser.IE)  {
							$$('#' + e.element().readAttribute('group') + ' .fadeMessage').invoke('show');

							window.clearTimeout(window.messageTimeout);
							window.messageTimeout = window.setTimeout(function(){
								$$('.browserContent span.fadeMessage').invoke('fade');
							}, 1400);
						}

						$$('#leftPane ul li.selected').invoke('removeClassName', 'selected');
						$$('.browserContent .groups').invoke('hide');

						var group = e.element().addClassName('selected').readAttribute('group');

						// Prototype only delivers a check for IE6 and IE7, but we need to know about IE8.
						var ie_version = parseInt(navigator.userAgent.substring(navigator.userAgent.indexOf("MSIE")+5));

						if (ie_version != 7 && ie_version != 8)  {
							$(group).show().down('select').onchange(e);
						} else {
							$(group).show().down('select');
						}


					});
				});
			</script>

			<div class="browserContent">

				<div style="border-bottom:1px solid #888888;">

					[{if !$categoryFilter}]
					<div class="fr m5">
						<button type="button" class="btn" onclick="showWindow('new-object', 'createObjectTitle');checkEditRightByObjType($F('createObjectType'));">
							<img src="[{$dir_images}]icons/inc_arr.png" class="mr5" style="width:8px; height:8px; margin-top:-2px;" /><span>[{isys type="lang" ident="LC__CMDB__OBJECT_BROWSER__CREATE_NEW_OBJECT"}]</span>
						</button>
					</div>

					[{if is_array($arObjectTypes)}]
					<div id="groupObjectType" class="groups">
						[{isys type="f_dialog" p_onChange="if (this.selectedIndex > 0) {browserList.searchByType(Object.prototype.hasOwnProperty.call(this.options, this.selectedIndex) ? this.options[this.selectedIndex].value : 0);}" p_bInfoIconSpacer="0" p_bDbFieldNN=0 status=0 exclude="C__OBJTYPE__CONTAINER;C__OBJTYPE__LOCATION_GENERIC" p_bEditMode=1 p_arData=$arObjectTypes id="object_type" p_bEnableMetaMap=0 sort=true name="object_type" p_strClass="input-small"}]
						<span class="fadeMessage text-shadow">&larr;&nbsp;&nbsp;[{isys type="lang" ident="LC__CMDB__OBJECT_BROWSER__CHOOSE_OBJECT_TYPE"}]</span>
					</div>
					[{/if}]

					[{if is_array($arGroups)}]
					<div id="groupGroups" style="display:none;" class="groups">
						[{isys type="f_dialog" p_onChange="browserList.searchByGroup(this.options[this.selectedIndex].value);" p_bInfoIconSpacer="0" p_bDbFieldNN=0 status=0 p_bEditMode=1 p_arData=$arGroups p_bEnableMetaMap=0 id="object_group" sort=true name="object_group" p_strClass="input-small"}]
						<span class="fadeMessage text-shadow">&larr;&nbsp;&nbsp;[{isys type="lang" ident="LC__CMDB__OBJECT_BROWSER__CHOOSE_GROUP"}]</span>
					</div>
					[{/if}]

					[{if is_array($arPersonGroups)}]
					<div id="groupPersons" style="display:none;" class="groups">
						[{isys type="f_dialog" p_onChange="browserList.searchByPersonGroup(this.options[this.selectedIndex].value);" p_bInfoIconSpacer="0" p_bDbFieldNN=0 status=0 p_bEditMode=1 p_arData=$arPersonGroups p_bEnableMetaMap=0 id="person_group" sort=true name="person_group" p_strClass="input-small"}]
						<span class="fadeMessage text-shadow">&larr;&nbsp;&nbsp;[{isys type="lang" ident="LC__CMDB__OBJECT_BROWSER__CHOOSE_PERSON_GROUP"}]</span>
					</div>
					[{/if}]

					[{if is_array($arRelationTypes)}]
					<div id="groupRelationTypes" style="display:none;" class="groups">
						<select name="object_latest" class="input input-small" id="relation_type" onchange="browserList.searchByRelationType(this.options[this.selectedIndex].value);" z-index="1">
							<option value="-1">-</option>
							[{foreach from=$arRelationTypes key=relTypeId item=relType}]
							<option value="[{$relTypeId}]">[{isys type=lang ident=$relType.title}]</option>
							[{/foreach}]
						</select>
						<span class="fadeMessage text-shadow">&larr;&nbsp;&nbsp;[{isys type="lang" ident="LC__CMDB__OBJECT_BROWSER__CHOOSE_RELATION_TYPE"}]</span>
					</div>
					[{/if}]

					<div id="groupLatest" style="display:none;" class="groups">
						<select name="object_latest" class="input input-small" id="object_type" onchange="if (this.selectedIndex != 0) browserList.searchByTimeCondition(this.options[this.selectedIndex].value, null, null, {typeFilter:'[{$typeFilter}]', groupFilter:'[{$groupFilter}]', catFilter:'[{$catFilter}]'});" z-index="1">
							<option value="-1">-</option>
							<option value="latest-created">[{isys type="lang" ident="LC__CMDB__OBJECT_BROWSER__NEWLY_CREATED"}]</option>
							<option value="latest-updated">[{isys type="lang" ident="LC__CMDB__OBJECT_BROWSER__NEWLY_UPDATED"}]</option>
							<option value="this-month">[{isys type="lang" ident="LC__CMDB__OBJECT_BROWSER__CREATED_THIS_MONTH"}]</option>
							<option value="last-month">[{isys type="lang" ident="LC__CMDB__OBJECT_BROWSER__CREATED_LAST_MONTH"}]</option>
						</select>
						<span class="fadeMessage text-shadow">&larr;&nbsp;&nbsp;[{isys type="lang" ident="LC__CMDB__OBJECT_BROWSER__CHOOSE_DATE"}]</span>
					</div>
					[{else}]
					<div id="groupCategoryData">
						<select name="object_catfilter" class="input input-small m10" id="object_catfilter" onchange="if (this.selectedIndex != 0) browserList.searchByCategoryFilter({request: this.options[this.selectedIndex].value, objID: glob(C__CMDB__GET__OBJECT)}, '[{$categoryFilter}]');" z-index="1">
							<option value="-1">-</option>
							[{foreach from=$arCategoryFilter key="request" item="filter"}]
							<option value="[{$request}]">[{isys type=lang ident=$filter}]</option>
							[{/foreach}]
						</select>
						<span class="fadeMessage text-shadow">&larr;&nbsp;&nbsp;[{isys type="lang" ident="LC__CMDB__OBJECT_BROWSER__CHOOSE_OBJECT_FILTER"}]</span>
					</div>
					[{/if}]

				</div>

				<div id="objectList"[{if $secondSelection}] class="left fl"[{/if}]>
					<p class="p5 m10 note"><strong>[{isys type="lang" ident="LC__CMDB__OBJECT_BROWSER__PLEASE_MAKE_A_SELECTION"}]</strong></p>
				</div>

				[{if $secondSelection}]
				<div id="portList" class="right fl">
					<p class="p5 m10 note"><strong>[{isys type="lang" ident="LC__CMDB__OBJECT_BROWSER__PLEASE_MAKE_A_SELECTION"}]</strong></p>
				</div>
				[{/if}]
			</div>

		</div>

		<div id="locationview">
			<div class="browserContent">
				<h2 class="header p10">[{isys type="lang" ident="LC__CMDB__OBJECT_BROWSER__LOCATION_VIEW"}]</h2>

				<div class="p10" id="locationBrowser"></div>
			</div>
			<script type="text/javascript">[{$locationBrowser}]</script>
		</div>

		<div id="searchview">
			<div class="browserContent">
				<h2 class="header p10">
					<input type="search" placeholder="[{isys type='lang' ident='LC__CMDB__OBJECT_BROWSER__ENTER_SEARCH_PHRASE'}]" class="input input-block" id="obj-filter" name="obj-filter" onkeypress="if(event.keyCode == Event.KEY_RETURN) return false;" onkeyup="delay(function(){browserSearch.search(this, '[{$typeFilter}]', '[{$groupFilter}]');}.bind(this), 500);" />
				</h2>

				<div id="objectSearch" class="tree p10"></div>
			</div>

		</div>

		<div id="filterview">
			<div class="browserContent">

				<div class="m10 fr" id="filterReports_btn" style="display:none;">
					<button type="button" class="btn" onclick="checkEditRightByObjTypeForObjGroup($F('createObjectType'));showWindow('new-objectgroup');if(browserPreselection.options.preselection.length > 0)$('selectedObjectsForGroup').update(browserPreselection.options.preselection.length); else $('selectedObjectsForGroup').update(window.browserReport.cache.length);" title="[{isys type='lang' ident='LC__CMDB__OBJECT_BROWSER__CREATE_NEW_OBJECT_GROUP_FROM_REPORT_NOTICE'}]">
						[{isys type="lang" ident="LC__CMDB__OBJECT_BROWSER__CREATE_NEW_OBJECT_GROUP_FROM_REPORT"}]
					</button>
				</div>

				<h2 class="header p10">Reports</h2>

				<div id="filterReports" style="padding:10px 0 11px 10px;border-bottom:1px solid #888888;">

					<label for="report_dropdown">[{isys type="lang" ident="LC__REPORT__BROWSE_REPORTS"}]</label>

					<select id="report_dropdown" name="report_dropdown" onchange="if(this.value == '-')$('filterReports_btn').hide(); else $('filterReports_btn').show();" class="input input-small" z-index="1">
						<option value="-">-</option>
						[{foreach from=$reports key=report_category item=reports_arr}]
						<optgroup label="[{$report_category}]">
							[{foreach from=$reports_arr key=report_id item=report_title}]
							<option value="[{$report_id}]">[{$report_title}]</option>
							[{/foreach}]
						</optgroup>
						[{/foreach}]
					</select>

				</div>
				<div id="reportList"><p class="emptyPageMessage"><img class="" src="[{$dir_images}]outlet.png"> Keine Ergebnisse gefunden</p></div>
			</div>
		</div>

		<div id="selectionview">
			<div class="browserContent">

				[{if !$secondSelection}]
				<div class="m10 fr">
					<button type="button" class="btn" onclick="checkEditRightByObjTypeForObjGroup($F('createObjectType'));showWindow('new-objectgroup');$('selectedObjectsForGroup').update(browserPreselection.options.preselection.length);">
						<img src="[{$dir_images}]icons/inc_arr.png" class="mr5" style="width:8px; height:8px; margin-top:-2px;" /><span>[{isys type="lang" ident="LC__CMDB__OBJECT_BROWSER__CREATE_NEW_OBJECT_GROUP_FROM_SELECTION"}]</span>
					</button>
				</div>
				[{/if}]

				<h2 class="header p10">[{isys type="lang" ident="LC__CMDB__OBJECT_BROWSER__SELECTED_OBJECTS"}]</h2>

				<div id="objectPreselection"></div>
			</div>
		</div>

		<div id="logview">
			<div class="browserContent">
				<pre id="logWindow" class="m10 cb"></pre>
			</div>
		</div>
	</div>

	<div class="popup-footer">
		<p id="latestLog" class="fr"></p>

		<button type="button" class="btn mr5" onclick="moveToParent('[{$return_element}]', '[{$return_view}]');[{if $callback_accept}][{$callback_accept}][{/if}][{if $formsubmit}]document.getElementsByName(C__GET__NAVMODE)[0].value = C__NAVMODE__JS_ACTION; $('isys_form').submit(); $('preselectionLoader').show();if($('browser-content')){$('browser-content').hide();} if($('bottombar')){$('bottombar').hide();}[{else}]popup_close();[{/if}]">
			<img src="[{$dir_images}]icons/silk/tick.png" class="mr5" /><span>[{isys type="lang" ident="LC__CMDB__OBJECT_BROWSER__BUTTON_SAVE"}]</span>
		</button>
		<button type="button" class="btn popup-closer" onclick="[{if $callback_abort}][{$callback_abort}][{/if}]">
			<img src="[{$dir_images}]icons/silk/cross.png" class="mr5" /><span>[{isys type="lang" ident="LC__CMDB__OBJECT_BROWSER__BUTTON_CANCEL"}]</span>
		</button>
	</div>

	[{else}]

	<div>
		<p class="emptyPageMessage" style="margin-top:100px;"><img src="[{$dir_images}]outlet.png" class="" /> [{$error}]</p>

		[{if $errorDetail}]
		<pre class="p10" style="margin-top:100px;height:300px;overflow:auto;border:1px solid #ccc;background-color:#eee;">[{$errorDetail}]</pre>
		[{/if}]
	</div>
	[{/if}]
</div>

<script>
	(function () {
		'use strict';

		var $popup = $('popup-object-ng');

		$popup.select('.popup-closer').invoke('on', 'click', function () {
			popup_close();
		});
	})();
</script>