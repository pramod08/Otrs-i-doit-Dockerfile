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
 * DAO: specific category for group type
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Van Quyen Hoang <qhoang@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_dao_category_s_group_type extends isys_cmdb_dao_category_specific
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'group_type';

    /**
     * Is category multi-valued or single-valued?
     *
     * @var  boolean
     */
    protected $m_multivalued = false;

    /**
     * Callback method for property type.
     *
     * @return  array
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function callback_property_type()
    {
        return [
            '0' => 'LC__CMDB__CATS__GROUP_TYPE__TYPE_STATIC',
            '1' => 'LC__CMDB__CATS__GROUP_TYPE__TYPE_DYNAMIC'
        ];
    }

    /**
     * Callback method for property report.
     *
     * @return  array
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function callback_property_report()
    {
        global $g_comp_database_system;

        $l_dao            = new isys_report_dao($g_comp_database_system);
        $l_reports_custom = $l_dao->get_reports(
            null,
            isys_auth_report::instance()
                ->get_allowed_reports()
        );

        $l_reports_arr = [];

        if ($l_reports_custom->num_rows() > 0)
        {
            while ($l_row = $l_reports_custom->get_row())
            {
                $l_reports_arr[$l_row['isys_report_category__title']][$l_row['isys_report__id']] = $l_row['isys_report__title'];
            } // while
            $l_reports_arr = array_map(
                function ($l_item)
                {
                    asort($l_item);

                    return $l_item;
                },
                $l_reports_arr
            );
        } // if

        return $l_reports_arr;
    } // function

    /**
     * Save global category access element
     *
     * @param      $p_cat_level        level to save, default 0
     * @param      &$p_intOldRecStatus __status of record before update
     * @param bool $p_create
     *
     * @return bool|null
     * @version Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function save_element(&$p_cat_level = null, &$p_intOldRecStatus)
    {
        $l_catdata         = $this->get_result()
            ->__to_array();
        $p_intOldRecStatus = $l_catdata["isys_cats_group_type_list__status"];

        if (!isset($l_catdata['isys_cats_group_type_list__id']))
        {
            $l_catdata['isys_cats_group_type_list__id'] = $this->create(
                $_GET[C__CMDB__GET__OBJECT],
                C__RECORD_STATUS__NORMAL,
                $_POST['C__CATS__OBJECT_GROUP__TYPE'],
                $_POST['C__CATS__OBJECT_GROUP__REPORT'],
                $_POST["C__CMDB__CAT__COMMENTARY_" . $this->get_category_type() . $this->get_category_id()]
            );

            if ($l_catdata['isys_cats_group_type_list__id'])
            {
                $this->m_strLogbookSQL .= $this->get_last_query();
                $p_cat_level = 1;
            } // if
        } // if

        $l_bRet = $this->save(
            $l_catdata['isys_cats_group_type_list__id'],
            C__RECORD_STATUS__NORMAL,
            $_POST['C__CATS__OBJECT_GROUP__TYPE'],
            $_POST['C__CATS__OBJECT_GROUP__REPORT'],
            $_POST["C__CMDB__CAT__COMMENTARY_" . $this->get_category_type() . $this->get_category_id()]
        );
        $this->m_strLogbookSQL .= $this->get_last_query();

        return $l_bRet;
    } // function

    /**
     * @param int       $p_cat_level
     * @param array|int $p_newRecStatus
     * @param int       $p_type
     * @param null      $p_report_id
     * @param string    $p_description
     *
     * @return bool
     * @author Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function save($p_cat_level, $p_newRecStatus = C__RECORD_STATUS__NORMAL, $p_type = 0, $p_report_id = null, $p_description = '')
    {
        $l_strSql = "UPDATE isys_cats_group_type_list SET " . "isys_cats_group_type_list__type = " . $this->convert_sql_int(
                $p_type
            ) . ", " . "isys_cats_group_type_list__isys_report__id  = " . $this->convert_sql_id(
                $p_report_id
            ) . ", " . "isys_cats_group_type_list__description = " . $this->convert_sql_text(
                $p_description
            ) . ", " . "isys_cats_group_type_list__status = " . $p_newRecStatus . " " . "WHERE isys_cats_group_type_list__id = " . $p_cat_level . ";";

        if ($this->update($l_strSql) && $this->apply_update())
        {
            if ($p_type == 1)
            {
                $l_objID = $this->get_data($p_cat_level)
                    ->get_row_value('isys_cats_group_type_list__isys_obj__id');

                /**
                 * @var $l_dao_group isys_cmdb_dao_category_s_group
                 */
                $l_dao_group = isys_cmdb_dao_category_s_group::instance($this->get_database_component());

                $l_res = $l_dao_group->get_connected_objects($l_objID);
                if ($l_res->num_rows() > 0)
                {
                    $l_deleted_objects = [];
                    while ($l_row = $l_res->get_row())
                    {
                        // Delete entry and relations
                        $this->delete_entry($l_row['isys_cats_group_list__id'], 'isys_cats_group_list');
                        $l_deleted_objects[] = $l_row['isys_obj__title'];
                    } // while

                    $l_changes = [
                        'isys_cmdb_dao_category_s_group::object' => [
                            'from' => implode(', ', $l_deleted_objects),
                            'to'   => ''
                        ]
                    ];

                    $l_event_manager      = isys_event_manager::getInstance();
                    $l_changes_compressed = serialize($l_changes);

                    $l_event_manager->triggerCMDBEvent(
                        'C__LOGBOOK_EVENT__CATEGORY_CHANGED',
                        '',
                        $l_objID,
                        isys_glob_get_param(C__CMDB__GET__OBJECTTYPE),
                        _L('LC__OBJTYPE__GROUP'),
                        $l_changes_compressed
                    );
                } // if
            } // if
            return true;
        }
        else
        {
            return false;
        } // if
    } // function

    /**
     * @param array  $p_objID
     * @param int    $p_newRecStatus
     * @param int    $p_type
     * @param null   $p_report_id
     * @param string $p_description
     *
     * @return bool|int|mixed
     * @author Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function create($p_objID, $p_newRecStatus = C__RECORD_STATUS__NORMAL, $p_type = 0, $p_report_id = null, $p_description = '')
    {
        $l_strSql = "INSERT IGNORE INTO isys_cats_group_type_list SET " . "isys_cats_group_type_list__isys_obj__id = " . $this->convert_sql_id(
                $p_objID
            ) . ", " . "isys_cats_group_type_list__type = " . $this->convert_sql_int(
                $p_type
            ) . ", " . "isys_cats_group_type_list__isys_report__id  = " . $this->convert_sql_id(
                $p_report_id
            ) . ", " . "isys_cats_group_type_list__description = " . $this->convert_sql_text(
                $p_description
            ) . ", " . "isys_cats_group_type_list__status = " . $p_newRecStatus . "; ";

        if ($this->update($l_strSql) && $this->apply_update())
        {
            if ($p_type == 1)
            {
                /**
                 * @var $l_dao_group isys_cmdb_dao_category_s_group
                 */
                $l_dao_group = isys_cmdb_dao_category_s_group::instance($this->get_database_component());

                $l_res = $l_dao_group->get_connected_objects($p_objID);
                if ($l_res->num_rows() > 0)
                {
                    $l_deleted_objects = [];
                    while ($l_row = $l_res->get_row())
                    {
                        // Delete entry and relations
                        $this->delete_entry($l_row['isys_cats_group_list__id'], 'isys_cats_group_list');
                        $l_deleted_objects[] = $l_row['isys_obj__title'];
                    } // while

                    $l_changes = [
                        'isys_cmdb_dao_category_s_group::object' => [
                            'from' => implode(', ', $l_deleted_objects),
                            'to'   => ''
                        ]
                    ];

                    $l_event_manager      = isys_event_manager::getInstance();
                    $l_changes_compressed = serialize($l_changes);

                    $l_event_manager->triggerCMDBEvent(
                        'C__LOGBOOK_EVENT__CATEGORY_CHANGED',
                        '',
                        $p_objID,
                        isys_glob_get_param(C__CMDB__GET__OBJECTTYPE),
                        _L('LC__OBJTYPE__GROUP'),
                        $l_changes_compressed
                    );
                } // if
            } // if
            return $this->get_last_insert_id();
        }
        else
        {
            return false;
        } // if
    } // function

    /**
     * Method for returning the properties.
     *
     * @author Van Quyen Hoang <qhoang@i-doit.de>
     * @return  array
     */
    protected function properties()
    {
        return [
            'group_type'  => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'Typ',
                        C__PROPERTY__INFO__DESCRIPTION => 'Typ'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_cats_group_type_list__type'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATS__OBJECT_GROUP__TYPE',
                        C__PROPERTY__UI__PARAMS => [
                            'p_arData' => new isys_callback(
                                [
                                    'isys_cmdb_dao_category_s_group_type',
                                    'callback_property_type'
                                ]
                            ),
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__SEARCH     => false,
                        C__PROPERTY__PROVIDES__MULTIEDIT  => false,
                        C__PROPERTY__PROVIDES__VALIDATION => false,
                        C__PROPERTY__PROVIDES__LIST       => false,
                        C__PROPERTY__PROVIDES__REPORT     => false
                    ]
                ]
            ),
            'report'      => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'Report',
                        C__PROPERTY__INFO__DESCRIPTION => 'Report'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_cats_group_type_list__isys_report__id'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATS__OBJECT_GROUP__REPORT',
                        C__PROPERTY__UI__PARAMS => [
                            'p_arData' => new isys_callback(
                                [
                                    'isys_cmdb_dao_category_s_group_type',
                                    'callback_property_report'
                                ]
                            ),
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__SEARCH     => false,
                        C__PROPERTY__PROVIDES__MULTIEDIT  => false,
                        C__PROPERTY__PROVIDES__VALIDATION => false,
                        C__PROPERTY__PROVIDES__LIST       => false,
                        C__PROPERTY__PROVIDES__REPORT     => false
                    ]
                ]
            ),
            'description' => array_replace_recursive(
                isys_cmdb_dao_category_pattern::commentary(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__LOGBOOK__DESCRIPTION',
                        C__PROPERTY__INFO__DESCRIPTION => 'Description'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_cats_group_type_list__description'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_SPECIFIC . C__CATS__GROUP_TYPE
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__REPORT => false,
                        C__PROPERTY__PROVIDES__LIST   => false
                    ],
                ]
            )
        ];
    } // function

} // class
?>