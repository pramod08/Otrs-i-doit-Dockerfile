<table class="contentTable">
	<tr>
		<td class="key">
			<span>Theme</span>
		</td>
		<td class="value">
			[{if is_array($g_themes)}]
				[{isys type="f_dialog" name="theme" p_arData=$g_themes p_strSelectedID=$g_current_theme p_strClass="input input-mini" p_bDbFieldNN=true}]
			[{else}]
				<span class="ml20">[{$g_current_theme}]</span>
			[{/if}]
		</td>
	</tr>
</table>

<input type="hidden" name="IDOIT_DELETE_TEMP" value="1" />