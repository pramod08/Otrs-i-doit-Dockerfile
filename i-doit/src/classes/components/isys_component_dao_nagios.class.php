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
 * DAO for nagios settings
 *
 * @package     i-doit
 * @subpackage  Components
 * @author      Dennis Bluemer <dbluemer@synetics.de>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_component_dao_nagios extends isys_component_dao
{
    /**
     * This variable will hold the ndo database component.
     *
     * @var  isys_component_database
     */
    protected $m_ndo_db = null;

    /**
     * This variable will hold the ndo database prefix.
     *
     * @var  string
     */
    protected $m_ndo_db_prefix = null;

    /**
     * Return an associative array of the NDO server configuration by its ID.
     *
     * @static
     *
     * @param   integer $p_id
     *
     * @return  array
     */
    public static function get_ndo_config($p_id)
    {
        global $g_comp_database;

        return isys_monitoring_dao_hosts::instance($g_comp_database)
            ->get_data($p_id, C__MONITORING__TYPE_NDO)
            ->get_row();
    } // function

    /**
     * Method for finding out if the given NDO is active.
     *
     * @param   integer $p_id
     *
     * @return  boolean
     */
    public function is_ndo_instance_active($p_id)
    {
        $l_nagiosSettings = $this->get_ndo_config($p_id);

        return ($l_nagiosSettings['isys_monitoring_hosts__active'] > 0);
    } // function

    /**
     * This method will set the NDO database and prefix.
     *
     * @param   isys_component_database $p_ndo_db
     * @param   string                  $p_ndo_prefix
     *
     * @return  isys_component_dao_nagios
     */
    public function set_ndo($p_ndo_db, $p_ndo_prefix)
    {
        $this->m_ndo_db        = $p_ndo_db;
        $this->m_ndo_db_prefix = $p_ndo_prefix;

        return $this;
    } // function

    /**
     * Method for retrieving the nagios configuration.
     *
     * @return  isys_component_dao_result
     */
    public function getConfig()
    {
        return $this->retrieve(
            'SELECT isys_nagios_config__key AS "key", isys_nagios_config__value AS "value" FROM isys_nagios_config WHERE isys_nagios_config__configfile_id = 1;'
        );
    } // function

    /**
     * Method for saving the nagios configuration.
     *
     * @param   array $p_postdata
     *
     * @return  boolean
     */
    public function saveConfig($p_postdata)
    {
        $l_keys = $this->getConfigKeys();

        foreach ($l_keys as $l_key)
        {
            $l_value = $p_postdata["C__MODULE__NAGIOS__" . $l_key];

            if (is_array($l_value))
            {
                $l_value = serialize($l_value);
            } // if

            $l_query = 'UPDATE isys_nagios_config
				SET isys_nagios_config__value = ' . $this->convert_sql_text($l_value) . '
				WHERE isys_nagios_config__key = ' . $this->convert_sql_text($l_key) . '
				AND isys_nagios_config__configfile_id = 1';

            if (!($this->update($l_query) && $this->apply_update()))
            {
                return false;
            } // if
        } // foreach

        return true;
    } // function

    public function validateNDOPost()
    {
        if (!(isys_glob_is_valid_ip($_POST["C__MODULE__NAGIOS__NDODB_IP"]) && isys_glob_is_valid_ip6($_POST["C__MODULE__NAGIOS__NDODB_IP"])) && !gethostbyname(
                $_POST["C__MODULE__NAGIOS__NDODB_IP"]
            )
        )
        {
            throw new Exception($_POST["C__MODULE__NAGIOS__NDODB_IP"] . " is an invalid address.");
        } // if

        if (gethostbyname($_POST["C__MODULE__NAGIOS__NDODB_IP"]))
        {
            try
            {
                global $g_comp_database, $g_comp_database_system;
                $g_comp_database->close();
                $g_comp_database_system->close();

                $l_db = isys_component_database::get_database(
                    "mysqli",
                    $_POST["C__MODULE__NAGIOS__NDODB_IP"],
                    $_POST["C__MODULE__NAGIOS__NDODB_PORT"],
                    $_POST["C__MODULE__NAGIOS__NDODB_USER"],
                    $_POST["C__MODULE__NAGIOS__NDODB_PASS"],
                    $_POST["C__MODULE__NAGIOS__NDODB_SCHEMA"]
                );

                $g_comp_database->reconnect();
                $g_comp_database_system->reconnect();
            }
            catch (Exception $e)
            {
                $g_comp_database->reconnect();
                $g_comp_database_system->reconnect();

                throw new Exception(_L("LC__MODULE__NAGIOS__NDODB_CONNECTION_FAILED") . ': <p class="p10 m10">' . str_replace("\n", "<br />", $e->getMessage()) . '</p>');
            } // try

            $l_result = $l_db->query("SHOW TABLES LIKE '" . $_POST["C__MODULE__NAGIOS__NDODB_PREFIX"] . "hosts';");

            if ($l_db->num_rows($l_result) < 1)
            {
                throw new Exception('Table ' . $_POST["C__MODULE__NAGIOS__NDODB_PREFIX"] . 'hosts does not exist. Wrong prefix?');
            } // if

            return true;
        }
        else
        {
            throw new Exception(_L('LC__MODULE__NAGIOS__NDODB_CONNECTION_FAILED') . ' - ' . $_POST['C__MODULE__NAGIOS__NDODB_IP'] . ' unreachable or invalid host.');
        } // if
    } // function

    /**
     * Validate method for timeperiod POST data.
     *
     * @param   array $p_post
     *
     * @throws  UnexpectedValueException
     * @return  boolean
     */
    public function validateTimeperiodPost(array $p_post)
    {
        if (empty($p_post["C__MODULE__NAGIOS__TIMEPERIOD_NAME"]) && empty($p_post["C__MODULE__NAGIOS__TIMEPERIOD_ALIAS"]))
        {
            throw new UnexpectedValueException(_('LC___UNIVERSAL__ERROR_INFO_ICON'), 2);
        }
        else if (empty($p_post["C__MODULE__NAGIOS__TIMEPERIOD_NAME"]))
        {
            throw new UnexpectedValueException(_('LC___UNIVERSAL__ERROR_INFO_ICON'), 0);
        }
        else if (empty($p_post["C__MODULE__NAGIOS__TIMEPERIOD_ALIAS"]))
        {
            throw new UnexpectedValueException(_('LC___UNIVERSAL__ERROR_INFO_ICON'), 1);
        } // if

        return true;
    } // function

    /**
     * Validate method for command POST data.
     *
     * @param   array $p_post
     *
     * @return  boolean
     * @throws  UnexpectedValueException
     */
    public function validateCommandPost(array $p_post)
    {
        if (empty($p_post["C__MODULE__NAGIOS__COMMAND_NAME"]) && empty($p_post["C__MODULE__NAGIOS__COMMAND_LINE"]))
        {
            throw new UnexpectedValueException(_('LC___UNIVERSAL__ERROR_INFO_ICON'), 2);
        }
        else if (empty($p_post["C__MODULE__NAGIOS__COMMAND_NAME"]))
        {
            throw new UnexpectedValueException(_('LC___UNIVERSAL__ERROR_INFO_ICON'), 0);
        }
        else if (empty($p_post["C__MODULE__NAGIOS__COMMAND_LINE"]))
        {
            throw new UnexpectedValueException(_('LC___UNIVERSAL__ERROR_INFO_ICON'), 1);
        } // if

        return true;
    } // function

    /**
     * Creates a new timeperiod entry.
     *
     * @return  mixed  Will return the last inserted id or boolean false, on error.
     */
    public function createTimeperiod()
    {
        $this->begin_update();

        $l_query = "INSERT INTO isys_nagios_timeperiods SET isys_nagios_timeperiods__name  = 'timeperiod_name', isys_nagios_timeperiods__alias = 'timeperiod_alias';";

        if ($this->update($l_query) && $this->apply_update())
        {
            return $this->get_last_insert_id();
        } // if

        return false;
    } // function

    /**
     * @return  mixed  Will return the last inserted id or boolean false, on error.
     */
    public function createContact()
    {
        if ($this->update('INSERT INTO isys_nagios_contacts VALUES();') && $this->apply_update())
        {
            return $this->get_last_insert_id();
        } // if

        return false;
    } // function

    /**
     * Updates existing configuration for a time period.
     *
     * @param   integer $p_id Entity's identifier
     *
     * @return  boolean  Success?
     */
    public function saveTimeperiod($p_id)
    {
        $this->begin_update();

        if (isset($_POST['C__MODULE__NAGIOS__DEFAULT_CHECK_PERIOD']) && $_POST['C__MODULE__NAGIOS__DEFAULT_CHECK_PERIOD'] == '1')
        {
            if (!$this->update('UPDATE isys_nagios_timeperiods SET isys_nagios_timeperiods__default_check = 0;'))
            {
                $this->cancel_update();

                return false;
            } // if
        } // if

        if (isset($_POST['C__MODULE__NAGIOS__DEFAULT_NOTIFICATION_PERIOD']) && $_POST['C__MODULE__NAGIOS__DEFAULT_NOTIFICATION_PERIOD'] == '1')
        {
            if (!$this->update('UPDATE isys_nagios_timeperiods SET isys_nagios_timeperiods__default_notification = 0;'))
            {
                $this->cancel_update();

                return false;
            } // if
        } // if

        $l_data = [];

        if (isset($_POST['C__MODULE__NAGIOS__TIMEPERIOD_NAME']))
        {
            $l_data['isys_nagios_timeperiods__name'] = $this->convert_sql_text($_POST['C__MODULE__NAGIOS__TIMEPERIOD_NAME']);
        } // if

        if (isset($_POST['C__MODULE__NAGIOS__TIMEPERIOD_ALIAS']))
        {
            $l_data['isys_nagios_timeperiods__alias'] = $this->convert_sql_text($_POST['C__MODULE__NAGIOS__TIMEPERIOD_ALIAS']);
        } // if

        if (isset($_POST['C__MODULE__NAGIOS__TIMEPERIOD_DEFINITION']))
        {
            $l_data['isys_nagios_timeperiods__definition'] = $this->convert_sql_text($_POST['C__MODULE__NAGIOS__TIMEPERIOD_DEFINITION']);
        } // if

        if (isset($_POST['C__MODULE__NAGIOS__TIMEPERIOD_DEFINITION']))
        {
            $l_data['isys_nagios_timeperiods__definition'] = $this->convert_sql_text($_POST['C__MODULE__NAGIOS__TIMEPERIOD_DEFINITION']);
        } // if

        if (isset($_POST['C__MODULE__NAGIOS__TIMEPERIOD_EXCLUDE__selected_values']))
        {
            $l_data['isys_nagios_timeperiods__exclude'] = $this->convert_sql_text($_POST['C__MODULE__NAGIOS__TIMEPERIOD_EXCLUDE__selected_values']);
        } // if

        if (isset($_POST['C__MODULE__NAGIOS__DEFAULT_CHECK_PERIOD']))
        {
            $l_data['isys_nagios_timeperiods__default_check'] = $this->convert_sql_int($_POST['C__MODULE__NAGIOS__DEFAULT_CHECK_PERIOD']);
        } // if

        if (isset($_POST['C__MODULE__NAGIOS__DEFAULT_NOTIFICATION_PERIOD']))
        {
            $l_data['isys_nagios_timeperiods__default_notification'] = $this->convert_sql_int($_POST['C__MODULE__NAGIOS__DEFAULT_NOTIFICATION_PERIOD']);
        } // if

        $l_prepare_query = [];

        foreach ($l_data as $l_key => $l_value)
        {
            $l_prepare_query[] = $l_key . ' = ' . $l_value;
        }// foreach

        $l_query = 'UPDATE isys_nagios_timeperiods SET ' . implode(', ', $l_prepare_query) . ' WHERE isys_nagios_timeperiods__id = ' . $this->convert_sql_id($p_id) . ';';

        return ($this->update($l_query) && $this->apply_update());
    } // function

    /**
     * Updates existing Nagios contact.
     *
     * @param   integer $p_id Contact's identifier
     *
     * @return  boolean  Success?
     */
    public function saveContact($p_id)
    {
        $l_data = [];

        if (isset($_POST['C__MODULE__NAGIOS__ALIAS']))
        {
            $l_data['isys_nagios_contacts__alias'] = $this->convert_sql_text($_POST['C__MODULE__NAGIOS__ALIAS']);
        } // if

        if (isset($_POST['C__MODULE__NAGIOS__CONTACT_HOST_NOTIFICATION']))
        {
            $l_data['isys_nagios_contacts__host_notification_enabled'] = $this->convert_sql_text($_POST['C__MODULE__NAGIOS__CONTACT_HOST_NOTIFICATION']);
        } // if

        if (isset($_POST['C__MODULE__NAGIOS__CONTACT_SERVICE_NOTIFICATION']))
        {
            $l_data['isys_nagios_contacts__service_notification_enabled'] = $this->convert_sql_text($_POST['C__MODULE__NAGIOS__CONTACT_SERVICE_NOTIFICATION']);
        } // if

        if (isset($_POST['C__MODULE__NAGIOS__CONTACT_HOST_NOTIFICATION_PERIOD']))
        {
            $l_data['isys_nagios_contacts__host_notification_period'] = $this->convert_sql_text($_POST['C__MODULE__NAGIOS__CONTACT_HOST_NOTIFICATION_PERIOD']);
        } // if

        if (isset($_POST['C__MODULE__NAGIOS__CONTACT_SERVICE_NOTIFICATION_PERIOD']))
        {
            $l_data['isys_nagios_contacts__service_notification_period'] = $this->convert_sql_text($_POST['C__MODULE__NAGIOS__CONTACT_SERVICE_NOTIFICATION_PERIOD']);
        } // if

        if (isset($_POST['C__MODULE__NAGIOS__CONTACT_HOST_NOTIFICATION_OPTIONS__selected_values']))
        {
            $l_data['isys_nagios_contacts__host_notification_options'] = $this->convert_sql_text(
                $_POST['C__MODULE__NAGIOS__CONTACT_HOST_NOTIFICATION_OPTIONS__selected_values']
            );
        } // if

        if (isset($_POST['C__MODULE__NAGIOS__CONTACT_SERVICE_NOTIFICATION_OPTIONS__selected_values']))
        {
            $l_data['isys_nagios_contacts__service_notification_options'] = $this->convert_sql_text(
                $_POST['C__MODULE__NAGIOS__CONTACT_SERVICE_NOTIFICATION_OPTIONS__selected_values']
            );
        } // if

        if (isset($_POST['C__MODULE__NAGIOS__CONTACT_HOST_NOTIFICATION_COMMANDS__selected_values']))
        {
            $l_data['isys_nagios_contacts__host_notification_commands'] = $this->convert_sql_text(
                $_POST['C__MODULE__NAGIOS__CONTACT_HOST_NOTIFICATION_COMMANDS__selected_values']
            );
        } // if

        if (isset($_POST['C__MODULE__NAGIOS__CONTACT_SERVICE_NOTIFICATION_COMMANDS__selected_values']))
        {
            $l_data['isys_nagios_contacts__service_notification_commands'] = $this->convert_sql_text(
                $_POST['C__MODULE__NAGIOS__CONTACT_SERVICE_NOTIFICATION_COMMANDS__selected_values']
            );
        } // if

        if (isset($_POST['C__MODULE__NAGIOS__CONTACT_CAN_SUBMIT_COMMANDS']))
        {
            $l_data['isys_nagios_contacts__can_submit_commands'] = $this->convert_sql_text($_POST['C__MODULE__NAGIOS__CONTACT_CAN_SUBMIT_COMMANDS']);
        } // if

        if (isset($_POST['C__MODULE__NAGIOS__CONTACT_RETAIN_STATUS_INFORMATION']))
        {
            $l_data['isys_nagios_contacts__retain_status_information'] = $this->convert_sql_text($_POST['C__MODULE__NAGIOS__CONTACT_RETAIN_STATUS_INFORMATION']);
        } // if

        if (isset($_POST['C__MODULE__NAGIOS__CONTACT_RETAIN_NONSTATUS_INFORMATION']))
        {
            $l_data['isys_nagios_contacts__retain_nonstatus_information'] = $this->convert_sql_text($_POST['C__MODULE__NAGIOS__CONTACT_RETAIN_NONSTATUS_INFORMATION']);
        } // if

        $l_prepare_query = [];

        foreach ($l_data as $l_key => $l_value)
        {
            $l_prepare_query[] = $l_key . ' = ' . $l_value;
        } // foreach

        $l_query = 'UPDATE isys_nagios_contacts SET ' . implode(', ', $l_prepare_query) . ' WHERE isys_nagios_contacts__id = ' . $this->convert_sql_id($p_id);

        if ($this->update($l_query) && $this->apply_update())
        {
            return true;
        } // if

        return false;
    } // function

    /**
     * Deletes a timeperiod from isys_nagios_timeperiods given by its id $p_id.
     * Returns true on success, false otherwise.
     *
     * @param   integer $p_id
     *
     * @return  boolean
     */
    public function deleteTimeperiod($p_id)
    {
        return ($this->update('DELETE FROM isys_nagios_timeperiods WHERE isys_nagios_timeperiods__id = ' . $this->convert_sql_id($p_id) . ';') && $this->apply_update());
    } // function

    /**
     * Delete a contact from isys_nagios_contacts given by its id $p_id.
     * Returns true on success, false otherwise.
     *
     * @param   integer $p_id
     *
     * @return  boolean
     */
    public function deleteContact($p_id)
    {
        return ($this->update('DELETE FROM isys_nagios_contacts WHERE isys_nagios_contacts__id = ' . $this->convert_sql_id($p_id) . ';') && $this->apply_update());
    } // function

    public function createCommand()
    {
        if ($this->update(
                "INSERT INTO isys_nagios_commands SET isys_nagios_commands__name = 'command_name', isys_nagios_commands__line = 'command_line';"
            ) && $this->apply_update()
        )
        {
            return $this->get_last_insert_id();
        } // if

        return false;
    } // function

    /**
     * Method for creating a new host escalation.
     *
     * @return  integer  Returns the last inserted ID from the table "isys_nagios_host_escalations".
     * @throws  isys_exception_dao
     */
    public function createHostEscalation()
    {
        try
        {
            $l_daoContact = new isys_contact_dao_reference($this->get_database_component());
            $l_daoContact->save();

            if (!($this->update(
                    'INSERT INTO isys_nagios_host_escalations SET isys_nagios_host_escalations__isys_contact__id = ' . $this->convert_sql_id($l_daoContact->get_id()) . ';'
                ) && $this->apply_update())
            )
            {
                throw new isys_exception_dao(
                    $this->get_database_component()
                        ->get_last_error_as_string()
                );
            } // if
        }
        catch (Exception $e)
        {
            throw new isys_exception_dao($e->getMessage());
        } // try

        return $this->get_last_insert_id();
    } // function

    /**
     * @return  integer
     * @throws  isys_exception_dao
     */
    public function createServiceEscalation()
    {
        try
        {
            $l_daoContact = new isys_contact_dao_reference($this->get_database_component());
            $l_daoContact->save();

            $l_update = 'INSERT INTO isys_nagios_service_escalations SET isys_nagios_service_escalations__isys_contact__id = ' . $this->convert_sql_id(
                    $l_daoContact->get_id()
                ) . ';';

            if (!$this->update($l_update))
            {
                throw new isys_exception_dao(
                    $this->get_database_component()
                        ->get_last_error_as_string()
                );
            }

            $l_last_id = $this->get_last_insert_id();
            $this->apply_update();

            return $l_last_id;
        }
        catch (isys_exception_dao $e)
        {
            throw new isys_exception_dao($e->getMessage());
        } // try
    } // function

    /**
     * Method for finding out, if the nagios category is assigned anywhere.
     *
     * @param   integer $p_objtype_id
     *
     * @return  boolean
     */
    public function catNagiosAssigned($p_objtype_id)
    {
        $l_sql = 'SELECT * FROM isys_obj_type_2_isysgui_catg
			INNER JOIN isys_obj_type ON isys_obj_type__id = isys_obj_type_2_isysgui_catg__isys_obj_type__id
			INNER JOIN isysgui_catg ON isysgui_catg__id = isys_obj_type_2_isysgui_catg__isysgui_catg__id
			WHERE isysgui_catg__const = "C__CATG__NAGIOS"
			AND isys_obj_type__id = ' . $this->convert_sql_id($p_objtype_id) . ';';

        return (count($this->retrieve($l_sql)) > 0);
    } // function

    /**
     * Method for saving a host escalation.
     *
     * @param   integer $p_id
     *
     * @return  boolean
     */
    public function saveHostEscalation($p_id)
    {
        $l_query = 'UPDATE isys_nagios_host_escalations
			SET isys_nagios_host_escalations__title = ' . $this->convert_sql_text($_POST['C__MODULE__NAGIOS__TITLE']) . ',
			isys_nagios_host_escalations__first_notification = ' . $this->convert_sql_text($_POST['C__MODULE__NAGIOS__FIRST_NOTIFICATION']) . ',
			isys_nagios_host_escalations__last_notification = ' . $this->convert_sql_text($_POST['C__MODULE__NAGIOS__LAST_NOTIFICATION']) . ',
			isys_nagios_host_escalations__notification_interval = ' . $this->convert_sql_text($_POST['C__MODULE__NAGIOS__NOTIFICATION_INTERVAL']) . ',
			isys_nagios_host_escalations__escalation_period = ' . $this->convert_sql_id($_POST['C__MODULE__NAGIOS__ESCALATION_PERIOD']) . ',
			isys_nagios_host_escalations__escalation_period_plus = ' . $this->convert_sql_id($_POST['C__MODULE__NAGIOS__ESCALATION_PERIOD_PLUS']) . ',
			isys_nagios_host_escalations__escalation_options = ' . $this->convert_sql_text($_POST['C__MODULE__NAGIOS__ESCALATION_OPTIONS__selected_values']) . '
			WHERE isys_nagios_host_escalations__id = ' . $this->convert_sql_id($p_id) . ';';

        return ($this->update($l_query) && $this->apply_update());
    } // function

    /**
     * Method for saving a service escalation.
     *
     * @param   integer $p_id
     *
     * @return  boolean
     */
    public function saveServiceEscalation($p_id)
    {
        $l_query = 'UPDATE isys_nagios_service_escalations
			SET isys_nagios_service_escalations__title = ' . $this->convert_sql_text($_POST['C__MODULE__NAGIOS__TITLE']) . ',
            isys_nagios_service_escalations__first_notification = ' . $this->convert_sql_text($_POST['C__MODULE__NAGIOS__FIRST_NOTIFICATION']) . ',
            isys_nagios_service_escalations__last_notification = ' . $this->convert_sql_text($_POST['C__MODULE__NAGIOS__LAST_NOTIFICATION']) . ',
            isys_nagios_service_escalations__notification_interval = ' . $this->convert_sql_text($_POST['C__MODULE__NAGIOS__NOTIFICATION_INTERVAL']) . ',
            isys_nagios_service_escalations__escalation_period = ' . $this->convert_sql_id($_POST['C__MODULE__NAGIOS__ESCALATION_PERIOD']) . ',
            isys_nagios_service_escalations__escalation_period_plus = ' . $this->convert_sql_id($_POST['C__MODULE__NAGIOS__ESCALATION_PERIOD_PLUS']) . ',
            isys_nagios_service_escalations__escalation_options = ' . $this->convert_sql_text($_POST['C__MODULE__NAGIOS__ESCALATION_OPTIONS__selected_values']) . '
            WHERE isys_nagios_service_escalations__id = ' . $this->convert_sql_id($p_id) . ';';

        return ($this->update($l_query) && $this->apply_update());
    } // function

    /**
     * Method for saving contact-relations.
     *
     * @param   integer $p_contactID
     * @param   mixed   $p_values May be a JSON string, a comma-separated list or an array.
     *
     * @return  boolean  true
     */
    public function saveContacts($p_contactID = null, $p_values)
    {
        $l_daoContactRef = new isys_contact_dao_reference($this->get_database_component());

        if (empty($p_values))
        {
            $l_daoContactRef->save($p_contactID);
        }
        else
        {
            $l_daoContactRef->ref_contact($p_values, $p_contactID);
        } // if

        return true;
    } // function

    /**
     * Fetches the host escalation contact ID.
     *
     * @param   integer $p_id
     *
     * @return  integer
     */
    public function getHostEscalationContactID($p_id)
    {
        $l_sql = 'SELECT isys_nagios_host_escalations__isys_contact__id AS id
			FROM isys_nagios_host_escalations
			WHERE isys_nagios_host_escalations__id = ' . $this->convert_sql_id($p_id) . ';';

        $l_row = $this->retrieve($l_sql)
            ->get_row();

        if (empty($l_row['id']))
        {
            $l_q = "INSERT INTO isys_contact (isys_contact__title, isys_contact__description, isys_contact__property, isys_contact__status) " . "VALUES (NULL, NULL, 0, " . C__RECORD_STATUS__NORMAL . ")";
            if ($this->update($l_q))
            {
                $l_id     = $this->get_last_insert_id();
                $l_update = 'UPDATE isys_nagios_host_escalations SET isys_nagios_host_escalations__isys_contact__id = ' . $this->convert_sql_id(
                        $l_id
                    ) . ' ' . 'WHERE isys_nagios_host_escalations__id = ' . $this->convert_sql_id($p_id);
                if ($this->update($l_update) && $this->apply_update())
                {
                    return $l_id;
                }
            }
        }

        return (int) $l_row['id'];
    } // function

    /**
     * Fetches the service escalation contact ID.
     *
     * @param   integer $p_id
     *
     * @return  integer
     */
    public function getServiceEscalationContactID($p_id)
    {
        $l_sql = 'SELECT isys_nagios_service_escalations__isys_contact__id AS id
			FROM isys_nagios_service_escalations
			WHERE isys_nagios_service_escalations__id = ' . $this->convert_sql_id($p_id) . ';';

        $l_row = $this->retrieve($l_sql)
            ->get_row();

        if (empty($l_row['id']))
        {
            $l_q = "INSERT INTO isys_contact (isys_contact__title, isys_contact__description, isys_contact__property, isys_contact__status) " . "VALUES (NULL, NULL, 0, " . C__RECORD_STATUS__NORMAL . ")";
            if ($this->update($l_q))
            {
                $l_id     = $this->get_last_insert_id();
                $l_update = 'UPDATE isys_nagios_service_escalations SET isys_nagios_service_escalations__isys_contact__id = ' . $this->convert_sql_id(
                        $l_id
                    ) . ' ' . 'WHERE isys_nagios_service_escalations__id = ' . $this->convert_sql_id($p_id);
                if ($this->update($l_update) && $this->apply_update())
                {
                    return $l_id;
                }
            }
        }

        return (int) $l_row['id'];
    } // function

    public function deleteHostEscalation($p_id)
    {
        $this->begin_update();

        try
        {
            $l_query  = 'SELECT isys_nagios_host_escalations__isys_contact__id ' . 'FROM isys_nagios_host_escalations ' . 'WHERE isys_nagios_host_escalations__id = ' . $this->convert_sql_id(
                    $p_id
                ) . ';';
            $l_result = $this->retrieve($l_query);
            $l_row    = $l_result->get_row();

            if ($l_row['isys_nagios_host_escalations__isys_contact__id'] != null)
            {
                $l_update = 'DELETE FROM isys_contact ' . 'WHERE isys_contact__id = ' . $l_row['isys_nagios_host_escalations__isys_contact__id'];
                $this->update($l_update);
            }

            $l_update = 'DELETE FROM isys_nagios_host_escalations ' . 'WHERE isys_nagios_host_escalations__id = ' . $this->convert_sql_id($p_id) . ';';
            $this->update($l_update);
        }
        catch (Exception $e)
        {
            $this->cancel_update();
            throw new isys_exception_dao($e->getMessage());
        }

        return $this->apply_update();
    }

    public function deleteServiceEscalation($p_id)
    {
        $this->begin_update();

        try
        {
            $l_query  = 'SELECT isys_nagios_service_escalations__isys_contact__id ' . 'FROM isys_nagios_service_escalations ' . 'WHERE isys_nagios_service_escalations__id = ' . $this->convert_sql_id(
                    $p_id
                ) . ';';
            $l_result = $this->retrieve($l_query);
            $l_row    = $l_result->get_row();

            if ($l_row['isys_nagios_service_escalations__isys_contact__id'] != null)
            {
                $l_update = 'DELETE FROM isys_contact ' . 'WHERE isys_contact__id = ' . $l_row['isys_nagios_service_escalations__isys_contact__id'];
                $this->update($l_update);
            }

            $l_update = 'DELETE FROM isys_nagios_service_escalations ' . 'WHERE isys_nagios_service_escalations__id = ' . $this->convert_sql_id($p_id) . ';';
            $this->update($l_update);
        }
        catch (Exception $e)
        {
            $this->cancel_update();
            throw new isys_exception_dao($e->getMessage());
        }

        return $this->apply_update();
    }

    public function saveCommand($p_id)
    {
        $l_data = [];

        if (isset($_POST['C__MODULE__NAGIOS__COMMAND_NAME']))
        {
            $l_data['isys_nagios_commands__name'] = $this->convert_sql_text($_POST['C__MODULE__NAGIOS__COMMAND_NAME']);
        } // if

        if (isset($_POST['C__MODULE__NAGIOS__COMMAND_LINE']))
        {
            $l_data['isys_nagios_commands__line'] = $this->convert_sql_text($_POST['C__MODULE__NAGIOS__COMMAND_LINE']);
        } // if

        if (isset($_POST['C__MODULE__NAGIOS__COMMAND_DESCRIPTION']))
        {
            $l_data['isys_nagios_commands__description'] = $this->convert_sql_text($_POST['C__MODULE__NAGIOS__COMMAND_DESCRIPTION']);
        } // if

        $l_prepare_query = [];

        foreach ($l_data as $l_key => $l_value)
        {
            $l_prepare_query[] = $l_key . ' = ' . $l_value;
        } // foreach

        $l_query = 'UPDATE isys_nagios_commands
			SET ' . implode(', ', $l_prepare_query) . '
			WHERE isys_nagios_commands__id = ' . $this->convert_sql_id($p_id) . ';';

        return ($this->update($l_query) && $this->apply_update());
    }

    /**
     * Delete a command from isys_nagios_commands given by its id $p_id.
     * Returns true on success, false otherwise.
     *
     * @param   integer $p_id
     *
     * @return  boolean
     */
    public function deleteCommand($p_id)
    {
        return ($this->update('DELETE FROM isys_nagios_commands WHERE isys_nagios_commands__id = ' . $this->convert_sql_id($p_id) . ';') && $this->apply_update());
    }

    /**
     * Get status of host.
     *
     * @param   array $p_catData Array containing the data from isys_catg_nagios_list for the corresponding object
     *
     * @throws  Exception
     * @return  string
     */
    public function getHostStatus($p_catData)
    {
        global $g_dirs;

        $l_status = isys_tenantsettings::get('gui.empty_value', '-');

        if ($p_catData["isys_catg_monitoring_list__active"] == "1")
        {
            try
            {
                $l_daoNDO = new isys_component_dao_ndo($this->m_ndo_db, $this->m_ndo_db_prefix);

                $l_host_state = $l_daoNDO->getCurrentHostState(isys_monitoring_helper::render_export_hostname($p_catData['isys_obj__id']));

                if (count($l_host_state) > 0)
                {
                    $l_currentState = $l_host_state->get_row();
                }
                else
                {
                    throw new Exception("UNKNOWN HOST");
                } // if
            }
            catch (Exception $e)
            {
                return '<img src="' . $g_dirs["images"] . 'icons/silk/help.png" class="vam mr5" /><span class="vam blue">' . _L($e->getMessage()) . '</span>';
            } // try

            $l_states = isys_monitoring_helper::get_state_info();
            $l_state  = $l_states[$l_currentState['current_state']];

            $l_status = '<img src="' . $g_dirs['images'] . $l_state['icon'] . '" class="vam mr5" /><span class="vam ' . $l_state['color'] . '">' . $l_state['state'] . '</span>';
        } // if

        return $l_status;
    } // function

    public function getHostStatusTable($p_catData)
    {
        global $g_comp_template_language_manager;

        if ($p_catData["isys_catg_monitoring_list__active"] == "1")
        {

            try
            {
                $l_daoNDO = new isys_component_dao_ndo($this->m_ndo_db, $this->m_ndo_db_prefix);

                $l_host_state = $l_daoNDO->getCurrentHostState(isys_monitoring_helper::render_export_hostname($p_catData['isys_obj__id']));

                if (count($l_host_state) > 0)
                {
                    $l_currentState = $l_host_state->get_row();

                    if ($l_currentState["has_been_checked"] == "0")
                    {
                        throw new Exception("HOST HAS NOT BEEN CHECKED, YET");
                    } // if
                }
                else
                {
                    throw new Exception("UNKNOWN HOST");
                } // if
            }
            catch (Exception $e)
            {
                die($g_comp_template_language_manager->get($e->getMessage()));
            } // try

            $l_states    = isys_monitoring_helper::get_host_state_info();
            $l_strStatus = '<tr><td class="key">Host Status:</td><td class="value"><span class="ml15 pl5 pr5 ' . $l_states[$l_currentState['current_state']]['color'] . '">' . $l_states[$l_currentState['current_state']]['state'] . '</span></td>' . '</tr><tr><td class="key">Status Information:</td><td class="value"><span style="margin-left:15px;">' . $l_currentState["output"] . '</span></td></tr>' . '<tr><td class="key">Performance Data:</td><td class="value"><span style="margin-left:15px;">' . $l_currentState["perfdata"] . '</span></td></tr>' . '<tr><td class="key">Current Attempt:</td><td class="value"><span style="margin-left:15px;">' . $l_currentState["current_check_attempt"] . '/' . $l_currentState["max_check_attempts"] . '</span></td></tr>' . '<tr><td class="key">Last Check Time:</td><td class="value"><span style="margin-left:15px;">' . $l_currentState["last_check"] . '</span></td></tr>' . '<tr><td class="key">Check Type:</td><td class="value">';

            switch ($l_currentState['check_type'])
            {
                case "0":
                    $l_strStatus .= '<span class="ml15">ACTIVE</td>';
                    break;
                case "1":
                    $l_strStatus .= '<span class="ml15">PASSIVE</td>';
                    break;
            }
            $l_strStatus .= '</tr><tr><td class="key">Check Latency / Duration:</td><td class="value"><span class="ml15">' . $l_currentState["latency"] . ' / ' . $l_currentState["execution_time"] . '</span></td></tr>' . '<tr><td class="key">Next Scheduled Active Check:</td><td class="value"><span class="ml15">' . $l_currentState["next_check"] . '</span></td></tr>' . '<tr><td class="key">Last State Change:</td><td class="value">';

            switch ($l_currentState["last_state_change"])
            {
                case '':
                case null:
                case '1970-01-01 01:00:00':
                    $l_strStatus .= '<span style="ml15">N/A</span></td>';
                    break;
                default:
                    $l_strStatus .= '<span style="ml15">' . $l_currentState["last_state_change"] . '</span></td>';
            } // switch

            $l_strStatus .= '</tr><tr><td class="key">Last Notification:</td><td class="value">';

            switch ($l_currentState['last_notification'])
            {
                case '':
                case null:
                case '1970-01-01 01:00:00':
                    $l_strStatus .= '<span class="ml15">N/A</span></td>';
                    break;
                default:
                    $l_strStatus .= '<span class="ml15">' . $l_currentState['last_notification'] . '</span></td>';
                    break;
            } // switch

            $l_strStatus .= '</tr><tr><td class="key">Is This Host Flapping?</td><td class="value">';

            switch ($l_currentState['is_flapping'])
            {
                case "0":
                    $l_strStatus .= '<span class="ml15 pl5 pr5 green">NO</span></td>';
                    break;
                case "1":
                    $l_strStatus .= '<span class="ml15 pl5 pr5 red">YES</span></td>';
                    break;
            } // switch

            $l_strStatus .= '</tr><tr><td class="key">In Scheduled Downtime?</td><td class="value">';

            switch ($l_currentState['scheduled_downtime_depth'])
            {
                case "0":
                    $l_strStatus .= '<span class="ml15 pl5 pr5 green">NO</span></td>';
                    break;
                default:
                    $l_strStatus .= '<span class="ml15 pl5 pr5 red">YES</span></td>';
                    break;
            } // switch

            $l_strStatus .= "</tr>";
        } // if

        return $l_strStatus;
    } // function

    /**
     * Get status of supervised service connected to object with id = $p_objID.
     *
     * @param   array  $p_catData
     * @param   string $p_service_description Service_description of the service.
     *
     * @throws  Exception
     * @return  string
     */
    public function getServiceStatus($p_catData, $p_service_description)
    {
        global $g_dirs;

        $l_daoNagios = new isys_cmdb_dao_category_g_nagios($this->get_database_component());

        if ($l_daoNagios)
        {
            try
            {
                $l_daoNDO       = new isys_component_dao_ndo($this->m_ndo_db, $this->m_ndo_db_prefix);
                $l_currentState = $l_daoNDO->getCurrentServiceState($p_catData["isys_catg_nagios_list__name1"], $p_service_description);

                if (!$l_currentState)
                {
                    throw new Exception($p_service_description . " not found for this host in nagios config");
                } // if

                $l_states = isys_monitoring_helper::get_state_info();
                $l_state  = $l_states[$l_currentState['current_state']];

                return '<img src="' . $g_dirs['images'] . $l_state['icon'] . '" class="vam mr5"><span class="vam ' . $l_state['color'] . '" />' . $l_state['state'] . '</span>';
            }
            catch (Exception $e)
            {
                return '<img src="' . $g_dirs['images'] . 'icons/silk/help.png" class="vam mr5" /><span class="vam">' . _L($e->getMessage()) . '</span>';
            } // try
        } // if

        return '';
    } // function

    public function getServiceStatusTable($p_catData, $p_service_description)
    {
        global $g_comp_template_language_manager, $g_dirs;

        $l_daoNagios = new isys_cmdb_dao_category_g_nagios($this->get_database_component());

        if ($l_daoNagios)
        {

            try
            {
                $l_daoNDO       = new isys_component_dao_ndo($this->m_ndo_db, $this->m_ndo_db_prefix);
                $l_currentState = $l_daoNDO->getCurrentServiceState($p_catData["isys_catg_nagios_list__name1"], $p_service_description);

                if (!$l_currentState)
                {
                    throw new Exception($p_service_description . " not found for this host in nagios config");
                }

                $l_strStatus = "<tr><td class=\"key\">Host Status:</td><td class=\"value\">";

                $l_states = isys_monitoring_helper::get_state_info();
                $l_state  = $l_states[$l_currentState['current_state']];

                $l_strStatus .= '<span class="ml15 pl5 pr5 ' . $l_state['color'] . '">' . $l_state['state'] . '</span></td></tr>' . '<tr><td class="key">Status Information:</td><td class="value"><span class="ml15">' . $l_currentState["output"] . '</span></td></tr>' . '<tr><td class="key">Performance Data:</td><td class="value"><span class="ml15">' . $l_currentState["perfdata"] . '</span></td></tr>' . '<tr><td class="key">Current Attempt:</td><td class="value"><span class="ml15">' . $l_currentState["current_check_attempt"] . '/' . $l_currentState["max_check_attempts"] . '</span></td></tr>' . '<tr><td class="key">Last Check Time:</td><td class="value"><span class="ml15">' . $l_currentState["last_check"] . '</span></td></tr>' . '<tr><td class="key">Check Type:</td><td class="value">';

                switch ($l_currentState['check_type'])
                {
                    case "0":
                        $l_strStatus .= '<span class="ml15">ACTIVE</td>';
                        break;
                    case "1":
                        $l_strStatus .= '<span class="ml15">PASSIVE</td>';
                        break;
                }
                $l_strStatus .= '</tr><tr><td class="key">Check Latency / Duration:</td><td class="value"><span class="ml15">' . $l_currentState['latency'] . ' / ' . $l_currentState['execution_time'] . '</span></td></tr>' . '<tr><td class="key">Next Scheduled Active Check:</td><td class="value"><span class="ml15">' . $l_currentState['next_check'] . '</span></td></tr>' . '<tr><td class="key">Last State Change:</td><td class="value"><span class="ml15">' . $l_currentState['last_state_change'] . '</span></td></tr>' . '<tr><td class="key">Last Notification:</td><td class="value">';

                switch ($l_currentState['last_notification'])
                {
                    case '':
                    case null:
                    case '1970-01-01 01:00:00':
                        $l_strStatus .= "<span style=\"margin-left:15px;\"> N/A</span></td>\n";
                        break;
                    default:
                        $l_strStatus .= "<span style=\"margin-left:15px;>" . $l_currentState['last_notification'] . "</span></td>\n";
                        break;
                }
                $l_strStatus .= "</tr>";

                $l_strStatus .= "<tr>\n" . "	<td class=\"key\">Is This Service Flapping?</td>\n" . "	<td class=\"value\">";

                switch ($l_currentState['is_flapping'])
                {
                    case "0":
                        $l_strStatus .= '<span class="ml15 pl5 pr5 green">NO</span></td>';
                        break;
                    case "1":
                        $l_strStatus .= '<span class="ml15 pl5 pr5 red">YES</span></td>';
                        break;
                }
                $l_strStatus .= "</tr>";

                $l_strStatus .= "<tr>\n" . "	<td class=\"key\">In Scheduled Downtime?</td>\n" . "	<td class=\"value\">";

                switch ($l_currentState['scheduled_downtime_depth'])
                {
                    case "0":
                        $l_strStatus .= '<span class="ml15 pl5 pr5 green">NO</span></td>';
                        break;
                    default:
                        $l_strStatus .= '<span class="ml15 pl5 pr5 red">YES</span></td>';
                        break;
                }
                $l_strStatus .= "</tr>";
            }
            catch (Exception $e)
            {
                $l_strStatus = "<img class=\"infoIcon\" src=\"" . $g_dirs["images"] . "icons/infoicon/help.png\" \" alt=\"\" height=\"15px\" width=\"15px\" style=\"margin-right:5px;\" />" . $g_comp_template_language_manager->get(
                        $e->getMessage()
                    );
            }
        }

        return ($l_strStatus);
    } // function

    /**
     * Method for retrieving an associative array of nagios-hosts.
     *
     * @return  array
     */
    public function getNagiosHostsAssoc()
    {
        $l_result = isys_monitoring_dao_hosts::instance($this->m_db)
            ->get_export_data();

        if (count($l_result) > 0)
        {
            while ($l_row = $l_result->get_row())
            {
                $l_return[$l_row['isys_monitoring_export_config__id']] = $l_row['isys_monitoring_export_config__title'];
            } // while
        } // if

        return $l_return;
    }

    /**
     * Retrieve the nagios timeperiods.
     *
     * @return  isys_component_dao_result
     */
    public function getTimeperiods()
    {
        $l_sql = 'SELECT isys_nagios_timeperiods__id AS "id",
			isys_nagios_timeperiods__name AS "name",
			isys_nagios_timeperiods__alias AS "alias",
			isys_nagios_timeperiods__definition AS "definition",
			isys_nagios_timeperiods__default_check AS "def_check",
			isys_nagios_timeperiods__default_notification AS "def_not"
			FROM isys_nagios_timeperiods;';

        return $this->retrieve($l_sql);
    } // function

    /**
     * Method for retrieving the timeperiods in an associative array.
     *
     * @return  array
     */
    public function getTimeperiodsAssoc()
    {
        $l_return = [];
        $l_res    = $this->retrieve('SELECT isys_nagios_timeperiods__id AS id, isys_nagios_timeperiods__name AS name FROM isys_nagios_timeperiods;');

        while ($l_row = $l_res->get_row())
        {
            $l_return[$l_row["id"]] = $l_row["name"];
        } // while

        return $l_return;
    } // function

    /**
     * @param   integer $p_id
     *
     * @return  array
     */
    public function getTimeperiod($p_id)
    {
        $l_sql = 'SELECT
			isys_nagios_timeperiods__alias AS alias,
			isys_nagios_timeperiods__name AS name,
			isys_nagios_timeperiods__definition AS definition,
			isys_nagios_timeperiods__default_check AS def_check,
			isys_nagios_timeperiods__default_notification AS def_not,
			isys_nagios_timeperiods__exclude AS exclude
			FROM isys_nagios_timeperiods
			WHERE isys_nagios_timeperiods__id = ' . $this->convert_sql_id($p_id) . ';';

        return $this->retrieve($l_sql)
            ->get_row();
    } // function

    public function getDefaultCheckPeriod()
    {
        $l_query  = "SELECT isys_nagios_timeperiods__id AS id FROM isys_nagios_timeperiods WHERE isys_nagios_timeperiods__default_check = 1";
        $l_result = $this->retrieve($l_query);
        $l_row    = $l_result->get_row();

        return $l_row["id"];
    } // function

    public function getDefaultNotificationPeriod()
    {
        $l_query  = "SELECT isys_nagios_timeperiods__id AS id FROM isys_nagios_timeperiods WHERE isys_nagios_timeperiods__default_notification = 1";
        $l_result = $this->retrieve($l_query);
        $l_row    = $l_result->get_row();

        return $l_row["id"];
    }

    /**
     * Method for retrieving all nagios commands.
     *
     * @return  isys_component_dao_result
     */
    public function getCommands()
    {
        return $this->retrieve(
            'SELECT isys_nagios_commands__id AS "id", isys_nagios_commands__name AS "name", isys_nagios_commands__line AS "line", isys_nagios_commands__description AS "description" FROM isys_nagios_commands;'
        );
    }

    /**
     * @return  array
     */
    public function getCommandsAssoc()
    {
        $l_return = [];
        $l_result = $this->retrieve('SELECT isys_nagios_commands__id AS "id", isys_nagios_commands__name AS "name" FROM isys_nagios_commands');

        if (count($l_result) > 0)
        {
            while ($l_row = $l_result->get_row())
            {
                $l_return[$l_row["id"]] = $l_row["name"];
            } // while
        } // if

        return $l_return;
    } // function

    public function getCommand($p_id)
    {
        return $this->retrieve(
            'SELECT isys_nagios_commands__name AS name, isys_nagios_commands__line AS line, isys_nagios_commands__description AS description FROM isys_nagios_commands WHERE isys_nagios_commands__id = ' . $this->convert_sql_id(
                $p_id
            ) . ';'
        )
            ->get_row();
    } // function

    /**
     * Check, if a command, given by its name, exists.
     *
     * @param   string $p_name
     *
     * @return  boolean
     */
    public function commandExists($p_name)
    {
        $l_query = 'SELECT isys_nagios_commands__id
			FROM isys_nagios_commands
			WHERE isys_nagios_commands__name = ' . $this->convert_sql_text($p_name) . ';';

        return (count($this->retrieve($l_query)) > 0);
    } // function

    /**
     * Retrieves all host escalations in a raw format.
     *
     * @return  array
     */
    public function getHostEscalations()
    {
        $l_return = [];
        $l_result = $this->retrieve('SELECT isys_nagios_host_escalations__id AS id, isys_nagios_host_escalations__title AS title FROM isys_nagios_host_escalations;');

        if (count($l_result) > 0)
        {
            while ($l_row = $l_result->get_row())
            {
                $l_return[] = $l_row;
            } // while
        } // if

        return $l_return;
    } // function

    /**
     * Retrieves all host escalations in an associative array.
     *
     * @return  array
     */
    public function getHostEscalationsAssoc()
    {
        $l_return = [];
        $l_result = $this->retrieve('SELECT isys_nagios_host_escalations__id AS id, isys_nagios_host_escalations__title AS title FROM isys_nagios_host_escalations;');

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
     * Retrieves a single host escalation by ID.
     *
     * @param   integer $p_id
     *
     * @return  array
     */
    public function getHostEscalation($p_id)
    {
        return $this->retrieve('SELECT * FROM isys_nagios_host_escalations WHERE isys_nagios_host_escalations__id = ' . $this->convert_sql_id($p_id) . ';')
            ->get_row();
    } // function

    /**
     * Retrieves a single service escalation by ID.
     *
     * @param   integer $p_id
     *
     * @return  array
     */
    public function getServiceEscalation($p_id)
    {
        return $this->retrieve('SELECT * FROM isys_nagios_service_escalations WHERE isys_nagios_service_escalations__id = ' . $p_id . ';')
            ->get_row();
    } // function

    /**
     * Method for retrieving all service escalations and return as result-set.
     *
     * @return  isys_component_dao_result
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function getServiceEscalationsResult()
    {
        return $this->retrieve('SELECT * FROM isys_nagios_service_escalations;');
    } // function

    /**
     * Retrieves all service escalations in a raw format.
     *
     * @return  array
     */
    public function getServiceEscalations()
    {
        $l_return = [];
        $l_result = $this->retrieve('SELECT isys_nagios_service_escalations__id AS id, isys_nagios_service_escalations__title AS title FROM isys_nagios_service_escalations;');

        if (count($l_result) > 0)
        {
            while ($l_row = $l_result->get_row())
            {
                $l_return[] = $l_row;
            } // while
        } // if

        return $l_return;
    } // function

    /**
     * Retrieves all service escalations in an associative array.
     *
     * @return  array
     */
    public function getServiceEscalationsAssoc()
    {
        $l_return = [];
        $l_result = $this->retrieve('SELECT isys_nagios_service_escalations__id AS id, isys_nagios_service_escalations__title AS title FROM isys_nagios_service_escalations;');

        if (count($l_result) > 0)
        {
            while ($l_row = $l_result->get_row())
            {
                $l_return[$l_row["id"]] = $l_row["title"];
            } // while
        } // if

        return $l_return;
    }

    /**
     * Returns all nagios contacts, which have status NORMAL.
     *
     * @return  isys_component_dao_result
     */
    public function getContacts()
    {
        $l_sql = 'SELECT *, isys_catg_mail_addresses_list__title AS isys_cats_person_list__mail_address
			FROM isys_cats_person_nagios_list
			INNER JOIN isys_cats_person_list ON isys_cats_person_list__isys_obj__id = isys_cats_person_nagios_list__isys_obj__id
			INNER JOIN isys_obj ON isys_obj__id = isys_cats_person_list__isys_obj__id
			LEFT JOIN isys_catg_mail_addresses_list ON (isys_obj__id = isys_catg_mail_addresses_list__isys_obj__id AND isys_catg_mail_addresses_list__primary = 1)
			WHERE isys_obj__status = ' . $this->convert_sql_int(C__RECORD_STATUS__NORMAL) . ';';

        return $this->retrieve($l_sql . ';');
    } // function

    /**
     * Returns a single nagios contact.
     *
     * @param   integer $p_id
     *
     * @return  array
     */
    public function getContact($p_id)
    {
        $l_sql = 'SELECT *
			FROM isys_cats_person_nagios_list
			WHERE isys_cats_person_nagios_list__isys_obj__id = ' . $this->convert_sql_id($p_id) . ';';

        return $this->retrieve($l_sql)
            ->get_row();
    } // function

    /**
     * Returns all nagios contact groups, which have status NORMAL.
     *
     * @return  isys_component_dao_result
     */
    public function getContactGroups()
    {
        $l_sql = 'SELECT *
			FROM isys_cats_person_group_nagios_list
			INNER JOIN isys_obj ON isys_obj__id = isys_cats_person_group_nagios_list__isys_obj__id
			WHERE isys_obj__status = ' . $this->convert_sql_int(C__RECORD_STATUS__NORMAL) . ';';

        return $this->retrieve($l_sql);
    } // function

    /**
     * Retrieves all host-groups.
     *
     * @return  isys_component_dao_result
     */
    public function getHostGroups()
    {
        $l_sql = 'SELECT isys_obj__id, isys_obj__title, isys_catg_nagios_group_list.*
			FROM isys_catg_nagios_group_list
			INNER JOIN isys_obj ON isys_obj__id = isys_catg_nagios_group_list__isys_obj__id
			WHERE isys_catg_nagios_group_list__type = ' . $this->convert_sql_int(C__CATG_NAGIOS_GROUP__TYPE_HOST) . '
			AND isys_obj__status = ' . $this->convert_sql_int(C__RECORD_STATUS__NORMAL) . ';';

        return $this->retrieve($l_sql);
    } // function

    /**
     * Retrieves all service groups.
     *
     * @return  isys_component_dao_result
     */
    public function getServiceGroups()
    {
        $l_sql = 'SELECT isys_obj__id, isys_obj__title, isys_catg_nagios_group_list.*
			FROM isys_catg_nagios_group_list
			INNER JOIN isys_obj ON isys_obj__id = isys_catg_nagios_group_list__isys_obj__id
			WHERE isys_catg_nagios_group_list__type = ' . $this->convert_sql_int(C__CATG_NAGIOS_GROUP__TYPE_SERVICE) . '
			AND isys_obj__status = ' . $this->convert_sql_int(C__RECORD_STATUS__NORMAL) . ';';

        return $this->retrieve($l_sql);
    } // function

    /**
     * Method for retrieving all host dependencies.
     *
     * @return  isys_component_dao_result
     */
    public function getHostDepedencies()
    {
        ;
    } // function

    /**
     *
     * @param   integer $p_objID
     *
     * @return  isys_component_dao_result
     */
    public function getNagiosContacts($p_objID)
    {
        $l_sql = 'SELECT * FROM isys_obj
			INNER JOIN isys_obj_type ON isys_obj_type__id = isys_obj__isys_obj_type__id
			INNER JOIN isys_connection ON isys_connection__isys_obj__id = isys_obj__id
			INNER JOIN isys_catg_contact_list ON isys_catg_contact_list__isys_connection__id = isys_connection__id
			INNER JOIN isys_contact_tag ON isys_contact_tag__id = isys_catg_contact_list__isys_contact_tag__id
			WHERE isys_catg_contact_list__isys_obj__id = ' . $this->convert_sql_id($p_objID) . '
			AND (isys_contact_tag__const = "C__CONTACT_TYPE__NAGIOS" OR isys_contact_tag__const = "C__CONTACT_TYPE__ADMIN")
			AND isys_obj__status = ' . $this->convert_sql_int(C__RECORD_STATUS__NORMAL) . ';';

        return $this->retrieve($l_sql);
    } // function

    /**
     * Returns the parents of the given host.
     *
     * @param   integer $p_obj_id
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function getParents($p_obj_id)
    {
        $l_return = [];

        /**
         * @var  isys_component_dao_result       $l_relation_res
         * @var  isys_cmdb_dao_category_g_nagios $l_nagios_dao
         * @var  isys_cmdb_dao_category_g_ip     $l_ip_dao
         */
        $l_relation_res = isys_cmdb_dao_category_g_relation::instance($this->m_db)
            ->get_data(null, $p_obj_id);
        $l_nagios_dao   = isys_cmdb_dao_category_g_nagios::instance($this->m_db);

        if (count($l_relation_res) > 0)
        {
            while (($l_row = $l_relation_res->get_row()))
            {
                if ($l_row['isys_catg_relation_list__isys_obj__id__slave'] == $p_obj_id)
                {
                    $l_nagios = $l_nagios_dao->get_data(null, $l_row['isys_catg_relation_list__isys_obj__id__master'])
                        ->get_row();

                    if ($l_nagios['isys_catg_nagios_list__is_exportable'] == 1)
                    {
                        $l_return[$l_row['isys_catg_relation_list__isys_obj__id__master']] = [
                            'id'                  => $l_row['isys_catg_relation_list__isys_obj__id__master'],
                            'type'                => _L($l_nagios_dao->get_obj_type_name_by_obj_id($l_row['isys_catg_relation_list__isys_obj__id__master'])),
                            'title'               => $l_nagios_dao->get_obj_name_by_id_as_string($l_row['isys_catg_relation_list__isys_obj__id__master']),
                            'host_name'           => $l_nagios['isys_catg_nagios_list__host_name'],
                            'host_name_selection' => $l_nagios['isys_catg_nagios_list__host_name_selection'],
                            'rendered_host_name'  => isys_nagios_helper::render_export_hostname($l_row['isys_catg_relation_list__isys_obj__id__master'])
                        ];
                    } // if
                } // if
            } // while
        } // if

        return $l_return;
    } // function

    /**
     * Returns the additional parents of the given host.
     *
     * @param   integer $p_obj_id
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function get_additional_parents($p_obj_id)
    {
        $l_return = [];

        /**
         * @var  isys_cmdb_dao_category_g_nagios $l_nagios_dao
         * @var  isys_cmdb_dao_category_g_ip     $l_ip_dao
         */
        $l_nagios_dao = isys_cmdb_dao_category_g_nagios::instance($this->m_db);

        $l_nagios_row = $l_nagios_dao->get_data(null, $p_obj_id)
            ->get_row();

        if (isys_format_json::is_json($l_nagios_row['isys_catg_nagios_list__parents']))
        {
            $l_objects = isys_format_json::decode($l_nagios_row['isys_catg_nagios_list__parents'], true);
        } // if

        if (is_array($l_objects) && count($l_objects) > 0)
        {
            foreach ($l_objects as $l_obj_id)
            {
                $l_row = $l_nagios_dao->get_data(null, $l_obj_id)
                    ->get_row();

                if ($l_row['isys_catg_nagios_list__is_exportable'] == 1)
                {
                    $l_return[$l_obj_id] = [
                        'id'                  => $l_obj_id,
                        'type'                => _L($l_nagios_dao->get_obj_type_name_by_obj_id($l_row['isys_catg_relation_list__isys_obj__id__master'])),
                        'title'               => $l_nagios_dao->get_obj_name_by_id_as_string($l_row['isys_catg_relation_list__isys_obj__id__master']),
                        'host_name'           => $l_row['isys_catg_nagios_list__host_name'],
                        'host_name_selection' => $l_row['isys_catg_nagios_list__host_name_selection'],
                        'rendered_host_name'  => isys_nagios_helper::render_export_hostname($l_row['isys_obj__id'])
                    ];
                } // if
            } // foreach
        } // if

        return $l_return;
    } // function

    /**
     * Return an associative array with all "host notification" options.
     *
     * @return  array
     */
    public function getHostNotificationOptionsAssoc()
    {
        return [
            "d" => "notify on DOWN",
            "u" => "notify on UNREACHABLE",
            "r" => "notify on RECOVERIES",
            "f" => "notify on start/stop FLAPPING",
            "s" => "notify on start/stop SCHEDULED downtime",
            "n" => "NONE"
        ];
    } // function

    /**
     * Return an associative array with all "service notification" options.
     *
     * @return  array
     */
    public function getServiceNotificationOptionsAssoc()
    {
        return [
            "w" => "notify on WARNING",
            "u" => "notify on UNKNOWN",
            "c" => "notify on CRITICAL",
            "r" => "notify on RECOVERIES",
            "f" => "notify on start/stop FLAPPING",
            "s" => "notify on start/stop SCHEDULED downtime",
            "n" => "NONE"
        ];
    } // function

    /**
     * Returns an associative array with all "host failure" criterias.
     *
     * @return  array
     */
    public function getHostFailureCriteriaAssoc()
    {
        return [
            "o" => "UP",
            "d" => "DOWN",
            "u" => "UNREACHABLE",
            "p" => "PENDING",
            "n" => "NONE"
        ];
    } // function

    /**
     * Returns an associative array with all "service failure" criterias.
     *
     * @return  array
     */
    public function getServiceFailureCriteriaAssoc()
    {
        return [
            "o" => "OK",
            "w" => "WARNING",
            "u" => "UNKNOWN",
            "c" => "CRITICAL",
            "p" => "PENDING",
            "n" => "NONE"
        ];
    } // function

    /**
     * Returns an associative array with all "host escalation" options.
     *
     * @return  array
     */
    public function getHostEscalationOptions()
    {
        return [
            "d" => "DOWN",
            "u" => "UNREACHABLE",
            "r" => "RECOVERY"
        ];
    } // function

    /**
     * Returns an associative array with all "service escalation" options.
     *
     * @return  array
     */
    public function getServiceEscalationOptions()
    {
        return [
            "w" => "WARNING",
            "u" => "UNKNOWN",
            "c" => "CRITICAL",
            "r" => "RECOVERY"
        ];
    } // function

    /**
     * Returns an associative array with all "service flap detection" options.
     *
     * @return  array
     */
    public function getServiceFlapDetectionOptionsAssoc()
    {
        return [
            "o" => "OK",
            "w" => "WARNING",
            "c" => "CRITICAL",
            "u" => "UNKNOWN"
        ];
    } // function

    /**
     * Returns an associative array with all "host flap detection" options.
     *
     * @return  array
     */
    public function getHostFlapDetectionOptionsAssoc()
    {
        return [
            "o" => "UP",
            "d" => "DOWN",
            "u" => "UNREACHABLE"
        ];
    } // function

    /**
     * Retrieve the parent object ID by a given object ID.
     *
     * @param   integer $p_id
     *
     * @return  integer
     */
    public function getParentObjectID($p_id)
    {
        $l_sql = 'SELECT isys_container__isys_obj__id__parent AS id
			FROM isys_container
			WHERE isys_container__isys_obj__id = ' . $this->convert_sql_id($p_id) . ';';

        $l_row = $this->retrieve($l_sql)
            ->get_row();

        return $l_row["id"];
    } // function

    /**
     * Retrieve the child object ID by a given object ID.
     *
     * @param   integer $p_id
     *
     * @return  integer
     */
    public function getChildObjectID($p_id)
    {
        $l_sql = 'SELECT isys_container__isys_obj__id__child AS id
			FROM isys_container
			WHERE isys_container__isys_obj__id = ' . $this->convert_sql_id($p_id) . ';';

        $l_row = $this->retrieve($l_sql)
            ->get_row();

        return $l_row["id"];
    } // function

    /**
     * Method for updating a configuration value.
     *
     * @param   string $p_key
     * @param   string $p_val
     *
     * @return  boolean
     */
    public function updateMainConfigValue($p_key, $p_val)
    {
        $l_sql = 'UPDATE isys_nagios_config
			SET isys_nagios_config__value = ' . $this->convert_sql_text($p_val) . '
			WHERE isys_nagios_config__key = ' . $this->convert_sql_text($p_key) . ';';

        return ($this->update($l_sql) && $this->apply_update());
    } // function

    /**
     * Method which returns the configuration keys.
     *
     * @return  array
     */
    private function getConfigKeys()
    {
        return [
            'PERSON_NAME_OPTION',
            'log_file',
            'cfg_file',
            'cfg_dir',
            'object_cache_file',
            'precached_object_file',
            'resource_file',
            'temp_file',
            'temp_path',
            'status_file',
            'status_update_interval',
            'nagios_user',
            'nagios_group',
            'enable_notifications',
            'execute_service_checks',
            'accept_passive_service_checks',
            'execute_host_checks',
            'accept_passive_host_checks',
            'enable_event_handlers',
            'log_rotation_method',
            'log_archive_path',
            'check_external_commands',
            'command_check_interval',
            'command_file',
            'external_command_buffer_slots',
            'lock_file',
            'retain_state_information',
            'state_retention_file',
            'retention_update_interval',
            'use_retained_program_state',
            'use_retained_scheduling_info',
            'retained_host_attribute_mask',
            'retained_service_attribute_mask',
            'retained_process_host_attribute_mask',
            'retained_process_service_attribute_mask',
            'retained_contact_host_attribute_mask',
            'retained_contact_service_attribute_mask',
            'use_syslog',
            'log_notifications',
            'log_service_retries',
            'log_host_retries',
            'log_event_handlers',
            'log_initial_states',
            'log_external_commands',
            'log_passive_checks',
            'global_host_event_handler',
            'global_service_event_handler',
            'sleep_time',
            'service_inter_check_delay_method',
            'max_service_check_spread',
            'service_interleave_factor',
            'max_concurrent_checks',
            'check_result_reaper_frequency',
            'max_check_result_reaper_time',
            'check_result_path',
            'max_check_result_file_age',
            'host_inter_check_delay_method',
            'max_host_check_spread',
            'interval_length',
            'auto_reschedule_checks',
            'auto_rescheduling_interval',
            'auto_rescheduling_window',
            'use_aggressive_host_checking',
            'translate_passive_host_checks',
            'passive_host_checks_are_soft',
            'enable_predictive_host_dependency_checks',
            'enable_predictive_service_dependency_checks',
            'cached_host_check_horizon',
            'cached_service_check_horizon',
            'use_large_installation_tweaks',
            'free_child_process_memory',
            'child_processes_fork_twice',
            'enable_environment_macros',
            'enable_flap_detection',
            'low_service_flap_threshold',
            'high_service_flap_threshold',
            'low_host_flap_threshold',
            'high_host_flap_threshold',
            'soft_state_dependencies',
            'service_check_timeout',
            'host_check_timeout',
            'event_handler_timeout',
            'notification_timeout',
            'ocsp_timeout',
            'ochp_timeout',
            'perfdata_timeout',
            'obsess_over_services',
            'ocsp_command',
            'obsess_over_hosts',
            'ochp_command',
            'process_performance_data',
            'host_perfdata_command',
            'service_perfdata_command',
            'host_perfdata_file',
            'service_perfdata_file',
            'host_perfdata_file_template',
            'service_perfdata_file_template',
            'host_perfdata_file_mode',
            'service_perfdata_file_mode',
            'host_perfdata_file_processing_interval',
            'service_perfdata_file_processing_interval',
            'host_perfdata_file_processing_command',
            'service_perfdata_file_processing_command',
            'check_for_orphaned_services',
            'check_for_orphaned_hosts',
            'check_service_freshness',
            'service_freshness_check_interval',
            'check_host_freshness',
            'host_freshness_check_interval',
            'additional_freshness_latency',
            'p1_file',
            'enable_embedded_perl',
            'use_embedded_perl_implicitly',
            'date_format',
            'use_timezone',
            'illegal_object_name_chars',
            'illegal_macro_output_chars',
            'use_regexp_matching',
            'use_true_regexp_matching',
            'admin_email',
            'admin_pager',
            'event_broker_options',
            'broker_module',
            'debug_file',
            'debug_level',
            'debug_verbosity',
            'max_debug_file_size',
            'check_for_updates',
            'bare_update_checks'
        ];
    } // function
} // class