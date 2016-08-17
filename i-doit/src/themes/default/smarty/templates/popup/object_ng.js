(function () {
	"use strict";

	/* Initialize preselection component */
	window.browserPreselection = new Browser.preselection('objectPreselection', {
		preselection: ('[{$preselection|default:"[]"}]').evalJSON(),
		objectCountElement: 'numObjects',
		logElement: 'logWindow',
		secondElement: false,
		multiselection: ('[{if $multiselection}]true[{else}]false[{/if}]').evalJSON(),
		latestLogElement: 'latestLog',
		instanceName: 'browserPreselection',
		afterFinish: function () {
			$('preselectionLoader').hide();
			$('browser-content').show();
		}
	});

	// Moves object browser data to a parent field.
	window.moveToParent = function (hiddenElement, viewElement) {
        var $view = $(viewElement),
            $hidden = $(hiddenElement);

		if (window.browserPreselection.options.multiselection) {
            $hidden.setValue(window.browserPreselection.getData());

			if ($view) {
				$view.setValue('[{isys type="lang" ident="LC__CMDB__OBJECT_BROWSER__SCRIPT__SELECTED_OBJECTS" p_bHtmlEncode=0}]'.replace('{0}', window.browserPreselection.options.preselection.length));
			}
		} else {
			if (window.browserPreselection.options.preselection.length > 0) {
				if ($view && window.browserPreselection.options.preselection[0]) {
					$view.setValue(window.browserPreselection.options.preselection[0][2] + ' >> ' + window.browserPreselection.options.preselection[0][1]);

                    $view.setAttribute('data-last-value', $view.getValue());
				}

				if ($hidden && window.browserPreselection.options.preselection[0]) {
                    $hidden.setValue(window.browserPreselection.options.preselection[0][0]);
				}
			} else {
				if ($view) {
					$view.setValue('[{isys type="lang" ident="LC__UNIVERSAL__CONNECTION_DETACHED" p_bHtmlEncode=0}]');

                    $view.setAttribute('data-last-value', $view.getValue());
				}

				if ($hidden) {
                    $hidden.setValue('');
				}
			}
		}
	};

	// Initialization...

	// Activate fade message for all browser except IE.
	if (!Prototype.Browser.IE) {
		window.messageTimeout = window.setTimeout(function () {
			$$('.browserContent .fadeMessage').invoke('fade');
		}, 1600);
	}

	// Pre-load the current list view.
	if ($('object_type')) {
		$('object_type').selectedIndex = 1;
		$('object_type').simulate('change');
	} else if ($('object_catfilter')) {
		$('object_catfilter').selectedIndex = 1;
		$('object_catfilter').simulate('change');
	}
}());