<h2 class="p5 gradient text-shadow border-bottom">[{isys type="lang" ident="LC_WORKFLOW_EMAIL__NOTIFICATION_SETTINGS"}]:</h2>

<div class="p5">
	<p>[{isys type="lang" ident="LC_WORKFLOW_EMAIL__NOTIFY1"}]</p>

	<div class="mt10 p5 border bg-white">
		<label class="display-block">
			<input type="checkbox" name="reg_value[]" [{if $smarty.const.C__WORKFLOW__MAIL__NOTIFICATION & $g_current_setting}]checked="checked"[{/if}] value="[{$smarty.const.C__WORKFLOW__MAIL__NOTIFICATION}]"/>
			[{isys type="lang" ident="LC__WORKFLOW__ACTION__TYPE__ASSIGN"}]
		</label>
		<label class="display-block">
			<input type="checkbox" name="reg_value[]" [{if $smarty.const.C__WORKFLOW__MAIL__ACCEPTED & $g_current_setting}]checked="checked"[{/if}] value="[{$smarty.const.C__WORKFLOW__MAIL__ACCEPTED}]"/> [{isys type="lang" ident="LC__WORKFLOW__ACTION__TYPE__ACCEPTED"}]
		</label>
		<label class="display-block">
			<input type="checkbox" name="reg_value[]" [{if $smarty.const.C__WORKFLOW__MAIL__OPEN & $g_current_setting}]checked="checked"[{/if}] value="[{$smarty.const.C__WORKFLOW__MAIL__OPEN}]"/>
			[{isys type="lang" ident="LC__WORKFLOW__ACTION__TYPE__OPEN"}]
		</label>
		<label class="display-block">
			<input type="checkbox" name="reg_value[]" [{if $smarty.const.C__WORKFLOW__MAIL__COMPLETED & $g_current_setting}]checked="checked"[{/if}] value="[{$smarty.const.C__WORKFLOW__MAIL__COMPLETED}]"/> [{isys type="lang" ident="LC__WORKFLOW__ACTION__TYPE__COMPLETE"}] /
			[{isys type="lang" ident="LC__WORKFLOW__ACTION__TYPE__CANCEL"}]
		</label>
	</div>
</div>