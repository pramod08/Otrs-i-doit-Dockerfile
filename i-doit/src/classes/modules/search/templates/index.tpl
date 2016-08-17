<fieldset class="overview">
	<legend><span style="border-top:0;">[{$headline}]</span></legend>

	<div class="mt10 search-area">
		<div class="inner-search-area">
			</form>
			<form action="[{$config.www_dir}]search" method="get">
				<input type="text" class="search-box inputText" name="q" id="main-search-query" value="" placeholder="" />

				<p class="mt10">
					<button class="search-button button">[{isys type="lang" ident="LC__SEARCH__SEARCH"}]</button>
				</p>
			</form>
			<form action="" method="get">
		</div>
	</div>

</fieldset>

<style type="text/css">

	div.search-area
	{
		padding:100px;
	}

	div.search-area .inner-search-area
	{
		text-align: center;
	}

	div.search-area .inner-search-area input
	{
	    width: 70%;
	    padding: 5px 10px;
	    font-size: 15px;
		height:35px;
	}

	div.search-area .inner-search-area button
	{
	    padding: 17px;
	    font-size: 15px;
		line-height: 3px;
	}

</style>

<script type="text/javascript">

	/* ------------------------------------------------------------------------------------------------------------ */
	// Search Autocompletion, Author: DS
	/* ------------------------------------------------------------------------------------------------------------ */
	{
		"use strict";

		var rnd        = Math.floor(Math.random() * 9999),
		    el_choices = 'theChoices' + rnd;

		document.body.insert(
			new Element('div', {
				'id':        el_choices,
				'className': 'autocomplete main-search'
			})
		);

		var _completer = new Autocompleter.Json(
			$('main-search-query'),
			el_choices,
			idoit.cachedLookup,
			{
				frequency: .2,
				choices: 85,
				searchPlaceHolder: '[{isys type="lang" ident="LC__MODULE__SEARCH__FOR"}]',
				updateElement: function(li)
                {
	                // override default behaviour
	                var link = li.getAttribute('data-link'), search = li.getAttribute('data-search');

                    if (link)
                    {
	                    this.element.value = '[{isys type="lang" ident="LC__MODULE__SEARCH__LOADING_ITEM"}]'.format(li.querySelector('div.title').innerHTML);
	                    document.location.href = link;
	                    return;
                    }

	                if (search == '1')
                    {
	                    document.location.href = '[{$config.www_dir}]search?q=' + this.element.value;
	                    return true;
                    }

	                this.element.value = li.querySelector('div.title').innerHTML;
                }
			}
		);
	}
	/* ------------------------------------------------------------------------------------------------------------ */

	if ($('cSpanRecFilter')) $('cSpanRecFilter').hide();
</script>