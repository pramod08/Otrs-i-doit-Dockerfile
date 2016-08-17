[{if count($connections) > 0}]
	[{foreach from=$connections item="con"}]

		[{if count($con) > 1}]
			<table class="wiring">
				<tbody>
					<tr>

					[{foreach $con as $c}]

						[{if $c.object}]
							[{include file="./wiring-object-connections-recursion.tpl"}]

							[{\idoit\Module\Wiring\View\Ajax\Object::smartyRecurseMultipleConnections($c)}]
						[{/if}]

					[{/foreach}]

					</tr>
				</tbody>
			</table>
		[{/if}]

	[{/foreach}]
[{else}]
	<p class="warning p10">[{isys type="lang" ident="LC__MODULE__WIRING__EMPTY_RESULT"}]</p>
[{/if}]