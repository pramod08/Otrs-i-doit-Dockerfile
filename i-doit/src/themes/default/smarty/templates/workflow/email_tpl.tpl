<h2 class="p5 gradient text-shadow">E-Mail Templates</h2>

<fieldset class="overview">
	<legend><span>[{isys type="lang" ident="LC__LOCALE__GERMAN"}]</span></legend>
	<table class="contentTable">
		<tbody>
		<tr>
			<td class="key">[{isys type="lang" ident="LC__NOTIFICATIONS__NOTIFICATION_TEMPLATE_SUBJECT"}]</td>
			<td class="value">[{isys type="f_text" name="email_de_subject" p_strValue=$email_de_subject p_bEditMode=1}]</td>
		</tr>
		<tr>
			<td class="key">[{isys type="lang" ident="LC__UNIVERSAL__TEMPLATE"}]</td>
			<td class="value">[{isys type="f_textarea" name="email_de_body" p_strValue=$email_de_body p_bEditMode=1}]</td>
		</tr>
		<tbody>
	</table>
</fieldset>

<fieldset class="overview">
	<legend><span>[{isys type="lang" ident="LC__LOCALE__ENGLISH"}]</span></legend>
	<table class="contentTable mt10">
		<tr>
			<td class="key">[{isys type="lang" ident="LC__NOTIFICATIONS__NOTIFICATION_TEMPLATE_SUBJECT"}]</td>
			<td class="value">[{isys type="f_text" name="email_en_subject" p_strValue=$email_en_subject p_bEditMode=1}]</td>
		</tr>
		<tr>
			<td class="key">[{isys type="lang" ident="LC__UNIVERSAL__TEMPLATE"}]</td>
			<td class="value">[{isys type="f_textarea" name="email_en_body" p_strValue=$email_en_body p_bEditMode=1}]</td>
		</tr>
	</table>
</fieldset>

<fieldset class="overview">
	<legend><span>[{isys type="lang" ident="LC__RFC__HELP"}]</span></legend>

	<div class="p5 mt5">
		<h2>[{isys type="lang" ident="LC__RFC__USEFULL_INFORMATION"}]</h2>
		<p>[{isys type="lang" ident="LC__RFC__USEFULL_INFORMATION_TEXT"}] <a href="http://www.smarty.net/documentation" target="_blank">Smarty 3 Documentation</a></p>

		<br />
		<h3>[{isys type="lang" ident="LC__RFC__INFORMATION_VARIABLE"}]</h3>
		<p>[{isys type="lang" ident="LC__RFC__INFORMATION_VARIABLE_TEXT"}] <code>[{ldelim}]VARIABLE[{rdelim}]</code>.</p>
		<p>[{isys type="lang" ident="LC__RFC__INFORMATION_VARIABLE_EXAMPLES"}]</p>

		<table class="mt5 ml10">
			<tr>
				<td class="right">Workflow title</td>
				<td class="pl20"><code class="bold">[{ldelim}]$g_task.title[{rdelim}]</code></td>
			</tr>
			<tr>
				<td class="right">Intiator fullname</td>
				<td class="pl20"><code class="bold">[{ldelim}]$g_task.initiator.fullname[{rdelim}]</code></td>
			</tr>
			<tr>
				<td class="right">Title of first contact</td>
				<td class="pl20"><code class="bold">[{ldelim}]$g_task.contacts.0.title[{rdelim}]</code></td>
			</tr>
		</table>

		<br/>
		<h3>[{isys type="lang" ident="LC__RFC__INFORMATION_VARIABLE_LIST"}]</h3>
        <pre>
  'id' <span color="#888a85">=&gt;</span> <span color="#cc0000">'Workflow ID'</span>
  'link' <span color="#888a85">=&gt;</span> <span color="#cc0000">'Workflow link'</span>
  'query' <span color="#888a85">=&gt;</span> <span color="#cc0000">'Workflow query link'</span>
  'category' <span color="#888a85">=&gt;</span> <span color="#cc0000">'Workflow category'</span>
  'type' <span color="#888a85">=&gt;</span> <span color="#cc0000">'Workflow type'</span>
  'title' <span color="#888a85">=&gt;</span> <span color="#cc0000">'Workflow title'</span>
  'status' <span color="#888a85">=&gt;</span> <span color="#cc0000">'Workflow status'</span>
  'message' <span color="#888a85">=&gt;</span> <span color="#cc0000">'Workflow message'</span>
  'contactID' <span color="#888a85">=&gt;</span> <span color="#cc0000">'Workflow creator ID'</span>
  'objects' <span color=""><b>List of assigned objects</b></span> <span color="#888a85">=&gt;</span>
    <b>array</b>
      'id' <span color="#888a85">=&gt;</span> <span color="#cc0000">'Object ID'</span>
      'title' <span color="#888a85">=&gt;</span> <span color="#cc0000">'Object title'</span>
      'link' <span color="#888a85">=&gt;</span> <span color="#cc0000">'Object link'</span>
      'status' <span color="#888a85">=&gt;</span> <span color="#cc0000">'Object status ID'</span>
      'type' <span color="#888a85">=&gt;</span> <span color="#cc0000">'Object type'</span>
  'contacts' <span color=""><b>List of assigned contacts</b></span> <span color="#888a85">=&gt;</span>
    <b>array</b>
      'id' <span color="#888a85">=&gt;</span> <span color="#cc0000">'Contact ID'</span>
      'title' <span color="#888a85">=&gt;</span> <span color="#cc0000">'Contact title'</span>
      'link' <span color="#888a85">=&gt;</span> <span color="#cc0000">'Contact link'</span>
      'type' <span color="#888a85">=&gt;</span> <span color="#cc0000">'Contact/Object type'</span>
  'initiator' <span color=""><b>Initiator/creator of the workflow</b></span> <span color="#888a85">=&gt;</span>
    <b>array</b>
      'fullname' <span color="#888a85">=&gt;</span> <span color="#cc0000">'Intiator fullname'</span>
      'username' <span color="#888a85">=&gt;</span> <span color="#cc0000">'Initiator lastname'</span>
      'id' <span color="#888a85">=&gt;</span> <span color="#cc0000">'Initiator ID'</span>
  'actor' <span color=""><b>Actor depends on the situation. It can be the initiator on create or the person who is accepting the workflow</b></span> <span color="#888a85">=&gt;</span>
    <b>array</b>
      'id' <span color="#888a85">=&gt;</span> <span color="#cc0000">'Actor ID'</span>
      'title' <span color="#888a85">=&gt;</span> <span color="#cc0000">'Actor title'</span>
      'type' <span color="#888a85">=&gt;</span> <span color="#cc0000">'Actor type'</span>
      'link' <span color="#888a85">=&gt;</span> <span color="#cc0000">'Actor link'</span></pre>
	</div>
</fieldset>