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
 * Maintenance DAO.
 *
 * @package     modules
 * @subpackage  maintenance
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @version     1.0.2
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 * @since       i-doit 1.5.0
 */
class isys_maintenance_dao extends isys_cmdb_dao
{
    /**
     * @var  array
     */
    protected $m_interated_contacts = [];
    /**
     * @var  array
     */
    protected $m_mail_contacts = [];
    /**
     * @var  array
     */
    protected $m_mailtemplate_fields = [
        'isys_maintenance_mailtemplate__id'     => 'convert_sql_id',
        'isys_maintenance_mailtemplate__title'  => 'convert_sql_text',
        'isys_maintenance_mailtemplate__text'   => 'convert_sql_text',
        'isys_maintenance_mailtemplate__status' => 'convert_sql_int'
    ];
    /**
     * This array holds all fields of our "isys_maintenance" table.
     *
     * @var  array
     */
    protected $m_planning_fields = [
        'isys_maintenance__id'                                => 'convert_sql_id',
        'isys_maintenance__isys_maintenance_type__id'         => 'convert_sql_id',
        'isys_maintenance__date_from'                         => 'convert_sql_datetime',
        'isys_maintenance__date_to'                           => 'convert_sql_datetime',
        'isys_maintenance__comment'                           => 'convert_sql_text',
        'isys_maintenance__finished'                          => 'convert_sql_datetime',
        'isys_maintenance__mail_dispatched'                   => 'convert_sql_datetime',
        'isys_maintenance__isys_maintenance_mailtemplate__id' => 'convert_sql_id',
        'isys_maintenance__isys_contact_tag__id'              => 'convert_sql_id',
        'isys_maintenance__status'                            => 'convert_sql_int'
    ];

    /**
     * Method for retrieving planning data.
     *
     * @param   mixed   $p_id
     * @param   string  $p_condition
     * @param   boolean $p_archive
     *
     * @return  isys_component_dao_result
     * @throws  Exception
     * @throws  isys_exception_database
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function get_data($p_id = null, $p_condition = '', $p_archive = null)
    {
        $l_sql = 'SELECT * FROM isys_maintenance
			LEFT JOIN isys_maintenance_type ON isys_maintenance_type__id = isys_maintenance__isys_maintenance_type__id
			LEFT JOIN isys_maintenance_mailtemplate ON isys_maintenance_mailtemplate__id = isys_maintenance__isys_maintenance_mailtemplate__id
			WHERE TRUE';

        if ($p_archive === true)
        {
            $l_sql .= ' AND isys_maintenance__finished IS NULL';
        }
        else if ($p_archive === false)
        {
            $l_sql .= ' AND isys_maintenance__finished IS NOT NULL';
        } // if

        if ($p_id !== null)
        {
            if (is_array($p_id))
            {
                $l_sql .= ' AND isys_maintenance__id ' . $this->prepare_in_condition($p_id);
            }
            else
            {
                $l_sql .= ' AND isys_maintenance__id  = ' . $this->convert_sql_id($p_id);
            } // if
        } // if

        return $this->retrieve($l_sql . ' ' . $p_condition . ';');
    } // function

    /**
     * @param   integer $p_object_id
     * @param   string  $p_from
     * @param   string  $p_to
     *
     * @return  isys_component_dao_result
     * @throws  Exception
     * @throws  isys_exception_database
     */
    public function get_data_by_maintenance_object($p_object_id, $p_from = null, $p_to = null)
    {
        $l_sql = 'SELECT * FROM isys_obj
			INNER JOIN isys_maintenance_2_object ON isys_maintenance_2_object__isys_obj__id = isys_obj__id
			LEFT JOIN isys_maintenance ON isys_maintenance__id = isys_maintenance_2_object__isys_maintenance__id
			WHERE isys_obj__id = ' . $this->convert_sql_id($p_object_id);

        if ($p_from !== null && !empty($p_from))
        {
            $l_sql .= ' AND isys_maintenance__date_from >= ' . $this->convert_sql_datetime($p_from);
        } // if

        if ($p_to !== null && !empty($p_to))
        {
            $l_sql .= ' AND isys_maintenance__date_to <= ' . $this->convert_sql_datetime($p_to);
        } // if

        return $this->retrieve($l_sql . ';');
    } // function

    /**
     * Method for saving a maintenance plan.
     *
     * @param   integer $p_id
     * @param   array   $p_data
     * @param   array   $p_objects
     * @param   array   $p_contacts
     *
     * @return  integer
     * @throws  isys_exception_dao
     * @throws  isys_exception_database
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function save_planning($p_id = null, array $p_data = [], array $p_objects = [], array $p_contacts = [])
    {
        $l_data = [];

        foreach ($p_data as $l_key => $l_value)
        {
            if (isset($this->m_planning_fields[$l_key]))
            {
                $l_data[] = $l_key . ' = ' . call_user_func(
                        [
                            $this,
                            $this->m_planning_fields[$l_key]
                        ],
                        $l_value
                    );
            } // if
        } // foreach

        if (count($l_data))
        {
            if ($p_id === null)
            {
                $l_sql = 'INSERT INTO isys_maintenance SET ' . implode(', ', $l_data);
            }
            else
            {
                $l_sql = 'UPDATE isys_maintenance SET ' . implode(', ', $l_data) . ' WHERE isys_maintenance__id = ' . $this->convert_sql_id($p_id);
            } // if

            if ($this->update($l_sql) && $this->apply_update())
            {
                if ($p_id === null)
                {
                    $p_id = $this->get_last_id_from_table('isys_maintenance');
                } // if
            }
            else
            {
                throw new isys_exception_database($this->m_last_error);
            } // if
        } // if

        if ($p_id > 0 && count($p_objects))
        {
            // First we remove all connected objects.
            $this->update(
                'DELETE FROM isys_maintenance_2_object WHERE isys_maintenance_2_object__isys_maintenance__id = ' . $this->convert_sql_id($p_id) . ';'
            ) && $this->apply_update();

            $l_items = [];

            foreach ($p_objects as $l_object)
            {
                $l_items[] = '(' . $this->convert_sql_id($p_id) . ', ' . $this->convert_sql_id($l_object) . ')';
            } // foreach

            // Now we add all the connected objects.
            $this->update(
                'INSERT INTO isys_maintenance_2_object (isys_maintenance_2_object__isys_maintenance__id, isys_maintenance_2_object__isys_obj__id) VALUES ' . implode(
                    ',',
                    $l_items
                ) . ';'
            ) && $this->apply_update();
        } // if

        if ($p_id > 0 && count($p_contacts))
        {
            // First we remove all connected objects.
            $this->update(
                'DELETE FROM isys_maintenance_2_contact WHERE isys_maintenance_2_contact__isys_maintenance__id = ' . $this->convert_sql_id($p_id) . ';'
            ) && $this->apply_update();

            $l_items = [];

            foreach ($p_contacts as $l_contact)
            {
                $l_items[] = '(' . $this->convert_sql_id($p_id) . ', ' . $this->convert_sql_id($l_contact) . ')';
            } // foreach

            // Now we add all the connected objects.
            $this->update(
                'INSERT INTO isys_maintenance_2_contact (isys_maintenance_2_contact__isys_maintenance__id, isys_maintenance_2_contact__isys_obj__id) VALUES ' . implode(
                    ',',
                    $l_items
                ) . ';'
            ) && $this->apply_update();
        } // if

        return $p_id;
    } // function

    /**
     * Method for retrieving all objects for a certain maintenance plan.
     *
     * @param   integer $p_id
     *
     * @return  isys_component_dao_result
     * @throws  Exception
     * @throws  isys_exception_database
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function get_planning_objects($p_id)
    {
        $l_sql = 'SELECT * FROM isys_obj
			LEFT JOIN isys_obj_type ON isys_obj_type__id = isys_obj__isys_obj_type__id
			LEFT JOIN isys_maintenance_2_object ON isys_maintenance_2_object__isys_obj__id = isys_obj__id
			WHERE isys_maintenance_2_object__isys_maintenance__id = ' . $this->convert_sql_id($p_id) . ';';

        return $this->retrieve($l_sql);
    } // function

    /**
     * Method for retrieving all contacts for a certain maintenance plan.
     *
     * @param   integer $p_id
     *
     * @return  isys_component_dao_result
     * @throws  Exception
     * @throws  isys_exception_database
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function get_planning_contacts($p_id)
    {
        $l_sql = 'SELECT * FROM isys_obj
			LEFT JOIN isys_obj_type ON isys_obj_type__id = isys_obj__isys_obj_type__id
			LEFT JOIN isys_maintenance_2_contact ON isys_maintenance_2_contact__isys_obj__id = isys_obj__id
			WHERE isys_maintenance_2_contact__isys_maintenance__id = ' . $this->convert_sql_id($p_id) . ';';

        return $this->retrieve($l_sql);
    } // function

    /**
     * Method for deleting mailtemplates.
     *
     * @param   array $p_ids
     *
     * @return  boolean
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function delete_planning(array $p_ids)
    {
        if (!count($p_ids))
        {
            return true;
        } // if

        $l_sql         = 'DELETE FROM isys_maintenance WHERE isys_maintenance__id ' . $this->prepare_in_condition($p_ids) . ';';
        $l_object_sql  = 'DELETE FROM isys_maintenance_2_object WHERE isys_maintenance_2_object__isys_maintenance__id ' . $this->prepare_in_condition($p_ids) . ';';
        $l_contact_sql = 'DELETE FROM isys_maintenance_2_contact WHERE isys_maintenance_2_contact__isys_maintenance__id ' . $this->prepare_in_condition($p_ids) . ';';

        return ($this->update($l_sql) && $this->update($l_object_sql) && $this->update($l_contact_sql) && $this->apply_update());
    } // function

    /**
     * Method for retrieving mailtemplate data.
     *
     * @param   mixed  $p_id
     * @param   string $p_condition
     *
     * @return  isys_component_dao_result
     * @throws  Exception
     * @throws  isys_exception_database
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function get_mailtemplates($p_id = null, $p_condition = '')
    {
        $l_sql = 'SELECT * FROM isys_maintenance_mailtemplate WHERE TRUE ' . $p_condition . ' ';

        if ($p_id !== null)
        {
            if (is_array($p_id))
            {
                $l_sql .= 'AND isys_maintenance_mailtemplate__id ' . $this->prepare_in_condition($p_id) . ' ';
            }
            else
            {
                $l_sql .= 'AND isys_maintenance_mailtemplate__id  = ' . $this->convert_sql_id($p_id) . ' ';
            } // if
        } // if

        return $this->retrieve($l_sql . ';');
    } // function

    /**
     * Method for saving a maintenance mailtemplate.
     *
     * @param   integer $p_id
     * @param   array   $p_data
     *
     * @return  integer
     * @throws  isys_exception_dao
     * @throws  isys_exception_database
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function save_mailtemplate($p_id = null, array $p_data = [])
    {
        $l_data = [];

        foreach ($p_data as $l_key => $l_value)
        {
            if (isset($this->m_mailtemplate_fields[$l_key]))
            {
                $l_data[] = $l_key . ' = ' . call_user_func(
                        [
                            $this,
                            $this->m_mailtemplate_fields[$l_key]
                        ],
                        $l_value
                    );
            } // if
        } // foreach

        if (count($l_data))
        {
            if ($p_id === null)
            {
                $l_sql = 'INSERT INTO isys_maintenance_mailtemplate SET ' . implode(', ', $l_data);
            }
            else
            {
                $l_sql = 'UPDATE isys_maintenance_mailtemplate SET ' . implode(', ', $l_data) . ' WHERE isys_maintenance_mailtemplate__id = ' . $this->convert_sql_id($p_id);
            } // if

            if ($this->update($l_sql) && $this->apply_update())
            {
                if ($p_id === null)
                {
                    $p_id = $this->get_last_id_from_table('isys_maintenance_mailtemplate');
                } // if
            }
            else
            {
                throw new isys_exception_database($this->m_last_error);
            } // if
        } // if

        return $p_id;
    } // function

    /**
     * Method for deleting mailtemplates.
     *
     * @param   array $p_ids
     *
     * @return  boolean
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function delete_mailtemplate(array $p_ids)
    {
        if (!count($p_ids))
        {
            return true;
        } // if

        $l_sql = 'DELETE FROM isys_maintenance_mailtemplate WHERE isys_maintenance_mailtemplate__id ' . $this->prepare_in_condition($p_ids) . ';';

        return ($this->update($l_sql) && $this->apply_update());
    } // function

    /**
     * Method to find out if the given object is currently in maintenance.
     *
     * @param   integer $p_obj_id
     * @param   string  $p_date
     *
     * @return  boolean
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function is_in_maintenance($p_obj_id, $p_date = null)
    {
        if ($p_obj_id > 0)
        {
            if ($p_date === null)
            {
                // ID-1933  Because of the data type DATE the "NOW()" function will not work, so we need "CURDATE()".
                $p_date = 'CURDATE()';
            } // if

            $l_sql = 'SELECT isys_maintenance__id FROM isys_maintenance
				INNER JOIN isys_maintenance_2_object ON isys_maintenance_2_object__isys_maintenance__id = isys_maintenance__id
				WHERE isys_maintenance_2_object__isys_obj__id = ' . $this->convert_sql_id($p_obj_id) . '
				AND ((isys_maintenance__date_from <= ' . $this->convert_sql_datetime($p_date) . ' AND isys_maintenance__date_to >= ' . $this->convert_sql_datetime($p_date) . ')
                OR (isys_maintenance__date_from IS NULL AND isys_maintenance__date_to >= ' . $this->convert_sql_datetime($p_date) . ')
                OR (isys_maintenance__date_from <= ' . $this->convert_sql_datetime($p_date) . ' AND isys_maintenance__date_to IS NULL))
				LIMIT 1;';

            return !!count($this->retrieve($l_sql));
        } // if

        return false;
    } // function

    /**
     * Returns a list of filtered "planning" rows.
     *
     * @param   string  $p_from
     * @param   string  $p_to
     * @param   boolean $p_order_by_startdate
     * @param   integer $p_type_id
     *
     * @return  isys_component_dao_result
     * @throws  Exception
     * @throws  isys_exception_database
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function get_filtered_planning_list($p_from = null, $p_to = null, $p_order_by_startdate = null, $p_type_id = null)
    {
        $l_sql = 'SELECT * FROM isys_obj
			LEFT JOIN isys_obj_type ON isys_obj_type__id = isys_obj__isys_obj_type__id
			LEFT JOIN isys_maintenance_2_object ON isys_maintenance_2_object__isys_obj__id = isys_obj__id
			LEFT JOIN isys_maintenance ON isys_maintenance__id = isys_maintenance_2_object__isys_maintenance__id
			LEFT JOIN isys_contact_tag ON isys_contact_tag__id = isys_maintenance__isys_contact_tag__id
			WHERE isys_obj__status = ' . $this->convert_sql_int(C__RECORD_STATUS__NORMAL) . '
			AND isys_maintenance__status = ' . $this->convert_sql_int(C__RECORD_STATUS__NORMAL);

        if ($p_from !== null && !empty($p_from))
        {
            $l_sql .= ' AND isys_maintenance__date_from >= ' . $this->convert_sql_datetime($p_from);
        } // if

        if ($p_to !== null && !empty($p_to))
        {
            $l_sql .= ' AND isys_maintenance__date_to <= ' . $this->convert_sql_datetime($p_to);
        } // if

        if ($p_type_id !== null && $p_type_id > 0)
        {
            $l_sql .= ' AND isys_maintenance__isys_maintenance_type__id = ' . $this->convert_sql_id($p_type_id);
        } // if

        if ($p_order_by_startdate !== null)
        {
            if ($p_order_by_startdate)
            {
                $l_sql .= ' ORDER BY isys_maintenance__date_from ASC';
            }
            else
            {
                $l_sql .= ' ORDER BY isys_maintenance__date_from DESC';
            } // if
        } // if

        return $this->retrieve($l_sql);
    } // function

    /**
     * Method for finishing a maintenance plan + adding a entry to the logbook.
     *
     * @param   integer $p_id
     * @param   string  $p_comment
     *
     * @return  boolean
     * @throws  isys_exception_database
     * @throws  isys_exception_general
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function finish_maintenance_planning($p_id, $p_comment)
    {
        $this->save_planning($p_id, ['isys_maintenance__finished' => 'NOW()']);

        $l_object_res = $this->get_planning_objects($p_id);

        if (!count($l_object_res))
        {
            throw new isys_exception_general(_L('LC__MAINTENANCC__EXCEPTION__NO_OBJECTS_SELECTED'));
        } // if

        while ($l_object_row = $l_object_res->get_row())
        {
            isys_component_dao_logbook::instance($this->m_db)
                ->set_entry(
                    _L('LC__MAINTENANCE__PLANNING__FINISHED'),
                    $this->get_last_query(),
                    null,
                    C__LOGBOOK__ALERT_LEVEL__0,
                    $l_object_row['isys_obj__id'],
                    $l_object_row['isys_obj__title'],
                    $l_object_row['isys_obj_type__title'],
                    'LC__CMDB__CATG__LOCATION',
                    C__LOGBOOK_SOURCE__MAINTENANCE,
                    serialize([]),
                    $p_comment,
                    null
                );
        } // while

        return true;
    } // function

    /**
     * This method will send the mails to all necessary contacts from the the maintenance planning.
     *
     * @param   integer $p_id
     *
     * @return  boolean
     * @throws  isys_exception_general
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function send_maintenance_planning_mail($p_id)
    {
        global $g_loc;

        /*  @var  $l_contact_dao  isys_cmdb_dao_category_g_contact */
        $l_contact_dao = isys_cmdb_dao_category_g_contact::instance($this->m_db);
        $l_contact_res = $this->get_planning_contacts($p_id);
        $l_contacts    = [];

        if (count($l_contact_res))
        {
            while ($l_contact_row = $l_contact_res->get_row())
            {
                $this->get_email_recipients($l_contact_row);
            } // while
        } // if

        $l_object_res = $this->get_planning_objects($p_id);

        if (!count($l_object_res))
        {
            throw new isys_exception_general(_L('LC__MAINTENANCC__EXCEPTION__NO_OBJECTS_SELECTED'));
        } // if

        $l_maintenance = $this->get_data($p_id)
            ->get_row();

        if (!$l_maintenance['isys_maintenance__isys_maintenance_mailtemplate__id'])
        {
            throw new isys_exception_general(_L('LC__MAINTENANCC__EXCEPTION__NO_MAILTEMPLATE_SELECTED'));
        } // if

        while ($l_object_row = $l_object_res->get_row())
        {
            // Add the contacts from each object via role.
            if ($l_maintenance['isys_maintenance__isys_contact_tag__id'] > 0)
            {
                $l_contact_res = $l_contact_dao->get_contact_objects_by_tag($l_object_row['isys_obj__id'], $l_maintenance['isys_maintenance__isys_contact_tag__id']);

                if (count($l_contact_res))
                {
                    while ($l_contact_row = $l_contact_res->get_row())
                    {
                        $this->get_email_recipients($l_contact_row);
                    } // while
                } // if
            } // if

            if (!count($this->m_mail_contacts))
            {
                throw new isys_exception_general(_L('LC__MAINTENANCC__EXCEPTION__NO_RECIPIENTS'));
            } // if

            // And finally we generate the email and send it.
            $l_mailer = new isys_library_mail();

            // Replace variables in our subject and mail body.
            $l_subject   = isys_helper_link::handle_url_variables(
                $l_mailer->get_subject() . ' ' . $l_maintenance['isys_maintenance_mailtemplate__title'],
                $l_object_row['isys_obj__id']
            );
            $l_mail_body = isys_helper_link::handle_url_variables($l_maintenance['isys_maintenance_mailtemplate__text'], $l_object_row['isys_obj__id']);

            $l_mailer->set_charset('UTF-8');
            $l_mailer->set_content_type(isys_library_mail::C__CONTENT_TYPE__PLAIN);
            $l_mailer->set_backend(isys_library_mail::C__BACKEND__SMTP);

            foreach ($this->m_mail_contacts as $l_contact)
            {
                $l_mailer->AddAddress($l_contact['email'], $l_contact['name']);
                $l_contacts[] = $l_contact['salutation'] . $l_contact['name'];
            } //foreach contact

            // Max. line length:
            $l_lengths = array_map('strlen', explode(PHP_EOL, $l_mail_body));
            sort($l_lengths, SORT_NUMERIC);
            $l_mailer->WordWrap = end($l_lengths);

            // These variables are used additionally.
            $l_variables = [
                '%recipients%'       => implode(', ', $l_contacts),
                '%maintenance_from%' => $g_loc->fmt_date($l_maintenance['isys_maintenance__date_from']),
                '%maintenance_to%'   => $g_loc->fmt_date($l_maintenance['isys_maintenance__date_to'])
            ];

            $l_subject   = strtr($l_subject, $l_variables);
            $l_mail_body = strtr($l_mail_body, $l_variables);

            $l_mailer->set_subject($l_subject);
            $l_mailer->set_body($l_mail_body);
            $l_mailer->add_default_signature();

            $l_mailer->send();

            $this->save_planning($p_id, ['isys_maintenance__mail_dispatched' => 'NOW()']);
        } // while

        return true;
    } // function

    /**
     * This method will collect all necessary email addresses, even recursively (if we select persongroups).
     *
     * @param   array $p_contact
     *
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    protected function get_email_recipients($p_contact)
    {
        if (!isset($this->m_interated_contacts[$p_contact['isys_obj__id']]))
        {
            $this->m_interated_contacts[$p_contact['isys_obj__id']] = true;

            // Shall the recipients be resolved (if it's a organization or person group).
            $l_resolve_groups = (isys_tenantsettings::get(
                    'maintenance.email.recipients',
                    C__MAINTENANCE__RECIPIENTS__RESOLVE_CONTACT_GROUPS
                ) == C__MAINTENANCE__RECIPIENTS__RESOLVE_CONTACT_GROUPS);

            $l_email_address   = isys_cmdb_dao_category_g_mail_addresses::instance($this->m_db)->get_primary_mail_as_string_by_obj_id($p_contact['isys_obj__id']);
            $l_is_person       = $this->objtype_is_cats_assigned($p_contact['isys_obj__isys_obj_type__id'], C__CATS__PERSON);
            $l_is_organizaion  = $this->objtype_is_cats_assigned($p_contact['isys_obj__isys_obj_type__id'], C__CATS__ORGANIZATION);
            $l_is_person_group = $this->objtype_is_cats_assigned($p_contact['isys_obj__isys_obj_type__id'], C__CATS__PERSON_GROUP);

            if (!$l_resolve_groups || $l_is_person)
            {
                $l_person_dao = isys_cmdb_dao_category_s_person_master::instance($this->m_db);

                $l_salutation_value = $l_person_dao->get_data(null, $p_contact['isys_obj__id'])
                    ->get_row_value('isys_cats_person_list__salutation');
                $l_salutation       = $l_person_dao->callback_property_salutation();

                if (!$l_email_address)
                {
                    return;
                } // if

                $this->m_mail_contacts[$p_contact['isys_obj__id']] = [
                    'email'      => $l_email_address,
                    'name'       => $p_contact['isys_obj__title'],
                    'salutation' => (isset($l_salutation[$l_salutation_value]) ? $l_salutation[$l_salutation_value] . ' ' : null)
                ];
            }
            else
            {
                if ($l_is_person_group)
                {
                    // We're dealing with a person group.
                    $l_person_res = isys_cmdb_dao_category_s_person_group_members::instance($this->m_db)
                        ->get_data(null, $p_contact['isys_obj__id'], '', null, C__RECORD_STATUS__NORMAL);

                    if (count($l_person_res))
                    {
                        while ($l_person_row = $l_person_res->get_row())
                        {
                            $this->get_email_recipients(
                                [
                                    'isys_obj__id'                => $l_person_row['person_id'],
                                    'isys_obj__title'             => $l_person_row['person_title'],
                                    'isys_obj__isys_obj_type__id' => $l_person_row['person_type'],
                                ]
                            );
                        } // while
                    } // if
                }
                else if ($l_is_organizaion)
                {
                    // We're dealing with a organization.
                    $l_person_res = isys_cmdb_dao_category_s_organization_person::instance($this->m_db)
                        ->get_data(null, $p_contact['isys_obj__id'], '', null, C__RECORD_STATUS__NORMAL);

                    if (count($l_person_res))
                    {
                        while ($l_person_row = $l_person_res->get_row())
                        {
                            $this->get_email_recipients($l_person_row);
                        } // while
                    } // if
                } // if
            } // if
        } // if
    } // function
} // class