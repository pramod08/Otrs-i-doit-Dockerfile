<div id="content" class="content">
	<h2>File-Update</h2>

	<p>The following files will be updated:</p>

	<fieldset class="filelist" id="filelist_truncate">
	<legend>Files ([{$g_filecount}])</legend>
		<div>
			[{$g_files|truncate:200:".."|nl2br}]
		</div>
		[{if strlen($g_files)>200}]
		<div style="text-align:right;">
			<a href="javascript:void(0);" onclick="document.getElementById('filelist').className='filelist'; document.getElementById('filelist_truncate').className='filelist hidden';">+ Expand</a>
		</div>
		[{/if}]
	</fieldset>

	[{if strlen($g_files)>200}]
		<fieldset class="filelist hidden" id="filelist">
		<legend>Files</legend>
			<div>
				[{$g_files|nl2br}]
			</div>
			<div style="text-align:right;">
				<a href="javascript:void(0);" onclick="document.getElementById('filelist').className='filelist hidden';document.getElementById('filelist_truncate').className='filelist';">- Collapse</a>
			</div>
		</fieldset>
	[{/if}]

	[{if $g_not_writeable}]
		<p class="bold message" style="font-weight: bold; padding: 4px; margin: 10px 2px; background-color: rgb(255, 221, 221); border: 1px solid rgb(255, 67, 67); display: block;">
			<u>Warning: File copy will not work properly.</u><br /><br />
			Please make sure the apache user got write permissions to your complete i-doit directory and reload this page.<br />

			Linux users can use the following shell script: "[{$g_config.base_dir}]idoit-rights.sh set"
			<br /><br />

			<input type="submit" value="Reload" />
		</p>
	[{/if}]

	[{if $g_filecount gt 0}]
	<p>
	<input type="checkbox" [{if $g_not_writeable || isset($smarty.session.no_temp)}]checked="checked"[{/if}][{if $g_not_writeable}] disabled="disabled"[{/if}] name="no_temp" id="no_temp" value="true" style="vertical-align:middle;" />
	<label for="no_temp">Don't delete temp directories!</label>
	</p>
	[{/if}]

	<p class="bold message" style="font-weight: bold; padding: 4px; margin: 10px 2px; background-color: rgb(255, 221, 221); border: 1px solid rgb(255, 67, 67); display: block;">
		We strongly recommend that you do a database backup of your system and mandator databases before starting this update !
	</p>

	<p style="margin:2px;">Click "Yes, i did a backup!" to start the update procedure.</p>

	<script type="text/javascript">
		Event.observe(window, 'load', function(){
			$('btn_next').value = 'Yes, i did a backup! - Start the update';
			$('btn_next').style.width = '400px';
			$('btn_next').style.fontWeight = 'bold';
		});
	</script>


</div>

<div id="loadingTable" style="display:none;padding:10px;" class="loadingTable">
    <img src="[{$g_config.www_dir}]setup/images/main_installing.gif" style="vertical-align:middle;" />
   <strong>Update in progress, please wait ...</strong><br />
	<p>Depending on the size of your database and hardware performance, this update could take up to 15 minutes ...</p>

	<button onclick="$('game').show();this.up().hide();$('loadingGif').show();" style="margin:10px auto;">Play a game while the update is in progress</button>
</div>
<div id="game" class="loadingTable" style="display:none;position:absolute;top:130px;padding:3px;" >
	<button onclick="this.up().hide();$('loadingGif').hide();$('loadingTable').show();" style="position: absolute;bottom:-25px;left:30px;">Quit Game</button>
	<canvas id="V" onclick="C=-Y" style="border:1px solid #ddd;">
		<script>
			// Inspired by featherweight, by john girvin || https://github.com/johngirvin/featherweight/blob/master/featherweight.src.js
			var X=$('V').getContext("2d"), i = new Image(); i.src = '[{$g_config.www_dir}]images/logo16.png';
			window.onload=function()
			{
				var R = B = C = H = V.height = 450;
				X.r = X.fillRect;
				setInterval(function ()
				{
					R += B += ++C, (0 > B || B > H || Y > P && (Q > B || B > Q + 160)) &&
					               (P = B = 0, A = C = -1), P -= 6, 0 >
					                                                P &&
					                                                (P = 760, Q = R %
					                                                              180, A++), V.width = 760, X.fillStyle = "rgb(200,0,0)", X.r(
							P, 0, Y, Q), X.r(P, Q + 200, Y, H), X.drawImage(i, Y,
							B);
					X.font = "Bold 20px Arial";
					X.fillText(A, Y, Y)
				}, Y = 20)
			}
		</script>
</div>