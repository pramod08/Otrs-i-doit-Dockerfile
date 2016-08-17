<table class="contentTable">
    <tr>
        <td class="key">[{isys type='f_label' name='C__CATG__FILE_OBJ_FILE' ident="LC__CMDB__CATG__FILE_OBJ_FILE"}]</td>
        <td class="value">[{isys type="f_popup" p_strPopupType="browser_file" name="C__CATG__FILE_OBJ_FILE" p_bDbFieldNN="0"}]</td>
    </tr>
    <tr>
        <td class="key">[{isys type='f_label' name='C__CATG__FILE_NAME' ident="LC__CMDB__CATS__FILE_NAME"}]</td>
        <td class="value">[{isys type="f_data" name="C__CATG__FILE_NAME"}]</td>
    </tr>

    [{if $file_uploaded}]
    <tr>
        <td class="key">[{isys type='f_label' name='C__CATG__FILE_DOWNLOAD' ident="LC__CMDB__CATS__FILE_DOWNLOAD"}]</td>
        <td class="value">[{isys type="image" p_strSrc="$dir_images/icons/silk/disk.png" name="C__CATG__FILE_DOWNLOAD"}]</td>
    </tr>
    <tr>
        <td colspan="2">&nbsp;</td>
    </tr>
    [{/if}]

    <tr>
        <td class="key">[{isys type="f_label" ident="LC__CMDB__CATG__FILE__FILE_LINK" name="C__CATG__FILE_LINK"}]</td>
        <td class="value">[{isys type="f_link" name="C__CATG__FILE_LINK"}]</td>
    </tr>
</table>

<script type="text/javascript" src="[{$dir_tools}]js/ajax_upload/fileuploader.js"></script>