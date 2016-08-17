<p class="mb5">[{isys type="lang" ident="LC__MODULE__CHECK_MK__TAG_GUI__DYNAMIC_DESCRIPTION" p_bHtmlEncode=false}]</p>
<p class="mb5">[{isys type="lang" ident="LC__MODULE__CHECK_MK__TAG_GUI__DYNAMIC_EXAMPLE_1" p_bHtmlEncode=false}]</p>
<p>[{isys type="lang" ident="LC__MODULE__CHECK_MK__TAG_GUI__DYNAMIC_EXAMPLE_2" p_bHtmlEncode=false}]</p>

<div class="mt15 border">
	<table style="width:100%;" cellspacing="0" id="dynamic-tags-table">
		<thead class="gradient">
		<tr>
			<th>[{isys type="lang" ident="LC__MODULE__CHECK_MK__TAG_GUI__CONDITION"}]</th>
			<th>[{isys type="lang" ident="LC__MODULE__CHECK_MK__TAG_GUI__PARAMETER"}]</th>
			<th>[{isys type="lang" ident="LC__MODULE__CHECK_MK__TAG_GUI__TAGS"}]</th>
			<th>[{isys type="lang" ident="LC__MODULE__CHECK_MK__TAG_GUI__ACTION"}]</th>
		</tr>
		</thead>
		<tbody>
		[{foreach $dynamic_tags as $row}]
		<tr data-cnt="[{$row.cnt}]">
			<td>[{$row.condition}]</td>
			<td>[{$row.parameter}]</td>
			<td>[{$row.tags}]</td>
			<td><button type="button" class="btn btn-small remove"><img src="[{$dir_images}]icons/silk/cross.png" /></button></td>
		</tr>
		[{/foreach}]
		</tbody>
	</table>

	[{isys name="max_counter" type="f_text" p_bInvisible=true p_bInfoIconSpacer=0}]
</div>

<button type="button" id="new-dynamic-tag" class="btn mt15"><img src="[{$dir_images}]icons/silk/add.png" /> <span>[{isys type="lang" ident="LC__MODULE__CHECK_MK__TAGS__ADD_DYNAMIC_TAG"}]</span></button>
<script type="text/javascript">
	(function () {
		"use strict";

		var count = $F('max_counter'),
			conditions = '[{$dynamic_tag_condition_dialog|strip|escape:"quotes"}]',
			remove_button = new Element('button', {type:'button', className:'btn btn-small remove'})
				.update(new Element('img', {src:'[{$dir_images}]icons/silk/cross.png'})).outerHTML;

		$('dynamic-tags-table').on('click', 'button.remove', function (ev) {
			ev.findElement().up('tr').remove();
		});

		$('dynamic-tags-table').on('change', 'select.condition-select', function (ev) {
			var select = ev.findElement(),
				row = select.up('tr'),
				count = row.readAttribute('data-cnt');

			new Ajax.Request('[{$ajax_url}]&func=load_dynamic_parameter', {
				method: 'post',
				parameters:{
					count:count,
					condition:select.getValue()
				},
				onSuccess: function (response) {
					var json = response.responseJSON;

					if (json.success) {
						row.down('td', 1).update(json.data.parameter);
					} else {
						alert(json.message);
					}
				}.bind(this)
			});
		});

		$('new-dynamic-tag').on('click', function () {
			++ count;

			new Ajax.Request('[{$ajax_url}]&func=load_new_dynamic_tag_row', {
				method: 'post',
				parameters:{
					condition:1,
					count:count
				},
				onSuccess: function (response) {
					var json = response.responseJSON,
						condition = conditions.replace(/%s/g, json.data.count),
						tr = new Element('tr', {'data-cnt':json.data.count});

					$('max_counter').setValue(count);

					if (json.success) {
						tr.update(new Element('td').update(condition))
							.insert(new Element('td').update(json.data.parameter))
							.insert(new Element('td').update(json.data.tags))
							.insert(new Element('td').update(remove_button));

						$('dynamic-tags-table').down('tbody').insert(tr);
					} else {
						alert(json.message);
					}
				}
			});
		});
	}());
</script>