<div class="login">

	[{if $error}]<div class="error p10 mb10">[{$error}]</div>[{/if}]

	<form action="[{$loginAction}]" method="post">
		<table>
			<tr>
				<td>Username:</td>
				<td><input type="text" name="username" class="inputText" id="username" value="" style="width:150px" /></td>
			</tr>
			<tr>
				<td>Password:</td>
				<td><input type="password" name="password" class="inputText" id="password" value="" style="width:150px" /></td>
			</tr>
			<tr>
				<td></td>
				<td class="toolbar"><input type="submit" name="submit" value="Login" /></td>
			</tr>
		</table>
	</form>

</div>

<script type="text/javascript">$('username').focus();</script>