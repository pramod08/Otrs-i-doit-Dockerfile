<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>
<head>
	<title>i-doit Update</title>
	<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
	<link rel="stylesheet" type="text/css" href="[{$g_config.www_dir}]updates/update.css">
	<script type="text/javascript" language="JavaScript" src="src/tools/js/prototype/prototype.js"></script>
	<script type="text/javascript" language="JavaScript"
	        src="src/tools/js/scriptaculous/src/scriptaculous.js?load=effects"></script>
</head>
<body>

<form name="install_form" enctype="multipart/form-data" method="POST">
	<div id="mainContainer" [{if $g_current_step == 1}]class="fadeIn"[{/if}]>
		<table id="mainTable" cellpadding="0" cellspacing="0">
			<tr>
				<td colspan="2" id="mainBanner">
					<img src="[{$g_config.www_dir}]updates/images/logo.png" alt="i-doit" />
				</td>
			</tr>
			<tr>
				<td id="mainMenu" nowrap="nowrap">

					<div class="mainMenuButton" id="mainStep1">
						<img src="images/icons/silk/page_white_gear.png" alt="" />[{counter}]. i-doit Update
					</div>

					<div class="mainMenuButton" id="mainStep2">
						<img src="images/icons/silk/page_white_gear.png" alt="" />[{counter}]. Available Updates
					</div>

					<div class="mainMenuButton" id="mainStep3">
						<img src="images/icons/silk/page_white_gear.png" alt="" />[{counter}]. Database(s)
					</div>

					<div class="mainMenuButton" id="mainStep4">
						<img src="images/icons/silk/page_white_gear.png" alt="" />[{counter}]. File-Update
					</div>

					<div class="mainMenuButton" id="mainStep5">
						<img src="images/icons/silk/page_white_gear.png" alt="" />[{counter}]. Overview (Log)
					</div>

					<div class="mainMenuButton" id="mainStep6">
						<img src="images/icons/silk/page_white_gear.png" alt="" />[{counter}]. Migration
					</div>

					<div class="mainMenuButton" id="mainStep7">
						<img src="images/icons/silk/page_white_gear.png" alt="" />[{counter}]. Attribute migration
					</div>

					<div class="mainMenuButton" id="mainStep8">
						<img src="images/icons/silk/page_white_gear.png" alt="" />[{counter}]. Completion
					</div>

				</td>
				<td id="mainContent">
					<div id="mainContentInner">
						[{include file=$g_steps.$g_current_step|default:"steps/1.tpl"}]
					</div>
				</td>
			</tr>
			<tr>
				<td id="mainCopyright" class="p10">
					copyright&nbsp;[{$smarty.now|date_format:"%Y"}]<br />
					synetics&nbsp;gmbh
				</td>
				<td id="mainDialog" nowrap="nowrap" class="p10">
					<img src="[{$g_config.www_dir}]setup/images/main_installing.gif" style="display:none;float:left;margin-top:3px;" id="loadingGif" />
					<input type="hidden" name="step" value="[{$g_current_step}]" />
					[{if $g_current_step gt 1 and $g_current_step lt count($g_steps)-1}]
						<input type="button" class="button" name="prev" id="btn_prev" value="&laquo; Previous"
						       onClick="this.form.step.value=[{$g_current_step-1}]; this.form.submit();" />
					[{/if}]
					[{if $g_current_step gt 0 and $g_current_step lt count($g_steps)-1}]
						[{if !$g_stop}]
							<input type="button" class="button" name="next" id="btn_next" value="Next &raquo;" />
							<input type="hidden" name="debug_log" value="[{$debug_log}]" />
							<input type="hidden" name="debug_log_www" value="[{$debug_log_www}]" />
							<input type="hidden" name="migration_log_file" value="[{$migration_log_file}]" />
						[{/if}]
					[{/if}]
				</td>
			</tr>
		</table>
	</div>
</form>

<script language="JavaScript" type="text/javascript">
	// Activate button for current step
	g_current_step = [{$g_current_step|default:'0'}];

	var e = $('mainStep[{$g_current_step}]');
	if (e) e.className += ' mainMenuButtonActive';

	if ($('btn_next')) {

		$('btn_next').focus();

		$('btn_next').on('click', function () {
			if (g_current_step == 4 || g_current_step == 5 || g_current_step == 6) {
				new Effect.Fade('content', {duration: 0.2});

				if ($('loadingTable')) $('loadingTable').show();
				if ($('btn_next'))
				{
					$('btn_next').disabled = "disabled";
					$('btn_next').addClassName('disabled');
				}
			}

			this.form.step.value = [{$g_current_step+1}];
			this.form.submit();
		});
	}

	if ($$('.red').length > 0 && $('content'))
	{
		var table = $('content').down('table');
		if (table)
		{
			$('content').scrollTop = table.getHeight();
		}
	}
</script>

</body>
</html>