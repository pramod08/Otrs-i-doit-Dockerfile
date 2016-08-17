<script type="text/javascript">
	var LC_DELETE_CONFIRM		= "Do you really want to delete the selected tenant(s)?\n\n"+
									"Note that you lose all your data inside this or these tenant(s) !!" +
									"\n\nWithout a separate backup is it not possible to recover your data! "+
									"If you just want to disable a tenant, use deactivate.";


	var LC_PW_UNEQUAL 			= 'Passwords are unequal.';

	var LC_NO_MANDATOR_SELECTED	= 'No tenant selected!';

	/**
	  * Check database name for correctness
	  */
	function CheckDatabaseName(el, message) {
		var strVal = el.value;

		if (strVal.search(/\W+/) > 0 || strVal == '') {
			alert(message);
			el.className = 'red redbg';
			el.focus();
			el.value=el.value;
		} else {
			el.className = 'green greenbg';
		}
	}

    /**
      * Check Auto-Increment start value
      */
    function checkAutoInc(el, message) {
        var strVal = el.value;

		if (strVal <= 0 || strVal == '') {
			alert(message);
			el.className = 'red redbg';
			el.focus();
			el.value=el.value;
		} else {
			el.className = 'green greenbg';
		}
    }

	/**
	  * Plausibility check
	  */
	function formCheck() {

		if ($('mandator_password').value != $('mandator_password2').value) {
			alert(LC_PW_UNEQUAL);
			return false;
		}

		return true;
	}

	/**
	  * Show editable mandator form
	  */
	function edit_mandator() {

		var _id = 0;
		$A(document.getElementsByName('id[]')).each(function(node){
			if (node.checked) _id = node.value
		});

		if (_id > 0) {
			$('ajax_result').hide();
			$('mandators').hide();
			$('toolbar_loading').show();
			new Ajax.Updater('mandator_edit', '?req=mandator&action=edit',
			{
			  parameters: { id: _id },
			  onComplete: function() {
			  	$('mandator_edit').appear(); $('toolbar_loading').hide();
			  }
			});
		} else {
			$('ajax_result').appear(); $('ajax_result').update(LC_NO_MANDATOR_SELECTED);
		}
	}

	/**
	  * Submit mandator action (activate, deactivate, delete)
	  */
	function submit_mandators(p_action) {

		$('toolbar_loading').show();
		var arIds = [];
		$A(document.getElementsByName('id[]')).each(function(node){
			if (node.checked) arIds.push(node.value);
		});

		new Ajax.Updater('ajax_result', '?req=mandator&action=' + p_action,
		{
		  parameters: { ids: Object.toJSON(arIds) },
		  onComplete: function(transport) {
		  	$('ajax_result').appear(); $('ajax_result').highlight();
		  	$('toolbar_loading').hide();
		  	window.transportHandler(transport);
	  		reload_mandators();
		  }
		});

	}

	/**
	  * Reload mandator list
	  */
	function reload_mandators() {
		new Ajax.Updater('mandators', '?req=mandator&action=list');
		if (!$('mandators').visible()) new Effect.SlideDown('mandators', {duration:0.3});
	}

	/**
	  * Save edited mandator
	  */
	function save_mandator() {

		$('edit_loading').show();
		new Ajax.Updater('ajax_result', '?req=mandator&action=edit',
		{
		  parameters: $('edit_form').serialize(true), evalScripts:true,
		  onComplete: function(transport) {
		  	$('ajax_result').show(); $('ajax_result').highlight();
		  	$('edit_loading').hide();


		  	if (window.transportHandler(transport)) {
		  		$('mandator_edit').hide();
		  		reload_mandators();
		  	}
		  }
		});

	}

	window.transportHandler = function(transport) {
		var jsonObject = transport.responseJSON;

		if (jsonObject) {

			$('ajax_result').update(jsonObject.message);
			$('ajax_result').style.backgroundColor = '';

			if (jsonObject.error) {
				$('ajax_result').className = 'error p10 mb10';

				return false;
			} else {
				$('ajax_result').className = 'note p10 mb10';

				return true;
			}

		}

		return false;
	};

	function delete_mandators() {

		if (confirm(LC_DELETE_CONFIRM)) {
			submit_mandators('delete');
		}

	}

</script>

<div class="gradient content-header">
	<img src="../images/icons/silk/database_table.png" class="vam mr5" /><span class="bold text-shadow headline vam">Tenants</span>
</div>

<div id="innercontent">
	[{if $error}]
	<div id="error" class="error p10 mb10"><strong>Error:</strong><br /><br />[{$error}]</div>
	[{/if}]

	[{if $output}]
	<div id="note" class="note p10 mt0">[{$output}]</div>
	[{/if}]

	<div id="ajax_result" class="note p10 mb10" style="display:none;"></div>

	<div id="add-new" class="mt10" style="display:none;">
		<form id="add_form" action="?req=mandator&action=add" method="post">
			<fieldset>
				<legend class="bold text-shadow">Add a new tenant</legend>

				<table cellpadding="2" cellspacing="0" width="100%" class="sortable mt10">
					<colgroup>
						<col width="350" />
					</colgroup>
					<tr>
					  <th colspan="2">
					  	<span>Tenant Info</span>
					  </th>
					 </tr>
					<tr>
					  <td class="bold">
					   Tenant GUI title:
					  </td>
					  <td>
					  	<input type="text" name="mandator_title" onfocus="if (this.value=='New Tenant')this.value='';" value="New Tenant" />
					  </td>
					 </tr>
					<tr>
					  <th colspan="2">
					  	<span class="fr">This user will be authorized to the tenant database.
					  	<span class="red">Note that this is NOT an i-doit login!</span></span>
					  	<span>MySQL user settings</span>
					  </th>
					 </tr>
					 <tr>
					  <td class="bold">
					   Username (max. 16 & no special chars):
					  </td>
					  <td>
					  	<input onblur="CheckDatabaseName(this, 'Your username has got special charactes. Only a-z & A-Z is allowed here. Please correct your value.'); return false;" type="text" name="mandator_username" value="idoit" />
					  	(a-z A-Z)
					  </td>
					 </tr>
					 <tr>
					  <td class="bold">
					   Password:
					  </td>
					  <td>
					  	<input type="password" name="mandator_password" id="mandator_password" value="" />
					  </td>
					 </tr>
					 <tr>
					  <td class="bold">Retype password:</td>
					  <td>
					  	<input type="password" name="mandator_password2" id="mandator_password2" value="" />
					  </td>
					 </tr>
					 <tr>
					  <th colspan="2">
					   <span>Database settings</span>
					  </th>
					 </tr>
					 <tr>
					 <td class="bold" valign="top">
					   New Database
					  </td>
					  <td>
						 <label><input type="checkbox" name="addNewDatabase" value="1" checked="checked" onchange="" /> Yes</label>
					  </td>
					 </tr>
					 <tr>
					  <td class="bold">
					   Tenant Database Name (max. 64 char):
					  </td>
					  <td>
					  	<input onblur="CheckDatabaseName(this, 'Be aware that the database name only allow the characters 0-9, a-Z and _. Please correct your value.'); return false;" type="text" name="mandator_database" onfocus="if (this.value=='idoit_data_new')this.value='';" value="idoit_data_new" />
					  	(0-9, a-z, A-Z and _)
					  </td>
					</tr>
                     <tr>
					  <td class="bold">
					   Auto-Increment start value:
					  </td>
					  <td>
                          <input onblur="checkAutoInc(this, 'Please use a value bigger then 1.'); return false;" type="text" name="mandator_autoinc" value="1" />
					  	(>0)
					  </td>
					</tr>
					[{if $db_conf.user != "root" || $smarty.post.root_pw}]
				 	<tr>
					  <th colspan="2">
					   <span>MySQL privileges</span>
					  </th>
					 </tr>
					 <tr class="newDatabase">
					  <td class="bold">
					  Type in your MySQL root password:
					  </td>
					  <td>
					  	<input type="password" name="root_pw" value="[{$smarty.post.root_pw}]" />
					  </td>
					 </tr>
					 [{/if}]
			 	</table>

				<div class="toolbar">
					<a class="bold" href="javascript:" id="btnAddTenant"> Add tenant</a>
					<a class="bold" href="javascript:" onclick="new Effect.SlideUp('add-new', {duration:0.3});new Effect.Appear('mandators',{duration:0.4});"> Abort</a>
					<span id="add_loading" style="display:none;"><img src="../images/ajax-loading.gif" class="vam" style="margin-top:1px;margin-left:5px;" /> Tenant is being added, please wait..</span>
				</div>

			</fieldset>
		</div>

		<div id="mandators">
			[{include file="pages/mandator_list.tpl"}]
		</div>
	</form>

	<div id="mandator_edit" style="display:none;"></div>
</div>

<script type="text/javascript">
	$('btnAddTenant').on('click', function (ev) {

		if (formCheck()) {
			$('add_loading').show();

			new Ajax.Updater('ajax_result', '?req=mandator&action=add',
					{
						parameters:  $('add_form').serialize(true),
						evalScripts: true,
						onComplete:  function (transport) {
							if (window.transportHandler(transport))
							{
								$('add-new').hide();
								reload_mandators();
							}

							$('ajax_result').show();
							$('ajax_result').highlight();

							$('add_loading').hide();
						}
					}
			);
		}

	});
</script>