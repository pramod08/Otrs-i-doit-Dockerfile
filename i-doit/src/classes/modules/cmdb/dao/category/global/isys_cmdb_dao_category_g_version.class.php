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
 * DAO: Global category versions.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_dao_category_g_version extends isys_cmdb_dao_category_global
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'version';

    /**
     * Is category multi-valued or single-valued?
     *
     * @var  boolean
     */
    protected $m_multivalued = true;

    /**
     * Dynamic property handling for getting the property patchlevel
     *
     * @param   array $p_row
     *
     * @return  string
     */
    public function dynamic_property_callback_patchlevel($p_row)
    {
        global $g_comp_database;

        $l_dao = isys_cmdb_dao_category_g_version::instance($g_comp_database);

        return $l_dao->handle_property_callbacks($p_row['isys_obj__id'], 'isys_catg_version_list__hotfix');
    } // function

    /**
     * Dynamic property handling for getting the property kernel
     *
     * @param   array $p_row
     *
     * @return  string
     */
    public function dynamic_property_callback_kernel($p_row)
    {
        global $g_comp_database;

        $l_dao = isys_cmdb_dao_category_g_version::instance($g_comp_database);

        return $l_dao->handle_property_callbacks($p_row['isys_obj__id'], 'isys_catg_version_list__kernel');
    }

    /**
     * Dynamic property handling for getting the property servicepack
     *
     * @param   array $p_row
     *
     * @return  string
     */
    public function dynamic_property_callback_servicepack($p_row)
    {
        global $g_comp_database;

        $l_dao = isys_cmdb_dao_category_g_version::instance($g_comp_database);

        return $l_dao->handle_property_callbacks($p_row['isys_obj__id'], 'isys_catg_version_list__servicepack');
    } // function

    /**
     * Dynamic property handling for getting the property version
     *
     * @param   array $p_row
     *
     * @return  string
     */
    public function dynamic_property_callback_version($p_row)
    {
        global $g_comp_database;

        $l_dao = isys_cmdb_dao_category_g_version::instance($g_comp_database);

        return $l_dao->handle_property_callbacks($p_row['isys_obj__id'], 'isys_catg_version_list__title');
    } // function

    /**
     * Dynamic property handling for getting the specified property
     *
     * @param   array $p_row
     *
     * @return  string
     */
    public function handle_property_callbacks($p_obj_id, $p_field)
    {
        global $g_comp_database;

        $l_sql    = 'SELECT ' . $p_field . ' FROM isys_catg_version_list WHERE isys_catg_version_list__isys_obj__id = ' . (int) $p_obj_id . ' ORDER BY isys_catg_version_list__id';
        $l_res    = $g_comp_database->query($l_sql);
        $l_return = '';

        if (count($l_res) > 0)
        {
            while ($l_row = $g_comp_database->fetch_row_assoc($l_res))
            {
                $l_return .= $l_row[$p_field] . ', ';
            } // while
            $l_return = rtrim($l_return, ', ');
        } // if
        return $l_return;
    } // function

    /**
     * @param $p_cat_level
     * @param &$p_intOldRecStatus __status of record before update
     *
     * @version Niclas Potthast <npotthast@i-doit.org> - 2006-11-15
     */
    public function save_element($p_cat_level, &$p_intOldRecStatus, $p_create = false)
    {
        $l_intErrorCode = -1;

        $l_catdata = $this->get_general_data();

        $p_intOldRecStatus = $l_catdata["isys_catg_backup_list__status"];

        if ($p_create)
        {
            $l_id = $this->create(
                $_GET[C__CMDB__GET__OBJECT],
                C__RECORD_STATUS__NORMAL,
                $_POST['C__CATG__VERSION_TITLE'],
                $_POST['C__CATG__VERSION_SERVICEPACK'],
                $_POST['C__CATG__VERSION_PATCHLEVEL'],
                $_POST['C__CATG__VERSION_KERNEL'],
                $_POST["C__CMDB__CAT__COMMENTARY_" . $this->get_category_type() . $this->get_category_id()]
            );

            if ($l_id != false)
            {
                $this->m_strLogbookSQL = $this->get_last_query();
            } // if

            $p_cat_level = null;

            return $l_id;
        }
        else
        {
            if ($l_catdata['isys_catg_version_list__id'] != "")
            {
                $l_bRet = $this->save(
                    $l_catdata['isys_catg_version_list__id'],
                    C__RECORD_STATUS__NORMAL,
                    $_POST['C__CATG__VERSION_TITLE'],
                    $_POST['C__CATG__VERSION_SERVICEPACK'],
                    $_POST['C__CATG__VERSION_PATCHLEVEL'],
                    $_POST['C__CATG__VERSION_KERNEL'],
                    $_POST["C__CMDB__CAT__COMMENTARY_" . $this->get_category_type() . $this->get_category_id()]
                );

                $this->m_strLogbookSQL = $this->get_last_query();

                return $l_bRet;
            } // if
        } // if
        return $l_intErrorCode;
    } // function

    /**
     * Executes the query to save the category entry given by its ID $p_cat_level
     *
     * @param int    $p_cat_level
     * @param String $p_servicePack
     * @param String $p_hotfix
     * @param String $p_kernel
     * @param String $p_description
     *
     * @return boolean true, if transaction executed successfully, else false
     * @author Dennis Bluemer <dbluemer@i-doit.org>
     */
    public function save($p_cat_level, $p_status = C__RECORD_STATUS__NORMAL, $p_title = null, $p_servicePack = null, $p_hotfix = null, $p_kernel = null, $p_description)
    {

        $l_strSql = "UPDATE isys_catg_version_list SET " . "isys_catg_version_list__title  = " . $this->convert_sql_text(
                $p_title
            ) . ", " . "isys_catg_version_list__servicepack  = " . $this->convert_sql_text(
                $p_servicePack
            ) . ", " . "isys_catg_version_list__hotfix  = " . $this->convert_sql_text($p_hotfix) . ", " . "isys_catg_version_list__kernel  = " . $this->convert_sql_text(
                $p_kernel
            ) . ", " . "isys_catg_version_list__description  = " . $this->convert_sql_text(
                $p_description
            ) . ", " . "isys_catg_version_list__status = " . $this->convert_sql_int($p_status) . " " . "WHERE isys_catg_version_list__id = " . $this->convert_sql_id(
                $p_cat_level
            );

        if ($this->update($l_strSql))
        {
            return $this->apply_update();
        }
        else
        {
            return false;
        } // if
    } // function

    /**
     * Executes the query to create the category entry referenced by isys_catd_version__id $p_fk_id
     *
     * @param int    $p_objID
     * @param String $p_servicePack
     * @param String $p_hotfix
     * @param String $p_kernel
     * @param String $p_description
     *
     * @return int the newly created ID or false
     * @author Dennis Bluemer <dbluemer@i-doit.org>
     */
    public function create($p_objID, $p_rec_status = C__RECORD_STATUS__NORMAL, $p_title = null, $p_servicePack = null, $p_hotfix = null, $p_kernel = null, $p_description = '')
    {

        $l_strSql = "INSERT INTO isys_catg_version_list SET " . "isys_catg_version_list__title = " . $this->convert_sql_text(
                $p_title
            ) . ", " . "isys_catg_version_list__servicepack  = " . $this->convert_sql_text(
                $p_servicePack
            ) . ", " . "isys_catg_version_list__hotfix  = " . $this->convert_sql_text($p_hotfix) . ", " . "isys_catg_version_list__kernel  = " . $this->convert_sql_text(
                $p_kernel
            ) . ", " . "isys_catg_version_list__description  = " . $this->convert_sql_text(
                $p_description
            ) . ", " . "isys_catg_version_list__status = " . $this->convert_sql_int($p_rec_status) . ", " . "isys_catg_version_list__isys_obj__id = " . $this->convert_sql_id(
                $p_objID
            );

        if ($this->update($l_strSql) && $this->apply_update())
        {
            return $this->get_last_insert_id();
        }
        else
        {
            return false;
        } // if
    } // function

    protected function dynamic_properties()
    {
        return [
            '_title'       => [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CATG__VERSION_TITLE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Versionsnummer'
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        $this,
                        'dynamic_property_callback_version'
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__LIST   => true,
                    C__PROPERTY__PROVIDES__REPORT => false
                ]
            ],
            '_servicepack' => [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CATG__VERSION_SERVICEPACK',
                    C__PROPERTY__INFO__DESCRIPTION => 'Servicepack'
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        $this,
                        'dynamic_property_callback_servicepack'
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__LIST   => true,
                    C__PROPERTY__PROVIDES__REPORT => false
                ]
            ],
            '_kernel'      => [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CATG__VERSION_KERNEL',
                    C__PROPERTY__INFO__DESCRIPTION => 'Kernel'
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        $this,
                        'dynamic_property_callback_kernel'
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__LIST   => true,
                    C__PROPERTY__PROVIDES__REPORT => false
                ]
            ],
            '_patchlevel'  => [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CATG__VERSION_PATCHLEVEL',
                    C__PROPERTY__INFO__DESCRIPTION => 'Patchlevel'
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        $this,
                        'dynamic_property_callback_patchlevel'
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__LIST   => true,
                    C__PROPERTY__PROVIDES__REPORT => false
                ]
            ],
        ];
    } // function

    /**
     * Method for returning the properties.
     *
     * @return  array
     * @author  Van Quyen Hoang <qhoang@synetics.de>
     */
    protected function properties()
    {
        return [
            'title'       => array_replace_recursive(
                isys_cmdb_dao_category_pattern::text(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CATG__VERSION_TITLE',
                        C__PROPERTY__INFO__DESCRIPTION => 'Versionnumber'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_version_list__title'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID => 'C__CATG__VERSION_TITLE'
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__LIST => false,
                    ]
                ]
            ),
            'servicepack' => array_replace_recursive(
                isys_cmdb_dao_category_pattern::text(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CATG__VERSION_SERVICEPACK',
                        C__PROPERTY__INFO__DESCRIPTION => 'Servicepack'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_version_list__servicepack'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID => 'C__CATG__VERSION_SERVICEPACK'
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__LIST => false,
                    ]
                ]
            ),
            'kernel'      => array_replace_recursive(
                isys_cmdb_dao_category_pattern::text(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CATG__VERSION_KERNEL',
                        C__PROPERTY__INFO__DESCRIPTION => 'Kernel'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_version_list__kernel'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID => 'C__CATG__VERSION_KERNEL'
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__LIST => false,
                    ]
                ]
            ),
            'patchlevel'  => array_replace_recursive(
                isys_cmdb_dao_category_pattern::text(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CATG__VERSION_PATCHLEVEL',
                        C__PROPERTY__INFO__DESCRIPTION => 'Patchlevel'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_version_list__hotfix'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID => 'C__CATG__VERSION_PATCHLEVEL'
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__LIST => false,
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
                        C__PROPERTY__DATA__FIELD => 'isys_catg_version_list__description'
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_GLOBAL . C__CATG__VERSION
                    ]
                ]
            )
        ];
    }

    /**
     * Synchronizes properties from an import with the database.
     *
     * @param   array   $p_category_data
     * @param   integer $p_object_id
     * @param   integer $p_status
     *
     * @return  mixed
     */
    public function sync($p_category_data, $p_object_id, $p_status = 1 /* isys_import_handler_cmdb::C__CREATE */)
    {
        $l_indicator = false;

        if (is_array($p_category_data) && isset($p_category_data['properties']))
        {
            $this->m_sync_catg_data = $p_category_data;
            switch ($p_status)
            {
                case isys_import_handler_cmdb::C__CREATE:
                    $p_category_data['data_id'] = $this->create(
                        $p_object_id,
                        C__RECORD_STATUS__NORMAL,
                        $p_category_data['properties']['title'][C__DATA__VALUE],
                        $p_category_data['properties']['servicepack'][C__DATA__VALUE],
                        $p_category_data['properties']['patches'][C__DATA__VALUE],
                        $p_category_data['properties']['kernel'][C__DATA__VALUE],
                        $p_category_data['properties']['description'][C__DATA__VALUE]
                    );

                    if ($p_category_data['data_id'])
                    {
                        $l_indicator = true;
                    } // if
                    break;

                case isys_import_handler_cmdb::C__UPDATE:
                    $l_indicator = $this->save(
                        $p_category_data['data_id'],
                        C__RECORD_STATUS__NORMAL,
                        $p_category_data['properties']['title'][C__DATA__VALUE],
                        $p_category_data['properties']['servicepack'][C__DATA__VALUE],
                        $p_category_data['properties']['patches'][C__DATA__VALUE],
                        $p_category_data['properties']['kernel'][C__DATA__VALUE],
                        $p_category_data['properties']['description'][C__DATA__VALUE]
                    );
                    break;
            } // switch
        } // if

        return ($l_indicator === true) ? $p_category_data['data_id'] : false;
    } // function
} // class