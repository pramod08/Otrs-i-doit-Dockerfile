[{isys_group name="tom.popup.visualization"}]
<div id="visualization-popup">
	<h3 class="popup-header">
		<img class="fr mouse-pointer popup-closer" alt="x" src="[{$dir_images}]prototip/styles/default/close.png">

		<span>[{isys type="lang" ident="LC__MODULE__CMDB__VISUALIZATION__IT_SERVICE_SELECTION"}]</span>
	</h3>

	<table class="popup-content" cellspacing="0" cellpadding="0">
		<tr>
			<td class="vat" style="width:220px; background: #eee; border-right: 1px solid #aaa;">
				<h4 class="p5 gradient border-bottom text-shadow">[{isys type="lang" ident="LC__MODULE__CMDB__VISUALIZATION__IT_SERVICE_TYPES"}]</h4>

				<ul class="list-style-none m0" style="max-height: 290px; overflow-y: auto;">
					[{foreach $it_service_types as $it_service_type_id => $it_service_type_title}]
					<li>
						<label><input type="radio" name="C_VISUALIZATION_ITS_TYPE_SELECTION" value="[{$it_service_type_id}]" class="ml5 mr5" />[{$it_service_type_title}]</label>
					</li>
					[{/foreach}]
				</ul>
				<!--
				<div class="border-top mouse-pointer" style="bottom: 34px; position: absolute; background: #fff;">
					<img src="[{$visualization_assets}]cmdb-explorer-type-tree.png" style="float:left; width:110px;" />
					<img src="[{$visualization_assets}]cmdb-explorer-type-graph.png" style="float:left; width:110px;" class="opacity-30" />
				</div>
				-->
			</td>
			<td class="vat">
				<h4 class="p5 gradient border-bottom text-shadow">
					[{isys type="f_text" name="C_VISUALIZATION_ITS_SEARCH" p_strClass="input input-small fr vam" p_bInfoIconSpacer=0 p_strPlaceholder="LC__PROPERTY_SELECTOR__SEARCH_IN_PROPERTIES"}]
					[{isys type="lang" ident="LC__MODULE__CMDB__VISUALIZATION__IT_SERVICES"}]
				</h4>

				<div id="C_VISUALIZATION_ITS_CONTAINER">
					<!-- To be filled by AJAX -->
				</div>
			</td>
		</tr>
	</table>

	<div class="popup-footer">
		<button type="button" class="btn popup-closer" id="visualization-popup-cancel">
			<img src="[{$dir_images}]icons/silk/cross.png" class="mr5" />
			<span>[{isys type="lang" ident="LC__VISUALIZATION_PROFILES__CLOSE_POPUP"}]</span>
		</button>
	</div>
</div>

<script type="text/javascript">
	(function () {
		'use strict';

		var $popup = $('visualization-popup'),
			$its_search = $('C_VISUALIZATION_ITS_SEARCH'),
			$itservice_container = $('C_VISUALIZATION_ITS_CONTAINER'),
			its_searchword = '';

		$popup.select('.popup-closer').invoke('on', 'click', function () {
			popup_close();
		});

		$its_search.on('keyup', function () {
			var searchword = $its_search.getValue();

			if (searchword.blank()) {
				$itservice_container.select('li').invoke('show');
			}

			// Only trigger the filter-process, if we type in something new.
			if (its_searchword != searchword) {
				its_searchword = searchword;

				$itservice_container.select('li')
					.invoke('show')
					.filter(function($el) {
						return ! $el.down('span').innerHTML.toLowerCase().include(its_searchword.toLowerCase());
					})
					.invoke('hide');
			}
		});

		$popup.on('change', '[name="C_VISUALIZATION_ITS_TYPE_SELECTION"]', function (ev) {
			$itservice_container.update(new Element('div', {className:'m5'})
				.update(new Element('img', {src:'[{$dir_images}]ajax-loading.gif', className:'vam mr5'}))
				.insert(new Element('span', {className:'vam'}).update('[{isys type="lang" ident="LC__UNIVERSAL__LOADING"}]')));

			// Reloads the second chosen field.
			new Ajax.Request('[{$ajax_url}]', {
				parameters: {
					type: ev.findElement('input').getValue()
				},
				onComplete: function (response) {
					var json = response.responseJSON, i, $ul = new Element('ul', {className:'list-style-none m0'});

					if (!is_json_response(response, true)) {
						return;
					}

					if (json.success) {
						if (json.data.length === 0) {
							$itservice_container.update(new Element('p', {className: 'p5 m5 info'}).update('[{isys type="lang" ident="LC__MODULE__CMDB__VISUALIZATION__NO_IT_SERVICES"}]'));
						} else {
							for (i in json.data) {
								if (json.data.hasOwnProperty(i)) {
									$ul.insert(new Element('li', {className:'service-selection', 'data-object':i})
										.update(new Element('img', {src: '[{$dir_images}]icons/silk/link.png'}))
										.insert(new Element('span', {title:json.data[i]}).update(json.data[i])));
								}
							}

							$itservice_container.update($ul);
						}
					} else {
						idoit.Notify.error(json.message);
					}
				}
			});
		});

		$itservice_container.on('click', 'li[data-object]', function (ev) {
			var $el = ev.findElement('li');

			$('C_VISUALIZATION_OBJ_SELECTION__VIEW').setValue('[{isys type="lang" ident="LC__OBJTYPE__IT_SERVICE"}] >> ' + $el.down('span').innerHTML);
			$('C_VISUALIZATION_OBJ_SELECTION__HIDDEN').setValue($el.readAttribute('data-object'));

			popup_close();

			idoit.callbackManager.triggerCallback('visualization-init-explorer');
		});


		// We need this snippet to size the content area correctly, so we don't scroll the header and footer as well. Also the "undeletable" profiles get disabled.
		$popup.down('.popup-content').setStyle({height: ($popup.getHeight() - ($popup.down('.popup-header').getHeight() + $popup.down('.popup-footer').getHeight())) + 'px'});

		// Clicks the first type "all" on startup.
		$popup.down('[name="C_VISUALIZATION_ITS_TYPE_SELECTION"]').simulate('click');

		// Focus the search field.
		$its_search.focus();
	})();
</script>
<style>
	#visualization-popup {
		box-sizing: border-box;
		position: relative;
		height: 100%;
	}

	#visualization-popup li {
		border-bottom: 1px solid #ccc;
	}

	#visualization-popup li.service-selection {
		line-height: 22px;
		display:block;
		height: 24px;
		padding-left: 5px;
		cursor: pointer;
	}

	#visualization-popup li.service-selection:hover {
		background-color: #eee;
	}

	#visualization-popup label {
		display: block;
	}

	#C_VISUALIZATION_ITS_SEARCH {
		height: 21px;
		margin-top: -3px;
	}

	#C_VISUALIZATION_ITS_CONTAINER {
		max-height: 406px;
		overflow-y: auto;
	}

	#C_VISUALIZATION_ITS_CONTAINER li img {
		float:left;
		margin-top:4px;
	}

	#C_VISUALIZATION_ITS_CONTAINER li span {
		width: 535px;
		height: 24px;
		overflow: hidden;
		float: left;
		margin-left: 5px;
	}
</style>
[{/isys_group}]