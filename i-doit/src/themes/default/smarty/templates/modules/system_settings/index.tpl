[{if !$disableTabs}]
<div class="whitebg">
	<h3 class="fr m10">
		<a href="?moduleID=[{$smarty.const.C__MODULE__SYSTEM}]&moduleSubID=[{$smarty.get.moduleSubID}]&pID=[{$smarty.get.pID}]&treeNode=[{$smarty.get.treeNode}]&expert">
			[{isys type="lang" ident="LC__SYSTEM_SETTINGS__EXPERT_SETTINGS"}]
		</a>
	</h3>

	<ul id="objectTabs" class="m0 gradient browser-tabs">
		<li><a href="#system" data-tab="#system">[{isys type="lang" ident="LC__SYSTEM_SETTINGS__SYSTEM_WIDE"}]</a></li>
		<li><a href="#tenant" data-tab="#tenant">[{$tenantTab|default:"Tenant"}]</a></li>
	</ul>
[{else}]
<div class="whitebg border-bottom">
[{/if}]
	<div id="system">
		[{foreach $definition as $headline => $definition_content}]
		<h3 class="p5 gradient border-top border-bottom">[{$headline}]</h3>

		<table class="whitebg contentTable p0 mb10">
			<colgroup>
				<col style="width:170px;"/>
				<col style="width:360px"/>
			</colgroup>
			[{foreach $definition_content as $key => $setting}]
			[{if !isset($setting.hidden)}]
			<tr>
				<td class="key vat">
					<label for="[{$key}]">[{isys type="lang" ident=$setting.title}]</label>
				</td>
				<td class="pl10 vat">
					[{if $setting.type == 'select'}]

						<select name="settings[[{$systemWideKey}]][[{$key}]]" id="[{$key}]" class="input input-mini">
							[{foreach from=$setting.options item="optionTitle" key="option"}]
								<option value="[{$option}]" [{if (isset($settings.$key) && $option == $settings.$key) || (!isset($settings.$key) && $option == $setting.default)}]selected="selected"[{/if}]>[{isys type="lang" ident=$optionTitle}]</option>
							[{/foreach}]
						</select>

					[{elseif $setting.type == 'textarea'}]

						<textarea rows="8" class="input" placeholder="[{$setting.placeholder}]" style="width:350px;" id="[{$key}]" name="settings[[{$systemWideKey}]][[{$key}]]">[{$settings.$key|default:$setting.default}]</textarea>

					[{else}]

						<input type="[{$setting.type}]" placeholder="[{$setting.placeholder}]" style="width:350px;" class="input input-small" name="settings[[{$systemWideKey}]][[{$key}]]" id="[{$key}]" value="[{$settings.$key|default:$setting.default}]"/>

					[{/if}]
				</td>
				<td class="pl5">
					[{if isset($setting.description)}]
					<img src="[{$dir_images}]icons/silk/information.png" class="vam" alt="*"/> [{isys type="lang" p_bHtmlEncode=false ident=$setting.description}]
					[{/if}]
				</td>
			</tr>
			[{/if}]
			[{/foreach}]
		</table>
		[{/foreach}]
	</div>

	<div id="tenant" class="mt5">
		[{foreach from=$tenant_definition item="definition_content" key="headline"}]
		<h3 class="p5 gradient border-bottom border-top">[{isys type="lang" ident=$headline}]</h3>

		<table class="contentTable p0 mb10">
			<colgroup>
				<col style="width:170px;"/>
				<col style="width:360px"/>
			</colgroup>
			[{foreach from=$definition_content item="setting" key="key"}]
			[{if !isset($setting.hidden)}]
			<tr>
				<td class="key vat">
					<label for="[{$key}]">[{isys type="lang" ident=$setting.title}]</label>
				</td>
				<td class="pl10 vat">

					[{if $setting.type == 'select'}]

						<select name="settings[[{$tenantWideKey}]][[{$key}]]" id="[{$key}]" class="input input-mini">
						[{foreach from=$setting.options item="optionTitle" key="option"}]
							<option value="[{$option}]" [{if (isset($tenant_settings.$key) && $tenant_settings.$key == $option) || (!isset($tenant_settings.$key) && $option == $setting.default)}]selected="selected"[{/if}]>[{isys type="lang" ident=$optionTitle}]</option>
						[{/foreach}]
						</select>

					[{elseif $setting.type == 'textarea'}]

						<textarea rows="8" class="input" placeholder="[{$setting.placeholder}]" style="width:350px;" id="[{$key}]" name="settings[[{$tenantWideKey}]][[{$key}]]">[{$tenant_settings.$key|default:$setting.default}]</textarea>

					[{else}]

						<input type="[{$setting.type}]" placeholder="[{$setting.placeholder}]" style="width:350px;" class="input input-small" name="settings[[{$tenantWideKey}]][[{$key}]]" id="[{$key}]" value="[{$tenant_settings.$key|default:$setting.default}]"/>

					[{/if}]
				</td>
				<td class="pl5">
					[{if isset($setting.description)}]
						<img src="[{$dir_images}]icons/silk/information.png" class="vam" alt="*"/> [{isys type="lang" p_bHtmlEncode=false ident=$setting.description}]
					[{/if}]
				</td>
			</tr>
			[{/if}]
			[{/foreach}]
		</table>
		[{/foreach}]
	</div>
</div>

<script type="text/javascript">
	(function(){
		'use strict';

		var $system = $('system');

		[{if !$disableTabs}]
		if ($('objectTabs')) {
			new Tabs('objectTabs', {
				wrapperClass: 'browser-tabs',
				contentClass: 'browser-tab-content',
				tabClass: 'text-shadow'
			});
		}

		if ($system) {
			$system.down('h3.border-top').addClassName('mt5');
		}
		[{else}]
		if ($system) {
			$system.down('h3.border-top').removeClassName('border-top');
		}
		[{/if}]
	})();
</script>