<script language="JavaScript" type="text/javascript">
	function switch_action (action) {
		var $el = $(action);

		if ($el.visible()) {
			new Effect.SlideUp($el, {duration:0.3});
		} else {
			new Effect.SlideDown($el, {duration:0.2});
		}
	}
</script>

<div id="wf_container">
	[{foreach from=$g_workflow_actions item=l_action}]
	<fieldset class="overview">
		<legend>
			<span onclick="switch_action('action_[{$l_action->get_id()}]');">[{isys type="lang" ident=$l_action->get_title()}]<small class="black">, [{$l_action->get_datetime()}]</small></span>
		</legend>
		<div id="action_[{$l_action->get_id()}]">
			<div id="actiondata">

				[{include file=$l_action->get_template()}]

			</div>
		</div>
	</fieldset>
	[{/foreach}]
</div>