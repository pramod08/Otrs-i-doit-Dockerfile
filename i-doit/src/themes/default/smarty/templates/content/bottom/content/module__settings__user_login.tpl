<h2 class="p5 gradient border-bottom">[{isys type="lang" ident=$title}]</h2>

<table class="contentTable">
	<tr>
		<td class="key">[{isys type="f_label" ident="LC__LOGIN__PASSWORD" name="C__CONTACT__PERSON_PASSWORD"}]</td>
		<td class="value">[{isys type="f_text" name="C__CONTACT__PERSON_PASSWORD" p_bPassword="1"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" ident="LC__LOGIN__PASSWORD" name="C__CONTACT__PERSON_PASSWORD_SECOND"}]</td>
		<td class="value">[{isys type="f_text" name="C__CONTACT__PERSON_PASSWORD_SECOND" p_bPassword="1"}]</td>
	</tr>
</table>

[{if $error}]
<div class="exception p5 mt10">[{$error}]</div>
[{/if}]