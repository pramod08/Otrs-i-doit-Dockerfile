<input type="hidden" name="request" id="request" value="saveList" />
<input type="hidden" name="category" id="category" value="" />
<input type="hidden" name="object_ids" id="object_ids" value="" />
<input type="hidden" name="changes_in_entry" id="changes_in_entry" value="" />
<input type="hidden" name="changes_in_object" id="changes_in_object" value="" />

<div id="C__MODULE__MULTIEDIT">
	<h3 class="gradient border-bottom p10 text-shadow">[{isys type="lang" ident="LC__MULTIEDIT__MULTIEDIT"}]</h3>

		<table width="100%" class="contentTable">
			<tr>
				<td class="key">[{isys type="lang" ident="LC__MULTIEDIT__SELECT_OBJECTS_TO_EDIT"}]</td>
				<td class="value">
					[{isys
						name="C__MULTIEDIT__OBJECTS"
						type="f_popup"
						p_strPopupType="browser_object_ng"
                        p_bEnableMetaMap=0
						callback_accept="\$('listLoadButton').show();"}]
				</td>
			</tr>
			<tr>
				<td class="key">[{isys type="lang" ident="LC__MULTIEDIT__SELECT_CATEGORY_TO_EDIT"}]</td>
				<td class="value">[{isys name="C__MULTIEDIT__CATEGORY" p_bEnableMetaMap=0 p_onChange="\$('listLoadButton').show();" chosen=1 type="f_dialog"}]</td>
			</tr>
			<tr>
				<td class="key">[{isys type="f_label" ident="LC_UNIVERSAL__FILTERS" name="C__MULTIEDIT__FILTER"}]</td>
				<td class="value" style="vertical-align: top;">[{isys name="C__MULTIEDIT__FILTER" type="f_text"}]</td>
			</tr>
			<tr id="listLoadButton" [{if !$smarty.get.catgID}]style="display:none;"[{/if}]>
				<td></td>
				<td class="pl20 p10 value">
					<div id="editButtons">
						<button type="button" id="startEditing" class="btn">
							<img src="[{$dir_images}]icons/silk/table_refresh.png" class="mr5" />
							<span>[{isys type="lang" ident="LC__MULTIEDIT__BEGIN_MULTIEDIT"}]</span>
						</button>

						<button type="button" id="addValues" class="btn" style="display:none;">
							<img src="[{$dir_images}]icons/silk/table_row_insert.png" class="mr5" />
							<span>[{isys type="lang" ident="LC__MULTIEDIT__ADD_NEW_ENTRY"}]</span>
						</button>

						<img src="[{$dir_images}]ajax-loading-big.gif" height="18" style="display:none;" class="vam ml20" id="loadListLoader" />
					</div>
				</td>
			</tr>
		</table>

	<div id="multiedit_list" class="border-top border-ccc mt10" style="overflow:auto; max-height:470px;min-height:470px;"></div>

	<p class="note m10 p5" id="changesNote">[{isys type="lang" ident="LC__MULTIEDIT__REGISTERED_CHANGES"}]: <span id="registeredChanges">0</span></p>

	<div class="m10" id="saveButton" style="display:none;">
		<img src="[{$dir_images}]ajax-loading-big.gif" height="18" style="display:none;" class="vam" id="multiEditLoader" />
	</div>

	<div class="m10" id="saveReturn"></div>

	[{$message}]

	<hr class="mt5 mb5" />

	<div class="m5">
		<p><img src="[{$dir_images}]icons/silk/information.png" class="vam"> [{isys type="lang" ident="LC__MULTIEDIT__PLACEHOLDER_INFO"}]</p>

		<ul>
			<li>[{isys type="lang" ident="LC__MULTIEDIT__PLACEHOLDER_INFO_EXAMPLE_1" p_bHtmlEncode=false}]</li>
			<li>[{isys type="lang" ident="LC__MULTIEDIT__PLACEHOLDER_INFO_EXAMPLE_2" p_bHtmlEncode=false}]</li>
			<li>[{isys type="lang" ident="LC__MULTIEDIT__PLACEHOLDER_INFO_EXAMPLE_3" p_bHtmlEncode=false}]</li>
		</ul>
	</div>
</div>

<script type="text/javascript">
	"use strict";

	$('changesNote').setOpacity(0.5);

	var $multivalue_categories = [{$multivalue_categories}];

	/**
	 * @todo move Tablesort extension to general javascript code
	 */
	(function () {
	    function Tablesort(el, options) {
	        if (el.tagName !== 'TABLE') {
	            throw new Error('Element must be a table');
	        }

	        this.init(el, options || {});
	    }

	    Tablesort.prototype = {

	        init: function(el, options) {
	            var that = this,
	                firstRow;
	            this.thead = false;
	            this.options = options;

	            if (el.rows && el.rows.length > 0) {
	                if (el.tHead && el.tHead.rows.length > 0) {
	                    firstRow = el.tHead.rows[el.tHead.rows.length - 1];
	                    that.thead = true;
	                } else {
	                    firstRow = el.rows[0];
	                }
	            }

	            if (!firstRow) {
	                return;
	            }

	            var onClick = function () {
	                if (that.current && that.current !== this) {
	                    if (that.current.classList.contains(classSortUp)) {
	                        that.current.classList.remove(classSortUp);
	                    }
	                    else if (that.current.classList.contains(classSortDown)) {
	                        that.current.classList.remove(classSortDown);
	                    }
	                }

	                that.current = this;
	                that.sortTable(this);
	            };

	            var defaultSort;

	            // Assume first row is the header and attach a click handler to each.
	            for (var i = 0; i < firstRow.cells.length; i++) {
	                var cell = firstRow.cells[i];
	                if (!cell.classList.contains('no-sort')) {
	                    cell.classList.add('sort-header');
	                    cell.addEventListener('click', onClick, false);

	                    if (cell.classList.contains('sort-default')) {
	                        defaultSort = cell;
	                    }
	                }
	            }

	            if (defaultSort) {
	                that.current = defaultSort;
	                that.sortTable(defaultSort, true);
	            }
	        },

	        getFirstDataRowIndex: function() {
	            // If table does not have a <thead>, assume that first row is
	            // a header and skip it.
	            if (!this.thead) {
	                return 1;
	            } else {
	                return 0;
	            }
	        },

	        sortTable: function(header, update) {
	            var that = this,
	                column = header.cellIndex,
	                sortFunction,
	                t = getParent(header, 'table'),
	                item = '',
	                i = that.getFirstDataRowIndex();

	            if (t.rows.length <= 1) return;

	            while (item === '' && i < t.tBodies[0].rows.length) {
	                item = getInnerText(t.tBodies[0].rows[i].cells[column]);
	                item = item.trim();
	                // Exclude cell values where commented out HTML exists
	                if (item.substr(0, 4) === '<!--' || item.length === 0) {
	                    item = '';
	                }
	                i++;
	            }

	            if (item === '') return;

	            // Possible sortFunction scenarios
	            var sortCaseInsensitive = function (a, b) {
	                var aa = getInnerText(a.cells[that.col]).toLowerCase(),
	                    bb = getInnerText(b.cells[that.col]).toLowerCase();

	                if (aa === bb) return 0;
	                if (aa < bb) return 1;

	                return -1;
	            };

	            var sortNumber = function (a, b) {
	                var aa = getInnerText(a.cells[that.col]),
	                    bb = getInnerText(b.cells[that.col]);

	                aa = cleanNumber(aa);
	                bb = cleanNumber(bb);
	                return compareNumber(bb, aa);
	            };

	            var sortDate = function(a, b) {
	                var aa = getInnerText(a.cells[that.col]).toLowerCase(),
	                    bb = getInnerText(b.cells[that.col]).toLowerCase();
	                return parseDate(bb) - parseDate(aa);
	            };

	            // Sort as number if a currency key exists or number
	            if (item.match(/^-?[£\x24Û¢´€]?\d+\s*([,\.]\d{0,2})/) || // prefixed currency
	                item.match(/^-?\d+\s*([,\.]\d{0,2})?[£\x24Û¢´€]/) || // suffixed currency
	                item.match(/^-?(\d)+-?([,\.]){0,1}-?(\d)+([E,e][\-+][\d]+)?%?$/) // number
	               ) {
	                sortFunction = sortNumber;
	            } else if (testDate(item)) {
	                sortFunction = sortDate;
	            } else {
	                sortFunction = sortCaseInsensitive;
	            }

	            this.col = column;
	            var newRows = [],
	                noSorts = {},
	                j,
	                totalRows = 0;

	            for (i = 0; i < t.tBodies.length; i++) {
	                for (j = 0; j < t.tBodies[i].rows.length; j++) {
	                    var tr = t.tBodies[i].rows[j];
	                    if (tr.classList.contains('no-sort')) {
	                        // keep no-sorts in separate list to be able to insert
	                        // them back at their original position later
	                        noSorts[totalRows] = tr;
	                    } else {
	                        // Save the index for stable sorting
	                        newRows.push({
	                            tr: tr,
	                            index: totalRows
	                        });
	                    }
	                    totalRows++;
	                }
	            }

	            var sortUp   = that.options.descending ? classSortDown : classSortUp,
	                sortDown = that.options.descending ? classSortUp : classSortDown;

	            if (!update) {
	                if (header.classList.contains(sortUp)) {
	                    header.classList.remove(sortUp);
	                    header.classList.add(sortDown);
	                } else {
	                    header.classList.remove(sortDown);
	                    header.classList.add(sortUp);
	                }
	            } else if (!header.classList.contains(sortUp) && !header.classList.contains(sortDown)) {
	                header.classList.add(sortUp);
	            }

	            // Make a stable sort function
	            var stabilize = function (sort) {
	                return function (a, b) {
	                    var unstableResult = sort(a.tr, b.tr);
	                    if (unstableResult === 0) {
	                        return a.index - b.index;
	                    }
	                    return unstableResult;
	                };
	            };

	            // Make an `anti-stable` sort function. If two elements are equal
	            // under the original sort function, then there relative order is
	            // reversed.
	            var antiStabilize = function (sort) {
	                return function (a, b) {
	                    var unstableResult = sort(a.tr, b.tr);
	                    if (unstableResult === 0) {
	                        return b.index - a.index;
	                    }
	                    return unstableResult;
	                };
	            };

	            // Before we append should we reverse the new array or not?
	            // If we reverse, the sort needs to be `anti-stable` so that
	            // the double negatives cancel out
	            if (header.classList.contains(classSortDown)) {
	                newRows.sort(antiStabilize(sortFunction));
	                newRows.reverse();
	            } else {
	                newRows.sort(stabilize(sortFunction));
	            }

	            // append rows that already exist rather than creating new ones
	            var noSortsSoFar = 0;
	            for (i = 0; i < totalRows; i++) {
	                var whatToInsert;
	                if (noSorts[i]) {
	                    // We have a no-sort row for this position, insert it here.
	                    whatToInsert = noSorts[i];
	                    noSortsSoFar++;
	                } else {
	                    whatToInsert = newRows[i - noSortsSoFar].tr;
	                }
	                // appendChild(x) moves x if already present somewhere else in the DOM
	                t.tBodies[0].appendChild(whatToInsert);
	            }
	        },

	        refresh: function() {
	            if (this.current !== undefined) {
	                this.sortTable(this.current, true);
	            }
	        }
	    };

	    var classSortUp   = 'sort-up',
	        classSortDown = 'sort-down';

	    var week       = /(Mon|Tue|Wed|Thu|Fri|Sat|Sun)\.?\,?\s*/i,
	        commonDate = /\d{1,2}[\/\-]\d{1,2}[\/\-]\d{2,4}/,
	        month      = /(Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)/i;

	    var testDate = function(date) {
	            return (
	                date.search(week) !== -1 ||
	                date.search(commonDate) !== -1  ||
	                date.search(month !== -1)
	            ) !== -1 && !isNaN(parseDate(date));
	        },

	        parseDate = function (date) {
	            date = date.replace(/\-/g, '/');
	            date = date.replace(/(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{2})/, '$1/$2/$3'); // format before getTime
	            return new Date(date).getTime();
	        },

	        getParent = function (el, pTagName) {
	            if (el === null) {
	                return null;
	            } else if (el.nodeType === 1 && el.tagName.toLowerCase() === pTagName.toLowerCase()) {
	                return el;
	            } else {
	                return getParent(el.parentNode, pTagName);
	            }
	        },

	        getInnerText = function (el) {
	            var that = this;

	            if (typeof el === 'string' || typeof el === 'undefined') {
	                return el;
	            }

	            var str = el.getAttribute('data-sort') || '';

	            if (str) {
	                return str;
	            }
	            else if (el.textContent) {
	                return el.textContent;
	            }
	            else if (el.innerText) {
	                return el.innerText;
	            }

	            var cs = el.childNodes,
	                l = cs.length;

	            for (var i = 0; i < l; i++) {
	                switch (cs[i].nodeType) {
	                    case 1:
	                        // ELEMENT_NODE
		                    if (that && that.getInnerText) {
			                    str += that.getInnerText(cs[i]);
		                    }
	                    break;
	                    case 3:
	                        // TEXT_NODE
	                        str += cs[i].nodeValue;
	                    break;
	                }
	            }

	            return str;
	        },

	        compareNumber = function (a, b) {
	            var aa = parseFloat(a),
	                bb = parseFloat(b);

	            a = isNaN(aa) ? 0 : aa;
	            b = isNaN(bb) ? 0 : bb;
	            return a - b;
	        },

	        cleanNumber = function (i) {
	            return i.replace(/[^\-?0-9.]/g, '');
	        };

	    if (typeof module !== 'undefined' && module.exports) {
	        module.exports = Tablesort;
	    } else {
	        window.Tablesort = Tablesort;
	    }
	})();

	var Multiedit = {
		changes: 0,
		changedFields: [],
        changedEntry: $H(),
        changedObject: $H(),
		tableSort: null,
		url: '',
		templateCounter: 0,

		initialize: function(url) {
			this.url = url;

			$('startEditing').on('click', function() {
				$('editButtons').select('button').invoke('disable');
				this.loadList('multiedit_list', $F('C__MULTIEDIT__OBJECTS__HIDDEN'), $F('C__MULTIEDIT__FILTER'));
			}.bind(this));

			$('addValues').on('click', function(){
				$('editButtons').select('button').invoke('disable');
				this.addNewValues();
			}.bind(this));
		},

		prepareHiddenFields: function () {
			$('category').setValue($F('C__MULTIEDIT__CATEGORY'));
			$('object_ids').setValue($F('C__MULTIEDIT__OBJECTS__HIDDEN'));
		},
		loadList: function(p_id, p_objects, p_filter) {
			$('saveReturn').update();
            $('changes_in_entry').setValue('');
            $('changes_in_object').setValue('');
            this.changedEntry = $H();
            this.changedObject = $H();

			if ($(p_id)) {
				this.prepareHiddenFields();
				this.changes = 0;
				this.updateChanges();
				//$('listLoadButton').hide();
				$('loadListLoader').show();
				new Ajax.Updater(p_id, this.url, {
					parameters: {
						request: 'loadList',
						object_ids: p_objects,
						category: $F('category'),
						'filter': p_filter
					},
					evalJS: true,
					evalScripts: true,
					method: "post",
					onFailure: function () {
						if ($multivalue_categories[$F('category')]) {
							$('addValues').show();
						} else {
							$('addValues').hide();
						}
					},
					onSuccess: function (r) {
						$('saveButton').appear();
						$('loadListLoader').hide();

						// Delaying this because table is not rendered by the browser, yet..
						delay(function () {
							var $table = $(p_id).down('table');

							if ($table && $table.select('tr').length < 1500)
							{
								if ($table.down('tbody tr')) {
									$table.down('tbody tr').addClassName('no-sort');
								}

								if ($table.down('thead th')) {
									$table.down('thead th').addClassName('sort-default');
								}

								$table.select('input,select').each(function (e) {
									e.up('td').setAttribute('data-sort', e.tagName == 'SELECT' ? ((e.options[e.selectedIndex]) ? e.options[e.selectedIndex].innerHTML : null) : e.getValue());
								});

								$table.select('select.chosen-select').each(function ($e) {
									new Chosen($e, {search_contains: true});
								});

								$table.on('change', 'select,input', function (ev, e) {
									e.up('td').setAttribute('data-sort', e.tagName == 'SELECT' ? ((e.options[e.selectedIndex]) ? e.options[e.selectedIndex].innerHTML : null) : e.getValue());
								});

								this.tableSort = new Tablesort($table, {
									descending: true
								});
							}
						}.bind(this), 750);

						if ($multivalue_categories[$F('category')]) {
							$('addValues').show();
						} else {
							$('addValues').hide();
						}
					}.bind(this),
					onComplete: function () {
						$('editButtons').select('button').invoke('enable');
					}
				});
			}
		},
		save: function() {
			var i, formdata = $('isys_form').serialize(true);

			if (Multiedit.changes > 0) {

				$('multiEditLoader').show();
				this.prepareHiddenFields();

				if ($('changes_in_entry')) {
					var changes_in_entry = eval($F('changes_in_entry'));

					if (changes_in_entry) {
						$('multiedit_list').down('tbody').select('tr').each(function ($tr) {
							var entry_id = $tr.getAttribute('data-category-id');

							if (entry_id != 'skip' && entry_id != 'new' && !entry_id.blank() && entry_id.indexOf('new') < 0) {
								if (changes_in_entry.indexOf(parseInt(entry_id)) == -1) {
									$tr.remove();
								}
							}
						});
					}
				}

				$('multiedit_list')
					.setOpacity(0.6)
					.select('.error').invoke('removeClassName', 'error').invoke('writeAttribute', 'title', null);

				// Nested arrays can not be handled as ajax parameters, so we need to prevent this.
				for (i in formdata) {
					if (! formdata.hasOwnProperty(i)) continue;

					if (Object.isArray(formdata[i])) {
						formdata[i] = Object.toJSON(formdata[i]);
					}
				}

				new Ajax.Request(document.location.href, {
					parameters: formdata,
					onSuccess: function() {
						$('multiedit_list').setOpacity(1);
						$('multiEditLoader').hide();
					},
                    onComplete: function(transport) {
	                    var json = transport.responseJSON, i, $el;

	                    if (json === null) {
	                        this.loadList('multiedit_list', $F('object_ids'), $F('C__MULTIEDIT__FILTER'));
	                        idoit.Notify.success('[{isys type="lang" ident="LC__MULTIEDIT__SUCCESSFUL"}]');
	                    } else if (is_json_response(transport)) {

		                    if (json.success) {
			                    this.loadList('multiedit_list', $F('object_ids'), $F('C__MULTIEDIT__FILTER'));
			                    idoit.Notify.success('[{isys type="lang" ident="LC__MULTIEDIT__SUCCESSFUL"}]');
		                    } else {

								if(json.data[0])
								{
									idoit.Notify.error(json.data[0].message, {sticky:true});
								}
								else
								{
									idoit.Notify.error(json.message, {sticky:true});
								}

			                    for (i in json.data) {
				                    if (json.data.hasOwnProperty(i)) {
					                    $el = $(json.data[i].prop_ui_id + '[' + json.data[i].cat_entry_id + '-' + json.data[i].obj_id + ']');

					                    if (! $el) {
					                        $el = $(json.data[i].prop_ui_id + '[new-' + json.data[i].obj_id + '-' + json.data[i].obj_id + ']');
					                    }

					                    if ($el) {
							                $el.addClassName('error').writeAttribute('title', json.data[i].message);
					                    }
				                    }
			                    }
		                    }
	                    } else {
		                    idoit.Notify.error('[{isys type="lang" ident="LC__VALIDATION_ERROR"}]', {sticky:true});
	                    }
                    }.bind(this)
                });

			} else {
                idoit.Notify.error('[{isys type="lang" ident="LC__MULTIEDIT__NOT_SAVED"}]');
			}
		},
		overwriteAll: function(element) {
			var hiddenField = false,
				matches,
				className,
				el_hiddenfield_id = '',
				value;

			if (typeof element == 'object') {
				className = element.name.replace('[skip]', '').replace('[]', '');

                if((matches = element.className.match(className+'_[0-9]')) != null) {
                    className = matches[0];     // This is for ipv4 addresses
                }
			} else {
				className = element;
				element = $(element + '__VIEW[skip]') ? $(element + '__VIEW[skip]') : $(element + '[skip]__VIEW');
			}

			if (element) {
				if (! className.blank()) {
					Placeholder.iteration = 0;

					$$('.' + className).each(function (el, i) {
						if (el.id == element.id) return;

						value = element.getValue();

						// Prevent overriding a non-existing '-' entry for this select-box.
						if (value == '-1' && element.tagName === 'SELECT') {
							if (el.options[0].innerHTML.trim() !== '-1') {
								el.selectedIndex = 0;
								return;
							}
						}

						// We use "--i" because the iteration should start with 0. But the first iteration is skipped!
						el.setValue(Placeholder.process_counter_string(value, --i)).highlight({duration: 0.4});

						if ($(className + '__HIDDEN[skip]')) {
							hiddenField = el.getAttribute('data-hidden-field') ? $(el.getAttribute('data-hidden-field')) : false;

							if (hiddenField) {
								hiddenField.setValue($F(className + '__HIDDEN[skip]'));
							}

							el_hiddenfield_id = el.id.replace('__VIEW', '__HIDDEN');

							if ($(el_hiddenfield_id)) {
								$(el_hiddenfield_id).setValue($F(className + '__HIDDEN[skip]'));
							}
						}

						// We need this to trigger the "onchange" events.
						el.simulate('change');
						el.fire('chosen:updated');
					}.bind(this));
				}

				this.updateChanges();
			}
		},
		updateChanges: function() {
			$('registeredChanges').innerHTML = this.changes;

			if (this.changes == 1) {
				$('changesNote').setOpacity(1);
				$('changesNote').highlight();
			}
		},
		changed: function(field) {
			this.changes++;
			this.changedFields.pop(field);
			this.updateChanges();
		},
        changesInEntry: function(p_entry_id) {
            var arr_entries = [];

            if(p_entry_id != null) {
                this.changedEntry.set(p_entry_id, true);
                this.changedEntry.each(function(ele){
                    if(!ele[0].match('new') && !ele[0].match('skip')){
                        arr_entries.push(parseInt(ele[0]));
                    }
                });
                if(arr_entries.length > 0){
                    $('changes_in_entry').setValue(Object.toJSON(arr_entries));
                }
            }
        },
        changesInObject: function(p_object_id) {
            var arr_entries = [];

            if(p_object_id != null) {
                this.changedObject.set(p_object_id, true);
                this.changedObject.each(function(ele){
                    if(!ele[0].match('new')){
                        arr_entries.push(parseInt(ele[0]));
                    }
                });
                if(arr_entries.length > 0){
                    $('changes_in_object').setValue(Object.toJSON(arr_entries));
                }
            }
        },
		addNewValues: function () {
			var tpl_counter = this.templateCounter;

			$('loadListLoader').show();

			new Ajax.Request(this.url, {
				parameters:  {
					request:     'renderTemplateRow',
					object_ids:  $F('object_ids'),
					category:    $F('category'),
					row_counter: tpl_counter
				},
				evalJS:      true,
				evalScripts: true,
				method:      "post",
				onSuccess:   function (transport) {
					$('loadListLoader').hide();

					var $newRow = $('multiedit_list')
							.down('tr[data-category-id="skip"]')
							.insert({after: transport.responseText})
							.next('tr');

					$newRow.select('.chosen-select').each(function ($select) {
						new Chosen($select, {search_contains: true});
					});
				},
				onComplete:  function () {
					$('editButtons').select('button').invoke('enable');
				}
			});

			this.templateCounter = this.templateCounter + 1;
		}
	};

	Multiedit.initialize('?viewMode=[{$smarty.const.C__CMDB__VIEW__MULTIEDIT}]');

	// Start editing after pressing enter key
	$('C__MULTIEDIT__CATEGORY').on('change', function(ev) {
		if ($('startEditing')) $('startEditing').simulate('click');
	});

	// Hook on custom Dialog Plus event to fill all dialog+ DropDown Boxes after adding a new entry
	document.observe('dialog-plus:afterSave', function(ev)
	{
		if (ev.memo.classIterator && ev.memo.selectBox)
		{
			var elements = $$('#multiedit_list .' + ev.memo.classIterator), current_id;
			if (elements.length > 0)
			{
				elements.each(function (ele)
				{
					current_id = ele.getValue();

					if (ev.memo.parent == 0)
					{
						// Update sbox blindly
						ele.update(ev.memo.selectBox.innerHTML);
					}
					else
					{
						// Update if sec is the same only.
						if ($F(ele.getAttribute('data-secidentifier')) == $F(ev.memo.selectBox.getAttribute('data-secidentifier')))
						{
							ele.update(ev.memo.selectBox.innerHTML);
						}
					}

					// Set value to current.
					ele.setValue(current_id);
				});
			}
		}
	});
</script>

<style type="text/css">
	th.sort-header {
		cursor: pointer;
		position: relative;
	}

	th.sort-header::-moz-selection,
	th.sort-header::selection {
		background: transparent;
	}

	table th.sort-header:after {
		content: '';
		position: absolute;
		top: 5px;
		right: 5px;
		margin-top: 7px;
		border-width: 0 4px 4px;
		border-style: solid;
		border-color: #404040 transparent;
		visibility: hidden;
	}

	table th.sort-header:hover:after {
		visibility: visible;
	}

	table th.sort-up:after,
	table th.sort-down:after,
	table th.sort-down:hover:after {
		visibility: visible;
		opacity: 0.4;
	}

	table th.sort-up:after {
		border-bottom: none;
		border-width: 4px 4px 0;
	}
</style>
