<ul id="breadcrumb" class="noprint">
	[{isys type="breadcrumb_navi" name="breadcrumb" p_home=1 p_prepend="<li>" p_append="</li>"}]

	[{if $trialInfo}]
		<li class="bold red">
			[{$trialInfo.message}]
		</li>
	[{/if}]
</ul>

<ul id="flags" class="f16">
	[{if $flag_de}]
		<li class="flag de"><a href="[{$flag_de}]">&nbsp;</a></li>
	[{/if}]
	[{if $flag_en}]
		<li class="flag us"><a href="[{$flag_en}]">&nbsp;</a></li>
	[{/if}]
	<li>
		<a title="[{isys type="lang" ident="LC__MODULE__USER_SETTINGS__TITLE"}]" href="[{$g_link__user}]">
			<img alt="user settings" src="[{$dir_images}]icons/silk/user_gray.png" />
		</a>
	</li>
	<li>
		<a title="[{isys type="lang" ident="LC__NAVIGATION__MAINMENU__TITLE_ADMINISTRATION"}]"
		   href="[{$g_link__settings}]">
			<img alt="system settings" src="[{$dir_images}]icons/silk/cog.png" />
		</a>
	</li>
	<li>
		<a title="[{isys type="lang" ident="LC__NAVIGATION__MAINMENU__TITLE_LOGOUT"}]" href="?logout=1">
			<img alt="logout" title='' src="[{$dir_images}]icons/logout.png" />
		</a>
	</li>
</ul>

<div id="main_content">
	[{include file=$index_includes.contentarea|default:"content/main.tpl"}]
</div>

<div id="infoBox">
	<div class="version">i-doit [{$gProductInfo.version}] [{$gProductInfo.step}] [{$gProductInfo.type}]</div>
	<div>
		[{$infobox->show_html()|strip}]
	</div>
</div>