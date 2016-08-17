<fieldset class="overview">
	<legend>
		<span class="searchOptions" style="border-top:0;">[{$headline|default:"-"}] |
			<label for="normalSearchRadio" class="ml10"><input type="radio" id="normalSearchRadio" name="search-mode" data-mode="[{\idoit\Module\Search\Query\Condition::MODE_DEFAULT}]" [{if $searchMode == \idoit\Module\Search\Query\Condition::MODE_DEFAULT}]checked="checked"[{/if}] /> Normal</label>
			<label for="fuzzySearchRadio" class="ml10"><input type="radio" id="fuzzySearchRadio" name="search-mode" data-mode="[{\idoit\Module\Search\Query\Condition::MODE_FUZZY}]" [{if $searchMode == \idoit\Module\Search\Query\Condition::MODE_FUZZY}]checked="checked"[{/if}] /> Fuzzy search</label>
			<label for="deepSearchRadio" class="ml10"><input type="radio" id="deepSearchRadio" name="search-mode" data-mode="[{\idoit\Module\Search\Query\Condition::MODE_DEEP}]" [{if $searchMode == \idoit\Module\Search\Query\Condition::MODE_DEEP}]checked="checked"[{/if}] /> Deep Search</label>
		</span>
	</legend>

	<div class="mt10 search-results">
		[{if !$error}]
			[{$objectTableList}]
		[{else}]
			<div class="error m10 mt20 p10">[{$error}]</div>
		[{/if}]
	</div>

</fieldset>

<style type="text/css">
	div.fuzzy-search-checkbox
	{
		position:absolute;
		top:10px;
		right:20px;
		margin-top:-25px;
	}
	div.search-results table tr td:first-child {
		width:250px;
	}
	div.search-results table tr td:nth-child(2) {
		white-space: normal;
	}
</style>

<script type="text/javascript">
	(function () {
		"use strict";

		var currentSearch = '[{$smarty.get.q}]';

		$$('.searchOptions label').invoke('on', 'click', function (ev) {
			$$('div.search-results')[0].update('<p class="muted mt20 m10 p10">' + idoit.Translate.get('LC__UNIVERSAL__LOADING') + '</p>').setStyle('z-index:1001');
			show_overlay();

			var searchMode = '';

			$$('span.searchOptions label input').each(function(el) {
				if (el.checked)
				{
					var type = el.getAttribute('data-mode');

					if (type !== "")
					{
						searchMode = '&mode=' + type;
					}
				}
			});

			document.location.href = window.www_dir + 'search?q=' + currentSearch + searchMode;
		});

		progressBarInit();

		if ($('cSpanRecFilter')) $('cSpanRecFilter').hide();
	}());
</script>