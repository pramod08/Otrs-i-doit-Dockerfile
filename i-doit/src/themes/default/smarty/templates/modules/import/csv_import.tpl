[{isys type="f_text" name="csv_filename" p_bInvisible=true p_bInfoIconSpacer=0}]

<div id="import-csv" class="border-bottom">
	<h2 class="p10">CSV Import &raquo; [{$csv_filename}]</h2>

	<h3 class="gradient border-top border-bottom p5">[{isys type="lang" ident="LC__MASS_CHANGE__OPTIONS"}]</h3>

	<div id="import_button_container">
		[{isys type="f_label" name="profile_sbox" ident="LC__MODULE__IMPORT__CSV__PROFILES"}][{isys type="f_dialog" name="profile_sbox" p_strClass="input-mini"}]

		<button type="button" class="btn btn-small" id="import-csv-load-profile">
			<img src="[{$dir_images}]icons/silk/text_horizontalrule.png" class="mr5">
			<span>[{isys type="lang" ident="LC__MODULE__IMPORT__CSV__PROFILE_LOAD"}]</span>
		</button>

		<button type="button" class="btn btn-small" id="import-csv-delete-profile">
			<img src="[{$dir_images}]icons/silk/delete.png" class="mr5">
			<span>[{isys type="lang" ident="LC__MODULE__IMPORT__CSV__PROFILE_DELETE"}]</span>
		</button>
	</div>

	<table id="import-csv-options" class="contentTable mt5 mb5">
		<tr>
			<td class="key">
				[{isys type="f_label" name="object_type" ident="LC__UNIVERSAL__GLOBAL_OBJECTTYPE"}]
				<img src="[{$dir_images}]icons/infoicon/help.png" alt="help" title="[{isys type='lang' ident='LC__UNIVERSAL__GLOBAL_OBJECTTYPE_INFO'}]" class="vam mouse-help" />
			</td>
			<td class="value">
				[{isys type="f_dialog" name="object_type" p_strClass="input-small" chosen=true}]
			</td>
		</tr>
		<tr>
			<td class="key">
				[{isys type="lang" name="csv_separator" ident="LC__UNIVERSAL__SEPARATOR"}]
				<img title="[{isys type="lang" ident="LC__UNIVERSAL__CSV_SEPARATOR_INFO"}]" alt="help" src="[{$dir_images}]icons/infoicon/help.png" class="vam mouse-help"/>
			</td>
			<td class="value">
				[{isys type="f_text" name="csv_separator" p_strValue=";" p_strStyle="width:30px;"}] <code id="csv-preview" class="ml10 box p5">"Wert";"Wert2";"..."</code>
			</td>
		</tr>
		<tr>
			<td class="key">
				[{isys type="f_label" name="csv_header" ident="LC__UNIVERSAL__HEADER"}]
				<img title="[{isys type='lang' ident='LC__UNIVERSAL__CSV_HEADER_INFO'}]" alt="help" src="[{$dir_images}]icons/infoicon/help.png" class="vam mouse-help" />
			</td>
			<td class="value">
				[{isys type="checkbox" name="csv_header" p_bChecked=true p_strClass="vam"}]
			</td>
		</tr>
		<tr>
			<td></td>
			<td class="pl20 pt10">
				[{isys type="f_button" name="csv_import_process_options" icon="`$dir_images`icons/silk/arrow_down.png" p_strValue="LC__MODULE__IMPORT__CSV__PROCESS_OPTIONS"}]
				<img id="import-csv-options-notice" src="[{$dir_images}]icons/silk/error.png" class="ml10 hide vam" title="[{isys type="lang" ident="LC__MODULE__IMPORT__CSV__OPTION_CHANGE_NOTICE"}]" />
			</td>
		</tr>
		<tr>
			<td colspan="2">
				<hr class="mt5 mb5" />
			</td>
		</tr>
		<tr>
			<td class="key">
				<span>[{isys type='lang' ident='LC__UNIVERSAL__SINGLEVALUE_OBJECTS'}]</span>
				<img title="[{isys type='lang' ident='LC__UNIVERSAL__SINGLEVALUE_OBJECTS_INFO'}]" alt="help" src="[{$dir_images}]icons/infoicon/help.png" class="vam mouse-help" />
			</td>
			<td class="value pl20">
				<label class="mr10"><input type="radio" name="singlevalue_overwrite_empty_values" id="singlevalue_overwrite_yes" value="1" checked="checked" /> [{isys type='lang' ident='LC__UNIVERSAL__YES'}]</label>
				<label><input type="radio" name="singlevalue_overwrite_empty_values" id="singlevalue_overwrite_no" value="0" /> [{isys type='lang' ident='LC__UNIVERSAL__NO'}]</label>
			</td>
		</tr>
		<tr>
			<td class="key">
				<span>[{isys type='lang' ident='LC__UNIVERSAL__MULTIVALUE_OBJECTS'}]</span>
				<img title="[{isys type='lang' ident='LC__UNIVERSAL__MULTIVALUE_OBJECTS_INFO'}]" alt="help" src="[{$dir_images}]icons/infoicon/help.png" class="vam mouse-help" />
			</td>
			<td class="value pl20">
				<label class="mr10"><input type="radio" name="multivalue" id="multivalue_column" value="column" checked="checked" /> [{isys type='lang' ident='LC__UNIVERSAL__COLUMN'}]</label>
				<label class="mr10"><input type="radio" name="multivalue" id="multivalue_row" value="row" /> [{isys type='lang' ident='LC__UNIVERSAL__ROW'}]</label>
				<label><input type="radio" name="multivalue" id="multivalue_comma" value="comma" /> [{isys type='lang' ident='LC__UNIVERSAL__COMMA_SEPARATED'}]</label>
			</td>
		</tr>
		<tr>
			<td class="key vat">
				<span>[{isys type="lang" ident="LC__MASS_CHANGE__HANDLING_MULTI-VALUED_CATEGORIES"}]</span>
			</td>
			<td class="value pl20">
				<label><input type="radio" class="mr5" value="[{$multivalue_modes.untouched}]" name="multivalue_mode" checked="checked" />[{isys type='lang' ident='LC__CSV__KEEP_CATEGORY_ENTRIES_UNTOUCHED'}]</label><br />
				<label><input type="radio" class="mr5" value="[{$multivalue_modes.add}]" name="multivalue_mode" />[{isys type='lang' ident='LC__CSV__ADD_CATEGORY_ENTRIES'}]</label><br />
				<label><input type="radio" class="mr5" value="[{$multivalue_modes.overwrite}]" name="multivalue_mode" />[{isys type='lang' ident='LC__CSV__DELETE_BEFORE_ADD_CATEGORY_ENTRIES'}]</label>
			</td>
		</tr>
	</table>

	<div id="import-csv-assignment">
		<div id="import-csv-assignment-modal" class="opacity-50"></div>

		<h3 class="gradient p5 text-shadow border-top border-bottom">[{isys type='lang' ident='LC__UNIVERSAL__ASSIGNMENT'}]</h3>

		<div class="p5 mt5 mb5">
		    <div class="mt10">
		        <button type="button" id="import-csv-add-identificator" class="btn mb5">
		            <img src="[{$dir_images}]icons/silk/add.png" class="mr5" />
		            <span>[{isys type="lang" ident="LC__MODULE__IMPORT__CSV__PROFILE_ADD_IDENTIFICATIONFIELD"}]</span>
		        </button>

		        <img title="[{isys type='lang' ident='LC__MODULE__IMPORT__CSV__IDENTIFICATION_DESCRIPTION'}]" alt="help" src="[{$dir_images}]icons/infoicon/help.png" class="mouse-help" />

		        <div id="identificators_hidden" class="hide">
		            <div class="mt5">
			            <!-- Remove the ID's, because this HTML will be cloned n-times -->
			            [{isys type="f_dialog" id="" name="csv_ident[]" p_strClass="input-small mr5" p_bInfoIconSpacer=0 p_bDbFieldNN=true}]
			            [{isys type="f_dialog" id="" name="identificator[]" p_strClass="input-small mr5" p_bInfoIconSpacer=0 p_bDbFieldNN=true}]

		                <button type="button" class="btn">
		                    <img src="[{$dir_images}]icons/silk/cross.png" class="mr5" />
		                    <span>[{isys type="lang" ident="LC__UNIVERSAL__REMOVE"}]</span>
		                </button>
		            </div>
		        </div>

		        <div id="identificators" style="display:none;">
		            <ul class="list-style-none m0">
		                <li style="display: inline; margin-right: 193px;">[{isys type="lang" ident="LC__UNIVERSAL__CSV_HEADER"}]</li>
		                <li style="display: inline;">[{isys type="lang" ident="LC__MODULE__IMPORT__CSV__CATEGORY_ATTRIBUTES"}]</li>
		            </ul>
		        </div>
		    </div>

		    <div class="mt10 mb10">
		        <table cellspacing="0" cellpadding="0" class="listing" id="csv_assignment_table">
			        <colgroup>
				        <col width="25%" />
				        <col width="25%" />
				        <col width="50%" />
			        </colgroup>
		            <thead>
		            <tr>
		                <th>[{isys type='lang' ident='LC__UNIVERSAL__CSV_HEADER'}]</th>
		                <th class="grey">[{isys type='lang' ident='LC__UNIVERSAL__FIRST_LINE'}]</th>
		                <th>[{isys type='lang' ident='LC__UNIVERSAL__ASSIGNMENT'}]</th>
		            </tr>
		            </thead>
		            <tbody>

		            </tbody>
		        </table>
		    </div>

			<div class="mb10">
				<div class="fr right">
					<label class="display-block">[{isys type="lang" ident="LC__MODULE__IMPORT__CSV__LOGGING__SIMPLE"}]<input type="radio" name="csv-log-detail" class="ml5" value="simple" checked="checked" /></label>
					<label class="display-block">[{isys type="lang" ident="LC__MODULE__IMPORT__CSV__LOGGING__NORMAL"}]<input type="radio" name="csv-log-detail" class="ml5" value="normal" /></label>
					<label class="display-block">[{isys type="lang" ident="LC__MODULE__IMPORT__CSV__LOGGING__ALL"}]<input type="radio" name="csv-log-detail" class="ml5" value="all" /></label>

					<button class="btn btn-large bold mt20" type="button" id="import-start-button">
						<img src="[{$dir_images}]icons/silk/database_copy.png" class="mr5" /><span>[{isys type='lang' ident='LC__UNIVERSAL__IMPORT'}]</span>
					</button>
				</div>

		        <div id="profiles_container" class="mb10">
		            <h3 class="mb5">[{isys type="lang" ident="LC__MODULE__IMPORT__CSV__PROFILE_SAVE_AS"}]</h3>

		            <input type="text" id="csv-profile-title" class="input input-small" placeholder="[{isys type="lang" ident="LC__MODULE__IMPORT__CSV__PROFILE_TITLE"}]" />
		            <button type="button" id="import-csv-save-profile" class="btn">
		                <img src="[{$dir_images}]icons/silk/page_save.png" class="mr5">
		                <span>[{isys type="lang" ident="LC__MODULE__IMPORT__CSV__PROFILE_SAVE"}]</span>
		            </button>
		        </div>

		        <br class="cb" />
			</div>

			<div id="import-result-container" style="width:auto;"></div>
		</div>
	</div>
</div>

<style type="text/css">
	#import-csv-assignment {
		position: relative;
	}

	#import-csv-assignment-modal {
		position: absolute;
		width: 100%;
		height: 100%;
		background: #fff;
	}

	#import_button_container {
		position: absolute;
		top: 39px;
		right: 5px;
	}

	#import_button_container select {
		height: 20px;
		min-height: 20px;
	}

	#csv_assignment_table .attribute-box {
		box-sizing: border-box;
		width: 500px;
		min-height: 24px;
		padding: 2px 5px;
		vertical-align: middle;
		border: 1px dashed #ccc;
		background: #fff;
	}

	#import-csv .chosen-single {
		box-sizing: border-box;
		height: 24px;
	}

	#import-csv tr.active {
		background: #eee;
	}

	#import-csv .object-type-assignment label,
	#import-csv .special-assignment label {
		width: 280px;
		display: block;
		float: left;
	}

	/* This is necessary because of other stylings :/ */
	#object_type_chosen {
		width: 280px;
	}
</style>

<script>
	(function () {
		var $input_submit_form = $('submit_isys_form'),
			$select_profiles = $('profile_sbox'),
			$button_profile_loader = $('import-csv-load-profile');

		if ($input_submit_form) {
			$input_submit_form.disable();
		}

		$select_profiles.on('profiles:preselect', function (ev) {
			if (!! ev.memo.selectLatest) {
				$select_profiles.setValue($select_profiles.down('option:last').readAttribute('value'));
			} else if (ev.memo.preselection > 0) {
				$select_profiles.setValue(ev.memo.preselection);
			}

			if (!! ev.memo.simulateClick) {
				$button_profile_loader.simulate('click');
			}
		});
	})();

	[{include file='modules/import/csv_mapping.js'}]
	[{include file='modules/import/csv_import.js'}]
</script>