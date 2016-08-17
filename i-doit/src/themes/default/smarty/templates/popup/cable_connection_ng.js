Browser.portList = Class.create(Browser.objectList, {
	createRow:function (obj, index) {
		var values = Object.values(obj), tmpClassName, tmpContent;
		var tr = new Element('tr', {'class':'data line' + (index % 2), id:this.table.id + '-' + index}).writeAttribute('data-objectid', values[0]);

		this.tableColumnsName.each(function (s, index) {
			if (s == '__checkbox__') {

				// Handle preselection
				if (this.options.multiselection) {
					if (this.options.preselection.length > 0) {
						for (var i = 0; i < this.options.preselection.length; i++) {
							if (this.options.preselection[i][0] == values[0]) {
								tmpContent = this.removeButton(values, 'r');
								break;
							} else {
								tmpContent = this.addButton(values, 'r');
							}
						}
					} else {
						tmpContent = this.addButton(values, 'r');
					}
				} else {
					if (this.options.preselection != undefined &&
						this.options.preselection[0] != undefined &&
						this.options.preselection[0] == values[0]) {
						tmpContent = this.removeButton(values, 'r');
					} else {
						tmpContent = this.addButton(values, 'r');
					}
				}

				tmpClassName = this.table.id + '-column-checkbox toolbar center';
			} else {
				tmpContent = values[index];
				tmpClassName = this.table.id + '-column-' + s;
			}

			tr.insert(new Element('td', {
				className:tmpClassName
			}).update(tmpContent));

		}.bind(this));

		if (Prototype.Browser.IE && tr.outerHTML)
        {
            return tr.outerHTML;
        }
		else
        {
            return tr;
        }
	},

	radioButton:function (values, checked) {
        // Is port already connected?
        var l_in_use = [{if $usageWarning}](values[2].length > 1 && !checked)[{else}]false[{/if}];

        return '<input type="radio" id="port-id-' + values[0] + '" name="portSelection" ' + (checked ? 'checked="checked" ' : ' ') + 'onclick="window.portSelection(' + values[0] + ', \'' + values[1] + '\', '+ l_in_use +');" />';
	}
});

// Store the selected objects.
/* Smarty [{if $multiselection}] */
window.secondSelection = {
	objName:[],
	id:[],
	title:[]
};
/* Smarty [{else}] */
window.secondSelection = {
	objName:null,
	id:null,
	title:null
};
/* Smarty [{/if}] */

// Method for setting selected Port.
window.portSelection = function (pId, pName, pInUse) {
	window.browserPreselection.log(('<span class="green">' + idoit.Translate.get('LC__CMDB__OBJECT_BROWSER__SCRIPT__0_FROM_TYPE_1_SELECTED') + '</span>').replace('{0}', pName));

	if (('[{if $multiselection}]true[{else}]false[{/if}]').evalJSON()) {
		secondSelection.id.push(pId);
		secondSelection.title.push(pName);

		window.browserPreselection.add([pId, pName, 0, '-']);
	} else {
		secondSelection.id = pId;
		secondSelection.title = pName;
	}

    // Show warning
    if (pInUse && !$('in_use_warning'))
    {
        // Create warning
        $('portList').insert(
            new Element('p',
                { 'class': 'exception p10 m10', 'id': 'in_use_warning', 'style':'text-align: left;'}).
                update("[{isys type='lang' ident=$usageWarning}]")
        );
    }
    else if (!pInUse)
    {
        // Remove warning
        if ($('in_use_warning')) $('in_use_warning').remove();
    }
};

// Method for saving the selected objects to the hidden forms.
window.moveToParent = function (hiddenElement, viewElement) {
	if (window.browserPreselection.options.multiselection) {
		$(hiddenElement).value = window.browserPreselection.getData();

		if ($(viewElement)) {
			$(viewElement).value = '[{isys type="lang" ident="LC__CMDB__OBJECT_BROWSER__SCRIPT__SELECTED_OBJECTS" p_bHtmlEncode=0}]'.replace('{0}', window.browserPreselection.options.preselection.length);
		}
	} else {
		if (secondSelection.title != null) {
			if ($(viewElement) != null) {
				$(viewElement).value = secondSelection.objName + ' > ' + secondSelection.title;
			}

            if ($('[{$return_cable_name}]'))
            {
                $('[{$return_cable_name}]').value = secondSelection.title;
            }
            else if ($('[{$returnElement2}]'))
            {
                $('[{$returnElement2}]').value = secondSelection.title;
            }
        }

        if (browserPreselection.options.preselection.length == 0) {
            if ($(viewElement)) {
                $(viewElement).value = '[{isys type="lang" ident="LC__UNIVERSAL__CONNECTION_DETACHED" p_bHtmlEncode=0}]';
            }
            if ($(hiddenElement)) {
                $(hiddenElement).value = '';
            }
        }

		if ($(hiddenElement) && secondSelection.id != null) {
			$(hiddenElement).value = secondSelection.id;
		}
	}

	popup_close()
};

// Initialize preselection component.
window.browserPreselection = new Browser.preselection('objectPreselection', {
	secondElement:'portList',
	ajaxURL:'[{$ajax_url}]',
	objectCountElement:'numObjects',
	logElement:'logWindow',
	multiselection:('[{if $multiselection}]true[{else}]false[{/if}]').evalJSON(),
	latestLogElement: 'latestLog',
	instanceName:'browserPreselection',
	afterFinish:function () {
		$('preselectionLoader').hide();
		$('browser-content').show();
	},
	secondList: new Browser.portList('portList', {
		listOptions:{
			colgroup:'<colgroup><col width="80" /><col /><col width="130" /></colgroup>',
			search:false,
			filter:false,
			preselection:('[{$second_preselection|default:"[]"}]').evalJSON(),
			objectSelectionCallback: 'window.portSelection',
			firstSelection:false,
			secondSelection:true,
			secondSelectionExists:true,
			multiselection:('[{if $multiselection}]true[{else}]false[{/if}]').evalJSON()
		}
	})
});

if (window.browserPreselection) {
	window.browserPreselection.secondSelectionCall(('[{$preselection|default:"[]"}]').evalJSON());
}

if (window.browserPreselection.options.multiselection) {
    window.browserPreselection.options.preselection = [];
	if (window.browserPreselection.secondList.options.preselection.length > 0) {
		for (var cnt = 0; cnt < window.browserPreselection.secondList.options.preselection.length; cnt++) {
			window.browserPreselection.add(window.browserPreselection.secondList.options.preselection[cnt], true);
		}
	}
}

// Pre-load the current list view.
if ($('object_type')) {

    var objTypePreselect = [{$category_preselection|default:'-1'}];

    for (var count = 0; count < $('object_type').length; count++)
    {
        if ($('object_type')[count].value == objTypePreselect)
        {
            $('object_type').selectedIndex = count;
        }
    }
    $('object_type').simulate('change');
}
else if ($('object_catfilter'))
{
    $('object_catfilter').selectedIndex = 1;
    $('object_catfilter').onchange();
}