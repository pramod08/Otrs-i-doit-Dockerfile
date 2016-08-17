<script type="text/javascript">
	var ajax_start_count = 0,
		ajax_finished_count = 0;

	window.fix_them_all = function () {
        ajax_start_count = ajax_finished_count = 0;

        $('loading').show();
        $('ajax_return').update();

		window.flush_cache(true, true);
		window.flush_database(true, true);
	};

	window.flush_cache = function (type, append) {
        $('loading').show();
		// If we get "true" as parameter, we flush every cache.
		if (type === true) {
			if (append !== true) {
				$('ajax_return').update();
			}
			window.flush_cache('IDOIT_DELETE_TEMPLATES_C', true);
			window.flush_database('cache_system', true);
			window.flush_database('cache_auth', true);
			window.flush_validation_cache(true);
		} else {
            ajax_start_count ++;
			new Ajax.Request('?ajax=1&' + type,
				{
					method:"post",
					onSuccess:function (response) {
						if (append === true) {
							$('ajax_return').insert(response.responseText);
						} else {
							$('ajax_return').update(response.responseText);
						}
						window.highlight_response();
					}.bind(this)
				});
		}
	};

	window.flush_database = function (type, append) {
        $('loading').show();
		// If we get "true" as parameter, we start all actions.
		if (type === true) {
			if (append !== true) {
				$('ajax_return').update();
			}

			window.flush_database('db_optimize', true);
			window.flush_database('db_defrag', true);
			window.flush_database('db_location', true);
			window.flush_database('db_properties', true);
            window.flush_database('db_cleanup_cat_assignments', true);
            window.flush_database('db_renew_relation_titles', true);
            window.flush_database('db_cleanup_duplicate_sv_entries', true);
            window.flush_database('db_cleanup_unassigned_relations', true);
            window.flush_database('db_refill_empty_sysids', true);
		} else {
            ajax_start_count ++;
			new Ajax.Request('?ajax=1&moduleID=[{$smarty.const.C__MODULE__SYSTEM}]&what=cache&do=' + type,
				{
					method:"post",
					onSuccess:function (response) {
						if (append === true) {
							$('ajax_return').insert(response.responseText);
						} else {
							$('ajax_return').update(response.responseText);
						}
						window.highlight_response();
					}.bind(this)
				});
		}
	};

	window.flush_validation_cache = function (append) {
		new Ajax.Request('?call=validate_field&ajax=1&func=reset_validation_cache', {
			method: 'post',
			onSuccess: function (transport) {
				var json = transport.responseJSON,
					$msg = new Element('p');

				if (json.success) {
					$msg.update('[{isys type="lang" ident="LC__SETTINGS__CMDB__VALIDATION__CACHE_REFRESH"}]... ' + json.data + '!');
				} else {
					$msg.addClassName('red').update('[{isys type="lang" ident="LC__SETTINGS__CMDB__VALIDATION__CACHE_REFRESH"}]... ' + json.message + '!');
				}

				if (append === true) {
					$('ajax_return').insert($msg);
				} else {
					$('ajax_return').update($msg);
				}
			}.bind(this)
		});
	};

    window.flush_objects = function (type, message) {
        new Ajax.Request('?ajax=1&moduleID=[{$smarty.const.C__MODULE__SYSTEM}]&what=cache&do=db_list_objects&param=' + type,
            {
                method:"post",
                onSuccess:function (response) {
                    $('ajax_return').update(response.responseText);
                    window.highlight_response();
                }
            });

        if (confirm(message)) {
            $('loading').show();
            // Remove objects with status as defined in "type".
            new Ajax.Request('?ajax=1&moduleID=[{$smarty.const.C__MODULE__SYSTEM}]&what=cache&do=db_cleanup_objects&param=' + type,
                    {
                        method:"post",
                        onSuccess:function (response) {
                            $('ajax_return').update(response.responseText);
                            window.highlight_response();
                            $('loading').hide();
                        }
                    });
        }
    };

    window.flush_categories = function (type, message) {
        if (confirm(message)) {
            $('loading').show();
            // Remove objects with status as defined in "type".
            new Ajax.Request('?ajax=1&moduleID=[{$smarty.const.C__MODULE__SYSTEM}]&what=cache&do=db_cleanup_categories&param=' + type, {
				method:"post",
				onSuccess:function (response) {
					$('ajax_return').update(response.responseText);
					window.highlight_response();
					$('loading').hide();
				}
			});
        }
    };

	window.flush_other = function (type, message) {
		if (confirm(message)) {
			$('loading').show();
			// Remove objects with status as defined in "type".
			new Ajax.Request('?ajax=1&moduleID=[{$smarty.const.C__MODULE__SYSTEM}]&what=cache&do=cleanup_other&param=' + type, {
				method:"post",
				onSuccess:function (response) {
					$('ajax_return').update(response.responseText);
					window.highlight_response();
					$('loading').hide();
				}
			});
		}
	};

	window.export_database = function () {
		new Ajax.Request('?ajax=1&moduleID=[{$smarty.const.C__MODULE__SYSTEM}]&what=cache&do=export', {
			method:"post",
			parameters:{
				mysqldump:$('mysqldump').getValue(),
				system:$('system').getValue(),
				mandator:$('mandator').getValue()
			},
			onSuccess:function (response) {
				$('ajax_return').update(response.responseText);
				window.highlight_response();
			}
		});
	};

	window.highlight_response = function () {
        ajax_finished_count ++;

		if (ajax_start_count == ajax_finished_count) {
            $('loading').hide();
        }

		new Effect.Highlight('ajax_return', {
			duration:0.5,
			startcolor:'#ffff99',
			endcolor:'#ffffff',
			restorecolor:'#ffffff'
		});
	};
</script>

<style type="text/css">
	fieldset {
		border: 1px solid #ccc;
	}

	fieldset legend {
		padding: 0 5px;
	}

	#sys_cache div#cache.box,
	#sys_cache div#database.box,
	#sys_cache div#objects.box {
		width: 320px;
		margin-right: 10px;
		border-top: none;
	}

	#sys_cache div#objects.box {
		margin-right: 0;
	}

	#sys_cache div.box h3 {
		border-top: 1px solid #b7b7b7;
		border-bottom: 1px solid #b7b7b7;
	}

	#sys_cache #the-one {
		width: 655px;
		background-color: #E64117;
		text-shadow: 0 1px 0 #000;
		color: #fff;
	}

	#sys_cache #loading img,
	#sys_cache #loading span {
		vertical-align: middle;
	}

	#sys_cache #ajax_return ul {
		list-style: none;
		margin: 0;
	}

	#sys_cache #ajax_return ul i {
		color: #888;
	}
</style>

<h2 class="p5 gradient border-bottom">[{isys type="lang" ident="LC__SYSTEM__CACHE"}]</h2>

<div id="sys_cache" class="mt10 p10">

	[{isys id="the-one" type="f_button" p_bDisabled="0" p_onClick="window.fix_them_all();" p_strValue="LC__SETTINGS__SYSTEM__THE_ONE_BUTTON_TO_FIX_THEM_ALL" p_strClass="mb15 btn-block bold"}]

	<div id="cache" class="box fl">
		<h3 class="gradient p5">[{isys type="lang" ident="LC__UNIVERSAL__CACHE"}]</h3>

		<div class="m10">
			[{foreach $cache_buttons as $name => $button}]
				[{isys type="f_button" p_bDisabled="0" p_onClick=$button.onclick p_strValue=$name p_strClass="btn-block mb5 `$button.css`" p_strStyle=$button.style}]
			[{/foreach}]
		</div>
	</div>

	<div id="database" class="box fl">
		<h3 class="gradient p5">[{isys type="lang" ident="LC__UNIVERSAL__DATABASE"}]</h3>

		<div class="m10">
			[{foreach $database_buttons as $name => $button}]
				[{isys type="f_button" p_bDisabled="0" p_onClick=$button.onclick p_strValue=$name p_strClass="btn-block mb5 `$button.css`" p_strStyle=$button.style}]
			[{/foreach}]
		</div>

		<h3 class="gradient p5 mt15">Export</h3>
		<div class="m10">
			<label for="mysqldump">"mysqldump" [{isys type="lang" ident="LC__UNIVERSAL__PATH"}]</label>
			<input type="text" id="mysqldump" class="input input-block mt5" name="mysqldump" size="40" value="[{$mysqldump}]"/>

			<table width="100%" class="mt15 mb15">
				<tr>
					<td>
						[{isys type="lang" ident="LC__UNIVERSAL__DATABASE"}]
					</td>
					<td>
						<label><input type="checkbox" checked="checked" id="system" name="system" value="1"/> System</label><br/>
						<label><input type="checkbox" checked="checked" id="mandator" name="mandator" value="1"/> [{isys type="lang" ident="LC__LOGIN__MANDATOR"}]</label>
					</td>
				</tr>
			</table>

            [{isys type="f_button" p_bDisabled="0" p_onClick="window.export_database();" p_strValue="Export" p_strStyle="cursor:pointer;"}]
		</div>
	</div>

    <div id="objects" class="box fl">
        <h3 class="gradient p5">[{isys type="lang" ident="LC__CMDB__CATG__OBJECT"}]</h3>
        <div class="m10">
            [{foreach $object_buttons as $name => $button}]
            [{isys type="f_button" p_bDisabled="0" p_onClick=$button.onclick p_strValue=$name p_strClass="mb5 `$button.css`" p_strStyle=$button.style}]
            [{if $button.query}]<a href="#" class="btn fr mb5" data-query="[{$button.query}]"><img src="[{$dir_images}]icons/eye.png" alt="[{isys type="lang" ident="LC__UNIVERSAL__PREVIEW"}]" title="[{isys type="lang" ident="LC__UNIVERSAL__PREVIEW"}]" /></a>[{/if}]
            [{/foreach}]
	        <br class="cb" />
        </div>

        <h3 class="gradient p5">[{isys type="lang" ident="LC_UNIVERSAL__CATEGORIES"}]</h3>
        <div class="m10">
            [{foreach $category_buttons as $name => $button}]
            [{isys type="f_button" p_bDisabled="0" p_onClick=$button.onclick p_strValue=$name p_strClass="btn-block mb5 `$button.css`" p_strStyle=$button.style}]
            [{/foreach}]
        </div>

        <h3 class="gradient p5">[{isys type="lang" ident="LC__SYSTEM__CACHE__OTHERS"}]</h3>
        <div class="m10">
            [{foreach $other_buttons as $name => $button}]
            [{isys type="f_button" p_bDisabled="0" p_onClick=$button.onclick p_strValue=$name p_strClass="btn-block mb5 `$button.css`" p_strStyle=$button.style}]
            [{/foreach}]
        </div>
    </div>
	<div class="cb mb10"></div>
</div>

<fieldset class="overview">
	<legend><span>[{isys type="lang" ident="LC__SETTINGS__SYSTEM__SYS_MSG"}]</span></legend>
	<div class="p10 bg-white">
		<div id="loading" style="display: none;">
			<img src="[{$dir_images}]ajax-loading.gif" alt="[{isys type="lang" ident="LC__UNIVERSAL__LOADING"}]" />
			<span>[{isys type="lang" ident="LC__UNIVERSAL__LOADING"}]</span>
		</div>
		<div id="ajax_return"></div>
	</div>
</fieldset>

<input type="hidden" id="query" name="query" />

<script type="text/javascript">
	(function(){
		'use strict';

		[{if file_exists($report_sql_path)}]
		[{include file=$report_sql_path}]

		$('objects').select('a[data-query]').invoke('on', 'click', function (ev) {
			var $button = ev.findElement('a');
			$('query').setValue($button.readAttribute('data-query'));
			get_popup('report', '', 800, 508, {func:'report_preview_sql'});
		});
		[{else}]
		$('objects').select('a[data-query]').invoke('addClassName', 'disabled');
		[{/if}]
	})();
</script>