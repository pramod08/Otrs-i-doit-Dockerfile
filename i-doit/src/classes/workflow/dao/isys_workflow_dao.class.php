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
 * @package     i-doit
 * @subpackage  Workflow
 * @author      Dennis Stuecken <dstuecken@i-doit.org>
 * @version     1.0
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_workflow_dao extends isys_component_dao
{
    /**
     * Array of assigned persons (isys_obj__id).
     *
     * @var  array
     */
    protected $m_assigns;
    /**
     * @var  integer
     */
    protected $m_id;

    /**
     * Returns the workflow ID.
     *
     * @return  integer
     */
    public function get_id()
    {
        return $this->m_id;
    } // function

    /**
     * Returns the assignments.
     *
     * @return  array
     */
    public function get_assigns()
    {
        return $this->m_assigns;
    } // function

    /**
     * Returns a serialized array with occurrences.
     *
     * @return  string
     */
    public function get_occurrence_data()
    {
        return serialize(
            [
                C__TASK__OCCURRENCE__HOURLY          => _L("LC__TASK__HOURLY"),
                C__TASK__OCCURRENCE__DAILY           => _L("LC__TASK__DAILY"),
                C__TASK__OCCURRENCE__WEEKLY          => _L("LC__TASK__WEEKLY"),
                C__TASK__OCCURRENCE__EVERY_TWO_WEEKS => _L("LC__TASK__EVERY_TWO_WEEKS"),
                C__TASK__OCCURRENCE__MONTHLY         => _L("LC__TASK__MONTHLY"),
                C__TASK__OCCURRENCE__YEARLY          => _L("LC__TASK__YEARLY"),
            ]
        );
    } // function

    /**
     * Get exceptions assigned to language constants.
     *
     * @return  array
     */
    public function get_exceptions()
    {
        return [
            1 => _L("LC__UNIVERSAL__CALENDAR__DAYS_MONDAY"),
            2 => _L("LC__UNIVERSAL__CALENDAR__DAYS_TUESDAY"),
            3 => _L("LC__UNIVERSAL__CALENDAR__DAYS_WEDNESDAY"),
            4 => _L("LC__UNIVERSAL__CALENDAR__DAYS_THURSDAY"),
            5 => _L("LC__UNIVERSAL__CALENDAR__DAYS_FRIDAY"),
            6 => _L("LC__UNIVERSAL__CALENDAR__DAYS_SATURDAY"),
            0 => _L("LC__UNIVERSAL__CALENDAR__DAYS_SUNDAY")
        ];
    } // function

    public function get_workflows($p_id = null, $p_parent_workflow__id = null, $p_workflow_type = null, $p_action_type = null, $p_filter = null, $p_limit = null, $p_date_from = null, $p_date_to = null, $p_order_by = null, $p_user_id = null, $p_owner_mode = null, $p_status = null)
    {

        $l_sql = "SELECT " . "isys_workflow__id, " . "isys_workflow__isys_workflow__id, " . "isys_workflow__isys_contact__id, " . "isys_workflow__isys_workflow_category__id, " . "isys_workflow__isys_workflow_type__id, " . //"isys_workflow__isys_obj__id, ".
            "isys_workflow__title, " . "isys_workflow__occurrence, " . "isys_workflow__exception, " . "isys_workflow__datetime, " . "isys_workflow__status, " . "isys_workflow_type__id, " . "isys_workflow_type__title, " . "isys_workflow_type__occurrence, " . "new.isys_workflow_action__id, " . "new.isys_workflow_action__isys_contact__id, " . "isys_workflow_action_type__id , " . "isys_workflow_action_type__title, " . "isys_workflow_action_type__class, " . "isys_workflow_action_type__const, ";

        $l_sql .= "start_date.isys_workflow_action_parameter__id AS start_id, " . "date_format(start_date.isys_workflow_action_parameter__datetime, '%Y-%m-%d') AS startdate, " . "end_date.isys_workflow_action_parameter__id AS end_id, " . "date_format(end_date.isys_workflow_action_parameter__datetime, '%Y-%m-%d') AS enddate, ";

        $l_sql .= "con.isys_contact_2_isys_obj__isys_contact__id AS isys_contact__id, " . "creator.isys_obj__title, " . "creator.isys_obj__id AS isys_workflow__isys_obj__id " .

            "FROM isys_workflow AS wf " . "INNER JOIN isys_workflow_type ON isys_workflow__isys_workflow_type__id = isys_workflow_type__id " . "INNER JOIN isys_workflow_2_isys_workflow_action w2a_new ON w2a_new.isys_workflow_2_isys_workflow_action__isys_workflow__id = isys_workflow__id " . "INNER JOIN isys_workflow_action new ON w2a_new.isys_workflow_2_isys_workflow_action__isys_workflow_action__id = new.isys_workflow_action__id " . "INNER JOIN isys_workflow_action_type ON isys_workflow_action_type__id = new.isys_workflow_action__isys_workflow_action_type__id ";

        $l_sql .= "LEFT OUTER JOIN " . "isys_workflow_action_parameter start_date ON start_date.isys_workflow_action_parameter__isys_workflow_action__id = new.isys_workflow_action__id " . "AND start_date.isys_workflow_action_parameter__isys_wf_template_parameter__id = (SELECT isys_workflow_template_parameter__id FROM isys_workflow_template_parameter WHERE isys_workflow_template_parameter__key = " . "CONCAT(REPLACE(LOWER(isys_workflow_type__title), ' ', ''), '__start_date')) " . "LEFT OUTER JOIN isys_workflow_action_parameter end_date ON end_date.isys_workflow_action_parameter__isys_workflow_action__id = new.isys_workflow_action__id " . "AND end_date.isys_workflow_action_parameter__isys_wf_template_parameter__id = (SELECT isys_workflow_template_parameter__id FROM isys_workflow_template_parameter WHERE isys_workflow_template_parameter__key = CONCAT(REPLACE(LOWER(isys_workflow_type__title), ' ', ''), '__end_date')) ";

        if ($p_action_type == C__WORKFLOW__ACTION__TYPE__ASSIGN)
        {
            $l_sql .= "INNER JOIN isys_workflow_2_isys_workflow_action w2a ON " . "w2a.isys_workflow_2_isys_workflow_action__isys_workflow__id = " . "isys_workflow__id \n";

            $l_sql .= "INNER JOIN isys_workflow_action assign ON " . "w2a.isys_workflow_2_isys_workflow_action__isys_workflow_action__id = " . "assign.isys_workflow_action__id \n";

            $l_where = " AND (assign.isys_workflow_action__isys_workflow_action_type__id = '" . C__WORKFLOW__ACTION__TYPE__ASSIGN . "') ";
        }

        if ($p_action_type == C__WORKFLOW__ACTION__TYPE__ACCEPT)
        {
            $l_sql .= "INNER JOIN isys_workflow_2_isys_workflow_action w2a ON " . "w2a.isys_workflow_2_isys_workflow_action__isys_workflow__id = " . "isys_workflow__id \n";

            $l_sql .= "INNER JOIN isys_workflow_action accept ON " . "w2a.isys_workflow_2_isys_workflow_action__isys_workflow_action__id = " . "accept.isys_workflow_action__id \n";

            $l_where = " AND (accept.isys_workflow_action__isys_workflow_action_type__id = '" . C__WORKFLOW__ACTION__TYPE__ACCEPT . "') ";
        }

        /* -------------------------------------------------------------------------- */

        $l_sql .= " LEFT JOIN isys_contact_2_isys_obj AS con ON ";

        switch ($p_action_type)
        {
            case C__WORKFLOW__ACTION__TYPE__ASSIGN:
                $l_sql .= "con.isys_contact_2_isys_obj__isys_contact__id = assign.isys_workflow_action__isys_contact__id ";
                break;
            case C__WORKFLOW__ACTION__TYPE__ACCEPT:
                $l_sql .= "con.isys_contact_2_isys_obj__isys_contact__id = accept.isys_workflow_action__isys_contact__id ";
                break;
            case C__WORKFLOW__ACTION__TYPE__NEW:
                $l_sql .= "con.isys_contact_2_isys_obj__isys_contact__id = isys_workflow__isys_contact__id ";
                break;
            default:
                $l_sql .= "con.isys_contact_2_isys_obj__isys_contact__id = new.isys_workflow_action__isys_contact__id ";
                break;
        }

        $l_sql .= "LEFT JOIN isys_contact_2_isys_obj AS con_creator ON con_creator.isys_contact_2_isys_obj__isys_contact__id = wf.isys_workflow__isys_contact__id " . "LEFT JOIN isys_obj AS creator ON creator.isys_obj__id = con_creator.isys_contact_2_isys_obj__isys_obj__id ";

        //$l_sql .= "LEFT JOIN isys_obj creator ON isys_contact_2_isys_obj__isys_obj__id = creator.isys_obj__id ";

        $l_sql .= "WHERE TRUE ";
        /* -------------------------------------------------------------------------- */

        if (!empty($p_workflow_type))
        {
            $l_sql .= " AND isys_workflow__isys_workflow_type__id = " . $this->convert_sql_id($p_workflow_type) . ' ';
        }

        /* -------------------------------------------------------------------------- */

        $l_sql .= $l_where;

        if (!empty($p_filter))
        {
            $l_sql .= $p_filter;
        }

        /* -------------------------------------------------------------------------- */
        if (!empty($p_user_id))
        {
            /* Retrieve Groups */
            $l_contact_person_dao = new isys_contact_dao_person($this->get_database_component());
            $l_groups_res         = $l_contact_person_dao->get_groups_by_id($p_user_id);
            $l_user_groups        = [$p_user_id];

            if ($l_groups_res->num_rows())
            {
                while ($l_row = $l_groups_res->get_row())
                {
                    $l_user_groups[] = $l_row['isys_person_2_group__isys_obj__id__group'];
                }
            }
            $l_sql .= " AND con.isys_contact_2_isys_obj__isys_obj__id " . $this->get_database_component()
                    ->escape_string($p_owner_mode) . " IN(" . implode(",", $l_user_groups) . ")";
        }

        /* -------------------------------------------------------------------------- */

        if (is_numeric($p_action_type) && $p_action_type != C__WORKFLOW__ACTION__TYPE__ASSIGN)
        {
            $l_sql .= " AND isys_workflow_action_type__id = " . $this->convert_sql_id($p_action_type);
        }

        /* -------------------------------------------------------------------------- */

        if (is_numeric($p_id))
        {
            $l_sql .= " AND isys_workflow__id = " . $this->convert_sql_id($p_id);
        }

        /* -------------------------------------------------------------------------- */

        if (is_numeric($p_parent_workflow__id))
        {
            $l_sql .= " AND isys_workflow__isys_workflow__id = " . $this->convert_sql_id($p_parent_workflow__id);
        }

        /* -------------------------------------------------------------------------- */

        if ($p_workflow_type == C__WORKFLOW_TYPE__CHECKLIST)
        {
            $l_sql .= " AND end_date.isys_workflow_action_parameter__key LIKE '%end_date%'";
        }

        /* -------------------------------------------------------------------------- */

        if (!empty($p_date_from) && $p_workflow_type != '0' && ($p_workflow_type == C__WORKFLOW_TYPE__TASK || $p_workflow_type == C__WORKFLOW_TYPE__CHECKLIST))
        {
            $l_sql .= "\n AND " . "((date_format(start_date.isys_workflow_action_parameter__datetime, '%Y-%m-%d') >= '" . $p_date_from . "')";
            $l_connector = "OR";

        }
        else $l_connector = "AND";

        if (!empty($p_date_to) && $p_workflow_type != '0' && ($p_workflow_type == C__WORKFLOW_TYPE__TASK || $p_workflow_type == C__WORKFLOW_TYPE__CHECKLIST))
        {
            $l_sql .= "\n " . $l_connector . " " . "(date_format(end_date.isys_workflow_action_parameter__datetime, '%Y-%m-%d') <= '" . $p_date_to . "'))";

        }

        /* -------------------------------------------------------------------------- */

        if (!empty($p_status))
        {
            $l_sql .= " AND isys_workflow__status = " . $this->convert_sql_id($p_status);
        }

        /* -------------------------------------------------------------------------- */

        $l_sql .= " GROUP BY isys_workflow__id ";

        /* -------------------------------------------------------------------------- */

        if (!empty($p_order_by))
        {
            $l_sql .= " ORDER BY " . $this->m_db->escape_string($p_order_by);
        }

        if (is_numeric($p_limit))
        {
            $l_sql .= " LIMIT " . $p_limit;
        }

        return $this->retrieve($l_sql);
    }

    /**
     * @desc Get all workflows, without any joins
     *
     * @param int $p_id
     *
     * @return isys_component_dao_result
     */
    public function get_workflows_clean($p_id = null, $p_parent_workflow__id = null)
    {
        $l_sql = "SELECT * FROM isys_workflow WHERE TRUE";

        if (!is_null($p_id))
        {
            $l_sql .= " AND isys_workflow__id = " . $this->convert_sql_id($p_id);
        }
        if (!is_null($p_parent_workflow__id))
        {
            $l_sql .= " AND isys_workflow__isys_workflow__id = " . $this->convert_sql_id($p_parent_workflow__id);
        }

        return $this->retrieve($l_sql);
    }

    public function get_workflow_list($p_exclude = null)
    {
        $l_res    = $this->get_workflows_clean();
        $l_result = [];

        if ($l_res->num_rows())
        {
            while ($l_row = $l_res->get_row())
            {
                $l_result[$l_row['isys_workflow__id']] = $l_row['isys_workflow__title'];
            }

            if ($p_exclude && isset($l_result[$p_exclude])) unset($l_result[$p_exclude]);
        }

        return $l_result;
    }

    /**
     * Creates a workflow.
     *
     * @param string   $p_title
     * @param integer  $p_contact__id
     * @param integer  $p_workflow_type__id
     * @param integer  $p_category__id
     * @param integer  $p_object__id
     * @param integer  $p_occurrence
     * @param integer  $p_exception
     * @param integer  $p_parent_workflow_id
     *
     * @return integer
     * @throws isys_exception_dao
     */
    public function create_workflow($p_title, $p_contact__id, $p_workflow_type__id, $p_category__id, $p_object__id, $p_occurrence = 0, $p_exception = 0, $p_parent_workflow_id = null)
    {
        if (!is_numeric($p_occurrence))
        {
            $p_occurrence = 0;
        } // if

        if (!is_numeric($p_exception))
        {
            $p_exception = 0;
        } // if

        $l_parent_workflow = '';

        if (!empty($p_parent_workflow_id))
        {
            $l_parent_workflow = 'isys_workflow__isys_workflow__id = ' . $this->convert_sql_id($p_parent_workflow_id) . ', ';
        } // if

        $l_sql = 'INSERT INTO isys_workflow SET
            isys_workflow__isys_contact__id = ' . $this->convert_sql_id($p_contact__id) . ',
            isys_workflow__isys_workflow_type__id = ' . $this->convert_sql_id($p_workflow_type__id) . ',
            isys_workflow__isys_workflow_category__id = ' . $this->convert_sql_id($p_category__id) . ',
            ' . $l_parent_workflow . '
            isys_workflow__title = ' . $this->convert_sql_text($p_title) . ',
            isys_workflow__occurrence = ' . $this->convert_sql_int($p_occurrence) . ',
            isys_workflow__exception = ' . $this->convert_sql_int($p_exception) . ',
            isys_workflow__datetime = ' . $this->convert_sql_datetime(time()) . ',
            isys_workflow__property = 0,
            isys_workflow__sort = 0,
            isys_workflow__status = ' . $this->convert_sql_id(C__RECORD_STATUS__NORMAL) . ';';

        if ($this->update($l_sql) && $this->apply_update())
        {
            $this->m_id = $this->get_last_insert_id();
            $this->link_objects([$p_object__id]);

            return $this->m_id;
        } // if

        return -1;
    } // function

    /**
     *
     * @param   integer $p_workflow_id
     *
     * @return  mixed
     * @throws  isys_exception_database
     */
    public function get_linked_objects($p_workflow_id = null)
    {
        $p_workflow_id = (!empty($p_workflow_id)) ? $p_workflow_id : $this->m_id;

        if (!empty($p_workflow_id))
        {
            $l_linked_objects = [];
            $l_sql            = "SELECT isys_workflow_2_isys_obj__isys_obj__id
				FROM isys_workflow_2_isys_obj
				WHERE isys_workflow_2_isys_obj__isys_workflow__id = " . $this->convert_sql_id($p_workflow_id) . ";";

            $l_res = $this->retrieve($l_sql);

            if (count($l_res))
            {
                while ($l_row = $l_res->get_row())
                {
                    $l_linked_objects[] = $l_row['isys_workflow_2_isys_obj__isys_obj__id'];
                } // while

                return $l_linked_objects;
            } // if
        } // if

        return false;
    } // function

    public function link_objects(array $p_object_ids = [], $p_workflow_id = null)
    {
        if (count($p_object_ids))
        {
            $p_workflow_id = (!empty($p_workflow_id)) ? $p_workflow_id : $this->m_id;

            if (!empty($p_workflow_id))
            {
                $l_sql          = "INSERT INTO isys_workflow_2_isys_obj VALUES ";
                $l_sql_addition = '';

                foreach ($p_object_ids AS $l_object_id)
                {
                    if (is_array($l_object_id) && count($l_object_id) > 0)
                    {
                        foreach ($l_object_id AS $l_real_obj_id)
                        {
                            if ($l_real_obj_id)
                            {
                                $l_sql_addition .= "(NULL, " . $this->convert_sql_id($p_workflow_id) . ", " . $this->convert_sql_id($l_real_obj_id) . "),";
                            }
                        }
                    }
                    else
                    {
                        if ($l_object_id)
                        {
                            $l_sql_addition .= "(NULL, " . $this->convert_sql_id($p_workflow_id) . ", " . $this->convert_sql_id($l_object_id) . "),";
                        }
                    }
                }

                if ($l_sql_addition)
                {
                    $l_sql .= $l_sql_addition;
                    $l_sql[strlen($l_sql) - 1] = ";";

                    return ($this->update($l_sql) && $this->apply_update());
                }
            }
        }

        return false;
    }

    public function clear_linked_objects($p_workflow_id = null)
    {
        $p_workflow_id = (!empty($p_workflow_id)) ? $p_workflow_id : $this->m_id;

        if (!empty($p_workflow_id))
        {
            $l_sql = "DELETE FROM isys_workflow_2_isys_obj WHERE isys_workflow_2_isys_obj__isys_workflow__id = " . $this->convert_sql_id($p_workflow_id) . ";";

            return ($this->update($l_sql) && $this->apply_update());
        }
        else
        {
            return false;
        }
    }

    /**
     * Modifies a workflow
     *
     * @param integer  $p_workflow_id
     * @param string   $p_title
     * @param integer  $p_object__id
     * @param integer  $p_category
     * @param integer  $p_occurrence
     * @param integer  $p_parent
     * @param integer  $p_occurrence_exception
     *
     * @return bool|int
     * @throws isys_exception_dao
     */
    public function modify_workflow($p_workflow_id, $p_title, $p_object__id, $p_category, $p_occurrence, $p_parent, $p_occurrence_exception = null)
    {
        $l_data = [
            'isys_workflow__isys_obj__id = ' . $this->convert_sql_id($p_object__id)
        ];

        if (!empty($p_title))
        {
            $l_data[] = 'isys_workflow__title = ' . $this->convert_sql_text($p_title);
        } // if

        if (!empty($p_category))
        {
            $l_data[] = 'isys_workflow__isys_workflow_category__id  = ' . $this->convert_sql_id($p_category);
        } // if

        if (!empty($p_occurrence))
        {
            $l_data[] = 'isys_workflow__occurrence = ' . $this->convert_sql_int($p_occurrence);
        } // if

        if (!empty($p_parent))
        {
            $l_data[] = 'isys_workflow__isys_workflow__id = ' . $this->convert_sql_id($p_parent);
        } // if

        if ($p_occurrence_exception !== null)
        {
            $l_data[] = 'isys_workflow__exception = ' . $this->convert_sql_int($p_occurrence_exception);
        } // if

        $l_sql = 'UPDATE isys_workflow SET ' . implode(', ', $l_data) . ' WHERE isys_workflow__id = ' . $this->convert_sql_id($p_workflow_id);

        if ($this->update($l_sql))
        {
            $this->clear_linked_objects($p_workflow_id);
            $this->link_objects([$p_object__id], $p_workflow_id);

            return $this->apply_update();
        } // if

        return -1;
    } // function

    /**
     * @desc BE CAREFULL, THIS ONE IS REALLY KILLING YOUR WORKFLOW ;)
     */
    public function delete($p_workflow__id)
    {
        $l_sql = "DELETE FROM isys_workflow WHERE isys_workflow__id = " . $this->convert_sql_id($p_workflow__id);

        if ($this->update($l_sql))
        {
            return $this->apply_update();
        }
    }

    /**
     * @desc return workflow title by id
     *
     * @param int $p_workflow__id
     *
     * @return string
     */
    public function get_title_by_id($p_workflow__id)
    {
        $l_workflow_data = $this->get_workflows($p_workflow__id)
            ->get_row();

        return $l_workflow_data["isys_workflow__title"];
    }

    /**
     * Get object id by workflow id
     *
     * @param int $p_workflow__id
     *
     * @return int
     */
    public function get_object($p_workflow__id)
    {
        $l_workflow_data = $this->get_workflows($p_workflow__id)
            ->get_row();

        return intval($l_workflow_data["isys_workflow__isys_obj__id"]);
    }

    /**
     * @param   integer $p_workflow_id
     *
     * @return  array
     * @throws  Exception
     * @throws  isys_exception_database
     */
    public function get_assgined_objects($p_workflow_id)
    {
        $l_return = [];
        $l_res    = $this->retrieve(
            "SELECT isys_workflow_2_isys_obj__isys_obj__id FROM isys_workflow_2_isys_obj WHERE isys_workflow_2_isys_obj__isys_workflow__id = " . $this->convert_sql_id(
                $p_workflow_id
            ) . ";"
        );
        if (count($l_res))
        {
            while ($l_row = $l_res->get_row())
            {
                $l_return[] = $l_row['isys_workflow_2_isys_obj__isys_obj__id'];
            } // while
        } // if

        return $l_return;
    } // function

    /**
     * Return workflow type id by workflow id.
     *
     * @param   integer $p_workflow__id
     *
     * @return  integer
     */
    public function get_workflow_type_by_id($p_workflow__id)
    {
        return (int) $this->get_workflows($p_workflow__id)
            ->get_row_value('isys_workflow__isys_workflow_type__id');
    } // function

    /**
     *
     * @param   integer $p_id
     *
     * @return  isys_component_dao_result
     * @throws  isys_exception_database
     */
    public function get_status($p_id = null)
    {
        $l_sql = "SELECT * FROM isys_workflow_status WHERE TRUE";

        if (!is_null($p_id))
        {
            $l_sql .= " AND isys_workflow_status__id = " . $this->convert_sql_id($p_id);
        } // if

        return $this->retrieve($l_sql . ';');
    } // function

    /**
     * Get the status of a workflow by its ID.
     *
     * @param   integer $p_workflow_id
     *
     * @return  integer
     */
    public function get_workflow_status($p_workflow_id)
    {
        return (int) $this->retrieve('SELECT isys_workflow__status FROM isys_workflow WHERE isys_workflow__id = ' . $this->convert_sql_id($p_workflow_id) . ';')
            ->get_row_value('isys_workflow__status');
    } // function

    /**
     * @desc sets a new status for the given workflow ID.
     *
     * @param   integer $p_workflow__id
     * @param   integer $p_status
     *
     * @return  boolean
     */
    public function set_status($p_workflow__id, $p_status)
    {
        $l_sql = "UPDATE isys_workflow SET isys_workflow__status = " . $this->convert_sql_int($p_status) . " WHERE isys_workflow__id = " . $this->convert_sql_id(
                $p_workflow__id
            ) . ";";

        return $this->update($l_sql) && $this->apply_update();
    } // function

    /**
     * Get assigned contacts to a workflow
     *
     * @author Selcuk Kekec <skekec@i-doit.com>
     *
     * @param type $p_workflow_id
     *
     * @return array Object-IDs of contacts
     */
    public function get_assigned_contacts($p_workflow_id)
    {
        $l_person_ids    = [];
        $l_sql = "SELECT * FROM isys_workflow
            INNER JOIN isys_workflow_2_isys_workflow_action ON isys_workflow_2_isys_workflow_action__isys_workflow__id = isys_workflow__id
            INNER JOIN isys_workflow_action ON isys_workflow_2_isys_workflow_action__isys_workflow_action__id = isys_workflow_action__id
            INNER JOIN isys_workflow_action_type ON isys_workflow_action_type__id = isys_workflow_action__isys_workflow_action_type__id
            WHERE isys_workflow__id = " . $this->convert_sql_id($p_workflow_id) . "
            AND isys_workflow_action_type__const = " . $this->convert_sql_text('C__WORKFLOW__ACTION__TYPE__ASSIGN') . ";";

        $l_workflowRes = $this->retrieve($l_sql);

        if ($l_workflowRes->num_rows())
        {
            $l_workflowData = $l_workflowRes->get_row();

            $l_dao_reference = new isys_contact_dao_reference($this->get_database_component());
            $l_dao_reference->load($l_workflowData['isys_workflow_action__isys_contact__id']);

            $l_data_items = $l_dao_reference->get_data_item_array();
            if (is_array($l_data_items))
            {
                foreach ($l_data_items as $l_key => $l_value)
                {
                    $l_person_ids[] = $l_key;
                } // foreach
            } // if
        } // if

        return $l_person_ids;
    } // function

    /**
     * Retrieve message of workflow.
     *
     * @param   integer $p_workflow_id
     *
     * @return  mixed
     * @author  Selcuk Kekec <skekec@i-doit.com>
     */
    public function get_message($p_workflow_id)
    {
        $l_sql = "SELECT isys_workflow_action_parameter__text
			FROM isys_workflow
			INNER JOIN isys_workflow_2_isys_workflow_action ON isys_workflow_2_isys_workflow_action__isys_workflow__id = isys_workflow__id
			INNER JOIN isys_workflow_action ON isys_workflow_2_isys_workflow_action__isys_workflow_action__id = isys_workflow_action__id
			LEFT JOIN isys_workflow_action_parameter ON isys_workflow_action_parameter__isys_workflow_action__id = isys_workflow_action__id
			WHERE isys_workflow__id = " . $this->convert_sql_id($p_workflow_id) . "
			AND isys_workflow_action_parameter__key = " . $this->convert_sql_text('task__description') . ";";

        $l_res = $this->retrieve($l_sql);

        if (count($l_res))
        {
            return $l_res->get_row_value('isys_workflow_action_parameter__text');
        } // if

        return null;
    }

    /**
     * Get category of workflow.
     *
     * @param   integer $p_workflow_id
     *
     * @return  mixed
     * @throws  isys_exception_database
     * @author  Selcuk Kekec <skekec@i-doit.com>
     */
    public function get_category($p_workflow_id)
    {
        $l_sql = 'SELECT isys_workflow_category__title
			FROM isys_workflow
			INNER JOIN isys_workflow_category ON isys_workflow__isys_workflow_category__id = isys_workflow_category__id
			WHERE isys_workflow__id = ' . $this->convert_sql_id($p_workflow_id) . ';';

        return $this->retrieve($l_sql)
            ->get_row_value('isys_workflow_category__title');
    } // function

    /**
     * Constructor
     *
     * @param  isys_component_database &$p_database
     * @param  integer                 $p_id
     */
    public function __construct(isys_component_database &$p_database, $p_id = 0)
    {
        parent::__construct($p_database);
        $this->m_id = $p_id;
    } // function
} // class