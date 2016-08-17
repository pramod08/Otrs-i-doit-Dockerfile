<style type="text/css">
	#relpool {
		list-style:  none;
		border-left: 4px solid #ddd;
	}

	#relpool li {
		background-color: #f6f6f6;
		border:           1px solid #ddd;
		border-left:      0;
		margin-top:       10px;
		padding:          15px;
		width:            550px;
		height:           15px;
		font-weight:      bold;
		text-overflow:    ellipsis;
		overflow:         hidden;
		white-space:      nowrap;
	}
</style>

<table class="contentTable">
	<tr>
		<td class="key">[{isys type="f_label" ident="LC__CMDB__LOGBOOK__TITLE" name="C__CMDB__CATS__RELPL__TITLE"}]</td>
		<td class="value">[{isys type="f_text" name="C__CMDB__CATS__RELPL__TITLE"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" ident="LC__CMDB__CATS__PARALLEL_RELATION__THRESHOLD" name="C__CMDB__CATS__RELPL__THRESHOLD"}]</td>
		<td class="value">[{isys type="f_text" name="C__CMDB__CATS__RELPL__THRESHOLD"}]</td>
	</tr>
</table>

[{if isys_glob_is_edit_mode()}]
	<hr />
	<br />
	<div class="toolbar m5">
		<a href="javascript:" onclick="[{$relation_browser}]">
			<img src="[{$dir_images}]icons/plus-green.gif" class="vam"
			     alt="+" /> [{isys type="lang" ident="LC__CATG__OBJECT__ADD"}]
		</a>
	</div>
	<br />
[{/if}]

<h3 class="gradient text-shadow p5 toolbar vam"
    style="margin-left:5px;margin-right:5px;border:1px solid #ccc;">[{isys type="lang" ident="LC__RELATION__PARALLEL_RELATIONS"}]
	:</h3>
<div class="m5 p10" style="margin-top:0;border:1px solid #ccc;border-top-width:0;">

	<input type="hidden" name="objectID__VIEW" />
	<input type="hidden" id="objectID" name="objectID__HIDDEN" />

	[{if $link_pool}]
		<ul id="relpool" class="toolbar m0">[{$link_pool}]</ul>
	[{else}]
		<span id="norelpool">Es wurden noch keine gleichgerichteten Beziehungen gebildet.</span>
	[{/if}]


</div>

<script type="text/javascript">
	rpool_callback = function (p_url) {
		if ($('objectID').value == '') return;

		if ($('norelpool')) {
			$('norelpool').up().insert(new Element('ul', {id: 'relpool'}).addClassName('toolbar').addClassName('m0'));
			$('norelpool').remove();
		}

		$(C__GET__NAVMODE).value = '[{$smarty.const.C__NAVMODE__SAVE}]';

		new Ajax.Submit(
				p_url,
				'relpool',
				'',
				{
					parameters: { relpool: 1 },
					method:     'post',
					history:    false,
					onComplete: relpool_observer
				});
	};

	remove_from_pool = function (e, id) {
		$(C__GET__NAVMODE).value = '[{$smarty.const.C__NAVMODE__SAVE}]';

		new Ajax.Request(
				'[{$ajax_link}]',
				{
					parameters: {
						'[{$smarty.const.C__GET__NAVMODE}]': '[{$smarty.const.C__NAVMODE__SAVE}]',
						remove:                              id,
						relpool:                             1
					},
					method:     'post',
					onComplete: function (r) {
						e.up().remove();
					}
				});
	};

	relpool_observer = function () {
		$$('#relpool li').each(function (el) {
			el.observe('mouseover', function (e) {
				this.down().show();
			});
			el.observe('mouseout', function (e) {
				this.down().hide();
			});
		});
	};

	[{if isys_glob_is_edit_mode()}]
	relpool_observer();
	[{/if}]
</script>