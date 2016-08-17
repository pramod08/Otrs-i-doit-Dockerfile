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
 * @param $p_structure
 */
function structure($p_structure)
{
    foreach ($p_structure as $l_category => $l_properties)
    {

        $l_cat = explode(':', $l_category);

        $l_example = 'array(<br />';
        echo '<h3>' . $l_cat[0] . ' (' . $l_cat[1] . ')</h3>';
        echo '<table style="border-spacing: 0;" cellpadding="0" cellspacing="0">';
        echo '<tr>' . '<th align="left" width="200">Feldname</th>' . '<th align="left" width="110">Key</th>' . '<th align="left" width="75">Datentyp</th>' . '<th align="left" width="100">Referenz</th>' . //'<th align="left">Datenbank-Feld</th>'.
            '<th align="left" width="50">Optional</th>' . '</tr>';
        foreach ($l_properties as $l_prop)
        {
            echo '<tr style="font-size:80%;">';
            echo '<td>' . $l_prop['title'] . '</td>';
            echo '<td>' . $l_prop['key'] . '</td>';
            echo '<td>' . $l_prop['type'] . '</td>';
            echo '<td>' . $l_prop['ref'] . '</td>';
            //echo '<td>'.$l_prop['field'] . '</td>';
            echo '<td>' . $l_prop['optional'] . '</td>';
            echo '</tr>';

            $l_example .= "&nbsp;&nbsp;'" . $l_prop['key'] . "' => '" . $l_prop['type'] . "',\n";
        }
        $l_example = rtrim($l_example, "\n,");
        echo '</table>';

        echo "<br />";
        echo '<strong>Beispiel-Array:</strong>';
        echo '<p style="border:1px solid #000; background-color:#ccc; font-family: Monaco, Courier New; font-size:9pt;">';

        echo nl2br($l_example) . "<br />)";

        echo '</p>';

    }
}

global $g_comp_database;

if (!$g_comp_database)
{
    die('You need to be logged in.');
}

$l_dao       = new isys_cmdb_dao($g_comp_database);
$l_structure = [];
$i           = 0;

foreach ([
             'g',
             's'
         ] as $l_cattype)
{
    $l_categories = $l_dao->get_isysgui('isysgui_cat' . $l_cattype);

    while ($l_row = $l_categories->get_row())
    {

        $l_class = $l_row['isysgui_cat' . $l_cattype . '__class_name'];
        if (class_exists($l_class))
        {

            $l_category_dao = new $l_class($g_comp_database);
            $l_properties   = $l_category_dao->get_properties();

            foreach ($l_properties as $l_key => $l_prop)
            {
                $l_structure[$l_cattype][_L($l_row['isysgui_cat' . $l_cattype . '__title']) . ':' . $l_row['isysgui_cat' . $l_cattype . '__const']][$l_key] = [
                    'title'    => _L($l_prop[C__PROPERTY__INFO][C__PROPERTY__INFO__TITLE]),
                    'key'      => $l_key,
                    'type'     => $l_prop[C__PROPERTY__DATA][C__PROPERTY__DATA__TYPE],
                    'field'    => $l_prop[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD],
                    'ref'      => $l_prop[C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][1],
                    'optional' => $l_prop[C__PROPERTY__CHECK][C__PROPERTY__CHECK__MANDATORY] === true ? 'Nein' : 'Ja',
                ];
            }

            $i++;
        }

    }
}

echo '<h1>Kategorie-Felder für Datenarrays</h1>';

if (is_array($l_structure['g']))
{
    echo '<h2>' . _L('LC__CMDB__GLOBAL_CATEGORIES') . ' (catg)</h2>';
    structure($l_structure['g']);
}

if (is_array($l_structure['s']))
{
    echo '<h2>' . _L('LC__CMDB__SPECIFIC_CATEGORIES') . ' (cats)</h2>';
    structure($l_structure['s']);
}