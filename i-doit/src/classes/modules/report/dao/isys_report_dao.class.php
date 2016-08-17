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
 * Template Module Dao.
 *
 * @package     i-doit
 * @subpackage  Modules
 * @author      Dennis Stücken <dstuecken@i-doit.org>
 * @version     0.9
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_report_dao extends isys_module_dao
{
    private $m_hidden_columns = [
        "isys_cats_person_list__user_pass" => true,
        "isys_mandator__db_host"           => true,
        "isys_mandator__db_user"           => true,
        "isys_mandator__db_pass"           => true,
        "isys_ldap__password"              => true,
        "isys_logbook__changes"            => true
    ];

    /**
     * @param $col
     *
     * @return bool
     */
    public function isHiddenColumn($col)
    {
        return isset($this->m_hidden_columns[$col]);
    }

    /**
     * Method for creating a new report.
     *
     * @param   string  $p_title
     * @param   string  $p_description
     * @param   string  $p_query
     * @param   null    $deprecated
     * @param   boolean $p_standard
     * @param   boolean $p_user_specific
     * @param   integer $p_report_category_id
     * @param   string  $p_querybuilder_json
     *
     * @return  mixed  boolean false or integer
     */
    public function createReport($p_title, $p_description, $p_query, $deprecated = null, $p_standard = false, $p_user_specific = false, $p_report_category_id = null, $p_querybuilder_json = null)
    {
        global $g_comp_session;

        $l_update = "INSERT INTO isys_report SET
			isys_report__title = " . $this->convert_sql_text($p_title) . ",
			isys_report__description = " . $this->convert_sql_text($p_description) . ",
			isys_report__query = " . $this->convert_sql_text($p_query) . ",
			isys_report__type = '" . ($p_standard ? 's' : 'c') . "',
			isys_report__datetime = NOW(),
			isys_report__last_edited = NOW(),
			isys_report__mandator = " . (int) $g_comp_session->get_mandator_id() . ",
			isys_report__user = " . (int) $g_comp_session->get_user_id() . ",
			isys_report__isys_report_category__id = " . $this->convert_sql_id($p_report_category_id) . ",
			isys_report__querybuilder_data = " . $this->convert_sql_text($p_querybuilder_json) . ",
			isys_report__user_specific = 0;";

        if ($this->update($l_update) && $this->apply_update())
        {
            return $this->get_last_insert_id();
        } // if

        return false;
    } // function

    /**
     * Deletes an report.
     *
     * @param   integer $p_id
     *
     * @return  boolean
     */
    public function deleteReport($p_id)
    {
        $l_update = "DELETE FROM isys_report WHERE isys_report__id = " . $this->convert_sql_id($p_id) . ";";

        return ($this->update($l_update) && $this->apply_update());
    } // function

    /**
     * Get data method. Unused.
     *
     * @return  null
     */
    public function get_data()
    {
        return null;
    } // function

    /**
     * Retrieve a single report.
     *
     * @global  isys_component_session $g_comp_session
     *
     * @param   integer                $p_id
     *
     * @return  array
     * @throws  Exception
     */
    public function get_report($p_id)
    {
        global $g_comp_session;

        $l_row = $this->retrieve("SELECT * FROM isys_report WHERE isys_report__id = " . (int) $p_id . ";")
            ->get_row();

        if (empty($l_row))
        {
            return [];
        } // if

        if ($l_row['isys_report__mandator'] == $g_comp_session->get_mandator_id())
        {
            return $l_row;
        } // if

        throw new Exception('This is not your report!');
    } // function

    /**
     * Method for retrieving all reports by type.
     * Types can be "C__REPORT__CUSTOM" or "C__REPORT__STANDARD".
     *
     * @param   integer $p_type
     * @param   array   $p_allowed_reports
     *
     * @return  isys_component_dao_result
     */
    public function get_reports($p_type = null, $p_allowed_reports = null, $p_report_category = null)
    {
        global $g_comp_session;

        $l_sql = "SELECT *, (CASE isys_report__user_specific WHEN 1 THEN '" . _L('LC__REPORT__LIST__ONLY_YOU') . "' WHEN 0 THEN '" . _L('LC__UNIVERSAL__GLOBAL') . "' END) AS 'user_specific',
				(CASE WHEN isys_report__querybuilder_data IS NULL OR isys_report__querybuilder_data = '' THEN '0' ELSE '1' END) AS 'with_qb',
				isys_report_category__title AS 'category_title'
				FROM isys_report
				LEFT JOIN isys_report_category ON isys_report_category__id = isys_report__isys_report_category__id ";

        // This condition is needed to only display reports the user is allowed to see.
        if (is_array($p_allowed_reports) && count($p_allowed_reports) > 0)
        {
            $l_rights_condition = "isys_report__id IN (" . implode(',', $p_allowed_reports) . ") AND ";
        }
        elseif ($p_allowed_reports === false)
        {
            $l_rights_condition = "FALSE AND ";
        }
        else
        {
            $l_rights_condition = "";
        }

        $l_condition = "WHERE " . $l_rights_condition . " isys_report__mandator = " . (int) $g_comp_session->get_mandator_id() . " ";

        if ($p_report_category !== null)
        {
            $l_condition .= "AND isys_report__isys_report_category__id = " . (int) $p_report_category . " ";
        }

        $l_order = " ORDER BY isys_report__title ASC";

        return $this->retrieve($l_sql . $l_condition . $l_order . ";");
    } // function

    /**
     * @param  string   $p_query
     * @param  null     $deprecated
     * @param  null     $deprecated
     * @param  null     $deprecated
     * @param  boolean  $p_context_html
     *
     * @return array
     * @throws Exception
     * @throws isys_exception_general
     */
    public function query($p_query, $deprecated = null, $deprecated = null, $deprecated = null, $p_context_html = true)
    {
        $l_listing = $l_groups = [];

        $p_query = trim($p_query);

        if (!empty($p_query))
        {
            try
            {
                if ($this->validate_query($p_query))
                {
                    $l_db = isys_application::instance()->database;
                    $l_result         = $l_db->query($p_query);
                    $l_num            = $l_db->num_rows($l_result);
                    $l_listing["num"] = $l_num;

                    if ($l_num > 0)
                    {
                        $l_memory      = \idoit\Component\Helper\Memory::instance();
                        $l_first_row   = true;
                        $translatedKey = $l_callbacks = [];

                        while ($l_row = $l_db->fetch_row_assoc($l_result))
                        {
                            $l_origin_key = null;
                            $l_memory->outOfMemoryBreak();

                            if ($l_first_row)
                            {
                                $l_first_row = false;
                                foreach ($l_row AS $l_key => $l_row_value)
                                {
                                    $l_origin_key = $l_key;

                                    if (strpos($l_key, 'isys_cmdb_dao_category') === 0)
                                    {
                                        $l_key_arr = explode('::', $l_key);
                                        $l_key     = array_pop($l_key_arr);

                                        // Add Callback only if the callback class exists
                                        // This increases performance by reducing class_exists calls for each row
                                        // .. also adding the category dao instance to the callback instead of retrieving it in each row
                                        if (class_exists($l_key_arr[0]))
                                        {
                                            $l_callbacks[$l_origin_key] = [
                                                call_user_func(
                                                    [
                                                        $l_key_arr[0],
                                                        'instance'
                                                    ],
                                                    isys_application::instance()->database
                                                ),
                                                $l_key_arr[1],
                                                $l_key_arr[2]
                                            ];
                                        }
                                    } // if

                                    if ($l_cut_to = strpos($l_key, '###'))
                                    {
                                        $l_key = substr($l_key, 0, $l_cut_to);
                                    } // if

                                    if (strpos($l_key, '#'))
                                    {
                                        $l_title_arr = explode('#', $l_key);

                                        $l_title_key = implode(
                                            ' -> ',
                                            array_reverse(
                                                array_map(
                                                    function ($val)
                                                    {
                                                        return _L($val);
                                                    },
                                                    $l_title_arr
                                                )
                                            )
                                        );
                                    }
                                    else
                                    {
                                        $l_title_key = _L($l_key);
                                    } // if

                                    // In case the key already exists
                                    // This closes #5069
                                    $i = 2;
                                    while (in_array($l_title_key, $translatedKey))
                                    {
                                        $l_title_key .= ' ' . $i;
                                        $i++;
                                    } // while

                                    // Increase performance by caching translation in first row, to have it then available in all remaining rows
                                    if ($l_title_key)
                                    {
                                        $translatedKey[$l_origin_key] = $l_title_key;
                                    }
                                } // foreach
                            } // if

                            $l_row["__obj_id__"] = $l_row["__id__"] = $l_row["isys_obj__id"] = isys_glob_which_isset($l_row["isys_obj__id"], $l_row["__obj_id__"], $l_row["__id__"]);

                            // Fixing translations.
                            $l_fixed_row = [];

                            foreach ($l_row as $l_key => $l_value)
                            {
                                $l_title_key = $translatedKey[$l_key];
                                if (!$l_title_key) continue;

                                if (isset($l_callbacks[$l_key]))
                                {
                                    if ($l_value)
                                    {
                                        $l_callback = $l_callbacks[$l_key];
                                        if(isset($l_callback[2]) && !isset($l_row[$l_callback[2]]))
                                        {
                                            $l_row[$l_callback[2]] = $l_row[$l_key];
                                        } // if
                                        $l_value    = call_user_func(
                                            [
                                                $l_callback[0],
                                                $l_callback[1]
                                            ],
                                            $l_row
                                        );
                                    }
                                    else
                                    {
                                        $l_value = '';
                                    }
                                }

                                if ($p_context_html)
                                {
                                    $l_fixed_row[$l_title_key] = html_entity_decode($l_value, ENT_QUOTES, $GLOBALS['g_config']['html-encoding']);

                                    // This replaces all plain text links to clickable links. See ID-2604
                                    if (strpos($l_fixed_row[$l_key], '://') !== false || strpos($l_fixed_row[$l_key], 'www') !== false)
                                    {
                                        $l_fixed_row[$l_key] = isys_helper_textformat::link_urls_in_string($l_fixed_row[$l_key], "'");
                                    } // if

                                    // This replaces all plain text emails to clickable links. See ID-2604
                                    if (strpos($l_fixed_row[$l_key], '@') !== false)
                                    {
                                        $l_fixed_row[$l_key] = isys_helper_textformat::link_mailtos_in_string($l_fixed_row[$l_key], "'");
                                    } // if
                                }
                                else
                                {
                                    $l_fixed_row[$l_title_key] = strip_tags(
                                        preg_replace("(<script[^>]*>([\\S\\s]*?)<\/script>)", '', $l_value)
                                    );
                                }

                            } // foreach

                            $l_listing["content"][] = $l_fixed_row;

                        } // while

                        // Free memory result
                        $l_db->free_result($p_query);

                        // Get column names.
                        if (count($l_groups) > 0)
                        {
                            $l_listing["grouped"] = true;
                            $l_tmp                = $l_listing["content"][$l_groups[0]][0];
                        }
                        else
                        {
                            if (isset($l_listing["content"][0]))
                            {
                                $l_tmp = $l_listing["content"][0];
                            }
                            else
                            {
                                $l_tmp = false;
                            } // if
                        } // if

                        if (is_array($l_tmp))
                        {
                            $l_tmp     = array_keys($l_tmp);
                            $l_columns = [];

                            foreach ($l_tmp as $l_value)
                            {
                                if (!isset($this->m_hidden_columns[$l_value]) && !preg_match("/^__[\w]+__$/i", $l_value))
                                {
                                    $l_columns[] = _L($l_value);
                                } // if
                            } // foreach
                        }
                        else
                        {
                            $l_columns = null;
                        } // if

                        $l_listing["headers"] = $l_columns;
                    } // if
                } // if
            }
            catch (Exception $e)
            {
                throw $e;
            } // try
        } // if

        return $l_listing;
    } // function

    /**
     * Checks if a report already exists.
     *
     * @param   string $p_title
     * @param   string $p_query
     *
     * @return  boolean
     */
    public function reportExists($p_title, $p_query)
    {
        $l_sql = 'SELECT isys_report__id FROM isys_report
			WHERE isys_report__title = ' . $this->convert_sql_text($p_title) . '
			AND isys_report__query = ' . $this->convert_sql_text($p_query) . ';';

        return (count($this->retrieve($l_sql)) > 0);
    } // function

    /**
     * Update an existing report.
     *
     * @param   integer $p_id
     * @param   string  $p_title
     * @param   string  $p_description
     * @param   string  $p_query
     * @param   null    $deprecated
     * @param   boolean $p_user_specific
     * @param   integer $p_report_category_id
     *
     * @return  boolean
     */
    public function saveReport($p_id, $p_title, $p_description, $p_query, $deprecated = null, $p_user_specific = false, $p_report_category_id = null)
    {
        $l_update = "UPDATE isys_report SET
			isys_report__title = " . $this->convert_sql_text($p_title) . ",
			isys_report__description = " . $this->convert_sql_text($p_description) . ",
			isys_report__query = " . $this->convert_sql_text($p_query) . ",
			isys_report__querybuilder_data = NULL,
			isys_report__last_edited = NOW(),
			isys_report__isys_report_category__id = " . $this->convert_sql_id($p_report_category_id) . ",
			isys_report__user_specific = 0
			WHERE isys_report__id = " . $this->convert_sql_id($p_id) . ";";

        return ($this->update($l_update) && $this->apply_update());
    } // function

    /**
     * Method for validating that there are no updates, drops, truncates, ... inside the query.
     *
     * @param   string $p_query
     *
     * @return  boolean
     * @throws  Exception
     */
    public function validate_query($p_query)
    {
        return \idoit\Module\Report\Validate\Query::validate($p_query);
    } // function

    /**
     * Gets report title by report id.
     *
     * @param   integer $p_id
     *
     * @return  string
     */
    public function get_report_title_by_id($p_id)
    {
        $l_sql = "SELECT isys_report__title FROM isys_report WHERE isys_report__id = " . $this->convert_sql_id($p_id) . ";";

        return $this->retrieve($l_sql)
            ->get_row_value('isys_report__title');
    } // function

    /**
     * Modifies result row.
     *
     * @param  array $p_row
     */
    public function modify_row(&$p_row)
    {
        if ($p_row['with_qb'] == 1)
        {
            $p_row['with_qb'] = _L("LC__UNIVERSAL__YES");
        }
        else
        {
            $p_row['with_qb'] = _L("LC__UNIVERSAL__NO");
        } // if
    } // function

    /**
     * Gets data from the report category table as array.
     *
     * @param   mixed   $p_id
     * @param   boolean $p_as_array
     *
     * @return  array|isys_component_dao_result
     */
    public function get_report_categories($p_id = null, $p_as_array = true)
    {
        $l_return    = [];
        $l_condition = '';

        if (is_array($p_id) && count($p_id))
        {
            $l_condition = ' AND isys_report_category__id IN (' . implode(',', $p_id) . ') ';
        }
        else if ($p_id !== null && is_numeric($p_id))
        {
            $l_condition = ' AND isys_report_category__id = ' . $this->convert_sql_id($p_id);
        }
        else if (is_string($p_id))
        {
            $l_condition = ' AND isys_report_category__title = ' . $this->convert_sql_text($p_id);
        } // if

        $l_res = $this->retrieve('SELECT * FROM isys_report_category WHERE TRUE ' . $l_condition . ' ORDER BY isys_report_category__sort ASC;');

        if ($p_as_array)
        {
            while ($l_row = $l_res->get_row())
            {
                $l_return[] = $l_row;
            } // while
        }
        else
        {
            $l_return = $l_res;
        } // if

        return $l_return;
    } // function

    /**
     * Adds a new entry into the report category table.
     *
     * @param   string  $p_title
     * @param   string  $p_description
     * @param   integer $p_sorting
     *
     * @return  boolean
     */
    public function create_category($p_title, $p_description = null, $p_sorting = 99)
    {
        $l_insert = 'INSERT INTO isys_report_category SET
			isys_report_category__title = ' . $this->convert_sql_text($p_title) . ',
			isys_report_category__description = ' . $this->convert_sql_text($p_description) . ',
			isys_report_category__sort = ' . $this->convert_sql_int($p_sorting) . ',
			isys_report_category__status = ' . $this->convert_sql_int(C__RECORD_STATUS__NORMAL) . ';';

        if ($this->update($l_insert) && $this->apply_update())
        {
            return $this->get_last_insert_id();
        }
        else
        {
            return false;
        } // if
    } // function

    /**
     * Updates an existing report category entry.
     *
     * @param   integer $p_id
     * @param   string  $p_title
     * @param   string  $p_description
     * @param   integer $p_sorting
     *
     * @return  boolean
     */
    public function update_category($p_id, $p_title, $p_description = null, $p_sorting = 99)
    {
        $l_update = 'UPDATE isys_report_category SET
			isys_report_category__title = ' . $this->convert_sql_text($p_title) . ',
			isys_report_category__description = ' . $this->convert_sql_text($p_description) . ',
			isys_report_category__sort = ' . $this->convert_sql_int($p_sorting) . '
			WHERE isys_report_category__id = ' . $this->convert_sql_id($p_id);

        return ($this->update($l_update) && $this->apply_update());
    } // function

    /**
     * Retrieves all reports by report category.
     *
     * @param   mixed $p_data
     *
     * @return  isys_component_dao_result
     */
    public function get_reports_by_category($p_data = null)
    {
        $l_query = 'SELECT * FROM isys_report
			INNER JOIN isys_report_category ON isys_report__isys_report_category__id = isys_report_category__id
			WHERE TRUE';

        if ($p_data !== null)
        {
            if (is_array($p_data) && count($p_data))
            {

                $l_query .= ' AND isys_report__isys_report_category__id ' . $this->prepare_in_condition($p_data);
            }
            else
            {
                $l_query .= ' AND isys_report__isys_report_category__id = ' . $this->convert_sql_id($p_data);
            } // if
        } // if

        return $this->retrieve($l_query . ';');
    } // function

    /**
     * Deletes a report category.
     *
     * @param   integer $p_report_category_id
     *
     * @return  boolean
     */
    public function delete_report_category($p_report_category_id)
    {
        $l_sql = 'DELETE FROM isys_report_category WHERE isys_report_category__id = ' . $this->convert_sql_id($p_report_category_id) . ';';

        return ($this->update($l_sql) && $this->apply_update());
    } // function

    /**
     * Checks if report category exists.
     *
     * @param   integer $p_report_category_id
     *
     * @return  integer
     */
    public function check_report_category($p_report_category_id)
    {
        $l_sql = 'SELECT isys_report_category__id FROM isys_report_category WHERE isys_report_category__id = ' . $this->convert_sql_id($p_report_category_id) . ';';

        return count($this->retrieve($l_sql));
    } // function

    /**
     * Gets the default report category id (Global);
     *
     * @todo    Use constants... But not a title string :X
     * @return  mixed
     */
    public function get_default_report_category()
    {
        return $this->retrieve('SELECT isys_report_category__id FROM isys_report_category WHERE isys_report_category__title = ' . $this->convert_sql_text('Global') . ';')
            ->get_row_value('isys_report_category__id');
    } // function
} // class