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
 * i-doit APi
 *
 * @package    i-doit
 * @subpackage API
 * @author     Dennis Stücken <dstuecken@synetics.de>
 * @copyright  synetics GmbH
 * @license    http://www.i-doit.com/license
 */
class isys_api_model_idoit_constants implements isys_api_model_interface
{

    /**
     * Possible options and their parameters
     *
     * @var array
     */
    protected $m_options = [
        'read' => []
    ];

    /**
     * Documentation missing
     *
     * @param array $p_params
     *
     * @return array
     */
    public function read($p_params)
    {
        global $g_comp_database;

        $l_return = [
            'objectTypes' => [],
            'categories'  => [
                'g' => [],
                's' => []
            ]
        ];

        if (is_object($g_comp_database))
        {
            $l_dao_cmdb = new isys_cmdb_dao_category_property($g_comp_database);

            /**
             * Retrieving all object types
             */
            $l_object_types = $l_dao_cmdb->get_object_types();

            while ($l_row = $l_object_types->get_row())
            {
                $l_return['objectTypes'][$l_row['isys_obj_type__const']] = ucwords(strtolower(_L($l_row['isys_obj_type__title'])));
            }

            foreach ([
                         'g',
                         's'
                     ] as $l_type)
            {
                $l_categories[$l_type] = $l_dao_cmdb->get_isysgui('isysgui_cat' . $l_type);

                if (isset($l_categories[$l_type]) && is_object($l_categories[$l_type]))
                {
                    while ($l_row = $l_categories[$l_type]->get_row())
                    {

                        $l_return['categories'][$l_type][$l_row['isysgui_cat' . $l_type . '__const']] = ucwords(strtolower(_L($l_row['isysgui_cat' . $l_type . '__title'])));

                    }
                }

            }
        }
        else
        {
            throw new isys_exception_api('Error getting constants: The database component is not available.');
        }

        return $l_return;
    } // function

} // class