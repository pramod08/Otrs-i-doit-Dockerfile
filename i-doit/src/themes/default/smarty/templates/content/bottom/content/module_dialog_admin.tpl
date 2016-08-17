
[{if $g_list}]

[{$g_list}]

[{elseif !empty($g_data.id) || $smarty.post.navMode == $smarty.const.C__NAVMODE__NEW}]
<h2 class="p5 gradient border-bottom">[{isys type="lang" ident=$smarty.get.table}]</h2>

<div>
    <input type="hidden" name="dialog_id" value="[{$g_data.id}]"/>

    <table class="contentTable" cellspacing="5">
        <tr>
            <td class="key">ID</td>
            <td class="value"><strong class="ml20">[{$g_data.id}]</strong></td>
        </tr>
        <tr>
            <td class="key"><label for="title">[{isys type="lang" ident="LC__CATD__TITLE"}]</label></td>
            <td class="value">
	            <input class="input input-small ml20" type="text" id="title" name="title" value="[{$g_data.title}]" />
                <i class="ml5 grey" style="font-weight:normal;">[{isys type="lang" ident="LC__UNIVERSAL__ALPHANUMERIC"}]</i>
            </td>
        </tr>
        <tr>
            <td class="key"><label for="const">[{isys type="lang" ident="LC__CMDB__OBJTYPE__CONST"}]</label></td>
            <td class="value">
                <input class="input input-small ml20" type="text" id="const" name="const" value="[{$g_data.const}]" [{if !empty($g_data.const)}]disabled="disabled" readonly="readonly"[{/if}] />
                <i class="ml5 grey" style="font-weight:normal;">[{isys type="lang" ident="LC__UNIVERSAL__ALPHANUMERIC"}]</i>
            </td>
        </tr>
        <tr>
            <td class="key"><label for="status">[{isys type="lang" ident="LC__UNIVERSAL__STATUS"}]</label></td>
            <td class="value">
	            [{isys type="f_dialog" name="status" p_strClass="input-mini" p_arData=$recordStatus p_strSelectedID=$g_data.status}]
            </td>
        </tr>

        [{if $g_data.has_parent}]
        <tr>
            <td class="key"><label for="C__DIALOG__PARENTS">Parent</label></td>
            <td class="value">
	            [{isys type="f_dialog" name="C__DIALOG__PARENTS" p_strClass="input-mini" p_bDbFieldNN="1"}]
            </td>
        </tr>
        [{/if}]

        [{if $addons.relation}]
        <tr>
            <td colspan="2">
                <hr class="mt5 mb5"/>
            </td>
        </tr>
        <tr>
            <td class="key"><label for="relation_master">[{isys type="lang" ident="LC__CATG__RELATION__RELATION_DESC_MASTER"}]</label></td>
            <td class="value">
	            <input class="input input-small ml20" type="text" id="relation_master" name="relation_master" value="[{$g_data.master}]" />
                <i class="ml5 grey" style="font-weight:normal;">[{isys type="lang" ident="LC__UNIVERSAL__ALPHANUMERIC"}]</i>
            </td>
        </tr>
        <tr>
            <td class="key">[{isys type="lang" ident="LC__UNIVERSAL__EXAMPLE"}]</td>
            <td class="value"><span class="ml20">[{isys type="lang" ident="LC__RELATION_TYPE__MASTER__DEPENDS_ON_ME"}]</span></td>
        </tr>
        <tr>
            <td class="key"><label for="relation_slave">[{isys type="lang" ident="LC__CATG__RELATION__RELATION_DESC_SLAVE"}]</label></td>
            <td class="value">
	            <input class="input input-small ml20" type="text" id="relation_slave" name="relation_slave" value="[{$g_data.slave}]" />
                <i class="ml5 grey" style="font-weight:normal;">[{isys type="lang" ident="LC__UNIVERSAL__ALPHANUMERIC"}]</i>
            </td>
        </tr>
        <tr>
            <td class="key">[{isys type="lang" ident="LC__UNIVERSAL__EXAMPLE"}]:</td>
            <td class="value"><span class="ml20">[{isys type="lang" ident="LC__RELATION_TYPE__SLAVE__DEPENDS_ON_ME"}]</span></td>
        </tr>
        [{/if}]

        [{if $display_wysiwyg}]
        <tr>
            <td colspan="2">
                <hr class="mt5 mb5" />
            </td>
        </tr>
        <tr>
            <td class="key">
                [{isys type="f_label" name="description" ident="LC__UNIVERSAL__DESCRIPTION"}]
            </td>
            <td class="value">
                [{isys type="f_wysiwyg" name="description" p_strValue=$g_data.description}]
            </td>
        </tr>
        [{/if}]
    </table>

</div>
[{else}]
	<h3 class="p5 gradient border-bottom">[{$g_message}]</h3>
[{/if}]