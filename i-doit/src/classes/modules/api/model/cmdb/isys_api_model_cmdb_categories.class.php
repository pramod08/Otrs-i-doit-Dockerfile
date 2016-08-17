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
 * i-doit
 *
 * API model
 *
 * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 * Retrieve multiple
 * categories by one request
 * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 *
 * @package    i-doit
 * @subpackage API
 * @author     Dennis Stücken <dstuecken@synetics.de>
 * @author     Selcuk Kekec <skekec@synetics.de>
 * @copyright  synetics GmbH
 * @license    http://www.i-doit.com/license
 */
class isys_api_model_cmdb_categories extends isys_api_model_cmdb_category
{

    /**
     * Data formatting used in format methods
     *
     * @var array
     */
    protected $m_mapping = [];
    /**
     * Possible options and their parameters
     *
     * @var array
     */
    protected $m_options = [
        'read' => []
    ];
    /**
     * Validation
     *
     * @var array
     */
    protected $m_validation = [];

    /**
     * Read category data
     *
     * $p_param structure:
     *    array (
     *        'objID'        => 1,
     *        'catgID'    => 10
     *    )
     *
     * or
     *    array(
     *        'objID'        => 1,
     *        'catsID'    => 12
     *    )
     *
     * @param array $p_params
     *
     * @return array
     */
    public function read($p_params)
    {

        /* Init */
        $l_return         = [];
        $i                = 0;
        $l_object_id      = @$p_params[C__CMDB__GET__OBJECT];
        $l_category_types = [
            [
                'suffix'    => 'g',
                'parameter' => C__CMDB__GET__CATG
            ],
            [
                'suffix'    => 's',
                'parameter' => C__CMDB__GET__CATS
            ]
        ];
        $g_return         = [];
        $l_category_count = 0;

        /* Validate object id */
        if (!$l_object_id || $l_object_id < 1)
        {
            throw new isys_exception_api(
                'Object id invalid. ID must be positive and higher than one.', -32602
            );
        }

        if (isset($p_params[C__CMDB__GET__CATS]) || isset($p_params[C__CMDB__GET__CATG]))
        {
            foreach ($l_category_types AS $l_s_category_type)
            {
                if (isset($p_params[$l_s_category_type['parameter']]))
                {
                    $p_params[$l_s_category_type['parameter']] = (!is_array($p_params[$l_s_category_type['parameter']])) ? [
                        $p_params[$l_s_category_type['parameter']]
                    ] : $p_params[$l_s_category_type['parameter']];

                    foreach ($p_params[$l_s_category_type['parameter']] AS $l_s_category_identifier)
                    {
                        $l_category_count++;
                        $l_return = [];
                        $i        = 0;

                        /* Get category info */
                        if (is_numeric(addslashes($l_s_category_identifier)))
                        {
                            $l_isysgui = $this->m_dao->get_isysgui(
                                'isysgui_cat' . $l_s_category_type['suffix'],
                                (int) $l_s_category_identifier
                            )
                                ->__to_array();
                        }
                        else
                        {
                            $l_isysgui = $this->m_dao->get_isysgui(
                                'isysgui_cat' . $l_s_category_type['suffix'],
                                null,
                                null,
                                addslashes($l_s_category_identifier)
                            )
                                ->__to_array();
                        }

                        /* Check class and instantiate it */
                        if (class_exists($l_isysgui["isysgui_cat{$l_s_category_type['suffix']}__class_name"]))
                        {

                            /* Process data */
                            if (($l_cat = new $l_isysgui["isysgui_cat{$l_s_category_type['suffix']}__class_name"]($this->m_dao->get_database_component())))
                            {

                                if (method_exists($l_cat, 'get_data'))
                                {

                                    if (isset($p_params['condition']))
                                    {
                                        $l_condition = addslashes(urldecode($p_params['condition']));
                                    }
                                    else $l_condition = null;

                                    if (isset($p_params['filter']))
                                    {
                                        $l_filter = addslashes($p_params['filter']);
                                    }
                                    else $l_filter = null;

                                    if (isset($p_params['status']))
                                    {
                                        $l_status = is_numeric($p_params['status']) ? $p_params['status'] : (defined($p_params['status']) ? constant(
                                            $p_params['status']
                                        ) : null);
                                    }
                                    else
                                    {
                                        $l_status = C__RECORD_STATUS__NORMAL;
                                    }

                                    //get_data($p_catg_list_id = null, $p_obj_id = null, $p_condition = "", $p_filter = null, $p_status = null)
                                    $l_catdata = $l_cat->get_data(
                                        null,
                                        addslashes($l_object_id),
                                        urldecode($l_condition),
                                        $l_filter,
                                        $l_status
                                    );

                                    if ($l_catdata->num_rows() > 0)
                                    {
                                        if (!isset($p_params['raw']) || !$p_params['raw'])
                                        {
                                            $l_properties = $l_cat->get_properties();

                                            /* Format category result */
                                            while ($l_row = $l_catdata->get_row())
                                            {
                                                if (isset($l_row[$l_isysgui["isysgui_cat{$l_s_category_type['suffix']}__source_table"] . '_list__id']))
                                                {
                                                    $l_return[$i]['id'] = $l_row[$l_isysgui["isysgui_cat{$l_s_category_type['suffix']}__source_table"] . '_list__id'];
                                                }
                                                else if (isset($l_row[$l_isysgui["isysgui_cat{$l_s_category_type['suffix']}__source_table"] . '__id']))
                                                {
                                                    $l_return[$i]['id'] = $l_row[$l_isysgui["isysgui_cat{$l_s_category_type['suffix']}__source_table"] . '__id'];
                                                }

                                                if (isset($l_row[$l_isysgui["isysgui_cat{$l_s_category_type['suffix']}__source_table"] . '_list__isys_obj__id']))
                                                {
                                                    $l_return[$i]['objID'] = $l_row[$l_isysgui["isysgui_cat{$l_s_category_type['suffix']}__source_table"] . '_list__isys_obj__id'];
                                                }

                                                foreach ($l_properties as $l_key => $l_propdata)
                                                {
                                                    if (is_string($l_key))
                                                    {
                                                        if (isset($l_propdata[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][0]) && isset($l_propdata[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][1]))
                                                        {

                                                            /* Call helper object to retrieve more information */
                                                            if (class_exists($l_propdata[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][0]))
                                                            {

                                                                $l_helper = new $l_propdata[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][0](
                                                                    $l_row,
                                                                    $this->m_dao->get_database_component(),
                                                                    $l_propdata[C__PROPERTY__DATA],
                                                                    $l_propdata[C__PROPERTY__FORMAT],
                                                                    $l_propdata[C__PROPERTY__UI]
                                                                );

                                                                /* Set the Unit constant for the convert-helper */
                                                                if ($l_propdata[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][1] == 'convert')
                                                                {
                                                                    $l_row_unit = $l_properties[$l_propdata[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__UNIT]][C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD];
                                                                    $l_helper->set_unit_const($l_row[$l_row_unit]);
                                                                }

                                                                $l_return[$i][$l_key] = call_user_func(
                                                                    [
                                                                        $l_helper,
                                                                        $l_propdata[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][1]
                                                                    ],
                                                                    $l_row[$l_propdata[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD]]
                                                                );

                                                                /* Retrieve data from isys_export_data */
                                                                if ($l_return[$i][$l_key] instanceof isys_export_data)
                                                                {
                                                                    $l_return[$i][$l_key] = $l_return[$i][$l_key]->get_data();
                                                                }
                                                            }
                                                        }
                                                        else
                                                        {
                                                            $l_return[$i][$l_key] = $l_row[$l_propdata[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD]];
                                                        }

                                                        unset($l_helper_class, $l_helper);
                                                    }
                                                }
                                                $i++;
                                            }
                                        }
                                        else
                                        {
                                            $l_return = $l_catdata->__as_array();
                                        }

                                        /* Handling for Multi/Single-Value categories */
                                        if ($l_isysgui["isysgui_cat{$l_s_category_type['suffix']}__list_multi_value"]) $g_return[$l_s_category_type['parameter']][$l_isysgui["isysgui_cat{$l_s_category_type['suffix']}__const"]] = $l_return;
                                        else
                                            $g_return[$l_s_category_type['parameter']][$l_isysgui["isysgui_cat{$l_s_category_type['suffix']}__const"]] = reset($l_return);
                                    }
                                }
                                else
                                {
                                    throw new isys_exception_api(
                                        'get_data method does not exist for ' . get_class($l_cat), -32601
                                    );
                                }
                            }
                        }
                    }
                }
            }
        }
        else
        {
            /* Nothing setted */
        }

        /* Compatibility-Mode: Return single resultset as root */
//        if ($l_category_count == 1 && is_array($g_return) && count($g_return)) {
//            $g_return = reset(reset($g_return));
//        }

        return $g_return;
    }

} // class