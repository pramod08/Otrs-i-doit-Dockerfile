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
for ($i = 0;$i <= 255;$i++)
{
    $fpdf_charwidths['courier'][chr($i)] = 600;
}
$fpdf_charwidths['courierB']  = $fpdf_charwidths['courier'];
$fpdf_charwidths['courierI']  = $fpdf_charwidths['courier'];
$fpdf_charwidths['courierBI'] = $fpdf_charwidths['courier'];
?>