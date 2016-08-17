<div id="widget-config-popup">
    <table class="contentTable">
        <tr>
            <td class="key">[{isys type="f_label" name="widget-popup-config-bookmark-title" ident="LC__WIDGET__BOOKMARKS_CONFIG__TITLE"}]</td>
            <td class="value">[{isys type="f_text" id="widget-popup-config-bookmark-title" p_strClass="input-small"}]</td>
        </tr>
        <tr>
            <td class="key">[{isys type="f_label" name="widget-popup-config-bookmark-link" ident="LC__WIDGET__BOOKMARKS_CONFIG__LINK"}]</td>
            <td class="value">[{isys type="f_text" id="widget-popup-config-bookmark-link" p_strClass="input-small" p_strPlaceholder="http://"}]</td>
        </tr>
	    <tr>
		    <td class="key">[{isys type="f_label" name="widget-popup-config-bookmark-new-window" ident="LC__WIDGET__BOOKMARKS_CONFIG__NEW_WINDOW"}]</td>
		    <td class="value">[{isys type="f_dialog" id="widget-popup-config-bookmark-new-window" p_arData=$dialog_selection p_strClass="input-mini" p_bDbFieldNN=true}]</td>
	    </tr>
        <tr>
            <td></td>
            <td>
                <button type="button" id="widget-config-bookmark-add-button" class="btn ml20">
                    <img src="[{$dir_images}]icons/silk/add.png" alt="+" class="mr5" /><span>[{isys type="lang" ident="LC__UNIVERSAL__BUTTON_ADD"}]</span>
                </button>
                <button type="button" id="widget-config-bookmark-cancel-button" class="btn ml5">
                    <img src="[{$dir_images}]icons/silk/delete.png" class="mr5" /><span>[{isys type="lang" ident="LC__WIDGET__CONFIG__RESET"}]</span>
                </button>
            </td>
        </tr>
    </table>
    <div class="mt5 gradient p5 border">[{isys type="lang" ident="LC__WIDGET__BOOKMARKS"}]</div>
    <ul id="widget-config-list">
	    [{if is_array($bookmark_list)}]
        [{foreach $bookmark_list as $bookmark}]
        <li data-id="[{$bookmark.id}]" data-new-window="[{$bookmark.new_window|intval}]" data-url="[{$bookmark.link}]" data-title="[{$bookmark.title}]">
            <img src="[{$dir_images}]icons/silk/cross.png" class="fr delete mouse-pointer" alt="[{isys type="lang" ident="LC__WIDGET__BOOKMARKS_CONFIG__REMOVE_BOOKMARK"}]" title="[{isys type="lang" ident="LC__WIDGET__BOOKMARKS_CONFIG__REMOVE_BOOKMARK"}]" />
            <img src="[{$dir_images}]icons/silk/pencil.png" class="fr edit mouse-pointer mr5" alt="[{isys type="lang" ident="LC__WIDGET__BOOKMARKS_CONFIG__EDIT_BOOKMARK"}]" title="[{isys type="lang" ident="LC__WIDGET__BOOKMARKS_CONFIG__EDIT_BOOKMARK"}]" />
            <span class="handle">&nbsp;&nbsp;&nbsp;</span>
	        <span class="ml5">[{$bookmark.title}]</span>
	        <em class="ml10 grey">[{$bookmark.link}]</em>
        </li>
        [{/foreach}]
	    [{/if}]
    </ul>

</div>

<script type="text/javascript">
	(function () {
		'use strict';

		var current_edit = null,
			$buttonCancel = $('widget-config-bookmark-cancel-button'),
			$buttonSave = $('widget-config-bookmark-add-button'),
			$bookmark_list = $('widget-config-list'),
			$link = $('widget-popup-config-bookmark-link'),
			$title = $('widget-popup-config-bookmark-title'),
			$new_window = $('widget-popup-config-bookmark-new-window');

		function reset_observer () {
			Sortable.destroy('widget-config-list');

			Sortable.create('widget-config-list', {
				handle: 'handle',
				onChange: window.remember_bookmarks
			});
		}

		$buttonSave.on('click', function () {
			$title.removeClassName('error');
			$link.removeClassName('error');

			if ($title.getValue().blank()) {
				$title.addClassName('error');
				return;
			}

			if ($link.getValue().blank()) {
				$link.addClassName('error');
				return;
			}

			// Try to avoid XSS and such.
			$title.setValue($title.getValue().encodeHTML());
			$link.setValue($link.getValue().encodeHTML());

			if (current_edit === null) {
				$bookmark_list
					.insert(new Element('li', {'data-id':'new_' + new Date().getTime(), 'data-new-window':$new_window.getValue(), 'data-url':$link.getValue(), 'data-title':$title.getValue()})
						.update(new Element('img', {className:'fr delete mouse-pointer', src:'[{$dir_images}]icons/silk/cross.png', alt:'[{isys type="lang" ident="LC__WIDGET__BOOKMARKS_CONFIG__REMOVE_BOOKMARK"}]', title:'[{isys type="lang" ident="LC__WIDGET__BOOKMARKS_CONFIG__REMOVE_BOOKMARK"}]'}))
						.insert(new Element('img', {className:'fr edit mouse-pointer mr5', src:'[{$dir_images}]icons/silk/pencil.png', alt:'[{isys type="lang" ident="LC__WIDGET__BOOKMARKS_CONFIG__EDIT_BOOKMARK"}]', title:'[{isys type="lang" ident="LC__WIDGET__BOOKMARKS_CONFIG__EDIT_BOOKMARK"}]'}))
						.insert(new Element('span', {className:'handle'}).update('&nbsp;&nbsp;&nbsp;'))
						.insert(new Element('span', {className:'ml5'}).update($title.getValue()))
						.insert(new Element('em', {className:'ml10 grey'}).update($link.getValue())));

				reset_observer();
			} else {
				var $li = $bookmark_list.down('li[data-id="' + current_edit + '"]');

				if ($li) {
					$li
						.highlight()
						.writeAttribute('data-url', $link.getValue())
						.writeAttribute('data-title', $title.getValue())
						.writeAttribute('data-new-window', $new_window.getValue())
						.down('span.ml5').update($title.getValue())
						.next('em').update($link.getValue());
				}
			}

			window.remember_bookmarks();
			$buttonCancel.simulate('click');
		});

		$buttonCancel.on('click', function () {
			$link.setValue('').removeClassName('error');
			$title.setValue('').removeClassName('error');
			$new_window.setValue(1);

			$buttonSave
					.down('img').writeAttribute('src', '[{$dir_images}]icons/silk/add.png')
					.next('span').update('[{isys type="lang" ident="LC__UNIVERSAL__BUTTON_ADD"}]');

			$buttonCancel
					.down('img').writeAttribute('src', '[{$dir_images}]icons/silk/delete.png')
					.next('span').update('[{isys type="lang" ident="LC__WIDGET__CONFIG__RESET"}]');

			current_edit = null;
		});

		$bookmark_list.on('click', '.delete', function (ev) {
			ev.findElement('li').remove();

			window.remember_bookmarks();
			current_edit = null;
		});

		$bookmark_list.on('click', '.edit', function (ev) {
			var $li = ev.findElement('li');

			$link.setValue($li.readAttribute('data-url')).highlight();
			$title.setValue($li.readAttribute('data-title')).highlight();
			$new_window.setValue($li.readAttribute('data-new-window')).highlight();
			current_edit = $li.readAttribute('data-id');

			$buttonSave
					.down('img').writeAttribute('src', '[{$dir_images}]icons/silk/disk.png')
					.next('span').update('[{isys type="lang" ident="LC__WIDGET__CONFIG__SAVE"}]');

			$buttonCancel
					.down('img').writeAttribute('src', '[{$dir_images}]icons/silk/cross.png')
					.next('span').update('[{isys type="lang" ident="LC__WIDGET__CONFIG__ABORT"}]');
		});

		window.remember_bookmarks = function () {
			var bookmarks = [];

			$bookmark_list.select('li').each(function ($el) {
				bookmarks.push({
					id:$el.readAttribute('data-id'),
					title:$el.readAttribute('data-title'),
					link:$el.readAttribute('data-url'),
					new_window:($el.readAttribute('data-new-window') == 1)
				});
			});

			$('widget-popup-config-changed').setValue('1');
			$('widget-popup-config-hidden').setValue(Object.toJSON(bookmarks));
		};

		window.remember_bookmarks();
		reset_observer();
	})();
</script>