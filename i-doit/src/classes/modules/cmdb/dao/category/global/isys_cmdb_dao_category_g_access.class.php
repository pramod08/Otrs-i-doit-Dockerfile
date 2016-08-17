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
 * DAO: global category for accesses.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Niclas Potthast <npotthast@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_dao_category_g_access extends isys_cmdb_dao_category_global
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'access';

    /**
     * Is category multi-valued or single-valued?
     *
     * @var  boolean
     */
    protected $m_multivalued = true;

    /**
     * Dynamic property handling for getting the primary access url
     *
     * @param   array $p_row
     *
     * @return  string
     */
    public function dynamic_property_callback_url($p_row)
    {
        $l_res = $this->get_primary_element($p_row['__id__']);

        if ($l_res->num_rows() > 0)
        {
            $l_data = $l_res->get_row();

            return $this->format_url($l_data['isys_catg_access_list__url'], $p_row['isys_obj__id']);
        } // if

        return isys_tenantsettings::get('gui.empty_value', '-');
    } // function

    /**
     * Save global category access element
     *
     * @param  int $p_cat_level        level to save, default 0
     * @param  int &$p_intOldRecStatus __status of record before update
     * @param bool $p_create
     *
     * @return bool|null
     * @version Dennis Bluemer <dbluemer@i-doit.org>
     */
    public function save_element(&$p_cat_level, &$p_intOldRecStatus, $p_create = false)
    {
        $l_catdata         = $this->get_result()
            ->__to_array();
        $l_bRet            = true;
        $p_intOldRecStatus = $l_catdata["isys_catg_access_list__status"];

        if ($p_create || !$l_catdata["isys_catg_access_list__id"])
        {
            if (isys_glob_get_param(
                    C__CMDB__GET__CATG
                ) == C__CATG__OVERVIEW && $_POST['C__CATG__ACCESS_TITLE'] == "" && $_POST['C__CATG__ACCESS_TYPE'] == -1 && $_POST['C__CATG__ACCESS_URL'] == "" && $_POST['C__CATG__ACCESS_PRIMARY'] == "0"
            ) return null;

            $l_id = $this->create(
                $_GET[C__CMDB__GET__OBJECT],
                C__RECORD_STATUS__NORMAL,
                $_POST['C__CATG__ACCESS_TITLE'],
                $_POST['C__CATG__ACCESS_TYPE'],
                $_POST['C__CATG__ACCESS_URL'],
                $_POST['C__CATG__ACCESS_PRIMARY'],
                $_POST["C__CMDB__CAT__COMMENTARY_" . $this->get_category_type() . $this->get_category_id()]
            );

            if ($l_id)
            {
                $this->m_strLogbookSQL = $this->get_last_query();
                $p_cat_level           = 1;
                $l_bRet                = true;
            }
        }
        else
        {
            $l_bRet = $this->save(
                $l_catdata['isys_catg_access_list__id'],
                C__RECORD_STATUS__NORMAL,
                $_POST['C__CATG__ACCESS_TITLE'],
                $_POST['C__CATG__ACCESS_TYPE'],
                $_POST['C__CATG__ACCESS_URL'],
                $_POST['C__CATG__ACCESS_PRIMARY'],
                $_POST["C__CMDB__CAT__COMMENTARY_" . $this->get_category_type() . $this->get_category_id()]
            );

            $this->m_strLogbookSQL = $this->get_last_query();
            $l_id                  = $l_catdata['isys_catg_access_list__id'];
        }

        if ($_POST['C__CATG__ACCESS_PRIMARY'] == "1")
        {

            // if the entry is primary we set all other entries for this object to NOT primary
            // toggle all other isys_catg_access_list__primary to zero
            // which belong to the actual object
            $l_strSql = "UPDATE isys_catg_access_list SET " . "isys_catg_access_list__primary = 0 " . // not primary
                "WHERE isys_catg_access_list__id <> " . $this->convert_sql_id($l_id) . " " . // all but not the actual primary entry
                "AND isys_catg_access_list__isys_obj__id = " . $this->convert_sql_id($_GET[C__CMDB__GET__OBJECT]);

            $this->m_strLogbookSQL .= "\n" . $l_strSql;
            $this->update($l_strSql) && $this->apply_update();
        }

        return $l_bRet;
    } // function

    /**
     * Executes the query to save the category entry given by its ID $p_cat_level.
     *
     * @param   integer $p_cat_level
     * @param   integer $p_newRecStatus
     * @param   string  $p_title
     * @param   integer $p_accessTypeID
     * @param   string  $p_url
     * @param   integer $p_primary
     * @param   string  $p_description
     *
     * @return  boolean
     * @author  Dennis Bluemer <dbluemer@i-doit.org>
     */
    public function save($p_cat_level, $p_newRecStatus, $p_title, $p_accessTypeID, $p_url, $p_primary, $p_description)
    {
        $l_strSql = "UPDATE isys_catg_access_list SET
			isys_catg_access_list__title = " . $this->convert_sql_text($p_title) . ",
			isys_catg_access_list__isys_access_type__id = " . $this->convert_sql_id($p_accessTypeID) . ",
			isys_catg_access_list__url  = " . $this->convert_sql_text($p_url) . ",
			isys_catg_access_list__primary = " . $this->convert_sql_boolean($p_primary) . ",
			isys_catg_access_list__description = " . $this->convert_sql_text($p_description) . ",
			isys_catg_access_list__status = " . $this->convert_sql_int($p_newRecStatus) . "
			WHERE isys_catg_access_list__id = " . $this->convert_sql_id($p_cat_level) . "";

        return $this->update($l_strSql) && $this->apply_update();
    } // function

    /**
     * Executes the query to create the category entry referenced by isys_catg_access__id $p_fk_id
     *
     * @param int    $p_fk_id
     * @param int    $p_newRecStatus
     * @param String $p_title
     * @param String $p_manufacturerID
     * @param int    $p_frequencyID
     * @param int    $p_typeID
     * @param String $p_description
     *
     * @return int the newly created ID or false
     * @author Dennis Bluemer <dbluemer@i-doit.org>
     */
    public function create($p_objID, $p_newRecStatus, $p_title, $p_accessTypeID, $p_url, $p_primary, $p_description)
    {
        $l_id = $this->create_connector('isys_catg_access_list', $p_objID);
        if ($this->save($l_id, $p_newRecStatus, $p_title, $p_accessTypeID, $p_url, $p_primary, $p_description))
        {
            return $l_id;
        }
        return false;
    } // function

    /**
     * Return result set for current primary access.
     *
     * @param   integer $p_object_id
     *
     * @return  isys_component_dao_result
     * @author  Niclas Potthast <npotthast@i-doit.org>
     */
    public function get_primary_element($p_object_id = null)
    {
        $l_sql = "SELECT * FROM isys_catg_access_list
			LEFT OUTER JOIN isys_access_type ON isys_access_type__id = isys_catg_access_list__isys_access_type__id
			WHERE isys_catg_access_list__isys_obj__id = " . $this->convert_sql_id($p_object_id) . "
			AND isys_catg_access_list__primary = 1
			LIMIT 1;";

        return $this->retrieve($l_sql);
    }

    /**
     * Return URL from access list, or null.
     *
     * @param   integer $p_object_id
     *
     * @return  mixed
     * @author  Niclas Potthast <npotthast@i-doit.org> - 2006-03-02
     */
    public function get_url($p_object_id = null)
    {
        $l_res = $this->get_primary_element($p_object_id);

        if (count($l_res))
        {
            return $l_res->get_row_value('isys_catg_access_list__url');
        } // if

        return null;
    } // function

    /**
     * Replaces all placeholders of the given url
     *
     * @param      $p_url
     * @param null $p_objID
     *
     * @return string
     * @author Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function format_url($p_url, $p_objID = null)
    {
        global $g_comp_database;

        $l_dao_ip          = isys_cmdb_dao_category_g_ip::instance($g_comp_database);
        $l_primary_ip_data = $l_dao_ip->get_primary_ip($p_objID)
            ->get_row();

        $l_base_dir = rtrim(isys_helper_link::get_base(), '/');

        $l_replace_pairs = [
            '%idoit_host%' => $l_base_dir,
            '%hostname%'   => $l_primary_ip_data['isys_catg_ip_list__hostname'],
            '%ipaddress%'  => $l_primary_ip_data['isys_cats_net_ip_addresses_list__title'],
            '%objid%'      => $p_objID
        ];

        if (strpos(' ' . $p_url, '%ipaddress#') && $p_objID)
        {
            preg_match_all("/\%ipaddress\#\d*\%/", $p_url, $l_matches);
            if (isset($l_matches[0]))
            {
                $l_data = isys_cmdb_dao_category_data::initialize($p_objID)
                    ->path('C__CATG__IP')
                    ->data()
                    ->pluck('hostaddress')
                    ->toArray();

                foreach ($l_matches[0] AS $l_key => $l_match)
                {
                    $l_pos = ((int) substr($l_match, strpos($l_match, '#') + 1, -1) - 1);
                    if (isset($l_data[$l_pos]))
                    {
                        $l_replace_pairs['%ipaddress#' . ($l_pos + 1) . '%'] = $l_data[$l_pos];
                    } // if
                } // foreach
                isys_cmdb_dao_category_data::free($p_objID);
            } // if
        } // if

        return strtr($p_url, $l_replace_pairs);
    }

    public function pre_rank($p_list_id, $p_direction, $p_table)
    {

        if ($p_direction == C__CMDB__RANK__DIRECTION_DELETE)
        {
            $l_primary_element = $this->get_data($p_list_id, $_GET[C__CMDB__GET__OBJECT], " AND isys_catg_access_list__primary = 1")
                ->get_row();

            if ($l_primary_element)
            {
                $this->set_primary($l_primary_element['isys_catg_access_list__id'], "unprimary");
            }
        }
    } // function

    public function post_rank($p_list_id, $p_direction, $p_table)
    {
        $l_rows            = $this->get_data(null, $_GET[C__CMDB__GET__OBJECT], null, null, C__RECORD_STATUS__NORMAL);
        $l_primary_element = $this->get_primary_element($_GET[C__CMDB__GET__OBJECT])
            ->get_row();
        $l_num             = $l_rows->num_rows();

        if ($l_num && !$l_primary_element)
        {
            $l_row = $l_rows->get_row();
            $this->set_primary($l_row["isys_catg_access_list__id"], "primary");
        }
    } // function

    public function set_primary($p_list_id, $p_mode = null)
    {

        $l_sql = "UPDATE isys_catg_access_list SET isys_catg_access_list__primary = ";

        switch ($p_mode)
        {
            case 'primary':
                $l_sql .= "1 WHERE isys_catg_access_list__id = " . $p_list_id . ";";
                $this->update($l_sql);
                break;
            default:
            case 'unprimary':
                $l_sql .= "0 WHERE isys_catg_access_list__id  = " . $p_list_id . ";";
                $this->update($l_sql);
                break;
        }
        $this->apply_update();
    }

    /**
     * @return  array
     */
    protected function dynamic_properties()
    {
        return [
            '_url' => [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__ACCESS_URL',
                    C__PROPERTY__INFO__DESCRIPTION => 'URL'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD => 'isys_obj__id'
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        $this,
                        'dynamic_property_callback_url'
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__LIST   => true,
                    C__PROPERTY__PROVIDES__REPORT => false
                ]
            ]
        ];
    }

    /**
     * Method for returning the properties.
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    protected function properties()
    {
        return [
            'title'         => array_replace_recursive(
                isys_cmdb_dao_category_pattern::text(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__TITLE',
                        C__PROPERTY__INFO__DESCRIPTION => 'Title'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_access_list__title'
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID => 'C__CATG__ACCESS_TITLE'
                    ]
                ]
            ),
            'type'          => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog_plus(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__ACCESS_TYPE',
                        C__PROPERTY__INFO__DESCRIPTION => 'Access type'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD      => 'isys_catg_access_list__isys_access_type__id',
                        C__PROPERTY__DATA__REFERENCES => [
                            'isys_access_type',
                            'isys_access_type__id'
                        ]
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID     => 'C__CATG__ACCESS_TYPE',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strTable' => 'isys_access_type'
                        ]
                    ]
                ]
            ),
            'url'           => array_replace_recursive(
                isys_cmdb_dao_category_pattern::text(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__ACCESS_URL',
                        C__PROPERTY__INFO__DESCRIPTION => 'URL'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_access_list__url'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID => 'C__CATG__ACCESS_URL'
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__REPORT => true,
                        C__PROPERTY__PROVIDES__LIST   => false
                    ]
                ]
            ),
            'formatted_url' => array_replace_recursive(
                isys_cmdb_dao_category_pattern::text(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__ACCESS_URL',
                        C__PROPERTY__INFO__DESCRIPTION => 'URL'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_access_list__id'
                    ],
                    C__PROPERTY__FORMAT   => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'isys_export_helper',
                            'access_property_formatted_url'
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__REPORT     => false,
                        C__PROPERTY__PROVIDES__LIST       => false,
                        C__PROPERTY__PROVIDES__IMPORT     => false,
                        C__PROPERTY__PROVIDES__MULTIEDIT  => false,
                        C__PROPERTY__PROVIDES__SEARCH     => false,
                        C__PROPERTY__PROVIDES__VALIDATION => false,
                        C__PROPERTY__PROVIDES__VIRTUAL    => true,
                        C__PROPERTY__PROVIDES__EXPORT     => true
                    ]
                ]
            ),
            'primary'       => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__ACCESS_PRIMARY',
                        C__PROPERTY__INFO__DESCRIPTION => 'Primary?'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_access_list__primary'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID      => 'C__CATG__ACCESS_PRIMARY',
                        C__PROPERTY__UI__PARAMS  => [
                            'p_arData'     => serialize(get_smarty_arr_YES_NO()),
                            'p_bDbFieldNN' => 1
                        ],
                        C__PROPERTY__UI__DEFAULT => 1,
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__SEARCH => false
                    ],
                    C__PROPERTY__FORMAT   => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'isys_export_helper',
                            'get_yes_or_no'
                        ]
                    ]
                ]
            ),
            'description'   => array_replace_recursive(
                isys_cmdb_dao_category_pattern::commentary(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__DESCRIPTION',
                        C__PROPERTY__INFO__DESCRIPTION => 'Description'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_access_list__description'
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_GLOBAL . C__CATG__ACCESS
                    ]
                ]
            )
        ];
    }

    /**
     * @param array $p_category_data
     * @param int   $p_object_id
     * @param int   $p_status
     *
     * @return bool|int
     */
    public function sync($p_category_data, $p_object_id, $p_status = 1 /* isys_import_handler_cmdb::C__CREATE */)
    {
        if (is_array($p_category_data) && isset($p_category_data['properties']))
        {
            switch ($p_status)
            {
                case isys_import_handler_cmdb::C__CREATE:
                    if ($p_object_id > 0)
                    {
                        return $this->create(
                            $p_object_id,
                            C__RECORD_STATUS__NORMAL,
                            $p_category_data['properties']['title'][C__DATA__VALUE],
                            $p_category_data['properties']['type'][C__DATA__VALUE],
                            $p_category_data['properties']['url'][C__DATA__VALUE],
                            $p_category_data['properties']['primary'][C__DATA__VALUE],
                            $p_category_data['properties']['description'][C__DATA__VALUE]
                        );
                    }
                    break;
                case isys_import_handler_cmdb::C__UPDATE:
                    if ($p_category_data['data_id'] > 0)
                    {
                        $this->save(
                            $p_category_data['data_id'],
                            C__RECORD_STATUS__NORMAL,
                            $p_category_data['properties']['title'][C__DATA__VALUE],
                            $p_category_data['properties']['type'][C__DATA__VALUE],
                            $p_category_data['properties']['url'][C__DATA__VALUE],
                            $p_category_data['properties']['primary'][C__DATA__VALUE],
                            $p_category_data['properties']['description'][C__DATA__VALUE]
                        );

                        return $p_category_data['data_id'];
                    }
                    break;
            } // switch
        } // if

        return false;
    } // function
} // class
?>