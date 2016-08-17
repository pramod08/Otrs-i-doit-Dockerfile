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
 * Test DAO for users.
 *
 * @package     i-doit
 * @subpackage  Components
 * @author      Andre Woesten <awoesten@i-doit.de>
 * @author      Niclas Potthast <npotthast@i-doit.org>
 * @author      Dennis Stücken <dstuecken@i-doit.org>
 * @version     0.9
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_component_dao_user extends isys_component_dao
{
    /**
     * Default value for menu width
     */
    const C__CMDB__TREE_MENU_WIDTH = 235;
    /**
     * User cache, used mainly for import purpose.
     *
     * @var  array
     */
    protected static $m_user_cache = [];
    /**
     * @var array
     */
    protected $m_user_settings = null;
    /**
     * Variable for the settings-ID.
     *
     * @var  integer
     */
    private $m_setting_id = 0;

    /**
     * Creates an entry in isys_user_setting and binds it to the
     * internal or external person. If there is an entry existent,
     * it won't be created. Returns the ID of the setting record
     * and ISYS_NULL on failure.
     *
     * @param   integer $p_user_id
     *
     * @return  integer
     */
    public function get_user_setting_id($p_user_id = null)
    {
        try
        {
            if ($this->m_setting_id > 0)
            {
                return $this->m_setting_id;
            } // if

            if (is_null($p_user_id)) $l_piID = ($this->get_current_user_id() + 0);
            else
                $l_piID = $p_user_id;

            if ($l_piID > 0)
            {
                // Determine if user has a settings entry.
                $l_q = "SELECT isys_user_setting__id AS settingID FROM isys_user_setting " . "WHERE isys_user_setting__isys_obj__id = " . $this->convert_sql_id($l_piID);

                $l_res = $this->retrieve($l_q);

                if ($l_res && ($l_res->num_rows() == 0))
                {

                    // OK, there is no entry. Create one.
                    $l_q = "INSERT INTO isys_user_setting (isys_user_setting__id, isys_user_setting__isys_obj__id) VALUES (DEFAULT, " . $this->convert_sql_id($l_piID) . ")";

                    if ($this->update($l_q))
                    {
                        $l_setting_id = ($this->get_last_insert_id() + 0);

                        $l_q = "REPLACE INTO " . "isys_user_ui SET " . "isys_user_ui__isys_user_setting__id = " . $l_setting_id . ", " . "isys_user_ui__theme = 'default';";

                        if ($this->update($l_q))
                        {
                            $l_q = "REPLACE INTO isys_user_locale " . "SET isys_user_locale__isys_user_setting__id = " . $l_setting_id . ";";
                        } // if

                        if ($this->update($l_q))
                        {
                            if ($this->apply_update())
                            {
                                return $this->m_setting_id = $l_setting_id;
                            } // if
                        } // if
                    } // if
                }
                else
                {
                    // There is an entry - return ID.
                    $l_row = $l_res->get_row();

                    return $this->m_setting_id = $l_row["settingID"];
                } // if
            } // if
        }
        catch (isys_exception_database $e)
        {
            isys_application::instance()->container['notify']->error($e->getMessage());
        } // try

        return null;
    } // function

    /**
     * This method can be called to create the necessary "isys_user_*" table entries.
     *
     * @return  $this
     * @throws  Exception
     * @throws  isys_exception_dao
     * @throws  isys_exception_database
     * @throws  isys_exception_general
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function prepare_user_setting()
    {
        $l_user_id = $this->convert_sql_id($this->get_current_user_id());

        // Here we try to find out which setting is missing.
        $l_res                  = $this->retrieve('SELECT * FROM isys_user_setting WHERE isys_user_setting__isys_obj__id = ' . $l_user_id . ';');
        $l_isys_user_setting_id = $l_res->get_row_value('isys_user_setting__id');

        if (!count($l_res))
        {
            // There's no entry in "isys_user_setting".
            if (!($this->update('INSERT INTO isys_user_setting SET isys_user_setting__isys_obj__id = ' . $l_user_id . ';') && $this->apply_update()))
            {
                throw new isys_exception_general('Your user-settings could not be saved.');
            } // if

            $l_isys_user_setting_id = $this->get_last_insert_id();
        } // if

        $l_res = $this->retrieve('SELECT * FROM isys_user_ui WHERE isys_user_ui__isys_user_setting__id = ' . $this->convert_sql_id($l_isys_user_setting_id) . ';');

        if (!count($l_res))
        {
            // There's no entry in "isys_user_ui".
            $l_sql = 'INSERT INTO isys_user_ui
				SET isys_user_ui__isys_user_setting__id = ' . $this->convert_sql_id($l_isys_user_setting_id) . ',
				isys_user_ui__theme = "default",
				isys_user_ui__archive_color = "#FFFF00",
				isys_user_ui__del_color = "#FF0000",
				isys_user_ui__tree_visible = 3;';

            if (!($this->update($l_sql) && $this->apply_update()))
            {
                throw new isys_exception_general('Your user-settings could not be saved.');
            } // if
        } // if

        $l_res = $this->retrieve('SELECT * FROM isys_user_locale WHERE isys_user_locale__isys_user_setting__id = ' . $this->convert_sql_id($l_isys_user_setting_id) . ';');

        if (!count($l_res))
        {
            // There's no entry in "isys_user_locale".
            $l_sql = 'INSERT INTO isys_user_locale SET isys_user_locale__isys_user_setting__id = ' . $this->convert_sql_id($l_isys_user_setting_id) . ';';

            if (!($this->update($l_sql) && $this->apply_update()))
            {
                throw new isys_exception_general('Your user-settings could not be saved.');
            } // if
        } // if

        return $this;
    } // function

    /**
     * Retrieve the user's settings.
     *
     * @return  array
     * @author  Dennis Stücken <dstuecken@synetics.de>
     */
    public function get_user_settings()
    {
        return $this->m_user_settings;
    }

    /**
     * Get the user's theme as string.
     *
     * @return  string
     * @author  Dennis Stücken <dstuecken@synetics.de>
     */
    public function get_user_theme_as_string()
    {
        if ($this->m_user_settings)
        {
            return $this->m_user_settings['isys_user_ui__theme'];
        }
        else
        {
            return 'default';
        } // if
    } // function

    /**
     * Get the user's interface and port theme as string.
     *
     * @return  array
     */
    public function get_reference_coloration()
    {
        if ($this->m_user_settings)
        {
            return [
                C__RECORD_STATUS__ARCHIVED => $this->m_user_settings['isys_user_ui__archive_color'],
                C__RECORD_STATUS__DELETED  => $this->m_user_settings['isys_user_ui__del_color']
            ];
        }
        else
        {
            return [
                C__RECORD_STATUS__ARCHIVED => '#cc0000',
                C__RECORD_STATUS__DELETED  => '#ff0000'
            ];
        } // if
    } // function

    /**
     * Save user settings.
     *
     * @param   integer $p_settings_type
     * @param   array   $p_posts
     *
     * @author  Dennis Stücken <dstuecken@synetics.de>
     * @author  Leonard Fischer <lfischer@i-doit.org>
     * @throws  Exception
     *
     * @return  boolean
     */
    public function save_settings($p_settings_type, $p_posts)
    {
        $l_setting_id = (int) $this->get_user_setting_id();
        $l_update     = [];

        switch ($p_settings_type)
        {
            case C__SETTINGS_PAGE__THEME:
                /* @see  ID-1220
                 * if (isset($p_posts['theme']) && !empty($p_posts['theme']))
                 * {
                 * $l_update[] = 'isys_user_ui__theme = ' . $this->convert_sql_text($p_posts['theme']);
                 * } // if
                 */

                // Don't check for "empty" because the value might be "0".
                if (isset($p_posts['menu_visibility']))
                {
                    $l_update[] = 'isys_user_ui__tree_visible = ' . $this->convert_sql_int($p_posts['menu_visibility']);
                } // if

                if (count($l_update) > 0)
                {
                    $l_sql = 'UPDATE isys_user_ui SET ' . implode(', ', $l_update) . ' WHERE isys_user_ui__isys_user_setting__id = ' . $this->convert_sql_id(
                            $l_setting_id
                        ) . ';';
                } // if
                break;

            default:
            case C__SETTINGS_PAGE__SYSTEM:
                global $g_comp_session;
                switch ($p_posts['C__CATG__OVERVIEW__LANGUAGE'])
                {
                    case ISYS_LANGUAGE_ENGLISH:
                        $g_comp_session->set_language('en');
                        break;
                    case ISYS_LANGUAGE_GERMAN:
                        $g_comp_session->set_language('de');
                        break;
                    case ISYS_LANGUAGE_PORTUGUESE:
                        $g_comp_session->set_language('pt');
                        break;
                }

                if (isset($p_posts['C__CATG__OVERVIEW__LANGUAGE']) && !empty($p_posts['C__CATG__OVERVIEW__LANGUAGE']))
                {
                    $l_update[] = "isys_user_locale__language = " . $this->convert_sql_int($p_posts['C__CATG__OVERVIEW__LANGUAGE']);
                } // if

                if (isset($p_posts['C__CATG__OVERVIEW__DATE_FORMAT']) && !empty($p_posts['C__CATG__OVERVIEW__DATE_FORMAT']))
                {
                    $l_update[] = "isys_user_locale__language_time = " . $this->convert_sql_int($p_posts['C__CATG__OVERVIEW__DATE_FORMAT']);
                } // if

                if (isset($p_posts['C__CATG__OVERVIEW__NUMERIC_FORMAT']) && !empty($p_posts['C__CATG__OVERVIEW__NUMERIC_FORMAT']))
                {
                    $l_update[] = "isys_user_locale__language_numeric = " . $this->convert_sql_int($p_posts['C__CATG__OVERVIEW__NUMERIC_FORMAT']);
                } // if

                if (isset($p_posts['C__CATG__OVERVIEW__MONETARY_FORMAT']) && !empty($p_posts['C__CATG__OVERVIEW__MONETARY_FORMAT']))
                {
                    $l_update[] = "isys_user_locale__isys_currency__id = " . $this->convert_sql_id($p_posts['C__CATG__OVERVIEW__MONETARY_FORMAT']);
                } // if

                if (isset($p_posts['C__CATG__OVERVIEW__DEFAULT_TREEVIEW']) && !empty($p_posts['C__CATG__OVERVIEW__DEFAULT_TREEVIEW']))
                {
                    $l_update[] = "isys_user_locale__default_tree_view = " . $this->convert_sql_int($p_posts['C__CATG__OVERVIEW__DEFAULT_TREEVIEW']);
                } // if

                if (isset($p_posts['C__CATG__OVERVIEW__DEFAULT_TREETYPE']) && !empty($p_posts['C__CATG__OVERVIEW__DEFAULT_TREETYPE']))
                {
                    $l_update[] = "isys_user_locale__default_tree_type = " . $this->convert_sql_id($p_posts['C__CATG__OVERVIEW__DEFAULT_TREETYPE']);
                } // if

                if (count($l_update) > 0)
                {
                    $l_sql = "UPDATE isys_user_locale SET " . implode(', ', $l_update) . " WHERE isys_user_locale__isys_user_setting__id = " . $this->convert_sql_id(
                            $l_setting_id
                        ) . ";";
                } // if
                break;
        } // switch

        try
        {
            if (!empty($l_sql))
            {
                $this->update($l_sql);
                $this->apply_update();

                return true;
            } // if
        }
        catch (Exception $e)
        {
            throw $e;
        } // try
    } // function

    /**
     * Member of isys_component_dao_user.
     *
     * @return  integer
     * @author  Dennis Stücken <dstuecken@synetics.de>
     */
    public function get_current_user_id()
    {
        global $g_comp_session;

        return $g_comp_session->get_user_id();
    } // function

    /**
     * Get user by object id.
     *
     * @param   integer $p_by_userid
     * @param   string  $p_by_username
     *
     * @return  isys_component_dao_result
     */
    public function get_user($p_by_userid = null, $p_by_username = null)
    {
        $l_q = 'SELECT *, isys_catg_mail_addresses_list__title AS isys_cats_person_list__mail_address
			FROM isys_cats_person_list
			LEFT JOIN isys_catg_mail_addresses_list ON isys_catg_mail_addresses_list__isys_obj__id = isys_cats_person_list__isys_obj__id AND isys_catg_mail_addresses_list__primary = 1
			WHERE TRUE';

        if ($p_by_userid != null && $p_by_userid > 0)
        {
            $l_q .= ' AND isys_cats_person_list__isys_obj__id = ' . $this->convert_sql_id($p_by_userid);
        } // if

        if ($p_by_username != null && $p_by_username != '')
        {
            $l_q .= ' AND isys_cats_person_list__title = ' . $this->convert_sql_text($p_by_username);
        } // if

        return $this->retrieve($l_q . ';');
    } // function

    /**
     * Returns name and surname if it's a local user.
     *
     * @param   integer $p_nUserID
     *
     * @return  string
     * @author  Dennis Blümer <dbluemer@i-doit.org>
     * @author  Dennis Stücken <dstuecken@i-doit.org>
     */
    public function get_user_title($p_nUserID)
    {
        if (!is_null($p_nUserID))
        {
            $l_row = $this->get_user_cached($p_nUserID);

            if (strlen($l_row["isys_cats_person_list__first_name"]) > 0 && strlen($l_row["isys_cats_person_list__last_name"]) > 0)
            {
                return $l_row["isys_cats_person_list__first_name"] . " " . $l_row["isys_cats_person_list__last_name"];
            }
            else if (strlen($l_row["isys_cats_person_list__first_name"]) > 0)
            {
                return $l_row["isys_cats_person_list__first_name"];
            }
            else if (strlen($l_row["isys_cats_person_list__last_name"]) > 0)
            {
                return $l_row["isys_cats_person_list__last_name"];
            }
            else
            {
                return $l_row["isys_cats_person_list__title"];
            } // if
        }
        else
        {
            return isys_tenantsettings::get('gui.empty_value', '-');
        } // if
    } // function

    /**
     * Gets the user title with the specified logbook configuration
     *
     * @param         $p_nUserID
     * @param int     $p_type
     * @param string  $p_placeholder_string
     *
     * @return string
     * @author Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function get_user_title_by_logbook_config($p_nUserID, $p_type, $p_placeholder_string = null)
    {
        $l_return = isys_tenantsettings::get('gui.empty_value', '-');
        if (!is_null($p_nUserID))
        {
            if ($p_type == 0 || $p_placeholder_string == '')
            {
                return $this->get_user_title($p_nUserID);
            }
            else
            {
                $l_row           = $this->get_user_cached($p_nUserID);
                $l_replace_array = [
                    '#first_name#' => $l_row['isys_cats_person_list__first_name'],
                    '#last_name#'  => $l_row['isys_cats_person_list__last_name'],
                    '#user_name#'  => $l_row['isys_cats_person_list__title'],
                ];
                $l_return        = strtr($p_placeholder_string, $l_replace_array);
            }
            $l_return = rtrim($l_return);
        }

        return $l_return;
    } // function

    /**
     * Gets for the current user the menu tree width.
     *
     * @return  integer
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function get_user_menu_width()
    {
        return isys_usersettings::get('gui.leftcontent.width', self::C__CMDB__TREE_MENU_WIDTH);
    } // function

    /**
     * Sets for the current user the menu tree width.
     *
     * @param   integer $p_width
     *
     * @return  boolean
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function set_user_menu_width($p_width)
    {
        if ($p_width > 0)
        {
            isys_usersettings::set('gui.leftcontent.width', $p_width);

            return true;
        } // if

        return false;
    }

    /**
     * This method will be used for the import.
     *
     * @param   integer $p_by_userid
     * @param   string  $p_by_username
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    protected function get_user_cached($p_by_userid = null, $p_by_username = null)
    {
        // Check, if we have already cached this object.
        if ($p_by_userid && array_key_exists($p_by_userid, self::$m_user_cache))
        {
            return self::$m_user_cache[$p_by_userid];
        } // if

        $l_user                                                             = $this->get_user($p_by_userid, $p_by_username)
            ->get_row();
        self::$m_user_cache[$l_user['isys_cats_person_list__isys_obj__id']] = $l_user;

        return $l_user;
    } // function

    /**
     * @throws isys_exception_database
     */
    private function load_settings()
    {
        $l_sql = "SELECT * FROM isys_user_setting
            INNER JOIN isys_user_ui ON isys_user_ui__isys_user_setting__id = isys_user_setting__id
            LEFT JOIN isys_user_locale ON isys_user_locale__isys_user_setting__id = isys_user_setting__id
            WHERE isys_user_setting__isys_obj__id = " . $this->convert_sql_id($this->get_current_user_id()) . ";";

        $l_settings = $this->retrieve($l_sql);
        if ($l_settings->count())
        {
            $this->m_user_settings = $l_settings->get_row();
        }

        $l_settings->free_result();
    } // function

    /**
     * isys_component_dao_user constructor.
     *
     * @param isys_component_database $p_db
     */
    public function __construct(isys_component_database $p_db)
    {
        parent::__construct($p_db);

        $this->load_settings();
    }
} // class