<style type="text/css">
	#system-overview table.listing tbody tr {
		border-top: 1px solid #888888;
	}
</style>

<div id="system-overview">
	<h2 class="p5 gradient border-bottom">[{isys type="lang" ident="LC__MODULE__SYSTEM__OVERVIEW"}] & Config Check</h2>

	<h3 class="p5 gradient border-bottom">System</h3>

	<table class="listing" style="border-left: 0;">
		<colgroup>
			<col width="200" />
			<col width="350" />
		</colgroup>
		<tbody>
			<tr>
				<td>Operating System</td>
				<td><strong>[{$os}]</strong></td>
				<td>
					<img src="[{$dir_images}]icons/silk/tick.png" class="vam" />
					<span class="vam green">[{$os_msg}]</span>
				</td>
			</tr>
			<tr>
				<td>PHP Version</td>
				<td><strong>[{$php_version}]</strong> (>[{$php_version_recommended}] recommended)</td>
				<td>
					<img src="[{$dir_images}]icons/silk/tick.png" class="vam" />
					<span class="vam green">OK</span>
				</td>
			</tr>
			<tr>
				<td>i-doit Code Version</td>
				<td><strong>[{$idoit_version.version}]</strong> [{$idoit_version.step}]</td>
				<td></td>
			</tr>
			[{if $idoit_info.version}]
			<tr>
				<td>i-doit Database Version</td>
				<td><strong>[{$idoit_info.version}]</strong> Revision [{$idoit_info.revision}]</td>
				<td>
					[{if $idoit_info.version != $idoit_version.version}]
						<img src="[{$dir_images}]icons/silk/cross.png" class="vam" />
						<strong class="vam red">FAIL</strong><br />
						DB VERSION DOES NOT MATCH CODE VERSION!<br />
						UPDATE YOUR CODE OR DATABASE!!
					[{/if}]
				</td>
			</tr>
			[{/if}]
            <tr>
                <td>Database size</td>
                <td>
                    [{$db_size}]
                </td>
                <td>
                </td>
            </tr>
            [{if $update_error_msg}]
                <tr>
                    <td>Updates</td>
                    <td>[{$update_error_msg}]</td>
                    <td>
                        <img src="[{$dir_images}]icons/silk/cross.png" class="vam" />
                        <strong class="vam red">FAIL</strong><br />
                    </td>
                </tr>
			[{elseif !$update}]
			<tr>
				<td>Updates</td>
				<td>[{$update_msg}]</td>
				<td>
					<img src="[{$dir_images}]icons/silk/tick.png" class="vam" />
					<span class="vam green">OK</span>
				</td>
			</tr>
			[{else}]
			<tr>
				<td>Updates</td>
				<td>
					<img src="[{$dir_images}]icons/silk/information.png" class="vam mr5" /><strong class="vam blue">Theres a newer version available!</strong><br />
					<strong>[{$update.version}]</strong> Revision [{$update.revision}] (Released: [{$update.release|date_format:"%d.%m.%Y"}])
				</td>
				<td>
					<img src="[{$dir_images}]icons/silk/tick.png" class="vam" />
					[{if $gProductInfo.type == 'PRO'}]
						<span class="vam"><a href="http://login.i-doit.com">Download Update from http://login.i-doit.com</a></span>
					[{else}]
						<span class="vam"><a href="[{$update.filename}]">Download Update from http://www.i-doit.org</a></span>
					[{/if}]
				</td>
			</tr>
			<tr>
				<td></td>
				<td colspan="2">
				<span class="grey">
					You need to extract the downloaded update into your i-doit source directory:
					<span class="grey bold">[{$config.base_dir}]</span> and then open the <a href="updates/">i-doit update manager</a>.
				</span>
				</td>
			</tr>
			[{/if}]
			<tr>
				<td>Browser (client)</td>
				<td>[{$browser.browser_complete}],
					[{if !$browser.engine}]
						[{$browser.os}]
					[{else}]
						[{$browser.engine}] [{if $browser.browser_title}]([{$browser.browser_title}])[{/if}]
					[{/if}]
				</td>
				<td>
					[{if $browser.chk}]
						<img src="[{$dir_images}]icons/silk/tick.png" class="vam" />
						<span class="vam green">OK</span>
					[{else}]
						<img src="[{$dir_images}]icons/silk/error.png" class="vam" />
						<span class="vam yellow">ATTENTION</span>
						[{$browser.msg}]
					[{/if}]
				</td>
			</tr>
		<tr>
			<td>
				Configuration examples
			</td>
			<td>
				<a target="_new" href="https://i-doit.atlassian.net/wiki/display/KB/Systemeinstellungen"><img class="vam" src="[{$dir_images}]icons/silk/link_go.png" /></a>
			</td>
		</tr>
		</tbody>
	</table>

	<h3 class="p5 gradient border-top border-bottom">PHP.ini Settings</h3>

	<table class="listing" style="border-left: 0;">
		<colgroup>
			<col width="200" />
			<col width="350" />
		</colgroup>
		<tbody>
			<tr>
				<td>max_execution_time</td>
				<td>
					[{if $php.max_execution_time > 0}]
						<strong>[{$php.max_execution_time}]</strong>s
					[{else}]
						<strong>infinite</strong>
					[{/if}]
				</td>
				<td>
					[{if $php.max_execution_time < 180 && $php.max_execution_time != 0}]
						<img src="[{$dir_images}]icons/silk/error.png" class="vam" />
						<span class="vam yellow">>180 recommended</span>
					[{else}]
						<img src="[{$dir_images}]icons/silk/tick.png" class="vam" />
						<span class="vam green">OK</span>
					[{/if}]
				</td>
			</tr>
			<tr>
				<td>upload_max_filesize</td>
				<td><strong>[{$php.upload_max_filesize}]</strong></td>
				<td>
					[{if isys_convert::to_bytes($php.upload_max_filesize) < isys_convert::to_bytes('128M')}]
						<img src="[{$dir_images}]icons/silk/error.png" class="vam" />
						<span class="vam yellow">>=128M recommended, 64M OK</span>
					[{else}]
						<img src="[{$dir_images}]icons/silk/tick.png" class="vam" />
						<span class="vam green">OK</span>
					[{/if}]
				</td>
			</tr>
			<tr>
				<td>post_max_size</td>
				<td><strong>[{$php.post_max_size}]</strong></td>
				<td>
					[{if isys_convert::to_bytes($php.post_max_size) < isys_convert::to_bytes('128M')}]
						<img src="[{$dir_images}]icons/silk/error.png" class="vam" />
						<span class="vam yellow">>=128M recommended, 64M OK</span>
					[{else}]
						<img src="[{$dir_images}]icons/silk/tick.png" class="vam" />
						<span class="vam green">OK</span>
					[{/if}]
				</td>
			</tr>
			<tr>
				<td>allow_url_fopen</td>
				<td><strong>[{$php.allow_url_fopen}]</strong></td>
				<td>
					[{if !$php.allow_url_fopen || $php.allow_url_fopen == 'Off'}]
						<img src="[{$dir_images}]icons/silk/error.png" class="vam" />
						<span class="vam yellow">Enable in order to use web requests (used for automatic updates, report browser, etc.)</span>
					[{else}]
						<img src="[{$dir_images}]icons/silk/tick.png" class="vam" />
						<span class="vam green">OK</span>
					[{/if}]
				</td>
			</tr>
			<tr>
				<td>post_max_size</td>
				<td><strong>[{$php.post_max_size}]</strong></td>
				<td>
					[{if $php.post_max_size != 0 && isys_convert::to_bytes($php.post_max_size) < isys_convert::to_bytes('128M')}]
						<img src="[{$dir_images}]icons/silk/error.png" class="vam" />
						<span class="vam yellow">>= 128M</span>
					[{else}]
						<img src="[{$dir_images}]icons/silk/tick.png" class="vam" />
						<span class="vam green">OK</span>
					[{/if}]
				</td>
			</tr>
			<tr>
				<td>max_input_vars</td>
				<td><strong>[{$php.max_input_vars}]</strong></td>
				<td>
					[{if $php.max_input_vars != 0 && intval($php.max_input_vars) < 10000}]
				<img src="[{$dir_images}]icons/silk/error.png" class="vam" />
					<span class="vam yellow">>= 10000</span>
					[{else}]
				<img src="[{$dir_images}]icons/silk/tick.png" class="vam" />
					<span class="vam green">OK</span>
					[{/if}]
				</td>
			</tr>
			<tr>
				<td>file_uploads</td>
				<td><strong>[{$php.file_uploads}]</strong></td>
				<td>
					[{if !$php.file_uploads || $php.file_uploads == 'Off'}]
						<img src="[{$dir_images}]icons/silk/error.png" class="vam" />
						<span class="vam yellow">Enable in order to upload files (http://php.net/file-uploads)</span>
					[{else}]
						<img src="[{$dir_images}]icons/silk/tick.png" class="vam" />
						<span class="vam green">OK</span>
					[{/if}]
				</td>
			</tr>
			<tr>
				<td>memory_limit</td>
				<td><strong>[{$php.memory_limit}]</strong></td>
				<td>
					[{if $php.memory_limit != 0 && isys_convert::to_bytes($php.memory_limit) < isys_convert::to_bytes('256M')}]
						<img src="[{$dir_images}]icons/silk/error.png" class="vam" />
						<span class="vam yellow">>=256M recommended</span>
					[{else}]
						<img src="[{$dir_images}]icons/silk/tick.png" class="vam" />
						<span class="vam green">OK</span>
					[{/if}]
				</td>
			</tr>
		</tbody>
	</table>

	<h3 class="p5 gradient border-top border-bottom">MySQL Settings</h3>

	<table class="listing" style="border-left: 0;">
		<colgroup>
			<col width="200" />
			<col width="350" />
		</colgroup>
		<tbody>
			<tr>
				<td>innodb_buffer_pool_size</td>
				<td><strong>[{$mysql.innodb_buffer_pool_size/1024/1024}] MB</strong></td>
				<td>
					[{if $mysql.innodb_buffer_pool_size/1024/1024 < 1024}]
						<img src="[{$dir_images}]icons/silk/error.png" class="vam" />
						<span class="vam yellow">>=1024MB recommended (<a target="_new" href="http://dev.mysql.com/doc/refman/5.6/en/innodb-buffer-pool.html"><img class="vam" src="[{$dir_images}]icons/silk/link_go.png" /></a>)</span>
					[{else}]
						<img src="[{$dir_images}]icons/silk/tick.png" class="vam" />
						<span class="vam green">OK</span>
					[{/if}]
				</td>
			</tr>
			<tr>
				<td>max_allowed_packet</td>
				<td><strong>[{$mysql.max_allowed_packet/1024/1024}] MB</strong></td>
				<td>
					[{if $mysql.max_allowed_packet/1024/1024 < 128}]
						<img src="[{$dir_images}]icons/silk/error.png" class="vam" />
						<span class="vam yellow">>=128MB recommended (<a target="_new" href="https://dev.mysql.com/doc/refman/5.5/en/packet-too-large.html"><img class="vam" src="[{$dir_images}]icons/silk/link_go.png" /></a>)</span>
					[{else}]
						<img src="[{$dir_images}]icons/silk/tick.png" class="vam" />
						<span class="vam green">OK</span>
					[{/if}]
				</td>
			</tr>
			<tr>
				<td>query_cache_limit</td>
				<td><strong>[{$mysql.query_cache_limit/1024/1024}] MB</strong></td>
				<td>
					[{if $mysql.query_cache_limit/1024/1024 < 5}]
						<img src="[{$dir_images}]icons/silk/error.png" class="vam" />
						<span class="vam yellow">5MB recommended (<a target="_new" href="http://dev.mysql.com/doc/refman/5.6/en/server-system-variables.html#sysvar_query_cache_limit"><img class="vam" src="[{$dir_images}]icons/silk/link_go.png" /></a>)</span>
					[{else}]
						<img src="[{$dir_images}]icons/silk/tick.png" class="vam" />
						<span class="vam green">OK</span>
					[{/if}]
				</td>
			</tr>
			<tr>
				<td>query_cache_size</td>
				<td><strong>[{$mysql.query_cache_size/1024/1024}] MB</strong></td>
				<td>
					[{if $mysql.query_cache_size/1024/1024 > 80}]
						<img src="[{$dir_images}]icons/silk/error.png" class="vam" />
						<span class="vam yellow"><=80M recommended (<a target="_new" href="http://dev.mysql.com/doc/refman/5.6/en/server-system-variables.html#sysvar_query_cache_size"><img class="vam" src="[{$dir_images}]icons/silk/link_go.png" /></a>)</span>
					[{else}]
						<img src="[{$dir_images}]icons/silk/tick.png" class="vam" />
						<span class="vam green">OK</span>
					[{/if}]
				</td>
			</tr>
			<tr>
				<td>tmp_table_size</td>
				<td><strong>[{$mysql.tmp_table_size/1024/1024}] MB</strong></td>
				<td>
					[{if $mysql.tmp_table_size/1024/1024 < 32}]
						<img src="[{$dir_images}]icons/silk/error.png" class="vam" />
						<span class="vam yellow">32M recommended (<a target="_new" href="http://dev.mysql.com/doc/refman/5.6/en/server-system-variables.html#tmp_table_size"><img class="vam" src="[{$dir_images}]icons/silk/link_go.png" /></a>)</span>
					[{else}]
						<img src="[{$dir_images}]icons/silk/tick.png" class="vam" />
						<span class="vam green">OK</span>
					[{/if}]
				</td>
			</tr>
			<tr>
				<td>join_buffer_size</td>
				<td><strong>[{$mysql.join_buffer_size}] bytes</strong></td>
				<td>
					[{if $mysql.join_buffer_size > 262144}]
						<img src="[{$dir_images}]icons/silk/error.png" class="vam" />
						<span class="vam yellow">262144 recommended (<a target="_new" href="http://dev.mysql.com/doc/refman/5.6/en/server-system-variables.html#join_buffer_size"><img class="vam" src="[{$dir_images}]icons/silk/link_go.png" /></a>)</span>
					[{else}]
						<img src="[{$dir_images}]icons/silk/tick.png" class="vam" />
						<span class="vam green">OK</span>
					[{/if}]
				</td>
			</tr>
			<tr>
				<td>sort_buffer_size</td>
				<td><strong>[{$mysql.sort_buffer_size}] bytes</strong></td>
				<td>
					[{if $mysql.sort_buffer_size > 262144}]
						<img src="[{$dir_images}]icons/silk/error.png" class="vam" />
						<span class="vam yellow">262144 recommended (<a target="_new" href="http://dev.mysql.com/doc/refman/5.6/en/server-system-variables.html#sort_buffer_size"><img class="vam" src="[{$dir_images}]icons/silk/link_go.png" /></a>)</span>
					[{else}]
						<img src="[{$dir_images}]icons/silk/tick.png" class="vam" />
						<span class="vam green">OK</span>
					[{/if}]
				</td>
			</tr>
			<tr>
				<td>innodb_sort_buffer_size</td>
				<td><strong>[{$mysql.innodb_sort_buffer_size/1024/1024}] MB</strong></td>
				<td>
					[{if $mysql.innodb_sort_buffer_size/1024/1024 < 64}]
						<img src="[{$dir_images}]icons/silk/error.png" class="vam" />
						<span class="vam yellow">64M recommended (<a target="_new" href="http://dev.mysql.com/doc/refman/5.6/en/server-system-variables.html#innodb_sort_buffer_size"><img class="vam" src="[{$dir_images}]icons/silk/link_go.png" /></a>)</span>
					[{else}]
						<img src="[{$dir_images}]icons/silk/tick.png" class="vam" />
						<span class="vam green">OK</span>
					[{/if}]
				</td>
			</tr>
			<tr>
				<td>innodb_log_file_size</td>
				<td><strong>[{$mysql.innodb_log_file_size/1024/1024}] MB</strong></td>
				<td>
					[{if $mysql.innodb_log_file_size/1024/1024 < 512}]
						<img src="[{$dir_images}]icons/silk/error.png" class="vam" />
						<span class="vam yellow">>=512M recommended (<a target="_new" href="http://dev.mysql.com/doc/refman/5.6/en/server-system-variables.html#innodb_log_file_size"><img class="vam" src="[{$dir_images}]icons/silk/link_go.png" /></a>)</span>
					[{else}]
						<img src="[{$dir_images}]icons/silk/tick.png" class="vam" />
						<span class="vam green">OK</span>
					[{/if}]
				</td>
			</tr>
			<tr>
				<td>tmp-table-size</td>
				<td><strong>[{$mysql.tmp_table_size/1024/1024}] MB</strong></td>
				<td>
					[{if $mysql.tmp_table_size < 16777216}]
						<img src="[{$dir_images}]icons/silk/error.png" class="vam" />
						<span class="vam yellow">>=16MB recommended (<a target="_new" href="http://dev.mysql.com/doc/refman/5.6/en/server-system-variables.html#sysvar_tmp_table_size"><img class="vam" src="[{$dir_images}]icons/silk/link_go.png" /></a>)</span>
					[{else}]
						<img src="[{$dir_images}]icons/silk/tick.png" class="vam" />
						<span class="vam green">OK</span>
					[{/if}]
				</td>
			</tr>
			<tr>
				<td>max-heap-table-size</td>
				<td><strong>[{$mysql.max_heap_table_size/1024/1024}] MB</strong></td>
				<td>
					[{if $mysql.max_heap_table_size < 16777216}]
						<img src="[{$dir_images}]icons/silk/error.png" class="vam" />
						<span class="vam yellow">>=16MB recommended (<a target="_new" href="http://dev.mysql.com/doc/refman/5.6/en/server-system-variables.html#sysvar_max_heap_table_size"><img class="vam" src="[{$dir_images}]icons/silk/link_go.png" /></a>)</span>
					[{else}]
						<img src="[{$dir_images}]icons/silk/tick.png" class="vam" />
						<span class="vam green">OK</span>
					[{/if}]
				</td>
			</tr>
			<tr>
				<td class="grey">datadir</td>
				<td class="grey">[{$mysql.datadir}]</td>
				<td>
					<img src="[{$dir_images}]icons/silk/information.png" class="vam" />
					<span class="vam green">INFO</span>
				</td>
			</tr>
		</tbody>
	</table>

	<h3 class="p5 gradient border-top border-bottom">PHP Extension</h3>

	<table class="listing" style="border-left: 0;">
		<colgroup>
			<col width="200" />
			<col width="350" />
		</colgroup>
		<tbody>
			[{foreach $php_dependencies as $dependency => $modules}]
			<tr>
				<td>[{$dependency}]</td>
				<td>[{$modules|implode:", "}]</td>
				<td>
					[{if extension_loaded($dependency)}]<img src="[{$dir_images}]icons/silk/tick.png" class="vam" /> <span class="vam green">OK</span>
					[{else}]<img src="[{$dir_images}]icons/silk/cross.png" class="vam" /> <span class="vam red">ERROR</span>[{/if}]
				</td>
			</tr>
			[{/foreach}]

			<tr>
				<td>SNMP</td>
				<td>CMDB</td>
				<td>
					[{if !extension_loaded("snmp")}]
						<img src="[{$dir_images}]icons/silk/error.png" class="vam" />
						<span class="vam yellow">WARNING</span><span class="vam"> - Extension needed for SNMP Connections. (Category SNMP or PDU)</span>
					[{else}]
						<img src="[{$dir_images}]icons/silk/tick.png" class="vam" />
						<span class="vam green">OK</span>
					[{/if}]
				</td>
			</tr>
			<tr>
				<td>Sockets</td>
				<td>Monitoring</td>
				<td>
					[{if !extension_loaded("sockets")}]
						<img src="[{$dir_images}]icons/silk/error.png" class="vam" />
						<span class="vam yellow">NOTICE</span><span class="vam"> - Extension needed for querying livestatus</span>
					[{else}]
						<img src="[{$dir_images}]icons/silk/tick.png" class="vam" />
						<span class="vam green">OK</span>
					[{/if}]
				</td>
			</tr>
		</tbody>
	</table>

	<h3 class="p5 gradient border-top border-bottom">Apache Modules</h3>

	<table class="listing" style="border-left: 0;">
		<colgroup>
			<col width="200" />
			<col width="350" />
		</colgroup>
		<tbody>
			[{foreach $apache_dependencies as $dependency => $modules}]
			<tr>
				<td>[{$dependency}]</td>
				<td>[{$modules|implode:", "}]</td>
				<td>
					[{if function_exists('apache_get_modules')}]
						[{if isys_core::is_webserver_module_installed($dependency)}]
							[{if isys_core::is_webserver_module_configured($dependency)}]
								<img src="[{$dir_images}]icons/silk/tick.png" class="vam" /> <span class="vam green">OK</span>
							[{else}]
								<img src="[{$dir_images}]icons/silk/cross.png" class="vam" /> <span class="vam red">Please verify that the apache module "[{$dependency}]" is correctly configured. An automatic check identified that it is not.</span>
							[{/if}]
						[{else}]<img src="[{$dir_images}]icons/silk/cross.png" class="vam" /> <span class="vam red">ERROR</span>[{/if}]
					[{else}]
						<img src="[{$dir_images}]icons/silk/error.png" class="vam" /> <span class="vam yellow">Please verify that an equivalent to the apache module "[{$dependency}]" is installed and active.</span>
					[{/if}]
				</td>
			</tr>
			[{/foreach}]
		</tbody>
	</table>

	<h3 class="p5 gradient border-top border-bottom">Rights & Directories</h3>

	<table class="listing" style="border-left: 0;">
		<colgroup>
			<col width="200" />
			<col width="350" />
		</colgroup>
		<tbody>
			[{foreach $rights as $k => $r}]
			<tr>
				<td>[{$k|capitalize}]</td>
				<td>[{$r.dir}]</td>
				<td>
					[{assign var=chk value=$r.chk}]
					[{if $r.chk}]
						<img src="[{$dir_images}]icons/silk/tick.png" class="vam" />
						<span class="vam green">[{$r.msg}]</span>
					[{else}]
						<img src="[{$dir_images}]icons/silk/cross.png" class="vam" />
						<span class="vam red bold">NOT [{$r.msg}]</span>
						[{if $r.note}]
							<br /><span class="vam bold">[{$r.note}]</span>
						[{/if}]
					[{/if}]
				</td>
			</tr>
			[{/foreach}]
		</tbody>
	</table>
</div>