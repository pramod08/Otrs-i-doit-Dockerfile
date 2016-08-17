<h2>i-doit Update</h2>
<table class="info">
	<colgroup>
		<col width="150" />
	</colgroup>
 	<tr>
  		<td colspan="2"><h3>Compatibility check</h3></td>
	</tr>
	<tr>
		<td class="key">Operating System:</td>
		<td>[{$g_os.name}]</td>
	</tr>
	<tr>
		<td class="key">Version: </td>
		<td>[{$g_os.version}]</td>
	</tr>
	<tr>
		<td colspan="2">&nbsp;</td>
	</tr>
	[{if $php_version_error}]
		<tr>
			<td class="key">PHP Version</td>
			<td><p class="exception bold" style="padding:5px;">[{$php_version_error}]</p></td>
		</tr>
	[{else}]
		<tr>
			<td class="key">PHP Version</td>
			<td>[{$smarty.const.PHP_VERSION}] (PHP [{$smarty.const.PHP_VERSION_MINIMUM_RECOMMENDED}] or higher recommended)</td>
		</tr>
	[{/if}]
	[{if $sql_version_error}]
	<tr>
		<td class="key">MySQL Version</td>
		<td><p class="exception bold" style="padding:5px;">[{$sql_version_error}]</p></td>
	</tr>
	[{else}]
	<tr>
		<td class="key">MySQL Version</td>
		<td>[{$smarty.const.MYSQL_VERSION_MINIMUM}] (MySQL [{$smarty.const.MYSQL_VERSION_MINIMUM_RECOMMENDED}] recommended)</td>
	</tr>
	[{/if}]
	<tr>
		<td colspan="2">&nbsp;</td>
	</tr>
	<tr>
		<td class="key" style="vertical-align: top;">PHP Settings</td>
		<td>
			<ul>
				[{foreach $php_settings as $setting => $data}]
				<li><strong>[{$setting}]</strong> <span style="float:left; width:50px;">[{$data.value}]</span> [{if $data.check}]<img src="[{$dir_images}]icons/silk/tick.png" />[{else}]<img src="[{$dir_images}]icons/silk/cross.png" /><span class="red">[{$data.message}]</span>[{/if}]</li>
				[{/foreach}]
			</ul>
		</td>
	</tr>
	<tr>
		<td colspan="2">&nbsp;</td>
	</tr>
	<tr>
		<td class="key" style="vertical-align: top;">PHP Extensions</td>
		<td>
			<ul>
			[{foreach $dependencies as $dependency => $module}]
				<li><strong>[{$dependency}] <img src="[{$dir_images}]icons/silk/information.png" class="mouse-help" title="Used by [{$module|implode:', '}]" /></strong> [{if extension_loaded($dependency)}]<img src="[{$dir_images}]icons/silk/tick.png" /><span class="green">OK</span>[{else}]<img src="[{$dir_images}]icons/silk/cross.png" /><span class="red">NOT FOUND</span>[{/if}]</li>
			[{/foreach}]
			</ul>
		</td>
	</tr>
	<tr>
		<td colspan="2">&nbsp;</td>
	</tr>
	<tr>
		<td class="key" style="vertical-align: top;">Apache modules</td>
		<td>
			[{if function_exists('apache_get_modules')}]
				<ul>
				[{foreach $apache_dependencies as $dependency => $module}]
					<li><strong>[{$dependency}] <img src="[{$dir_images}]icons/silk/information.png" class="mouse-help" title="Used by [{$module|implode:', '}]" /></strong> [{if isys_update::is_webserver_module_installed($dependency)}]<img src="[{$dir_images}]icons/silk/tick.png" /><span class="green">OK</span>[{else}]<img src="[{$dir_images}]icons/silk/cross.png" /><span class="red">NOT FOUND</span>[{/if}]</li>
				[{/foreach}]
				</ul>
			[{else}]
			<ul>
				<li><img src="[{$dir_images}]icons/silk/error.png" /> <span>Please verify that the apache module "mod_rewrite" is installed and active.</span></li>
			</ul>
			[{/if}]
		</td>
	</tr>
	<tr>
  		<td colspan="2"><h3>i-doit</h3></td>
	</tr>
	<tr>
		<td class="key">Current version</td>
		<td>[{$g_info.version|default:"<= 0.9"}]</td>
	</tr>
	<tr>
		<td class="key">Current revision</td>
		<td>[{$g_info.revision|default:"<= 2500"}]</td>
	</tr>
 </table>

<style type="text/css">
	ul, li {
		margin: 0;
		padding: 0;
		list-style: none;
	}

	li strong {
		clear: both;
		width: 110px;
		display: block;
		float: left;
	}

	li strong img {
		height:12px;
	}

	li strong,
	li span,
	li img {
		vertical-align: middle;
	}

	li strong,
	li img {
		margin-right: 5px;
	}

	.mouse-help {
		cursor: help;
	}

	span.green {
		color:#009900;
	}
	span.red {
		color:#AA0000;
	}
</style>