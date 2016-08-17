<h2 class="gradient p5">Modules:</h2>
<hr />

<div class="m10">
	<h3>Initialized:</h3>

	<table class="mainTable mt10">
		<colgroup width="180" />
		<tr>
			<th>Identifier</th>
			<th>Init File</th>
		</tr>
	[{foreach from=$init_modules item=module key=ident}]
		<tr>
			<td>[{$ident}]</td>
			<td>[{$module}]</td>
		</tr>
	[{/foreach}]
	</table>

	<br />

	<h3>Registered:</h3>

	<table class="mainTable mt10">
		<colgroup width="120" />
		<tr>
			<th>ID</th>
			<th>Module</th>
		</tr>
	[{foreach from=$modules item=module key=ident}]
		[{assign var="data" value=$module->get_data()}]
		<tr>
			<td><a href="?moduleID=[{$ident}]">[{$ident}]</a></td>
			<td><a href="?moduleID=[{$ident}]">[{isys type="lang" ident=$data.isys_module__title}]</a></td>
		</tr>
	[{/foreach}]
	</table>
</div>