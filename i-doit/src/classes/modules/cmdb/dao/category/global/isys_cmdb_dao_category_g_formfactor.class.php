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
 * DAO: global category for form factors
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Niclas Potthast <npotthast@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_dao_category_g_formfactor extends isys_cmdb_dao_category_global
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'formfactor';

    /**
     * Category entry is purgable
     *
     * @var bool
     */
    protected $m_is_purgable = true;

    /**
     * Creates new entity.
     *
     * @param   array $p_data Properties in a associative array with tags as keys and their corresponding values as values.
     *
     * @return  mixed  Returns created entity's identifier (int) or false (bool).
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function create_data($p_data)
    {
        $p_data['width']  = (isset($p_data['width'])) ? isys_convert::measure($p_data['width'], $p_data['unit']) : null;
        $p_data['height'] = (isset($p_data['height'])) ? isys_convert::measure($p_data['height'], $p_data['unit']) : null;
        $p_data['depth']  = (isset($p_data['depth'])) ? isys_convert::measure($p_data['depth'], $p_data['unit']) : null;
        $p_data['weight'] = (isset($p_data['weight'])) ? isys_convert::weight($p_data['weight'], $p_data['weight_unit']) : null;

        return parent::create_data($p_data);
    } // function

    /**
     * Method for returning the properties.
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    protected function properties()
    {
        return [
            'formfactor'  => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog_plus(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__FORMFACTOR',
                        C__PROPERTY__INFO__DESCRIPTION => 'Form factor'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD      => 'isys_catg_formfactor_list__isys_catg_formfactor_type__id',
                        C__PROPERTY__DATA__REFERENCES => [
                            'isys_catg_formfactor_type',
                            'isys_catg_formfactor_type__id'
                        ]
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID     => 'C__CATG__FORMFACTOR_TYPE',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strTable' => 'isys_catg_formfactor_type',
                            'p_strClass' => 'input-small'
                        ]
                    ]
                ]
            ),
            'rackunits'   => array_replace_recursive(
                isys_cmdb_dao_category_pattern::int(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__RACKUNITS',
                        C__PROPERTY__INFO__DESCRIPTION => 'Rack units'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_formfactor_list__rackunits'
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID     => 'C__CATG__FORMFACTOR_RACKUNITS',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strClass' => 'input-mini'
                        ]
                    ]
                ]
            ),
            'unit'        => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__FORMFACTOR_INSTALLATION_DIMENSION_UNIT',
                        C__PROPERTY__INFO__DESCRIPTION => 'dimension unit'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD      => 'isys_catg_formfactor_list__isys_depth_unit__id',
                        C__PROPERTY__DATA__REFERENCES => [
                            'isys_depth_unit',
                            'isys_depth_unit__id'
                        ]
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID      => 'C__CATG__FORMFACTOR_INSTALLATION_DEPTH_UNIT',
                        C__PROPERTY__UI__PARAMS  => [
                            'p_strTable'   => 'isys_depth_unit',
                            'p_strClass'   => 'input-mini',
                            'p_bDbFieldNN' => 1
                        ],
                        C__PROPERTY__UI__DEFAULT => C__DEPTH_UNIT__INCH
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__REPORT => false,
                        C__PROPERTY__PROVIDES__SEARCH => false
                    ]
                ]
            ),
            'width'       => array_replace_recursive(
                isys_cmdb_dao_category_pattern::float(),
                [
                    C__PROPERTY__INFO   => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__FORMFACTOR_INSTALLATION_WIDTH',
                        C__PROPERTY__INFO__DESCRIPTION => 'Width'
                    ],
                    C__PROPERTY__DATA   => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_formfactor_list__installation_width'
                    ],
                    C__PROPERTY__UI     => [
                        C__PROPERTY__UI__ID => 'C__CATG__FORMFACTOR_INSTALLATION_WIDTH',
                    ],
                    C__PROPERTY__FORMAT => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'isys_export_helper',
                            'convert',
                            ['measure']
                        ],
                        C__PROPERTY__FORMAT__UNIT     => 'unit'
                    ]
                ]
            ),
            'height'      => array_replace_recursive(
                isys_cmdb_dao_category_pattern::float(),
                [
                    C__PROPERTY__INFO   => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__FORMFACTOR_INSTALLATION_HEIGHT',
                        C__PROPERTY__INFO__DESCRIPTION => 'Height'
                    ],
                    C__PROPERTY__DATA   => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_formfactor_list__installation_height'
                    ],
                    C__PROPERTY__UI     => [
                        C__PROPERTY__UI__ID => 'C__CATG__FORMFACTOR_INSTALLATION_HEIGHT',
                    ],
                    C__PROPERTY__FORMAT => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'isys_export_helper',
                            'convert',
                            ['measure']
                        ],
                        C__PROPERTY__FORMAT__UNIT     => 'unit'
                    ]
                ]
            ),
            'depth'       => array_replace_recursive(
                isys_cmdb_dao_category_pattern::float(),
                [
                    C__PROPERTY__INFO   => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__FORMFACTOR_INSTALLATION_DEPTH',
                        C__PROPERTY__INFO__DESCRIPTION => 'Depth'
                    ],
                    C__PROPERTY__DATA   => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_formfactor_list__installation_depth'
                    ],
                    C__PROPERTY__UI     => [
                        C__PROPERTY__UI__ID => 'C__CATG__FORMFACTOR_INSTALLATION_DEPTH',
                    ],
                    C__PROPERTY__FORMAT => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'isys_export_helper',
                            'convert',
                            ['measure']
                        ],
                        C__PROPERTY__FORMAT__UNIT     => 'unit'
                    ]
                ]
            ),
            'weight'      => array_replace_recursive(
                isys_cmdb_dao_category_pattern::float(),
                [
                    C__PROPERTY__INFO   => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__FORMFACTOR_INSTALLATION_WEIGHT',
                        C__PROPERTY__INFO__DESCRIPTION => 'Weight'
                    ],
                    C__PROPERTY__DATA   => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_formfactor_list__installation_weight'
                    ],
                    C__PROPERTY__UI     => [
                        C__PROPERTY__UI__ID     => 'C__CATG__FORMFACTOR_INSTALLATION_WEIGHT',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strClass' => 'input-dual-large'
                        ]
                    ],
                    C__PROPERTY__FORMAT => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'isys_export_helper',
                            'convert',
                            ['weight']
                        ],
                        C__PROPERTY__FORMAT__UNIT     => 'weight_unit'
                    ]
                ]
            ),
            'weight_unit' => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__FORMFACTOR_INSTALLATION_WEIGHT_UNIT',
                        C__PROPERTY__INFO__DESCRIPTION => 'weight unit'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD      => 'isys_catg_formfactor_list__isys_weight_unit__id',
                        C__PROPERTY__DATA__REFERENCES => [
                            'isys_weight_unit',
                            'isys_weight_unit__id'
                        ]
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID      => 'C__CATG__FORMFACTOR_INSTALLATION_WEIGHT_UNIT',
                        C__PROPERTY__UI__PARAMS  => [
                            'p_strTable'        => 'isys_weight_unit',
                            'p_strClass'        => 'input-dual-small',
                            'p_bDbFieldNN'      => 1,
                            'p_bInfoIconSpacer' => 0,
                        ],
                        C__PROPERTY__UI__DEFAULT => C__WEIGHT_UNIT__G,
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__REPORT => false,
                        C__PROPERTY__PROVIDES__SEARCH => false
                    ]
                ]
            ),
            'description' => array_replace_recursive(
                isys_cmdb_dao_category_pattern::commentary(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__LOGBOOK__DESCRIPTION',
                        C__PROPERTY__INFO__DESCRIPTION => 'Description'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_formfactor_list__description'
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_GLOBAL . C__CATG__FORMFACTOR
                    ]
                ]
            )
        ];
    } // function

    /**
     * Updates existing entity.
     *
     * @param   integer $p_category_data_id Entity's identifier
     * @param   array   $p_data             Properties in a associative array with tags as keys and their corresponding values as values.
     *
     * @return  boolean
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function save_data($p_category_data_id, $p_data)
    {
        $p_data['width']  = (isset($p_data['width'])) ? isys_convert::measure($p_data['width'], $p_data['unit']) : null;
        $p_data['height'] = (isset($p_data['height'])) ? isys_convert::measure($p_data['height'], $p_data['unit']) : null;
        $p_data['depth']  = (isset($p_data['depth'])) ? isys_convert::measure($p_data['depth'], $p_data['unit']) : null;
        $p_data['weight'] = (isset($p_data['weight'])) ? isys_convert::weight($p_data['weight'], $p_data['weight_unit']) : null;

        return parent::save_data($p_category_data_id, $p_data);
    } // function

    /**
     * @param  integer $p_objID
     */
    public function calcGroupRU($p_objID)
    {
        $l_dao   = new isys_cmdb_dao_category_s_group($this->m_db);
        $l_query = "SELECT isys_cats_group_list__isys_obj__id FROM isys_cats_group_list
			INNER JOIN isys_connection ON isys_connection__id = isys_cats_group_list__isys_connection__id
			WHERE isys_connection__isys_obj__id = " . $this->convert_sql_id($p_objID);

        $l_res = $this->retrieve($l_query);

        while ($l_row = $l_res->get_row())
        {
            $l_dao->calcRU($l_row["isys_cats_group_list__isys_obj__id"]);
        } // while
    } // function

    /**
     * Get height units for a rack object (how high is the object?).
     *
     * @param   integer $p_nObjectID
     *
     * @return  integer
     */
    public function get_rack_hu($p_nObjectID)
    {
        $l_nHU = null;

        $l_strSQL = "SELECT isys_catg_formfactor_list__rackunits FROM isys_catg_formfactor_list
			WHERE isys_catg_formfactor_list__isys_obj__id = " . $this->convert_sql_id($p_nObjectID) . ";";

        $l_ret = $this->retrieve($l_strSQL);

        if ($l_ret->num_rows() > 0)
        {
            $l_nHU = $l_ret->get_row_value('isys_catg_formfactor_list__rackunits');
        } // if

        return $l_nHU;
    } // function
} // class
?>