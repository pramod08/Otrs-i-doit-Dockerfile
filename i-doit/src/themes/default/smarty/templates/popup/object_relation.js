/**
 * Smarty variables are used like this, it helps the IDE to not display everything as error.
 * The performance will suffer - But that will not be noticable.
 *
 * ('[{$smarty_var}]').evalJSON();
 *
 * @author  Leonard Fischer <lfischer@i-doit.org>
 */

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

		if (Prototype.Browser.IE && tr.outerHTML) return tr.outerHTML;
		else return tr;
	},

	addButton:function (values, view) {
        var obj_title = (values[1]).replace(/"/g, '&quot;').replace(/'/g, "\\'");

		if (this.options.multiselection) {
			var func_string = "browserPreselection.add([" + values[0] + ", '" + obj_title + "', '" + values[2] + "', '" + values[3] + "'], true);" +
				"if(this.up()){this.up().update(window.browserList.removeButton([" + values[0] + ", '" + obj_title + "', '" + values[2] + "', '" + values[3] + "'], '" + view + "'));}";
			return '<a onclick="' + func_string + '"><img src="images/icons/plus-green.gif" height="10" class="vam" /> ' + idoit.Translate.get('LC__CMDB__OBJECT_BROWSER__SCRIPT__ADD') + '</a>';
		} else {
			return '<input type="radio" name="listSelection" onclick="browserPreselection.add([\'' + values[0] + '\', \'' + obj_title + '\', \'' + values[2] + '\', \'' + values[3] + '\'], true);" />';
		}
	},

	removeButton:function (values, view) {
        var obj_title = (values[1]).replace(/"/g, '&quot;').replace(/'/g, "\\'");

        if (this.options.multiselection) {
			var func_string = "browserPreselection.remove(" + values[0] + ");" +
				"if(this.up()){this.up().update(window.browserList.addButton([" + values[0] + ", '" + obj_title + "', '" + values[2] + "', '" + values[3] + "'], '" + view + "'));}";
			return '<a onclick="' + func_string + '"><img src="' + window.dir_images + 'icons/delete-2.gif" height="10" class="vam" /> ' + idoit.Translate.get('LC__CMDB__OBJECT_BROWSER__SCRIPT__REMOVE') + '</a>';
		} else {
			return '<input type="radio" name="listSelection" checked="checked" onclick="browserPreselection.add([\'' + values[0] + '\', \'' + obj_title + '\', \'' + values[2] + '\', \'' + values[3] + '\'], true);" />';
		}
	}
});

// This is not used here, but has to stay untouched because of the core-logic!
/* Smarty here [{if $multiselection}] */
window.secondSelection = {
	objName:[],
	id:[],
	title:[]
};
/* Smarty here [{else}] */
window.secondSelection = {
	objName:null,
	id:null,
	title:null
};
/* Smarty here [{/if}] */

// Method for saving the selected objects to the hidden forms.
window.moveToParent = function (hiddenElement, viewElement) {
	if (window.browserPreselection.options.multiselection) {
		$(hiddenElement).value = window.browserPreselection.getData();

		if ($(viewElement)) {
			$(viewElement).value = '[{isys type="lang" ident="LC__CMDB__OBJECT_BROWSER__SCRIPT__SELECTED_OBJECTS" p_bHtmlEncode=0}]'.replace('{0}', window.browserPreselection.options.preselection.length);
		}
	} else {
		var el = window.browserPreselection.options.preselection[0];

		if (el && el.hasOwnProperty(0) && el.hasOwnProperty(1) && el.hasOwnProperty(2)) {
			$(viewElement).value = el[2] + ' > ' + el[1];
			$(hiddenElement).value = el[0];
		}
	}

	popup_close();
};

// Initialize preselection component.
window.browserPreselection = new Browser.preselection('objectPreselection', {
	secondElement:'portList',
	ajaxURL:'[{$ajax_url}]',
	preselection:('[{$preselection|default:"[]"}]').evalJSON(),
	objectCountElement:'numObjects',
	logElement:'logWindow',
	multiselection:('[{$multiselection|default:"false"}]').evalJSON(),
	latestLogElement:'latestLog',
	instanceName:'browserPreselection',
	afterFinish:function () {
		$('preselectionLoader').hide();
		$('browser-content').show();
	},
	secondList:new Browser.portList('portList', {
		listOptions:{
			colgroup:'<colgroup><col width="80" /><col /><col width="130" /></colgroup>',
			search:false,
			filter:false,
			preselection:('[{$preselection|default:"[]"}]').evalJSON(),
			objectSelectionCallback:'browserPreselection.add',
			firstSelection:false,
			secondSelection:true,
			secondSelectionExists:true,
			multiselection:('[{$multiselection|default:"false"}]').evalJSON()
		}
	})
});

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
	for (var count = 0; count < $('object_type').length; count++) {
		if ($('object_type')[count].value == [{$category_preselection|default:'-1'}]) {
			$('object_type').selectedIndex = count;
		}
	}

	$('object_type').simulate('change');
} else if ($('object_catfilter')) {
	$('object_catfilter').selectedIndex = 1;
	$('object_catfilter').simulate('change');
}

window.is_relation = function () {
	return ('groupRelationTypes' == $$('div#leftPane ul li.selected')[0].readAttribute('group'));
};

Browser.relationList = Class.create(Browser.objectList, {
	createFirstRow:function (obj) {
		var i_cnt = 1;
		var row = '<thead><tr>';

		this.tableColumnsName.each(function (i) {
			var style = '';

			// Set the first element to a specific width, to avoid a ugly bug in webkit-browsers.
			if (i_cnt == 1) style = ' style="width:80px;" ';
			if (i == '__checkbox__') i = '';
			row += '<th id="' + this.table.id + '-' + i + '"' + style + '>' + i + '</th>';
			i_cnt++;
		}.bind(this));

		if (this.options.firstSelection) {
			row += '<th>' + idoit.Translate.get('LC__CMDB__CATG__RELATION') + '</th>';
		}

		row += '</tr></thead>';
		return row;
	},

	addButton:function (values, view) {
        var obj_title = (values[1]).replace(/"/g, '&quot;').replace(/'/g, "\\'");

		if (this.options.multiselection) {
			var func_string = "browserPreselection.add([" + values[0] + ", '" + obj_title + "', '" + values[2] + "', '" + values[3] + "'], true);" +
				"if(this.up()){this.up().update(window.browserList.removeButton([" + values[0] + ", '" + obj_title + "', '" + values[2] + "', '" + values[3] + "'], '" + view + "'));}";
			return '<a onclick="' + func_string + '"><img src="images/icons/plus-green.gif" height="10" class="vam" /> ' + idoit.Translate.get('LC__CMDB__OBJECT_BROWSER__SCRIPT__ADD') + '</a>';
		} else {
			return '<input type="radio" name="listSelection" onclick="browserPreselection.add([\'' + values[0] + '\', \'' + obj_title + '\', \'' + values[2] + '\', \'' + values[3] + '\'], true);" />';
		}
	},

	removeButton:function (values, view) {
        var obj_title = (values[1]).replace(/"/g, '&quot;').replace(/'/g, "\\'");

		if (this.options.multiselection) {
			var func_string = "browserPreselection.remove(" + values[0] + ");" +
				"if(this.up()){this.up().update(window.browserList.addButton([" + values[0] + ", '" + obj_title + "', '" + values[2] + "', '" + values[3] + "'], '" + view + "'));}";
			return '<a onclick="' + func_string + '"><img src="' + window.dir_images + 'icons/delete-2.gif" height="10" class="vam" /> ' + idoit.Translate.get('LC__CMDB__OBJECT_BROWSER__SCRIPT__REMOVE') + '</a>';
		} else {
			return '<input type="radio" name="listSelection" checked="checked" onclick="browserPreselection.add([\'' + values[0] + '\', \'' + obj_title + '\', \'' + values[2] + '\', \'' + values[3] + '\'], true);" />';
		}
	},

	createRow:function (obj, index) {
		var values = Object.values(obj),
			tmpClassName,
			tmpContent,
			tr = new Element('tr', {'class':'data line' + (index % 2), id:this.table.id + '-' + index}).writeAttribute('data-objectid', values[0]),
			is_relation = window.is_relation();

		this.tableColumnsName.each(function (s, index) {

			if (s == '__checkbox__') {

				// We check if we are only allow to display buttons for relations.
				if (!is_relation && ('[{$relation_only}]').evalJSON() && this.options.firstSelection) {
					tr.insert(new Element('td', {
						className:'toolbar center'
					}).update('-'));

					return;
				}

				if (!browserPreselection.exists(values[0])) {
					tmpContent = this.addButton(values, 'l');
				} else {
					tmpContent = this.removeButton(values, 'l');
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

		tr.insert(new Element('td', {
			className:this.table.id + '-column-checkbox toolbar center'
		}).update('<a onclick="browserPreselection.secondSelectionCall([\'' + values[0] + '\', \'' + values[1] + '\', \'' + values[2] + '\', \'' + values[3] + '\']);">&raquo;</a>'));

		if (Prototype.Browser.IE && tr.outerHTML) return tr.outerHTML;
		else return tr;
	}
});

// We have to override the window.browserlist, which has already been instanced in object_ng.tpl.
window.browserList = new Browser.relationList('objectList', {
	jsonClient:idoitJSON,
	listOptions:{
		colgroup:'<colgroup><col style="width:80px;" /><col /><col style="width:130px;" /><col style="width:130px;" /></colgroup>',
		multiselection:('[{if $multiselection}]true[{else}]false[{/if}]').evalJSON(),
		firstSelection:true,
		secondSelection:false,
		secondSelectionExists:('[{$secondSelection|default:"false"}]').evalJSON(),
		objectSelectionCallback:'[{if $secondSelection}]browserPreselection.secondSelectionCall[{else}]browserPreselection.add[{/if}]',
		instanceName:'browserList'
	}
});