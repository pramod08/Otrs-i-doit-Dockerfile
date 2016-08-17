<img id="menuScrollLeft" src="[{$dir_images}]icons/silk/control_rewind.png" alt="<" style="display:none;">

<div class="logo-top-left" id="logo-top-left">
	<a href="[{$config.www_dir}]"><img src="[{$dir_images}]logo16.png" alt="i-doit"/></a>
</div>
<div class="arrowsprite" id="downarrow"></div>
<div style="position:relative; display:inline-block; margin-left: 60px;">
	<ul id="mainMenu">
		[{foreach from=$mainMenu item='link' key='id'}]
		<li class="[{$link.3}][{if $activeMainMenuItem eq $id}] active[{/if}]">
			<a href="[{$link.0}]" onclick="[{$link.2}]">[{isys type="lang" ident=$link.1}]</a>
		</li>
		[{/foreach}]
	</ul>
</div>