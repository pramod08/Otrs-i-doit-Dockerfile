var $mainMenu = $('mainMenu');

new mainMenuObserver();

$mainMenu.select('li a').invoke('on', 'click', function (ev) {
	var $li = ev.findElement('a').up('li');
	move_arrow_to($li);
	$mainMenu.select('li').invoke('removeClassName', 'active');
	$li.addClassName('active');

	if (!$li.hasClassName('extras') && $('module-dropdown').visible()) {
		new Effect.SlideUp('module-dropdown', {duration: 0.1});
	}
});

Event.observe(window, 'load', function () {
	$mainMenu.select('li.active').each(move_arrow_to);

	var extrasMenu = $mainMenu.down('.extras'),
		extrasDropdown = $('module-dropdown');

	if (extrasMenu && extrasDropdown) {
		extrasDropdown.setStyle({
			left: (extrasMenu.offsetLeft + 50) + 'px',
			top: (parseInt($('top').getHeight()) - 4) + 'px'
		});

		extrasDropdown.close_all_childs = function () {
			// Hides all childs
			$$('#module-dropdown ul.moduleChilds').each(function (ele) {
				ele.hide();
				ele.previous().removeClassName('active');
			});
		};

		extrasDropdown.show_childs = function (p_childID) {
			this.close_all_childs();

			var leftPosi = parseInt($(p_childID).previous().getWidth());

			// Position of the Child Tab.
			$(p_childID).setStyle({
				top: $(p_childID).previous().offsetTop + 'px',
				left: leftPosi + 'px'
			});

			// Show childs.
			$(p_childID).previous().addClassName('active');
			$(p_childID).show();
		};

		extrasMenu.on('click', function (e) {
			e.preventDefault();

			if (!extrasDropdown.visible()) {
				if (extrasDropdown.innerHTML.blank()) {
					new Ajax.Updater(
						'module-dropdown',
						'?call=modules&ajax=1',
						{
							method: 'POST',
							evalScripts: true,
							onComplete: function () {
								new Effect.SlideDown('module-dropdown', {duration: 0.2});
							}
						}
					);
				} else {
					new Effect.SlideDown('module-dropdown', {duration: 0.2});

					// Hides all childs.
					extrasDropdown.close_all_childs();
				}
			} else {
				new Effect.SlideUp('module-dropdown', {duration: 0.2});
				extrasDropdown.close_all_childs();
			}
		});
	}

	if (dragBar) {
		var dragBarObj = new dragBar({
			dragContainer: 'draggableBar',
			leftContainer: 'menuTreeOn',
			rightContainer: 'contentArea',
			moveInfoBox: true,
			defaultWidth: '[{$menu_width}]'
		});

		dragBarObj.callback_save = function () {
			new Ajax.Request('?call=menu&ajax=1&func=save_menu_width', {
				parameters: {
					menu_width: $('menuTreeOn').getWidth()
				},
				method: 'post'
			});
		};
	}

	// This "inline" JS can come from anywhere (categories, modules, API, ...)
	[{if is_array($additionalInlineJS)}]
		[{$additionalInlineJS|implode}]
	[{elseif is_string($additionalInlineJS)}]
		[{$additionalInlineJS}]
	[{/if}]
});