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
 * DAO for table list for module logbook
 *
 * @package    i-doit
 * @subpackage Components
 * @author     Niclas Potthast <npotthast@i-doit.de> - 2005-12-12
 * @version    Dennis Bluemer <dbluemer@i-doit.org>
 * @version    Dennis Stücken <dstuecken@i-doit.org>
 * @copyright  synetics GmbH
 * @license    http://www.i-doit.com/license
 */
class isys_component_dao_logbook extends isys_component_dao
{

    /**
     * Last logbook id
     *
     * @var int
     */
    private $m_last_logbook_id = null;

    /**
     * Return compressed changes as "from"/"to" array.
     *
     * @param string $p_changes_binary
     *
     * @author Dennis Stuecken; 06-2009
     * @return array
     */
    public static function get_changes_as_array($p_changes_binary)
    {
        $l_changes = unserialize($p_changes_binary);

        if ($l_changes && is_array($l_changes))
        {
            if (array_key_exists("isys_cmdb_dao_category_s_person_login::user_pass", $l_changes))
            {
                $l_changes["isys_cmdb_dao_category_s_person_login::user_pass"]["to"] = "***";
            }
            if (array_key_exists("C__CONTACT__PERSON_PASSWORD", $l_changes))
            {
                $l_changes["C__CONTACT__PERSON_PASSWORD"]["to"] = "***";
            }
            if (array_key_exists("C__CONTACT__PERSON_PASSWORD_SECOND", $l_changes))
            {
                $l_changes["C__CONTACT__PERSON_PASSWORD_SECOND"]["to"] = "***";
            }

            return $l_changes;
        }

        else return [];
    }

    /**
     * Gets last logbook id
     *
     * @return int
     */
    public function get_logbook_id()
    {
        return $this->m_last_logbook_id;
    } // function

    /**
     * Match sql string and return it as html formatted string
     *
     * @author Dennis Stuecken <dstuecken@i-doit.org>
     *
     * @param string $l_desc
     *
     * @return string
     */
    public function match_description($l_desc)
    {
        global $g_comp_template_language_manager;

        /* Match the update statement */
        $l_regex_update = "(?:UPDATE|INSERT INTO|REPLACE INTO) [`]?(.*?)[`]? SET " . "(?:(.*?)WHERE(.*)|(.*?))";

        /* Match the delete statement */
        $l_regex_delete = "DELETE FROM [`]?(.*?)[`]? WHERE (.*?)[;]?";

        if (preg_match("/^{$l_regex_update}$/si", trim($l_desc), $l_reg))
        {
            $l_desc = "";

            /* Extract sql statement */
            $l_category = $l_reg[1];
            $l_sets     = (!empty($l_reg[2])) ? $l_reg[2] : $l_reg[4];

            if (!empty($l_reg[3]))
            {
                $l_where = strtoupper(str_replace("=", " : ", str_replace($l_category . "__", "", str_replace("'", "", trim($l_reg[3], ";"))))) . " (" . trim(
                        trim($l_reg[3], ";")
                    ) . ")";

                $l_where = str_replace("\\", "", $l_where);

                /* Updated headline */
                $l_desc .= "<h4>" . $g_comp_template_language_manager->{"LC__UNIVERSAL__UPDATED"} . ":</h4>";

                $l_lc_changes = "LC__UNIVERSAL__CHANGES";

            }
            else
            {
                /* Data headline */
                $l_desc .= "<h4>" . $g_comp_template_language_manager->{"LC__TASK__STATUS__INIT__SHORT"} . ":</h4>";

                $l_lc_changes = "LC__CMDB__LOGBOOK__DATA";

            }

            /* Get category title */
            if (is_object($this->m_cmdb_dao_category))
            {
                $l_catg = $this->m_cmdb_dao_category->get_catg_by_table_name($l_category);
                if ($l_catg->num_rows() > 0)
                {
                    $l_r = $l_catg->get_row(IDOIT_C__DAO_RESULT_TYPE_ARRAY);
                    $l_desc .= $g_comp_template_language_manager->{"LC__CMDB__CATG__CATEGORY"} . ": " . $g_comp_template_language_manager->{$l_r["isysgui_catg__title"]} .
                        "\n";
                }
            }

            /* Show updated table */
            $l_desc .= $g_comp_template_language_manager->{"LC__UNIVERSAL__TABLE"} . ": " . $l_category . "\n";

            /* Show condition for update */
            if (isset($l_where)) $l_desc .= $g_comp_template_language_manager->{"LC__UNIVERSAL__CONDITION"} . ": " . $l_where . "\n";

            /* Explode changings */
            $l_arSet = explode(",", $l_sets);
            if (is_array($l_arSet) && count($l_arSet) > 0)
            {
                $i = 0;
                foreach ($l_arSet as $l_set)
                {

                    if (preg_match("/^(.*?)[\s]*=[\s]*\'(.*?)\'$/i", str_replace("\\", "", trim($l_set)), $l_changed))
                    {
                        if ($i++ == 0) $l_desc .= "<h4>" . $g_comp_template_language_manager->{$l_lc_changes} . ":</h4>";

                        $l_row   = $l_changed[1];
                        $l_value = $l_changed[2];

                        if ($l_value != "")
                        {

                            $l_desc .= ucfirst(str_replace($l_category . "__", "", $l_row)) . ": " . $l_value . "\n";
                        }
                    }

                }
            }

        }
        else
        {
            if (preg_match("/^{$l_regex_delete}$/si", trim($l_desc), $l_reg))
            {

                $l_where = strtoupper(
                    str_replace(
                        "=",
                        " : ",
                        str_replace(
                            $l_reg[1] . "__",
                            "",
                            str_replace("'", "", trim($l_reg[2], ";"))
                        )
                    )
                );

                /* Deleted headline */
                $l_desc = "<h4>" . $g_comp_template_language_manager->{"LC__CMDB__RECORD_STATUS__DELETED"} . ":</h4>";

                $l_desc .= $g_comp_template_language_manager->{"LC__UNIVERSAL__TABLE"} . ": " . $l_reg[1] . "\n" .

                    $g_comp_template_language_manager->{"LC__UNIVERSAL__CONDITION"} . ": " . $l_where . " (" . trim(trim($l_reg[2], ";")) . ")\n";
            }
        }

        return $l_desc;

    } // function

    /**
     * @param integer $p_userid
     * @param bool    $p_bLastEntry
     * @param integer $p_objID
     * @param string  $p_limit Format: Offset,Limit
     *
     * @return isys_component_dao_result
     */
    public function get_result($p_userid = null, $p_bLastEntry = false, $p_objID = null, $p_skipordering = false, $p_order = false, $p_limit = false)
    {
        $l_strSQL = "SELECT * FROM isys_logbook " . "INNER JOIN isys_logbook_source ON isys_logbook_source__id = isys_logbook__isys_logbook_source__id " .
            "LEFT JOIN isys_logbook_level ON isys_logbook__isys_logbook_level__id = isys_logbook_level__id ";

        if ($p_objID != null)
        {
            $l_strSQL .= "LEFT JOIN isys_catg_logb_list ON isys_catg_logb_list__isys_logbook__id = isys_logbook__id ";

            if (is_array($p_objID))
            {
                $l_strSQL .= " WHERE isys_catg_logb_list__isys_obj__id IN (" . implode(',', $p_objID) . ")";
            }
            else
            {
                $l_strSQL .= " WHERE isys_catg_logb_list__isys_obj__id = " . $this->convert_sql_id($p_objID);
            }
        }
        else
        {
            $l_strSQL .= "WHERE TRUE";
        }

        if ($p_userid)
        {
            $l_strSQL .= " AND isys_logbook__isys_obj__id = '$p_userid' ";
        }

        if (!$p_skipordering)
        {
            $l_strSQL = isys_glob_sql_append_order($l_strSQL);
        }

        if ($p_order)
        {
            $l_strSQL .= 'ORDER BY ' . $p_order;
        }

        if ($p_limit)
        {
            $l_limit = explode(',', $p_limit);

            if (isset($l_limit[1]))
            {
                $this->m_db->limit_query($l_strSQL, $l_limit[0], $l_limit[1]);
            }

        }

        return $this->retrieve($l_strSQL);
    }

    /**
     * Count all logbook entries
     *
     * @return mixed
     * @throws Exception
     * @throws isys_exception_database
     */
    public function count()
    {
        return $this->retrieve('SELECT COUNT(*) AS cnt FROM isys_logbook')
            ->get_row_value('cnt');
    }

    /**
     * @param integer $p_userid
     * @param bool    $p_bLastEntry
     * @param Array   $p_objID
     *
     * @return isys_component_dao_result
     */
    public function get_result_by_array($p_userid = null, $p_bLastEntry = false, $p_objID = null)
    {
        $l_strSQL = "SELECT * FROM isys_logbook " .

            "LEFT JOIN isys_logbook_level ON isys_logbook__isys_logbook_level__id = isys_logbook_level__id " .
            "INNER JOIN isys_logbook_source ON isys_logbook_source__id = isys_logbook__isys_logbook_source__id " .
            "LEFT JOIN isys_catg_logb_list ON isys_catg_logb_list__isys_logbook__id = isys_logbook__id " . "WHERE TRUE";

        if ($p_objID != null)
        {
            if (is_array($p_objID))
            {
                $l_strSQL .= " AND isys_catg_logb_list__isys_obj__id IN (" . implode(",", $p_objID) . ") ";
            }
            else
            {
                $l_strSQL .= " AND isys_catg_logb_list__isys_obj__id = " . $this->convert_sql_id($p_objID) . " ";
            }
        }

        if ($p_userid)
        {
            $l_strSQL .= " AND isys_logbook__isys_obj__id = " . $this->convert_sql_id($p_userid) . " ";
        }

        $l_strSQL = isys_glob_sql_append_order($l_strSQL);

        return $this->retrieve($l_strSQL);
    }

    /**
     * @return isys_component_dao_result
     */
    public function get_result_all()
    {
        return $this->get_result(0, false);
    }

    /**
     * @param string $p_strSource
     *
     * @return isys_component_dao_result
     */
    public function get_result_latest_entry()
    {
        $l_query = "SELECT isys_logbook__id, isys_logbook__date, isys_logbook__isys_obj__id, " .
            "isys_logbook__event_static, isys_logbook__obj_name_static, isys_logbook__category_static, " .
            "isys_logbook__obj_type_static, isys_logbook_level__const, isys_logbook__user_name_static FROM isys_logbook " .
            "INNER JOIN isys_logbook_level ON isys_logbook__isys_logbook_level__id = isys_logbook_level__id " .
            "WHERE isys_logbook__id = (SELECT max(isys_logbook__id) FROM isys_logbook);";

        return $this->retrieve($l_query);
    }

    /**
     * @param integer $p_nLogbookID
     *
     * @return isys_component_dao_result
     */
    public function get_result_by_logbook_id($p_nLogbookID)
    {
        $l_strSQL = "SELECT " . "isys_logbook__date, " . //date
            "isys_logbook__event_static, " . "isys_logbook__obj_name_static, " . "isys_logbook__category_static, " . "isys_logbook__obj_type_static, " .
            "isys_logbook__changes, " . "isys_logbook__comment, " . "isys_logbook__isys_obj__id, " . //user id
            "isys_logbook_level__title, " . //const of alert level
            "isys_logbook__description, " . "isys_catg_logb_list__id, " . "isys_logbook__isys_logbook_reason__id " . "FROM isys_logbook " . "LEFT JOIN isys_catg_logb_list " .
            "ON isys_catg_logb_list__isys_logbook__id = isys_logbook__id " . "LEFT JOIN isys_logbook_level " . "ON isys_logbook__isys_logbook_level__id = " .
            "isys_logbook_level__id " . "WHERE isys_logbook__id = " . $this->convert_sql_id(
                $p_nLogbookID
            ) . ";";

        return $this->retrieve($l_strSQL);
    }

    /**
     * @param integer $p_nUserID
     *
     * @return isys_component_dao_result
     */
    public function get_result_by_user_id($p_nUserID)
    {
        return $this->get_result($p_nUserID, false);
    }

    /**
     * @param $p_logbookID
     *
     * @return mixed
     * @throws Exception
     * @throws isys_exception_database
     */
    public function getDescription($p_logbookID)
    {
        $l_query  = "SELECT isys_logbook__description AS 'desc' FROM isys_logbook WHERE isys_logbook__id = '" . $p_logbookID . "';";
        $l_result = $this->retrieve($l_query);

        if ($l_row = $l_result->get_row())
        {
            return $l_row["desc"];
        }
        else
        {
            throw new Exception("No row with ID: " . $p_logbookID);
        }
    }

    /**
     * @param $p_logbookID
     *
     * @return mixed
     * @throws Exception
     * @throws isys_exception_database
     */
    public function get_changes_utf8($p_logbookID)
    {
        $l_query  = "SELECT isys_logbook__changes FROM isys_logbook WHERE isys_logbook__id = '" . $p_logbookID . "';";
        $l_result = $this->retrieve($l_query);

        if ($l_row = $l_result->get_row())
        {
            return $l_row["isys_logbook__changes"];
        }
        else
        {
            throw new Exception("No row with ID: " . $p_logbookID);
        }
    }

    /**
     * @param $p_logbookID
     *
     * @return mixed
     * @throws Exception
     * @throws isys_exception_database
     *
     * @deprecated this function is not used anymore.
     */
    public function get_changes($p_logbookID)
    {
        $this->m_db->query('SET NAMES latin1');

        $l_query  = "SELECT isys_logbook__changes FROM isys_logbook WHERE isys_logbook__id = '" . $p_logbookID . "';";
        $l_result = $this->retrieve($l_query);

        if ($l_row = $l_result->get_row())
        {
            return $l_row["isys_logbook__changes"];
        }
        else
        {
            throw new Exception("No row with ID: " . $p_logbookID);
        }
    }

    /**
     * @param $p_nLogbookID
     *
     * @return integer object ID (or NULL)
     * @author Niclas Potthast <npotthast@i-doit.org> - 2005-09-30
     * @desc   check if object is connected to logbook entry and return id
     */
    public function get_object_id_by_logbook_id($p_nLogbookID)
    {
        $l_ret    = ISYS_NULL;
        $l_strSQL = "";

        if (is_numeric($p_nLogbookID))
        {
            $l_strSQL .= "SELECT isys_catg_logb_list__isys_obj__id " . "FROM isys_catg_logb_list " . "JOIN isys_logbook " . "ON isys_catg_logb_list__isys_logbook__id = '" .
                $p_nLogbookID . "'";

            $l_res = $this->retrieve($l_strSQL);
            $l_row = $l_res->get_row();
            $l_ret = $l_row["isys_catg_logbook_list__isys_obj__id"];
        }

        return $l_ret;
    }

    /**
     * Set Entry method.
     *
     * @global  isys_component_database $g_comp_database
     * @global  isys_component_session  $g_comp_database
     *
     * @param   string                  $p_strConstEvent
     * @param   string                  $p_strDescription
     * @param   string                  $p_datetime
     * @param   integer                 $p_cAlertLevel
     * @param   integer                 $p_nObjectID
     * @param   string                  $p_strObjName
     * @param   string                  $p_strObjTypeTitle
     * @param   string                  $p_strCategory
     * @param   integer                 $p_nSourceID
     * @param   string                  $p_changes
     * @param   string                  $p_comment
     * @param   integer                 $p_reasonID
     * @param   string                  $p_entry_identifier
     *
     * @return  boolean
     * @author  Dennis Stücken <dstuecken@i-doit.org>
     */
    public function set_entry($p_strConstEvent = "", $p_strDescription = null, $p_datetime = null, $p_cAlertLevel = C__LOGBOOK__ALERT_LEVEL__1, $p_nObjectID = null, $p_strObjName, $p_strObjTypeTitle, $p_strCategory, $p_nSourceID = C__LOGBOOK_SOURCE__INTERNAL, $p_changes = "", $p_comment = "", $p_reasonID = null, $p_entry_identifier = null, $p_count_changes = 0)
    {
        global $g_comp_database;

        $l_strQuery    = "";
        $l_nUserID     = isys_application::instance()->session->get_user_id();
        $l_strUserName = "";
        $l_nSourceID   = 1;

        $l_objDAOUser = isys_component_dao_user::instance($g_comp_database);

        if (!$p_cAlertLevel)
        {
            return false;
        } // if

        //get logbook config
        $l_config     = null;
        $l_config_res = $this->get_logbook_config();

        if ($l_config_res->num_rows() > 0)
        {
            $l_config = $l_config_res->get_row();
        } // if

        //get user name
        if ($l_nUserID > 0 && empty($l_config))
        {
            $l_strUserName = $l_objDAOUser->get_user_title($l_nUserID);
        }
        else
        {
            if ($l_nUserID > 0 && !empty($l_config))
            {
                $l_strUserName = $l_objDAOUser->get_user_title_by_logbook_config(
                    $l_nUserID,
                    $l_config['isys_logbook_configuration__type'],
                    $l_config['isys_logbook_configuration__placeholder_string']
                );
            }
        } // if

        if ($p_cAlertLevel == null)
        {
            $p_cAlertLevel = C__LOGBOOK__ALERT_LEVEL__0;
        } // if

        $this->begin_update();

        $l_strQuery .= "INSERT INTO isys_logbook SET
			isys_logbook__user_name_static = " . $this->convert_sql_text($l_strUserName) . ",
			isys_logbook__isys_obj__id = " . $this->convert_sql_id($l_nUserID) . ",
			isys_logbook__isys_logbook_level__id = " . $this->convert_sql_id($p_cAlertLevel) . ",
			isys_logbook__status = " . $this->convert_sql_int(C__RECORD_STATUS__NORMAL) . ",
			isys_logbook__isys_logbook_reason__id = " . $this->convert_sql_id($p_reasonID) . ",
			isys_logbook__entry_identifier_static = " . $this->convert_sql_text($p_entry_identifier) . ",
			isys_logbook__changecount = " . $this->convert_sql_int($p_count_changes) . ",
			isys_logbook__property = NULL, ";

        if ($p_strDescription == null)
        {
            $l_strQuery .= "isys_logbook__description = NULL, ";
        }
        else
        {
            $l_strQuery .= "isys_logbook__description = " . $this->convert_sql_text($p_strDescription) . ", ";
        } // if

        if ($p_datetime == null)
        {
            $p_datetime = isys_glob_datetime();
        } // if

        if ($p_nSourceID > 0)
        {
            $l_nSourceID = $p_nSourceID;
        } // if

        $l_strQuery .= "isys_logbook__isys_logbook_source__id = " . $this->convert_sql_id($l_nSourceID) . ",
			isys_logbook__date  = " . $this->convert_sql_datetime($p_datetime) . ",
			isys_logbook__event_static = " . $this->convert_sql_text($p_strConstEvent) . ",
			isys_logbook__obj_name_static  = " . $this->convert_sql_text($p_strObjName) . ",
			isys_logbook__category_static = " . $this->convert_sql_text($p_strCategory) . ",
			isys_logbook__comment = " . $this->convert_sql_text($p_comment) . ",
			isys_logbook__changes = " . $this->convert_sql_text($p_changes) . ",
			isys_logbook__obj_type_static = " . $this->convert_sql_text($p_strObjTypeTitle);

        $l_bAllGood = $this->update($l_strQuery);

        if ($l_bAllGood)
        {
            $this->set_logbook_id($this->get_last_insert_id());
        } // if

        if (is_numeric($p_nObjectID))
        {
            if ($l_bAllGood)
            {
                $l_strQuery = "INSERT INTO isys_catg_logb_list SET " . "isys_catg_logb_list__isys_obj__id = " . $l_objDAOUser->convert_sql_id(
                        $p_nObjectID
                    ) . ", " . "isys_catg_logb_list__isys_logbook__id = (SELECT LAST_INSERT_ID()), " . "isys_catg_logb_list__status = " . C__RECORD_STATUS__NORMAL;

                $l_bAllGood = $this->update($l_strQuery);
            } // if
        } // if

        if ($l_bAllGood)
        {
            $l_bAllGood = $this->apply_update();
        } // if

        return $l_bAllGood;
    }

    /**
     * Retrieves the date, of the last check_mk logbook entry for a certain object.
     *
     * @param   integer $p_obj_id
     *
     * @return  string
     */
    public function get_date_of_last_check_mk_entry($p_obj_id)
    {
        $l_sql = "SELECT MAX(isys_logbook__date) AS 'date' FROM isys_logbook
			INNER JOIN isys_catg_logb_list ON isys_catg_logb_list__isys_logbook__id = isys_logbook__id
			INNER JOIN isys_logbook_source ON isys_logbook_source__id = isys_logbook__isys_logbook_source__id
			WHERE isys_logbook_source__const = 'C__LOGBOOK_SOURCE__CMK'
			AND isys_catg_logb_list__isys_obj__id = " . $this->convert_sql_id($p_obj_id) . ";";

        return $this->retrieve($l_sql)
            ->get_row_value('date');
    } // function

    /**
     * Retrieves the date, of the last NDO logbook entry for a certain object.
     *
     * @param   integer $p_obj_id
     *
     * @return  string
     */
    public function getDateOfLastNDOEntry($p_obj_id)
    {
        $l_sql = "SELECT MAX(isys_logbook__date) AS 'date' FROM isys_logbook
			INNER JOIN isys_catg_logb_list ON isys_catg_logb_list__isys_logbook__id = isys_logbook__id
			INNER JOIN isys_logbook_source ON isys_logbook_source__id = isys_logbook__isys_logbook_source__id
			WHERE isys_logbook_source__const = 'C__LOGBOOK_SOURCE__NDO'
			AND isys_catg_logb_list__isys_obj__id = " . $this->convert_sql_id($p_obj_id) . ";";

        return $this->retrieve($l_sql)
            ->get_row_value('date');
    } // function

    /**
     * Method for retrieving sources.
     *
     * @return  array
     */
    public function getSources()
    {
        $l_return = [];
        $l_result = $this->retrieve('SELECT isys_logbook_source__title AS "title", isys_logbook_source__id AS "id" FROM isys_logbook_source;');

        if (count($l_result) > 0)
        {
            while ($l_row = $l_result->get_row())
            {
                $l_return[$l_row["id"]] = $l_row["title"];
            } // while
        } // if

        return $l_return;
    } // function

    /**
     * Method for retrieving alert levels.
     *
     * @return  array
     */
    public function getAlertlevels()
    {
        $l_return = [];
        $l_result = $this->retrieve('SELECT isys_logbook_level__title AS "title", isys_logbook_level__id AS "id" FROM isys_logbook_level;');

        if (count($l_result) > 0)
        {
            while ($l_row = $l_result->get_row())
            {
                $l_return[$l_row["id"]] = $l_row["title"];
            } // while
        } // if

        return $l_return;
    } // function

    /**
     *
     * @return  boolean
     * @throws  Exception
     */
    public function archiveAccessible()
    {
        global $g_db_system;

        if (!(is_numeric($_POST["archiveInterval"]) && $_POST["archiveInterval"] > 0))
        {
            throw new Exception(_L('LC__SETTINGS__CMDB__VALIDATION_MESSAGE__NEEDS_TO_BE_INTEGER_BIGGER_ZERO'));
        } // if

        if ($_POST["archiveDest"] == 1)
        {
            isys_component_database::get_database(
                $g_db_system["type"],
                $_POST["archiveHost"],
                $_POST["archivePort"],
                $_POST["archiveUser"],
                $_POST["archivePass"],
                $_POST["archiveDB"]
            )
                ->close();
        } // if

        return true;
    } // function

    /**
     *
     * @return  boolean
     */
    public function saveArchivingSettings()
    {
        $l_query = "UPDATE isys_logbook_archive SET
			isys_logbook_archive__interval = " . $this->convert_sql_int($_POST["archiveInterval"]) . ",
			isys_logbook_archive__destination = " . $this->convert_sql_int($_POST["archiveDest"]) . ",
			isys_logbook_archive__host = " . $this->convert_sql_text($_POST["archiveHost"]) . ",
			isys_logbook_archive__port = " . $this->convert_sql_text($_POST["archivePort"]) . ",
			isys_logbook_archive__db = " . $this->convert_sql_text($_POST["archiveDB"]) . ",
			isys_logbook_archive__user = " . $this->convert_sql_text($_POST["archiveUser"]) . ",
			isys_logbook_archive__pass = " . $this->convert_sql_text($_POST["archivePass"]) . ";";

        return ($this->update($l_query) && $this->apply_update());
    } // function

    /**
     * @return array
     * @throws isys_exception_database
     */
    public function getArchivingSettings()
    {
        $l_query = "SELECT isys_logbook_archive__interval AS 'interval',
			isys_logbook_archive__destination AS 'dest',
			isys_logbook_archive__host AS 'host',
			isys_logbook_archive__port AS 'port',
			isys_logbook_archive__db AS 'db',
			isys_logbook_archive__user AS 'user',
			isys_logbook_archive__pass AS 'pass'
			FROM isys_logbook_archive";

        return $this->retrieve($l_query)
            ->get_row();
    } // function

    /**
     * @param $p_interval
     *
     * @return isys_component_dao_result
     * @throws isys_exception_database
     */
    public function get_data($p_interval)
    {
        $l_query = "SELECT * FROM isys_logbook " . "LEFT JOIN isys_catg_logb_list ON isys_logbook__id = isys_catg_logb_list__isys_logbook__id ";

        if ($p_interval > 0)
        {
            $l_query .= "WHERE isys_logbook__date <= DATE_SUB(CURDATE(),INTERVAL " . $p_interval . " DAY)";
        }

        $l_query .= "ORDER BY isys_logbook__id ASC";

        return $this->retrieve($l_query);
    }

    /**
     *
     * @param   integer $p_toDate
     *
     * @return  array
     */
    public function getEntries($p_toDate)
    {
        $l_entries = [];
        $l_result  = $this->get_data($p_toDate);

        while ($l_row = $l_result->get_row())
        {
            $l_entries[] = $l_row;
        } // while

        return $l_entries;
    }

    /**
     * Gets the current logbook configuration.
     *
     * @return  isys_component_dao_result
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function get_logbook_config()
    {
        return $this->retrieve('SELECT * FROM isys_logbook_configuration;');
    } // function

    /**
     * Saves the logbook configuration.
     *
     * @param   integer $p_id
     * @param   integer $p_type
     * @param   string  $p_placeholder_string
     *
     * @return  boolean
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function save_logbook_config($p_id, $p_type, $p_placeholder_string = null)
    {
        $l_sql = 'UPDATE isys_logbook_configuration SET
			isys_logbook_configuration__type = ' . $this->convert_sql_int($p_type) . ',
			isys_logbook_configuration__placeholder_string = ' . $this->convert_sql_text($p_placeholder_string) . '
			WHERE isys_logbook_configuration__id = ' . $this->convert_sql_id($p_id);

        return ($this->update($l_sql) && $this->apply_update());
    } // function

    /**
     * Creates an entry in the logbook config table
     *
     * @param   integer $p_type
     * @param   string  $p_placeholder_string
     *
     * @return  mixed
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function create_logbook_config($p_type = 0, $p_placeholder_string = null)
    {
        $l_update = 'INSERT INTO isys_logbook_configuration (isys_logbook_configuration__type, isys_logbook_configuration__placeholder_string)
			VALUES (' . $this->convert_sql_int($p_type) . ',' . $this->convert_sql_text($p_placeholder_string) . ');';

        if ($this->update($l_update) && $this->apply_update())
        {
            return $this->get_last_insert_id();
        } // if

        return false;
    } // function

    /**
     * Creates an entry in the relation table between logbook and import.
     *
     * @param   integer $p_import_id
     *
     * @return  boolean
     */
    public function set_import_entry($p_import_id)
    {
        return $this->attach_import($this->get_logbook_id(), $p_import_id);
    } // function

    /**
     * Attach isys_import to isys_logbook.
     *
     * @param   integer $p_logbook_id
     * @param   integer $p_import_id
     *
     * @return  boolean
     * @throws  isys_exception_dao
     */
    public function attach_import($p_logbook_id, $p_import_id)
    {
        $l_update = 'INSERT INTO isys_logbook_2_isys_import (isys_logbook_2_isys_import__isys_logbook__id, isys_logbook_2_isys_import__isys_import__id)
			VALUES (' . $this->convert_sql_id($p_logbook_id) . ', ' . $this->convert_sql_id($p_import_id) . ');';

        return ($this->update($l_update) && $this->apply_update());
    } // function

    /**
     * Gets logbook entries by import.
     *
     * @param   integer $p_import_id
     *
     * @return  isys_component_dao_result
     */
    public function get_result_by_import_id($p_import_id = null)
    {
        $l_strSQL = "SELECT * FROM isys_logbook
			LEFT JOIN isys_logbook_level ON isys_logbook__isys_logbook_level__id = isys_logbook_level__id
			INNER JOIN isys_logbook_source ON isys_logbook_source__id = isys_logbook__isys_logbook_source__id
			INNER JOIN isys_logbook_2_isys_import ON  isys_logbook_2_isys_import__isys_logbook__id = isys_logbook__id
			WHERE TRUE";

        if ($p_import_id != null)
        {
            $l_strSQL .= " AND isys_logbook_2_isys_import__isys_import__id = " . $this->convert_sql_id($p_import_id);
        } // if

        return $this->retrieve($l_strSQL);
    } // function

    /**
     * Sets last logbook id
     *
     * @param $p_value
     */
    private function set_logbook_id($p_value)
    {
        $this->m_last_logbook_id = $p_value;
    } // function
} // class