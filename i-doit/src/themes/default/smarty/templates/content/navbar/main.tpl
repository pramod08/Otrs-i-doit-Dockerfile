[{strip}]
<div id="navBar">

	[{if $navbar_buttons}]
		[{foreach $navbar_buttons as $button}]
			[{$button}]
		[{/foreach}]
	[{/if}]

	<a id="navbar_item_8" title="" class="navbar_item" href="?[{$current_link}]">
		<img src="[{$dir_images}]icons/silk/link.png" alt="-" />
		<span class="navBarLink">
			&nbsp;[{isys type="lang" ident="LC__UNIVERSAL__LINK_TO_THIS_PAGE"}]&nbsp;
		</span>
	</a>

	[{if ($list_display)}]
		<div id="cSpanRecFilter">
			[{include file="content/top/list_paging.tpl"}]

			[{if ($ajax_calls)}]
				[{assign var="onchange" value="document.isys_form.navMode.value='';remove_action_parameter('navPageStart');reset_listselection();form_submit();"}]
			[{else}]
				[{assign var="onchange" value="document.isys_form.navMode.value='';remove_action_parameter('navPageStart');reset_listselection();$('isys_form').submit()"}]
			[{/if}]

			[{isys type="f_dialog"
					p_strClass="input input-mini"
					id="cRecStatus"
					name="cRecStatus"
					p_bEditMode="1"
					p_strInfoIconHelp="LC__CMDB__LIST_FILTER"
					p_strStyle="min-width:100px;"
					p_onClick=""
					p_bDbFieldNN="1"
					p_onChange=$onchange}]
		</div>
	[{/if}]

	[{* This Submit-Button is used for enabling saving by pressing enter in input elements *}]
	<input type="submit" name="submit_isys_form" id="submit_isys_form" value="" style="visibility: hidden; width: 0; height: 0; position: absolute;"/>

</div>
[{/strip}]

<script type="text/javascript">
	$('isys_form').stopObserving('submit');
    $('isys_form').observe("submit", function(evt) {
        if ($('navbar_item_C__NAVMODE__SAVE')) {
            $('navbar_item_C__NAVMODE__SAVE').simulate('click');

            Event.stop(evt);
        }
    });
</script>