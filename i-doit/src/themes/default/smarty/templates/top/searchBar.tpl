<div id="searchBar" class="text-shadow-black">
	<span class="login-small" title="Logged in as: [{$session->get_current_username()|default:"Unknown"}]">
		<img class="vam" src="[{$dir_images}]/icons/silk/user_gray.png" alt="" /> [{$session->get_current_username()|default:"Unknown"}]
	</span>

	<img id="menuScrollRight" src="[{$dir_images}]icons/silk/control_fastforward.png" alt=">" style="display:none;" />
	
	<span class="login-string">

		<span>Logged in as: <strong>[{$session->get_current_username()|default:"Unknown"}]</strong> in </span>
		<span id="mandator_selection">
			<span onmouseover="new Ajax.Updater('mandator_selection','?ajax=1&call=fetch_mandators');">
				<strong title="[{isys type="lang" ident="LC__UNIVERSAL__CURRENT_MANDANT"}]">[{$g_mandant_name}]</strong>
			</span>
		</span>
	</span>
	<span class="searchField">
		<span class="sbox">
			<div id="searchHelpText" style="display:none;">
				<table>
					<tr>
						<td class="bold right pr5">#1234</td>
						<td>[{isys type="lang" ident="LC__GLOBAL_SEARCH_HELP__NAVIGATE_TO"}]</td>
					</tr>
					<tr>
						<td class="bold right pr5">title:1234</td>
						<td>[{isys type="lang" ident="LC__GLOBAL_SEARCH_HELP__SPECIFIC_SEARCH"}]</td>
					</tr>
					<tr>
						<td class="bold right pr5">abc123</td>
						<td>[{isys type="lang" ident="LC__GLOBAL_SEARCH_HELP__GLOBAL_SEARCH"}]</td>
					</tr>
				</table>
			</div>

			[{if isys_auth_search::instance()->is_allowed_to(isys_auth::VIEW, 'search')}]
				<img src="[{$dir_images}]icons/silk/help.png" id="helpSearch" alt="?" class="greyscale vam" />
				<input type="text" name="q" id="globalSearch"
				       placeholder="[{isys type="lang" ident="LC__MODULE__SEARCH__TITLE"}].."
				       autocapitalize="off" autocomplete="off" autosave="idoit_search" spellcheck="false"
				       results="5" value="[{$smarty.get.q|escape}]" />

				<script type="text/javascript">

					/* ------------------------------------------------------------------------------------------------------------ */
					// Global Search Autocompletion
					/* ------------------------------------------------------------------------------------------------------------ */
					{
						"use strict";

						var cachedBackend = new Autocompleter.Cache(
							function (searchString, suggest, options)
							{

								new Ajax.Request(www_dir + 'search', {
									method:     'get',
									parameters: {
										q:    searchString.trim(),
										rand: (new Date()).getTime()
									},
									onSuccess:  function (response) {
										suggest(response.responseJSON);
									}
								});
							},
								{
									minChars:           3,
									choices:            125,
									fuzzySearch:        true
								}
						);
						idoit.cachedLookup = cachedBackend.lookup.bind(cachedBackend);

						var rnd        = Math.floor(Math.random() * 9999),
						    el_choices = 'theChoices' + rnd;

						document.body.insert(
							new Element('div', {
								'id':        el_choices,
								'className': 'autocomplete global-search'
							})
						);

						var _completer = new Autocompleter.Json(
							$('globalSearch'), el_choices, idoit.cachedLookup, {
								frequency: .15,
								choices: 85,
								searchPlaceHolder: '[{isys type="lang" ident="LC__MODULE__SEARCH__FOR"}]',
								updateElement: function(li)
				                {
					                // override default behaviour
					                var link = li.getAttribute('data-link'), search = li.getAttribute('data-search');

				                    if (link)
				                    {
					                    document.location.href = encodeURI(link);
					                    this.selectedItem = li;
				                    }

					                if (search == '1')
				                    {
					                    document.location.href = www_dir + 'search?q=' + this.element.value;
					                    return true;
				                    }
				                }
							}
						);
					}
					/* ------------------------------------------------------------------------------------------------------------ */


					new Tip($('helpSearch'), $('searchHelpText').innerHTML, {
						style: 'darkgrey'
					});

					$('helpSearch').observe('prototip:shown', function (ev, tip) {
						this.pulsate({pulses: 1, duration: 0.3, from: 0.5});
					});

					$('globalSearch').on('keydown', function (event) {
						var el = event.findElement('input');

						if ((event.which && event.which == 13) || (event.keyCode && event.keyCode == 13)) {
							event.preventDefault();

							if (el.value.search('#') == 0) {
								window.location.href = www_dir + '?objID=' + el.value.replace('#', '');
							}
							else
							{
								// Submit search only if there is no item selected
								if (_completer.selectedItem == null) window.location.href = www_dir + 'search?q=' + el.value;

							}
						}
					});
				</script>
			[{/if}]
		</span>
	</span>
</div>