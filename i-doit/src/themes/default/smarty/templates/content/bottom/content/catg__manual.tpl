<table class="contentTable">
    <tr>
        <td class="key">[{isys type='f_label' name='C__CATG__MANUAL_TITLE' ident="LC__CMDB__CATG__MANUAL_TITLE"}]</td>
        <td class="value">[{isys type="f_text" name="C__CATG__MANUAL_TITLE" tab="1"}]</td>
    </tr>
    <tr>
        <td class="key">[{isys type='f_label' name='C__CATG__MANUAL_OBJ_FILE__VIEW' ident="LC__CMDB__CATG__MANUAL_OBJ_FILE"}]</td>
        <td class="value">[{isys name="C__CATG__MANUAL_OBJ_FILE" type="f_popup" p_strPopupType="browser_file" p_strValue=""}]
        </td>
    </tr>

    [{if $file_uploaded eq "1"}]
    <tr>
        <td class="key">[{isys type='f_label' name='C__CATG__FILE_NAME' ident="LC__CMDB__CATS__FILE_NAME"}]</td>
        <td class="value">[{isys type="f_data" name="C__CATG__FILE_NAME"}]</td>
    </tr>
    <tr>
        <td class="key">[{isys type='f_label' name='C__CATG__FILE_DOWNLOAD' ident="LC__CMDB__CATS__FILE_DOWNLOAD"}]</td>
        <td class="value">[{isys type="image" p_strSrc="$dir_images/icons/disk.gif" name="C__CATG__FILE_DOWNLOAD" tab="3"}]</td>
    </tr>
    [{/if}]
</table>

<script type="text/javascript" src="[{$dir_tools}]js/ajax_upload/fileuploader.js"></script>