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
 * gets the current content of the logbook for the infobox.
 *
 * @package     i-doit
 * @subpackage  Components_Template
 * @author      Niclas Potthast <npotthast@i-doit.de>
 * @author      Dennis Blümer <dbluemer@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_component_template_infobox extends isys_component_template
{
    private static $m_instance = null;
    protected $m_arParameters;
    protected $m_daoLogbook;
    protected $m_nAlertLevel;
    protected $m_nMessageID;
    protected $m_nMessageType;
    protected $m_nUserID;
    protected $m_strDate;
    protected $m_strMessage;

    /**
     * @return isys_component_template_infobox
     */
    public static function instance($p_options = [])
    {
        if (!self::$m_instance)
        {
            self::$m_instance = new self;
        }

        return self::$m_instance;
    }

    /**
     * Returns the alert level of the message as an integer value.
     *
     * @todo    Get value from DB!
     * @return  integer
     * @author  Niclas Potthast <npotthast@i-doit.de>
     */
    public function get_alert_level()
    {
        return $this->m_nAlertLevel;
    } // function

    /**
     * Sets the alert level of the message with an integer value.
     *
     * @param   integer $p_nLevel
     *
     * @author  Niclas Potthast <npotthast@i-doit.de>
     */
    public function set_alert_level($p_nLevel)
    {
        $this->m_nAlertLevel = $p_nLevel;
    } // function

    /**
     * Returns the message.
     *
     * @todo    Get value from DB!
     * @return  string
     * @author  Niclas Potthast <npotthast@i-doit.de>
     */
    public function get_message()
    {
        global $g_comp_template_language_manager;

        $l_m_strMessage = "";
        $l_arParams     = null;

        if (is_array($this->m_arParameters))
        {
            $l_arParams = $this->m_arParameters;
        } // if

        if (strlen($this->m_strMessage))
        {
            $l_m_strMessage = $this->m_strMessage;
        } // if

        return $g_comp_template_language_manager->get($l_m_strMessage, $l_arParams);
    } // function

    /**
     * Sets the message.
     *
     * @param   string  $p_message
     * @param   integer $p_messageID
     * @param   null    $p_m_nMessageType
     * @param   array   $p_arParameters
     * @param   integer $p_m_nAlertLevel
     *
     * @return $this
     *
     * @author  Niclas Potthast <npotthast@i-doit.de>
     */
    public function set_message($p_message, $p_messageID = null, $p_m_nMessageType = null, $p_arParameters = null, $p_m_nAlertLevel = null)
    {
        $this->m_strMessage = $p_message;

        // Our own messages get id 0 and are internal.
        if (is_numeric($p_messageID))
        {
            $this->m_nMessageID = $p_messageID;
        }
        else
        {
            $this->m_nMessageID = 0;
        } // if

        if (is_array($p_arParameters))
        {
            $this->m_arParameters = $p_arParameters;
        } // if

        if (is_numeric($p_m_nAlertLevel))
        {
            $this->m_nAlertLevel = $p_m_nAlertLevel;
        } // if

        return $this;
    } // function

    /**
     * Returns the message type, can be 'intern', 'extern' or 'user'.
     *
     * @todo    Get value from DB!
     * @return  string
     * @author  Niclas Potthast <npotthast@i-doit.de>
     */
    public function get_message_type()
    {
        $this->m_nMessageType;
    }

    /**
     * @param const $p_m_nMessageType
     *
     * @author Niclas Potthast <npotthast@i-doit.de>
     * @desc   returns the message type, can be 'intern', 'extern' or 'user'
     */
    public function set_message_type($p_m_nMessageType)
    {
        $this->m_nMessageType = $p_m_nMessageType;
    }

    /**
     * @todo   get value from DB
     * @return integer
     * @author Niclas Potthast <npotthast@i-doit.de>
     * @desc   returns the message id, returns NULL if something goes wrong
     */
    public function get_message_id()
    {
        return $this->m_nMessageID;
    }

    /**
     * @todo   get date from DB
     * @return integer
     * @author Niclas Potthast <npotthast@i-doit.de>
     * @desc   returns the date
     */
    public function get_date()
    {
        return $this->m_strDate;
    }

    /**
     * @global $g_comp_template_language_manager
     * @global $g_dirs
     * @global $g_comp_database
     */
    public function show_html()
    {
        global $g_comp_template_language_manager;
        global $g_dirs;
        global $g_comp_database;
        global $g_loc;

        $l_mod_event_manager = isys_event_manager::getInstance();

        $l_out              = "";
        $l_strAlertLevel    = "blue";
        $l_url              = "";
        $l_imagesrc         = "";
        $l_title            = "";
        $l_m_strMessageText = '';

        if ($g_comp_database)
        {
            $l_objCMDBDAO = new isys_cmdb_dao($g_comp_database);

            //use DAO to get last entry in logbook
            $this->m_daoLogbook = new isys_component_dao_logbook($g_comp_database);

            try
            {
                $l_arLastEntry = $this->m_daoLogbook->get_result_latest_entry($this->m_nMessageType)
                    ->__to_array();

                //if set_message() was used don't do anything
                if (is_null($this->m_strMessage))
                {
                    if (!empty($l_arLastEntry))
                    {

                        $l_m_strMessage = $l_mod_event_manager->translateEvent(
                            $l_arLastEntry["isys_logbook__event_static"],
                            $l_arLastEntry["isys_logbook__obj_name_static"],
                            $l_arLastEntry["isys_logbook__category_static"],
                            $l_arLastEntry["isys_logbook__obj_type_static"],
                            $l_arLastEntry["isys_logbook__entry_identifier_static"],
                            $l_arLastEntry["isys_logbook__changecount"]
                        );

                        $this->m_nAlertLevel = $l_arLastEntry["isys_logbook_level__const"];
                        $this->m_strMessage  = $l_m_strMessage;
                        $this->m_nMessageID  = $l_arLastEntry["isys_logbook__id"];
                        $this->m_nUserID     = $l_arLastEntry["isys_logbook__isys_obj__id"];
                        $this->m_strDate     = $g_loc->fmt_datetime($l_arLastEntry["isys_logbook__date"]);
                    }
                    else
                    {
                        $this->m_nAlertLevel = C__LOGBOOK__ALERT_LEVEL__0;
                        $this->m_strMessage  = $g_comp_template_language_manager->{'LC__INFOBOX__NO_ENTRIES'};
                        $this->m_nMessageID  = 0;
                    }
                }

            }
            catch (isys_exception $e)
            {
                echo $e->getMessage();
            }

        }
        $l_nObjID = null;

        //set alert level
        if ($this->m_nAlertLevel == C__LOGBOOK__ALERT_LEVEL__0)
        {
            $l_strAlertLevel = "blue";
        }
        else if ($this->m_nAlertLevel == C__LOGBOOK__ALERT_LEVEL__1)
        {
            $l_strAlertLevel = "green";
        }
        else if ($this->m_nAlertLevel == C__LOGBOOK__ALERT_LEVEL__2)
        {
            $l_strAlertLevel = "yellow";
        }
        else if ($this->m_nAlertLevel == C__LOGBOOK__ALERT_LEVEL__3)
        {
            $l_strAlertLevel = "red";
            $l_fatal         = true;
        }

        if ($this->m_nMessageID != 0)
        {
            $l_url   = "href=\"?" . C__GET__MODULE_ID . "=" . C__MODULE__LOGBOOK . "&id=" . $this->m_nMessageID . "\"";
            $l_title = $g_comp_template_language_manager->{"LC__INFOBOX__TITLE"};
        }

        $l_imagesrc = $g_dirs["images"] . "icons/infobox/$l_strAlertLevel.png";

        if (!empty($this->m_strMessage))
        {
            $l_strExtInfo = '';
            if (!empty($this->m_strDate))
            {

                $l_date = date(C__INFOBOX__DATEFORMAT, strtotime(str_replace("- ", "", $this->m_strDate)));
                $l_strExtInfo .= " [" . $l_arLastEntry["isys_logbook__user_name_static"] . "]";

                $l_m_strMessageText = $l_date . " " . $this->m_strMessage . $l_strExtInfo;
                $l_m_strMessageText = isys_glob_cut_string($l_m_strMessageText, C__INFOBOX__LENGTH);

            }
            else
            {
                $l_m_strMessageText = $this->m_strMessage;
            }
        }

        $l_m_strMessageText = html_entity_decode(stripslashes($l_m_strMessageText), null, $GLOBALS['g_config']['html-encoding']);

        if (!empty($l_url))
        {
            $l_icon = "<a title=\"$l_title\" $l_url>" . "<img title=\"$l_title\" height=\"16\" alt=\"alertlevel\" src=\"$l_imagesrc\" />" . "</a>";
        }
        else
        {
            $l_icon = "<img title=\"$l_title\" height=\"16\" alt=\"alertlevel\" src=\"$l_imagesrc\" />";
        }

        if (isset($l_fatal) && $l_fatal)
        {

            $l_out .= <<<OUT
		$l_icon
		<span>$l_m_strMessageText</span>

	<script type="text/javascript">
		  isys_glob_show_error('$l_m_strMessageText');
	</script>
OUT;

        }
        else
        {
            $l_out .= <<<OUT
		$l_icon
		<span>$l_m_strMessageText</span>
OUT;
        }

        return $l_out;
    }

    public function __construct()
    {
        if (isys_glob_get_param("infoboxMsgType") != false)
        {
            $this->m_nMessageType = isys_glob_get_param("infoboxMsgType");
        } // if
    } // function
} // class