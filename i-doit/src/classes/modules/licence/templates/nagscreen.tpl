<div id="exception-fullscreen" class="error">
	<h2 class="p5 gradient text-shadow">
		<strong class="fr mr5 mouse-pointer" onclick="popup_close();">&times;</strong>
		[{isys type="lang" ident="LC__LICENCE__NO_LICENCE"}]
	</h2>
	<p><a href="[{$www_dir}]index.php?moduleID=[{$smarty.const.C__MODULE__SYSTEM}]&handle=licence_overview">[{isys type="lang" ident="LC__UNIVERSAL__LICENCEADMINISTRATION"}]</a></p>
</div>

<style>
	#exception-fullscreen {
		margin: -1px;
		width: 100%;
		height: 100%;
	}

	#exception-fullscreen h2.gradient {
		background-color: #faa;
		border-bottom: 1px solid #ff4343;
	}

	#exception-fullscreen p {
		padding: 5px;
	}
</style>