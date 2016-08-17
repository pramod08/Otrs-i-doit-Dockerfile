<h2 class="gradient p5 border-bottom text-shadow">[{isys type="lang" ident="LC__MAINTENANCE__MAILTEMPLATE"}]</h2>

[{isys type="f_text" name="C__MAINTENANCE__MAILTEMPLATE__ID"}]

<table class="contentTable">
	<tr>
		<td class="key">[{isys type="f_label" name="C__MAINTENANCE__MAILTEMPLATE__TITLE" ident="LC__MAINTENANCE__MAILTEMPLATE__TITLE"}]</td>
		<td class="value">[{isys type="f_text" name="C__MAINTENANCE__MAILTEMPLATE__TITLE"}]</td>
	</tr>
	<tr>
		<td class="key vat">[{isys type="f_label" name="C__MAINTENANCE__MAILTEMPLATE__TEXT" ident="LC__MAINTENANCE__MAILTEMPLATE__TEXT"}]</td>
		<td class="value">[{isys type="f_textarea" name="C__MAINTENANCE__MAILTEMPLATE__TEXT"}]</td>
	</tr>
	<tr>
		<td class="key vat">[{isys type="lang" ident="LC__MAINTENANCE__MAILTEMPLATE__TEXT_VARIABLES"}]</td>
		<td class="pl20">[{$variables}]</td>
	</tr>
</table>

<script>
	(function () {
		'use strict';

		var id = $F('C__MAINTENANCE__MAILTEMPLATE__ID'),
			location = document.location.href.toQueryParams();

		// This comes in handy to set the "id" parameter, even if the user got here using the checkboxes.
		if (id > 0 && Object.isFunction(window.pushState) && !location.hasOwnProperty('[{$smarty.const.C__GET__ID}]')) {
			location['[{$smarty.const.C__GET__ID}]'] = id;

			setTimeout(function () {
				window.pushState({}, document.title, '?' + Hash.toQueryString(location));
			}, 100);
		}
	})();
</script>