<?php
/**
 * i-doit - Documentation and CMDB solution for IT environments
 *
 * This file is part of the i-doit framework. Modify at your own risk.
 *
 * Please visit http://www.i-doit.com/license for a full copyright and license information.
 *
 * @version     1.7.3
 * @package     i-doit
 * @author      synetics GmbH
 * @copyright   synetics GmbH
 * @url         http://www.i-doit.com
 * @license     http://www.i-doit.com/license
 */
/**
 * Handler for QR Requests
 *
 * @package     i-doit
 * @subpackage  General
 * @author      Dennis Stuecken <dstuecken@i-doit.de>
 * @version     1.0
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */

$l_url      = @$_GET['url'];
$l_ajax_url = $l_url . '?ajax=1&call=qrcode&func=load_qr_code';
$l_obj_id   = @$_GET['objID'] ?: 0;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <title>i-doit barcode</title>
    <meta name="author" content="synetics gmbh" />
    <meta name="description" content="i-doit" />

    <script type="text/javascript" language="JavaScript" src="../../js/prototype/prototype.js"></script>
    <script type="text/javascript" language="JavaScript" src="../../js/scriptaculous/src/scriptaculous.js?load=effects"></script>

    <style type="text/css">
        body {
            font-family: "Lucida Grande", Tahoma, Arial, Helvetica, sans-serif;
            color: #000;
            font-size: 10pt;
        }

        #qrcode {
            text-align: center;
        }

        p {
            margin: 0;
        }

        #logo {
            margin: 5px;
        }
    </style>
</head>
<body>

<table id="qrcode">
    <tr>
        <td>
            <img id="code" src="<?php echo $l_url; ?>images/ajax-loading.gif" alt="Error loading the QR Code" />
        </td>
        <td>
            <p id="description"></p>
        </td>
        <td>
            <img id="logo" src="<?php echo $l_url; ?>images/ajax-loading.gif" />
        </td>
    </tr>
</table>

<script type="text/javascript">
    new Ajax.Request('<?php echo $l_ajax_url; ?>', {
        method:     'post',
        parameters: {
            objID:<?php echo $l_obj_id; ?>
        },
        onSuccess:  function (response) {
            var json = response.responseJSON;

            $('code').writeAttribute('src', 'qr_img.php?s=2&d=' + json.data.url);
            $('logo').writeAttribute('src', json.data.logo);
            $('description').update(json.data.description);

            // We need this timer for the browser to correctly detect the image heights...
            setTimeout('calc_sizes_and_print()', 100);
        }
    });

    function calc_sizes_and_print() {
        // Now we try to set the logo to the same size as the QR Code.
        $('logo').writeAttribute('height', $('code').getHeight() + 'px');

        if (window.print)
        {
            window.print();
        }
    }
</script>
</body>
</html>
