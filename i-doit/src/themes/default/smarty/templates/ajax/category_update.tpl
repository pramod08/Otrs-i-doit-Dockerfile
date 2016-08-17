<script type="text/javascript">
	(function () {
		'use strict';

		var $contentbottomcontent = $('contentBottomContent'),
			failedElements = [], i, $el, $img;

		$contentbottomcontent.select('img[data-validation-error]').each(function($el) {
			Tips.remove($el.removeClassName('mouse-pointer').writeAttribute({src: '[{$dir_images}]empty.gif', 'data-validation-error': null}));
		});

		// Remove all "error" fields, before adding new ones. See: ID-1664.
		$contentbottomcontent.select('.error').invoke('removeClassName', 'error');

		[{foreach $validation_errors as $key => $error}]
		failedElements.push({
			id: '[{$key}]',
			message: '[{$error.p_strInfoIconError|escape:"javascript"}]'
		});
		[{/foreach}]

		for (i in failedElements) {
			if (failedElements.hasOwnProperty(i)) {
				$el = $(failedElements[i].id);

				if (!$el) {
					$el = $contentbottomcontent.down('[name="' + failedElements[i].id + '"]');
				}

				if (!$el) {
					$el = $(failedElements[i].id + '__VIEW');
				}

				if ($el) {
					$el.addClassName('error');
					$img = $el.previous('img.infoIcon');

					if (!$img) {
						$img = $el.up('td').down('img.infoIcon');
					}

					if ($img) {
						new Tip($img.addClassName('mouse-pointer').writeAttribute({src: '[{$dir_images}]icons/alert-icon.png', 'data-validation-error': 1}),
							new Element('p', {
								className: 'p5',
								style: 'font-size:12px;'
							}).update(failedElements[i].message),
							{showOn: 'click', hideOn: 'click', effect: 'appear', style: 'darkgrey'});
					}
				}
			}
		}

		// Add category ID to our action parameters.
		[{if $categoryID}]change_action_parameter('cateID', '[{$categoryID}]');[{/if}]

		// Reload tree.
		[{if $smarty.get.objID > 0}]
		get_tree_by_object('[{$smarty.get.objID}]', [{$smarty.const.C__CMDB__VIEW__TREE_OBJECT}],
			[{if isset($smarty.get.catgID)}]'[{$smarty.get.catgID}]'[{else}]null[{/if}],
			[{if isset($smarty.get.catsID)}]'[{$smarty.get.catsID}]'[{else}]null[{/if}]);
		[{/if}]
	})();
</script>