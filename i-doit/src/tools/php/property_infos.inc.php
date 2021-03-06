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
 * @param array $p_structure
 */
function structure(array $p_structure)
{
    foreach ($p_structure as $l_category => $l_properties)
    {

        $l_cat = explode(':', $l_category);

        $l_example = 'array(<br />';
        echo '<h3>' . $l_cat[0] . ' </h3>';
        echo '<h4>' . _L('LC__DATABASE_OBJECTS__TABLE') . ': ' . $l_cat[2] . ' </h4>';
        echo '<table style="border-spacing: 1;min-width:1280px;" cellpadding="0" cellspacing="0">';
        echo '<tr>' . '<th align="left" width="15%">' . _L('LC__UNIVERSAL__FIELD') . '</th>' . '<th align="left" width="5%">' . _L(
                'LC__CMDB__CATS__GROUP_TYPE'
            ) . '</th>' . '<th align="left" width="15%">' . _L('LC__DATABASE_OBJECTS__TABLE') . '</th>' . '<th align="left" width="30%">' . _L(
                'LC__CMDB__TREE__DATABASE'
            ) . '-' . _L('LC__UNIVERSAL__FIELD') . '</th>' . '<th align="left" width="20%">' . _L('LC__CMDB__CATG__REFERENCED_VALUE') . '-' . _L(
                'LC__UNITS__TABLE'
            ) . '</th>' . '<th align="left" width="15%">' . _L('LC__CMDB__CATG__REFERENCED_VALUE') . '</th>' . '</tr>';

        foreach ($l_properties as $l_prop)
        {
            echo '<tr style="font-size:80%;">';
            echo '<td>' . $l_prop['title'] . '</td>';
            echo '<td>' . $l_prop['type'] . '</td>';
            echo '<td>' . $l_prop['table'] . '</td>';
            echo '<td>' . $l_prop['field'] . '</td>';
            echo '<td>' . $l_prop['ref_table'] . '</td>';
            echo '<td>' . $l_prop['ref'] . '</td>';
            echo '</tr>';

        }
        echo '</table>';

        echo "<br />";
    }
}

global $g_comp_database;
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

        $l_class        = $l_row['isysgui_cat' . $l_cattype . '__class_name'];
        $l_source_table = $l_row['isysgui_cat' . $l_cattype . '__source_table'];
        $l_source_table .= (strpos($l_source_table, '_listener') !== false) ? '_list' : (strpos($l_source_table, '_list') === false) ? '_list' : '';
        if (strpos(' ' . $l_class, 'isys_') !== false && class_exists($l_class))
        {
            $l_category_dao = new $l_class($g_comp_database);
            $l_properties   = $l_category_dao->get_properties();

            if ($l_properties && count($l_properties) > 0)
            {
                foreach ($l_properties as $l_key => $l_prop)
                {
                    $l_structure[$l_cattype][_L(
                        $l_row['isysgui_cat' . $l_cattype . '__title']
                    ) . ':' . $l_row['isysgui_cat' . $l_cattype . '__const'] . ':' . $l_source_table][$l_key] = [
                        'title'     => _L($l_prop[C__PROPERTY__INFO][C__PROPERTY__INFO__TITLE]),
                        'key'       => $l_key,
                        'table'     => isset($l_prop[C__PROPERTY__DATA][C__PROPERTY__DATA__TABLE_ALIAS]) && $l_prop[C__PROPERTY__DATA][C__PROPERTY__DATA__TABLE_ALIAS] ? $l_prop[C__PROPERTY__DATA][C__PROPERTY__DATA__TABLE_ALIAS] : $l_source_table,
                        'type'      => $l_prop[C__PROPERTY__DATA][C__PROPERTY__DATA__TYPE],
                        'field'     => $l_prop[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD],
                        'ref'       => $l_prop[C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][1],
                        'ref_table' => $l_prop[C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0]
                    ];
                }
            }
        }
    }
}

echo '<h1>i-doit Database</h1>';

if (is_array($l_structure['g']))
{
    echo '<h2>' . _L('LC__CMDB__GLOBAL_CATEGORIES') . '</h2>';
    structure($l_structure['g']);
}

if (is_array($l_structure['s']))
{
    echo '<h2>' . _L('LC__CMDB__SPECIFIC_CATEGORIES') . '</h2>';
    structure($l_structure['s']);
}
/*
if (is_array($l_structure['c']))
{
	echo '<h2>'._L('LC__CMDB__CUSTOM_CATEGORIES').'</h2>';
	structure($l_structure['c']);
}
*/